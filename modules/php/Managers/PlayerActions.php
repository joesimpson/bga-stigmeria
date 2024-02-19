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
  public static function getDifficult($type)
  {
    //TODO JSA SWITCH
    return 2;
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
}
