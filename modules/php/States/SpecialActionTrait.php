<?php

namespace STIG\States;

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
        if($flowerType == OPTION_FLOWER_VERTIGHAINEUSE && $remaining >=ACTION_COST_MERGE){
            $actions[] = ACTION_TYPE_MERGE;
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
            default:
                throw new UnexpectedException(14,"Not supported action type : $actionType");
        }
        if($player->countRemainingPersonalActions() < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $this->gamestate->nextPrivateState($pId, $nextState);
    }
 
}
