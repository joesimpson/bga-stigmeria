<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;

trait NextRoundTrait
{
  
  public function stNewRound()
  { 
    self::trace("stNewRound()");

    $this->addCheckpoint(ST_NEXT_ROUND);
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
    
    $this->gamestate->nextState('next');
  }
}
