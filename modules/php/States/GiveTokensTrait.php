<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;

trait GiveTokensTrait
{
    public function argGiveTokens($playerId)
    {
        //$player = Players::get($playerId);
        
        return [
        ];
    }
      
    /**
     */
    public function actGiveTokens()
    {
        self::checkAction( 'actGiveTokens' ); 
        self::trace("actGiveTokens()");
        //TODO JSA actGiveTokens
        $player = Players::getCurrent();
        $pId = $player->id;
  
        $this->gamestate->nextPrivateState($pId, "next");
    }
 
}
