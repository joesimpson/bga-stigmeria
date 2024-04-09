<?php

namespace STIG\Managers;

use STIG\Core\Game;
use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\Stats;

/*
 * Players manager : allows to easily access players ...
 *  a player is an instance of Player class
 */

class Players extends \STIG\Helpers\DB_Manager
{
  protected static $table = 'player';
  protected static $primary = 'player_id';
  protected static function cast($row)
  {
    return new \STIG\Models\Player($row);
  }

  public static function setupNewGame($players, $options)
  {
    // Create players
    $gameInfos = Game::get()->getGameinfos();
    $colors = $gameInfos['player_colors'];
    $query = self::DB()->multipleInsert(['player_id', 'player_color', 'player_canal', 'player_name', 'player_avatar']);

    $values = [];
    foreach ($players as $pId => $player) {
      $color = array_shift($colors);
      $values[] = [$pId, $color, $player['player_canal'], $player['player_name'], $player['player_avatar']];
    }
    $query->values($values);

    Game::get()->reattributeColorsBasedOnPreferences($players, $gameInfos['player_colors']);
    Game::get()->reloadPlayersBasicInfos();

  }

  /**
   * @return Collection Players
   */
  public static function setupNewRound()
  {
    $players = Players::getAll();
    foreach($players as $player){
      $player->setJokerUsed(false);
    }
    return $players;
  }

  /**
   * @param Collection $players Players
   * @param int $turn
   */
  public static function setupNewTurn($players,$turn)
  {
    Game::get()->trace("setupNewTurn($turn)");
    if(!Globals::isModeCompetitive()) return;

    $players = $players->filter(function ($player) { 
      return $player->getZombie() ==0 && $player->getEliminated() == 0;
    });

    //First player calculation is for competitive games 
    $maxRecruits = Tokens::getPlayerIdsWithMaxRecruit($players); 
    Game::get()->trace("setupNewTurn() : maxRecruits =".json_encode($maxRecruits));
    $tiedPlayers = $maxRecruits['pId'];
    if(count( $tiedPlayers) > 1){
      //All these players need a tie breaker decision : check yellow tokens
      $players = $players->filter(function ($p) use ($tiedPlayers){ 
        return in_array($p->id,$tiedPlayers); 
      });
      $maxYellowRecruits = Tokens::getPlayerIdsWithMaxRecruit($players,[TOKEN_STIG_YELLOW]);
      Game::get()->trace("setupNewTurn() : maxYellowRecruits =".json_encode($maxYellowRecruits));
      $tiedPlayers = $maxYellowRecruits['pId']; 
      if(count( $tiedPlayers) > 1){
        //All these players need a tie breaker decision : automatic draw tokens in each deck until difference
        $firstPlayer = Tokens::drawUntilYellow($tiedPlayers);
        self::updateFirstPlayer($turn,$players[$firstPlayer],3);
      }
      else if(count( $tiedPlayers) == 1) {
        $firstPlayer = $players->filter(function ($p) use ($tiedPlayers){ 
            return in_array($p->id,$tiedPlayers); 
          })->first();
        self::updateFirstPlayer($turn,$firstPlayer,2);
      }
    }
    else if(count( $tiedPlayers) == 1){
      $firstPlayer = $players->filter(function ($p) use ($tiedPlayers){ 
          return in_array($p->id,$tiedPlayers); 
        })->first();
      self::updateFirstPlayer($turn,$firstPlayer,1);
    }
    
  }

  public static function getActiveId()
  {
    return Game::get()->getActivePlayerId();
  }

  public static function getCurrentId($bReturnNullIfNotLogged = false)
  {
    return (int) Game::get()->getCurrentPId($bReturnNullIfNotLogged);
  }

  public static function getAll()
  {
    return self::DB()->get(false);
  }

  /*
   * get : returns the Player object for the given player ID
   */
  public static function get($pId = null)
  {
    $pId = $pId ?: self::getActiveId();
    return self::DB()
      ->where($pId)
      ->getSingle();
  }

  public static function getActive()
  {
    return self::get();
  }

  public static function getCurrent()
  {
    return self::get(self::getCurrentId());
  }

  public static function getNextId($player = null)
  {
    $player = $player ?? Players::getCurrent();
    $pId = is_int($player) ? $player : $player->getId();
    $table = Game::get()->getNextPlayerTable();
    return $table[$pId];
  }
  
  /**
   * @param int $player_id
   * @param int $turn current turn
   * @return Player
   */
  public static function getNextInactivePlayerInTurn($player_id, $turn)
  {
    $nextPlayer_id = Players::getNextId($player_id);
    $nextPlayer = Players::get($nextPlayer_id);
    if(isset($nextPlayer) && !$nextPlayer->isMultiactive() && $nextPlayer->getLastTurn()< $turn
      && $nextPlayer->getZombie() != 1 && $nextPlayer->getEliminated() == 0
    ){
      //CHECK nextPlayer not active / not already played this turn/ not zombie
      return $nextPlayer;
    }
    return null;
  }
  
  /**
   * @param int $player_id
   * @return Player
   */
  public static function getNextPlayerNotElimininated($player_id)
  {
    $nextPlayer_id = Players::getNextId($player_id);
    $nextPlayer = Players::get($nextPlayer_id);
    if(isset($nextPlayer) 
      && $nextPlayer->getZombie() != 1 && $nextPlayer->getEliminated() == 0
    ){
      return $nextPlayer;
    }
    return self::getNextPlayerNotElimininated($nextPlayer_id);
  }


  /*
   * Return the number of players
   */
  public static function count()
  {
    return self::DB()->count();
  }

  /*
   * getUiData : get all ui data of all players
   */
  public static function getUiData($pId)
  {
    return self::getAll()
      ->map(function ($player) use ($pId) {
        return $player->getUiData($pId);
      })
      ->toAssoc();
  }

  /**
   * @return int $playerId of last player still in game (if only one), else null
   */
  public static function getRemainingPlayer()
  {
    $remaining = self::DB()
      ->where('player_zombie', 0)
      ->where('player_eliminated', 0)
      ->get();
    if(count($remaining) == 1) return $remaining->first()->getId();
    else return null;
  }
  /**
   * Get current turn order according to first player variable
   */
  public static function getTurnOrder($firstPlayer = null)
  {
    $firstPlayer = $firstPlayer ?? Globals::getFirstPlayer();
    $order = [];
    $p = $firstPlayer;
    do {
      $order[] = $p;
      $p = self::getNextId($p);
    } while ($p != $firstPlayer);
    return $order;
  }

  /**
   * This allow to change active player
   */
  public static function changeActive($pId)
  {
    Game::get()->gamestate->changeActivePlayer($pId);
  }

  /**
   * Sets player datas related to turn number $turn
   * @param array $player_ids
   * @param int $turn
   */
  public static function startTurn($player_ids,$turn)
  {
    foreach($player_ids as $player_id){
      $player = self::get($player_id);
      $player->startTurn($turn);
    }
  }
  
  /**
   * @param int $turn
   * @param Player $player
   * @param int $subcase used to choose player
   */
  public static function updateFirstPlayer($turn,$player,$subcase){
    if(!isset($player)) return;
    Globals::setFirstPlayer($player->id);
    if(!Globals::isModeCompetitive()) return;
    Notifications::updateFirstPlayer($turn,$player,$subcase);
  }
}
