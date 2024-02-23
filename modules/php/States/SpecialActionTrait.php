<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
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
        PGlobals::setState($player->id, ST_TURN_CHOICE_SPECIAL_ACTION);
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
                $nextState = "startMixing";
                $nextStateId = ST_TURN_SPECIAL_ACT_MIX;
                break;
            case ACTION_TYPE_COMBINATION:
                $nextState = "startCombination";
                $nextStateId = ST_TURN_SPECIAL_ACT_COMBINATION;
                break;
            case ACTION_TYPE_FULGURANCE:
                $nextState = "startFulgurance";
                $nextStateId = ST_TURN_SPECIAL_ACT_FULGURANCE;
                break;
            case ACTION_TYPE_CHOREOGRAPHY:
                $nextState = "startChoreography";
                $nextStateId = ST_TURN_SPECIAL_ACT_CHOREOGRAPHY;
                break;
            case ACTION_TYPE_DIAGONAL:
                $nextState = "startDiagonal";
                $nextStateId = ST_TURN_SPECIAL_ACT_DIAGONAL;
                break;
            case ACTION_TYPE_SWAP:
                $nextState = "startSwap";
                $nextStateId = ST_TURN_SPECIAL_ACT_SWAP;
                break;
            case ACTION_TYPE_MOVE_FAST:
                $nextState = "startFastMove";
                $nextStateId = ST_TURN_SPECIAL_ACT_MOVE_FAST;
                break;
            case ACTION_TYPE_WHITE:
                $nextState = "startWhite";
                $nextStateId = ST_TURN_SPECIAL_ACT_WHITE_STEP1;
                break;
            case ACTION_TYPE_BLACK:
                $nextState = "startBlack";
                $nextStateId = ST_TURN_SPECIAL_ACT_BLACK_STEP1;
                break;
            case ACTION_TYPE_TWOBEATS:
                $nextState = "startTwoBeats";
                $nextStateId = ST_TURN_SPECIAL_ACT_TWOBEATS;
                break;
            case ACTION_TYPE_REST:
                $nextState = "startRest";
                $nextStateId = ST_TURN_SPECIAL_ACT_REST;
                break;
            case ACTION_TYPE_NSNK:
                $nextState = "startNSNK";
                $nextStateId = ST_TURN_SPECIAL_ACT_NSNK;
                break;
            case ACTION_TYPE_COPY:
                $nextState = "startCopy";
                $nextStateId = ST_TURN_SPECIAL_ACT_COPY;
                break;
            case ACTION_TYPE_PREDICTION:
                $nextState = "startPrediction";
                $nextStateId = ST_TURN_SPECIAL_ACT_PREDICTION;
                break;
            case ACTION_TYPE_MIMICRY:
                $nextState = "startMimicry";
                $nextStateId = ST_TURN_SPECIAL_ACT_MIMICRY;
                break;
            default:
                throw new UnexpectedException(14,"Not supported action type : $actionType");
        }
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        PGlobals::setState($player->id, $nextStateId);
        $this->gamestate->nextPrivateState($pId, $nextState);
    }
 
}
