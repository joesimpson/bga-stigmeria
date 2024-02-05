<?php

namespace STIG\Models;

use STIG\Core\Globals;
use STIG\Core\Stats;
use STIG\Core\Notifications;
use STIG\Core\Preferences;
use STIG\Managers\Players;

/*
 * Player: all utility functions concerning a player
 */

class Player extends \STIG\Helpers\DB_Model
{
  private $map = null;
  protected $table = 'player';
  protected $primary = 'player_id';
  protected $attributes = [
    'id' => ['player_id', 'int'],
    'no' => ['player_no', 'int'],
    'name' => 'player_name',
    'color' => 'player_color',
    'eliminated' => 'player_eliminated',
    'score' => ['player_score', 'int'],
    'scoreAux' => ['player_score_aux', 'int'],
    'zombie' => 'player_zombie',
    //GAME SPECIFIC :
    'multiactive' => ['player_is_multiactive', 'bool'],
    'lastTurn' => ['player_turn', 'int'],
    'nbCommonActionsDone' => ['player_common_actions', 'int'],
    'nbPersonalActionsDone' => ['player_personal_actions', 'int'],
  ];

  public function getUiData($currentPlayerId = null)
  {
    $data = parent::getUiData();
    $current = $this->id == $currentPlayerId;

    return $data;
  }

  public function getPref($prefId)
  {
    return Preferences::get($this->id, $prefId);
  }

  public function getStat($name)
  {
    $name = 'get' . \ucfirst($name);
    return Stats::$name($this->id);
  }
  
  public function addPoints($points)
  {
    $this->setScore( $this->getScore() + $points);
    Stats::inc( "score", $this->id, $points );
  }

  /**
   * Sets player datas related to turn number $turnIndex
   * @param int $turnIndex
   */
  public function startTurn($turnIndex)
  {
    //$this->incLastTurn();
    $this->setLastTurn($turnIndex);
    $this->setNbCommonActionsDone(0);
    $this->setNbPersonalActionsDone(0);

    Notifications::startTurn($this,$turnIndex);
  }

  public function countRemainingCommonActions(){
    $max = MAX_COMMON_ACTIONS_BY_TURN;
    $done = $this->getNbCommonActionsDone();
    return $max - $done;
  }

  public function countRemainingPersonalActions(){
    $turn = Globals::getTurn();
    $max = min(MAX_PERSONAL_ACTIONS_BY_TURN, $turn); //10 actions for turns 11,12,...
    $done = $this->getNbPersonalActionsDone();
    return $max - $done;
  }
}
