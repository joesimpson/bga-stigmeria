<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;

trait PlayerTurnTrait
{
  
    public function stPlayerturn()
    {
        self::trace("stPlayerTurn()");
        
        $turn = Globals::getTurn();
        $firstPlayer = Globals::getFirstPlayer();
        //When starting this state, First player is almost in a "activeplayer" situation :
        $playersToActive = [$firstPlayer];
        //During his turn, others may become active...

        $players = Players::getAll();
        //IN NORMAL MODE, we can activate every one
        $noCentralBoard = Globals::isModeNoCentralBoard();
        if($noCentralBoard){
            $playersToActive = $players->map( function ($player) { return $player->getId(); } )->toArray();
        }
        self::trace("stPlayerTurn() playersToActive =".json_encode($playersToActive));

        Players::startTurn($playersToActive,$turn);
        
        Notifications::emptyNotif();
        //$this->addCheckpoint(ST_TURN_COMMON_BOARD);
        foreach($playersToActive as $pId){
            $this->addCheckpoint(ST_TURN_COMMON_BOARD,$pId);
        }

        $this->gamestate->setPlayersMultiactive( $playersToActive, 'end' );
        
        //this is needed when starting private parallel states; players will be transitioned to initialprivate state defined in master state
        $this->gamestate->initializePrivateStateForAllActivePlayers(); 
        
        if ($noCentralBoard) {
            //$this->addCheckpoint(ST_TURN_PERSONAL_BOARD);
            foreach($playersToActive as $pId){
                $this->addCheckpoint(ST_TURN_PERSONAL_BOARD,$pId);
            }
            //move all players to different state 
            $this->gamestate->nextPrivateStateForAllActivePlayers("next");
        }
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
        $player_id = $player->getId();
        $player_name = $player->getName();
        self::trace("actLetNextPlay($player_id,$player_name )");

        $turn = Globals::getTurn();
        $nextPlayer = $this->startNextPlayerTurn($player, $turn, false);
        /*if(isset($nextPlayer)){
            Notifications::letNextPlay($player,$nextPlayer);
        }
        else {*/
            Notifications::emptyNotif();
        //}
        $this->addCheckpoint(ST_TURN_PERSONAL_BOARD, $player->id);
    }
    
    public function actEndTurn()
    {
        self::checkAction( 'actEndTurn' ); 
        
        $player = Players::getCurrent();
        $player_id = $player->getId();
        $player_name = $player->getName();
        $turn = Globals::getTurn();
        self::trace("actEndTurn($player_id,$player_name,$turn )");

        //ACTIVATE NEXT PLAYER who did not already play this turn (ie. if some player did not click actLetNextPlay)
        //Don't go further than next player (Example 3 players after the current one, because it is not in the current player powers to let others play)
        $nextPlayer = $this->startNextPlayerTurn($player, $turn);

        Notifications::endTurn($player);

        $this->gamestate->setPlayerNonMultiactive( $player_id, 'end');
    }

    /**
     * @param Player $player
     * @param int $turn
     * @param bool $automatic If this comes from game automatic decision, Else it is from player decision
     */
    public function startNextPlayerTurn($player, $turn, $automatic = true)
    {
        $player_id = $player->getId();
        self::trace( "startNextPlayerTurn($player_id, $turn, $automatic)" ); 
        
        $this->addStep( $player_id, $player->getPrivateState());

        $turn = Globals::getTurn();
        $nextPlayer = Players::getNextInactivePlayerInTurn($player_id, $turn);
        if(isset($nextPlayer)){
            if(!$automatic){
                // SAVE THIS INFO TO AVOID $player_id playing VS actions
                PlayerActions::lockVSActionsForTurn($player_id);
                $actions = PlayerActions::getPlayer($player_id)->filter(function($action) {
                        return $action->state == ACTION_STATE_LOCKED_FOR_TURN;
                    });
                Notifications::letNextPlay($player,$nextPlayer,$actions);
            }
            $nextPlayer->startTurn($turn);

            //No ! next Player currently does not have a privateState to save !, don't save null PLEASE
            //$this->addStep( $nextPlayer->id, $nextPlayer->getPrivateState());
            $this->addCheckpoint(ST_TURN_COMMON_BOARD, $nextPlayer->id);
            
            $this->gamestate->setPlayersMultiactive( [$nextPlayer->id], 'end' );
            $this->gamestate->initializePrivateState($nextPlayer->id); 
        }
        return $nextPlayer;
    }
}
