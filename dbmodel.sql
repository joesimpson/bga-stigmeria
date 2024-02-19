
-- ------
-- BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
-- Stigmeria implementation : © joesimpson <1324811+joesimpson@users.noreply.github.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

ALTER TABLE `player` ADD `player_turn` INT(5) NOT NULL DEFAULT 0 COMMENT 'Last turn played by this player';
ALTER TABLE `player` ADD `player_common_actions` INT(1) NOT NULL DEFAULT 0 COMMENT 'actions played by this player on central board (during this turn)';
ALTER TABLE `player` ADD `player_personal_actions` INT(2) NOT NULL DEFAULT 0 COMMENT 'actions played by this player on their board (during this turn)';
ALTER TABLE `player` ADD `player_common_move` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'This player used the common action move on central board (during this turn) 0/1';
ALTER TABLE `player` ADD `player_joker_used` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'This player used the joker (during this round) 0/1';
-- I wanted this to be in global_variables, but I cannot be sure to store it linked to player
ALTER TABLE `player` ADD `player_selection` JSON COMMENT 'Selected tokens according to current private state';

CREATE TABLE IF NOT EXISTS `token` (
  `token_id` int(5) NOT NULL AUTO_INCREMENT,
  `token_state` int(10) DEFAULT 0,
  `token_location` varchar(32) NOT NULL,
  `player_id` int(10) NULL,
  `type` VARCHAR(32) NULL,
  `y` int(10) NULL COMMENT 'Row when token is on a board grid',
  `x` int(10) NULL COMMENT 'Column when token is on a board grid',
  PRIMARY KEY (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- Save datas about Unlocked actions during a game (it is a game design point!)
CREATE TABLE IF NOT EXISTS `player_action` (
  `action_id` int(5) NOT NULL AUTO_INCREMENT,
  `action_location` varchar(32) NOT NULL,
  `action_state` int(3) NOT NULL DEFAULT 0,
  `player_id` int(10) NOT NULL,
  `type` int(5) NOT NULL,
  PRIMARY KEY (`action_id`),
  UNIQUE KEY (`player_id`, `type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


-- CORE TABLES from tisaac boilerplate --
CREATE TABLE IF NOT EXISTS `global_variables` (
  `name` varchar(255) NOT NULL,
  `value` JSON,
  PRIMARY KEY (`name`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) NOT NULL,
  `pref_id` int(10) NOT NULL,
  `pref_value` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `move_id` int(10) NOT NULL,
  `table` varchar(32) NOT NULL,
  `primary` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `affected` JSON,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
ALTER TABLE `gamelog`
ADD `cancel` TINYINT(1) NOT NULL DEFAULT 0;
