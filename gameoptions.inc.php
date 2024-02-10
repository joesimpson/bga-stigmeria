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
    //I make it clearly different from BGA 'Game mode'
    'name' => totranslate('Stigmeria game mode'),    
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
                  'description' => totranslate('Compete with others to control the StigmaReine (central board). Reach the goal in 10 turns or more. Unleash the wind power. All actions will be possible.'), 
                  'tmdisplay' => totranslate('No Limit'),
                  'nobeginner' => true, 
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
                  'name' => '#1', 
                  'description' => '#1', 
                  'tmdisplay' => '#1',
                ),
                OPTION_SCHEMA_2 => array( 
                  'name' => '#2', 
                  'description' => '#2', 
                  'tmdisplay' => '#2',
                ),
                OPTION_SCHEMA_3 => array( 
                  'name' => '#3', 
                  'description' => '#3', 
                  'tmdisplay' => '#3',
                ),
                OPTION_SCHEMA_4 => array( 
                  'name' => '#4', 
                  'description' => '#4', 
                  'tmdisplay' => '#4',
                ),
                OPTION_SCHEMA_5 => array( 
                  'name' => '#5', 
                  'description' => '#5', 
                  'tmdisplay' => '#5',
                ),
                OPTION_SCHEMA_6 => array( 
                  'name' => '#6', 
                  'description' => '#6', 
                  'tmdisplay' => '#6',
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
                  'name' => '#7', 
                  'description' => '#7', 
                  'tmdisplay' => '#7',
                ),
                OPTION_SCHEMA_8 => array( 
                  'name' => '#8', 
                  'description' => '#8', 
                  'tmdisplay' => '#8',
                ),
                OPTION_SCHEMA_9 => array( 
                  'name' => '#9', 
                  'description' => '#9', 
                  'tmdisplay' => '#9',
                ),
                OPTION_SCHEMA_10 => array( 
                  'name' => '#10', 
                  'description' => '#10', 
                  'tmdisplay' => '#10',
                ),
                OPTION_SCHEMA_11 => array( 
                  'name' => '#11', 
                  'description' => '#11', 
                  'tmdisplay' => '#11',
                ),
                OPTION_SCHEMA_12 => array( 
                  'name' => '#12', 
                  'description' => '#12', 
                  'tmdisplay' => '#12',
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
  OPTION_SCHEMA_S => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_13 => array( 
                  'name' => '#13', 
                  'description' => '#13', 
                  'tmdisplay' => '#13',
                ),
                OPTION_SCHEMA_14 => array( 
                  'name' => '#14', 
                  'description' => '#14', 
                  'tmdisplay' => '#14',
                ),
                OPTION_SCHEMA_15 => array( 
                  'name' => '#15', 
                  'description' => '#15', 
                  'tmdisplay' => '#15',
                ),
                OPTION_SCHEMA_16 => array( 
                  'name' => '#16', 
                  'description' => '#16', 
                  'tmdisplay' => '#16',
                ),
                OPTION_SCHEMA_17 => array( 
                  'name' => '#17', 
                  'description' => '#17', 
                  'tmdisplay' => '#17',
                ),
                OPTION_SCHEMA_18 => array( 
                  'name' => '#18', 
                  'description' => '#18', 
                  'tmdisplay' => '#18',
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_SIFFLOCHAMP,
        ],
        [
          "type"=> "otheroptionisnot",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_RANDOM,
        ],
      ],
  ),
  
  OPTION_SCHEMA_D => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_19 => array( 
                  'name' => '#19', 
                  'description' => '#19', 
                  'tmdisplay' => '#19',
                ),
                OPTION_SCHEMA_20 => array( 
                  'name' => '#20', 
                  'description' => '#20', 
                  'tmdisplay' => '#20',
                ),
                OPTION_SCHEMA_21 => array( 
                  'name' => '#21', 
                  'description' => '#21', 
                  'tmdisplay' => '#21',
                ),
                OPTION_SCHEMA_22 => array( 
                  'name' => '#22', 
                  'description' => '#22', 
                  'tmdisplay' => '#22',
                ),
                OPTION_SCHEMA_23 => array( 
                  'name' => '#23', 
                  'description' => '#23', 
                  'tmdisplay' => '#23',
                ),
                OPTION_SCHEMA_24 => array( 
                  'name' => '#24', 
                  'description' => '#24', 
                  'tmdisplay' => '#24',
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_DENTDINE,
        ],
        [
          "type"=> "otheroptionisnot",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_RANDOM,
        ],
      ],
  ),
  
  OPTION_SCHEMA_I => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_25 => array( 
                  'name' => '#25', 
                  'description' => '#25', 
                  'tmdisplay' => '#25',
                ),
                OPTION_SCHEMA_26 => array( 
                  'name' => '#26', 
                  'description' => '#26', 
                  'tmdisplay' => '#26',
                ),
                OPTION_SCHEMA_27 => array( 
                  'name' => '#27', 
                  'description' => '#27', 
                  'tmdisplay' => '#27',
                ),
                OPTION_SCHEMA_28 => array( 
                  'name' => '#28', 
                  'description' => '#28', 
                  'tmdisplay' => '#28',
                ),
                OPTION_SCHEMA_29 => array( 
                  'name' => '#29', 
                  'description' => '#29', 
                  'tmdisplay' => '#29',
                ),
                OPTION_SCHEMA_30 => array( 
                  'name' => '#30', 
                  'description' => '#30', 
                  'tmdisplay' => '#30',
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_INSPIRACTRICE,
        ],
        [
          "type"=> "otheroptionisnot",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_RANDOM,
        ],
      ],
  ),
  
  OPTION_SCHEMA_RANDOM_ONLY_1 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    //OR doesn't work ? => so I need 2 identical lists  
    //'displayconditionoperand ' => 'OR',
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_RANDOM,
        ],
        [
          "type"=> "otheroptionisnot",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_RANDOM,
        ],
      ],
  ),
  
  OPTION_SCHEMA_RANDOM_ONLY_2 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_RANDOM,
        ],
      ],
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
