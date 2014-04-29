<?

require_once 'include/core.php';

require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/constants.php';
require_once sg_base_path . 'include/duels.php';
require_once sg_base_path . 'include/flag.php';
require_once sg_base_path . 'include/inventory.php';
require_once sg_base_path . 'include/mails.php';
require_once sg_base_path . 'include/sql.php';

class Char {
  function Char( $char_id ) {
    $time = time();

    $use_session = TRUE;
    if ( ( ! isset( $_SESSION[ 'c' ] ) ) || ( $_SESSION['c'] != $char_id ) ) {
        $use_session = FALSE;
    }

    $c = esc( $char_id );
    $this->changed = array();

    $query = "
      SELECT
        *
      FROM
        `characters`
      WHERE
        id = $c
    ";

    $results = sqlQuery( $query );
    if ( ! $results) { return FALSE; }

    $this->c = $results->fetch_assoc();

    $base_stat = 9 + floor( $this->c[ 'level' ] / 5 );
    $this->c[ 'str' ] = $base_stat;
    $this->c[ 'dex' ] = $base_stat;
    $this->c[ 'int' ] = $base_stat;
    $this->c[ 'cha' ] = $base_stat;
    $this->c[ 'con' ] = $base_stat;
    $this->c[ 'skills' ] = array();
    $this->c[ 'runes' ] = array();
    $this->c[ 'reputations' ] = array();
    $this->c[ 'buffs' ] = array();
    $this->c[ 'achievements' ] = array();
    $this->c[ 'quests' ] = array();
    $this->c[ 'flags' ] = array();
    $this->c[ 'tracking' ] = array(1 => array(), 2 => array());
    $this->c[ 'str_bonus' ] = 0;
    $this->c[ 'dex_bonus' ] = 0;
    $this->c[ 'int_bonus' ] = 0;
    $this->c[ 'cha_bonus' ] = 0;
    $this->c[ 'con_bonus' ] = 0;
    $this->c[ 'melee_dmg_bonus' ] = 0;
    $this->c[ 'defend_dmg_bonus' ] = 0;
    $this->c[ 'xp_bonus' ] = 0;
    $this->c[ 'rep_bonus' ] = 0;
    $this->c[ 'base_hp_bonus' ] = 0;
    $this->c[ 'fatigue_reduction_bonus' ] = 0;
    $this->c[ 'gold_bonus' ] = 0;
    $this->c[ 'item_bonus' ] = 0;
    $this->c[ 'armour' ] = 0;
    $this->c[ 'fishing_bonus' ] = 0;
    $this->c[ 'mining_bonus' ] = 0;
    $this->c[ 'buff_bonus' ] = 0;
    $this->c[ 'food_reduction' ] = 0;
    $this->c[ 'sandstorm_wisdom_solves' ] = 0;
    $this->c[ 'dodge_bonus' ] = 0;
    $this->c[ 'initiative_bonus' ] = 0;
    $this->c[ 'hunger_bonus' ] = 0;
    $this->c[ 'bonus_mana_percent' ] = 0;
    $this->c[ 'bonus_armour_percent' ] = 0;
    $this->c[ 'bonus_health_percent' ] = 0;
    $this->c[ 'mana_regen' ] = 1;
    $this->c[ 'bonus_melee_dmg_percent' ] = 0;
    $this->c[ 'bonus_magic_dmg_percent' ] = 0;
    $this->c[ 'hp_regen' ] = 0;
    $this->c[ 'xp_combat_bonus' ] = 0;
    $this->c[ 'convert_magic_dmg' ] = 0;
    $this->c[ 'reduce_craft_fatigue' ] = 0;
    $this->c[ 'burden' ] = 1.0;

    if ( ( ! $use_session ) ||
         ( ( $use_session ) && ( ! isset( $_SESSION[ 'runes' ] ) ) ) ) {
        $query = "
          SELECT r.* FROM char_runes AS c, runes AS r
          WHERE c.char_id = '$c' AND c.rune_id = r.id ORDER BY r.name ASC
        ";
        $results = sqlQuery( $query );

        if ( $results ) {
            while ( $rune = $results->fetch_assoc() ) {
                $rune[ 'name' ] = fixStr( $rune[ 'name' ] );
                $rune[ 'text' ] = fixStr( $rune[ 'text' ] );
                $this->c[ 'runes' ][ $rune[ 'id' ] ] = $rune;
            }
        }

        if ( $use_session ) {
            $_SESSION[ 'runes' ] = $this->c[ 'runes' ];
        }
    } else {
        $this->c[ 'runes' ] = $_SESSION[ 'runes' ];
    }

    foreach ( $this->c[ 'runes' ] as $rune ) {
        applyBuff( $this, $rune, 'modifier_type_1', 'modifier_amount_1' );
        applyBuff( $this, $rune, 'modifier_type_2', 'modifier_amount_2' );
        applyBuff( $this, $rune, 'modifier_type_3', 'modifier_amount_3' );
    }

    /*
    // now get this character's skills.

    if ( ( ! $use_session ) ||
         ( ( $use_session ) && ( ! isset( $_SESSION[ 'skills' ] ) ) ) ) {
        $query = "
          SELECT
            s.*
          FROM
            char_skills AS c, skills AS s
          WHERE
            c.char_id = '$c' AND c.skill_id = s.id
          ORDER BY
            s.id ASC
        ";

        $results = sqlQuery( $query );

        if ( $results ) {
            while ( $skill = $results->fetch_assoc() ) {
                $this->c[ 'skills' ][ $skill[ 'id' ] ] = $skill;
            }
        }

        if ( $use_session ) {
            $_SESSION[ 'skills' ] = $this->c[ 'skills' ];
        }
    } else {
      $this->c[ 'skills' ] = $_SESSION[ 'skills' ];
    }

    foreach ( $this->c[ 'skills' ] as $skill ) {
        applyBuff( $this, $skill, 'type', 'value' );
    }
    */

    // get the artifacts

    if ( $use_session ) {
        $this->c[ 'inventory_obj' ] = new CharInventory( $c );
        $tq = $this->c[ 'inventory_obj' ]->getTotalQuantity();
        if ( $tq > 250 ) {
            $this->c[ 'burden' ] = min( $tq / 250.0, 3.0 );
        } elseif ( $tq < 50 ) {
            $this->c[ 'burden' ] = 0.90;
        }
    }

    if ( ( ! $use_session ) ||
         ( ( $use_session ) && ( ! isset( $_SESSION[ 'reputations' ] ) ) ) ) {
        $query = "
          SELECT
            id, char_id, reputation_id, value
          FROM
            char_reputations
          WHERE
            char_id = '$c'
          ORDER BY
            reputation_id
        ";

        $results = sqlQuery( $query );

        if ( $results ) {
            while ( $rep = $results->fetch_assoc() ) {
                $rep[ 'name' ] = getReputationName( $rep[ 'reputation_id' ] );
                $this->c[ 'reputations' ][ $rep[ 'reputation_id' ] ] = $rep;
            }
        }

        if ( $use_session ) {
            $_SESSION[ 'reputations' ] = $this->c[ 'reputations' ];
        }
    } else {
        $this->c[ 'reputations' ] = $_SESSION[ 'reputations' ];
    }

//    if ( ( ! $use_session ) ||
//         ( ( $use_session ) && ( ! isset( $_SESSION[ 'artifact_array' ] ) ) ) ) {
      $artifact_id_array = array();
      $artifact_id_array[] = $this->c[ 'weapon' ];
      $artifact_id_array[] = $this->c[ 'armour_head' ];
      $artifact_id_array[] = $this->c[ 'armour_chest' ];
      $artifact_id_array[] = $this->c[ 'armour_legs' ];
      $artifact_id_array[] = $this->c[ 'armour_neck' ];
      $artifact_id_array[] = $this->c[ 'armour_trinket' ];
      $artifact_id_array[] = $this->c[ 'armour_trinket_2' ];
      $artifact_id_array[] = $this->c[ 'armour_trinket_3' ];
      $artifact_id_array[] = $this->c[ 'armour_hands' ];
      $artifact_id_array[] = $this->c[ 'armour_wrists' ];
      $artifact_id_array[] = $this->c[ 'armour_belt' ];
      $artifact_id_array[] = $this->c[ 'armour_boots' ];
      $artifact_id_array[] = $this->c[ 'armour_ring' ];
      $artifact_id_array[] = $this->c[ 'armour_ring_2' ];
      $artifact_id_array[] = $this->c[ 'mount_id' ];

      $a_array = getArtifactArray( $artifact_id_array );

/*      foreach ( $a_array as &$x ) {
          $x[ 'name' ] = utf8_encode( $x[ 'name' ] );
          $x[ 'o_name' ] = utf8_encode( $x[ 'o_name' ] );
      }*/

//        if ( $use_session ) {
//            $_SESSION[ 'artifact_array' ] = $a_array;
//        }
//    } elseif ( ( $use_session ) && ( isset( $_SESSION[ 'artifact_array' ] ) ) ) {
//        $a_array = $_SESSION[ 'artifact_array' ];
//    }
//debugPrint( $a_array );
//debugPrint( $_SESSION[ 'artifact_array' ] );

    $this->c[ 'weapon' ] = $a_array[ $this->c[ 'weapon' ] ];
    $this->c[ 'armour_head' ] = $a_array[ $this->c[ 'armour_head' ] ];
    $this->c[ 'armour_chest' ] = $a_array[ $this->c[ 'armour_chest' ] ];
    $this->c[ 'armour_legs' ] = $a_array[ $this->c[ 'armour_legs' ] ];
    $this->c[ 'armour_neck' ] = $a_array[ $this->c[ 'armour_neck' ] ];
    $this->c[ 'armour_trinket' ] = $a_array[ $this->c[ 'armour_trinket' ] ];
    $this->c[ 'armour_trinket_2' ] = $a_array[ $this->c[ 'armour_trinket_2' ] ];
    $this->c[ 'armour_trinket_3' ] = $a_array[ $this->c[ 'armour_trinket_3' ] ];
    $this->c[ 'armour_hands' ] = $a_array[ $this->c[ 'armour_hands' ] ];
    $this->c[ 'armour_wrists' ] = $a_array[ $this->c[ 'armour_wrists' ] ];
    $this->c[ 'armour_belt' ] = $a_array[ $this->c[ 'armour_belt' ] ];
    $this->c[ 'armour_boots' ] = $a_array[ $this->c[ 'armour_boots' ] ];
    $this->c[ 'armour_ring' ] = $a_array[ $this->c[ 'armour_ring' ] ];
    $this->c[ 'armour_ring_2' ] = $a_array[ $this->c[ 'armour_ring_2' ] ];
    $this->c[ 'mount' ] = $a_array[ $this->c[ 'mount_id' ] ];

    $this->c[ 'armour' ] =
        $this->c[ 'armour'] +
        $this->c[ 'weapon' ][ 'armour' ] +
        $this->c[ 'armour_head' ][ 'armour' ] +
        $this->c[ 'armour_chest' ][ 'armour' ] +
        $this->c[ 'armour_legs' ][ 'armour' ] +
        $this->c[ 'armour_neck' ][ 'armour' ] +
        $this->c[ 'armour_trinket' ][ 'armour' ] +
        $this->c[ 'armour_trinket_2' ][ 'armour' ] +
        $this->c[ 'armour_trinket_3' ][ 'armour' ] +
        $this->c[ 'armour_hands' ][ 'armour' ] +
        $this->c[ 'armour_wrists' ][ 'armour' ] +
        $this->c[ 'armour_belt' ][ 'armour' ] +
        $this->c[ 'armour_boots' ][ 'armour' ] +
        $this->c[ 'armour_ring' ][ 'armour' ] +
        $this->c[ 'armour_ring_2' ][ 'armour' ] +
        $this->c[ 'mount' ][ 'armour' ];

    $stat_awards = array();
    $stat_awards[] = $this->c[ 'weapon' ];
    $stat_awards[] = $this->c[ 'armour_head' ];
    $stat_awards[] = $this->c[ 'armour_chest' ];
    $stat_awards[] = $this->c[ 'armour_legs' ];
    $stat_awards[] = $this->c[ 'armour_neck' ];
    $stat_awards[] = $this->c[ 'armour_trinket' ];
    $stat_awards[] = $this->c[ 'armour_trinket_2' ];
    $stat_awards[] = $this->c[ 'armour_trinket_3' ];
    $stat_awards[] = $this->c[ 'armour_hands' ];
    $stat_awards[] = $this->c[ 'armour_wrists' ];
    $stat_awards[] = $this->c[ 'armour_belt' ];
    $stat_awards[] = $this->c[ 'armour_boots' ];
    $stat_awards[] = $this->c[ 'armour_ring' ];
    $stat_awards[] = $this->c[ 'armour_ring_2' ];
    $stat_awards[] = $this->c[ 'mount' ];

    foreach ( $stat_awards as $x ) {
        applyBuff( $this, $x, 'modifier_type_1', 'modifier_amount_1' );
        applyBuff( $this, $x, 'modifier_type_2', 'modifier_amount_2' );
        applyBuff( $this, $x, 'modifier_type_3', 'modifier_amount_3' );
    }

    $this->c['weapon']['m_enc'] = $this->c['weapon_enc'];
    $this->c['armour_head']['m_enc'] = $this->c['armour_head_enc'];
    $this->c['armour_chest']['m_enc'] = $this->c['armour_chest_enc'];
    $this->c['armour_legs']['m_enc'] = $this->c['armour_legs_enc'];
    $this->c['armour_neck']['m_enc'] = $this->c['armour_neck_enc'];
    $this->c['armour_trinket']['m_enc'] = $this->c['armour_trinket_enc'];
    $this->c['armour_trinket_2']['m_enc'] = $this->c['armour_trinket_2_enc'];
    $this->c['armour_trinket_3']['m_enc'] = $this->c['armour_trinket_3_enc'];
    $this->c['armour_hands']['m_enc'] = $this->c['armour_hands_enc'];
    $this->c['armour_wrists']['m_enc'] = $this->c['armour_wrists_enc'];
    $this->c['armour_belt']['m_enc'] = $this->c['armour_belt_enc'];
    $this->c['armour_boots']['m_enc'] = $this->c['armour_boots_enc'];
    $this->c['armour_ring']['m_enc'] = $this->c['armour_ring_enc'];
    $this->c['armour_ring_2']['m_enc'] = $this->c['armour_ring_2_enc'];

    $stat_awards = array();
    $stat_awards[] = $this->c['weapon_enc'];
    $stat_awards[] = $this->c['armour_head_enc'];
    $stat_awards[] = $this->c['armour_chest_enc'];
    $stat_awards[] = $this->c['armour_legs_enc'];
    $stat_awards[] = $this->c['armour_neck_enc'];
    $stat_awards[] = $this->c['armour_trinket_enc'];
    $stat_awards[] = $this->c['armour_trinket_2_enc'];
    $stat_awards[] = $this->c['armour_trinket_3_enc'];
    $stat_awards[] = $this->c['armour_hands_enc'];
    $stat_awards[] = $this->c['armour_wrists_enc'];
    $stat_awards[] = $this->c['armour_belt_enc'];
    $stat_awards[] = $this->c['armour_boots_enc'];
    $stat_awards[] = $this->c['armour_ring_enc'];
    $stat_awards[] = $this->c['armour_ring_2_enc'];

    foreach ($stat_awards as $x) {
      $e = getEnchant($x);
      applyBuff($this, $e, 'm', 'v');
    }

    // add details about certain skills here, before buffs are applied

    $this->c['mana_max'] =
        ($this->c['int'] + $this->c['int_bonus']) * 5;
    $this->c['mana_max'] = applyMultiplier(
        $this->c['mana_max'], $this->c['bonus_mana_percent']);
    if ($this->c['mana'] > $this->c['mana_max']) {
      $this->c['mana'] = $this->c['mana_max'];
    }
    $this->c['max_fatigue_reduction'] =
        ($this->c['level'] * 5) + ($this->c['con'] * 10) +
        ($this->c['hunger_bonus']);

    // now get this character's buffs.

    if ((!$use_session) ||
        (($use_session) && (!isset($_SESSION['buffs'])))) {
      $query = "
        SELECT
          b.*, c.expires, c.combat_turn_expires
        FROM
          char_buffs AS c, buffs AS b
        WHERE
          c.char_id = '$c' AND c.buff_id = b.id AND c.expires >= $time
        ORDER BY
          b.name ASC
      ";

      $results = sqlQuery($query);

      if ($results) {
        while ($buff = $results->fetch_assoc()) {
          $this->c['buffs'][$buff['id']] = $buff;
        }
      }

      if ($use_session) {
        $_SESSION['buffs'] = $this->c['buffs'];
      }
    } else {
      $this->c['buffs'] = $_SESSION['buffs'];
    }

    $old_buffs = array();
    foreach ($this->c['buffs'] as $k => $buff) {
      $combat_turns_used = FALSE;
      if (($buff['combat_turn_expires'] > 0) &&
          ($buff['combat_turn_expires'] <= $this->c['total_combats'])) {
        $combat_turns_used = TRUE;
      }

      if (($buff['expires'] < $time) ||
          ($combat_turns_used == TRUE)) {
        $old_buffs[] = $k;
      }
    }

    if (count($old_buffs) > 0) {
      foreach ($old_buffs as $buff_id) {
        unset($this->c['buffs'][$buff_id]);
      }

      if ($use_session) {
        $_SESSION['buffs'] = $this->c['buffs'];
      }
    }

    foreach ($this->c['buffs'] as $buff) {
      applyBuff($this, $buff, 'modifier_type', 'modifier_value');
      applyBuff($this, $buff, 'modifier_type_2', 'modifier_value_2');
    }

    // get this character's achievements

    if ((!$use_session) ||
        (($use_session) && (!isset($_SESSION['achievements'])))) {
      $query = "SELECT achievement_id FROM char_achievements WHERE char_id=$c";
      $results = sqlQuery($query);
      if ($results) {
        while ($o = $results->fetch_assoc()) {
          $this->c['achievements'][$o['achievement_id']] = TRUE;
        }
      }
      if ($use_session) {
        $_SESSION['achievements'] = $this->c['achievements'];
      }
    } else {
      $this->c['achievements'] = $_SESSION['achievements'];
    }

    // get this character's quests.

    if ($use_session) {
      if (!isset($_SESSION['quests'])) {
        $query = "
          SELECT
            q.id, q.npc_id, q.name, c.status, c.hidden,
            q.min_level, q.repeatable,
            q.quest_artifact1, q.quest_quantity1,
            q.quest_artifact2, q.quest_quantity2,
            q.quest_artifact3, q.quest_quantity3,
            q.quest_foe1, q.quest_foe_quantity1, c.foe_count_1,
            q.quest_foe2, q.quest_foe_quantity2, c.foe_count_2,
            q.quest_foe3, q.quest_foe_quantity3, c.foe_count_3
          FROM
            char_quests AS c, quests AS q
          WHERE
            c.char_id = '$c' AND c.quest_id = q.id
          ORDER BY
            q.min_level ASC, q.name ASC
        ";
        $results = sqlQuery($query);

        $quest_keys_retain = array('id' => 1, 'name' => 1, 'status' => 1);

        if ($results) {
          while ($quest = $results->fetch_assoc()) {
            $quest['name'] = utf8_encode($quest['name']);
            $quest['text'] = utf8_encode($quest['text']);

            if (!(($quest['status'] == sg_quest_in_progress) ||
                  ($quest['repeatable'] > 0))) {
              $quest_keys = array_keys($quest);
              foreach ($quest_keys as $key) {
                if (!array_key_exists($key, $quest_keys_retain)) {
                  unset($quest[$key]);
                }
              }
            }

            $this->c['quests'][$quest['id']] = $quest;
          }
        }

        $_SESSION['quests'] = $this->c['quests'];
      } else {
        $this->c['quests'] = $_SESSION['quests'];
      }
    }

    // get this character's flags.

    if ((!$use_session) ||
        (($use_session) && (!isset($_SESSION['flags'])))) {
      $query = "
        SELECT
          c.*
        FROM
          char_flags AS c
        WHERE
          c.char_id = '$c'
      ";

      $results = sqlQuery($query);

      if ($results) {
        while ($flag = $results->fetch_assoc()) {
          $this->c['flags'][$flag['flag_id']] = $flag['flag_value'];
        }
      }

      if ($use_session) {
        $_SESSION['flags'] = $this->c['flags'];
      }
    } else {
      $this->c['flags'] = $_SESSION['flags'];
    }

    // get this character's flags.

    if ($use_session) {
      if (!isset($_SESSION['tracking'])) {
        $query = "
          SELECT
            c.*
          FROM
            char_track AS c
          WHERE
            c.char_id = '$c'
        ";
        $results = sqlQuery($query);

        if ($results) {
          while ($o = $results->fetch_assoc()) {
            $this->c['tracking'][$o['track_type']][$o['track_id']] =
                $o['quantity'];
          }
        }

        $_SESSION['tracking'] = $this->c['tracking'];
      } else {
        $this->c['tracking'] = $_SESSION['tracking'];
      }
    }

    $this->flag_obj = new FlagUpdater();
    $this->artifact_obj = new ArtifactAwarder();

    // get this character's new messages, if any.
    if ( ( $use_session ) && ( isset( $_SESSION[ 'mail_time_check' ] ) ) ) {
      if ( ( $time - $_SESSION[ 'mail_time_check' ] ) > 10 ) {
        $this->c['new_mail_count'] = getMailCount($char_id);
        $_SESSION['new_mail_count'] = $this->c['new_mail_count'];
        $_SESSION['mail_time_check'] = $time;
      } else {
        $this->c['new_mail_count'] = $_SESSION['new_mail_count'];
      }
    }

    // get this character's duel requests, if any.
    if ( ( $use_session ) && ( isset( $_SESSION[ 'duel_time_check' ] ) ) ) {
      if (($time - $_SESSION['duel_time_check']) > 10) {
        $this->c['duel_requests'] = getAllDuelChallenges($char_id);
        $_SESSION['duel_requests'] = $this->c['duel_requests'];
        $_SESSION['duel_time_check'] = $time;
      } else {
        $this->c['duel_requests'] = $_SESSION['duel_requests'];
      }
    }

    if ($use_session) {
      if (isset($_SESSION['dungeon_run_count'])) {
        $this->c['dungeon_run_count'] = $_SESSION['dungeon_run_count'];
      } else {
        $this->c['dungeon_run_count'] = getDungeonRunCount($char_id);
      }
      $_SESSION['dungeon_run_count'] = $this->c['dungeon_run_count'];
    } else {
      $this->c['dungeon_run_count'] = getDungeonRunCount($char_id);
    }

    if ($use_session) {
      if (isset($_SESSION['char_status'])) {
        $this->c['char_status'] = $_SESSION['char_status'];
      } else {
        $this->c['char_status'] = getCharStatus($char_id);
      }
      $_SESSION['char_status'] = $this->c['char_status'];
    } else {
      $this->c['char_status'] = getCharStatus($char_id);
    }/**/

    if ($use_session) {
      if (isset($_SESSION['ally'])) {
        $this->c['ally'] = $_SESSION['ally'];
      } else {
        $this->c['ally'] = getAlly($this);
        $_SESSION['ally'] = $this->c['ally'];
      }
    } else {
      $this->c['ally'] = getAllyById($this->c['ally_id']);
    }

    // calculate the base_hp and current_hp for this char
    $this->calculateHpValues();

    if (array_key_exists(1, $this->c['flags'])) {
      $this->c['sandstorm_wisdom_solves'] +=
          bitCount($this->c['flags'][1]);
    }
    if (array_key_exists(2, $this->c['flags'])) {
      $this->c['sandstorm_wisdom_solves'] +=
          bitCount($this->c['flags'][2]);
    }

    $this->c['armour'] = applyMultiplier(
        $this->c['armour'], $this->c['bonus_armour_percent']);

    if ($this->c['armour'] < 0) { $this->c['armour'] = 0; }
    if ($this->c['fatigue'] > 100000) { $this->c['fatigue'] = 100000; }

/*    if (sg_debug) {
//      generateSigProfile($this);
    }*/

    if ( ( $use_session ) && ( isset( $_SESSION[ 'json_timestamp' ] ) ) ) {
      if (($time - $_SESSION['json_timestamp']) > 600) {
        generateJsonProfile($this);
        generateSigProfile($this);
        $_SESSION['json_timestamp'] = $time;
      }
    }

    if ( ( $use_session ) && ( isset( $_SESSION[ 'battle_cries' ] ) ) ) {
      if (isset($_SESSION['battle_cries'])) {
        $this->c['battle_cries'] = $_SESSION['battle_cries'];
      } else {
        $this->c['battle_cries'] = getBattleCries($char_id, 0);
        $_SESSION['battle_cries'] = $this->c['battle_cries'];
      }
    }
  }

  function c() {
    return $this->c;
  }

  function save() {
    if ($this->c['id'] == 0) { return FALSE; }
    if ($this->flag_obj != NULL) {
      $this->flag_obj->save($this);
    }
    if ($this->artifact_obj != NULL) {
      $this->artifact_obj->save($this->c['id']);
    }
    if (count($this->changed) == 0) { return FALSE; }

    $char_updates = array();
    foreach ($this->changed as $k => $v) {
      $char_updates[] = $k . ' = \'' . esc($v) . '\'';
    }

    $query = "
      UPDATE
        `characters`
      SET
    " . join(', ', $char_updates) . "
      WHERE
        id = '" . $this->c['id'] . "'
    ";
    $results = sqlQuery($query);

    $this->changed = array();

    return TRUE;
  }

  function setTitledName($n) {
    $this->c['titled_name'] = htmlspecialchars($n);
    $this->changed['titled_name'] = $this->c['titled_name'];
  }
  function setLevel($x) {
    $this->c['level'] = intval($x);
    $this->c['base_hp'] = 12 + $this->c['base_hp_bonus'] +
        (($this->c['con'] + $this->c['con_bonus'] - 9) * 10) +
        (($this->c['level'] - 1) * 12);
    $this->c['current_hp'] = $this->c['base_hp'];

    $this->changed['level'] = $this->c['level'];
    $this->changed['base_hp'] = $this->c['base_hp'];
    $this->changed['current_hp'] = $this->c['current_hp'];
  }
  function setBaseHp($x) {
    $this->c['base_hp'] = intval($x);
    $this->changed['base_hp'] = $this->c['base_hp'];
  }
  function setCurrentHp($x) {
    $this->c['current_hp'] = max(intval($x), 0);
    $this->changed['current_hp'] = $this->c['current_hp'];
  }
  function setMana($x) {
    $this->c['mana'] = max(intval($x), 0);
    $this->changed['mana'] = $this->c['mana'];
  }
  function setManaMax($x) {
    $this->c['mana_max'] = intval($x);
    $this->changed['mana_max'] = $this->c['mana_max'];
  }
  function setFatigue($x) {
    $this->c['fatigue'] = intval($x);
    $this->changed['fatigue'] = $this->c['fatigue'];
  }
  function setFatigueReduction($x) {
    $this->c['fatigue_reduction'] = intval($x);
    $this->changed['fatigue_reduction'] = $this->c['fatigue_reduction'];
  }
  function setFatigueRested($x) {
    $this->c['fatigue_rested'] = intval($x);
    $this->changed['fatigue_rested'] = $this->c['fatigue_rested'];
  }
  function setXp($x) {
    $this->c['xp'] = intval($x);
    $this->changed['xp'] = $this->c['xp'];
  }
  function setGold($x) {
    $this->c['gold'] = intval($x);
    $this->changed['gold'] = $this->c['gold'];
  }
  function setGoldBank($x) {
    $this->c['gold_bank'] = intval($x);
    $this->changed['gold_bank'] = $this->c['gold_bank'];
  }

  function setIdPair($v, $a, $e) {
    $this->c[$v]['id'] = intval($a);
    $this->c[$v . '_enc'] = intval($e);
    $this->changed[$v] = $this->c[$v]['id'];
    $this->changed[$v . '_enc'] =
        $this->c[$v . '_enc'];
    unset($_SESSION['equipped_array']);
  }

  function setIntVar($k, $v) {
    $this->c[$k] = intval($v);
    $this->changed[$k] = $this->c[$k];
  }
  function setStrVar($k, $v) {
    $this->c[$k] = htmlspecialchars($v);
    $this->changed[$k] = $this->c[$k];
  }

  function setMountId($a) {
    $this->c['mount_id'] = $a;
    $this->changed['mount_id'] = $this->c['mount_id'];
  }
  function setMountName($n) {
    $this->c['mount_name'] = htmlspecialchars($n);
    $this->changed['mount_name'] = $this->c['mount_name'];
  }
  function setEncounterId($x) {
    $this->c['encounter_id'] = htmlspecialchars($x);
    $this->changed['encounter_id'] = $this->c['encounter_id'];
  }
  function setEncounterType($x) {
    $this->c['encounter_type'] = htmlspecialchars($x);
    $this->changed['encounter_type'] = $this->c['encounter_type'];
  }
  function setEncounterHp($x) {
    $this->c['encounter_hp'] = htmlspecialchars($x);
    $this->changed['encounter_hp'] = $this->c['encounter_hp'];
  }
  function setEncounterMaxHp($x) {
    $this->c['encounter_max_hp'] = htmlspecialchars($x);
    $this->changed['encounter_max_hp'] = $this->c['encounter_max_hp'];
  }
  function setEncounterArtifact($x) {
    $this->c['encounter_artifact'] = htmlspecialchars($x);
    $this->changed['encounter_artifact'] = $this->c['encounter_artifact'];
  }
  function setProfCooking($x) {
    $this->c['prof_cooking'] = htmlspecialchars($x);
    $this->changed['prof_cooking'] = $this->c['prof_cooking'];
  }
  function setProfMining($x) {
    $this->c['prof_mining'] = htmlspecialchars($x);
    $this->changed['prof_mining'] = $this->c['prof_mining'];
  }
  function setProfFishing($x) {
    $this->c['prof_fishing'] = htmlspecialchars($x);
    $this->changed['prof_fishing'] = $this->c['prof_fishing'];
  }
  function setProfCrafting($x) {
    $this->c['prof_crafting'] = htmlspecialchars($x);
    $this->changed['prof_crafting'] = $this->c['prof_crafting'];
  }
  function setChatChannel($x) {
    $this->c['chat_channel'] = htmlspecialchars($x);
    $this->changed['chat_channel'] = $this->c['chat_channel'];
    $_SESSION['cc'] = $this->c['chat_channel'];
  }
  function setChatChannelType($x) {
    $this->c['chat_channel_type'] = htmlspecialchars($x);
    $this->changed['chat_channel_type'] = $this->c['chat_channel_type'];
    $_SESSION['cc_type'] = $this->c['chat_channel_type'];
  }
  function setTotalCombats($a) {
    $this->c['total_combats'] = intval($a);
    $this->changed['total_combats'] = $this->c['total_combats'];
  }
  function setTotalFatigueUses($a) {
    $this->c['total_fatigue_uses'] = intval($a);
    $this->changed['total_fatigue_uses'] = $this->c['total_fatigue_uses'];
  }
  function setTotalFatigue($a) {
    $this->c['total_fatigue'] = intval($a);
    $this->changed['total_fatigue'] = $this->c['total_fatigue'];
  }
  function setGuildId($a) {
    $this->c['guild_id'] = $a;
    $this->changed['guild_id'] = $this->c['guild_id'];
  }
  function setGuildName($n) {
    $this->c['guild_name'] = htmlspecialchars($n);
    $this->changed['guild_name'] = $this->c['guild_name'];
  }
  function setGuildRank($a) {
    $this->c['guild_rank'] = $a;
    $this->changed['guild_rank'] = $this->c['guild_rank'];
  }
  function setDungeonRun($d_id, $d_run) {
    $this->c['d_id'] = intval($d_id);
    $this->c['d_run'] = intval($d_run);
    $this->changed['d_id'] = $this->c['d_id'];
    $this->changed['d_run'] = $this->c['d_run'];
  }
  function setAvatar($a) {
    $this->c['avatar'] = htmlspecialchars($a);
    $this->changed['avatar'] = $this->c['avatar'];
  }

  function resetActionId() {
    $this->c['action_id'] = rand(100000, 2000000000);
    $this->changed['action_id'] = $this->c['action_id'];
  }

  function calculateHpValues() {
    $con = $this->c['con'] + $this->c['con_bonus'];

    $this->c['base_hp'] = 12 + $this->c['base_hp_bonus'] +
        (($con - 9) * (log($con) * 3)) +
        (($this->c['level'] - 1) * (log($this->c['level']) * 5));

/*    $this->c['base_hp'] = 12 + $this->c['base_hp_bonus'] +
        ($this->c['con'] + $this->c['con_bonus']) *
        ($this->c['level'] - 1);/**/

    $this->c['base_hp'] = applyMultiplier(
        $this->c['base_hp'], $this->c['bonus_health_percent']);

    if ($this->c['current_hp'] > $this->c['base_hp']) {
      $this->setCurrentHp($this->c['base_hp']);
    }
  }

  function addFatigue($x) {
    $this->c['total_fatigue'] += $x;
    $this->changed['total_fatigue'] = $this->c['total_fatigue'];

    $f_add = applyMultiplier($x, -$this->c['fatigue_reduction_bonus']);
    $f_rest = $this->c['fatigue_rested'];
    if ($f_rest > 0) {
      $f_bonus = min(round($f_add / 2.0), $f_rest);
      $f_add = $f_add - $f_bonus;
      $this->c['fatigue_rested'] = $f_rest - $f_bonus;
      $this->changed['fatigue_rested'] = $this->c['fatigue_rested'];
    }

    $this->c['fatigue'] += $f_add;
    $this->changed['fatigue'] = $this->c['fatigue'];

    return $this->c['fatigue'];
  }

  function addXp($x) {
    $xp_add = applyMultiplier($x, $this->c['xp_bonus']);
    $this->c['xp'] += $xp_add;
    $this->changed['xp'] = $this->c['xp'];

    return $xp_add;
  }

  function addFlag($flag_id, $flag_value) {
    $this->flag_obj->addFlag($this, $flag_id, $flag_value);
  }

  function enableFlagBit($flag_id, $flag_bit) {
    return $this->flag_obj->enableFlagBit($this, $flag_id, $flag_bit);
  }

  function disableFlagBit($flag_id, $flag_bit) {
    return $this->flag_obj->disableFlagBit($this, $flag_id, $flag_bit);
  }

  function awardArtifact($artifact_id, $artifact_quantity, $m_enc = 0) {
    return $this->artifact_obj->awardArtifact(
        $artifact_id, $artifact_quantity, $m_enc);
  }
  function clearArtifactAwards() {
    $this->artifact_obj->clearArtifactAwards();
  }
  function getInventory() {
    return $this->c['inventory_obj']->getInventory();
  }
  function saveInventory() {
    if ($this->artifact_obj != NULL) {
      $this->artifact_obj->save($this->c['id']);
    }
  }
}

function setArmour($c_obj, $armour_type, $armour_id, $m_enc = 0) {
  $a = esc($armour_id);

  unset($_SESSION['equipped_array']);

  switch ($armour_type) {
  case sg_artifact_armour_head:
    $c_obj->setIdPair('armour_head', $a, $m_enc); break;
  case sg_artifact_armour_chest:
    $c_obj->setIdPair('armour_chest', $a, $m_enc); break;
  case sg_artifact_armour_legs:
    $c_obj->setIdPair('armour_legs', $a, $m_enc); break;
  case sg_artifact_armour_neck:
    $c_obj->setIdPair('armour_neck', $a, $m_enc); break;
  case sg_artifact_armour_hands:
    $c_obj->setIdPair('armour_hands', $a, $m_enc); break;
  case sg_artifact_armour_wrists:
    $c_obj->setIdPair('armour_wrists', $a, $m_enc); break;
  case sg_artifact_armour_belt:
    $c_obj->setIdPair('armour_belt', $a, $m_enc); break;
  case sg_artifact_armour_boots:
    $c_obj->setIdPair('armour_boots', $a, $m_enc); break;
  case sg_artifact_armour_trinket:
    if ($c_obj->c['armour_trinket']['id'] == 0) {
      $c_obj->setIdPair('armour_trinket', $a, $m_enc);
    } elseif ($c_obj->c['armour_trinket_2']['id'] == 0) {
      $c_obj->setIdPair('armour_trinket_2', $a, $m_enc);
    } elseif ($c_obj->c['armour_trinket_3']['id'] == 0) {
      $c_obj->setIdPair('armour_trinket_3', $a, $m_enc);
    } else {
      // TODO: catastrophe
    }
    break;
  case sg_artifact_armour_ring:
    if ($c_obj->c['armour_ring']['id'] == 0) {
      $c_obj->setIdPair('armour_ring', $a, $m_enc);
    } elseif ($c_obj->c['armour_ring_2']['id'] == 0) {
      $c_obj->setIdPair('armour_ring_2', $a, $m_enc);
    } else {
      // TODO: catastrophe
    }
    break;
  default: return FALSE; break;
  }

  return TRUE;
}

?>