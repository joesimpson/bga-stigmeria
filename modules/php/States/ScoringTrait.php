<?php

namespace STIG\States;

use STIG\Core\Notifications;

trait ScoringTrait
{
  
  public function stScoring()
  {
    Notifications::message('TODO scoring');
    
    $this->gamestate->nextState('next');
  }
  
  public function stPreEndOfGame()
  {
    Notifications::message('Game is ending...');
    $this->gamestate->nextState('next');
  }
}
