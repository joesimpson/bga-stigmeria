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

    public function actCommonDrawAndLand()
    {
        self::checkAction( 'actCommonDrawAndLand' ); 
        
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
