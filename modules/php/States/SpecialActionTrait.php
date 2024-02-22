<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;
use STIG\Models\PlayerAction;
use STIG\Models\StigmerianToken;

trait SpecialActionTrait
{
    public function argSpecialAction($player_id)
    {
        $player = Players::get($player_id);
        $deckSize = Tokens::countDeck($player_id);
        $remaining = $player->countRemainingPersonalActions();
        
        $playerActions = PlayerActions::getPlayer($player_id);
        $possibleActions = $playerActions->map(function($action) {
                return $action->type;
            })->toArray();
        $unlockedActions = $playerActions
            ->filter(function($action) use ($remaining,$deckSize) {
                if(!$action->canBePlayed($remaining )) return false;
                if(!$action->canBePlayedWithCurrentBoard($deckSize )) return false;
                return true;
            })->map(function($action) {
                return $action->type;
            })->toArray();
        return [
            'a' => $possibleActions,
            'e' => $unlockedActions,
        ];
    }
    /**
     * @return int
     */
    public function getGetActionCostModifier()
    {
        return PlayerActions::getGetActionCostModifier();
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
            case ACTION_TYPE_NSNK:
                $actionCost = ACTION_COST_NSNK;
                $nextState = "startNSNK";
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
