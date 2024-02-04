<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Managers\Players;

trait PlayerTurnTrait
{
  
    public function stPlayerturn()
    {
        self::trace("stPlayerTurn()");
        
        $firstPlayer = Globals::getFirstPlayer();
        //When starting this state, First player is almost in a "activeplayer" situation :
        $playersToActive = [$firstPlayer];
        //During his turn, others may become active...

        //TODO JSA IN NORMAL MODE, we can activate every one

        $this->gamestate->setPlayersMultiactive( $playersToActive, 'end' );
    }

    public function argPlayerTurn()
    {

        return [
        ];
    }

    /**
     * MULTIACTIVE with a button to let next player start : this is wanted by publisher to have a semi simultaneous play
     */
    public function actLetNextPlay()
    {
        self::checkAction( 'actLetNextPlay' ); 
        
        $player = Players::getCurrent();
        $player_id = intval($player->getId());
        $player_name = $player->getName();
        self::trace("actLetNextPlay($player_id,$player_name )");

        //TODO JSA SAVE THIS INFO TO AVOID $player_id playing VS actions

        $nextPlayer_id = Players::getNextId($player_id);
        $nextPlayer = Players::get($nextPlayer_id);

        //TODO JSA CHECK nextPlayer not active AND not already played this turn

        Notifications::letNextPlay($player,$nextPlayer);

        $this->gamestate->setPlayersMultiactive( [$nextPlayer_id], 'end' );
    }
    
    public function actEndTurn()
    {
        self::checkAction( 'actEndTurn' ); 
        
        $player = Players::getCurrent();
        $player_id = intval($player->getId());
        $player_name = $player->getName();
        self::trace("actEndTurn($player_id,$player_name )");

        //TODO JSA ACTIVATE NEXT PLAYER who did not already play this turn (ie. if some player did not click actLetNextPlay)

        Notifications::endTurn($player);

        $this->gamestate->setPlayerNonMultiactive( $player_id, 'end');
    }
    
}
