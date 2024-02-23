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

trait SpecialPredictionTrait
{
    public function argSpPrediction($playerId)
    {
        $colors = STIG_COLORS;
        return [
            'colors' => $colors,
            'n' => NB_TOKENS_PREDICTION,
        ];
    } 
    /**
     * Special action of Taking Stigmerians from the game box
     * @param array $typeDest
     */
    public function actPrediction($typesDestArray)
    {
        self::checkAction( 'actPrediction' ); 
        self::trace("actPrediction()".json_encode($typesDestArray));
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionType = ACTION_TYPE_PREDICTION;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        $typesDestArray = array_unique($typesDestArray);
        if(count($typesDestArray) != NB_TOKENS_PREDICTION) {
            throw new UnexpectedException(505,"You must select 3 different colors");
        }
        //  EFFECT : create 3 tokens and put them in my bag
        foreach($typesDestArray as $typeDest){
            if(!in_array($typeDest, STIG_COLORS)){
                throw new UnexpectedException(11,"You cannot select a dest color $typeDest");
            }
            $token = Tokens::createToken([
                'type' => $typeDest,
                'location' => TOKEN_LOCATION_PLAYER_DECK.$pId,
                'player_id' => $pId,
            ]);
        }
        Tokens::shuffleBag($pId);
        Notifications::spPrediction($player,$typesDestArray,$actionCost); 

        //This action is now USED IN player turn
        $playerAction->setState(ACTION_STATE_LOCKED_FOR_TURN);
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player,$playerAction);
        Stats::inc("actions_s".$actionType,$pId);
        Stats::inc("actions",$pId);
        $player->giveExtraTime();

        PGlobals::setState($pId,ST_TURN_PERSONAL_BOARD);
        $this->gamestate->nextPrivateState($pId, 'next');
    }
 
}
