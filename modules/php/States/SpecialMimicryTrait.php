<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\Collection;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialMimicryTrait
{
    public function argSpMimicry($playerId)
    {
        $possibles = $this->listPossibleMimicry($playerId);
        $colors = $possibles['colors'];
        $tokenId = $possibles['tokenId'];
        $tokenLocation = $possibles['tokenLocation'];
        return [
            'colors' => $colors,
            'tokenId' => $tokenId,
            'L' => $tokenLocation,
        ];
    } 
    /**
     * @param array $typeDest
     */
    public function actMimicry($typeDest)
    {
        self::checkAction( 'actMimicry' ); 
        self::trace("actMimicry($typeDest)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionType = ACTION_TYPE_MIMICRY;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        $token = Tokens::getLastLanded($pId);
        if(!isset($token)){
            throw new UnexpectedException(404,"No last landed token to use mimicry !");
        }
        $possibles = $this->listPossibleMimicry($pId);
        $colors = $possibles['colors']->toArray();
        if(!in_array($typeDest, $colors)){
            throw new UnexpectedException(11,"The dest color $typeDest is not adjacent");
        }
        /* -> see listPossibleMimicry
        if(!in_array($typeDest, STIG_COLORS)){
            throw new UnexpectedException(11,"You cannot select a dest color $typeDest");
        }
        $used = $player->getMimicColorUsed();
        if(in_array($typeDest, $used)){
            throw new UnexpectedException(11,"You cannot reselect this dest color $typeDest");
        }
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        $adjacentTokens = Tokens::listAdjacentTokensOnReadBoard($boardTokens,$token->row, $token->col);
        $adjacentTokensColors = $adjacentTokens->map(function($t) { return $t->getType();} )->toArray();
        if(!in_array($typeDest, $adjacentTokensColors)){
            throw new UnexpectedException(11,"The dest color $typeDest is not adjacent");
        }
        */

        //  EFFECT : 
        $player->addMimicColorUsed($typeDest);
        $previousColor = $token->getType();
        $token->setType($typeDest);
        Notifications::spMimicry($player,$token,$previousColor,$actionCost); 
        $token->checkAndBecomesPollen($player);

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        $player->giveExtraTime();

        PGlobals::setState($pId,ST_TURN_PERSONAL_BOARD);
        $this->gamestate->nextPrivateState($pId, 'next');
    }

    /**
     * @param int $playerId
     * @return array [ 'colors' => Collection[possible colors to mimic], 'tokenId' => $tokenId, 'tokenLocation'=>$tokenLocation]
     */
    public function listPossibleMimicry($playerId){
        $colors = new Collection([]);
        $tokenId = null;
        $tokenLocation = '';
        $player = Players::get($playerId);
        $token = Tokens::getLastLanded($playerId);
        if(isset($token)){
            $colors = new Collection(STIG_COLORS);
            $used = $player->getMimicColorUsed();
            $tokenId = $token->getId();
            $tokenLocation = $token->getCoordName();
            $tokenColor = $token->getType();
            $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
            $adjacentTokens = Tokens::listAdjacentTokensOnReadBoard($boardTokens,$token->row, $token->col);
            $adjacentTokensColors = $adjacentTokens->map(function($t) { return $t->getType();} )->toArray();
            $colors = $colors->filter( function($color) use ($tokenColor,$used,$adjacentTokensColors){  
                return !in_array($color,$used) 
                    && in_array($color, $adjacentTokensColors)
                    && $color != $tokenColor; 
            });
        }
        return [ 
            'colors' => $colors,
            'tokenId' => $tokenId,
            'tokenLocation'=> $tokenLocation
        ];
    }
 
}
