<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\GridUtils;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait ChoiceTokenToMoveTrait
{
    public function argChoiceTokenToMove($player_id)
    {
        $player = Players::get($player_id);
        $boardTokens = Tokens::getAllOnPersonalBoard($player_id);
        return [
            'n' => ACTION_COST_MOVE,
            'p_places_m' => $this->listPossibleMovesOnBoard($player_id,$boardTokens),
        ];
    }
      
    public function actCancelChoiceTokenToMove()
    {
        self::checkAction( 'actCancelChoiceTokenToMove' ); 
        self::trace("actCancelChoiceTokenToMove()");
        
        $player = Players::getCurrent();

        //NOTHING TO CANCEL In BDD, return to previous state

        $this->gamestate->nextPrivateState($player->id, "cancel");
    }
    /**
     * @param int $token_id
     * @param int $row
     * @param int $col
     */
    public function actChoiceTokenToMove($token_id, $row, $column)
    {
        self::checkAction( 'actChoiceTokenToMove' ); 
        self::trace("actChoiceTokenToMove($token_id, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $remaining = $player->countRemainingPersonalActions();
        $actionCost = ACTION_COST_MOVE;

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        if(!$this->canMoveOnPlayerBoard($pId,$token,$row, $column)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        
        //EFFECT : MOVE the TOKEN 
        $token->moveToPlayerBoard($player,$row,$column,$actionCost);
        Stats::inc("actions_3",$player->getId());
        Stats::inc("actions",$player->getId());
        
        $this->gamestate->nextPrivateState($player->id, "continue");
    }

    /**
     * Still the action of moving, but specifying no row/col, because we want to move out of the grid
     * @param int $token_id
     */
    public function actMoveOut($token_id)
    {
        self::checkAction( 'actMoveOut' ); 
        self::trace("actMoveOut($token_id)");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $actionCost = ACTION_COST_MOVE;
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        if(!$this->canMoveOutOnBoard($token)){
            throw new UnexpectedException(101,"You cannot move out this token");
        }

        //EFFECT : 
        if(Globals::isModeCompetitiveNoLimit()){
            //EFFECT : MOVE the TOKEN oUT
            $token->moveToRecruitZone($player,$actionCost);
        }
        else {
            //EFFECT : REMOVE the TOKEN 
            Stats::inc("tokens_board",$player->getId(),-1);
            Notifications::moveBackToBox($player, $token,$token->getCoordName(),$actionCost);
            Tokens::delete($token->id);
        }

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        Stats::inc("actions_3",$player->getId());
        Stats::inc("actions",$player->getId());
        
        $this->gamestate->nextPrivateState($player->id, "continue");
    }
    
    /**
     * @param int $playerId
     * @param StigmerianToken $token
     * @param int $row
     * @param int $col
     * @return bool TRUE if this token can be moved on this player board ( Empty adjacent spot),
     *  FALSE otherwise
     */
    public function canMoveOnPlayerBoard($playerId,$token,$row, $column)
    {
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;
        if($token->isPollen()) return false;

        if(!$token->isAdjacentCoord($row, $column)){
            return false;
        }

        //TODO JSA PERFS We could read all tokens on personal board before calling this function if we want to loop on this func
        $existingToken = Tokens::findOnPersonalBoard($playerId,$row, $column);
        if(isset($existingToken)) return false;//not empty

        return true;
    }

    /**
     * @param StigmerianToken $token
     * @return bool TRUE if this token can be moved out on this board ( grid edges),
     *  FALSE otherwise
     */
    public function canMoveOutOnBoard($token)
    {
        if($token->isPollen()) return false;
        return GridUtils::isValidCellToMoveOut($token->getRow(),$token->getCol(),$token->getLocation() == TOKEN_LOCATION_CENTRAL_BOARD);
    }
    /**
     * @param int $playerId
     * @param array $tokens of StigmerianToken
     * @return array List of possible spaces. Example [[ 'row' => 1, 'col' => 5 ],]
     */
    public function listPossibleMovesOnBoard($playerId,$tokens){
        $spots = [];
        foreach($tokens as $tokenId => $token){
            if($this->canMoveOutOnBoard($token)){
                $spots[$tokenId][] = [ 'out' => true ];
            }
            for($row = ROW_MIN; $row <=ROW_MAX; $row++ ){
                for($column = COLUMN_MIN; $column <=COLUMN_MAX; $column++ ){
                    if(isset($playerId) && $this->canMoveOnPlayerBoard($playerId,$token,$row, $column)){
                        $spots[$tokenId][] = [ 'row' => $row, 'col' => $column ];
                    }
                    else if(!isset($playerId) && $this->canMoveOnCentralBoard($token,$row, $column)){
                        $spots[$tokenId][] = [ 'row' => $row, 'col' => $column ];
                    }
                }
            }
        }
        return $spots;
    }

}
