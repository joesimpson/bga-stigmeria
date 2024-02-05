<?php
namespace STIG;
use STIG\Core\Globals;
use STIG\Core\Game;
use STIG\Core\Notifications;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait DebugTrait
{
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
}
