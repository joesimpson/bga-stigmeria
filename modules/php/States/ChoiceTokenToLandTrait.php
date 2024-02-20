<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait ChoiceTokenToLandTrait
{
    public function argChoiceTokenToLand($player_id)
    {
        //$player = Players::get($player_id);
        $tokens = Tokens::getAllRecruits($player_id);
        return [
            'n' => ACTION_COST_LAND,
            'tokens' => $tokens->ui(),
            'p_places_p' => $this->listPossiblePlacesOnPersonalBoard($player_id),
        ];
    }
      
    public function actCancelChoiceTokenToLand()
    {
        self::checkAction( 'actCancelChoiceTokenToLand' ); 
        self::trace("actCancelChoiceTokenToLand()");
        
        $player = Players::getCurrent();

        //TODO JSA CHECK STATE  ST_TURN_CENTRAL_CHOICE_TOKEN_LAND cannot cancel if token revealed

        //NOTHING TO CANCEL In BDD, return to previous state

        $this->gamestate->nextPrivateState($player->id, "cancel");
    }
    /**
     * Basic Action 2 : landing a stigmerian on your board
     * @param int $token_id
     * @param int $row
     * @param int $column
     */
    public function actChoiceTokenToLand($token_id, $row, $column)
    {
        self::checkAction( 'actChoiceTokenToLand' ); 
        self::trace("actChoiceTokenToLand($token_id)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep( $player->id, $player->getPrivateState());

        $remaining = $player->countRemainingPersonalActions();
        $actionCost = 1;

        //CHECK REMAINING ACTIONS VS cost
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }

        //TODO JSA replace param with token type, in order to select a type in possible list (types in recruit)
        $token = Tokens::get($token_id);
        if($token->pId != $pId || $token->location != TOKEN_LOCATION_PLAYER_RECRUIT ){
            throw new UnexpectedException(20,"You cannot place this token");
        }
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canPlaceOnPlayerBoard($boardTokens,$row, $column)){
            throw new UnexpectedException(30,"You cannot place this token at $row, $column");
        }

        
        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();

        //EFFECT : PLACE the TOKEN 
        $token->moveToPlayerBoard($player,$row,$column,$actionCost);
        Stats::inc("tokens_recruit",$pId, -1);
        Stats::inc("actions_2",$player->getId());
        Stats::inc("actions",$player->getId());

        $this->gamestate->nextPrivateState($player->id, "continue");
    }

    /**
     * @param int $playerId
     * @return array List of possible spaces. Example [[ 'row' => 1, 'col' => 5 ],]
     */
    public function listPossiblePlacesOnPersonalBoard($playerId){
        $spots = [];
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        for($row = ROW_MIN; $row <=ROW_MAX; $row++ ){
            for($column = COLUMN_MIN; $column <=COLUMN_MAX; $column++ ){
                if($this->canPlaceOnPlayerBoard($boardTokens,$row, $column)){
                    $spots[] = [ 'row' => $row, 'col' => $column ];
                }
            }
        }
        return $spots;
    }

     
}
