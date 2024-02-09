<?php

namespace STIG\Core;

use STIG\Managers\Players;
use STIG\Helpers\Utils;
use STIG\Core\Globals;
use STIG\Models\Player;
use STIG\Models\Schema;
use STIG\Models\StigmerianToken;

class Notifications
{ 

  /**
   * @param int $round
   * @param Schema $schema
   * @param Collection $tokens
   */
  public static function newRound($round,$schema,$tokens){
    self::notifyAll('newRound',clienttranslate('Starting round #${n} with schema #${s}'),[ 
        'n' => $round,
        's' => $schema->id,
        //No need to send all the schema datas if we send it at start
        //'schema' => $schema->getUiData(),
        'tokens' => $tokens,
      ],
    );
  }
  /**
   * @param array $winds
   */
  public static function newWinds($winds){
    self::notifyAll('newWinds','',[
      'winds' => $winds,
    ],);
  }
  /**
   * @param int $turn
   */
  public static function newTurn($turn){
    self::notifyAll('newTurn',clienttranslate('Starting turn #${n}'),[ 
        'n' => $turn,
      ],
    );
  }

  /**
   * @param Player $player1
   * @param Player $player2
   */
  public static function letNextPlay($player1,$player2){
    self::notifyAll('letNextPlay',clienttranslate('${player_name} lets ${player_name2} start to play for this turn'),[ 
        'player' => $player1,
        'player2' => $player2,
      ],
    );
  }
  /**
   * @param Player $player
   * @param int $turn
   */
  public static function startTurn($player,$turn){
    self::notifyAll('startTurn',clienttranslate('${player_name} starts turn #${n}'),[ 
        'player' => $player,
        'n' => $turn,
      ],
    );
  }
  /**
   * @param Player $player
   */
  public static function endTurn($player){
    self::notifyAll('endTurn',clienttranslate('${player_name} ends his turn'),[ 
        'player' => $player,
      ],
    );
  }

  /**
   * Update number of actions
   * @param Player $player
   *///TODO JSA CHECK IF USELESS
  public static function useActions($player){
    self::notifyAll('useActions','',[ 
        'player' => $player,
        'nbCommonActionsDone' => $player->getNbCommonActionsDone(),
        'nbPersonalActionsDone' => $player->getNbPersonalActionsDone(),
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function drawToken($player, $token, $actionCost){
    self::notifyAll('drawToken',clienttranslate('${player_name} draws a new stigmerian to the recruitment zone (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'n' => $actionCost,
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function moveToCentralBoard($player, $token, $actionCost){
    self::notifyAll('moveToCentralBoard',clienttranslate('${player_name} places a new stigmerian on StigmaReine at ${L} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
        'n' => $actionCost,
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param string $from Coordinate name
   * @param string $to Coordinate name
   * @param int $actionCost
   */
  public static function moveOnCentralBoard($player, $token,$from,$to, $actionCost){
    self::notifyAll('moveOnCentralBoard',clienttranslate('${player_name} moves a stigmerian on StigmaReine from ${A} to ${B} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'A' => $from,
        'B' => $to,
        'n' => $actionCost,
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param int $actionCost
   */
  public static function moveToPlayerBoard($player, $token, $actionCost){
    self::notifyAll('moveToPlayerBoard',clienttranslate('${player_name} places a new stigmerian at ${L} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'L' => $token->getCoordName(),
        'n' => $actionCost,
      ],
    );
  }
  /**
   * @param Player $player
   * @param StigmerianToken $token
   * @param string $from Coordinate name
   * @param string $to Coordinate name
   * @param int $actionCost
   */
  public static function moveOnPlayerBoard($player, $token,$from,$to, $actionCost){
    self::notifyAll('moveOnPlayerBoard',clienttranslate('${player_name} moves a stigmerian from ${A} to ${B} (cost : ${n} actions)'),[ 
        'player' => $player,
        'token' => $token->getUiData(),
        'A' => $from,
        'B' => $to,
        'n' => $actionCost,
      ],
    );
  }

  /**
   * @param string $windDir
   * @param Collection $boardTokens StigmerianToken 
   * @param Player $player player board or null if central
   */
  public static function windBlows($windDir,$boardTokens,$player = null){
    $tokens = $boardTokens->ui();
    $nbTokens = count($tokens);
    
    if(isset($player)){
      $message = clienttranslate('Wind blows to ${dir} on ${player_name} flower and move ${n} tokens');
    } else {//CENTRAL
      $message = clienttranslate('Wind blows to ${dir} on StigmaReine flower and move ${n} tokens');
    }

    self::notifyAll('windBlows',$message,[ 
        'i18n'=>['dir'],
        'player' => $player,
        'tokens' => $tokens,
        'windDir' => $windDir,
        'dir' => Globals::getWindDirName($windDir),
        'n' => $nbTokens,
      ],
    );
  }
  /*************************
   **** GENERIC METHODS ****
   *************************/
  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data);
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($player, $name, $msg, $data)
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::updateArgs($data);
    Game::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function message($txt, $args = [])
  {
    self::notifyAll('message', $txt, $args);
  }

  public static function messageTo($player, $txt, $args = [])
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::notify($pId, 'message', $txt, $args);
  }

  /**
   *  Empty notif to send after an action, to let framework works & refresh ui
   * (Usually not needed if we send another notif or if we change state of a player)
   * */
  public static function emptyNotif(){
    self::notifyAll('emptyNotif','',[],);
  }
  /*********************
   **** UPDATE ARGS ****
   *********************/

  /*
   * Automatically adds some standard field about player and/or card
   */
  protected static function updateArgs(&$data)
  {
    if (isset($data['player'])) {
      $data['player_name'] = $data['player']->getName();
      $data['player_id'] = $data['player']->getId();
      unset($data['player']);
    }

    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      unset($data['player2']);
    }
  }
}
