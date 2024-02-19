<?php

namespace STIG\Models;

use STIG\Core\Game;
use STIG\Core\Globals;
use STIG\Managers\PlayerActions;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;

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
   * @return bool TRUE when there are enough actions to play this action
   */
  public function canBePlayed($remainingActions){
    if($this->getState() == ACTION_STATE_LOCKED ) return false;
    if($this->getState() == ACTION_STATE_LOCKED_FOR_TURN ) return false;
    if($remainingActions < $this->getCost()){
      return false;
    }
    return true;
  }
  
  /**
   * @param int $deckSize
   * @return bool TRUE when there is enough tokens at the right place to play this action
   */
  public function canBePlayedWithCurrentBoard($deckSize){
    $playerId = $this->getPId();
    switch($this->getType()){
      case ACTION_TYPE_MIXING:
        if(count(Game::get()->listMixableTokens($playerId)) == 0) return false;
        break;
      case ACTION_TYPE_COMBINATION:
        if(count(Game::get()->listTokensIdsForCombination($playerId)) == 0) return false;
        break;
      case ACTION_TYPE_FULGURANCE: 
        if($deckSize < FULGURANCE_NB_TOKENS ) return false;
        if(count(Game::get()->listSpotsForFulgurance($playerId)) == 0) return false;
        break;
      case ACTION_TYPE_CHOREOGRAPHY:
        $turn = Globals::getTurn();
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        if($turn < 1 + CHOREOGRAPHY_NB_TURNS_BEFORE || count(Game::get()->listPossibleChoreographyMovesOnBoard($playerId,$boardTokens,[])) == 0) return false;
        break;
      case ACTION_TYPE_DIAGONAL:
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        if(count(Game::get()->listPossibleDiagonalMovesOnBoard($playerId,$boardTokens)) == 0) return false;
        break;
      case ACTION_TYPE_SWAP:
        if(count(Game::get()->listSwapableTokens($playerId)) == 0) return false;
        break;
      case ACTION_TYPE_MOVE_FAST:
        $nMoves = Globals::getTurn();
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        if(count(Game::get()->listPossibleFastMovesOnBoard($playerId,$boardTokens,$nMoves)) == 0) return false;
        break;
      case ACTION_TYPE_WHITE:
        if(count(Game::get()->listWhiteableTokens($playerId)) == 0) return false;
        break;
      case ACTION_TYPE_BLACK:
        if(count(Game::get()->listBlackableTokens($playerId)) == 0) return false;
        break;
      case ACTION_TYPE_TWOBEATS:
        if(count(Game::get()->listSpotsForTwoBeats($playerId)) == 0) return false;
        break;
      case ACTION_TYPE_REST:
        if(Tokens::countOnPlayerBoard($playerId) == 0) return false;
        break;
    }

    return true;
  }
}
