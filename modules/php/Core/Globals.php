<?php

namespace STIG\Core;

use STIG\Core\Game;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\Collection;
use STIG\Helpers\Utils;
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
    'nbRounds' => 'int',
    'turn' => 'int',
    'firstPlayer' => 'int',
    'winnersIds' => 'obj',

    //Keep a trace of tokens charmed by players during the turn
    'charmedTokens' => 'obj',
    //Keep a trace of players who played or pass Charmed
    'afterTurnPlayers' => 'obj',

    //Last dice roll result
    'lastDie' => 'obj',

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
    'optionGameMode' => 'int',
    'optionFlowerType' => 'int',
    'optionDifficulty' => 'int',
    //For games with 1 schema in the entire game :(not solo challenge)
    'optionSchema' => 'int',
    'optionJokers' => 'int',

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
  
  /**
   * @param int $turn
   * @return string
   */
  public static function getWindDir($turn)
  {
    $maxWindDir = TURN_MAX +1;
    if($turn> $maxWindDir){
      $turn = $maxWindDir;
    }
    $getterName = "getWindDirection$turn";
    return self::$getterName();
  }
  /**
   * @param int $turn
   * @param string $value
   */
  public static function setWindDir($turn, $value)
  {
    $maxWindDir = TURN_MAX +1;
    if($turn> $maxWindDir){
      $turn = $maxWindDir;
    }
    $setterName = "setWindDirection$turn";
    return self::$setterName($value);
  }

  public static function getWindDirName($windDir)
  {
    switch($windDir){
      case WIND_DIR_SOUTH:
        return clienttranslate("South");
      case WIND_DIR_NORTH:
        return clienttranslate("North");
      case WIND_DIR_EAST:
        return clienttranslate("East");
      case WIND_DIR_WEST:
        return clienttranslate("West");
      default: 
        return "";
    }
  }
  public static function isModeNoCentralBoard()
  {
    $gameMode = Globals::getOptionGameMode();
    return ($gameMode == OPTION_MODE_NORMAL || $gameMode == OPTION_MODE_DISCOVERY);
  }
  public static function isModeDiscovery()
  {
    $gameMode = Globals::getOptionGameMode();
    return $gameMode == OPTION_MODE_DISCOVERY;
  }
  public static function isModeNormal()
  {
    $gameMode = Globals::getOptionGameMode();
    return $gameMode == OPTION_MODE_NORMAL;
  }
  public static function isModeCompetitive()
  {
    return !Globals::isModeNoCentralBoard();
  }
  public static function isModeCompetitiveNoLimit()
  {
    $gameMode = Globals::getOptionGameMode();
    return ($gameMode == OPTION_MODE_NOLIMIT);
  }
  public static function isModeNoTurnLimit()
  {
    $gameMode = Globals::getOptionGameMode();
    return ($gameMode == OPTION_MODE_NOLIMIT || $gameMode == OPTION_MODE_DISCOVERY);
  }
  /*
   * Setup new game
   */
  public static function setupNewGame($players, $options)
  {
    //TODO update nbRounds according to options when needed
    self::setNbRounds(1);
    self::setRound(0);
    self::setTurn(0);
    self::setLastDie([]);

    foreach($players as $pId => $player){
      if($player['player_table_order'] == 1){
        self::setFirstPlayer($pId);
        break;
      }
    }

    //              --------------------------------------------
    //GAME OPTIONS  --------------------------------------------
    //              --------------------------------------------
    $optionMode = $options[OPTION_MODE];
    self::setOptionGameMode($optionMode);

    $optionJoker = OPTION_JOKER_0;
    Utils::updateDataFromArray($options,OPTION_JOKER,$optionJoker);
    self::setOptionJokers($optionJoker);

    $flowerType = $options[OPTION_FLOWER];
    self::setOptionFlowerType($flowerType);

    //!!! We need to code the displayCondition from the json file because tournament UI doesn't use displayCondition
    $difficulty = OPTION_DIFFICULTY_RANDOM;
    if($flowerType !=OPTION_FLOWER_NO_LIMIT && $flowerType !=OPTION_FLOWER_RANDOM) Utils::updateDataFromArray($options,OPTION_DIFFICULTY,$difficulty);
    if($flowerType ==OPTION_FLOWER_NO_LIMIT) Utils::updateDataFromArray($options,OPTION_DIFFICULTY_NL,$difficulty);
    if($flowerType ==OPTION_FLOWER_RANDOM) Utils::updateDataFromArray($options,OPTION_DIFFICULTY_ALL,$difficulty);
    self::setOptionDifficulty($difficulty);

    $optionSchema = OPTION_SCHEMA_RANDOM;//DEFAULT RANDOM
    if($difficulty ==OPTION_DIFFICULTY_1 && $flowerType ==OPTION_FLOWER_VERTIGHAINEUSE) Utils::updateDataFromArray($options,OPTION_SCHEMA_V1,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_2 && $flowerType ==OPTION_FLOWER_VERTIGHAINEUSE) Utils::updateDataFromArray($options,OPTION_SCHEMA_V2,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_3 && $flowerType ==OPTION_FLOWER_VERTIGHAINEUSE) Utils::updateDataFromArray($options,OPTION_SCHEMA_V3,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_1 && $flowerType ==OPTION_FLOWER_MARONNE) Utils::updateDataFromArray($options,OPTION_SCHEMA_M1,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_2 && $flowerType ==OPTION_FLOWER_MARONNE) Utils::updateDataFromArray($options,OPTION_SCHEMA_M2,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_3 && $flowerType ==OPTION_FLOWER_MARONNE) Utils::updateDataFromArray($options,OPTION_SCHEMA_M3,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_1 && $flowerType ==OPTION_FLOWER_SIFFLOCHAMP) Utils::updateDataFromArray($options,OPTION_SCHEMA_S1,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_2 && $flowerType ==OPTION_FLOWER_SIFFLOCHAMP) Utils::updateDataFromArray($options,OPTION_SCHEMA_S2,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_3 && $flowerType ==OPTION_FLOWER_SIFFLOCHAMP) Utils::updateDataFromArray($options,OPTION_SCHEMA_S3,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_1 && $flowerType ==OPTION_FLOWER_DENTDINE) Utils::updateDataFromArray($options,OPTION_SCHEMA_D1,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_2 && $flowerType ==OPTION_FLOWER_DENTDINE) Utils::updateDataFromArray($options,OPTION_SCHEMA_D2,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_3 && $flowerType ==OPTION_FLOWER_DENTDINE) Utils::updateDataFromArray($options,OPTION_SCHEMA_D3,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_1 && $flowerType ==OPTION_FLOWER_INSPIRACTRICE) Utils::updateDataFromArray($options,OPTION_SCHEMA_I1,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_2 && $flowerType ==OPTION_FLOWER_INSPIRACTRICE) Utils::updateDataFromArray($options,OPTION_SCHEMA_I2,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_3 && $flowerType ==OPTION_FLOWER_INSPIRACTRICE) Utils::updateDataFromArray($options,OPTION_SCHEMA_I3,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_1 && $flowerType ==OPTION_FLOWER_COMPETITIVE) Utils::updateDataFromArray($options,OPTION_SCHEMA_C1,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_2 && $flowerType ==OPTION_FLOWER_COMPETITIVE) Utils::updateDataFromArray($options,OPTION_SCHEMA_C2,$optionSchema);
    if($difficulty ==OPTION_DIFFICULTY_3 && $flowerType ==OPTION_FLOWER_COMPETITIVE) Utils::updateDataFromArray($options,OPTION_SCHEMA_C3,$optionSchema);
    if($flowerType ==OPTION_FLOWER_NO_LIMIT) Utils::updateDataFromArray($options,OPTION_SCHEMA_NL,$optionSchema);
    
    $schemaTypes = Schemas::getTypes();
    $schemas = new Collection($schemaTypes);
    //PICK A RANDOM Schema in respect with every option
    // (Special warning if random/random/random, we don't want to pick an incompatible flower type and difficulty )
    if($optionSchema == OPTION_SCHEMA_RANDOM){
      //IF Schema is random, other options will influence this 
      //When others are known, let's filter existing schemas in model :
      $schemasIds = $schemas
        //REMOVE IMPOSSSIBLE SCHEMAS COMBINATIONS
        ->filter( function ($schema) use ($optionMode) {
          if (($schema->type == OPTION_FLOWER_COMPETITIVE && $optionMode == OPTION_MODE_NORMAL)
            ||($schema->type == OPTION_FLOWER_COMPETITIVE && $optionMode == OPTION_MODE_DISCOVERY) 
            ||($schema->type == OPTION_FLOWER_NO_LIMIT && $optionMode == OPTION_MODE_NORMAL)
            ||($schema->type == OPTION_FLOWER_NO_LIMIT && $optionMode == OPTION_MODE_DISCOVERY)
          ) return false;
          return true;
        })
        //KEEP SELECTED types/ difficulty
        ->filter( function ($schema) use ($flowerType,$difficulty) {
          return ($schema->type == $flowerType || $flowerType == OPTION_FLOWER_RANDOM)
              && ($schema->difficulty == $difficulty || $difficulty == OPTION_DIFFICULTY_RANDOM);
        })
        ->map(function ($schema) {
          return $schema->id;
        })->toArray();
      if(count($schemasIds) == 0) throw new UnexpectedException(1,"Missing random schema ($flowerType , $difficulty) !");
      $optionSchema = $schemasIds[array_rand($schemasIds, 1)];
    }
    else {
      //IF Schema is not random, it is precisely selected, and other options are not used
      if(!array_key_exists($optionSchema,$schemaTypes)) throw new UnexpectedException(1,"Missing schema $optionSchema !");
      /*
      $schema = $schemaTypes[$optionSchema];
      $difficulty = $schema->difficulty;
      $flowerType = $schema->type;
      */
    }
    self::setOptionSchema($optionSchema);

  }
  
  /**
   * Setup new game round
   */
  public static function setupNewRound()
  {
    self::setTurn(0);
    self::incRound();
    self::setLastDie(null);
    self::setWindDirection1( null );
    self::setWindDirection2( null );
    self::setWindDirection3( null );
    self::setWindDirection4( null );
    self::setWindDirection5( null );
    self::setWindDirection6( null );
    self::setWindDirection7( null );
    self::setWindDirection8( null );
    self::setWindDirection9( null );
    self::setWindDirection10(null);
    self::setWindDirection11(null);
  }

  /**
   * Setup new game turn
   */
  public static function setupNewTurn()
  {
    self::incTurn(1);
    self::setCharmedTokens([]);
    self::setAfterTurnPlayers([]);
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
