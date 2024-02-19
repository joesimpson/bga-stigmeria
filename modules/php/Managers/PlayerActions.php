<?php

namespace STIG\Managers;

use STIG\Core\Game;
use STIG\Core\Globals;
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
    return self::getAll()->ui();
  }

  /**
   * @param int $type
   * @return int 
   */
  public static function getCost($type)
  {
    //TODO JSA SWITCH
    return 1;
  }
  
  /**
   * @param int $type
   * @return int 
   */
  public static function getDifficulty($type)
  {
    //TODO JSA SWITCH
    return 2;
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
            'state' => $actionType['state'],
            'nbr' => 1,
          ];
        }
      }
    }

    self::create($actions, ACTION_LOCATION_PLAYER_BOARD);
  }
  
  /**
   * @param Collection $players Players
   * @param int $turn
   */
  public static function setupNewTurn($players,$turn)
  {
    self::updateAllState(ACTION_STATE_LOCKED_FOR_TURN,ACTION_STATE_UNLOCKED_FOR_ONCE_PER_TURN);
  }
  /**
   * @param PlayerAction $action
  */
  public static function createAction($action)
  { 
    return self::singleCreate($action);
  }
  /**
   * @param int $playerId
  * @return int nb of actions on player board
  */
  public static function countActions($playerId)
  { 
    return self::DB()
      ->wherePlayer($playerId)
      ->count();
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
