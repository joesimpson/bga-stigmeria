<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\Stats;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\GridUtils;
use STIG\Managers\PlayerActions;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

trait SpecialFulguranceTrait
{
    public function argSpFulgurance($player_id)
    {
        $spots = $this->listSpotsForFulgurance($player_id);
        return [
            'p_places_p' => $spots,
        ];
    }
    
    /**
     * Special action of selecting a position for a new token followed by 4 tokens
     * @param int $row COORD of new white token
     * @param int $column COORD of new white token
     */
    public function actFulgurance($row, $column)
    {
        self::checkAction( 'actFulgurance' ); 
        self::trace("actFulgurance($row, $column)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($player->id, $player->getPrivateState());
 
        $actionType = ACTION_TYPE_FULGURANCE;
        $playerAction = PlayerActions::getPlayer($pId,[$actionType])->first();
        if(!isset($playerAction)){
            throw new UnexpectedException(404,"Not found player action $actionType for $pId");
        }
        if(!$playerAction->canBePlayed($player->countRemainingPersonalActions())){
            throw new UnexpectedException(10,"Not enough actions to do that");
        }
        $actionCost = $playerAction->getCost();
        if( Tokens::countDeck($pId)< FULGURANCE_NB_TOKENS ){
            throw new UnexpectedException(153,"You cannot play Fulgurance now");
        }
        $boardTokens = Tokens::getAllOnPersonalBoard($pId);
        if(!$this->canPlayFulgurance($row, $column,$boardTokens)){
            throw new UnexpectedException(153,"You cannot play Fulgurance there");
        }

        //EFFECT
        Notifications::spFulgurance($player, FULGURANCE_NB_TOKENS, $actionCost);
        $tokens = Tokens::getTopOf(TOKEN_LOCATION_PLAYER_DECK.$pId, FULGURANCE_NB_TOKENS);
        $k = 0;
        foreach($tokens as $token){
            $token->moveToPlayerBoard($player,$row,$column + $k,0);
            $k++;
        }
        Tokens::shuffleBag($pId);

        $player->incNbPersonalActionsDone($actionCost);
        Notifications::useActions($player);
        $player->giveExtraTime();
        Stats::inc("actions_s".ACTION_TYPE_FULGURANCE,$pId);
        Stats::inc("actions",$pId);

        $this->addCheckpoint(ST_TURN_PERSONAL_BOARD,$pId);

        $this->gamestate->nextPrivateState($pId, 'next');
    }
 
    /**
     * @param int $playerId
     * @return array List of possible spaces. Example [[ 'row' => 1, 'col' => 5 ],]
     */
    public function listSpotsForFulgurance($playerId){
        $spots = [];
        $boardTokens = Tokens::getAllOnPersonalBoard($playerId);
        for($row = ROW_MIN; $row <=ROW_MAX; $row++ ){
            for($column = COLUMN_MIN; $column <=COLUMN_MAX; $column++ ){
                if($this->canPlayFulgurance($row,$column,$boardTokens)){
                    $spots[] = [ 'row' => $row, 'col' => $column ];
                }
            }
        }
        return $spots;
    }
    
    /**
     * @param int $row COORD of new token 
     * @param int $column COORD of new token
     * @param Collection $boardTokens 
     * @return bool + TRUE if this position can hold a new token followed by 4 tokens on the right
     *  + FALSE otherwise
     */
    public function canPlayFulgurance($row, $column,$boardTokens){ 
        if( ! $this->canPlaceOnPlayerBoard($boardTokens,$row, $column)) return false;

        //COUNT EMPTY spaces on the right :
        $startingCell = [ 'x' => $column, 'y' => $row, ];
        $costCallback = function ($source, $target, $d) use ($boardTokens) {
            $existingToken = Tokens::findTokenOnBoardWithCoord($boardTokens,$target['y'], $target['x']);
            if(isset($existingToken)) return 10000;//not valid position
            return 1;
        };
        $cellsMarkers = GridUtils::getReachableCellsAtDistance($startingCell,FULGURANCE_NB_TOKENS - 1, $costCallback);
        $cells = $cellsMarkers[0];
        $possibleMoveIndex = GridUtils::searchCell($cells, $column + FULGURANCE_NB_TOKENS - 1, $row);
        if ($possibleMoveIndex === false) {
            return false;
        }

        return true;
    }

 
}
