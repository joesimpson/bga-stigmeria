<?php
namespace STIG;
use STIG\Core\Globals;
use STIG\Core\Game;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Helpers\Collection;
use STIG\Helpers\GridUtils;
use STIG\Helpers\QueryBuilder;
use STIG\Helpers\Utils;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;

trait DebugTrait
{
  /**
   * Function to call to regenerate JSON from PHP (when json file is removed)
   */
  function debug_JSON(){
    include dirname(__FILE__) . '/gameoptions.inc.php';

    $customOptions = $this->getTableOptions();
    $customOptions = $game_options;//READ from module file
    //AUTO REMOVE BGA OPTIONS:
    foreach($customOptions as $key => $option){
      if($key <100 || $key>=200){
        unset($customOptions[$key]);
      }
      else {
        //there is a strange boolTrueId =null  and "type": "choice"
        unset($customOptions[$key]['boolTrueId']);
        unset($customOptions[$key]['type']);
      }
    }
    $json = json_encode($customOptions, JSON_PRETTY_PRINT);
    //Formatting options as json -> copy the DOM of this log : \n
    Notifications::message("$json",['json' => $json]);
    
    $customOptions = $this->getTablePreferences();
    $customOptions = $game_preferences;
    //AUTO REMOVE BGA OPTIONS:
    foreach($customOptions as $key => $option){
      if($key <100 || $key>=200){
        unset($customOptions[$key]);
      }
    }
    $json = json_encode($customOptions, JSON_PRETTY_PRINT);
    //Formatting prefs as json -> copy the DOM of this log : \n
    Notifications::message("$json",['json' => $json]);
  }

  ///*
  // * loadBug: in studio, type loadBug(20762) into the table chat to load a bug report from production
  // * client side JavaScript will fetch each URL below in sequence, then refresh the page
  // */
  //public function loadBug($reportId)
  //{
  //  $db = explode('_', $this->getUniqueValueFromDB("SELECT SUBSTRING_INDEX(DATABASE(), '_', -2)"));
  //  $game = $db[0];
  //  $tableId = $db[1];
  //  $this->notifyAllPlayers('loadBug', "Trying to load <a href='https://boardgamearena.com/bug?id=$reportId' target='_blank'>bug report $reportId</a>", [
  //    'urls' => [
  //      // Emulates "load bug report" in control panel
  //      "https://studio.boardgamearena.com/admin/studio/getSavedGameStateFromProduction.html?game=$game&report_id=$reportId&table_id=$tableId",
  //      
  //      // Emulates "load 1" at this table
  //      "https://studio.boardgamearena.com/table/table/loadSaveState.html?table=$tableId&state=1",
  //      
  //      // Calls the function below to update SQL
  //      "https://studio.boardgamearena.com/2/$game/$game/loadBugSQL.html?table=$tableId&report_id=$reportId",
  //      
  //      // Emulates "clear PHP cache" in control panel
  //      // Needed at the end because BGA is caching player info
  //      "https://studio.boardgamearena.com/admin/studio/clearGameserverPhpCache.html?game=$game",
  //    ]
  //  ]);
  //}
  ///*
  // * loadBugSQL: in studio, this is one of the URLs triggered by loadBug() above
  // */
  //public function loadBugSQL($reportId)
  //{
  //  $studioPlayer = $this->getCurrentPlayerId();
  //  $players = $this->getObjectListFromDb("SELECT player_id FROM player", true);
  //
  //  // Change for your game
  //  // We are setting the current state to match the start of a player's turn if it's already game over
  //  $state = ST_FIRST_TOKEN;
  //  $sql = [
  //    "UPDATE global SET global_value=$state WHERE global_id=1 AND global_value=99"
  //  ];
  //  foreach ($players as $pId) {
  //
  //    // All games can keep this SQL
  //    $sql[] = "UPDATE player SET player_id=$studioPlayer WHERE player_id=$pId";
  //    $sql[] = "UPDATE global SET global_value=$studioPlayer WHERE global_value=$pId";
  //    $sql[] = "UPDATE stats SET stats_player_id=$studioPlayer WHERE stats_player_id=$pId";
  //
  //    // Add game-specific SQL update the tables for your game
  //    $sql[] = "UPDATE token SET player_id=$studioPlayer WHERE player_id = $pId";
  //    $sql[] = "UPDATE player_action SET player_id=$studioPlayer WHERE player_id = $pId";
  //    $sql[] = "UPDATE global_variables SET `value` = REPLACE(`value`,'$pId','$studioPlayer')";
  //    $sql[] = "UPDATE user_preferences SET player_id=$studioPlayer WHERE player_id = $pId";
  //    $sql[] = "UPDATE `log` SET player_id=$studioPlayer WHERE player_id = $pId";
  //
  //    // This could be improved, it assumes you had sequential studio accounts before loading
  //    // e.g., quietmint0, quietmint1, quietmint2, etc. are at the table
  //    $studioPlayer++;
  //  }
  //  $msg = "<b>Loaded <a href='https://boardgamearena.com/bug?id=$reportId' target='_blank'>bug report $reportId</a></b><hr><ul><li>" . implode(';</li><li>', $sql) . ';</li></ul>';
  //  $this->warn($msg);
  //  $this->notifyAllPlayers('message', $msg, []);
  //
  //  foreach ($sql as $q) {
  //    $this->DbQuery($q);
  //  }
  //  $this->reloadPlayersBasicInfos();
  //  $this->gamestate->reloadState();
  //}
  /**
   * STUDIO : Get the database matching a bug report (when not empty)
   */
  public function loadBugReportSQL(int $reportId, array $studioPlayersIds): void {
    $this->trace("loadBugReportSQL($reportId, ".json_encode($studioPlayersIds));
    $players = $this->getObjectListFromDb('SELECT player_id FROM player', true);
  
    $sql = [];
    //This table is modified with boilerplate
    $sql[] = "ALTER TABLE `gamelog` ADD `cancel` TINYINT(1) NOT NULL DEFAULT 0;";

    // Change for your game
    // We are setting the current state to match the start of a player's turn if it's already game over
    $state = ST_FIRST_TOKEN;
    $sql[] = "UPDATE global SET global_value=$state WHERE global_id=1 AND global_value=99";
    foreach ($players as $index => $pId) {
      $studioPlayer = $studioPlayersIds[$index];
  
      // All games can keep this SQL
      $sql[] = "UPDATE player SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE global SET global_value=$studioPlayer WHERE global_value=$pId";
      $sql[] = "UPDATE stats SET stats_player_id=$studioPlayer WHERE stats_player_id=$pId";
  
      // Add game-specific SQL update the tables for your game
      $sql[] = "UPDATE token SET player_id=$studioPlayer WHERE player_id = $pId";
      $sql[] = "UPDATE token SET token_location='player_deck_$studioPlayer' WHERE token_location='player_deck_$pId'";
      $sql[] = "UPDATE player_action SET player_id=$studioPlayer WHERE player_id = $pId";
      $sql[] = "UPDATE global_variables SET `value` = REPLACE(`value`,'$pId','$studioPlayer')";
      
      //REPLACE Player Globals :
      $sql[] = "DELETE FROM pglobal_variables WHERE SUBSTRING_INDEX(`name`,'-',-1) = '$studioPlayer';";
      $sql[] = "UPDATE pglobal_variables SET `name` = CONCAT ( SUBSTRING_INDEX(`name`,'-',1),  '-', '$studioPlayer' ) WHERE SUBSTRING_INDEX(`name`,'-',-1) = '$pId';";

      $sql[] = "UPDATE user_preferences SET player_id=$studioPlayer WHERE player_id = $pId";
      $sql[] = "UPDATE `log` SET player_id=$studioPlayer WHERE player_id = $pId";
    }
  
    foreach ($sql as $q) {
      $this->DbQuery($q);
    }
  
    $this->reloadPlayersBasicInfos();
  }

  ////////////////////////////////////////////////////
  //Display a table of all schemas and in which mode it is playable
  function debug_playableSchemas()
  {
    $modes = [ 
      ['mode' => OPTION_MODE_DISCOVERY, 'name' => "Discovery",  ],
      ['mode' => OPTION_MODE_NORMAL,    'name' => "Normal", ],
      ['mode' => OPTION_MODE_COMPETITIVE, 'name'=> "Competitive", ],
      ['mode' => OPTION_MODE_NOLIMIT   ,  'name'=> "NoLimit", ],
      ['mode' => OPTION_MODE_SOLO_NOLIMIT, 'name'=> "SoloNoLimit", ],
    ];
    $schemas = Schemas::getTypes();

    $table = [];
    // Header line
    $firstRow = array( '' );
    foreach($modes as $mode){
      $modeType = $mode['mode'];
      $modeName = $mode['name'];

      //$table[] = ['str' => "Mode $modeName",'args' => [], 'type' => 'header'];
      $firstRow[] = array( 'str' => "$modeName",
        'args' => [],
        'type' => 'header'
      );
    }
    $table[] = $firstRow;
    foreach($schemas as $schema){
      //$tableRow = ['str' => "Schema $schema->id",'args' => [],];
      $tableRow = [];
      $tableRow[] = "Schema $schema->id";
      foreach($modes as $mode){
        $modeType = $mode['mode'];
        $modeName = $mode['name'];

        $playable = $schema->isPlayableWithMode($modeType);
        $tableRow[] = (!$playable) ? "NOO" : "y";
        //if($playable) Notifications::message("<li>Schema $schema->id is playable with mode $modeName</li>");
      }
      $table[] = $tableRow;

    }

    //BGA Display table window 
    $this->notifyAllPlayers( "tableWindow", '', [
      "id" => 'debugPlayableSchemas',
      "title" => "Playable Schemas",
      "header" => ['str' => '',
                  'args' => [  ],
                ],
      "table" => $table,
      "closing" =>  "Close",
    ]);

  } 
  ////////////////////////////////////////////////////
  
  function debug_StatsEx()
  {
    Stats::check_Existence();
  } 
  function debug_Elim()
  {
    $player = Players::getCurrent();
    //Notifications::windElimination($player,null,'TEST');
    $lastPlayer = Players::getRemainingPlayer();
    if($lastPlayer  == $player->id ){
      Notifications::message("debugElim getRemainingPlayer is current player ");
    } else {
      Notifications::message("debugElim getRemainingPlayer is $lastPlayer ");
    }

    Notifications::deckElimination($player);
    $nextP = Players::getNextInactivePlayerInTurn($player->id,Globals::getTurn());
    Notifications::message("debugElim nextP is $nextP ");
    $nextP2 = Players::getNextInactivePlayerInTurn(2373993,Globals::getTurn());
    Notifications::message("debugElim nextP is $nextP2 ");
    
    $nextP3 = Players::getNextInactivePlayerInTurn("2373993",Globals::getTurn());
    Notifications::message("debugElim nextP is $nextP3 ");
  }
  function debug_ForceState()
  {
    $this->gamestate->jumpToState( ST_NEXT_ROUND );
    //$this->gamestate->jumpToState( ST_NEXT_TURN );
  }
  function debug_GoToNextPlayer()
  {
    $this->gamestate->nextState( 'next' );
  }

  function debug_Tokens()
  {
    $players = Players::getAll();
    Tokens::DB()->delete()->run();
    Tokens::setupNewGame($players, []);
  }
  
  function debug_ShuffleTokens()
  {
    $player = Players::getCurrent();
    Tokens::shuffle(TOKEN_LOCATION_PLAYER_DECK.$player->id);
  }
  // Add many actions ! YEAH
  function debug_ManyActions()
  {
    $player = Players::getCurrent();
    $player->setNbPersonalActionsDone(-150);
    $player->setNbCommonActionsDone(-150);

    //For fast step...
    Globals::setTurn(10);

    //Test UNLIMITED ALL ACTIONS
    foreach(ACTION_TYPES as $actionType){
      $existing = PlayerActions::getPlayer($player->id,[$actionType])->first();
      if(!$existing) PlayerActions::createAction([
        'type'=>$actionType,
        'location'=>ACTION_LOCATION_PLAYER_BOARD,
        'player_id'=>$player->id,
        'state' => ACTION_STATE_UNLOCKED_FOREVER,
      ]);
      else $existing->setState(ACTION_STATE_UNLOCKED_FOREVER);
    }
    $this->gamestate->nextPrivateState($player->id, "continue");
  }

  //Add Charmer action to current player and end a turn to see if we have a step AfterTurn
  function debug_ActionCharmer()
  {

    $turn = Globals::getTurn();
    $player = Players::getCurrent();
    $players = Players::getAll();
    $p1 = $players->first();
    Globals::setFirstPlayer($p1->getId());
    foreach($players as $pid => $player){
      $player->setLastTurn($turn -1);
    }
    Globals::setAfterTurnPlayers([]);

    $this->gamestate->jumpToState( ST_PLAYER_TURN );

    $actionType = ACTION_TYPE_CHARMER;
    $existing = PlayerActions::getPlayer($player->id,[$actionType])->first();
    if(!$existing) PlayerActions::createAction([
      'type'=>$actionType,
      'location'=>ACTION_LOCATION_PLAYER_BOARD,
      'player_id'=>$player->id,
      'state' => PlayerActions::getInitialState($actionType),
    ]);
    else $existing->setState(PlayerActions::getInitialState($actionType));
    $this->debugUI();

    foreach($players as $pid => $player){
      $this->gamestate->setPrivateState($pid,ST_TURN_COMMON_BOARD);
      //actGoToNext
      $this->gamestate->nextPrivateState($pid, "next");
      //actEndTurn
      $nextPlayer = $this->startNextPlayerTurn($player, $turn);
      Notifications::endTurn($player);
      $this->gamestate->setPlayerNonMultiactive( $pid, 'end');
    }
    
  }

  function debug_Notifs(){
    $player = Players::getCurrent();
    $next = Players::getNextId($player);
    $targetplayer = Players::get($next);

    $token = Tokens::get(34);
    //Notifications::lastDriftRemove($player,$token,$targetplayer); 
    //Notifications::moveBackToBox($player,$token,'D9',1); 
    //Notifications::spRest($player,$token,1); 

    //test notif on unknown token :
    //Notifications::newPollen($player,Tokens::createToken([
    //    'type'=>TOKEN_STIG_WHITE,
    //    'location'=>TOKEN_LOCATION_PLAYER_BOARD,
    //    'player_id'=>$player->id,
    //    'y'=>1,
    //    'x'=>1,
    //]));

    Notifications::spPilferer($player,$targetplayer,$token,0);
  }
  function debug_Wind()
  {
    $player = Players::getCurrent();
    $turn = Globals::getTurn();
    $this->doWindEffect($turn,$player);
    $this->doWindEffect($turn);
  }
  function debug_NewWind()
  {
    $this->generateWind();
    Notifications::newWinds(Globals::getAllWindDir());
    Notifications::windBlows('W',new Collection([]),null); 
    Notifications::windBlows('E',new Collection([]),null); 
    Notifications::windBlows('S',new Collection([]),null); 
    Notifications::windBlows('N',new Collection([]),null); 
  }
  function debug_Schema()
  {
    $round = Globals::getRound();
    $schema = Schemas::getCurrentSchema();
    Notifications::newRound($round,$schema,[],[]);
  }

  //Direct successful schema and go to end of game (Remember to uncomment transition to state playerGameEnd)
  function debug_SchemaEnd()
  {
    $player = Players::getCurrent();
    $schema = Schemas::getCurrentSchema();
    //----------------------------------------
    // RESET Some values
    $player->setScore(0);
    Tokens::deleteAllAtLocation(TOKEN_LOCATION_PLAYER_BOARD,$player->id);
    Globals::setWinnersIds([]);
    //Add ALL ACTIONS for Solo no limit
    foreach(ACTION_TYPES as $actionType){
      $existing = PlayerActions::getPlayer($player->id,[$actionType])->first();
      if(!$existing) PlayerActions::createAction([
        'type'=>$actionType,
        'location'=>ACTION_LOCATION_PLAYER_BOARD,
        'player_id'=>$player->id,
        'state' => ACTION_STATE_UNLOCKED_FOREVER,
      ]);
      else $existing->setState(ACTION_STATE_UNLOCKED_FOREVER);
    }
    //----------------------------------------
    Globals::setTurn(10 + 3);
    //----------------------------------------
    $this->gamestate->jumpToState( ST_NEXT_TURN );
    //$this->gamestate->setPlayersMultiactive( [$player->id], 'end' );
    //$this->gamestate->initializePrivateState($player->id); 
    //----------------------------------------
    //Choose the turn and actions to reach that point
    $player->setNbPersonalActionsDone(3);
    $player->setNbCommonActionsDone(2);
    //----------------------------------------
    //RESET BOARD to match :
    $tokens = [];
    foreach($schema->end as $token){
      $tokens[] = [
        'type' => $token->type,
        'location' => TOKEN_LOCATION_PLAYER_BOARD,
        'player_id' => $player->id,
        'nbr' => 1,
        'y' => $token->row,
        'x' => $token->col,
      ];
    }
    Tokens::create($tokens);
    //-------------------------------------------
    $this->debugUI();
    //-------------------------------------------
    $isWin = $this->isSchemaFulfilled($player);
    //if($isWin) Notifications::message('Schema fulfilled !',[]);
    //else Notifications::message('Schema in progress...',[]);
    $this->debugUI();
    //$this->gamestate->jumpToState( ST_NEXT_TURN );
    $this->actEndTurn();
    
    //$this->gamestate->setPlayersMultiactive( [$player->id], 'end' );
    //Game::get()->gamestate->setPrivateState($player->id, ST_TURN_PERSONAL_BOARD);
  }

  function debug_Scoring(){
    $players = Players::getAll();
    foreach($players as $pId =>$player){
      $player->setScore(0);
      $player->setScoreAux(0);
    }
    $this->debugUI();

    $this->computeSchemaScoring();
  }

  function debug_Winners()
  {
    $winners = Players::getAll()->getIds();
    Globals::setWinnersIds($winners);
    Notifications::message('debugWinners',[ 'w'=> Globals::getWinnersIds()]);
  }
  
  function debug_Points()
  {
    $player = Players::getCurrent();
    Notifications::addPoints($player,4);
    Notifications::addPoints($player,3,'TEST ${n} / ${n2}',9);
  }
  
  function debug_Schemas()
  {
    Notifications::message('debugSchemas',[ 'types'=> Schemas::getUiData()]);
  }
  function debug_NewRoundTokens()
  {
    Tokens::setupNewRound(Players::getAll(),Schemas::getCurrentSchema());
  }
  function debug_NewRoundActions()
  {
    $schema = Schemas::getCurrentSchema();
    //$schema = Schemas::getTypes()[OPTION_SCHEMA_25];
    PlayerActions::setupNewRound(Players::getAll(),$schema);
    Notifications::message("",['a'=>PlayerActions::getAll()->ui()]);
  }
  function debug_NewRound()
  {
    $player = Players::getCurrent();
    //----------------------------------------
    // RESET Some values
    $player->setScore(0);
    PGlobals::setLastTurn($player->getId(),0);
    Tokens::deleteAllAtLocation(TOKEN_LOCATION_PLAYER_BOARD,$player->id);
    Globals::setWinnersIds([]);
    $this->debugUI();
    //----------------------------------------
    $this->gamestate->jumpToState( ST_NEXT_ROUND );
  }
  function debug_NewTurn()
  {
    
    $player = Players::getCurrent();

    $players = Players::getAll();
    $turn = (Globals::getTurn() ) % 10 +1;
    Globals::setTurn($turn);
    Players::setupNewTurn($players,$turn);
    Players::startTurn($players->getIds(),$turn);
    PlayerActions::setupNewTurn($players,$turn);
    Notifications::newTurn($turn);
  }
  function debug_EndTurn()
  {
    $player = Players::getCurrent();
    Notifications::endTurn($player);
  }
  function debug_CMD()
  {
    $player = Players::getCurrent();
    $player->setCommonMoveDone(FALSE);
    $player->setCommonMoveDone(FALSE);
    $player->setCommonMoveDone(TRUE);
    $player->setCommonMoveDone(FALSE);
    $this->gamestate->nextPrivateState($player->id, "continue");
  }
  
  function debug_ResetJoker()
  {
    $player = Players::getCurrent();
    $player->setJokerUsed(false);
    $this->gamestate->nextPrivateState($player->id, "continue");
  }
  
  function debug_GainSpActions(int $nbActions)
  {
    $player = Players::getCurrent();
    $pId = $player->getId();
    PGlobals::setNbSpActions($pId,$nbActions);
    PGlobals::setNbSpActionsMax($pId,$nbActions);
    PGlobals::setState($pId, ST_TURN_CENTRAL_CHOICE_SP);
    $player->setPrivateState(ST_TURN_CENTRAL_CHOICE_TOKEN_LAND);
    $this->gamestate->nextPrivateState($pId, "gainSp");
  }

  
  function debug_Stats()
  {
    $players = Players::setupNewRound();
    $round = Globals::getRound();
    $schema = Schemas::getCurrentSchema();

    Stats::setupNewRound($players,$schema);
  }

  function debug_PathFinding(){

    $player = Players::getCurrent();
    $boardTokens =Tokens::getAllOnPersonalBoard($player->id );

    $startingCell = [ 'x' => 4, 'y' => 1, ];
    $cost = function ($source, $target, $d) use ($boardTokens) {
      // If there is a unit => can't go there
      $existingToken = Tokens::findTokenOnBoardWithCoord($boardTokens,$target['y'], $target['x']);
      if(isset($existingToken)) return 100;//not empty
      return 1;
    };
    $cellsMarkers = GridUtils::getReachableCellsAtDistance($startingCell,10, $cost);
    $cells = $cellsMarkers[0];
    $markers = $cellsMarkers[1];
    $this->trace("debugPathFinding(".json_encode($startingCell)." ) : cells=".json_encode($cells)." /// : markers=".json_encode($markers));
  }
  //----------------------------------------------------------------
  //Clear logs
  function debug_CLS(){
    $query = new QueryBuilder('gamelog', null, 'gamelog_packet_id');
    $query->delete()->run();
  }
  
  //Clear all logs
  public static function debug_ClearLogs()
  {
      $query = new QueryBuilder('log', null, 'id');
      $query->delete()->run();
      $query = new QueryBuilder('gamelog', null, 'gamelog_packet_id');
      $query->delete()->run();
  }
  //*/
  function debug_UI(){
    //players colors are not reloaded after using LOAD/SAVE buttons
    self::reloadPlayersBasicInfos();
    $player = Players::getCurrent();
    Notifications::refreshUI($player->getId(),$this->getAllDatas());
  }
}
