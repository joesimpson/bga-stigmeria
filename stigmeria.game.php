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
  * stigmeria.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


$swdNamespaceAutoload = function ($class) {
    $classParts = explode('\\', $class);
    if ($classParts[0] == 'STIG') {
      array_shift($classParts);
      $file = dirname(__FILE__) . '/modules/php/' . implode(DIRECTORY_SEPARATOR, $classParts) . '.php';
      if (file_exists($file)) {
        require_once $file;
      } else {
        var_dump('Cannot find file : ' . $file);
      }
    }
};
spl_autoload_register($swdNamespaceAutoload, true, true);
  
require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );

use STIG\Managers\Players;
use STIG\Core\Globals;
use STIG\Core\Preferences;
use STIG\Core\Stats;
use STIG\Exceptions\UserException;
use STIG\Managers\Schemas;
use STIG\Managers\Tokens;

class Stigmeria extends Table
{
    use STIG\DebugTrait;
    use STIG\States\CentralLandTrait;
    use STIG\States\CentralMoveTrait;
    use STIG\States\ChoiceTokenToLandTrait;
    use STIG\States\ChoiceTokenToMoveTrait;
    use STIG\States\ChoiceTokenDiagonalTrait;
    use STIG\States\EndRoundTrait;
    use STIG\States\NextTurnTrait;
    use STIG\States\NextRoundTrait;
    use STIG\States\PlayerDiceTrait;
    use STIG\States\PlayerTurnTrait;
    use STIG\States\PlayerTurnCommonBoardTrait;
    use STIG\States\PlayerTurnPersonalBoardTrait;
    use STIG\States\ScoringTrait;
    use STIG\States\SetupTrait;
    use STIG\States\SpecialActionTrait;
    use STIG\States\SpecialFastMoveTrait;
    use STIG\States\SpecialMergeTrait;
    use STIG\States\SpecialSwapTrait;
    use STIG\States\SpecialWhiteTrait;
    use STIG\States\SpecialBlackTrait;
    use STIG\States\WindEffectTrait;
    use STIG\States\WindGenerationTrait;

    public static $instance = null;
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        self::$instance = $this;
        
        self::initGameStateLabels( array( 
            'logging' => 10,
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );        
	}
    public static function get()
    {
      return self::$instance;
    }
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "stigmeria";
    }	

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        // !! We must only return informations visible by this player !!
        $current_player_id = self::getCurrentPId();
        // Gather all information about current game situation (visible by player $current_player_id).
        $firstPlayer = (Globals::isModeCompetitive() ? Globals::getFirstPlayer() : null);
        return [
          'version'=> intval($this->gamestate->table_globals[BGA_GAMESTATE_GAMEVERSION]),
          'prefs' => Preferences::getUiData($current_player_id),
          'players' => Players::getUiData($current_player_id),
          'tokens' => Tokens::getUiData($current_player_id),
          'turn' => Globals::getTurn(),
          'firstPlayer' => $firstPlayer,
          'winds' => Globals::getAllWindDir(),
          'nocb' => Globals::isModeNoCentralBoard(),
          'jokerMode' => Globals::getOptionJokers(),
          'schema' => Schemas::getCurrentSchema()->id,
          'schemas' => Schemas::getUiData(),
        ];
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $nbRounds = Globals::getNbRounds(); 
        if($nbRounds == 0 ) $nbRounds =1;
        $round = Globals::getRound();
        $turn = Globals::getTurn();
        $turnMax = TURN_MAX;
        //TODO JSA MANAGE rounds WITH more than 10 turns ?
        $currentRoundProgression = ($turn-1) / $turnMax;
        $progress = ($round-1)/$nbRounds + 1/$nbRounds * $currentRoundProgression;
        $progress = min($progress, 100);
        return $progress * 100;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */
    /**
    * Check Server version to compare with client version : throw an error in case it 's not the same
    * From https://en.doc.boardgamearena.com/BGA_Studio_Cookbook#Force_players_to_refresh_after_new_deploy
    */
    public function checkVersion(int $clientVersion): void
    {
        if ($clientVersion != intval($this->gamestate->table_globals[BGA_GAMESTATE_GAMEVERSION])) {
            throw new UserException('!!!checkVersion');
        }
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in stigmeria.action.php)
    */

    /*
    
    Example:

    function playCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'playCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        // Add your game logic to play a card there 
        ...
        
        // Notify all players about the card played
        self::notifyAllPlayers( "cardPlayed", clienttranslate( '${player_name} plays ${card_name}' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'card_name' => $card_name,
            'card_id' => $card_id
        ) );
          
    }
    
    */

    
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    /*
    
    Example for game state "MyGameState":
    
    function argMyGameState()
    {
        // Get some values from the current game situation in database...
    
        // return values:
        return array(
            'variable1' => $value1,
            'variable2' => $value2,
            ...
        );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    /*
    
    Example for game state "MyGameState":

    function stMyGameState()
    {
        // Do some stuff ...
        
        // (very often) go to another gamestate
        $this->gamestate->nextState( 'some_gamestate_transition' );
    }    
    */

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
    
    /////////////////////////////////////////////////////////////
    // Exposing protected methods, please use at your own risk //
    /////////////////////////////////////////////////////////////

    // Exposing protected method getCurrentPlayerId
    public static function getCurrentPId()
    {
        return self::getCurrentPlayerId();
    }

    // Exposing protected method translation
    public static function translate($text)
    {
        return self::_($text);
    }
}
