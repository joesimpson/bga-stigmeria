<?php

namespace STIG\Managers;

use STIG\Core\Game;
use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Models\PlayerAction;

/* 
Class to manage all the PlayerAction for this game 
*/
class PlayerActions extends \STIG\Helpers\Pieces
{
  protected static $table = 'player_action';
  protected static $prefix = 'action_';
  protected static $autoIncrement = true;
  protected static $autoremovePrefix = false;
  protected static $customFields = ['type', 'player_id'];

  protected static function cast($row)
  {
    $data = [];
    return new PlayerAction($row, $data);
  }

  public static function getUiData()
  {
    $allGroupBy = [];
    $all = self::getAll();
    foreach($all as $action){
      $allGroupBy[$action->getPId()][] = $action->getUiData();
    }
    return $allGroupBy;
  }

  /**
   * @param int $type
   * @return int cost
   */
  public static function getCost($type)
  {
    switch($type){
      
      case ACTION_TYPE_DIAGONAL:
        //this action doesn t cost double for Inspir
        return 1;
      default:
        $modifier = PlayerActions::getGetActionCostModifier();
    }
    //DEFAULT 1
    return $modifier*1;
  }
  
  /**
   * @param int $type
   * @return int 
   */
  public static function getDifficulty($type)
  {
    
    switch($type){
      
      case ACTION_TYPE_WHITE:
      case ACTION_TYPE_SWAP:
      case ACTION_TYPE_FULGURANCE:
      case ACTION_TYPE_COPY:
        return 2;
      case ACTION_TYPE_CHOREOGRAPHY:
      case ACTION_TYPE_MOVE_FAST:
      case ACTION_TYPE_PREDICTION:
      case ACTION_TYPE_REST:
        return 3;
      default:
        return 1;
    }
    
  }
  
  /**
   * @return int
   */
  public static function getGetActionCostModifier()
  {
    $multiplier = 1;
    $flowerType = Schemas::getCurrentSchema()->type;
    if(!Globals::isModeCompetitive() && $flowerType == OPTION_FLOWER_INSPIRACTRICE){
        $multiplier = ACTION_COST_MODIFIER_INSPIRACTRICE;
    }
    //For competitive modes it is more complex
    return $multiplier;
  }

  public static function setupNewGame($players, $options)
  {
    
  }
  
  /**
   * @param Collection $players Player
   * @param Schema $schema 
   */
  public static function setupNewRound($players,$schema)
  {
    //DELETE ALL
    self::DB()->delete()->run();

    $actions = [];
    $modeWithFixedActions = Globals::isModeNoCentralBoard();
    if($modeWithFixedActions){
      $fixedActions = $schema->getNormalPlayerActions();
      //Init schema actions
      foreach ($fixedActions as $actionType) {
        foreach ($players as $pId => $player) {
          $actions[] = [
            'type' => $actionType['type'],
            'player_id' => $pId,
            'state' => PlayerActions::getInitialState($actionType['type']),
            'nbr' => 1,
          ];
        }
      }
    }

    if(count($actions)>0){
      self::create($actions, ACTION_LOCATION_PLAYER_BOARD);
    }
  }


    /**
     * @param int $actionType
     * @return int $state
     */
    public static function getInitialState($actionType)
    {
        switch($actionType){
            case ACTION_TYPE_MIXING:
                return ACTION_STATE_UNLOCKED_FOREVER;
            case ACTION_TYPE_COMBINATION:
                return ACTION_STATE_UNLOCKED_FOREVER;
            case ACTION_TYPE_FULGURANCE:
                return ACTION_STATE_UNLOCKED_FOREVER;
            case ACTION_TYPE_CHOREOGRAPHY:
                return ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN;
            case ACTION_TYPE_DIAGONAL:
                return ACTION_STATE_UNLOCKED_FOREVER;
            case ACTION_TYPE_SWAP:
                return ACTION_STATE_UNLOCKED_FOREVER;
            case ACTION_TYPE_MOVE_FAST:
                return ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN;
            case ACTION_TYPE_WHITE:
                return ACTION_STATE_UNLOCKED_FOREVER;
            case ACTION_TYPE_BLACK:
                return ACTION_STATE_UNLOCKED_FOREVER;
            case ACTION_TYPE_TWOBEATS:
                return ACTION_STATE_UNLOCKED_FOREVER;
            case ACTION_TYPE_REST:
                return ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN;
            case ACTION_TYPE_NSNK:
                return ACTION_STATE_UNLOCKED_FOREVER;
            case ACTION_TYPE_COPY:
                return ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN;
            case ACTION_TYPE_PREDICTION:
                return ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN;
            
            default:
              return ACTION_STATE_UNLOCKED_FOREVER;
        }
    }
  
  /**
   * @param Collection $players Players
   * @param int $turn
   */
  public static function setupNewTurn($players,$turn)
  {
    $oldState = ACTION_STATE_LOCKED_FOR_TURN;
    $newState = ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN;
    $actionsToUpdate = self::DB()->where(static::$prefix . 'state', $oldState)->get()->getIds();
    self::updateAllState($oldState,$newState);
    Notifications::updateSp($actionsToUpdate,$newState);
  }
  /**
   * @param array $action datas
  */
  public static function createAction($action)
  { 
    return self::singleCreate($action);
  }
  /**
   * @param int $playerId
   * @param array $states (optional)
  * @return int nb of actions on player board
  */
  public static function countActions($playerId,$states = null)
  { 
    $query = self::DB()
      ->wherePlayer($playerId);
      if(isset($states)){
        $query->whereIn(self::$prefix.'state',$states );
      }
    return $query->count();
  } 
  
  /**
   * @param int $playerId
   * @param array $types (optional) filter on these types
  * @return Collection
  */
  public static function getPlayer($playerId, $types = null)
  { 
    $query = self::DB()
      ->wherePlayer($playerId);
    if(isset($types)) $query = $query->whereIn('type',$types);
    return $query->get();
  } 
  /**
   * Update all state of specified to another state
   * @param int $stateSrc
   * @param int $stateDest
   */
  public static function updateAllState($stateSrc, $stateDest)
  {
    $data = [];
    $data[static::$prefix . 'state'] = $stateDest;
    $query = self::DB()->update($data);
    $query->where(static::$prefix . 'state', $stateSrc);
    return $query->run();
  }
}
