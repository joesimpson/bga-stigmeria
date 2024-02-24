<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
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
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionType = ACTION_TYPE_REST;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        $token1 = Tokens::get($tokenId);
        if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(150,"You cannot select this token");
        }

        //  EFFECT
        if($token1->id ==$tokenId ){
            Notifications::spRest($player,$token1,$actionCost); 
            Tokens::delete($tokenId);
        }

        //This action is now USED IN player turn
        $playerAction->setNewStateAfterUse();
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player,$playerAction);
        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        Stats::inc("tokens_board",$pId,-1);

        PGlobals::setState($player->id, ST_TURN_PERSONAL_BOARD);
        $this->gamestate->nextPrivateState($pId, 'next');
    }
 
}
