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
 * gameoptions.inc.php
 *
 * Stigmeria game options description
 *
 */

namespace STIG;

require_once 'modules/php/constants.inc.php';

$game_options = [

  OPTION_MODE => array(
    'name' => totranslate('Game mode'),    
    'values' => array(
                OPTION_MODE_DISCOVERY => array( 
                  'name' => totranslate('Discovery'), 
                  'description' => totranslate('Learn to play progressively. Reach the goal in 10 turns or more.'), 
                  'tmdisplay' => totranslate('Discovery'),
                  'firstgameonly' => true, 
                  ),
                  
                OPTION_MODE_NORMAL => array( 
                  'name' => totranslate('Normal'), 
                  'description' => totranslate('Learn to play progressively. Reach the goal in 10 turns.'), 
                  'tmdisplay' => totranslate('Normal'),
                  ),
                
                OPTION_MODE_COMPETITIVE => array( 
                  'name' => totranslate('Competitive'), 
                  'description' => totranslate('Compete with others to control the StigmaReine (central board). Reach the goal in 10 turns.'),
                  'tmdisplay' => totranslate('Competitive'),
                  'nobeginner' => true, 
                  ),
                OPTION_MODE_NOLIMIT => array( 
                  'name' => totranslate('No Limit'), 
                  'description' => totranslate('Compete with others to control the StigmaReine (central board). Reach the goal in 10 turns or more. Unleash the wind power. All actions are possible.'), 
                  'tmdisplay' => totranslate('No Limit'),
                  'nobeginner' => true, 
                  'premium' => true,
                  ),
            ),
    'default' => OPTION_MODE_NORMAL,
  ),
  OPTION_FLOWER => array(
    'name' => totranslate('Flower type'),    
    'values' => array(
                OPTION_FLOWER_VERTIGHAINEUSE => array( 
                  'name' => totranslate('VertigHaineuse'), 
                  'description' => totranslate('Choose it for your first game ! Green, violet, and orange petals.'), 
                  'tmdisplay' => totranslate('VertigHaineuse'),
                ),
                OPTION_FLOWER_MARONNE => array( 
                  'name' => totranslate('MarOnne'), 
                  'description' => totranslate('Brown petals.'), 
                  'tmdisplay' => totranslate('MarOnne'),
                ),
                OPTION_FLOWER_SIFFLOCHAMP => array( 
                  'name' => totranslate('SiffloChamp'), 
                  'description' => totranslate('Black and white petals.'), 
                  'tmdisplay' => totranslate('SiffloChamp'),
                ),
                OPTION_FLOWER_DENTDINE => array( 
                  'name' => totranslate('DentDîne'), 
                  'description' => totranslate('Pink petals and permanent moves.'), 
                  'tmdisplay' => totranslate('DentDîne'),
                ),
                OPTION_FLOWER_INSPIRACTRICE => array( 
                  'name' => totranslate('InspirActrice'), 
                  'description' => totranslate('Each variety of petals.'), 
                  'tmdisplay' => totranslate('InspirActrice'),
                ),
                OPTION_FLOWER_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random flower'), 
                  'tmdisplay' => totranslate('Random flower'),
                ),
            ),
    'default' => OPTION_FLOWER_VERTIGHAINEUSE,
    /*
    'displaycondition'=> [
      [
        "type"=> "otheroptionisnot ",
        "id"=> OPTION_MODE,
        "value"=> [ 4 ],
      ],
    ],
    */
  ),
  
  OPTION_DIFFICULTY => array(
    'name' => totranslate('Difficulty'),    
    'values' => array(
                OPTION_DIFFICULTY_1 => array( 
                  'name' => totranslate('1 Star'), 
                  'description' => totranslate('Normal'), 
                  'tmdisplay' => totranslate('1 Star'),
                ),
                OPTION_DIFFICULTY_2 => array( 
                  'name' => totranslate('2 Stars'), 
                  'description' => totranslate('Difficult'), 
                  'tmdisplay' => totranslate('2 Stars'),
                ),
                OPTION_DIFFICULTY_3 => array( 
                  'name' => totranslate('3 Stars'), 
                  'description' => totranslate('Very difficult'), 
                  'tmdisplay' => totranslate('3 Stars'),
                ),
                OPTION_DIFFICULTY_4 => array( 
                  'name' => totranslate('4 Stars'), 
                  'description' => totranslate('No Limit'), 
                  'tmdisplay' => totranslate('4 Stars'),
                  //TODO JSA FILTER NO LIMIT ?
                ),
                OPTION_DIFFICULTY_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random difficulty'), 
                  'tmdisplay' => totranslate('Random difficulty'),
                ),
            ),
    'default' => OPTION_DIFFICULTY_1,
  ),
  
  OPTION_SCHEMA_V => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_1 => array( 
                  'name' => '1', 
                  'description' => '1', 
                  'tmdisplay' => '1',
                ),
                OPTION_SCHEMA_2 => array( 
                  'name' => '2', 
                  'description' => '2', 
                  'tmdisplay' => '2',
                ),
                OPTION_SCHEMA_3 => array( 
                  'name' => '3', 
                  'description' => '3', 
                  'tmdisplay' => '3',
                ),
                OPTION_SCHEMA_4 => array( 
                  'name' => '4', 
                  'description' => '4', 
                  'tmdisplay' => '4',
                ),
                OPTION_SCHEMA_5 => array( 
                  'name' => '5', 
                  'description' => '5', 
                  'tmdisplay' => '5',
                ),
                OPTION_SCHEMA_6 => array( 
                  'name' => '6', 
                  'description' => '6', 
                  'tmdisplay' => '6',
                ),
            ),
    'default' => OPTION_SCHEMA_1,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_VERTIGHAINEUSE,
        ],
        [
          "type"=> "otheroptionisnot",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_RANDOM,
        ],
      ],
    //"notdisplayedmessage"=> totranslate("Schemas 1->6 available only with VertigHaineuse flower"),
  ),

  
  OPTION_SCHEMA_M => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_7 => array( 
                  'name' => '7', 
                  'description' => '7', 
                  'tmdisplay' => '7',
                ),
                OPTION_SCHEMA_8 => array( 
                  'name' => '8', 
                  'description' => '8', 
                  'tmdisplay' => '8',
                ),
                OPTION_SCHEMA_9 => array( 
                  'name' => '9', 
                  'description' => '9', 
                  'tmdisplay' => '9',
                ),
                OPTION_SCHEMA_10 => array( 
                  'name' => '10', 
                  'description' => '10', 
                  'tmdisplay' => '10',
                ),
                OPTION_SCHEMA_11 => array( 
                  'name' => '11', 
                  'description' => '11', 
                  'tmdisplay' => '11',
                ),
                OPTION_SCHEMA_12 => array( 
                  'name' => '12', 
                  'description' => '12', 
                  'tmdisplay' => '12',
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_MARONNE,
        ],
        [
          "type"=> "otheroptionisnot",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_RANDOM,
        ],
      ],
    //"notdisplayedmessage"=> totranslate("Schemas 7->12 available only with MarOnne flower"),
  ),
];

$game_preferences = [
   
  OPTION_CONFIRM => [
    'name' => totranslate('Turn confirmation'),
    'needReload' => false,
    'values' => [
      OPTION_CONFIRM_TIMER => [
        'name' => totranslate('Enabled with timer'),
      ],
      OPTION_CONFIRM_ENABLED => ['name' => totranslate('Enabled')],
      OPTION_CONFIRM_DISABLED => ['name' => totranslate('Disabled')],
    ],
  ],
];
