<?php

namespace STIG\Core;

use STIG\Core\Game;
/*
 * Player Globals
 */

class PGlobals extends \STIG\Helpers\DB_Manager
{
  protected static $initialized = false;
  protected static $variables = [
    'state' => 'obj', 
    'engineChoices' => 'int',

    'lastTurn' => 'int',
    //actions played by this player on central board (during this turn)
    'nbCommonActionsDone' => 'int',
    //actions played by this player on their board (during this turn)
    'nbPersonalActionsDone' => 'int',
    //This player used the common action move on central board (during this turn) false/true
    'commonMoveDone' => 'bool',
    //Selected tokens according to current private state
    'selection' => 'obj',

    'nbSpActions' => 'int',
    'nbSpActionsMax' => 'int',
    
    //Last landed token id
    'lastLanded' => 'int',
    //list of colors used for Mimicry in turn
    'mimicColorUsed' => 'obj',
    
    'eliminated' => 'bool',
  ];

  protected static $table = 'pglobal_variables';
  protected static $primary = 'name'; // Name is actually name-pId
  protected static function cast($row)
  {
    list($name, $pId) = explode('-', $row['name']);
    if (!isset(self::$variables[$name])) {
      return null;
    }

    $val = json_decode(\stripslashes($row['value']), true);
    return self::$variables[$name] == 'int' ? ((int) $val) : $val;
  }

  /*
   * Fetch all existings variables from DB
   */
  protected static $datas = [];
  public static function fetch()
  {
    // Turn of LOG to avoid infinite loop (Globals::isLogging() calling itself for fetching)
    $tmp = self::$log;
    self::$log = false;

    foreach (self::DB()
        ->select(['value', 'name'])
        ->get(false)
      as $uid => $variable) {
      list($name, $pId) = explode('-', $uid);

      if (\array_key_exists($name, self::$variables)) {
        self::$datas[$pId][$name] = $variable;
      }
    }
    self::$initialized = true;
    self::$log = $tmp;
  }

  /*
   * Create and store a global variable declared in this file but not present in DB yet
   *  (only happens when adding globals while a game is running)
   */
  public static function create($name, $pId)
  {
    if (!\array_key_exists($name, self::$variables)) {
      return;
    }

    $default = [
      'int' => 0,
      'obj' => [],
      'bool' => false,
      'str' => '',
    ];
    $val = $default[self::$variables[$name]];
    self::DB()->insert(
      [
        'name' => $name . '-' . $pId,
        'value' => \json_encode($val),
      ],
      true
    );
    self::$datas[$pId][$name] = $val;
  }

  /**
   * get all the variables of a given name
   */
  public static function getAll($name)
  {
    if (!self::$initialized) {
      self::fetch();
    }

    $t = [];
    foreach (self::$datas as $pId => $data) {
      if (isset($data[$name])) {
        $t[$pId] = $data[$name];
      }
    }
    return $t;
  }

  /*
   * Magic method that intercept not defined static method and do the appropriate stuff
   */
  public static function __callStatic($method, $args)
  {
    if (!self::$initialized) {
      self::fetch();
    }

    // First argument is always pId
    $pId = $args[0];

    if (preg_match('/^([gs]et|inc|is)([A-Z])(.*)$/', $method, $match)) {
      // Sanity check : does the name correspond to a declared variable ?
      $name = mb_strtolower($match[2]) . $match[3];
      if (!\array_key_exists($name, self::$variables)) {
        throw new \InvalidArgumentException("Property {$name} doesn't exist");
      }

      // Create in DB if don't exist yet
      if (!\array_key_exists($name, self::$datas[$pId] ?? [])) {
        self::create($name, $pId);
      }

      if ($match[1] == 'get') {
        // Basic getters
        return self::$datas[$pId][$name];
      } elseif ($match[1] == 'is') {
        // Boolean getter
        if (self::$variables[$name] != 'bool') {
          throw new \InvalidArgumentException("Property {$name} is not of type bool");
        }
        return (bool) self::$datas[$pId][$name];
      } elseif ($match[1] == 'set') {
        // Setters in DB and update cache
        $value = $args[1];
        if (self::$variables[$name] == 'int') {
          $value = (int) $value;
        }
        if (self::$variables[$name] == 'bool') {
          $value = (bool) $value;
        }

        self::$datas[$pId][$name] = $value;
        //if (in_array($name, ['state', 'engine', 'engineChoices'])) {
          self::DB()->update(['value' => \addslashes(\json_encode($value))], $name . '-' . $pId);
        //}

        return $value;
      } elseif ($match[1] == 'inc') {
        if (self::$variables[$name] != 'int') {
          throw new \InvalidArgumentException("Trying to increase {$name} which is not an int");
        }

        $getter = 'get' . $match[2] . $match[3];
        $setter = 'set' . $match[2] . $match[3];
        return self::$setter($args[0], self::$getter($args[0]) + ($args[1] ?? 1));
      }
    }
    throw new \feException(print_r(debug_print_backtrace()));
    return undefined;
  }

  /*
   * Setup new game
   */
  public static function setupNewGame($players, $options)
  {
    foreach($players as $playerId => $player){
      self::setLastTurn($playerId,0);
      self::setNbCommonActionsDone($playerId,0);
      self::setNbPersonalActionsDone($playerId,0);
      self::setCommonMoveDone($playerId,0);
      self::setSelection($playerId,[]);
      self::setNbSpActions($playerId,0);
      self::setNbSpActionsMax($playerId,0);
      self::setLastLanded($playerId,null);
      self::setMimicColorUsed($playerId,[]);
      self::setEliminated($playerId,false);
      //the first state to be activated:
      self::setState($playerId,ST_TURN_COMMON_BOARD);
      self::setEngineChoices($playerId, 0);
    }
  }
}
