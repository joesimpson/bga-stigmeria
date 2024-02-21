<?php
namespace STIG;
use STIG\Core\Globals;
use STIG\Core\Game;
use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Helpers\GridUtils;
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
  function debugJSON(){
    $customOptions = $this->getTableOptions();
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
  
  /*
  function debugStats()
  {
    Stats::checkExistence();
  }
  function debugElim()
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
  }
  /*
  function debugForceState()
  {
    $this->gamestate->jumpToState( ST_NEXT_ROUND );
    //$this->gamestate->jumpToState( ST_NEXT_TURN );
  }
  function debugGoToNextPlayer()
  {
    $this->gamestate->nextState( 'next' );
  }

  function debugTokens()
  {
    $players = Players::getAll();
    Tokens::DB()->delete()->run();
    Tokens::setupNewGame($players, []);
  }
  
  function debugShuffleTokens()
  {
    $player = Players::getCurrent();
    Tokens::shuffle(TOKEN_LOCATION_PLAYER_DECK.$player->id);
  }
  // Add many actions ! YEAH
  function debugManyActions()
  {
    $player = Players::getCurrent();
    $player->setNbPersonalActionsDone(-150);
    $player->setNbCommonActionsDone(-150);
    $this->gamestate->nextPrivateState($player->id, "continue");
  }
  function debugWind()
  {
    $player = Players::getCurrent();
    $turn = Globals::getTurn();
    $this->doWindEffect($turn,$player);
    $this->doWindEffect($turn);
  }
  function debugNewWind()
  {
    Notifications::newWinds(Globals::getAllWindDir());
  }
  
  function debugSchema()
  {
    $round = Globals::getRound();
    $schema = Schemas::getCurrentSchema();
    Notifications::newRound($round,$schema,[]);
  }
  function debugSchemaEnd()
  {
    $player = Players::getCurrent();
    $schema = Schemas::getCurrentSchema();
    //----------------------------------------
    //RESET BOARD to match :
    $tokens = [];
    Tokens::deleteAllAtLocation(TOKEN_LOCATION_PLAYER_BOARD,$player->id);
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
    $isWin = $this->isSchemaFulfilled($player);
    if($isWin) Notifications::message('Schema fulfilled !',[]);
    else Notifications::message('Schema in progress...',[]);
  }

  function debugScoring(){
    $this->computeSchemaScoring();
  }

  function debugWinners()
  {
    $winners = Players::getAll()->getIds();
    Globals::setWinnersIds($winners);
    Notifications::message('debugWinners',[ 'w'=> Globals::getWinnersIds()]);
  }
  
  function debugPoints()
  {
    $player = Players::getCurrent();
    Notifications::addPoints($player,4);
    Notifications::addPoints($player,3,'TEST ${n} / ${n2}',9);
  }
  
  function debugSchemas()
  {
    Notifications::message('debugSchemas',[ 'types'=> Schemas::getUiData()]);
  }
  function debugNewRoundTokens()
  {
    Tokens::setupNewRound(Players::getAll(),Schemas::getCurrentSchema());
  }
  function debugNewRoundActions()
  {
    $schema = Schemas::getCurrentSchema();
    //$schema = Schemas::getTypes()[OPTION_SCHEMA_25];
    PlayerActions::setupNewRound(Players::getAll(),$schema);
    Notifications::message("",['a'=>PlayerActions::getAll()->ui()]);
  }
  function debugNewRound()
  {
    $this->gamestate->jumpToState( ST_NEXT_ROUND );
  }
  function debugNewTurn()
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
  function debugEndTurn()
  {
    $player = Players::getCurrent();
    Notifications::endTurn($player);
  }
  function debugCMD()
  {
    $player = Players::getCurrent();
    $player->setCommonMoveDone(FALSE);
    $player->setCommonMoveDone(FALSE);
    $player->setCommonMoveDone(TRUE);
    $player->setCommonMoveDone(FALSE);
    $this->gamestate->nextPrivateState($player->id, "continue");
  }
  
  function debugResetJoker()
  {
    $player = Players::getCurrent();
    $player->setJokerUsed(false);
    $this->gamestate->nextPrivateState($player->id, "continue");
  }

  
  function debugStats()
  {
    $players = Players::setupNewRound();
    $round = Globals::getRound();
    $schema = Schemas::getCurrentSchema();

    Stats::setupNewRound($players,$schema);
  }

  function debugPathFinding(){

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
  */
}
