<?php

/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * Stigmeria implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 * 
 * states.inc.php
 *
 * Stigmeria game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!
/*
    "Visual" States Diagram :

        SETUP
        |
        v
        nextRound
        ^       |
        |       v
        |       generateWind <------\
        |       |    |              |
        |       |    v              /
 /<-endRound    |   playerDice  ----
 |      ^       | 
 |      |       v
 |      \<----- nextTurn   <-------------------------------------------\
 |                |                                                    |
 |                v                                                    |
 |              playerTurn                                             |
 |              |    |          |                                      |
 |              |    v          v                                      |
 |              |commonBoard personalBoard                             |
 |              |    |          |                                      |
 |              v    v          v                                      |
 |               --------------------\                                 |
 |                                   |                                 |
 |                                   v                                 |
 |                                 windEffect -------------------------/
 v        
 \-> endGameScoring
        | 
        v
        preEndOfGame
        | 
        v
        END
*/

require_once 'modules/php/constants.inc.php';
 
$machinestates = array(

    // The initial state. Please do not modify.
    ST_GAME_SETUP => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => ST_NEXT_ROUND )
    ),
    
    //PREPARE States machine for future with a Challenge made of several rounds
    ST_NEXT_ROUND => array(
        "name" => "newRound",
        "description" => clienttranslate('Preparing new round'),
        "type" => "game",
        "action" => "stNewRound",
        "transitions" => [ 
            "next" => ST_GENERATE_WIND,
        ],
    ),

    ST_GENERATE_WIND => array(
        "name" => "generateWind",
        "description" => clienttranslate('Computing Wind direction'),
        "type" => "game",
        "action" => "stGenerateWind",
        "transitions" => [ 
            "playerDice" => ST_PLAYER_DICE,
            "next" => ST_NEXT_TURN,
        ],
    ),    

    ST_PLAYER_DICE => array(
        "name" => "playerDice",
        "description" => clienttranslate('${actplayer} must choose a dice result'),
        "descriptionmyturn" => clienttranslate('${you} must choose a dice result'),
        "type" => "activeplayer",
        "possibleactions" => ["actChooseDice", "actRerollDice", ],
        "transitions" => [ 
            "nextDice" => ST_GENERATE_WIND, 
            "windEffect" => ST_WIND_EFFECT,
        ],
    ),

    ST_NEXT_TURN => array(
        "name" => "nextTurn",
        "description" => clienttranslate('Next turn'),
        "type" => "game",
        "action" => "stNextTurn",
        "transitions" => [ 
            "next" => ST_PLAYER_TURN,
            "end" => ST_END_ROUND,
        ],
    ),
   
    ST_PLAYER_TURN => array(
        "name" => "playerTurn",
        "description" => clienttranslate('Players may play actions or pass'),
        "descriptionmyturn" => ('${you} may play actions or pass'), // Won't be displayed anyway since each private state has its own description
        "type" => "multipleactiveplayer",
        "initialprivate" => ST_TURN_COMMON_BOARD,// This makes this state a master multiactive state and enables private states
        "action" => "stPlayerturn",
        "args" => "argPlayerTurn",
        "possibleactions" => [ 
            //this actions are possible if player is not in any private state which usually happens when they are inactive
        ],
        "transitions" => [ 
            "end" => ST_WIND_EFFECT,
        ],
    ),
    
    ST_TURN_COMMON_BOARD => [
        "name" => "commonBoardTurn",
        "descriptionmyturn" => clienttranslate('${you} must play ${n} actions on the common board'), 
        "type" => "private", // this state is reachable only as a private state
        "args" => "argCommonBoardTurn",
        "action" => "stCommonBoardTurn",
        "possibleactions" => [
            "actCommonDrawAndLand",
            "actCommonMove",
            "actCommonJoker",
            "actGoToNext",
        ],
        "transitions" => [
            'continue' => ST_TURN_COMMON_BOARD,
            'next' => ST_TURN_PERSONAL_BOARD,
            'startLand' => ST_TURN_CENTRAL_CHOICE_TOKEN_LAND,
        ],
    ],
    
    ST_TURN_CENTRAL_CHOICE_TOKEN_LAND => [
        "name" => "centralChoiceTokenToLand",
        "descriptionmyturn" => clienttranslate('${you} must choose a token to place (cost : ${n} actions)'), 
        "type" => "private",
        "args" => "argCentralChoiceTokenToLand",
        "possibleactions" => [
            "actCentralLand",
            //No cancel because token is revealed ?
            //"actCancelChoiceTokenToLand",
        ],
        "transitions" => [
            'continue' => ST_TURN_COMMON_BOARD,
            //'cancel' => ST_TURN_COMMON_BOARD,
        ],
    ],
    
    ST_TURN_PERSONAL_BOARD => [
        "name" => "personalBoardTurn",
        "descriptionmyturn" => clienttranslate('${you} may play ${n} actions on your board or pass'), 
        "type" => "private", // this state is reachable only as a private state
        "args" => "argPersonalBoardTurn",
        "action" => "stPersonalBoardTurn",
        "possibleactions" => [
            "actDraw",
            "actLand",
            "actMove",
            //TODO JSA ALL ACTIONS
            "actJoker",
            "actPass",
            "actLetNextPlay",
            "actEndTurn",
            "actBackToCommon",
        ],
        "transitions" => [
            'continue' => ST_TURN_PERSONAL_BOARD,
            'back' => ST_TURN_COMMON_BOARD,
            'startLand' => ST_TURN_CHOICE_TOKEN_LAND,
            'startMove' => ST_TURN_CHOICE_TOKEN_MOVE,
        ],
    ],
    
    ST_TURN_CHOICE_TOKEN_LAND => [
        "name" => "choiceTokenToLand",
        "descriptionmyturn" => clienttranslate('${you} must choose a token to place (cost : ${n} actions)'), 
        "type" => "private",
        "args" => "argChoiceTokenToLand",
        "possibleactions" => [
            "actChoiceTokenToLand",
            "actCancelChoiceTokenToLand",
        ],
        "transitions" => [
            'continue' => ST_TURN_PERSONAL_BOARD,
            'cancel' => ST_TURN_PERSONAL_BOARD,
        ],
    ],
    
    ST_TURN_CHOICE_TOKEN_MOVE => [
        "name" => "choiceTokenToMove",
        "descriptionmyturn" => clienttranslate('${you} must choose a token to move (cost : ${n} actions)'), 
        "type" => "private",
        "args" => "argChoiceTokenToMove",
        "possibleactions" => [
            "actChoiceTokenToMove",
            "actCancelChoiceTokenToMove",
        ],
        "transitions" => [
            'continue' => ST_TURN_PERSONAL_BOARD,
            'cancel' => ST_TURN_PERSONAL_BOARD,
        ],
    ],
    
    ST_WIND_EFFECT => array(
        "name" => "windEffect",
        "description" => clienttranslate('Wind blows'),
        "type" => "game",
        "action" => "stWindEffect",
        "transitions" => [ 
            "next" => ST_NEXT_TURN,
            "playerDice" => ST_PLAYER_DICE,
        ],
    ),
    ST_END_ROUND => array(
        "name" => "endRound",
        "description" => clienttranslate('Ending round'),
        "type" => "game",
        "action" => "stEndRound",
        "transitions" => [ 
            "next" => ST_NEXT_ROUND,
            "end" => ST_END_SCORING,
        ],
    ),
    ST_END_SCORING => array(
        "name" => "scoring",
        "description" => clienttranslate('Scoring'),
        "type" => "game",
        "action" => "stScoring",
        "transitions" => [ 
            "next" => ST_PRE_END_OF_GAME,
        ],
    ),
    ST_PRE_END_OF_GAME => array(
        "name" => "preEndOfGame",
        "description" => '',
        "type" => "game",
        "action" => "stPreEndOfGame",
        "transitions" => [ 
            "next" => ST_END_GAME,
        ],
    ),
   
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    ST_END_GAME => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



