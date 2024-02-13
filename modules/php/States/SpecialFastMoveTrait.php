<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\GridUtils;
use STIG\Helpers\Utils;
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
        return [
            'n' => $nMoves,
            'p_places_m' => $possibleMoves,
        ];
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
        $turn = Globals::getTurn();
 
        $actionCost = ACTION_COST_MOVE_FAST;
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        //TODO JSA CHECK NOT USED IN player turn
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canMoveFastOnPlayerBoard($pId,$boardTokens,$token,$row, $column, $turn)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        //EFFECT : MOVE the TOKEN 
        $token->moveToPlayerBoard($player,$row,$column,$actionCost);
        Stats::inc("actions_s6",$player->getId());
        Stats::inc("actions",$player->getId());

        $this->gamestate->nextPrivateState($pId, 'next');
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
        $possibleMoves = $this->listPossibleFastMovesOnBoardFromToken($playerId,$boardTokens,$token, $nMoves);
        $possibleMoveIndex = GridUtils::searchCell($possibleMoves, $column, $row);
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
     * @return array List of possible spaces. Example [[ 'x' => 1, 'y' => 5 ],] where x is for col, y for row
     */
    public function listPossibleFastMovesOnBoardFromToken($playerId,$tokens,$token, $nMoves){
        self::trace("listPossibleFastMovesOnBoardFromToken($playerId, $nMoves)");
        if($token->isPollen()) return [];

        $startingCell = [ 'x' => $token->getCol(), 'y' => $token->getRow(), ];
        $costCallback = function ($source, $target, $d) use ($tokens) {
            // If there is a unit => can't go there
            $existingToken = Tokens::findTokenOnBoardWithCoord($tokens,$target['y'], $target['x']);
            if(isset($existingToken)) return 10000;//not valid position
            return 1;
        };
        $cellsMarkers = GridUtils::getReachableCellsAtDistance($startingCell,$nMoves, $costCallback);
        $cells = $cellsMarkers[0];
        $markers = $cellsMarkers[1];
        //self::trace("listPossibleFastMovesOnBoardFromToken(".json_encode($startingCell)." ) : cells=".json_encode($cells)." /// : markers=".json_encode($markers));
        return $cellsMarkers[0];
    }

    /**
     * @param int $playerId
     * @param Collection $tokens of StigmerianToken
     * @return array List of possible spaces. Example [[ 'x' => 1, 'y' => 5 ],] where x is for col, y for row
     */
    public function listPossibleFastMovesOnBoard($playerId,$tokens, $nMoves){
        self::trace("listPossibleFastMovesOnBoard($playerId, $nMoves)");
        $spots = [];
        
        foreach($tokens as $tokenId => $token){
            $possibleMoves = $this->listPossibleFastMovesOnBoardFromToken($playerId,$tokens,$token, $nMoves);
            if(count($possibleMoves)>0) $spots[$tokenId] = $possibleMoves;
        }
        return $spots;
    }

 
}
