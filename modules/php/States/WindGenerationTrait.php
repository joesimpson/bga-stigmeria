<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;

trait WindGenerationTrait
{
  
  public function stGenerateWind()
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
    
    //TODO JSA ROLL DICE in NO LIMIT

    Notifications::newWinds(Globals::getAllWindDir());

    $this->gamestate->nextState('next');
  }
}
