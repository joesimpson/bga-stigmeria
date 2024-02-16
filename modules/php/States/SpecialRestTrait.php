<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialRestTrait
{
    public function argSpRest($player_id)
    {
        $tokens = Tokens::getAllOnPersonalBoard($player_id);
        return [
            'tokensIds' => $tokens->getIds(),
        ];
    } 
    /**
     * Special action of REMOVING 1 token from the board
     * @param int $tokenId
     */
    public function actRest($tokenId)
    {
        self::checkAction( 'actRest' ); 
        self::trace("actRest($tokenId)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
 
        $actionCost = ACTION_COST_REST* $this->getGetActionCostModifier();
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token1 = Tokens::get($tokenId);
        if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(150,"You cannot select this token");
        }
        //TODO JSA SPECIAL ACTION model to know if action already done

        //  EFFECT
        if($token1->id ==$tokenId ){
            Notifications::spRest($player,$token1,$actionCost); 
            Tokens::delete($tokenId);
        }

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        Stats::inc("actions_s".ACTION_TYPE_REST,$pId);
        Stats::inc("actions",$pId);
        Stats::inc("tokens_board",$pId,-1);

        $this->gamestate->nextPrivateState($pId, 'next');
    }
 
}
