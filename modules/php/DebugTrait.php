<?php
namespace STIG;
use STIG\Core\Globals;
use STIG\Core\Game;
use STIG\Core\Notifications;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;

trait DebugTrait
{
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
  
  /**
   * Add many actions ! YEAH
   */
  function debugAddNbActions()
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
    /*
    RESET BOARD to match :
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
    */
    $isWin = $this->isSchemaFulfilled($player);
    if($isWin) Notifications::message('Schema fulfilled !',[]);
    else Notifications::message('Schema in progress...',[]);
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
  }
  
  function debugSchemas()
  {
    Notifications::message('debugSchemas',[ 'types'=> Schemas::getUiData()]);
  }
  function debugNewRoundTokens()
  {
    Tokens::setupNewRound(Players::getAll(),Schemas::getCurrentSchema());
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
}
