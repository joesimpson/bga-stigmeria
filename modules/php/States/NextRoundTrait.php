<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\Schema;

trait NextRoundTrait
{
  
  public function stNewRound()
  { 
    self::trace("stNewRound()");

    $players = Players::setupNewRound();
    Globals::setupNewRound();
    $round = Globals::getRound();
    $schema = Schemas::getCurrentSchema();
    Tokens::setupNewRound($players,$schema);
    $tokens = Tokens::getUiData();

    Notifications::newRound($round,$schema,$tokens);
    //Notifications::emptyNotif();

    $this->gamestate->nextState('next');
  }
}
