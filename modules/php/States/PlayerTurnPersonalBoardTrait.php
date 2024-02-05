<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\Players;

trait PlayerTurnPersonalBoardTrait
{
  
    public function stPersonalBoardTurn()
    {
        self::trace("stCommonBoardTurn()");
        
    }

    public function argPersonalBoardTurn()
    {

        return [
        ];
    }
    
    /**
     * TODO JSA : Proof of concept, maybe not necessary
     */
    public function actBackToCommon()
    {
        self::checkAction( 'actBackToCommon' ); 
        
        //moving current player to different state :
        $this->gamestate->nextPrivateState($this->getCurrentPlayerId(), "back");
    }
    
}
