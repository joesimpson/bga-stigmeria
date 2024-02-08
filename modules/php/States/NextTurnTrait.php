<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;

trait NextTurnTrait
{
  
  public function stNextTurn()
  {
    
    if (Globals::getTurn() == TURN_MAX) {
      Notifications::emptyNotif();
      //TODO JSA MANAGE More with OPTIONS
      $this->gamestate->nextState('end');
      return;
    }

    Globals::incTurn(1);
    $turn = Globals::getTurn();

    Notifications::newTurn($turn);

    $this->gamestate->nextState('next');
  }
}
