<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialCombinationTrait
{
    public function argSpCombination($player_id)
    {
        $tokens = $this->listTokensIdsForCombination($player_id);
        return [
            'tokensIds' => $tokens,
        ];
    }
    
    /**
     * Special action of selecting a token with 8 neighbours
     * @param int $row COORD of new white token
     * @param int $column COORD of new white token
     */
    public function actCombination($tokenId)
    {
        self::checkAction( 'actCombination' ); 
        self::trace("actCombination($tokenId)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionCost = ACTION_COST_COMBINATION* $this->getGetActionCostModifier();
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token = Tokens::get($tokenId);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_BOARD ){
            throw new UnexpectedException(150,"You cannot select this token");
        }
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canPlayCombination($token,$boardTokens)){
            throw new UnexpectedException(155,"You cannot COMBINATION there");
        }

        //EFFECT
        $previousColor = $token->getType();
        $token->setType(TOKEN_STIG_BROWN);
        Notifications::spCombination($player,$token,$previousColor,$actionCost);
        $token->checkAndBecomesPollen($player);

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        Stats::inc("actions_s".ACTION_TYPE_COMBINATION,$pId);
        Stats::inc("actions",$pId);

        $this->gamestate->nextPrivateState($pId, 'next');
    }
 
    /**
     * @param int $playerId
     * @return array List of possible token ids
     */
    public function listTokensIdsForCombination($playerId){
        $tokens = [];
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        foreach($boardTokens as $tokenId => $token){
            if($this->canPlayCombination($token,$boardTokens)){
                $tokens[] = $tokenId;
            }
        }
        return $tokens;
    }
    
    /**
     * @param StigmerianToken $token
     * @param Collection $boardTokens 
     * @return bool + TRUE if this token can become a new brown token (ie. 8 tokens around +2/2/2)
     *  + FALSE otherwise
     */
    public function canPlayCombination($token,$boardTokens){
        if($token->isPollen()) return false;
        if(TOKEN_STIG_BROWN == $token->getType()) return false;

        $adjacentTokens = Tokens::listAdjacentTokensOnReadBoard($boardTokens,$token->getRow(), $token->getCol(),true);
        if($adjacentTokens->count() < MAX_GRID_NEIGHBOURS ) return false;
        //look for 2 blue/2 red/2 yellow tokens
        $nbBlue = $adjacentTokens->filter( function ($token2) { return $token2->type == TOKEN_STIG_BLUE || $token2->type == TOKEN_POLLEN_BLUE; } )->count();
        $nbRed = $adjacentTokens->filter( function ($token2) { return $token2->type == TOKEN_STIG_RED || $token2->type == TOKEN_POLLEN_RED; } )->count();
        $nbYellow = $adjacentTokens->filter( function ($token2) { return $token2->type == TOKEN_STIG_YELLOW || $token2->type == TOKEN_POLLEN_YELLOW; } )->count();

        if($nbBlue < 2) return false;
        if($nbRed < 2) return false;
        if($nbYellow < 2) return false;

        return true;
    }

 
}
