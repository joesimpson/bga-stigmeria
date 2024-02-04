<?php

namespace STIG\States;

use STIG\Core\Globals;

trait PlayerTurnTrait
{
  
    public function stPlayerturn()
    {
        self::trace("stPlayerTurn()");
        
        $firstPlayer = Globals::getFirstPlayer();
        //When starting this state, First player is almost in a "activeplayer" situation :
        $playersToActive = [$firstPlayer];
        //During his turn, others may become active...
        $this->gamestate->setPlayersMultiactive( $playersToActive, 'end' );
    }

    public function argPlayerTurn()
    {
    }
    
}
