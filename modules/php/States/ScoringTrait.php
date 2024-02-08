<?php

namespace STIG\States;

use STIG\Core\Notifications;

trait ScoringTrait
{
  
  public function stScoring()
  {
    Notifications::emptyNotif();
  }
  
  public function stPreEndOfGame()
  {
    Notifications::emptyNotif();
  }
}
