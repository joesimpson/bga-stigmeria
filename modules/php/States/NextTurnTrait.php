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
    // CHECK WIN conditions
    //-------------------------------------------
    $players = Players::getAll();
    $winners = Globals::getWinnersIds();
    if(!isset($winners)) $winners = [];
    foreach($players as $pid => $player){
      if(in_array($pid,$winners)) continue;

      $isWin = $this->isSchemaFulfilled($player);
      if($isWin){
        Notifications::schemaFulfilled($player);
        $winners[] = $pid;
      }
    }
    Globals::setWinnersIds($winners);
    if( count($winners)==count($players) 
      || count($winners)>0 && !Globals::isModeContinueToLastTurn()
    ){
      $this->gamestate->nextState('end');
      return;
    }
    //-------------------------------------------
    if (!Globals::isModeNoTurnLimit() && $turn >= TURN_MAX) {
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
      PGlobals::setEngineChoices($playerId, 0);
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
    Globals::setupNewTurn();
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
    if($turn > 1 && Globals::isModeSoloNoLimit())
    {
      //SOLO NO LIMIT GAIN 1 SPECIAL ACTION per turn
      $pId = Globals::getFirstPlayer();
      $canGainSp = (PlayerActions::countActions($pId) < MAX_SPECIAL_ACTIONS ) && count($this->listPossibleNewSpAction($pId))>0;
      if($canGainSp){
        Players::changeActive($pId);
        PGlobals::setNbSpActions($pId,1);
        PGlobals::setNbSpActionsMax($pId,1);
        $this->addCheckpoint(ST_SOLO_CHOICE_SP);
        $this->giveExtraTime($pId);
        $this->gamestate->nextState('soloGainSP');
        return;
      }
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
