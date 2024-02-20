<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\Log;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait PlayerTurnCommonBoardTrait
{
  
    public function stCommonBoardTurn()
    {
        self::trace("stCommonBoardTurn()");
        
        Notifications::emptyNotif();
    }

    public function argCommonBoardTurn($player_id)
    {
        $player = Players::get($player_id);
        $nbMoves = $player->countRemainingCommonActions();
        $actions[] = 'actCommonDrawAndLand';
        if(!$player->isCommonMoveDone()){
            $actions[] = 'actCommonMove';
        }
        if($nbMoves <1){
            $actions[] = 'actGoToNext';
        }
        $actions[] = 'actCommonJoker';
        return array_merge( [
            'n'=> $nbMoves,
            'a' => $actions,
        ], 
        $this->argsConfirmTurn($player_id));
    }
    /**
     * BEware : it is forbidden to go to next steps before ending this step
     */
    public function actGoToNext()
    {
        self::checkAction( 'actGoToNext' ); 
        
        $player = Players::getCurrent();
        if($player->countRemainingCommonActions() > 0){
            throw new UnexpectedException(10,"You still have actions to take");
        }

        $this->addCheckpoint(ST_TURN_PERSONAL_BOARD, $player->id);
        //moving current player to different state :
        $this->gamestate->nextPrivateState($this->getCurrentPlayerId(), "next");
    }

      
    /**
     * Central Action 1 : landing a stigmerian on central board
     */
    public function actCommonDrawAndLand()
    {
        self::checkAction( 'actCommonDrawAndLand' ); 
        self::trace("actCommonDrawAndLand()");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
        
        $remaining = $player->countRemainingCommonActions();
        $actionCost = ACTION_COST_CENTRAL_LAND;
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }

        //ACTION EFFECT
        $token = Tokens::pickOneForLocation(TOKEN_LOCATION_PLAYER_DECK.$pId, TOKEN_LOCATION_CENTRAL_RECRUIT_TOPLACE, TOKEN_STATE_STIGMERIAN);
        if($token == null){
            //TODO JSA LOST GAME (maybe already lost before looking in the bag ?)
            throw new UnexpectedException(404,"Not supported draw : empty draw bag for player $pId");
        }
        Stats::inc("tokens_deck",$player->getId(),-1);
        Notifications::drawTokenForCentral($player,$token);
        $this->addCheckpoint(ST_TURN_CENTRAL_CHOICE_TOKEN_LAND,$pId);

        $this->gamestate->nextPrivateState($player->id, "startLand");
        return;
        /*
        if($player->countRemainingCommonActions() == 0){
            //IF NO MORE ACTIONS on common board, go to personal board actions :
            $this->gamestate->nextPrivateState($player->id, "next");
        }
        else {
            $this->gamestate->nextPrivateState($player->id, "continue");
        }
        */
    }
    public function actCommonMove()
    {
        self::checkAction( 'actCommonMove' ); 
        self::trace("actCommonMove()");
        
        $player = Players::getCurrent();
        //no need with my white 'cancel' button ?
        //$this->addStep($player->id, $player->getPrivateState());
        PGlobals::setState($player->id, $player->getPrivateState());
        
        if($player->isCommonMoveDone()){
            throw new UnexpectedException(9,"You cannot do that action twice in the turn");
        }
        $remaining = $player->countRemainingCommonActions();
        $actionCost = ACTION_COST_CENTRAL_MOVE;
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }

        $this->gamestate->nextPrivateState($player->id, "startMove");
        /*
        if($player->countRemainingCommonActions() == 0){
            //IF NO MORE ACTIONS on common board, go to personal board actions :
            $this->gamestate->nextPrivateState($player->id, "next");
        }
        else {
            $this->gamestate->nextPrivateState($player->id, "continue");
        }
        */
    }

    /**
     * @param int $tokenType
     * @param int $tokenRow
     * @param int $tokenColumn
     * @return array aligned tokens for actions earned
     */
    public function checkBoardForGainingAction( $tokenType,$tokenRow,$tokenColumn)
    {
        $alignedTokens = [];
        $counter = 0;
        // LOOK for 2 tokens
        $delta = NB_ALIGNED_TOKENS_TO_GAIN_ACTIONS -1;
        for($row = max(ROW_MIN, $tokenRow- $delta ); $row <= min(ROW_MAX,$tokenRow+$delta); $row++ ){
            $token = Tokens::findOnCentralBoard($row,$tokenColumn);
            //self::trace("checkBoardForGainingAction ($tokenRow,$tokenColumn) DELTA ROW $row,$tokenColumn : ".json_encode($token));
            if(isset($token) && $tokenType == $token->getType()){
                $counter++;
                $alignedTokens[] = $token->getId();
            }
            else {
                $counter = 0;
                $alignedTokens = [];
            }
            if($counter == NB_ALIGNED_TOKENS_TO_GAIN_ACTIONS) {
                break;
            }
        }
        $counter = 0;
        $alignedTokensCol = [];
        for($column = max(COLUMN_MIN, $tokenColumn- $delta ); $column <= min(COLUMN_MAX,$tokenColumn+$delta); $column++ ){
            $token = Tokens::findOnCentralBoard($tokenRow,$column);
            //self::trace("checkBoardForGainingAction($tokenRow,$tokenColumn) DELTA COL $tokenRow,$column : ".json_encode($token));
            if(isset($token) && $tokenType == $token->getType()){
                $counter++;
                $alignedTokensCol[] = $token->getId();
            }
            else {
                $counter = 0;
                $alignedTokensCol = [];
            }
            if($counter == NB_ALIGNED_TOKENS_TO_GAIN_ACTIONS) {
                break;
            }
        }
        $alignedTokens = array_merge($alignedTokens, $alignedTokensCol);
        //self::trace("checkBoardForGainingAction($tokenRow,$tokenColumn) aligned tokens : ".json_encode($alignedTokens));
        return $alignedTokens;
    }

    
    //TODO JSA actCommonJoker for Competitive games NOT no limit !
}
