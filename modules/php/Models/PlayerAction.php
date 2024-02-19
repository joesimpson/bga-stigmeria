<?php

namespace STIG\Models;

use STIG\Core\Globals;
use STIG\Managers\PlayerActions;
use STIG\Managers\Schemas;

/*
 * PlayerAction: all utility functions concerning an action unlocked or not, played or not
 */

class PlayerAction extends \STIG\Helpers\DB_Model
{
  protected $table = 'player_action';
  protected $primary = 'action_id';
  protected $attributes = [
    'id' => ['action_id', 'int'],
    'state' => ['action_state', 'int'],
    'location' => 'action_location',
    'pId' => ['player_id', 'int'],
    'type' => ['type', 'int'],
  ];
  
  protected $staticAttributes = [
    //TODO JSA check useful attributes
    //Cost in actions to play the action
    'cost' => ['cost', 'int'],
    //Min difficulty to play the action
    'difficulty' => ['difficulty', 'int'],
  ];

  public function __construct($row, $datas)
  {
    parent::__construct($row);
    foreach ($datas as $attribute => $value) {
      $this->$attribute = $value;
    }
    $type = $this->type;
    $this->difficulty = PlayerActions::getDifficulty($type);
    $this->cost = PlayerActions::getCost($type) * PlayerActions::getGetActionCostModifier();
  }

  public function getUiData()
  {
    $data = parent::getUiData();
    return $data;
  }

  /**
   * @param int $remainingActions
   * @param int $deckSize
   */
  public function canBePlayed($remainingActions,$deckSize){
    if($this->getState() == ACTION_STATE_LOCKED ) return false;
    if($this->getState() == ACTION_STATE_LOCKED_FOR_TURN ) return false;
    if($remainingActions < $this->getCost()){
      return false;
    }
    if(ACTION_TYPE_FULGURANCE == $this->getType() && $deckSize < FULGURANCE_NB_TOKENS ){
      return false;
    }
    return true;
  }
}
