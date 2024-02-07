<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Exceptions\UserException;
use STIG\Helpers\Utils;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

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
     */
    public function actLand()
    {
        self::checkAction( 'actLand' ); 
        self::trace("actLand()");
        
        $player = Players::getCurrent();

        $remaining = $player->countRemainingPersonalActions();
        $actionCost = 1;

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        
        $this->gamestate->nextPrivateState($player->id, "startLand");
    }
    
    /**
     * Basic Action 3 : Moving a stigmerian on your board
     */
    public function actMove()
    {
        self::checkAction( 'actMove' ); 
        self::trace("actMove()");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $remaining = $player->countRemainingPersonalActions();
        $actionCost = ACTION_COST_MOVE;

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }

        $this->gamestate->nextPrivateState($player->id, "startMove");
    }

    
    /**
     * @return bool TRUE if a token can be placed on this player board ( Empty spot + Either Line A or adjacent to another token),
     *  FALSE otherwise
     */
    public function canPlaceOnPlayerBoard($playerId,$row, $column)
    {
        if(StigmerianToken::isCoordOutOfGrid($row, $column)) return false;

        $existingToken = Tokens::findOnPersonalBoard($playerId,$row, $column);
        if(isset($existingToken)) return false;//not empty

        //TODO JSA PERFS We could read all tokens on personal board before calling this function if we want to loop on this func
        if($row != ROW_START && Tokens::listAdjacentTokens($playerId,$row, $column)->isEmpty()){
            return false;
        }

        return true;
    }

}
