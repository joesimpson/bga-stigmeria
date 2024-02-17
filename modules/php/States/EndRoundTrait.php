<?php

namespace STIG\States;

use STIG\Core\Notifications;

trait EndRoundTrait
{
  
  public function stEndRound()
  { 
    self::trace("stEndRound()");

    Notifications::emptyNotif();
    
    $this->computeSchemaScoring();

    $this->gamestate->nextState('end');
  }
}
