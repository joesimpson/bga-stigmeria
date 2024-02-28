<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait CentralLandTrait
{
    public function argCentralChoiceTokenToLand($player_id)
    {
        //$player = Players::get($player_id);
        $tokens = Tokens::getAllCentralTokensToPlace();
        return [
            'n' => ACTION_COST_CENTRAL_LAND,
            'tokens' => $tokens->ui(),
            'p_places_p' => $this->listPossiblePlacesOnCentralBoard(),
        ];
    }
      
    /**
     * Central Action 1 : landing a stigmerian on central board
     * @param int $token_id
     * @param int $row
     * @param int $column
     */
    public function actCentralLand($token_id, $row, $column)
    {
        self::checkAction( 'actCentralLand' ); 
        self::trace("actCentralLand($token_id, $row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($pId, $player->getPrivateState());

        $remaining = $player->countRemainingCommonActions();
        $actionCost = ACTION_COST_CENTRAL_LAND;
        if($remaining < $actionCost){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $token = Tokens::get($token_id);
        if($token->location != TOKEN_LOCATION_CENTRAL_RECRUIT_TOPLACE ){
            throw new UnexpectedException(20,"You cannot place this token");
        }
        $boardTokens = Tokens::getAllOnCentralBoard();
        if(!$this->canPlaceOnCentralBoard($boardTokens,$row, $column)){
            throw new UnexpectedException(30,"You cannot place this token at $row, $column");
        }

        $player->incNbCommonActionsDone($actionCost);
        Notifications::useActions($player);
        Stats::inc("actions_c1",$player->getId());

        //EFFECT : PLACE the TOKEN 
        $token->moveToCentralBoard($player,$row,$column,$actionCost);
        
        $this->checkGainSpecialAction($player,$token, "continue", ST_TURN_COMMON_BOARD);
    }

    /**
     * @return array List of possible spaces. Example [[ 'row' => 1, 'col' => 5 ],]
     */
    public function listPossiblePlacesOnCentralBoard(){
        $spots = [];
        $boardTokens = Tokens::getAllOnCentralBoard();
        for($row = ROW_MIN; $row <=ROW_MAX; $row++ ){
            for($column = COLUMN_MIN; $column <=COLUMN_MAX; $column++ ){
                if($this->canPlaceOnCentralBoard($boardTokens,$row, $column)){
                    $spots[] = [ 'row' => $row, 'col' => $column ];
                }
            }
        }
        return $spots;
    }
     
    /**
     * @param Collection $boardTokens 
     * @param int $row
     * @param int $column
     * @return bool TRUE if a token can be placed on central board ( Empty spot + Either Line A or adjacent to another token),
     *  FALSE otherwise
     */
    public function canPlaceOnCentralBoard($boardTokens,$row, $column)
    {
        return $this->canPlaceOnPlayerBoard($boardTokens,$row, $column, true);
    }
}
