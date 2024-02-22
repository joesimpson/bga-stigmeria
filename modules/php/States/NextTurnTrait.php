<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait NextTurnTrait
{
  
  public function stNextTurn()
  {
    $turn = Globals::getTurn();
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
    if (!Globals::isModeDiscovery() && $turn >= TURN_MAX) {
      Notifications::lastTurnEnd($turn);
      $this->gamestate->nextState('end');
      return;
    }
    //-------------------------------------------
    if(Globals::isModeCompetitive()){
      $this->eliminatePlayersWithEmptyDeck($players);
    }
    //-------------------------------------------
    // CHECK ELIMINATED
    //-------------------------------------------
    $eliminatedPids = [];
    foreach($players as $playerId => $player){
      if(PGlobals::isEliminated($playerId)) $eliminatedPids[] = $playerId;
    }
    if(count($eliminatedPids) == count($players)){
      //Eliminate ALL players -> go to end
      $this->gamestate->nextState('end');
      return;
    }
    else if(count($eliminatedPids) >0) {//Eliminate some players
      foreach($eliminatedPids as $playerId){
        self::eliminatePlayer( $playerId );
      }
    }
    //-------------------------------------------

    Globals::incTurn(1);
    $turn = Globals::getTurn();
    Stats::inc( "turns_number");

    Notifications::newTurn($turn);
    Players::setupNewTurn($players,$turn);
    PlayerActions::setupNewTurn($players,$turn);
    $this->addCheckpoint(ST_NEXT_TURN);

    if($turn ==1 && !Globals::isModeNoCentralBoard())
    {
      $firstPlayer = Globals::getFirstPlayer();
      Players::changeActive($firstPlayer);
      $this->addCheckpoint(ST_FIRST_TOKEN);
      $this->giveExtraTime($firstPlayer);
      $this->gamestate->nextState('FT');
      return;
    }
    
    $this->gamestate->nextState('next');
  }

  /**
   * @param Collection $players
   */
  public function eliminatePlayersWithEmptyDeck($players){
    foreach($players as $pid => $player){
      if(Tokens::countDeck($pid) ==0){
        PGlobals::setEliminated($pid, true);
        Notifications::deckElimination($player);
      }
    }
  }
}
