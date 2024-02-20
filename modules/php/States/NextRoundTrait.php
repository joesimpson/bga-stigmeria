<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Managers\PlayerActions;
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
    Stats::setupNewRound($players,$schema);
    Tokens::setupNewRound($players,$schema);
    $tokens = Tokens::getUiData();
    PlayerActions::setupNewRound($players,$schema);
    $actions = PlayerActions::getUiData();
    Notifications::newRound($round,$schema,$tokens,$actions);
    $this->addCheckpoint();

    $this->gamestate->nextState('next');
  }
}
