<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;

trait PlayerTurnCommonBoardTrait
{
  
    public function stCommonBoardTurn()
    {
        self::trace("stCommonBoardTurn()");
        
    }

    public function argCommonBoardTurn($player_id)
    {
        $player = Players::get($player_id);
        return [
            'n'=> $player->countRemainingCommonActions(),
        ];
    }
    /**
     * TODO JSA : FOR TESTING only : it is forbidden to go to next steps before ending this step
     */
    public function actGoToNext()
    {
        self::checkAction( 'actGoToNext' ); 
        
        //moving current player to different state :
        $this->gamestate->nextPrivateState($this->getCurrentPlayerId(), "next");
    }

    public function actCommonDrawAndLand()
    {
        self::checkAction( 'actCommonDrawAndLand' ); 
        self::trace("actCommonDrawAndLand()");
        
        $player = Players::getCurrent();
        
        $remaining = $player->countRemainingCommonActions();
        $actionCost = 1;
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }

        //TODO JSA ACTION EFFECT
        $player->incNbCommonActionsDone();
        
        if($player->countRemainingCommonActions() == 0){
            //IF NO MORE ACTIONS on common board, go to personal board actions :
            $this->gamestate->nextPrivateState($player->id, "next");
        }
        else {
            $this->gamestate->nextPrivateState($player->id, "continue");
        }
    }
    public function actCommonMove()
    {
        self::checkAction( 'actCommonMove' ); 
        self::trace("actCommonMove()");
        
        $player = Players::getCurrent();
        
        $remaining = $player->countRemainingCommonActions();
        $actionCost = 1;
        //TODO JSA RULE Impossible to make 2 moves
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }

        //TODO JSA ACTION EFFECT
        $player->incNbCommonActionsDone();

        if($player->countRemainingCommonActions() == 0){
            //IF NO MORE ACTIONS on common board, go to personal board actions :
            $this->gamestate->nextPrivateState($player->id, "next");
        }
        else {
            $this->gamestate->nextPrivateState($player->id, "continue");
        }
    }
    
}
