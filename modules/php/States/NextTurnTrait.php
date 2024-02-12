<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;
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
    $winners = [];
    foreach($players as $pid => $player){
      $isWin = $this->isSchemaFulfilled($player);
      if($isWin){
        Notifications::schemaFulfilled($player);
        $end = true;
        $winners[] = $pid;
      }
    }
    if($end){
      
      Globals::setWinnersIds($winners);
      $this->gamestate->nextState('end');
      return;
    }
    //-------------------------------------------

    Globals::incTurn(1);
    $turn = Globals::getTurn();
    Stats::inc( "turns_number");

    Notifications::newTurn($turn);
    Players::setupNewTurn($players,$turn);

    $this->gamestate->nextState('next');
  }

}
