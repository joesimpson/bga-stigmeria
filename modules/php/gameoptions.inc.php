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
 * NB : 11/2023 new JSON format you can generate it from this file with PHP : 
 * call the debug function from chat :
 *    debugJSON()
 *
 */

namespace STIG;

//if placed at root folder
//require_once 'modules/php/constants.inc.php';
//Else near constants :
require_once 'constants.inc.php';

$game_options = [

  OPTION_MODE => array(
    //I make it clearly different from BGA 'Game mode'
    'name' => totranslate('Specific game mode'),    
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
                  'description' => totranslate('You must have learned normal games ! Compete with others to control the StigmaReine (central board) and unlock special actions. Reach the goal in 10 turns.'),
                  'tmdisplay' => totranslate('Competitive'),
                  'nobeginner' => true, 
                  ),
                OPTION_MODE_NOLIMIT => array( 
                  'name' => totranslate('No Limit'), 
                  'description' => totranslate('You must have learned normal games ! Compete with others to control the StigmaReine (central board) and unlock special actions. Reach the goal in 10 turns or more. Unleash the wind power. All actions will be possible.'), 
                  'tmdisplay' => totranslate('No Limit'),
                  'nobeginner' => true, 
                  ),
                /*  
                OPTION_MODE_CHALLENGE => [
                  'name' => totranslate('Challenge'), 
                  'description' => totranslate('Challenge'), 
                  'tmdisplay' => totranslate('Challenge'),
                  'nobeginner' => true, 
                  'alpha' => true, 
                ],
                */
            ),
    'default' => OPTION_MODE_NORMAL,
    
    'startcondition'=>  [
      OPTION_MODE_DISCOVERY => [
          [
            "type" => "otheroption",
            "id" => OPTION_GAMESTATE_RATING_MODE,
            "value"=> OPTION_GAMESTATE_RATING_MODE_TRAINING,
            "message"=> totranslate("Discovery is available in training only"),
          ],
        ],
      OPTION_MODE_NORMAL => [
          [
            "type" => "otheroption",
            "id" => OPTION_GAMESTATE_RATING_MODE,
            "value"=> OPTION_GAMESTATE_RATING_MODE_TRAINING,
            "message"=> totranslate("Normal is available in training only"),
          ],
        ],
        
      OPTION_MODE_COMPETITIVE => [
        [
          'type' => 'minplayers', 
          'value' => 2, 
          'message' => totranslate('Competitive modes are not for solo play'),
        ],
      ],
      OPTION_MODE_NOLIMIT => [
        [
          'type' => 'minplayers', 
          'value' => 2, 
          'message' => totranslate('Competitive modes are not for solo play'),
        ],
      ],
      /*
      OPTION_MODE_CHALLENGE => [
          [
            'type' => 'minplayers', 
            'value' => 99, 
            'message' => totranslate('Challenge not available in current version'),
          ],
        ],
      */
    ],
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
                OPTION_FLOWER_COMPETITIVE => array( 
                  'name' => totranslate('Competitive'), 
                  'description' => totranslate('Competitive card : designed to be played with competitive game mode.'),  
                  'tmdisplay' => totranslate('Competitive'),
                ),
                OPTION_FLOWER_NO_LIMIT => array( 
                  'name' => totranslate('Competitive No Limit'), 
                  'description' => totranslate('Competitive No Limit card : designed to be played with No Limit game mode.'), 
                  'tmdisplay' => totranslate('Competitive No Limit'),
                ),
                OPTION_FLOWER_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random flower'), 
                  'tmdisplay' => totranslate('Random flower'),
                ),
            ),
    'default' => OPTION_FLOWER_VERTIGHAINEUSE,
    
    'startcondition'=>  [
      OPTION_FLOWER_COMPETITIVE => [
          [
            "type" => "otheroptionisnot",
            "id" => OPTION_MODE,
            "value"=> OPTION_MODE_NORMAL,
            "message"=> totranslate("Competitive flowers cannot be played in normal/discovery mode"),
          ],
          [
            "type" => "otheroptionisnot",
            "id" => OPTION_MODE,
            "value"=> OPTION_MODE_DISCOVERY,
            "message"=> totranslate("Competitive flowers cannot be played in normal/discovery mode"),
          ],
        ], 
      OPTION_FLOWER_NO_LIMIT => [
        [
          "type" => "otheroptionisnot",
          "id" => OPTION_MODE,
          "value"=> OPTION_MODE_NORMAL,
          "message"=> totranslate("Competitive flowers cannot be played in normal/discovery mode"),
        ],
        [
          "type" => "otheroptionisnot",
          "id" => OPTION_MODE,
          "value"=> OPTION_MODE_DISCOVERY,
          "message"=> totranslate("Competitive flowers cannot be played in normal/discovery mode"),
        ],
      ], 
    ],
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
                OPTION_DIFFICULTY_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random difficulty'), 
                  'tmdisplay' => totranslate('Random difficulty'),
                ),
            ),
    'default' => OPTION_DIFFICULTY_1,
    'displaycondition'=> [
        [
          "type"=> "otheroptionisnot",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_NO_LIMIT,
        ],
        [
          "type"=> "otheroptionisnot",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_RANDOM,
        ],
      ],
  ),
  
  OPTION_DIFFICULTY_NL => array(
    'name' => totranslate('Difficulty'),    
    'values' => array(
                OPTION_DIFFICULTY_4 => array( 
                  'name' => totranslate('4 Stars'), 
                  'description' => totranslate('No Limit'), 
                  'tmdisplay' => totranslate('4 Stars'),
                ),
            ),
    'default' => OPTION_DIFFICULTY_4,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_NO_LIMIT,
        ],
      ],
  ),
  
  OPTION_DIFFICULTY_ALL => array(
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
                ),
                OPTION_DIFFICULTY_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random difficulty'), 
                  'tmdisplay' => totranslate('Random difficulty'),
                ),
            ),
    'default' => OPTION_DIFFICULTY_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_RANDOM,
        ],
      ],
  ),
  
  OPTION_SCHEMA_V1 => array(
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
                /*
                OPTION_SCHEMA_2 => array( 
                  'name' => '#2', 
                  'description' => '#2', 
                  'tmdisplay' => '#2',
                ),
                */
            ),
    'default' => OPTION_SCHEMA_1,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_VERTIGHAINEUSE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_1,
        ],
      ],
  ),

  
  OPTION_SCHEMA_V2 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_3 => array( 
                  'name' => '#3', 
                  'description' => '#3', 
                  'tmdisplay' => '#3',
                ),
                /*
                OPTION_SCHEMA_4 => array( 
                  'name' => '#4', 
                  'description' => '#4', 
                  'tmdisplay' => '#4',
                ),
                */
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_VERTIGHAINEUSE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_2,
        ],
      ],
  ),
  
  OPTION_SCHEMA_V3 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                /*
                OPTION_SCHEMA_5 => array( 
                  'name' => '#5', 
                  'description' => '#5', 
                  'tmdisplay' => '#5',
                ),
                */
                OPTION_SCHEMA_6 => array( 
                  'name' => '#6', 
                  'description' => '#6', 
                  'tmdisplay' => '#6',
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_VERTIGHAINEUSE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_3,
        ],
      ],
  ),

  
  OPTION_SCHEMA_M1 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                /*
                OPTION_SCHEMA_7 => array( 
                  'name' => '#7', 
                  'description' => '#7', 
                  'tmdisplay' => '#7',
                ),
                */
                OPTION_SCHEMA_8 => array( 
                  'name' => '#8', 
                  'description' => '#8', 
                  'tmdisplay' => '#8',
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
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_1,
        ],
      ],
  ),
  
  OPTION_SCHEMA_M2 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                /*
                OPTION_SCHEMA_9 => array( 
                  'name' => '#9', 
                  'description' => '#9', 
                  'tmdisplay' => '#9',
                ),
                */
                OPTION_SCHEMA_10 => array( 
                  'name' => '#10', 
                  'description' => '#10', 
                  'tmdisplay' => '#10',
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
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_2,
        ],
      ],
  ),
  
  OPTION_SCHEMA_M3 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_11 => array( 
                  'name' => '#11', 
                  'description' => '#11', 
                  'tmdisplay' => '#11',
                ),
                /*
                OPTION_SCHEMA_12 => array( 
                  'name' => '#12', 
                  'description' => '#12', 
                  'tmdisplay' => '#12',
                ),
                */
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_MARONNE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_3,
        ],
      ],
  ),

  OPTION_SCHEMA_S1 => array(
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
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_SIFFLOCHAMP,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_1,
        ],
      ],
  ),

  OPTION_SCHEMA_S2 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_14 => array( 
                  'name' => '#14', 
                  'description' => '#14', 
                  'tmdisplay' => '#14',
                ),
                /*
                OPTION_SCHEMA_15 => array( 
                  'name' => '#15', 
                  'description' => '#15', 
                  'tmdisplay' => '#15',
                ),
                */
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_SIFFLOCHAMP,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_2,
        ],
      ],
  ),

  OPTION_SCHEMA_S3 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                /*
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
                */
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
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_3,
        ],
      ],
  ),
  
  OPTION_SCHEMA_D1 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                /*
                OPTION_SCHEMA_19 => array( 
                  'name' => '#19', 
                  'description' => '#19', 
                  'tmdisplay' => '#19',
                ),
                */
                OPTION_SCHEMA_20 => array( 
                  'name' => '#20', 
                  'description' => '#20', 
                  'tmdisplay' => '#20',
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
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_1,
        ],
      ],
  ),
  
  OPTION_SCHEMA_D2 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_21 => array( 
                  'name' => '#21', 
                  'description' => '#21', 
                  'tmdisplay' => '#21',
                ),
                /*
                OPTION_SCHEMA_22 => array( 
                  'name' => '#22', 
                  'description' => '#22', 
                  'tmdisplay' => '#22',
                ),
                */
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_DENTDINE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_2,
        ],
      ],
  ),
  
  OPTION_SCHEMA_D3 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_23 => array( 
                  'name' => '#23', 
                  'description' => '#23', 
                  'tmdisplay' => '#23',
                ),
                /*
                OPTION_SCHEMA_24 => array( 
                  'name' => '#24', 
                  'description' => '#24', 
                  'tmdisplay' => '#24',
                ),
                */
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_DENTDINE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_3,
        ],
      ],
  ),
  
  OPTION_SCHEMA_I1 => array(
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
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_INSPIRACTRICE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_1,
        ],
      ],
  ),
  
  OPTION_SCHEMA_I2 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_26 => array( 
                  'name' => '#26', 
                  'description' => '#26', 
                  'tmdisplay' => '#26',
                ),
                /*
                OPTION_SCHEMA_27 => array( 
                  'name' => '#27', 
                  'description' => '#27', 
                  'tmdisplay' => '#27',
                ),
                */
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_INSPIRACTRICE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_2,
        ],
      ],
  ),
  
  OPTION_SCHEMA_I3 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_28 => array( 
                  'name' => '#28', 
                  'description' => '#28', 
                  'tmdisplay' => '#28',
                ),
                /*
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
                */
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_INSPIRACTRICE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_3,
        ],
      ],
  ),
  
  OPTION_SCHEMA_C1 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                /*
                OPTION_SCHEMA_31 => array( 
                  'name' => '#31', 
                  'description' => '#31', 
                  'tmdisplay' => '#31',
                ),
                OPTION_SCHEMA_32 => array( 
                  'name' => '#32', 
                  'description' => '#32', 
                  'tmdisplay' => '#32',
                ),
                */
                OPTION_SCHEMA_33 => array( 
                  'name' => '#33', 
                  'description' => '#33', 
                  'tmdisplay' => '#33',
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_COMPETITIVE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_1,
        ],
      ],
  ),
  OPTION_SCHEMA_C2 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ), 
                /*
                OPTION_SCHEMA_34 => array( 
                  'name' => '#34', 
                  'description' => '#34', 
                  'tmdisplay' => '#34',
                ),
                OPTION_SCHEMA_35 => array( 
                  'name' => '#35', 
                  'description' => '#35', 
                  'tmdisplay' => '#35',
                ),
                */
                OPTION_SCHEMA_36 => array( 
                  'name' => '#36', 
                  'description' => '#36', 
                  'tmdisplay' => '#36',
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_COMPETITIVE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_2,
        ],
      ],
  ),
  
  OPTION_SCHEMA_C3 => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                /*
                OPTION_SCHEMA_37 => array( 
                  'name' => '#37', 
                  'description' => '#37', 
                  'tmdisplay' => '#37',
                ),
                OPTION_SCHEMA_38 => array( 
                  'name' => '#38', 
                  'description' => '#38', 
                  'tmdisplay' => '#38',
                ),
                */
                OPTION_SCHEMA_39 => array( 
                  'name' => '#39', 
                  'description' => '#39', 
                  'tmdisplay' => '#39',
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_COMPETITIVE,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_3,
        ],
      ],
  ),
  
  OPTION_SCHEMA_NL => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                /*
                OPTION_SCHEMA_40 => array( 
                  'name' => '#40', 
                  'description' => '#40', 
                  'tmdisplay' => '#40',
                ),
                OPTION_SCHEMA_41 => array( 
                  'name' => '#41', 
                  'description' => '#41', 
                  'tmdisplay' => '#41',
                ),
                OPTION_SCHEMA_42 => array( 
                  'name' => '#42', 
                  'description' => '#42', 
                  'tmdisplay' => '#42',
                ),
                OPTION_SCHEMA_43 => array( 
                  'name' => '#43', 
                  'description' => '#43', 
                  'tmdisplay' => '#43',
                ),
                OPTION_SCHEMA_44 => array( 
                  'name' => '#44', 
                  'description' => '#44', 
                  'tmdisplay' => '#44',
                ),
                */
                OPTION_SCHEMA_45 => array( 
                  'name' => '#45', 
                  'description' => '#45', 
                  'tmdisplay' => '#45',
                ),
                /*
                OPTION_SCHEMA_46 => array( 
                  'name' => '#46', 
                  'description' => '#46', 
                  'tmdisplay' => '#46',
                ),
                OPTION_SCHEMA_47 => array( 
                  'name' => '#47', 
                  'description' => '#47', 
                  'tmdisplay' => '#47',
                ),
                OPTION_SCHEMA_48 => array( 
                  'name' => '#48', 
                  'description' => '#48', 
                  'tmdisplay' => '#48',
                ),
                OPTION_SCHEMA_49 => array( 
                  'name' => '#49', 
                  'description' => '#49', 
                  'tmdisplay' => '#49',
                ),
                */
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_NO_LIMIT,
        ],
      ],
  ),
  
  OPTION_SCHEMA_RANDOM_ONLY => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
            ),
    'default' => OPTION_SCHEMA_RANDOM,
    'displayconditionoperand' => 'or',
    'displaycondition'=> [
        [
          "type"=> "otheroption",
          "id"=> OPTION_FLOWER,
          "value"=> OPTION_FLOWER_RANDOM,
        ],
        [
          "type"=> "otheroption",
          "id"=> OPTION_DIFFICULTY,
          "value"=> OPTION_DIFFICULTY_RANDOM,
        ],
      ],
  ),
  
  OPTION_JOKER => array(
    'name' => totranslate('Joker'),    
    'values' => array(
            OPTION_JOKER_0 => array( 
              'name' => '0', 
              'description' => totranslate('0 Joker to use in the game'), 
            ),
            OPTION_JOKER_1 => array( 
              'name' => '1', 
              'description' => totranslate('1 Joker to use in the game. Optional but designed to help fight bad luck. Its use will result in a defeat in the event of a tie.'), 
              'tmdisplay' => totranslate('1 Joker'),
            ),
          ), 
    'default' => OPTION_JOKER_1,
    'level' => 'additional',
    'displaycondition'=>  [
      [
        "type" => "otheroptionisnot",
        "id" => OPTION_MODE,
        "value"=> OPTION_MODE_COMPETITIVE,
      ],
      [
        "type" => "otheroptionisnot",
        "id" => OPTION_MODE,
        "value"=> OPTION_MODE_NOLIMIT,
      ],
    ],
  ),

];


$game_preferences = [
  PREF_SCHEMA_BOARD_ORDER => [
    'name' => totranslate('Target schema position'),
    'needReload' => false,
    'values' => [
      PREF_SCHEMA_BOARD_ORDER_LEFT => [ 'name' => totranslate('Left of my board') ],
      PREF_SCHEMA_BOARD_ORDER_RIGHT => [ 'name' => totranslate('Right of my board')],
      PREF_SCHEMA_BOARD_ORDER_LAST => ['name' => totranslate('Last')],
    ],
    "default"=> PREF_SCHEMA_BOARD_ORDER_RIGHT,
    'attribute' => 'stig_schema_order',
  ],
  PREF_STIGMAREINE_BOARD_ORDER => [
    'name' => totranslate('StigmaReine position (in competitive mode)'),
    'needReload' => false,
    'values' => [
      PREF_STIGMAREINE_BOARD_ORDER_LEFT => [ 'name' => totranslate('Left of my board')],
      PREF_STIGMAREINE_BOARD_ORDER_RIGHT => [ 'name' => totranslate('Right of my board') ],
      PREF_STIGMAREINE_BOARD_ORDER_LAST => ['name' => totranslate('Last')],
    ],
    "default"=> PREF_STIGMAREINE_BOARD_ORDER_RIGHT,
    'attribute' => 'stig_stigmareine_order',
  ],
  PREF_STIGMAREINE_BOARD_AUTO_ORDER => [
    'name' => totranslate('StigmaReine auto position as first when I need to play'),
    'values' => [
      PREF_STIGMAREINE_BOARD_AUTO_ORDER_ENABLED => [ 'name' => totranslate('Enabled')],
      PREF_STIGMAREINE_BOARD_AUTO_ORDER_DISABLED => ['name' => totranslate('Disabled')],
    ],
    "default"=> PREF_STIGMAREINE_BOARD_AUTO_ORDER_ENABLED,
    'attribute' => 'stig_stigmareine_auto_order',
  ],
  
  PREF_SP_BUTTONS => [
    'name' => totranslate('Special action buttons'),
    'needReload' => false,
    'values' => [
      PREF_SP_BUTTONS_IMAGES_AND_TEXT => [ 'name' => totranslate('Text and Image')],
      PREF_SP_BUTTONS_IMAGES_ONLY => ['name' => totranslate('Image only')],
      PREF_SP_BUTTONS_TEXT_ONLY => [ 'name' => totranslate('Text only') ],
    ],
    "default"=> PREF_SP_BUTTONS_IMAGES_AND_TEXT,
    'attribute' => 'stig_sp_buttons',
  ],
];
