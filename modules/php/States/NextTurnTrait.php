<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\Players;

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
    //-------------------------------------------
    $end = false;
    $players = Players::getAll();
    foreach($players as $pid => $player){
      $isWin = $this->isSchemaFulfilled($player);
      if($isWin){
        Notifications::schemaFulfilled($player);
        $end = true;
      }
    }
    if($end){
      $this->gamestate->nextState('end');
      return;
    }
    //-------------------------------------------

    Globals::incTurn(1);
    $turn = Globals::getTurn();

    Notifications::newTurn($turn);

    $this->gamestate->nextState('next');
  }
}
