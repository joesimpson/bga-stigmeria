<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Exceptions\UserException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait PlayerTurnPersonalBoardTrait
{
  
    public function stPersonalBoardTurn()
    {
        self::trace("stCommonBoardTurn()");
        
    }

    public function argPersonalBoardTurn($player_id)
    {
        $player = Players::get($player_id);
        return [
            'n'=> $player->countRemainingPersonalActions(),
        ];
    }
    
    /**
     * TODO JSA : Proof of concept, maybe not necessary
     */
    public function actBackToCommon()
    {
        self::checkAction( 'actBackToCommon' ); 
        
        //moving current player to different state :
        $this->gamestate->nextPrivateState($this->getCurrentPlayerId(), "back");
    }
    
    /**
     * Basic Action 1 : draw a stigmerian in your bag
     */
    public function actDraw()
    {
        self::checkAction( 'actDraw' ); 
        self::trace("actDraw()");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $remaining = $player->countRemainingPersonalActions();
        $nbActionsDone = $player->getNbPersonalActionsDone();
        $actionCost = 1;//TODO JSA ACTION MODEL ?

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        
        $player->setNbPersonalActionsDone($nbActionsDone + $actionCost);
        Notifications::useActions($player);

        $tokens = Tokens::pickForLocation(1,TOKEN_LOCATION_PLAYER_DECK.$pId, TOKEN_LOCATION_PLAYER_RECRUIT, TOKEN_STATE_STIGMERIAN);
        $token = $tokens->first();
        if($token == null){
            //TODO JSA LOST GAME (maybe already lost before looking in the bag ?)
            throw new UnexpectedException(404,"Not supported draw : empty draw bag for player $pId");
        }

        Notifications::drawToken($player,$token, $actionCost);

        $this->gamestate->nextPrivateState($player->id, "continue");
    }

    
    /**
     * Basic Action 2 : landing a stigmerian on your board
     * @param int $token_id
     * @param int $row
     * @param int $col
     */
    public function actLand($token_id, $row, $column)
    {
        self::checkAction( 'actLand' ); 
        self::trace("actLand($token_id)");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $remaining = $player->countRemainingPersonalActions();
        $nbActionsDone = $player->getNbPersonalActionsDone();
        $actionCost = 1;

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }

        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_RECRUIT ){
            throw new UnexpectedException(20,"You cannot place this token");
        }
        //TODO JSA CHECK POSSIBLE MOVE $row, $column for this token (Empty spot : Either Line A or adjacent to another)

        $player->setNbPersonalActionsDone($nbActionsDone + $actionCost);
        Notifications::useActions($player);

        //EFFECT : PLACE the TOKEN 
        $token->moveToPlayerBoard($player,$row,$column);

        $this->gamestate->nextPrivateState($player->id, "continue");
    }
}
