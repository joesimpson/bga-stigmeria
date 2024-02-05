<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait PlayerTurnPersonalBoardTrait
{
  
    public function stPersonalBoardTurn()
    {
        self::trace("stCommonBoardTurn()");
        
    }

    public function argPersonalBoardTurn()
    {

        return [
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
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $nbActionsDone = $player->getNbPersonalActionsDone();
        $actionCost = 1;//TODO JSA ACTION MODEL
        //TODO JSA CHECK REMAINING ACTIONS VS cost
        
        $player->setNbPersonalActionsDone($nbActionsDone + $actionCost);
        Notifications::useActions($player);

        $tokens = Tokens::pickForLocation(1,TOKEN_LOCATION_PLAYER_DECK.$pId, TOKEN_LOCATION_PLAYER_RECRUIT, TOKEN_STATE_STIGMERIAN);
        $token = $tokens->first();
        if($token == null){
            //TODO JSA LOST GAME (maybe already lost before looking in the bag ?)
            throw new UnexpectedException(404,"Not supported draw : empty draw bag for player $pId");
        }

        Notifications::drawToken($player,$token, $actionCost);
    }
}
