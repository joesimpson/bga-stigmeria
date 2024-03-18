<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\GridUtils;
use STIG\Helpers\Utils;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;
use STIG\Models\TokenCoord;

trait SpecialFastMoveTrait
{
    public function argSpFastMove($player_id)
    {
        $player = Players::get($player_id);
        $boardTokens = Tokens::getAllOnPersonalBoard($player_id);
        $nMoves = Globals::getTurn();
        $possibleMoves = $this->listPossibleFastMovesOnBoard($player_id,$boardTokens,$nMoves);
        $args = [
            'n' => $nMoves,
            'p_places_m' => $possibleMoves,
            'cancel' => true,
        ];
        $this->checkCancelFromLastDrift($args,$player_id);
        return $args;
    } 
    /**
     * Special action of moving 1 token with 1->N moves to somewhere
     * @param int $token_id
     * @param int $row
     * @param int $col
     */
    public function actFastMove($token_id, $row, $column)
    {
        self::checkAction( 'actFastMove' ); 
        self::trace("actFastMove($token_id, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($pId, $player->getPrivateState());
        $turn = Globals::getTurn();
        $actionType = ACTION_TYPE_MOVE_FAST;
 
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canMoveFastOnPlayerBoard($pId,$boardTokens,$token,$row, $column, $turn)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }

        //This action is now USED IN player turn
        $playerAction->setNewStateAfterUse();
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player,$playerAction);
        $player->giveExtraTime();
        //EFFECT : MOVE the TOKEN 
        Notifications::spFastMove($player,$actionCost);
        $token->moveToPlayerBoard($player,$row,$column,0);
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$player->getId());

        if($this->returnToLastDriftState($pId,$playerAction)) return;

        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
        $this->gamestate->nextPrivateState($pId, 'next');
    }

    
    /**
     * Still the action of moving, but specifying no row/col, because we want to move out of the grid
     * @param int $token_id
     */
    public function actMoveOutFast($token_id)
    {
        self::checkAction( 'actMoveOutFast' ); 
        self::trace("actMoveOutFast($token_id)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($pId, $player->getPrivateState());
        $turn = Globals::getTurn();
        $actionType = ACTION_TYPE_MOVE_FAST;
 
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canMoveOutFastOnPlayerBoard($pId,$boardTokens,$token,$turn)){
            throw new UnexpectedException(101,"You cannot fast move out this token");
        }

        //EFFECT : 
        //This action is now USED IN player turn
        $playerAction->setNewStateAfterUse();
        Notifications::spFastMove($player,$actionCost);
        if(Globals::isModeNoLimitRules()){
            //EFFECT : MOVE the TOKEN oUT
            $token->moveToRecruitZone($player,0);
        }
        else {
            Stats::inc("tokens_board",$player->getId(),-1);
            //EFFECT : REMOVE the TOKEN 
            Notifications::moveBackToBox($player, $token,$token->getCoordName(),0);
            Tokens::delete($token->id);
        }

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player,$playerAction);
        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        
        if($this->returnToLastDriftState($pId,$playerAction)) return;
        
        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
        $this->gamestate->nextPrivateState($player->id, "next");
    }

    /**
     * @param int $playerId
     * @param Collection $boardTokens of StigmerianToken
     * @param StigmerianToken $token
     * @param int $row
     * @param int $column
     * @param int $nMoves number of steps to move
     * @return bool TRUE if this token can be move on this player board ( Empty adjacent spots on path) with n steps,
     *  FALSE otherwise
     */
    public function canMoveFastOnPlayerBoard($playerId,$boardTokens,$token,$row, $column,$nMoves)
    {
        /*
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;
        if($token->isPollen()) return false;
        $existingToken = Tokens::findTokenOnBoardWithCoord($boardTokens,$row, $column);
        if(isset($existingToken)) return false;//not empty
        */
        $passiveDiagonal = PlayerActions::hasUnlockedPassiveDiagonal($playerId);
        $possibleMoves = $this->listPossibleFastMovesOnBoardFromToken($playerId,$boardTokens,$token, $nMoves,$passiveDiagonal);
        $possibleMoveIndex = GridUtils::searchCell($possibleMoves, $column, $row);
        if ($possibleMoveIndex === false) {
            return false;
        }
        return true;
    }
    
    /**
     * @param int $playerId
     * @param Collection $boardTokens of StigmerianToken
     * @param StigmerianToken $token
     * @param int $row
     * @param int $column
     * @param int $nMoves number of steps to move
     * @return bool TRUE if this token can be moved OUT of this player board ( Empty adjacent spots on path) with n steps,
     *  FALSE otherwise
     */
    public function canMoveOutFastOnPlayerBoard($playerId,$boardTokens,$token,$nMoves)
    {
        $possibleMoves = $this->listPossibleFastMovesOnBoardFromToken($playerId,$boardTokens,$token, $nMoves);
        $possibleMoveIndex = GridUtils::array_usearch($possibleMoves, function ($cell) {
            return isset($cell['out']) && $cell['out']== true;
        });
        if ($possibleMoveIndex === false) {
            return false;
        }
        return true;
    }
    /**
     * @param int $playerId
     * @param Collection $tokens of StigmerianToken
     * @param StigmerianToken $token
     * @param int $nMoves
     * @param bool $passiveDiagonal (optional) false by default
     * @return array List of possible spaces. Example [[ 'x' => 1, 'y' => 5 ],] where x is for col, y for row
     */
    public function listPossibleFastMovesOnBoardFromToken($playerId,$tokens,$token, $nMoves,$passiveDiagonal =false){
        self::trace("listPossibleFastMovesOnBoardFromToken($playerId, $nMoves,$passiveDiagonal)");
        if($token->isPollen()) return [];

        $startingCell = [ 'x' => $token->getCol(), 'y' => $token->getRow(), ];
        $costCallback = function ($source, $target, $d) use ($tokens) {
            // If there is a token => can't go there
            $existingToken = Tokens::findTokenOnBoardWithCoord($tokens,$target['y'], $target['x']);
            if(isset($existingToken)) return 10000;//not valid position
            return 1;
        };
        $cellsMarkers = GridUtils::getReachableCellsAtDistance($startingCell,$nMoves, $costCallback,$passiveDiagonal);
        $cells = $cellsMarkers[0];
        //$markers = $cellsMarkers[1];
        //self::trace("listPossibleFastMovesOnBoardFromToken(".json_encode($startingCell)." ) : cells=".json_encode($cells)." /// : markers=".json_encode($markers));
        return $cells;
    }

    /**
     * @param int $playerId
     * @param Collection $tokens of StigmerianToken
     * @return array List of possible spaces. Example [[ 'x' => 1, 'y' => 5 ],] where x is for col, y for row
     */
    public function listPossibleFastMovesOnBoard($playerId,$tokens, $nMoves){
        self::trace("listPossibleFastMovesOnBoard($playerId, $nMoves)");
        $spots = [];
        
        $passiveDiagonal = PlayerActions::hasUnlockedPassiveDiagonal($playerId);
        foreach($tokens as $tokenId => $token){
            $possibleMoves = $this->listPossibleFastMovesOnBoardFromToken($playerId,$tokens,$token, $nMoves,$passiveDiagonal);
            if(count($possibleMoves)>0) $spots[$tokenId] = $possibleMoves;
        }
        return $spots;
    }

 
}
