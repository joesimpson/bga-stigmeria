<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\DiceRoll;
use STIG\Managers\Players;
use STIG\Models\DiceFace;

trait WindGenerationTrait
{
  
  public function stGenerateWind()
  {
    $this->addCheckpoint(ST_GENERATE_WIND);
    
    if($this->generateWind() ){
      Notifications::newWinds(Globals::getAllWindDir());
      $this->gamestate->nextState('next');
    }
  }

  /**
   * @return bool true if generation is complete, false if we go to another state to continue
   */
  public function generateWind()
  {
    $this->trace("generateWind()");

    if(Globals::isModeCompetitiveNoLimit()){
      //ROLL DICE in NO LIMIT
      $lastDie = Globals::getLastDie();
      $weatherTurn = 0;
      if(isset($lastDie) && array_key_exists('turn',$lastDie)){
        $weatherTurn = $lastDie['turn'];
      }

      $k = $weatherTurn +1;
      while($k<=TURN_MAX -1 ){
        if($k==1){
          $playerId = Globals::getFirstPlayer();
          $nextPlayer = Players::get($playerId);
        }
        else {
          $activePlayer = Players::getActive();
          $nextPlayer = Players::getNextPlayerNotElimininated($activePlayer->id);
        }
        Players::changeActive($nextPlayer->id);
        
        $diceFace = DiceRoll::rollNew();
        Globals::setLastDie(['die' => $diceFace->type, 'turn'=> $k, 'stateFrom'=> ST_GENERATE_WIND]);
        $newWind = $diceFace->getWindDir();
        Notifications::weatherDice($diceFace,$k,$nextPlayer,$newWind);
        if(isset($newWind)){
          Globals::setWindDir($k, $newWind);
        }
        else {
          Globals::setWindDir($k, null);
          if( $diceFace->askPlayerNoChoice() ||$diceFace->askPlayerChoice() || $diceFace->askPlayerReroll()){
            $nextPlayer->giveExtraTime();
            $this->addCheckpoint(ST_WEATHER_PLAYER_DICE);
            $this->gamestate->nextState('playerDice');
            return false;
          }
        }
        $k++;
      }
    }
    else {
        
      //DEFAULT SOUTH if not NO LIMIT
      Globals::setWindDirection1(WIND_DIR_SOUTH);
      Globals::setWindDirection2(WIND_DIR_SOUTH);
      Globals::setWindDirection3(WIND_DIR_SOUTH);
      Globals::setWindDirection4(WIND_DIR_SOUTH);
      Globals::setWindDirection5(WIND_DIR_SOUTH);
      Globals::setWindDirection6(WIND_DIR_SOUTH);
      Globals::setWindDirection7(WIND_DIR_SOUTH);
      Globals::setWindDirection8(WIND_DIR_SOUTH);
      Globals::setWindDirection9(WIND_DIR_SOUTH);
      Globals::setWindDirection10(WIND_DIR_SOUTH);
      Globals::setWindDirection11(WIND_DIR_SOUTH);
    }
    return true;
  }

}
