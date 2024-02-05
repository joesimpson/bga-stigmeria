<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\Players;

trait PlayerTurnCommonBoardTrait
{
  
    public function stCommonBoardTurn()
    {
        self::trace("stCommonBoardTurn()");
        
    }

    public function argCommonBoardTurn()
    {

        return [
        ];
    }

    public function actCommonMove()
    {
        self::checkAction( 'actCommonMove' ); 
        
        //TODO JSA IF NO MORE ACTIONS on common board, go to personal board actions :
        //moving current player to different state :
        $this->gamestate->nextPrivateState($this->getCurrentPlayerId(), "next");
    }
    
}
