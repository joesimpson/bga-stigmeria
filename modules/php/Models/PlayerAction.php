<?php

namespace STIG\Models;

use STIG\Core\Game;
use STIG\Core\Globals;
use STIG\Core\PGlobals;
use STIG\Helpers\GridUtils;
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
    if(ACTION_STATE_UNLOCKED_ONE_SHOT == $this->getState()) {
      $this->cost = 0;
    } else {
      $this->cost = PlayerActions::getCost($type);
    }
  }

  public function getUiData()
  {
    $data = parent::getUiData();
    $data['d'] = $this->difficulty;
    $data['name'] = $this->getName();
    unset($data['difficulty']);
    unset($data['cost']);
    unset($data['location']);
    return $data;
  }

  /**
   * @param int $remainingActions
   * @return bool TRUE when there are enough actions to play this action
   */
  public function canBePlayed($remainingActions){
    if($this->getState() == ACTION_STATE_LOCKED ) return false;
    if($this->getState() == ACTION_STATE_LOCKED_FOR_TURN ) return false;
    if(ACTION_STATE_UNLOCKED_ONE_SHOT == $this->getState()) {
      return true;
    }
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
      case ACTION_TYPE_NSNK:
        if(count(Game::get()->listPossibleNSNK($playerId)) == 0) return false;
        break;
      case ACTION_TYPE_COPY:
        if(Tokens::countOnPlayerBoard($playerId,STIG_PRIMARY_COLORS) == 0) return false;
        break;
      case ACTION_TYPE_PREDICTION:
        break;
      case ACTION_TYPE_MIMICRY:
        if(Game::get()->listPossibleMimicry($playerId)['colors']->count() == 0) return false;
        break;
      case ACTION_TYPE_FOGDIE:
        if(Tokens::countOnPlayerBoard($playerId)>=GridUtils::getNbCells()) return false;
        break;
      case ACTION_TYPE_PILFERER:
        if(count(Game::get()->listPilfererTargets($playerId)) == 0) return false;
        break;
      case ACTION_TYPE_SOWER:
        //nothing special
        break;
      case ACTION_TYPE_CHARMER:
        //not playable through the same path-> after the turn
        return false;
      case ACTION_TYPE_JEALOUSY:
        //can only be played as first action !
        if( PGlobals::getNbActionsDone($playerId)>0 ) return false;
        if($deckSize < NB_TOKENS_MIN_JEALOUSY){
          return false;
        }
        if(count(Game::get()->listJealousyTargets($playerId)) == 0) return false;
        break;
    }

    return true;
  }

  public function setNewStateAfterUse(){
    if(ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN == $this->getState())
    {
      $this->setState(ACTION_STATE_LOCKED_FOR_TURN);
    } else if(ACTION_STATE_UNLOCKED_FOR_ONCE_GAME == $this->getState())
    {
      $this->setState(ACTION_STATE_LOCKED);
    }
  }

  /**
   * @return string name
   */
  public function getName()
  {
    return self::getTypeName($this->getType());
  }
  /**
   * @param int $type
   * @return string name
   */       
  static public function getTypeName($type)
  {
        switch($type){
            case ACTION_TYPE_MIXING:
              return clienttranslate("Mixing");
            case ACTION_TYPE_COMBINATION:
              return clienttranslate("Combination");
            case ACTION_TYPE_FULGURANCE:
              return clienttranslate("Fulgurance");
            case ACTION_TYPE_CHOREOGRAPHY:
              return clienttranslate("Choreography");
            case ACTION_TYPE_DIAGONAL:
              return clienttranslate("Diagonal");
            case ACTION_TYPE_SWAP:
              return clienttranslate("Exchange");
            case ACTION_TYPE_MOVE_FAST:
              return clienttranslate("Fast Step");
            case ACTION_TYPE_WHITE:
              return clienttranslate("Half Note");
            case ACTION_TYPE_BLACK:
              return clienttranslate("Quarter Note");
            case ACTION_TYPE_TWOBEATS:
              return clienttranslate("Two Beats");
            case ACTION_TYPE_REST:
              return clienttranslate("Rest");
            case ACTION_TYPE_NSNK:
              return clienttranslate("No harm No foul");
            case ACTION_TYPE_COPY:
              return clienttranslate("Copy");
            case ACTION_TYPE_PREDICTION:
              return clienttranslate("Prediction");
            case ACTION_TYPE_MIMICRY:
              return clienttranslate("Mimicry");
            case ACTION_TYPE_FOGDIE:
              return clienttranslate("Fog Die");
            case ACTION_TYPE_PILFERER:
              return clienttranslate("Pilferer");
            case ACTION_TYPE_SOWER:
              return clienttranslate("Sower");
            case ACTION_TYPE_CHARMER:
              return clienttranslate("Charmer");
            case ACTION_TYPE_JEALOUSY:
              return clienttranslate("Jealousy");
            
            default:
              return "";
        }
    }

    /**
     * @return bool true if this action is played VERSUS an opponent
     */
    public function isVS()
    { 
      return in_array($this->getType(),ACTION_VS_TYPES);
    }
}
