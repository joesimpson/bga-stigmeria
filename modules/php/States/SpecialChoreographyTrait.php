<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialChoreographyTrait
{
    public function argSpChoreography($player_id)
    {
        $player = Players::get($player_id);
        $boardTokens = Tokens::getAllOnPersonalBoard($player_id);
        $turn = Globals::getTurn();
        $nbMovesMax = $turn -CHOREOGRAPHY_NB_TURNS_BEFORE;
        $movedTokensIds = $player->getSelection();
        $nbMovesDone = count($movedTokensIds);
        $nbMovesRemaining = $nbMovesMax - $nbMovesDone;
        $p_places_m = [];
        if($nbMovesRemaining>0){
            $p_places_m = $this->listPossibleChoreographyMovesOnBoard($player_id,$boardTokens,$movedTokensIds);
        }

        return [
            'n' => $nbMovesRemaining,
            'max' => $nbMovesMax,
            'movedTokensIds' => $movedTokensIds,
            'p_places_m' => $p_places_m,
        ];
    }
      
    /**
     * @param int $tokenId
     * @param int $row
     * @param int $col
     */
    public function actChoreography($tokenId, $row, $column)
    {
        self::checkAction( 'actChoreography' ); 
        self::trace("actChoreography($tokenId, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $turn = Globals::getTurn();
        $nbMovesMax = $turn -CHOREOGRAPHY_NB_TURNS_BEFORE;
        $movedTokensIds = $player->getSelection();
        $nbMovesDone = count($player->getSelection());

        $actionType = ACTION_TYPE_CHOREOGRAPHY;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if($nbMovesDone ==0 && !$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        if($nbMovesMax - $nbMovesDone < 1){
            throw new UnexpectedException(11,"Not enough moves remaining");
        }
        $token = Tokens::get($tokenId);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        if(!$this->canMoveChoreographyOnPlayerBoard($pId,$token,$row, $column,$movedTokensIds)){
            throw new UnexpectedException(101,"You cannot move this token at $row, $column");
        }

        //EFFECT : MOVE the TOKEN 
        if($nbMovesDone ==0 ){
            //This action is now USED IN player turn
            $playerAction->setState(ACTION_STATE_LOCKED_FOR_TURN);
            Notifications::spChoreography($player,$nbMovesMax,$actionCost);
            Stats::inc("actions_s".$actionType,$pId);
            Stats::inc("actions",$pId);
            $player->incNbPersonalActionsDone($actionCost);
            Notifications::useActions($player);
            $player->giveExtraTime();
        }
        $token->moveToPlayerBoard($player,$row,$column,0);
            
        $movedTokensIds[] = $tokenId;
        $player->setSelection($movedTokensIds);
        $nbMovesDone++;

        if($nbMovesMax - $nbMovesDone >= 1){
            $this->gamestate->nextPrivateState($player->id, "continue");
        }
        else {
            $this->gamestate->nextPrivateState($player->id, "next");
        }
    }
    
    /**
     * Still the action of moving, but specifying no row/col, because we want to move out of the grid
     * @param int $tokenId
     */
    public function actChoreMoveOut($tokenId)
    {
        self::checkAction( 'actChoreMoveOut' ); 
        self::trace("actChoreMoveOut($tokenId)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $turn = Globals::getTurn();
        $nbMovesMax = $turn -CHOREOGRAPHY_NB_TURNS_BEFORE;
        $movedTokensIds = $player->getSelection();
        $nbMovesDone = count($player->getSelection());

        $actionType = ACTION_TYPE_CHOREOGRAPHY;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if($nbMovesDone ==0 && !$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        if($nbMovesMax - $nbMovesDone < 1){
            throw new UnexpectedException(11,"Not enough moves remaining");
        }
        $token = Tokens::get($tokenId);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(100,"You cannot move this token");
        }
        if(!$this->canMoveOutOnBoard($token)){
            throw new UnexpectedException(101,"You cannot move out this token");
        }

        //EFFECT : 
        if($nbMovesDone ==0 ){
            //This action is now USED IN player turn
            $playerAction->setState(ACTION_STATE_LOCKED_FOR_TURN);
            Notifications::spChoreography($player,$nbMovesMax,$actionCost);
            Stats::inc("actions_s".$actionType,$pId);
            Stats::inc("actions",$pId);
            $player->incNbPersonalActionsDone($actionCost);
            Notifications::useActions($player);
            $player->giveExtraTime();
        }
        if(Globals::isModeCompetitiveNoLimit()){
            //EFFECT : MOVE the TOKEN oUT
            $token->moveToRecruitZone($player,0);
        }
        else {
            //EFFECT : REMOVE the TOKEN 
            Stats::inc("tokens_board",$player->getId(),-1);
            Notifications::moveBackToBox($player, $token,$token->getCoordName(),0);
            Tokens::delete($token->id);
        }
       
        $movedTokensIds[] = $tokenId;
        $player->setSelection($movedTokensIds);
        $nbMovesDone++;

        if($nbMovesMax - $nbMovesDone >= 1){
            $this->gamestate->nextPrivateState($player->id, "continue");
        }
        else {
            $this->gamestate->nextPrivateState($player->id, "next");
        }
    }
    
    
    public function actChoreographyStop()
    {
        self::checkAction( 'actChoreographyStop' ); 
        self::trace("actChoreographyStop()");
        
        $player = Players::getCurrent();
        $player->setSelection([]);
        $this->gamestate->nextPrivateState($player->id, "next");
    }

    
    /**
     * @param int $playerId
     * @param StigmerianToken $token
     * @param int $row
     * @param int $col
     * @param array $movedTokensIds already moved tokens
     * @return bool TRUE if this token can be move on this player board ( Empty adjacent spot),
     *  FALSE otherwise
     */
    public function canMoveChoreographyOnPlayerBoard($playerId,$token,$row, $column,$movedTokensIds)
    {
        if(in_array($token->getId(),$movedTokensIds)) return false;
        //TODO JSA PERFS
        if(!$this->canMoveOnPlayerBoard($playerId,$token,$row, $column)) return false;

        return true;
    }

    /**
     * @param int $playerId
     * @param array $boardTokens of StigmerianToken
     * @param array $movedTokensIds already moved tokens
     * @return array List of possible spaces. Example [[ 'row' => 1, 'col' => 5 ],]
     */
    public function listPossibleChoreographyMovesOnBoard($playerId,$boardTokens,$movedTokensIds){
        $spots = [];
        foreach($boardTokens as $tokenId => $token){
            if($this->canMoveOutOnBoard($token)){
                $spots[$tokenId][] = [ 'out' => true ];
            }
            for($row = ROW_MIN; $row <=ROW_MAX; $row++ ){
                for($column = COLUMN_MIN; $column <=COLUMN_MAX; $column++ ){
                    if(isset($playerId) && $this->canMoveChoreographyOnPlayerBoard($playerId,$token,$row, $column,$movedTokensIds)){
                        $spots[$tokenId][] = [ 'row' => $row, 'col' => $column ];
                    }
                }
            }
        }
        return $spots;
    }

}
