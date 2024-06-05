<?php

namespace STIG\States;

use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Exceptions\UnexpectedException;
use STIG\Helpers\GridUtils;
use STIG\Managers\Players;
use STIG\Managers\Tokens;
use STIG\Models\StigmerianToken;

/**
 * Actions about the rule : "Place a token on A5 when the central board is empty"
 */
trait CentralA5Trait
{
    /**
     * @param int $playerId
     */
    public function argCentralA5($playerId)
    {
        $possibles = $this->getListPossibleTokensForCentralA5();
        //A5
        $coordName = GridUtils::getCoordName(FIRST_TOKEN_ROW,FIRST_TOKEN_COLUMN);
        $args  = [
          'L' => $coordName,
          'p' => $possibles,
        ];
        $this->addArgsForUndo($playerId, $args);
        return $args;
    }
      
    /**
     * @param int $typeSource
     */
    public function actCentralA5($typeSource)
    {
        self::checkAction( 'actCentralA5' ); 
        self::trace("actCentralA5($typeSource)");
        
        $player = Players::getCurrent();
        $pId = $player->id;
        $this->addStep($pId, $player->getPrivateState());

        $possibles = $this->getListPossibleTokensForCentralA5();
        if(!in_array($typeSource, $possibles)){
            throw new UnexpectedException(11,"You cannot select this color $typeSource, see ".json_encode($possibles));
        }
        $nbExistingToken = Tokens::countOnCentralBoard(FIRST_TOKEN_ROW,FIRST_TOKEN_COLUMN);
        if($nbExistingToken == 0){//SANITY CHeck

            //EFFECT
            $token = Tokens::getAllCentralRecruits()->filter(function($token) use ($typeSource) {
                return $typeSource == $token->getType();
            })->first();
            if(isset($token)){
                //Move central Recruit
                $token->moveToCentralBoard($player,FIRST_TOKEN_ROW,FIRST_TOKEN_COLUMN,0,false);
            } else {
                //CREATE TOKEN IF not from recruits
                $token = Tokens::createToken([
                    'type' => $typeSource,
                    'location' => TOKEN_LOCATION_CENTRAL_BOARD,
                    'y' => FIRST_TOKEN_ROW,
                    'x' => FIRST_TOKEN_COLUMN,
                ]);
            }
            Notifications::firstToken($player,$token);
        }
        else {//Should not happen except in case of multiple actions by opponents (Last drift ?)
            self::trace("actCentralA5($typeSource) : A5 is already occupied on central board !");
        }
        
        PGlobals::setState($player->id, ST_TURN_COMMON_BOARD);
        $this->gamestate->nextPrivateState($pId, 'next');
    }

    /**
     * @return array of int : possible tokens types to place on A5 with this action
     */
    public function getListPossibleTokensForCentralA5(){
        
        //colors from StigmaReine recruits
        $possibles = Tokens::getAllCentralRecruits()->map(function($token) {
            return $token->getType();
        })->toArray();
        if(count($possibles)==0){
            $possibles = STIG_PRIMARY_COLORS;
        }
        return $possibles;
    }
     
}
