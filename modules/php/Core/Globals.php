<?php

namespace STIG\Core;

use STIG\Core\Game;
use STIG\Managers\Schemas;
use STIG\Models\Schema;

/*
 * Globals
 */

class Globals extends \STIG\Helpers\DB_Manager
{
  protected static $initialized = false;
  protected static $variables = [
    'round' => 'int',
    'turn' => 'int',
    'firstPlayer' => 'int',

    //We manage the wind direction for the 10 first turns, then the wind will be saved in windDirection11 (no need to display them)
    'windDirection1' => 'str',
    'windDirection2' => 'str',
    'windDirection3' => 'str',
    'windDirection4' => 'str',
    'windDirection5' => 'str',
    'windDirection6' => 'str',
    'windDirection7' => 'str',
    'windDirection8' => 'str',
    'windDirection9' => 'str',
    'windDirection10' => 'str',
    'windDirection11' => 'str',

    // Game options
    
    //For games with 1 schema in the entire game :(not solo challenge)
    'optionSchema' => 'int',
  ];

  public static function getAllWindDir()
  {
    $winds=[];
    for($k=1;$k<=TURN_MAX +1;$k++){
      $getterName = "getWindDirection$k";
      $winds[$k] = self::$getterName();
    }
    return $winds;
  }

  public static function getWindDirName($windDir)
  {
    switch($windDir){
      case WIND_DIR_SOUTH:
        return Game::get()->translate("South");
      case WIND_DIR_NORTH:
        return Game::get()->translate("North");
      case WIND_DIR_EAST:
        return Game::get()->translate("East");
      case WIND_DIR_WEST:
        return Game::get()->translate("West");
      default: 
        return "";
    }
  }
  
  /**
   * @return Schema
   */
  public static function getSchema()
  {
    $optionSchema = Globals::getOptionSchema();
    return Schemas::getTypes()->filter( function ($schema) use ($optionSchema)  { 
        return $schema->type == $optionSchema; 
      })->first();
  }

  /*
   * Setup new game
   */
  public static function setupNewGame($players, $options)
  {
    self::setRound(0);
    self::setTurn(0);
    
    foreach($players as $pId => $player){
      if($player['player_table_order'] == 1){
        self::setFirstPlayer($pId);
        break;
      }
    }

    //TODO JSA MANAGE Schema other SubList
    self::setOptionSchema($options[OPTION_SCHEMA_V]);
  }
  
  /**
   * Setup new game round
   */
  public static function setupNewRound()
  {
    self::setTurn(0);
    self::incRound();
  }


  protected static $table = 'global_variables';
  protected static $primary = 'name';
  protected static function cast($row)
  {
    $val = json_decode(\stripslashes($row['value']), true);
    return self::$variables[$row['name']] == 'int' ? ((int) $val) : $val;
  }

  /*
   * Fetch all existings variables from DB
   */
  protected static $data = [];
  public static function fetch()
  {
    // Turn of LOG to avoid infinite loop (Globals::isLogging() calling itself for fetching)
    $tmp = self::$log;
    self::$log = false;

    foreach (self::DB()
        ->select(['value', 'name'])
        ->get(false)
      as $name => $variable) {
      if (\array_key_exists($name, self::$variables)) {
        self::$data[$name] = $variable;
      }
    }
    self::$initialized = true;
    self::$log = $tmp;
  }

  /*
   * Create and store a global variable declared in this file but not present in DB yet
   *  (only happens when adding globals while a game is running)
   */
  public static function create($name)
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
        'name' => $name,
        'value' => \json_encode($val),
      ],
      true
    );
    self::$data[$name] = $val;
  }

  /*
   * Magic method that intercept not defined static method and do the appropriate stuff
   */
  public static function __callStatic($method, $args)
  {
    if (!self::$initialized) {
      self::fetch();
    }

    if (preg_match('/^([gs]et|inc|is)([A-Z])(.*)$/', $method, $match)) {
      // Sanity check : does the name correspond to a declared variable ?
      $name = strtolower($match[2]) . $match[3];
      if (!\array_key_exists($name, self::$variables)) {
        throw new \InvalidArgumentException("Property {$name} doesn't exist");
      }

      // Create in DB if don't exist yet
      if (!\array_key_exists($name, self::$data)) {
        self::create($name);
      }

      if ($match[1] == 'get') {
        // Basic getters
        return self::$data[$name];
      } elseif ($match[1] == 'is') {
        // Boolean getter
        if (self::$variables[$name] != 'bool') {
          throw new \InvalidArgumentException("Property {$name} is not of type bool");
        }
        return (bool) self::$data[$name];
      } elseif ($match[1] == 'set') {
        // Setters in DB and update cache
        $value = $args[0];
        if (self::$variables[$name] == 'int') {
          $value = (int) $value;
        }
        if (self::$variables[$name] == 'bool') {
          $value = (bool) $value;
        }

        self::$data[$name] = $value;
        self::DB()->update(['value' => \addslashes(\json_encode($value))], $name);
        return $value;
      } elseif ($match[1] == 'inc') {
        if (self::$variables[$name] != 'int') {
          throw new \InvalidArgumentException("Trying to increase {$name} which is not an int");
        }

        $getter = 'get' . $match[2] . $match[3];
        $setter = 'set' . $match[2] . $match[3];
        return self::$setter(self::$getter() + (empty($args) ? 1 : $args[0]));
      }
    }
    return undefined;
  }
}
