<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\DiceRoll;
use STIG\Models\DiceFace;

trait WindGenerationTrait
{
  
  public function stGenerateWind()
  {
    $this->addCheckpoint(ST_GENERATE_WIND);
    
    $this->generateWind();
    Notifications::newWinds(Globals::getAllWindDir());

    $this->gamestate->nextState('next');
  }

  public function generateWind()
  {
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
    
    if(Globals::isModeCompetitiveNoLimit()){
      //TODO JSA ROLL DICE in NO LIMIT
      for($k=1;$k<=TURN_MAX +1;$k++){
        $setterName = "setWindDirection$k";
        
        $diceFace = DiceRoll::rollNew();
        $newWind = $diceFace->getWindDir();
        if(isset($newWind)){
          Globals::$setterName( $newWind);
        }
        else {
          //TODO JSA next active player to choose
        }
      }
    }

  }

}
