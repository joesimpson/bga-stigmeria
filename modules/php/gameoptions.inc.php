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

$warningNotCompetitiveSchema = totranslate("Competitive mode is designed to be played on competitive flowers");
$warningCompetitiveSchema = totranslate('WARNING: Competitive schemas cannot be fulfilled except in Competitive modes. A random playable schema will be selected in other cases.');
$warningNoLimitSchema = totranslate('WARNING: No Limit schemas cannot be fulfilled except in No Limit modes. A random playable schema will be selected in other cases.');

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
                  'description' => totranslate('You must have learned normal games, this mode is designed to learn further with schemas 31 to 39. Compete with others to control the StigmaReine (central board) and unlock special actions. Reach the goal in 10 turns.'),
                  'tmdisplay' => totranslate('Competitive'),
                  'nobeginner' => true, 
                  ),
                OPTION_MODE_NOLIMIT => array( 
                  'name' => totranslate('No Limit'), 
                  'description' => totranslate('You must have learned normal games ! Compete with others to control the StigmaReine (central board) and unlock special actions. Reach the goal in 10 turns or more. Unleash the wind power. All actions will be possible.'), 
                  'tmdisplay' => totranslate('No Limit'),
                  'nobeginner' => true, 
                  ),
                OPTION_MODE_SOLO_NOLIMIT => [
                  'name' => totranslate('Solo No Limit'), 
                  'description' => totranslate('Solo No Limit : Play with No Limit rules without central board, but you automatically gain 9 actions in the first 9 turns.'), 
                  'tmdisplay' => totranslate('Solo No Limit'),
                  'nobeginner' => true, 
                ],
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
      OPTION_MODE_SOLO_NOLIMIT => [
        [
          "type" => "otheroption",
          "id" => OPTION_GAMESTATE_RATING_MODE,
          "value"=> OPTION_GAMESTATE_RATING_MODE_TRAINING,
          //No need to translate it for now because there is a BGA message over it for SOLO/training
          //"message"=> totranslate("Solo No Limit is available in training only"),
          "message"=>'',
        ],
        [
          'type' => 'maxplayers', 
          'value' => 1, 
          'message' => totranslate('Solo modes are for solo play'),
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
  /* Rework all in 1 list
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
    'name' => totranslate('Difficulty (Flower not No Limit)'),    
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
    'name' => totranslate('Difficulty (Flower No Limit)'),    
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
    'name' => totranslate('Difficulty (Random flower)'),    
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
  */
  
  OPTION_SCHEMA_ALL => array(
    'name' => totranslate('Targeted schema'),    
    'values' => array(
                OPTION_SCHEMA_RANDOM => array( 
                  'nobeginner' => true, 
                  'name' => totranslate('Random'), 
                  'description' => totranslate('Random schema'), 
                  'tmdisplay' => totranslate('Random schema'),
                ),
                OPTION_SCHEMA_RANDOM_VERTIG => array( 
                  'name' => totranslate('Random VertigHaineuse'), 
                  'description' => totranslate('Random VertigHaineuse (#1->#6) : Green, violet, and orange petals.'), 
                  'tmdisplay' => totranslate('Random VertigHaineuse'),
                ),
                OPTION_SCHEMA_1 => array( 
                  'name' => '#1 (*)', 
                  'description' => 'Level * : Normal', 
                  'tmdisplay' => '#1',
                ),
                OPTION_SCHEMA_2 => array( 
                  'name' => '#2 (*)', 
                  'description' => 'Level * : Normal', 
                  'tmdisplay' => '#2',
                ),
                OPTION_SCHEMA_3 => array( 
                  'name' => '#3 (**)', 
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#3',
                ),
                OPTION_SCHEMA_4 => array( 
                  'name' => '#4 (**)', 
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#4',
                ),
                OPTION_SCHEMA_5 => array( 
                  'name' => '#5 (***)',
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#5',
                ),
                OPTION_SCHEMA_6 => array( 
                  'name' => '#6 (***)', 
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#6',
                ),
                OPTION_SCHEMA_RANDOM_MARONNE => array( 
                  'name' => totranslate('Random MarOnne'), 
                  'description' => totranslate('Random MarOnne (#7->#12) : Brown petals.'), 
                  'tmdisplay' => totranslate('Random MarOnne'),
                ),
                OPTION_SCHEMA_7 => array( 
                  'name' => '#7 (*)', 
                  'description' => 'Level * : Normal', 
                  'tmdisplay' => '#7',
                ),
                OPTION_SCHEMA_8 => array( 
                  'name' => '#8 (*)', 
                  'description' => 'Level * : Normal', 
                  'tmdisplay' => '#8',
                ),
                OPTION_SCHEMA_9 => array( 
                  'name' => '#9 (**)', 
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#9',
                ),
                OPTION_SCHEMA_10 => array( 
                  'name' => '#10 (**)', 
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#10',
                ),
                OPTION_SCHEMA_11 => array( 
                  'name' => '#11 (***)', 
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#11',
                ),
                OPTION_SCHEMA_12 => array( 
                  'name' => '#12 (***)', 
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#12',
                ),
                OPTION_SCHEMA_RANDOM_SIFFLOCHAMP => array( 
                  'name' => totranslate('Random SiffloChamp'), 
                  'description' => totranslate('Random SiffloChamp (#13->#18) : Black and white petals.'), 
                  'tmdisplay' => totranslate('Random SiffloChamp'),
                ),
                OPTION_SCHEMA_13 => array( 
                  'name' => '#13 (*)', 
                  'description' => 'Level * : Normal', 
                  'tmdisplay' => '#13',
                ),
                OPTION_SCHEMA_14 => array( 
                  'name' => '#14 (**)', 
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#14',
                ),
                OPTION_SCHEMA_15 => array( 
                  'name' => '#15 (**)', 
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#15',
                ),
                OPTION_SCHEMA_16 => array( 
                  'name' => '#16 (***)', 
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#16',
                ),
                OPTION_SCHEMA_17 => array( 
                  'name' => '#17 (***)',  
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#17',
                ),
                OPTION_SCHEMA_18 => array( 
                  'name' => '#18 (***)', 
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#18',
                ),
                OPTION_SCHEMA_RANDOM_DENTDINE => array( 
                  'name' => totranslate('Random DentDîne'), 
                  'description' => totranslate('Random DentDîne (#19->#24) : Pink petals and permanent moves.'), 
                  'tmdisplay' => totranslate('Random DentDîne'),
                ),
                OPTION_SCHEMA_19 => array( 
                  'name' => '#19 (*)', 
                  'description' => 'Level * : Normal', 
                  'tmdisplay' => '#19',
                ),
                OPTION_SCHEMA_20 => array( 
                  'name' => '#20 (*)', 
                  'description' => 'Level * : Normal', 
                  'tmdisplay' => '#20',
                ),
                OPTION_SCHEMA_21 => array( 
                  'name' => '#21 (**)', 
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#21',
                ),
                OPTION_SCHEMA_22 => array( 
                  'name' => '#22 (**)',  
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#22',
                ),
                OPTION_SCHEMA_23 => array( 
                  'name' => '#23 (***)', 
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#23',
                ),
                OPTION_SCHEMA_24 => array( 
                  'name' => '#24 (***)',  
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#24',
                ),
                OPTION_SCHEMA_RANDOM_INSPIRACTRICE => array( 
                  'name' => totranslate('Random InspirActrice'), 
                  'description' => totranslate('Random InspirActrice (#24->#30): Each variety of petals.'), 
                  'tmdisplay' => totranslate('Random InspirActrice'),
                ),
                OPTION_SCHEMA_25 => array( 
                  'name' => '#25 (*)', 
                  'description' => 'Level * : Normal', 
                  'tmdisplay' => '#25',
                ),
                OPTION_SCHEMA_26 => array( 
                  'name' => '#26 (**)', 
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#26',
                ),
                OPTION_SCHEMA_27 => array( 
                  'name' => '#27 (**)',  
                  'description' => 'Level ** : Difficult', 
                  'tmdisplay' => '#27',
                ),
                OPTION_SCHEMA_28 => array( 
                  'name' => '#28 (***)', 
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#28',
                ),
                OPTION_SCHEMA_29 => array( 
                  'name' => '#29 (***)', 
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#29',
                ),
                OPTION_SCHEMA_30 => array( 
                  'name' => '#30 (***)', 
                  'description' => 'Level *** : Very difficult', 
                  'tmdisplay' => '#30',
                ),
                OPTION_SCHEMA_RANDOM_COMPETITIVE => array( 
                  'nobeginner' => true, 
                  'name' => totranslate('Random Competitive'), 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => totranslate('Random Competitive'),
                ),
                OPTION_SCHEMA_31 => array( 
                  'nobeginner' => true, 
                  'name' => '#31 (*)', 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#31',
                ),
                OPTION_SCHEMA_32 => array( 
                  'nobeginner' => true, 
                  'name' => '#32 (*)', 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#32',
                ),
                OPTION_SCHEMA_33 => array( 
                  'nobeginner' => true, 
                  'name' => '#33 (*)',  
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#33',
                ),
                OPTION_SCHEMA_34 => array( 
                  'nobeginner' => true, 
                  'name' => '#34 (**)', 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#34',
                ),
                OPTION_SCHEMA_35 => array( 
                  'nobeginner' => true, 
                  'name' => '#35 (**)', 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#35',
                ),
                OPTION_SCHEMA_36 => array( 
                  'nobeginner' => true, 
                  'name' => '#36 (**)', 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#36',
                ),
                OPTION_SCHEMA_37 => array( 
                  'nobeginner' => true, 
                  'name' => '#37 (***)', 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#37',
                ),
                OPTION_SCHEMA_38 => array( 
                  'nobeginner' => true, 
                  'name' => '#38 (***)', 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#38',
                ),
                OPTION_SCHEMA_39 => array( 
                  'nobeginner' => true, 
                  'name' => '#39 (***)', 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#39',
                ),

                OPTION_SCHEMA_RANDOM_COMPETITIVE_NL => array( 
                  'nobeginner' => true, 
                  'name' => totranslate('Random Competitive No Limit'), 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => totranslate('Random Competitive No Limit'),
                ),
                OPTION_SCHEMA_40 => array( 
                  'nobeginner' => true, 
                  'name' => '#40 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#40',
                ),
                OPTION_SCHEMA_41 => array( 
                  'nobeginner' => true, 
                  'name' => '#41 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#41',
                ),
                OPTION_SCHEMA_42 => array( 
                  'nobeginner' => true, 
                  'name' => '#42 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#42',
                ),
                OPTION_SCHEMA_43 => array( 
                  'nobeginner' => true, 
                  'name' => '#43 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#43',
                ),
                OPTION_SCHEMA_44 => array( 
                  'nobeginner' => true, 
                  'name' => '#44 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#44',
                ),
                OPTION_SCHEMA_45 => array( 
                  'nobeginner' => true, 
                  'name' => '#45 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#45',
                ),
                OPTION_SCHEMA_46 => array( 
                  'nobeginner' => true, 
                  'name' => '#46 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#46',
                ),
                OPTION_SCHEMA_47 => array( 
                  'nobeginner' => true, 
                  'name' => '#47 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#47',
                ),
                OPTION_SCHEMA_48 => array( 
                  'nobeginner' => true, 
                  'name' => '#48 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#48',
                ),
                OPTION_SCHEMA_49 => array( 
                  'nobeginner' => true, 
                  'name' => '#49 (****)', 
                  'description' => $warningNoLimitSchema, 
                  'tmdisplay' => '#49',
                ),
                
                OPTION_SCHEMA_UNOFFICIAL_101 => array( 
                  'nobeginner' => true, 
                  'name' => totranslate('#101 (Unofficial : France flag)'), 
                  'description' => $warningCompetitiveSchema, 
                  'tmdisplay' => '#101',
                  'alpha' => true, 
                ),
            ),
    'default' => OPTION_SCHEMA_1,
    'displaycondition'=> [
      ],
    'startcondition'=>  [
      //Author said Competitive mode rules are to to be played on "normal" schemas, else we woul need to change the filter of unlockable actions (difficulty)
      OPTION_SCHEMA_1 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_2 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_3 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_4 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_5 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_6 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_7 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_8 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_9 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_10 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_11 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_12 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_13 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_14 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_15 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_16 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_17 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_18 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_19 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_20 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_21 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_22 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_23 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_24 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_25 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_26 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_27 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_28 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_29 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
      OPTION_SCHEMA_30 => [ [ "type" => "otheroptionisnot", "id" => OPTION_MODE, "value"=> OPTION_MODE_COMPETITIVE, "message"=> $warningNotCompetitiveSchema,],], 
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
      [
        "type" => "otheroptionisnot",
        "id" => OPTION_MODE,
        "value"=> OPTION_MODE_SOLO_NOLIMIT,
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
    "default"=> PREF_STIGMAREINE_BOARD_AUTO_ORDER_DISABLED,
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
  
  /*
  -> replaced by a client settings
  PREF_ANIMATIONS_STYLE => [
    'name' => totranslate('Animations'),
    'needReload' => false,
    'values' => [
      PREF_ANIMATIONS_STYLE_ALL => [ 'name' => totranslate('Enabled')],
      PREF_ANIMATIONS_STYLE_WIND_ONLY => ['name' => totranslate('Wind only')],
    ],
    "default"=> PREF_ANIMATIONS_STYLE_ALL,
    'attribute' => 'stig_animations_style',
  ],
  */
  PREF_START_NEXT_PLAYER => [
    'name' => totranslate('Button start next player turn when I start my turn'),
    'needReload' => false,
    'values' => [
      PREF_START_NEXT_PLAYER_AUTO_WHEN_NO_VS => [ 'name' => totranslate('Automatic with no playable VS action')],
      PREF_START_NEXT_PLAYER_MANUAL => ['name' => totranslate('Manual')],
    ],
    "default"=> PREF_START_NEXT_PLAYER_AUTO_WHEN_NO_VS,
    'attribute' => 'stig_startnextplayer',
  ],

  PREF_UNDO_STYLE => [
    'name' => totranslate('Undo buttons style'),
    'needReload' => false,
    'values' => [
      PREF_UNDO_STYLE_TEXT => [ 'name' => totranslate('Text only') ],
      PREF_UNDO_STYLE_ICON => [ 'name' => totranslate('Icon only')],
    ],
    "default"=> PREF_UNDO_STYLE_ICON,
    'attribute' => 'stig_undo_style',
  ],

  PREF_ACTIONS_LANG => [
    'name' => totranslate('Special actions board'),
    'needReload' => false,
    'values' => [
      PREF_ACTIONS_LANG_EN => [ 'name' => totranslate('English') ],
      PREF_ACTIONS_LANG_FR => [ 'name' => totranslate('French')],
    ],
    "default"=> PREF_ACTIONS_LANG_EN,
    'attribute' => 'stig_sp_action_lang',
  ],
  
  PREF_PANEL_ICONS_SIZE => [
    'name' => totranslate('Player panel icons size'),
    'needReload' => false,
    'values' => [
      PREF_PANEL_ICONS_SIZE_SMALL => [ 'name' => totranslate('Small') ],
      PREF_PANEL_ICONS_SIZE_BIG => [ 'name' => totranslate('Big')],
    ],
    "default"=> PREF_PANEL_ICONS_SIZE_SMALL,
    'attribute' => 'stig_panel_icon_size',
  ],
];
