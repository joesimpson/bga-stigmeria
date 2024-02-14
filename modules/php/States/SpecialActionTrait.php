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
        $remaining = $player->countRemainingPersonalActions();
        $actions =[];
        if($flowerType == OPTION_FLOWER_VERTIGHAINEUSE && $remaining >=ACTION_COST_MERGE){
            $actions[] = ACTION_TYPE_MERGE;
        }
        if($flowerType == OPTION_FLOWER_DENTDINE){

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
        if($flowerType == OPTION_FLOWER_SIFFLOCHAMP){

            if($remaining >=ACTION_COST_WHITE){
                $actions[] = ACTION_TYPE_WHITE;
            }
            if($remaining >=ACTION_COST_BLACK){
                $actions[] = ACTION_TYPE_BLACK;
            }
        }
        return [
            'a' => $actions,
        ];
    }
      
    public function actCancelSpecial()
    {
        self::checkAction( 'actCancelSpecial' ); 
        self::trace("actCancelSpecial()");
        
        $player = Players::getCurrent();

        Globals::setSelectedTokens([]);
        
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

        switch($actionType){
            case ACTION_TYPE_MERGE:
                $actionCost = ACTION_COST_MERGE;
                $nextState = "startMerge";
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
            default:
                throw new UnexpectedException(14,"Not supported action type : $actionType");
        }
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $this->gamestate->nextPrivateState($pId, $nextState);
    }
 
}
