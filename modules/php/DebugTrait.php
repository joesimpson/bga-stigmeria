<?php
namespace STIG;
use STIG\Core\Globals;
use STIG\Core\Game;
use STIG\Core\Notifications;

trait DebugTrait
{
  function debugGoToNextPlayer()
  {
    $this->gamestate->nextState( 'next' );
  }

}
