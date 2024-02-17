<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialActionTrait
{
    public function argSpecialAction($player_id)
    {
        $player = Players::get($player_id);
        $flowerType = Schemas::getCurrentSchema()->type;
        $deckSize = Tokens::countDeck($player_id);
        $remaining = $player->countRemainingPersonalActions();
        $actions =[];
        if($flowerType == OPTION_FLOWER_VERTIGHAINEUSE){
            if($remaining >= ACTION_COST_MIXING){
                $actions[] = ACTION_TYPE_MIXING;
            }
        }
        else if($flowerType == OPTION_FLOWER_MARONNE){

            if($remaining >=ACTION_COST_COMBINATION){
                $actions[] = ACTION_TYPE_COMBINATION;
            }
            if($remaining >=ACTION_COST_FULGURANCE && $deckSize>= FULGURANCE_NB_TOKENS ){
                $actions[] = ACTION_TYPE_FULGURANCE;
            }
        }
        else if($flowerType == OPTION_FLOWER_DENTDINE){

            if($remaining >=ACTION_COST_CHOREOGRAPHY){
                $actions[] = ACTION_TYPE_CHOREOGRAPHY;
            }
            if($remaining >=ACTION_COST_MOVE_DIAGONAL){
                $actions[] = ACTION_TYPE_DIAGONAL;
            }
            if($remaining >=ACTION_COST_SWAP){
                $actions[] = ACTION_TYPE_SWAP;
            }
            if($remaining >=ACTION_COST_MOVE_FAST){
                $actions[] = ACTION_TYPE_MOVE_FAST;
            }
        }
        else if($flowerType == OPTION_FLOWER_SIFFLOCHAMP){

            if($remaining >=ACTION_COST_WHITE){
                $actions[] = ACTION_TYPE_WHITE;
            }
            if($remaining >=ACTION_COST_BLACK){
                $actions[] = ACTION_TYPE_BLACK;
            }
            if($remaining >=ACTION_COST_TWOBEATS){
                $actions[] = ACTION_TYPE_TWOBEATS;
            }
            if($remaining >=ACTION_COST_REST){
                $actions[] = ACTION_TYPE_REST;
            }
        }
        else if($flowerType == OPTION_FLOWER_INSPIRACTRICE){
            // ALL THE PREVIOUS ACTIONS but with a DOUBLE cost
            $cost = ACTION_COST_MODIFIER_INSPIRACTRICE;
            if($remaining >= $cost * ACTION_COST_MIXING){
                $actions[] = ACTION_TYPE_MIXING;
            }
            if($remaining >= $cost * ACTION_COST_COMBINATION){
                $actions[] = ACTION_TYPE_COMBINATION;
            }
            if($remaining >= $cost * ACTION_COST_FULGURANCE && $deckSize>= FULGURANCE_NB_TOKENS ){
                $actions[] = ACTION_TYPE_FULGURANCE;
            }
            if($remaining >= $cost * ACTION_COST_CHOREOGRAPHY){
                $actions[] = ACTION_TYPE_CHOREOGRAPHY;
            }
            if($remaining >= $cost * ACTION_COST_MOVE_DIAGONAL){
                $actions[] = ACTION_TYPE_DIAGONAL;
            }
            if($remaining >= $cost * ACTION_COST_SWAP){
                $actions[] = ACTION_TYPE_SWAP;
            }
            if($remaining >= $cost * ACTION_COST_MOVE_FAST){
                $actions[] = ACTION_TYPE_MOVE_FAST;
            }
            if($remaining >= $cost * ACTION_COST_WHITE){
                $actions[] = ACTION_TYPE_WHITE;
            }
            if($remaining >= $cost * ACTION_COST_BLACK){
                $actions[] = ACTION_TYPE_BLACK;
            }
            if($remaining >= $cost * ACTION_COST_TWOBEATS){
                $actions[] = ACTION_TYPE_TWOBEATS;
            }
            if($remaining >= $cost * ACTION_COST_REST){
                $actions[] = ACTION_TYPE_REST;
            }
        }
        return [
            'a' => $actions,
        ];
    }
    /**
     * @return int
     */
    public function getGetActionCostModifier()
    {
        $multiplier = 1;
        $flowerType = Schemas::getCurrentSchema()->type;
        if(!Globals::isModeCompetitive() && $flowerType == OPTION_FLOWER_INSPIRACTRICE){
            $multiplier = ACTION_COST_MODIFIER_INSPIRACTRICE;
        }
        //For competitive modes it is more complex
        return $multiplier;
    }

    public function actCancelSpecial()
    {
        self::checkAction( 'actCancelSpecial' ); 
        self::trace("actCancelSpecial()");
        
        $player = Players::getCurrent();

        $player->setSelection([]);
        
        //NOTHING TO CANCEL In BDD, return to previous state
        $this->gamestate->nextPrivateState($player->id, "cancel");
    }
    
    /**
     * Choose an action and go to another corresponding state
     * @param int $actionType
     */
    public function actChoiceSpecial($actionType)
    {
        self::checkAction( 'actChoiceSpecial' ); 
        self::trace("actChoiceSpecial($actionType)");
        
        $player = Players::getCurrent();
        $pId = $player->id;

        $player->setSelection([]);
        switch($actionType){
            case ACTION_TYPE_MIXING:
                $actionCost = ACTION_COST_MIXING;
                $nextState = "startMixing";
                break;
            case ACTION_TYPE_COMBINATION:
                $actionCost = ACTION_COST_COMBINATION;
                $nextState = "startCombination";
                break;
            case ACTION_TYPE_FULGURANCE:
                $actionCost = ACTION_COST_FULGURANCE;
                $nextState = "startFulgurance";
                break;
            case ACTION_TYPE_CHOREOGRAPHY:
                $actionCost = ACTION_COST_CHOREOGRAPHY;
                $nextState = "startChoreography";
                break;
            case ACTION_TYPE_DIAGONAL:
                $actionCost = ACTION_COST_MOVE_DIAGONAL;
                $nextState = "startDiagonal";
                break;
            case ACTION_TYPE_SWAP:
                $actionCost = ACTION_COST_SWAP;
                $nextState = "startSwap";
                break;
            case ACTION_TYPE_MOVE_FAST:
                $actionCost = ACTION_COST_MOVE_FAST;
                $nextState = "startFastMove";
                break;
            case ACTION_TYPE_WHITE:
                $actionCost = ACTION_COST_WHITE;
                $nextState = "startWhite";
                break;
            case ACTION_TYPE_BLACK:
                $actionCost = ACTION_COST_BLACK;
                $nextState = "startBlack";
                break;
            case ACTION_TYPE_TWOBEATS:
                $actionCost = ACTION_COST_TWOBEATS;
                $nextState = "startTwoBeats";
                break;
            case ACTION_TYPE_REST:
                $actionCost = ACTION_COST_REST;
                $nextState = "startRest";
                break;
            default:
                throw new UnexpectedException(14,"Not supported action type : $actionType");
        }
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $this->gamestate->nextPrivateState($pId, $nextState);
    }
 
}
