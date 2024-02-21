<?php

namespace STIG\States;

use STIG\Core\Globals;
use STIG\Core\Notifications;
use STIG\Core\PGlobals;
use STIG\Exceptions\UnexpectedException;
use STIG\Managers\Players;
use STIG\Managers\Tokens;

trait GiveTokensTrait
{
    public function argGiveTokens($playerId)
    {
        $player = Players::get($playerId);
        $alignedTokens =array_unique( $player->getSelection());
        $args = [
            'tokens' => $alignedTokens,
        ];
        $this->addArgsForUndo($playerId, $args);
        return $args;
    }
      
    /**
     * @param array $tokensArray
     * @param int $playerDestinationId id of who to give to
     */
    public function actGiveTokens($tokensArray, $playerDestinationId)
    {
        self::checkAction( 'actGiveTokens' ); 
        self::trace("actGiveTokens($playerDestinationId)".json_encode($tokensArray));
        $player = Players::getCurrent();
        $pId = $player->id;
        
        $possibleTokenIds = $player->getSelection();
        if(count($tokensArray) >0){
            $this->addStep( $pId, $player->getPrivateState());
            $playerDestination = Players::get($playerDestinationId);
            if(!isset($playerDestination)) {
                throw new UnexpectedException(404, "Unknow target player");
            }
            $tokens = Tokens::getMany($tokensArray);
            foreach($tokens as $tokenId => $token){
                if(!in_array($tokenId, $possibleTokenIds)){
                    throw new UnexpectedException(405, "You cannot select this token now");
                }
                $token->moveToPlayerBag($player,$playerDestination);
            }
            //update selection
            $possibleTokenIds = array_filter($possibleTokenIds, static function ($element) use ($tokensArray) {
                return !in_array($element, $tokensArray);
            });
            if($playerDestinationId != $player->id && $playerDestination->isMultiactive()){
                //We cannot cancel if another gets tokens and can play them (could happen with current rules if the other player has let the current one to play while still active )
                $this->addCheckpoint($player->getPrivateState(), $pId );
            }
        }
        $player->setSelection($possibleTokenIds);
        if(count($possibleTokenIds) >=1){
            $this->gamestate->nextPrivateState($pId, "continue");
            return;
        }

        PGlobals::setState($pId, ST_TURN_COMMON_BOARD);
        $this->gamestate->nextPrivateState($pId, "next");
    }
 
}
