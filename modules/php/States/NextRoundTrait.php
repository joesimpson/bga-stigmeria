<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait NextRoundTrait
{
  
  public function stNextRound()
  { 
    self::trace("stNextRound()");

    $players = Players::setupNewRound();
    Globals::setupNewRound();
    Tokens::setupNewRound($players);
    $tokens = Tokens::getUiData();

    $round = Globals::getRound();
    $schema = Globals::getSchema();
    Notifications::newRound($round,$schema,$tokens);

    $this->gamestate->nextState('next');
  }
}
