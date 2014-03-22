-- phpMyAdmin SQL Dump
-- version 3.3.10.4
-- http://www.phpmyadmin.net

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


-- --------------------------------------------------------

--
-- Table structure for table `achievements`
--

CREATE TABLE IF NOT EXISTS `achievements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `a_type` int(11) NOT NULL,
  `title` varchar(128) NOT NULL,
  `description` varchar(512) NOT NULL,
  `dev` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `actions`
--

CREATE TABLE IF NOT EXISTS `actions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `artifact_counter` int(11) NOT NULL DEFAULT '0',
  `self_use` text NOT NULL,
  `oppo_use` text NOT NULL,
  `action` text NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `allies`
--

CREATE TABLE IF NOT EXISTS `allies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `title` varchar(64) NOT NULL,
  `description` varchar(256) NOT NULL,
  `combat_fatigue` int(11) NOT NULL DEFAULT '2500',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `artifacts`
--

CREATE TABLE IF NOT EXISTS `artifacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `desc_id` int(11) NOT NULL DEFAULT '0',
  `name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `plural_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `text` varchar(512) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `attack_text` varchar(128) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'No.',
  `type` int(11) NOT NULL DEFAULT '0',
  `rarity` tinyint(4) NOT NULL DEFAULT '0',
  `item_level` int(11) NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL DEFAULT '0',
  `buy_price` int(11) NOT NULL DEFAULT '0',
  `sell_price` int(11) NOT NULL DEFAULT '0',
  `armour` int(11) NOT NULL DEFAULT '0',
  `xp` int(11) NOT NULL DEFAULT '0',
  `base_damage` int(11) NOT NULL DEFAULT '0',
  `random_damage` int(11) NOT NULL DEFAULT '0',
  `attack_type` int(11) NOT NULL DEFAULT '0',
  `attack_special` int(11) NOT NULL DEFAULT '0',
  `min_level` int(11) NOT NULL DEFAULT '0',
  `modifier_type_1` int(11) NOT NULL DEFAULT '0',
  `modifier_amount_1` int(11) NOT NULL DEFAULT '0',
  `modifier_type_2` int(11) NOT NULL DEFAULT '0',
  `modifier_amount_2` int(11) NOT NULL DEFAULT '0',
  `modifier_type_3` int(11) NOT NULL DEFAULT '0',
  `modifier_amount_3` int(11) NOT NULL DEFAULT '0',
  `reputation_id` int(11) NOT NULL DEFAULT '0',
  `reputation_required` int(11) NOT NULL DEFAULT '0',
  `skill_required` int(11) NOT NULL DEFAULT '0',
  `dr_destroy` tinyint(4) NOT NULL DEFAULT '0' COMMENT '1 if this should be destroyed when starting a dungeon run',
  `o_id` int(11) NOT NULL DEFAULT '0',
  `o_name` varchar(64) NOT NULL,
  `use_multiple` tinyint(4) NOT NULL DEFAULT '0',
  `ra` int(11) NOT NULL DEFAULT '0' COMMENT 'render artifact',
  `filename` varchar(32) NOT NULL,
  `maker_id` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `attacks`
--

CREATE TABLE IF NOT EXISTS `attacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `text_1` varchar(128) NOT NULL,
  `text_2` varchar(128) NOT NULL,
  `text_3` varchar(256) NOT NULL,
  `base_damage` int(11) NOT NULL,
  `random_damage` int(11) NOT NULL,
  `attack_type` int(11) NOT NULL DEFAULT '0',
  `special` int(11) NOT NULL DEFAULT '0',
  `buff` int(11) NOT NULL DEFAULT '0',
  `buff_time` int(11) NOT NULL DEFAULT '0',
  `buff_turns` int(11) NOT NULL DEFAULT '0',
  `buff_chance` int(11) NOT NULL DEFAULT '0' COMMENT 'Percentage out of 100',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `auctions`
--

CREATE TABLE IF NOT EXISTS `auctions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `char_name` varchar(64) NOT NULL,
  `artifact_id` int(11) NOT NULL,
  `artifact_name` varchar(128) NOT NULL,
  `artifact_type` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `m_enc` int(11) NOT NULL DEFAULT '0',
  `cost` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  `auction_type` int(11) NOT NULL DEFAULT '1',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `avatars`
--

CREATE TABLE IF NOT EXISTS `avatars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `filename` varchar(32) NOT NULL,
  `char_id` int(11) NOT NULL DEFAULT '0',
  `access_check` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE IF NOT EXISTS `badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `cost` int(11) NOT NULL DEFAULT '100',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `buffs`
--

CREATE TABLE IF NOT EXISTS `buffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `modifier_type` int(11) NOT NULL DEFAULT '0',
  `modifier_value` int(11) NOT NULL DEFAULT '0',
  `modifier_type_2` int(11) NOT NULL DEFAULT '0',
  `modifier_value_2` int(11) NOT NULL DEFAULT '0',
  `description` varchar(32) NOT NULL,
  `invisible` tinyint(4) NOT NULL DEFAULT '0',
  `image` varchar(16) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `builders`
--

CREATE TABLE IF NOT EXISTS `builders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `subtype` int(11) NOT NULL,
  `title` tinytext NOT NULL,
  `description` tinytext NOT NULL,
  `attack` tinytext NOT NULL,
  `resistances` tinytext NOT NULL,
  `damage` tinytext NOT NULL,
  `value` tinytext NOT NULL,
  `misc` tinytext NOT NULL,
  `score` float NOT NULL,
  `state` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `builder_votes`
--

CREATE TABLE IF NOT EXISTS `builder_votes` (
  `builder_id` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` tinyint(4) NOT NULL,
  `comment` text NOT NULL,
  `timestamp` int(11) NOT NULL,
  KEY `builder_id` (`builder_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `characters`
--

CREATE TABLE IF NOT EXISTS `characters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `titled_name` varchar(128) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '1',
  `str` int(11) NOT NULL,
  `dex` int(11) NOT NULL,
  `int` int(11) NOT NULL,
  `cha` int(11) NOT NULL,
  `con` int(11) NOT NULL,
  `base_hp` int(11) NOT NULL,
  `current_hp` int(11) NOT NULL,
  `mana` int(11) NOT NULL DEFAULT '45',
  `mana_max` int(11) NOT NULL DEFAULT '45',
  `fatigue` int(11) NOT NULL DEFAULT '0',
  `fatigue_reduction` int(11) NOT NULL DEFAULT '0',
  `fatigue_rested` int(11) NOT NULL DEFAULT '0',
  `xp` int(11) NOT NULL DEFAULT '0',
  `gold` bigint(20) NOT NULL DEFAULT '0',
  `gold_bank` bigint(20) NOT NULL DEFAULT '0',
  `weapon` int(11) NOT NULL DEFAULT '0',
  `armour_head` int(11) NOT NULL DEFAULT '0',
  `armour_chest` int(11) NOT NULL DEFAULT '0',
  `armour_legs` int(11) NOT NULL DEFAULT '0',
  `armour_neck` int(11) NOT NULL DEFAULT '0',
  `armour_trinket` int(11) NOT NULL DEFAULT '0',
  `armour_trinket_2` int(11) NOT NULL DEFAULT '0',
  `armour_trinket_3` int(11) NOT NULL DEFAULT '0',
  `armour_hands` int(11) NOT NULL DEFAULT '0',
  `armour_wrists` int(11) NOT NULL DEFAULT '0',
  `armour_belt` int(11) NOT NULL DEFAULT '0',
  `armour_boots` int(11) NOT NULL DEFAULT '0',
  `armour_ring` int(11) NOT NULL DEFAULT '0',
  `armour_ring_2` int(11) NOT NULL DEFAULT '0',
  `weapon_enc` int(11) NOT NULL DEFAULT '0',
  `armour_head_enc` int(11) NOT NULL DEFAULT '0',
  `armour_chest_enc` int(11) NOT NULL DEFAULT '0',
  `armour_legs_enc` int(11) NOT NULL DEFAULT '0',
  `armour_neck_enc` int(11) NOT NULL DEFAULT '0',
  `armour_trinket_enc` int(11) NOT NULL DEFAULT '0',
  `armour_trinket_2_enc` int(11) NOT NULL DEFAULT '0',
  `armour_trinket_3_enc` int(11) NOT NULL DEFAULT '0',
  `armour_hands_enc` int(11) NOT NULL DEFAULT '0',
  `armour_wrists_enc` int(11) NOT NULL DEFAULT '0',
  `armour_belt_enc` int(11) NOT NULL DEFAULT '0',
  `armour_boots_enc` int(11) NOT NULL DEFAULT '0',
  `armour_ring_enc` int(11) NOT NULL DEFAULT '0',
  `armour_ring_2_enc` int(11) NOT NULL DEFAULT '0',
  `mount_id` int(11) NOT NULL DEFAULT '0',
  `mount_id_enc` int(11) NOT NULL DEFAULT '0',
  `mount_name` varchar(32) NOT NULL,
  `encounter_id` int(11) NOT NULL DEFAULT '0',
  `encounter_type` int(11) NOT NULL DEFAULT '0',
  `encounter_hp` int(11) NOT NULL DEFAULT '0',
  `encounter_max_hp` int(11) NOT NULL DEFAULT '0',
  `encounter_artifact` int(11) NOT NULL DEFAULT '0',
  `ally_id` int(11) NOT NULL DEFAULT '0',
  `ally_fatigue` int(11) NOT NULL DEFAULT '0',
  `prof_cooking` int(11) NOT NULL DEFAULT '0',
  `prof_mining` int(11) NOT NULL DEFAULT '0',
  `prof_fishing` int(11) NOT NULL DEFAULT '0',
  `prof_crafting` int(11) NOT NULL DEFAULT '0',
  `sandstorm_wisdom_solves` int(11) NOT NULL DEFAULT '0',
  `chat_channel` int(11) NOT NULL DEFAULT '1',
  `chat_channel_type` tinyint(4) NOT NULL DEFAULT '0',
  `total_fatigue` int(11) NOT NULL DEFAULT '0',
  `total_combats` int(11) NOT NULL DEFAULT '0',
  `total_fatigue_uses` int(11) NOT NULL DEFAULT '0',
  `guild_id` int(11) NOT NULL DEFAULT '0',
  `guild_name` varchar(64) NOT NULL,
  `guild_rank` int(11) NOT NULL DEFAULT '0',
  `d_id` int(11) NOT NULL DEFAULT '0',
  `d_run` int(11) NOT NULL DEFAULT '0',
  `duel_elo` int(11) NOT NULL DEFAULT '0',
  `avatar` varchar(32) NOT NULL,
  `last_login` int(11) NOT NULL DEFAULT '0',
  `action_id` int(11) NOT NULL DEFAULT '0',
  `refer_id` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `char_achievements`
--

CREATE TABLE IF NOT EXISTS `char_achievements` (
  `char_id` int(11) NOT NULL,
  `achievement_id` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  KEY `char_id` (`char_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `char_artifacts`
--

CREATE TABLE IF NOT EXISTS `char_artifacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `artifact_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `m_enc` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `artifact_id` (`artifact_id`),
  KEY `char_artifact_id` (`char_id`,`artifact_id`),
  KEY `char_id` (`char_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `char_badges`
--

CREATE TABLE IF NOT EXISTS `char_badges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `char_bank`
--

CREATE TABLE IF NOT EXISTS `char_bank` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `artifact_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `m_enc` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `char_id` (`char_id`,`artifact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `char_buffs`
--

CREATE TABLE IF NOT EXISTS `char_buffs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `buff_id` int(11) NOT NULL,
  `expires` int(11) NOT NULL,
  `combat_turn_expires` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `char_flags`
--

CREATE TABLE IF NOT EXISTS `char_flags` (
  `char_id` int(11) NOT NULL,
  `flag_id` int(11) NOT NULL,
  `flag_value` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`char_id`,`flag_id`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `char_quests`
--

CREATE TABLE IF NOT EXISTS `char_quests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `quest_id` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `foe_count_1` int(11) NOT NULL DEFAULT '0',
  `foe_count_2` int(11) NOT NULL DEFAULT '0',
  `foe_count_3` int(11) NOT NULL DEFAULT '0',
  `hidden` tinyint(4) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `char_reputations`
--

CREATE TABLE IF NOT EXISTS `char_reputations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `reputation_id` int(11) NOT NULL,
  `value` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `char_runes`
--

CREATE TABLE IF NOT EXISTS `char_runes` (
  `char_id` int(11) NOT NULL,
  `rune_id` int(11) NOT NULL,
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `char_skills`
--

CREATE TABLE IF NOT EXISTS `char_skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `skill_id` int(11) NOT NULL,
  `perm` smallint(6) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `char_status`
--

CREATE TABLE IF NOT EXISTS `char_status` (
  `char_id` int(11) NOT NULL,
  `char_name` varchar(64) NOT NULL,
  `status` varchar(64) NOT NULL,
  KEY `char_id` (`char_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `char_track`
--

CREATE TABLE IF NOT EXISTS `char_track` (
  `char_id` int(11) NOT NULL,
  `track_id` int(11) NOT NULL,
  `track_type` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  KEY `char_id` (`char_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE IF NOT EXISTS `chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `private_id` int(11) NOT NULL DEFAULT '0',
  `channel` int(11) NOT NULL DEFAULT '0',
  `channel_type` tinyint(4) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL DEFAULT '0',
  `char_name` varchar(64) NOT NULL,
  `char_id` int(11) NOT NULL DEFAULT '0',
  `message` varchar(2048) NOT NULL,
  KEY `id` (`id`),
  KEY `timestamp` (`timestamp`),
  KEY `id_channel` (`id`,`channel`),
  KEY `id_2` (`id`,`private_id`,`channel`),
  KEY `id_3` (`id`,`channel`,`channel_type`),
  KEY `id_4` (`id`,`channel`,`private_id`,`timestamp`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `choice_encounters`
--

CREATE TABLE IF NOT EXISTS `choice_encounters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `text` varchar(512) NOT NULL,
  `c_choice_1` varchar(128) NOT NULL,
  `c_id_1` int(11) NOT NULL DEFAULT '0',
  `c_type_1` int(11) NOT NULL DEFAULT '0',
  `c_artifact_required_1` int(11) NOT NULL DEFAULT '0',
  `c_choice_2` varchar(128) NOT NULL,
  `c_id_2` int(11) NOT NULL DEFAULT '0',
  `c_type_2` int(11) NOT NULL DEFAULT '0',
  `c_choice_3` varchar(128) NOT NULL,
  `c_id_3` int(11) NOT NULL DEFAULT '0',
  `c_type_3` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `combats`
--

CREATE TABLE IF NOT EXISTS `combats` (
  `id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `initiative` text NOT NULL,
  `state` mediumtext NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cries`
--

CREATE TABLE IF NOT EXISTS `cries` (
  `id` int(11) NOT NULL,
  `is_char` tinyint(4) NOT NULL,
  `text` varchar(128) NOT NULL,
  KEY `id` (`id`,`is_char`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `duel_challenges`
--

CREATE TABLE IF NOT EXISTS `duel_challenges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `status` mediumint(9) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `char_id` (`char_id`),
  KEY `char_created` (`char_id`,`created`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `duel_ladder`
--

CREATE TABLE IF NOT EXISTS `duel_ladder` (
  `rank` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  PRIMARY KEY (`rank`),
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `duel_players`
--

CREATE TABLE IF NOT EXISTS `duel_players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `duel_states`
--

CREATE TABLE IF NOT EXISTS `duel_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id_1` int(11) NOT NULL,
  `char_rank_1` int(11) NOT NULL,
  `char_id_2` int(11) NOT NULL,
  `char_rank_2` int(11) NOT NULL,
  `state` smallint(6) NOT NULL DEFAULT '0',
  `timestamp` int(11) NOT NULL,
  `render_text` varchar(1024) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `dungeon_rune_runs`
--

CREATE TABLE IF NOT EXISTS `dungeon_rune_runs` (
  `d_id` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  `char_name` varchar(64) NOT NULL,
  `level` int(11) NOT NULL,
  `xp` int(11) NOT NULL,
  `total_fatigue` int(11) NOT NULL,
  `total_combats` int(11) NOT NULL,
  `date_started` int(11) NOT NULL,
  `date_completed` int(11) NOT NULL,
  `rune_1` int(11) NOT NULL,
  `rune_2` int(11) NOT NULL,
  `rune_3` int(11) NOT NULL,
  `rune_4` int(11) NOT NULL,
  `rune_5` int(11) NOT NULL,
  KEY `char_id` (`char_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dungeon_runs`
--

CREATE TABLE IF NOT EXISTS `dungeon_runs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `d_id` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  `char_name` varchar(64) NOT NULL,
  `level` int(11) NOT NULL,
  `xp` int(11) NOT NULL,
  `total_fatigue` int(11) NOT NULL,
  `total_combats` int(11) NOT NULL,
  `date_started` int(11) NOT NULL,
  `date_completed` int(11) NOT NULL,
  `skills_str` int(11) NOT NULL,
  `skills_dex` int(11) NOT NULL,
  `skills_int` int(11) NOT NULL,
  `skills_cha` int(11) NOT NULL,
  `skills_con` int(11) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `enchants`
--

CREATE TABLE IF NOT EXISTS `enchants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `enc_id` int(11) NOT NULL DEFAULT '0',
  `artifact_type` int(11) NOT NULL DEFAULT '0',
  `min_level` int(11) NOT NULL DEFAULT '0',
  `a1` int(11) NOT NULL DEFAULT '0',
  `q1` int(11) NOT NULL DEFAULT '0',
  `a2` int(11) NOT NULL DEFAULT '0',
  `q2` int(11) NOT NULL DEFAULT '0',
  `a3` int(11) NOT NULL DEFAULT '0',
  `q3` int(11) NOT NULL DEFAULT '0',
  `a4` int(11) NOT NULL DEFAULT '0',
  `q4` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `foes`
--

CREATE TABLE IF NOT EXISTS `foes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `text` varchar(512) NOT NULL,
  `creature_type` int(11) NOT NULL DEFAULT '0',
  `hp` int(11) NOT NULL DEFAULT '1',
  `xp` int(11) NOT NULL DEFAULT '1',
  `level` int(11) NOT NULL DEFAULT '1',
  `armour` int(11) NOT NULL DEFAULT '0',
  `str` int(11) NOT NULL DEFAULT '0',
  `dex` int(11) NOT NULL DEFAULT '0',
  `int` int(11) NOT NULL DEFAULT '0',
  `cha` int(11) NOT NULL DEFAULT '0',
  `con` int(11) NOT NULL DEFAULT '0',
  `base_gold` int(11) NOT NULL DEFAULT '0',
  `random_gold` int(11) NOT NULL DEFAULT '0',
  `attack_resistance` int(11) NOT NULL DEFAULT '0',
  `attack_resistance_amount` int(11) NOT NULL DEFAULT '0',
  `attack_penetration` int(11) NOT NULL DEFAULT '0',
  `attack_vulnerable` int(11) NOT NULL DEFAULT '0',
  `special` int(11) NOT NULL DEFAULT '0',
  `reputation_id` int(11) NOT NULL DEFAULT '0',
  `reputation_value` int(11) NOT NULL DEFAULT '0',
  `reputation_max_award` int(11) NOT NULL DEFAULT '1',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `foetype_artifacts`
--

CREATE TABLE IF NOT EXISTS `foetype_artifacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foetype_id` int(11) NOT NULL,
  `level_min` int(11) NOT NULL,
  `level_max` int(11) NOT NULL,
  `artifact_id` int(11) NOT NULL,
  `artifact_droprate` int(11) NOT NULL,
  `weapon_required` int(11) NOT NULL DEFAULT '0',
  `quest_required` int(11) NOT NULL DEFAULT '0',
  `max_quantity` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `foe_artifacts`
--

CREATE TABLE IF NOT EXISTS `foe_artifacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foe_id` int(11) NOT NULL DEFAULT '0',
  `artifact_id` int(11) NOT NULL DEFAULT '0',
  `artifact_droprate` int(11) NOT NULL DEFAULT '0',
  `weapon_required` int(11) NOT NULL DEFAULT '0',
  `quest_required` int(11) NOT NULL DEFAULT '0',
  `ensure_group_id` int(11) NOT NULL DEFAULT '0',
  `d_id` int(11) NOT NULL DEFAULT '0',
  `max_quantity` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `foe_attacks`
--

CREATE TABLE IF NOT EXISTS `foe_attacks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foe_id` int(11) NOT NULL,
  `attack_id` int(11) NOT NULL,
  `artifact_required` int(11) NOT NULL DEFAULT '0',
  `odds` int(11) NOT NULL DEFAULT '100',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `game_flags`
--

CREATE TABLE IF NOT EXISTS `game_flags` (
  `flag_id` int(11) NOT NULL,
  `flag_value` int(11) NOT NULL,
  KEY `flag_id` (`flag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `guilds`
--

CREATE TABLE IF NOT EXISTS `guilds` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `leader_id` int(10) unsigned NOT NULL,
  `leader_name` varchar(64) NOT NULL,
  `rank_1` varchar(32) NOT NULL DEFAULT 'Guild Leader',
  `rank_2` varchar(32) NOT NULL DEFAULT 'Officer',
  `rank_3` varchar(32) NOT NULL DEFAULT 'Member',
  `rank_4` varchar(32) NOT NULL DEFAULT 'Apprentice',
  `rank_5` varchar(32) NOT NULL DEFAULT 'Initiate',
  `motto` varchar(100) NOT NULL,
  `url` varchar(100) NOT NULL,
  `message` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `guild_chars`
--

CREATE TABLE IF NOT EXISTS `guild_chars` (
  `guild_id` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  `char_name` varchar(64) NOT NULL,
  `rank` int(11) NOT NULL,
  KEY `guild_id` (`guild_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE IF NOT EXISTS `logs` (
  `timestamp` int(11) NOT NULL,
  `ip_addr` varchar(16) NOT NULL,
  `action` int(11) NOT NULL,
  `target_id` int(11) NOT NULL,
  `status_1` int(11) NOT NULL,
  `status_2` int(11) NOT NULL,
  `status_3` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  `char_hp` int(11) NOT NULL,
  `char_level` int(11) NOT NULL,
  KEY `timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `lottery_tickets`
--

CREATE TABLE IF NOT EXISTS `lottery_tickets` (
  `char_id` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mail`
--

CREATE TABLE IF NOT EXISTS `mail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `to_char_id` int(11) NOT NULL,
  `from_char_id` int(11) NOT NULL,
  `from_char_name` varchar(64) NOT NULL,
  `subject` varchar(128) NOT NULL,
  `text` varchar(2048) NOT NULL,
  `artifact_id` int(11) NOT NULL DEFAULT '0',
  `artifact_quantity` int(11) NOT NULL DEFAULT '0',
  `artifact_enc` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  KEY `id` (`id`),
  KEY `getMailCount` (`to_char_id`,`status`,`created`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `npcs`
--

CREATE TABLE IF NOT EXISTS `npcs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` varchar(512) NOT NULL,
  `zone` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `outfits`
--

CREATE TABLE IF NOT EXISTS `outfits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `weapon` int(11) NOT NULL,
  `a_head` int(11) NOT NULL,
  `a_chest` int(11) NOT NULL,
  `a_legs` int(11) NOT NULL,
  `a_neck` int(11) NOT NULL,
  `a_t1` int(11) NOT NULL,
  `a_t2` int(11) NOT NULL,
  `a_t3` int(11) NOT NULL,
  `a_hands` int(11) NOT NULL,
  `a_wrists` int(11) NOT NULL,
  `a_belt` int(11) NOT NULL,
  `a_boots` int(11) NOT NULL,
  `a_r1` int(11) NOT NULL,
  `a_r2` int(11) NOT NULL,
  `mount` int(11) NOT NULL DEFAULT '0',
  `weapon_e` int(11) NOT NULL,
  `a_head_e` int(11) NOT NULL,
  `a_chest_e` int(11) NOT NULL,
  `a_legs_e` int(11) NOT NULL,
  `a_neck_e` int(11) NOT NULL,
  `a_t1_e` int(11) NOT NULL,
  `a_t2_e` int(11) NOT NULL,
  `a_t3_e` int(11) NOT NULL,
  `a_hands_e` int(11) NOT NULL,
  `a_wrists_e` int(11) NOT NULL,
  `a_belt_e` int(11) NOT NULL,
  `a_boots_e` int(11) NOT NULL,
  `a_r1_e` int(11) NOT NULL,
  `a_r2_e` int(11) NOT NULL,
  `mount_e` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `plots`
--

CREATE TABLE IF NOT EXISTS `plots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id` int(11) NOT NULL,
  `plot_zone` int(11) NOT NULL,
  `title` tinytext NOT NULL,
  `description` text NOT NULL,
  `value` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `plot_flags`
--

CREATE TABLE IF NOT EXISTS `plot_flags` (
  `plot_id` int(11) NOT NULL,
  `flag` int(11) NOT NULL,
  `value` int(11) NOT NULL,
  KEY `plot_id` (`plot_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plot_guestbooks`
--

CREATE TABLE IF NOT EXISTS `plot_guestbooks` (
  `plot_id` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  `message` tinytext NOT NULL,
  KEY `plot_id` (`plot_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plot_players`
--

CREATE TABLE IF NOT EXISTS `plot_players` (
  `plot_id` int(11) NOT NULL,
  `char_id` int(11) NOT NULL,
  KEY `plot_id` (`plot_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pvp`
--

CREATE TABLE IF NOT EXISTS `pvp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `p1_id` int(11) NOT NULL,
  `p1_name` varchar(64) NOT NULL,
  `p1_elo` int(11) NOT NULL,
  `p2_id` int(11) NOT NULL,
  `p2_name` varchar(64) NOT NULL,
  `p2_elo` int(11) NOT NULL,
  `game_type` int(11) NOT NULL,
  `game_move` int(11) NOT NULL,
  `game_state` int(11) NOT NULL,
  `last_move` int(11) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `pvp_ticktack`
--

CREATE TABLE IF NOT EXISTS `pvp_ticktack` (
  `id` int(11) NOT NULL,
  `board` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `quests`
--

CREATE TABLE IF NOT EXISTS `quests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `npc_id` int(11) NOT NULL,
  `name` varchar(128) NOT NULL,
  `text` varchar(2048) NOT NULL,
  `completed_text` varchar(2048) NOT NULL,
  `repeatable` int(11) NOT NULL DEFAULT '0',
  `min_level` int(11) NOT NULL DEFAULT '0',
  `artifact_required` int(11) NOT NULL DEFAULT '0',
  `quest_required` int(11) NOT NULL DEFAULT '0',
  `gift_artifact` int(11) NOT NULL DEFAULT '0',
  `gift_quantity` int(11) NOT NULL DEFAULT '0',
  `reward_artifact` int(11) NOT NULL DEFAULT '0',
  `reward_quantity` int(11) NOT NULL DEFAULT '0',
  `reward_xp` int(11) NOT NULL DEFAULT '0',
  `reward_rep_id` int(11) NOT NULL DEFAULT '0',
  `reward_rep_amount` int(11) NOT NULL DEFAULT '0',
  `reward_rep_max` int(11) NOT NULL DEFAULT '0',
  `quest_artifact1` int(11) NOT NULL DEFAULT '0',
  `quest_quantity1` int(11) NOT NULL DEFAULT '0',
  `quest_artifact2` int(11) NOT NULL DEFAULT '0',
  `quest_quantity2` int(11) NOT NULL DEFAULT '0',
  `quest_artifact3` int(11) NOT NULL DEFAULT '0',
  `quest_quantity3` int(11) NOT NULL DEFAULT '0',
  `quest_foe1` int(11) NOT NULL DEFAULT '0',
  `quest_foe_quantity1` int(11) NOT NULL DEFAULT '0',
  `quest_foe2` int(11) NOT NULL DEFAULT '0',
  `quest_foe_quantity2` int(11) NOT NULL DEFAULT '0',
  `quest_foe3` int(11) NOT NULL DEFAULT '0',
  `quest_foe_quantity3` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `recipes`
--

CREATE TABLE IF NOT EXISTS `recipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `output_id` int(11) NOT NULL DEFAULT '0',
  `output_quantity` int(11) NOT NULL DEFAULT '1',
  `artifact_id_1` int(11) NOT NULL DEFAULT '0',
  `artifact_quantity_1` int(11) NOT NULL DEFAULT '0',
  `artifact_id_2` int(11) NOT NULL DEFAULT '0',
  `artifact_quantity_2` int(11) NOT NULL DEFAULT '0',
  `artifact_id_3` int(11) NOT NULL DEFAULT '0',
  `artifact_quantity_3` int(11) NOT NULL DEFAULT '0',
  `artifact_id_4` int(11) NOT NULL DEFAULT '0',
  `artifact_quantity_4` int(11) NOT NULL DEFAULT '0',
  `min_skill` int(11) NOT NULL DEFAULT '0',
  `fatigue` int(11) NOT NULL DEFAULT '0',
  `recipe_type` int(11) NOT NULL DEFAULT '0',
  `recipe_subtype` int(11) NOT NULL DEFAULT '0',
  `trade_skill_required` int(11) NOT NULL DEFAULT '0',
  `flag_id` int(11) NOT NULL DEFAULT '0',
  `flag_bit` smallint(6) NOT NULL DEFAULT '0',
  `default_hide` tinyint(4) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `runes`
--

CREATE TABLE IF NOT EXISTS `runes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `text` text NOT NULL,
  `modifier_type_1` int(11) NOT NULL DEFAULT '0',
  `modifier_amount_1` int(11) NOT NULL DEFAULT '0',
  `modifier_type_2` int(11) NOT NULL DEFAULT '0',
  `modifier_amount_2` int(11) NOT NULL DEFAULT '0',
  `modifier_type_3` int(11) NOT NULL DEFAULT '0',
  `modifier_amount_3` int(11) NOT NULL DEFAULT '0',
  `rarity` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT '0',
  `username` varchar(64) NOT NULL,
  `char_id` int(11) NOT NULL DEFAULT '0',
  `char_name` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `timestamp` bigint(20) NOT NULL,
  `timestamp_chat` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`),
  KEY `char_id` (`char_id`)
) ENGINE=MEMORY  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `skills`
--

CREATE TABLE IF NOT EXISTS `skills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `title_granted` varchar(128) NOT NULL DEFAULT '_',
  `level_requirement` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `value` int(11) NOT NULL DEFAULT '0',
  `stat_type` int(11) NOT NULL DEFAULT '0',
  `stat_value` int(11) NOT NULL DEFAULT '0',
  `description` varchar(32) NOT NULL,
  `full_description` varchar(256) NOT NULL,
  `skill_requirement` int(11) NOT NULL DEFAULT '0',
  `slot_cost` smallint(6) NOT NULL DEFAULT '1',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `store_artifacts`
--

CREATE TABLE IF NOT EXISTS `store_artifacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artifact_id` int(11) NOT NULL,
  `zone_id` int(11) NOT NULL,
  `gold_cost` int(11) NOT NULL DEFAULT '0',
  `artifact_cost_1` int(11) NOT NULL DEFAULT '0',
  `artifact_quantity_1` int(11) NOT NULL DEFAULT '0',
  `artifact_cost_2` int(11) NOT NULL DEFAULT '0',
  `artifact_quantity_2` int(11) NOT NULL DEFAULT '0',
  `artifact_cost_3` int(11) NOT NULL DEFAULT '0',
  `artifact_quantity_3` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `trade_auctions`
--

CREATE TABLE IF NOT EXISTS `trade_auctions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `artifact_id` int(11) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `expires` int(11) NOT NULL DEFAULT '0',
  `expires_delta` int(11) NOT NULL DEFAULT '0',
  `text` varchar(128) NOT NULL,
  `bid_artifact` int(11) NOT NULL DEFAULT '0',
  `bid_char_id` int(11) NOT NULL DEFAULT '0',
  `bid_char_name` varchar(64) NOT NULL,
  `bid_quantity` int(11) NOT NULL DEFAULT '0',
  `bid_count` int(11) NOT NULL DEFAULT '0',
  `bid_reserve` int(11) NOT NULL DEFAULT '0',
  `completed` int(11) NOT NULL DEFAULT '0',
  `dev` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `treasures`
--

CREATE TABLE IF NOT EXISTS `treasures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `text` varchar(2048) NOT NULL,
  `reward` tinyint(4) NOT NULL DEFAULT '1',
  `artifact` int(11) NOT NULL COMMENT '0 == gold',
  `quantity` int(11) NOT NULL,
  `fatigue` int(11) NOT NULL DEFAULT '1000',
  `flag_id` int(11) NOT NULL DEFAULT '0',
  `flag_bit` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `password` varchar(64) NOT NULL,
  `email` varchar(64) NOT NULL,
  `email_verified` int(4) NOT NULL DEFAULT '0',
  `verification` int(11) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL DEFAULT '0',
  `refer_id` int(11) NOT NULL DEFAULT '0',
  `last_login` int(11) NOT NULL DEFAULT '0',
  `last_ip_addr` varchar(16) NOT NULL,
  `ban_timestamp` int(11) NOT NULL DEFAULT '0',
  `max_chars` int(11) NOT NULL DEFAULT '3',
  KEY `id` (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_allies`
--

CREATE TABLE IF NOT EXISTS `user_allies` (
  `user_id` int(11) NOT NULL,
  `ally_id` int(11) NOT NULL,
  `fatigue` int(11) NOT NULL,
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `warfare_games`
--

CREATE TABLE IF NOT EXISTS `warfare_games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `char_id_1` int(11) NOT NULL,
  `char_name_1` varchar(64) NOT NULL,
  `char_id_2` int(11) NOT NULL,
  `char_name_2` varchar(64) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `wager` int(11) NOT NULL DEFAULT '0',
  `a1` int(11) NOT NULL DEFAULT '0',
  `a2` int(11) NOT NULL DEFAULT '0',
  `a3` int(11) NOT NULL DEFAULT '0',
  `a4` int(11) NOT NULL DEFAULT '0',
  `a5` int(11) NOT NULL DEFAULT '0',
  `b1` int(11) NOT NULL DEFAULT '0',
  `b2` int(11) NOT NULL DEFAULT '0',
  `b3` int(11) NOT NULL DEFAULT '0',
  `b4` int(11) NOT NULL DEFAULT '0',
  `b5` int(11) NOT NULL DEFAULT '0',
  `s1` int(11) NOT NULL DEFAULT '0',
  `s2` int(11) NOT NULL DEFAULT '0',
  `s3` int(11) NOT NULL DEFAULT '0',
  `s4` int(11) NOT NULL DEFAULT '0',
  `s5` int(11) NOT NULL DEFAULT '0',
  `modified` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE IF NOT EXISTS `zones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `parent_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(1024) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `zone_type` int(11) NOT NULL,
  `min_level` int(11) NOT NULL DEFAULT '0',
  `ui_order` int(11) NOT NULL DEFAULT '10',
  `artifact_required` int(11) NOT NULL DEFAULT '0',
  `entry_check` int(11) NOT NULL DEFAULT '0',
  `dev` tinyint(4) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zone_artifacts`
--

CREATE TABLE IF NOT EXISTS `zone_artifacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone_id` int(11) NOT NULL,
  `artifact_id` int(11) NOT NULL,
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zone_encounters`
--

CREATE TABLE IF NOT EXISTS `zone_encounters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `zone_id` int(11) NOT NULL,
  `encounter_id` int(11) NOT NULL,
  `type` enum('foe','treasure','fishing','mining','choice') NOT NULL DEFAULT 'foe',
  `artifact_required` int(11) NOT NULL DEFAULT '0',
  `flag_id_set` int(11) NOT NULL DEFAULT '0',
  `flag_bit_set` int(11) NOT NULL DEFAULT '0',
  `odds_of_occurring` int(11) NOT NULL DEFAULT '1',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

-- --------------------------------------------------------

--
-- Table structure for table `zone_transitions`
--

CREATE TABLE IF NOT EXISTS `zone_transitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `source_zone` int(11) NOT NULL DEFAULT '0',
  `dest_zone` int(11) NOT NULL DEFAULT '0',
  KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;
