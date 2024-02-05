<?php

namespace STIG\States;

use STIG\Core\Game;
use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Engine;
use STIG\Core\Stats;
use STIG\Core\Preferences;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait SetupTrait
{
  
  /*
      setupNewGame:
      
      This method is called only once, when a new game is launched.
      In this method, you must setup the game according to the game rules, so that
      the game is ready to be played.
  */
  protected function setupNewGame($players, $options = [])
  {
    Players::setupNewGame($players, $options);
    Globals::setupNewGame($players, $options);
    Preferences::setupNewGame($players, $this->player_preferences);
    //    Stats::checkExistence();
    /*foreach ($players as $pId => $player) {
      Players::get($player['id'])->initStats(count($players));
    }
    */
    Tokens::setupNewGame($players, $options);

    $this->setGameStateInitialValue('logging', false);
    // Activate first player (which is in general a good idea :) )
    $this->activeNextPlayer();
    /************ End of the game initialization *****/
  }
}
