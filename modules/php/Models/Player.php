<?php

namespace STIG\Models;

use STIG\Core\Game;
use STIG\Core\Globals;
use STIG\Core\Stats;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Preferences;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

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
    'privateState' => ['player_state', 'int'],
    'zombie' => 'player_zombie',
    //GAME SPECIFIC :
    'multiactive' => ['player_is_multiactive', 'bool'],

    'jokerUsed' => ['player_joker_used', 'bool'],

    //Those are replaced by Player Globals 'PGlobals'
    //'nbCommonActionsDone' => ['player_common_actions', 'int'],
    //'nbPersonalActionsDone' => ['player_personal_actions', 'int'],
    //'lastTurn' => ['player_turn', 'int'],
    //'commonMoveDone' => ['player_common_move', 'bool'],
    //for tokens selection :
    //'selection' => ['player_selection', 'obj'],
  ];

  public function getUiData($currentPlayerId = null)
  {
    $data = parent::getUiData();
    $current = $this->id == $currentPlayerId;

    $data['tokens_recruit'] = Tokens::countRecruits($this->getId());
    $data['tokens_deck'] = Tokens::countDeck($this->getId());
    $data['pollens'] = Tokens::countOnPlayerBoard($this->getId(),array_values(TOKEN_POLLENS));
    //decrease JSON SIZE :
    $data['ncad'] = $this->getNbCommonActionsDone();
    unset($data['nbCommonActionsDone']);
    //decrease JSON SIZE :
    $data['npad'] = $this->getNbPersonalActionsDone();
    unset($data['nbPersonalActionsDone']);
    //decrease JSON SIZE :
    $data['ncmd'] = $this->isCommonMoveDone();
    unset($data['commonMoveDone']);

    unset($data['privateState']);

    //unlocked
    $data['ua'] = $this->countUnLockedActions();
    //locked
    $data['la'] = $this->countLockedActions();

    return $data;
  }
  
  public function getNbCommonActionsDone()
  {
    return PGlobals::getNbCommonActionsDone($this->getId());
  }
  public function setNbCommonActionsDone($nb)
  {
    return PGlobals::setNbCommonActionsDone($this->getId(),$nb);
  }
  public function incNbCommonActionsDone($nb)
  {
    return PGlobals::incNbCommonActionsDone($this->getId(),$nb);
  }

  public function getNbPersonalActionsDone()
  {
    return PGlobals::getNbPersonalActionsDone($this->getId());
  }
  public function setNbPersonalActionsDone($nb)
  {
    return PGlobals::setNbPersonalActionsDone($this->getId(),$nb);
  }
  public function incNbPersonalActionsDone($nb)
  {
    return PGlobals::incNbPersonalActionsDone($this->getId(),$nb);
  }
  
  public function isCommonMoveDone()
  {
    return PGlobals::isCommonMoveDone($this->getId());
  }
  public function setCommonMoveDone($val)
  {
    return PGlobals::setCommonMoveDone($this->getId(),$val);
  }

  public function getLastTurn()
  {
    return PGlobals::getLastTurn($this->getId());
  }
  public function setLastTurn($turn)
  {
    return PGlobals::setLastTurn($this->getId(),$turn);
  }
  public function getSelection()
  {
    return PGlobals::getSelection($this->getId());
  }
  public function setSelection($selection)
  {
    return PGlobals::setSelection($this->getId(),$selection);
  }
  
  public function getMimicColorUsed()
  {
    return PGlobals::getMimicColorUsed($this->getId());
  }
  public function setMimicColorUsed($selection)
  {
    return PGlobals::setMimicColorUsed($this->getId(),$selection);
  }
  public function addMimicColorUsed($type)
  {
    $colorsUsed = PGlobals::getMimicColorUsed($this->getId());
    $colorsUsed[] = $type;
    PGlobals::setMimicColorUsed($this->getId(),$colorsUsed);
    return $colorsUsed;
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
    if($points == 0) return;
    $this->setScore( $this->getScore() + $points);
    Stats::inc( "score", $this->id, $points );
  }
  
  public function setTieBreakerPoints($points)
  {
    $this->setScoreAux($points);
  }
  public function addTieBreakerPoints($points)
  {
    if($points == 0) return;
    $this->incScoreAux($points);
  }

  /**
   * Sets player datas related to turn number $turnIndex
   * @param int $turnIndex
   */
  public function startTurn($turnIndex)
  {
    $this->setLastTurn($turnIndex);
    $this->setNbCommonActionsDone(0);
    $this->setNbPersonalActionsDone(0);
    $this->setCommonMoveDone(false);
    $this->setSelection([]);
    $this->giveExtraTime();
    $this->setMimicColorUsed([]);

    if(Globals::isModeCompetitive()) Notifications::startTurn($this,$turnIndex);
  }
  
  public function giveExtraTime(){
    Game::get()->giveExtraTime($this->getId());
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
  
  public function countUnLockedActions(){
    return PlayerActions::countActions($this->getId(), [ACTION_STATE_UNLOCKED_FOREVER, ACTION_STATE_UNLOCKED_FOR_ONCE_GAME,ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN]);
  }
  public function countLockedActions(){
    return PlayerActions::countActions($this->getId(), [ACTION_STATE_LOCKED,ACTION_STATE_LOCKED_FOR_TURN]);
  }
}
