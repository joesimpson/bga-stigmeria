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

trait SpecialCopyTrait
{
    public function argSpCopy($player_id)
    {
        $tokensPrimary = Tokens::getAllOnPersonalBoard($player_id, STIG_PRIMARY_COLORS);
        $args = [
            'tokensIds' => $tokensPrimary->getIds(),
            'cancel' => true,
        ];
        $this->checkCancelFromLastDrift($args,$player_id);
        return $args;
    } 
    /**
     * Special action of Changing 1 Primary Color token from the board
     * @param int $tokenId
     * @param int $typeDest
     */
    public function actCopy($tokenId, $typeDest)
    {
        self::checkAction( 'actCopy' ); 
        self::trace("actCopy($tokenId)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionType = ACTION_TYPE_COPY;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        if(!in_array($typeDest, STIG_PRIMARY_COLORS)){
            throw new UnexpectedException(11,"You cannot play a with dest color $typeDest");
        }
        $token1 = Tokens::get($tokenId);
        if($token1->pId != $pId || $token1->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(150,"You cannot select this token");
        }
        $typeSrc = $token1->getType();
        if(!in_array($typeSrc, STIG_PRIMARY_COLORS) || $typeSrc== $typeDest){
            throw new UnexpectedException(11,"You cannot play a with src color $typeSrc");
        }

        //  EFFECT
        $token1->setType($typeDest);
        Notifications::spCopy($player,$token1,$typeSrc,$actionCost); 
        //Don't forget to check !
        $token1->checkAndBecomesPollen($player);

        //This action is now USED IN player turn
        $playerAction->setNewStateAfterUse();
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player,$playerAction);
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        $player->giveExtraTime();
        
        if($this->returnToLastDriftState($pId,$playerAction)) return;

        PGlobals::setState($pId,ST_TURN_PERSONAL_BOARD);
        $this->gamestate->nextPrivateState($pId, 'next');
    }
 
}
