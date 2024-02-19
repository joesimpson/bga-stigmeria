<?php

namespace STIG\Models;

use STIG\Managers\PlayerActions;

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
    $this->cost = PlayerActions::getCost($type);
    $this->difficulty = PlayerActions::getDifficult($type);
  }

  public function getUiData()
  {
    $data = parent::getUiData();
    return $data;
  }
}
