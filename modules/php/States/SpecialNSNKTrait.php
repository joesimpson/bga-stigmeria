<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait SpecialNSNKTrait
{
    public function argSpNSNK($playerId)
    { 
        return [
            'p' => $this->listPossibleNSNK($playerId),
        ];
    } 
    /**
     * Special action like normal mode joker
     * @param int $typeSource
     * @param int $typeDest
     */
    public function actNSNK($typeSource, $typeDest)
    {
        self::checkAction( 'actNSNK' ); 
        self::trace("actNSNK($typeSource, $typeDest)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionType = ACTION_TYPE_NSNK;
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
        //this is not a joker, but use the same query
        $tokens = $this->canPlayJoker($pId,$typeSource);
        if($tokens->isEmpty()){
            throw new UnexpectedException(12,"You cannot play a with source color $typeSource");
        }

        //EFFECT
        foreach($tokens as $token){
            $token->setType($typeDest);
        }
        $newTokens = $tokens;
        Notifications::spNSNK($player,$typeSource, $typeDest, $newTokens);

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);

        $this->gamestate->nextPrivateState($pId, 'next');
    }

    /**
     * @param int $playerId
     */
    public function listPossibleNSNK($playerId){
        $possibleChanges = [];
        foreach (STIG_PRIMARY_COLORS as $colorSrc) {
            //this is not a joker, but use the same query
            if(!$this->canPlayJoker($playerId,$colorSrc)->isEmpty()){
                foreach (STIG_PRIMARY_COLORS as $colorDest) {
                    if($colorSrc == $colorDest) continue;
                    $possibleChanges[] = ['src' => $colorSrc, 'dest' => $colorDest] ;
                }
            }
        }
        return $possibleChanges;
    }
 
}
