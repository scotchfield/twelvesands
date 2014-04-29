<?

require_once 'include/core.php';

require_once sg_base_path . 'include/achieve.php';
require_once sg_base_path . 'include/constants.php';
require_once sg_base_path . 'include/flag.php';
require_once sg_base_path . 'include/foes.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/quests.php';
require_once sg_base_path . 'include/reputation.php';
require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/zones.php';

function levelXp($level) {
  $levelXp = array(
    1 => 0, 100, 500, 1500, 3500,
    9000, 18000, 30000, 46000, 69000,
    103500, 155250, 232875, 349310, 523965,
    785945, 1178915, 1768370, 2652555, 3978830,
    5968245, 8952365, 13428545, 20142815, 30214220,
    45321330, 67981995, 101972990, 152959485, 229439225,
    344158835
  );

  return $levelXp[$level];
}

function fixStr($st) {
//  $st = str_replace('\'', '`', $st);
  $st = htmlentities($st, ENT_QUOTES);
  $st = utf8_encode($st);
  $st = str_replace('\'', '&#039;', $st);
  return $st;
}

function getEscapeQuoteStr($st) {
  return str_replace('&#039;', '\\&#039;', $st);
}

function getBasicStr($s) {
  $st = '';
  $valid_chars = array(
      '1','2','3','4','5','6','7','8','9','0',
      'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p',
      'q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F',
      'G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V',
      'W','X','Y','Z',' ','_','-','.');
  for ($i = 0; $i < strlen($s); $i++) {
    $c = $s[$i];
    if (in_array($c, $valid_chars)) {
      $st = $st . $c;
    }
  }
  return $st;
}

function getGetInt($id, $default) {
  $i = $default;
  if (isset($_GET[$id])) { $i = intval($_GET[$id]); }
  return $i;
}

function getGetStr($id, $default) {
  $st = $default;
  if (isset($_GET[$id])) { $st = fixStr($_GET[$id]); }
  return $st;
}

function getPostInt($id, $default) {
  $i = $default;
  if (isset($_POST[$id])) { $i = intval($_POST[$id]); }
  return $i;
}

function getPostStr($id, $default) {
  $st = $default;
  if (isset($_POST[$id])) { $st = fixStr($_POST[$id]); }
  return $st;
}

function getResourceAssocById($results, $utf8_obj = array()) {
  $obj = array();
  if (!$results) { return $obj; }
  while ($o = $results->fetch_assoc()) {
    foreach ($utf8_obj as $u) {
      $o[$u] = utf8_encode($o[$u]);
    }
    $obj[$o['id']] = $o;
  }
  return $obj;
}


function getArtifactArray($id_array) {
  $a_list = join(',', $id_array);
  $query = "
    SELECT
      *
    FROM
      `artifacts`
    WHERE
      id IN ($a_list)
  ";

  $results = sqlQuery($query);
  $a_array = array();

  while ($artifact = $results->fetch_assoc()) {
    $artifact['name'] = fixStr($artifact['name']);
    $artifact['plural_name'] = fixStr($artifact['plural_name']);
    $artifact['text'] = fixStr($artifact['text']);
    $artifact['o_name'] = fixStr($artifact['o_name']);
    $a_array[$artifact['id']] = $artifact;
  }

  return $a_array;
}

function getArtifact($artifact_id, $m_enc = 0) {
  $a = intval($artifact_id);

  $query = "
    SELECT
      *
    FROM
      `artifacts`
    WHERE
      id = '$a'
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $artifact = $results->fetch_assoc();
  $artifact['name'] = fixStr($artifact['name']);
  $artifact['plural_name'] = fixStr($artifact['plural_name']);
  $artifact['text'] = fixStr($artifact['text']);
  $artifact['o_name'] = fixStr($artifact['o_name']);

  $artifact['m_enc'] = $m_enc;

  return $artifact;
}

function getArtifactByDesc($artifact_id, $m_enc = 0) {
  $a = intval($artifact_id);

  $query = "
    SELECT
      *
    FROM
      `artifacts`
    WHERE
      desc_id = '$a'
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $artifact = $results->fetch_assoc();
  $artifact['name'] = fixStr($artifact['name']);
  $artifact['plural_name'] = fixStr($artifact['plural_name']);
  $artifact['text'] = fixStr($artifact['text']);
  $artifact['o_name'] = fixStr($artifact['o_name']);

  $artifact['m_enc'] = $m_enc;

  return $artifact;
}

function getAllZoneEncounters($zone_id) {
  $z = intval($zone_id);

  $query = "
    SELECT
      *
    FROM
      `zone_encounters`
    WHERE
      zone_id = '$z'
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $zone_encounters = array();
  while ($ze = $results->fetch_assoc()) {
    $zone_encounters[$ze['id']] = $ze;
  }

  return $zone_encounters;
}

function getChoiceEncounter($e_id) {
  $e = intval($e_id);
  $query = "SELECT * FROM `choice_encounters` WHERE id=$e";
  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $treasure = $results->fetch_assoc();
  $treasure['name'] = utf8_encode($treasure['name']);
  $treasure['text'] = utf8_encode($treasure['text']);
  $treasure['type'] = sg_encounter_choice;
  return $treasure;
}

function getTreasure($e_id) {
  $e = intval($e_id);
  $query = "SELECT * FROM `treasures` WHERE id=$e";
  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $treasure = $results->fetch_assoc();
  $treasure['name'] = utf8_encode($treasure['name']);
  $treasure['text'] = utf8_encode($treasure['text']);
  $treasure['type'] = sg_encounter_treasure;
  return $treasure;
}

function getEncounter($c_obj, $zone_id) {
  $zone_encounters = getAllZoneEncounters($zone_id);
  $zone_enc = array();

  if ($zone_encounters == FALSE) {
    return FALSE;
  }

  $max_val = 0;
  foreach ($zone_encounters as $ze) {
    $add_encounter = TRUE;

    if ($ze['artifact_required'] > 0) {
      if (!hasArtifact($c_obj, $ze['artifact_required'], 0, FALSE)) {
        $add_encounter = FALSE;
      }
    }

    if (($ze['flag_id_set'] > 0) &&
        (getFlagBit($c_obj,
                    $ze['flag_id_set'], $ze['flag_bit_set']) == TRUE)) {
      $add_encounter = FALSE;
    }

    if ($add_encounter == TRUE) {
      $bonus = 0;
      if ($ze['type'] == sg_encounter_treasure) {
        $bonus += $ze['odds_of_occurring'] *
            $c_obj->c['noncombat_frequency_boost'];
      }
      $ze['odds_of_occurring'] = $ze['odds_of_occurring'] + $bonus;
      $max_val = $max_val + $ze['odds_of_occurring'];
      $zone_enc[$ze['id']] = $ze;
    }
  }

  $e_id = getChoiceId($zone_enc, $max_val);
  $e = $zone_enc[$e_id];

  if ($e['type'] == sg_encounter_treasure) {
    $encounter = getTreasure($e['encounter_id']);
  } else if ($e['type'] == sg_encounter_foe) {
    $encounter = getFoe($c_obj, $e['encounter_id']);
  } else if ($e['type'] == sg_encounter_choice) {
    $encounter = getChoiceEncounter($e['encounter_id']);
  } else {
    return FALSE;
  }

  $encounter['type'] = $e['type'];
  $encounter['artifact_required'] = $e['artifact_required'];
  $encounter['flag_id_set'] = $e['flag_id_set'];
  $encounter['flag_bit_set'] = $e['flag_bit_set'];
  return $encounter;
}

function getNpc($c_obj, $npc_id) {
  $n = intval($npc_id);

  $query = "
    SELECT
      id, name, description, zone
    FROM
      `npcs`
    WHERE
      id = '$n'
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $npc = $results->fetch_assoc();
  $npc['name'] = utf8_encode($npc['name']);
  $npc['description'] = utf8_encode($npc['description']);
  $npc['quests'] = array();
  $quest_status = array();

  $query = "
    SELECT
      id, char_id, quest_id, status
    FROM
      `char_quests`
    WHERE
      char_id = '" . $c_obj->c['id'] . "' AND status = '" . sg_quest_done . "'
  ";

  $results = sqlQuery($query);

  if ($results) {
    while ($quest = $results->fetch_assoc()) {
      $quest_status[$quest['quest_id']] = $quest;
    }
  }

  $query = "
    SELECT
      q.*
    FROM
      `quests` AS q
    WHERE
      q.npc_id = '" . $npc['id'] . "' AND q.min_level <= '" .
      $c_obj->c['level'] . "'
  ";

  $results = sqlQuery($query);

  if ($results) {
    while ($quest = $results->fetch_assoc()) {
      if (($quest_status[$quest['id']]['status'] != sg_quest_done) ||
          ($quest['repeatable'] == 1)) {
        $quest['name'] = utf8_encode($quest['name']);
        $npc['quests'][$quest['id']] = $quest;
      }
    }
  }

  return $npc;
}

function inCombat($c_obj) {
  if ($c_obj->c['encounter_id'] != 0) {
    return TRUE;
  }
  return FALSE;
}

function forceCombatCheck($c_obj) {
  if ($c_obj->c['encounter_id'] != 0) {
    if ($c_obj->c['encounter_type'] == sg_encountertype_foe) {
      header('Location: fight.php');
      exit;
    } elseif ($c_obj->c['encounter_type'] == sg_encountertype_duel) {
      unset($_SESSION['flags']);
      header('Location: duel.php');
      exit;
    } elseif ($c_obj->c['encounter_type'] == sg_encountertype_choice) {
      header('Location: choice.php');
    }
  }
}

function checkZoneLevel($zone, $char_obj) {
  if (($zone == FALSE) || ($zone['min_level'] > $char_obj->c['level'] )) {
    $zone['name'] = '';
    include '_header.php';
    echo '<p class="zone_title">Unknown Zone</p>';
    echo '<p class="zone_description">You have no idea where you are.</p>';
    echo '<a href="main.php">Go home, and quick!</a>';
    include '_footer.php';
    exit;
  }
}

function levelCheck($c_obj) {
  $xpToGo = levelXp($c_obj->c['level'] + 1) - $c_obj->c['xp'];

  if ($xpToGo < 1) {
    $c_obj->setLevel($c_obj->c['level'] + 1);
    $st = awardAchievement($c_obj, 41);
    return '<p class="tip"><b>You gain a level!</b></p>' . $st;
  }

  return FALSE;
}

function baseRandomNumber($base, $rand) {
  return $base + rand(0, $rand);
}

function checkIfFatigued($c_obj) {
  if ($c_obj->c['fatigue'] >= 100000) {
    return TRUE;
  }
  return FALSE;
}

function checkIfWounded($c_obj) {
  if ($c_obj->c['current_hp'] <= 0) {
    return TRUE;
  }
  return FALSE;
}

function checkIfBurdened($c_obj) {
  if ($c_obj->c['burden'] >= 3) {
    return TRUE;
  }
  return FALSE;
}

function hasBuff($c_obj, $b_id) {
  $b_id = intval($b_id);
  if (isset($c_obj->c['buffs'][$b_id])) {
    $time = time();
    if ($c_obj->c['buffs'][$b_id]['expires'] >= $time) {
      return TRUE;
    }
  }
  return FALSE;
}

function addBuff($c_obj, $b_id,
                 $expires_in, $combat_turn_expires, $use_bonus) {
  $b = intval($b_id);
  $e = intval($expires_in);
  $cte = intval($combat_turn_expires);
  if (TRUE == $use_bonus) {
    $e = applyMultiplier($e, $c_obj->c['buff_bonus']);
  }
  $true_expires = time() + $e;

  $query = "
    INSERT INTO
      `char_buffs` (char_id, buff_id, expires, combat_turn_expires)
    VALUES
      ('" . $c_obj->c['id'] . "', '$b', '$true_expires', '$cte')
  ";
  $results = sqlQuery($query);

  $buff = getBuff($b);
  $buff['expires'] = $e;
  $buff['combat_turn_expires'] = $cte;

  $c_obj->c['buffs'][$b] = $buff;

  unset($_SESSION['buffs']);

  return $buff;
}

function deleteBuff($c_obj, $b_id) {
  $b = esc($b_id);

  $query = "
    DELETE FROM
      `char_buffs`
    WHERE
      char_id = " . $c_obj->c['id'] . " AND buff_id = $b
  ";

  $results = sqlQuery($query);

  unset($_SESSION['buffs']);
}

function deleteAllBuffs($c_obj) {
  $query = "
    DELETE FROM
      `char_buffs`
    WHERE
      char_id = " . $c_obj->c['id'] . "
  ";
  $results = sqlQuery($query);

  unset($_SESSION['buffs']);
}

function updateBuff($c_obj, $b_id, $expires_in, $ct_expires, $use_bonus) {
  deleteBuff($c_obj, $b_id);
  $buff = addBuff($c_obj, $b_id, $expires_in, $ct_expires, $use_bonus);
  return $buff;
}

function applyBuff($c_obj, $buff, $type_id, $value_id) {
  $attr_map = array(
    sg_skills_bonus_str => 'str_bonus',
    sg_skills_bonus_dex => 'dex_bonus',
    sg_skills_bonus_int => 'int_bonus',
    sg_skills_bonus_cha => 'cha_bonus',
    sg_skills_bonus_con => 'con_bonus',
    sg_skills_bonus_max_health => 'base_hp_bonus',
    sg_skills_fatigue_reduction => 'fatigue_reduction_bonus',
    sg_skills_gold_drop_boost => 'gold_bonus',
    sg_skills_bonus_melee_damage => 'melee_dmg_bonus',
    sg_skills_bonus_defend_damage => 'defend_dmg_bonus',
    sg_skills_bonus_xp_award_percent => 'xp_bonus',
    sg_skills_bonus_rep_award_percent => 'rep_bonus',
    sg_skills_bonus_fishing => 'fishing_bonus',
    sg_skills_bonus_mining => 'mining_bonus',
    sg_skills_item_drop_boost => 'item_bonus',
    sg_skills_bonus_buff_duration => 'buff_bonus',
    sg_skills_bonus_food_reduction => 'food_reduction',
    sg_skills_resist_fire => 'resist_' . sg_attacktype_fire,
    sg_skills_resist_water => 'resist_' . sg_attacktype_water,
    sg_skills_resist_earth => 'resist_' . sg_attacktype_earth,
    sg_skills_resist_air => 'resist_' . sg_attacktype_air,
    sg_skills_resist_arcane => 'resist_' . sg_attacktype_arcane,
    sg_skills_resist_electric => 'resist_' . sg_attacktype_electric,
    sg_skills_resist_necro => 'resist_' . sg_attacktype_necromancy,
    sg_skills_bonus_armour => 'armour',
    sg_skills_bonus_dodge => 'dodge_bonus',
    sg_skills_bonus_initiative => 'initiative_bonus',
    sg_skills_bonus_hunger => 'hunger_bonus',
    sg_skills_bonus_crafting_xp => 'crafting_xp_bonus',
    sg_skills_noncombat_freq_boost => 'noncombat_frequency_boost',
    sg_skills_bonus_crit => 'bonus_crit',
    sg_skills_bonus_to_hit => 'bonus_to_hit',
    sg_skills_bonus_mana_percent => 'bonus_mana_percent',
    sg_skills_bonus_armour_percent => 'bonus_armour_percent',
    sg_skills_bonus_health_percent => 'bonus_health_percent',
    sg_skills_mana_regen => 'mana_regen',
    sg_skills_bonus_melee_damage_percent => 'bonus_melee_dmg_percent',
    sg_skills_bonus_spell_damage => 'spell_dmg_bonus',
    sg_skills_hp_regen => 'hp_regen',
    sg_skills_rested_eating_bonus => 'rested_eating_bonus',
    sg_skills_resist_magical => 'resist_' . sg_attacktype_magical,
    sg_skills_xp_combat_bonus => 'xp_combat_bonus',
    sg_skills_pravokan => 'pravokan_bonus',
    sg_skills_fishing_fatigue_percent => 'fishing_fatigue',
    sg_skills_bonus_magic_dmg_percent => 'bonus_magic_dmg_percent',
    sg_skills_resist_physical => 'resist_' . sg_attacktype_physical,
    sg_skills_convert_magic_to_dmg_percent => 'convert_magic_dmg',
    sg_skills_reduce_cook_craft_fatigue_percent => 'reduce_craft_fatigue',
    sg_skills_dunnich => 'dunnich_bonus',
  );

  if (array_key_exists($buff[$type_id], $attr_map)) {
    $c_obj->c[$attr_map[$buff[$type_id]]] += $buff[$value_id];
  } elseif ($buff[$type_id] == sg_skills_bonus_all) {
    $c_obj->c['str_bonus'] += $buff[$value_id];
    $c_obj->c['dex_bonus'] += $buff[$value_id];
    $c_obj->c['int_bonus'] += $buff[$value_id];
    $c_obj->c['cha_bonus'] += $buff[$value_id];
    $c_obj->c['con_bonus'] += $buff[$value_id];
  }
  if ( ( isset( $buff[ 'stat_type' ] ) ) && ( $buff[ 'stat_type' ] > 0 ) ) {
    $c_obj->c[$attr_map[$buff['stat_type']]] += $buff['stat_value'];
  }
}

function getChoiceId($ze_array, $max_value) {
  $i = 0;
  $selected_val = rand(0, $max_value - 1);

  foreach ($ze_array as $x) {
    $i += $x['odds_of_occurring'];
    if ($selected_val < $i) {
      return $x['id'];
    }
  }

  return NULL;
}

function getBuff($buff_id) {
  $b = esc($buff_id);

  $query = "
    SELECT
      *
    FROM
      `buffs`
    WHERE
      id = $b
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $buff = $results->fetch_assoc();
  $buff['name'] = utf8_encode($buff['name']);
  return $buff;
}

function getAvailableQuests($c_obj) {
  $filename = '/home/swrittenb/ts_util/quests/quest_lookup.inc';
  if (file_exists($filename)) {
    include $filename;

    $qs = array();
    foreach ($quest_lookup as $q) {
      $q_add = FALSE;

      if ($q['min_level'] <= $c_obj->c['level']) {
        if (array_key_exists($q['id'], $c_obj->c['quests'])) {
          if ($q['repeatable'] == 1) {
            $q_add = TRUE;
          }
        } else {
          if (($q['quest_required'] == 0) ||
              ((array_key_exists($q['quest_required'], $c_obj->c['quests'])) &&
               ($c_obj->c['quests'][$q['quest_required']]['status'] > 0))) {
            if (($q['artifact_required'] == 0) ||
                (($q['artifact_required'] > 0) &&
                 (hasArtifact($c_obj, $q['artifact_required'])))) {
              $q_add = TRUE;
            }
          }
        }
      }

      if ($q_add == TRUE) {
        $q['name'] = utf8_encode($q['name']);
        $qs[] = $q;
      }
    }

    return $qs;
  } else {
    return array();
  }
}

function renderCharCss($c) {
  echo '<link rel="stylesheet" type="text/css" href="css/site.css?' .
       sg_css_version . '">';

  if ((is_array($c)) && (array_key_exists('flags', $c)) &&
      (array_key_exists(sg_flag_css_background_color, $c['flags'])) &&
      ($c['flags'][sg_flag_css_background_color] == sg_css_bgflag_beige)) {
    echo '<link rel="stylesheet" type="text/css" href="css/bg_beige.css?' .
         sg_css_version . '">';
  }

  if ($c['id'] == 1) {
//    echo '<link rel="stylesheet" type="text/css" href="css/scott.css?' .
//         time() . '">';
  }
}

function getArtifactQuantity($c_obj, $a_id, $m_enc = 0) {
  $artifact = hasArtifact($c_obj, $a_id, $m_enc, FALSE);
  if (FALSE == $artifact) { return 0; }
  if (!isset($artifact['quantity'])) { return 0; }
  return $artifact['quantity'];
}

function canAffordStore($c_obj, $artifact, $n) {
  if ($artifact['buy_price'] > 0) {
    if ($c_obj->c['gold'] < ($artifact['buy_price'] * $n)) { return FALSE; }
  }
  if ($artifact['gold_cost'] > 0) {
    if ($c_obj->c['gold'] < ($artifact['gold_cost'] * $n)) { return FALSE; }
  }
  if ($artifact['reputation_id'] > 0) {
    if ($artifact['reputation_required'] >
        $c_obj->c['reputations'][$artifact['reputation_id']]['value']) {
      return FALSE;
    }
  }
  if ($artifact['artifact_cost_1'] > 0) {
    if (getArtifactQuantity($c_obj, $artifact['artifact_cost_1']) <
            ($artifact['artifact_quantity_1'] * $n)) {
      return FALSE;
    }
  }
  if ($artifact['artifact_cost_2'] > 0) {
    if (getArtifactQuantity($c_obj, $artifact['artifact_cost_2']) <
            ($artifact['artifact_quantity_2'] * $n)) {
      return FALSE;
    }
  }
  if ($artifact['artifact_cost_3'] > 0) {
    if (getArtifactQuantity($c_obj, $artifact['artifact_cost_3']) <
            ($artifact['artifact_quantity_3'] * $n)) {
      return FALSE;
    }
  }
  return TRUE;
}

function initiateCombat($c_obj, $encounter, $zone) {
  if ($zone != NULL) {
    $c_obj->addFlag(sg_flag_last_combat_zone, $zone['id']);
  }

  $c_obj->setEncounterId($encounter['id']);
  $c_obj->setEncounterType(sg_encountertype_foe);
  $c_obj->setEncounterHp($encounter['hp']);
  $c_obj->setEncounterMaxHp($encounter['hp']);
  $c_obj->setEncounterArtifact($encounter['artifact_required']);

  $c_obj->addFlag(sg_flag_es1, 1);
  if (getFlagValue($c_obj, sg_flag_es2) != 0) {
    $c_obj->addFlag(sg_flag_es2, 0);
  }
  if (getFlagValue($c_obj, sg_flag_es3) != 0) {
    $c_obj->addFlag(sg_flag_es3, 0);
  }
  if (getFlagValue($c_obj, sg_flag_es4) != 0) {
    $c_obj->addFlag(sg_flag_es4, 0);
  }
  if (getFlagValue($c_obj, sg_flag_es5) != 0) {
    $c_obj->addFlag(sg_flag_es5, 0);
  }

  $c_obj->addFlag(sg_flag_combat_flag_id_set,
                     $encounter['flag_id_set']);
  $c_obj->addFlag(sg_flag_combat_flag_bit_set,
                     $encounter['flag_bit_set']);
  $c_obj->addFlag(sg_flag_game_flag_increase,
                     $encounter['game_flag_increase']);
  $c_obj->addFlag(sg_flag_game_flag_decrease,
                     $encounter['game_flag_decrease']);

  header('Location: fight.php');
}

function applyMultiplier($val, $mult) {
  return round($val * (1.0 + (0.01 * $mult)));
}

function getCharArtifactQuantity($c_obj, $i,
                                 $modifier = 0, $get_artifact = FALSE) {
  $artifact = hasArtifact($c_obj, $i, $modifier, $get_artifact);
  if (FALSE == $artifact) { return 0; }
  if (!isset($artifact['quantity'])) { return 0; }
  return $artifact['quantity'];
}

function renderPopupText() {
  echo '<div id="popup" class="invis"></div>';
  echo '<script type="text/javascript" language="javascript" ' .
       'src="include/ts_popup.js"></script>';
}

function generateJsonProfile($c_obj) {
  // TODO: fix for new installs
  return;

  $dest = '/home/swrittenb/profiles.twelvesands.com/j/' .
      $c_obj->c['id'] . '.json';

  touch($dest);
  if ((is_writable($dest)) && ($f_handle = fopen($dest, 'w'))) {
    $data = array();

    $keys = array('id', 'name', 'titled_name', 'level',
        'str', 'dex', 'int', 'cha', 'con', 'base_hp', 'mana_max',
        'xp', 'gold', 'armour',
        'str_bonus', 'dex_bonus', 'int_bonus', 'cha_bonus', 'con_bonus');
    foreach ($keys as $x) {
      $data[$x] = $c_obj->c[$x];
    }

    $keys = array('weapon', 'armour_head', 'armour_chest', 'armour_legs',
        'armour_neck', 'armour_trinket', 'armour_trinket_2',
        'armour_trinket_3', 'armour_hands', 'armour_wrists',
        'armour_belt', 'armour_boots', 'armour_ring', 'armour_ring_2',
        'mount');
    foreach ($keys as $x) {
      $data[$x] = array();
      $data[$x]['name'] = $c_obj->c[$x]['name'];
      $data[$x]['rarity'] = $c_obj->c[$x]['rarity'];
      $data[$x]['desc'] = getArtifactHoverStr($c_obj->c[$x]);
    }

    fwrite($f_handle, json_encode($data));
  }
}

function generateSigProfile($c_obj) {
  // TODO: fix for new installs
  return;

  $dest = '/home/swrittenb/images.twelvesands.com/sig/' .
      $c_obj->c['id'] . '.png';

  $sig = imagecreatefrompng('/home/swrittenb/images.twelvesands.com/' .
      'sig_base_3.png');
  if (!$sig) { return; }

  $colour_black = imagecolorallocate($sig, 0, 0, 0);
  $font = '/home/swrittenb/images.twelvesands.com/fsc.ttf';

  imagettftext($sig, 16, 0, 4, 18, $colour_black, $font,
      $c_obj->c['titled_name']);
  imagettftext($sig, 11, 0, 9, 33, $colour_black, $font,
      'Level: ' . $c_obj->c['level']);
  imagettftext($sig, 11, 0, 9, 47, $colour_black, $font,
      'Gold: ' . $c_obj->c['gold']);
  imagettftext($sig, 11, 0, 124, 33, $colour_black, $font,
      'Health: ' . $c_obj->c['current_hp'] . ' / ' . $c_obj->c['base_hp']);
  imagettftext($sig, 11, 0, 124, 47, $colour_black, $font,
      'Mana: ' . $c_obj->c['mana'] . ' / ' . $c_obj->c['mana_max']);
  imagettftext($sig, 11, 0, 259, 33, $colour_black, $font,
      'Armour: ' . $c_obj->c['armour']);
  imagettftext($sig, 11, 0, 259, 47, $colour_black, $font,
      'Achievements: ' . count($c_obj->c['achievements']));

  imagepng($sig, $dest);
  imagedestroy($sig);
}

function sortArmourArray($a, $b) {
  if ($a['type'] < $b['type']) {
    return -1;
  } elseif ($a['type'] > $b['type']) {
    return 1;
  } else {
    return ($a['name'] < $b['name']) ? -1 : 1;
  }
  return 0;
}

function getDungeonRunHistory($char_id) {
  $c_id = intval($char_id);

  $query = "
    SELECT
      *
    FROM
      `dungeon_runs`
    WHERE
      char_id = $c_id
    ORDER BY
      date_started
  ";

  $results = sqlQuery($query);
  $d_obj = array();

  if (!$results) { return $d_obj; }

  while ($run = $results->fetch_assoc()) {
    $d_obj[$run['id']] = $run;
  }

  return $d_obj;
}

function createWarfareGame($char_id, $char_name,
                           $wager, $a1, $a2, $a3, $a4, $a5) {
  $char_id = intval($char_id);
  $char_name = fixStr($char_name);
  $wager = intval($wager);
  $a1 = intval($a1);
  $a2 = intval($a2);
  $a3 = intval($a3);
  $a4 = intval($a4);
  $a5 = intval($a5);
  $modified = time() + 10800;

  $query = "
    INSERT INTO
      `warfare_games`
      (`char_id_1`, `char_name_1`, `wager`, `a1`, `a2`, `a3`, `a4`, `a5`,
       `modified`)
    VALUES
      ($char_id, '$char_name', $wager, $a1, $a2, $a3, $a4, $a5,
       $modified)
  ";
  $results = sqlQuery($query);
}

function updateWarfareGame($id, $char_id_1, $char_name_1,
    $char_id_2, $char_name_2, $status, $wager,
    $a1, $a2, $a3, $a4, $a5, $b1, $b2, $b3, $b4, $b5,
    $s1, $s2, $s3, $s4, $s5) {

  $id = intval($id); $status = intval($status); $wager = intval($wager);
  $char_id_1 = intval($char_id_1); $char_id_2 = intval($char_id_2);
  $char_name_1 = fixStr($char_name_1); $char_name_2 = fixStr($char_name_2);
  $a1 = intval($a1); $a2 = intval($a2); $a3 = intval($a3);
  $a4 = intval($a4); $a5 = intval($a5);
  $b1 = intval($b1); $b2 = intval($b2); $b3 = intval($b3);
  $b4 = intval($b4); $b5 = intval($b5);
  $s1 = intval($s1); $s2 = intval($s2); $s3 = intval($s3);
  $s4 = intval($s4); $s5 = intval($s5);
  $modified = time() + 10800;

  $query = "
    UPDATE
      `warfare_games`
    SET
      char_id_1 = $char_id_1, char_id_2 = $char_id_2,
      char_name_1 = '$char_name_1', char_name_2 = '$char_name_2',
      status = $status, wager = $wager,
      a1 = $a1, a2 = $a2, a3 = $a3, a4 = $a4, a5 = $a5,
      b1 = $b1, b2 = $b2, b3 = $b3, b4 = $b4, b5 = $b5,
      s1 = $s1, s2 = $s2, s3 = $s3, s4 = $s4, s5 = $s5,
      modified = $modified
    WHERE
      id = $id
  ";
  $results = sqlQuery($query);
}

function getCharWarfareArtifacts($char_id) {
  $c_id = intval($char_id);
  $artifacts = array();

  $query = "
    SELECT
      a.id, a.name, a.plural_name, a.base_damage,
      SUM(c.quantity) as quantity
    FROM
      char_artifacts AS c, artifacts AS a
    WHERE
      c.char_id = '$c_id' AND c.artifact_id = a.id AND a.type = 201
    GROUP BY
      a.id
    ORDER BY
      a.base_damage ASC, a.name ASC
  ";
  $results = sqlQuery($query);

  if ($results) {
    while ($artifact = $results->fetch_assoc()) {
      $artifact['name'] = fixStr($artifact['name']);
      $artifact['plural_name'] = fixStr($artifact['plural_name']);
      $artifacts[$artifact['id']] = $artifact;
    }
  }

  return $artifacts;
}

function getDungeonRunCount($char_id) {
  $c_id = esc($char_id);

  $query = "
    SELECT
      COUNT(*) AS dungeon_runs
    FROM
      `dungeon_runs`
    WHERE
      char_id = '$c_id'
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $runs = $results->fetch_assoc();
  return $runs['dungeon_runs'];
}

function getWarfareGame($game_id) {
  $g_id = intval($game_id);

  $query = "
    SELECT
      *
    FROM
      `warfare_games`
    WHERE
      id = $g_id
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $game = $results->fetch_assoc();
  return $game;
}

function getWarfareWinner($a, $b) {
  if ($a > $b) { return -1; }
  if ($a < $b) { return 1; }
  if (rand(0, 1) == 0) { return -1; }
  return 1;
}

function getEnchant($i) {
  $e = FALSE;
  switch ($i) {
  case 1: $e = array('m' => 1, 'v' => 1); break;
  case 2: $e = array('m' => 2, 'v' => 1); break;
  case 3: $e = array('m' => 3, 'v' => 1); break;
  case 4: $e = array('m' => 5, 'v' => 1); break;
  case 5: $e = array('m' => 6, 'v' => 1); break;
  case 6: $e = array('m' => 1, 'v' => 2); break;
  case 7: $e = array('m' => 2, 'v' => 2); break;
  case 8: $e = array('m' => 3, 'v' => 2); break;
  case 9: $e = array('m' => 5, 'v' => 2); break;
  case 10: $e = array('m' => 6, 'v' => 2); break;
  case 11: $e = array('m' => 1, 'v' => 3); break;
  case 12: $e = array('m' => 2, 'v' => 3); break;
  case 13: $e = array('m' => 3, 'v' => 3); break;
  case 14: $e = array('m' => 5, 'v' => 3); break;
  case 15: $e = array('m' => 6, 'v' => 3); break;
  case 16: $e = array('m' => 8, 'v' => 3); break;
  case 17: $e = array('m' => 8, 'v' => 8); break;
  case 18: $e = array('m' => 8, 'v' => 20); break;
  case 19: $e = array('m' => 9, 'v' => 1); break;
  case 20: $e = array('m' => 10, 'v' => 3); break;
  case 21: $e = array('m' => 11, 'v' => 3); break;
  case 22: $e = array('m' => 24, 'v' => 3); break;
  case 23: $e = array('m' => 24, 'v' => 5); break;
  case 24: $e = array('m' => 51, 'v' => 5); break;
  case 25: $e = array('m' => 51, 'v' => 15); break;
  }
  return $e;
}

function getGameFlags() {
  $query = "SELECT * from `game_flags`";
  $results = sqlQuery($query);

  $ret_obj = array();

  if ($results) {
    while ($flag = $results->fetch_assoc()) {
      $ret_obj[$flag['flag_id']] = $flag['flag_value'];
    }
  }

  return $ret_obj;
}

function getAvatars($char_id) {
  $char_id = intval($char_id);
  $query = "SELECT * from `avatars` WHERE char_id IN (0, $char_id)";
  $results = sqlQuery($query);

  $ret_obj = array();

  if ($results) {
    while ($avatar = $results->fetch_assoc()) {
      $ret_obj[$avatar['id']] = $avatar;
    }
  }

  return $ret_obj;
}

function getCombatLink($zone_id) {
  if ($zone_id == 111) {
    $st = 'main.php?z=' . $zone_id . '&a=11';
  } else {
    $st = 'main.php?z=' . $zone_id;
  }
  return $st;
}

function addTrackingData($c_obj, $track_id, $track_type, $quantity) {
  $track_id = intval($track_id);
  $track_type = intval($track_type);
  $quantity = intval($quantity);

  if (!isset($c_obj->c['tracking'][$track_type][$track_id])) {
    $query = "INSERT INTO char_track (char_id, track_id, track_type, quantity)
        VALUES (" . $c_obj->c['id'] . ',' . $track_id . ',' . $track_type .
        ',' . $quantity . ')';
  } else {
    $query = "UPDATE char_track SET quantity=quantity+$quantity
        WHERE char_id=" . $c_obj->c['id'] . ' AND track_id=' . $track_id .
        ' AND track_type=' . $track_type;
  }
  sqlQuery($query);

  $_SESSION['tracking'][$track_type][$track_id] =
      $_SESSION['tracking'][$track_type][$track_id] + $quantity;
}

function getTrackingData($c_id, $track_type) {
  $c_id = intval($c_id);
  $track_type = intval($track_type);
  $query = "SELECT * FROM char_track
      WHERE char_id=$c_id AND track_type=$track_type";
  $results = sqlQuery($query);

  $ret_obj = array();
  if ($results) {
    while ($o = $results->fetch_assoc()) {
      $ret_obj[$o['track_id']] = $o['quantity'];
    }
  }

  return $ret_obj;
}

function getIntWithSuffix($i) {
  $q_suffix = 'th';
  if (($i > 3) && ($i <= 20)) { }
  elseif ($i % 10 == 1) { $q_suffix = 'st'; }
  elseif ($i % 10 == 2) { $q_suffix = 'nd'; }
  elseif ($i % 10 == 3) { $q_suffix = 'rd'; }
  return $i . $q_suffix;
}

function getCharStatus($char_id) {
  $char_id = intval($char_id);
  $query = "SELECT status FROM `char_status` WHERE char_id=$char_id";
  $results = sqlQuery($query);
  if (!$results) { return FALSE; }
  $status = $results->fetch_assoc();
  return $status['status'];
}

function updateCharStatus($c_obj, $status) {
  $query = "DELETE FROM `char_status` WHERE char_id=" . $c_obj->c['id'];
  sqlQuery($query);
  $query = "INSERT INTO `char_status` (char_id, char_name, status) VALUES " .
      '(' . $c_obj->c['id'] . ",'" . $c_obj->c['name'] . "','" .
      $status . "')";
  sqlQuery($query);
  $_SESSION['char_status'] = $status;
}

function getCharListStatus($id_obj) {
  $status_obj = array();
  $query = "SELECT char_id AS id, status FROM `char_status` " .
      "WHERE char_id IN (" . join(',', $id_obj) . ')';
  $results = sqlQuery($query);
  return getResourceAssocById($results);
}

function getAllyById($ally_id) {
  $ally_id = intval($ally_id);
  $query = "SELECT * FROM `allies` WHERE id=$ally_id";
  $results = sqlQuery($query);
  if (!$results) { return FALSE; }
  $ally = $results->fetch_assoc();
  $ally['name'] = utf8_encode($ally['name']);
  if ( ! isset( $ally[ 'description' ] ) ) {
      $ally[ 'description' ] = '';
  }
  $ally['description'] = str_replace('\'', '&#039;', $ally['description']);
  $ally['description'] = getEscapeQuoteStr($ally['description']);
  return $ally;
}

function getAlly($c_obj) {
  $ally = getAllyById($c_obj->c['ally_id']);
  if (!$ally) { return FALSE; }
  $ally['fatigue'] = $c_obj->c['ally_fatigue'];
  return $ally;
}

function getUserAllies($user_id) {
  $user_id = intval($user_id);
  $query = "SELECT a.*, u.fatigue
      FROM `allies` AS a, `user_allies` AS u
      WHERE u.user_id=$user_id AND a.id=u.ally_id";
  $results = sqlQuery($query);
  return getResourceAssocById($results, $utf8_obj=array('name'));
}

function getUserAllyList($user_id) {
  $user_id = intval($user_id);
  $ret_obj = array();

  $query = "SELECT ally_id FROM `user_allies` WHERE user_id=$user_id";
  $results = sqlQuery($query);
  if ($results) {
    while ($o = $results->fetch_assoc()) {
      $ret_obj[$o['ally_id']] = TRUE;
    }
  }

  $query = "SELECT ally_id FROM `characters` WHERE user_id=$user_id";
  $results = sqlQuery($query);
  if ($results) {
    while ($o = $results->fetch_assoc()) {
      $ret_obj[$o['ally_id']] = TRUE;
    }
  }

  return $ret_obj;
}

function addUserAlly($c_obj) {
  $query = "INSERT INTO `user_allies` (user_id, ally_id, fatigue)
      VALUES (" . $c_obj->c['user_id'] . ',' . $c_obj->c['ally_id'] .
      ',' . $c_obj->c['ally_fatigue'] . ')';
  $results = sqlQuery($query);
}

function addUserAllyFromId($user_id, $ally_id) {
  $user_id = intval($user_id);
  $ally_id = intval($ally_id);

  $user_ally_list = getUserAllyList($user_id);
  if (isset($user_ally_list[$ally_id])) { return FALSE; }

  $query = "INSERT INTO `user_allies` (user_id, ally_id, fatigue)
      VALUES ($user_id, $ally_id, 0)";
  $results = sqlQuery($query);
  return TRUE;
}

function deleteUserAlly($user_id, $ally_id) {
  $user_id = intval($user_id);
  $ally_id = intval($ally_id);
  $query = "DELETE FROM `user_allies`
      WHERE user_id=$user_id AND ally_id=$ally_id";
  $results = sqlQuery($query);
}

function clearSelect($full) {
  if ($full) {
    unset($_SESSION['c']);
    unset($_SESSION['n']);
    unset($_SESSION['cc']);
    unset($_SESSION['cc_type']);
    unset($_SESSION['tracking']);
    unset($_SESSION['char_status']);
  }
  unset($_SESSION['artifact_array']);
  unset($_SESSION['inventory_obj']);
  unset($_SESSION['achievements']);
  unset($_SESSION['badges']);
  unset($_SESSION['buffs']);
  unset($_SESSION['equipped_array']);
  unset($_SESSION['flags']);
  unset($_SESSION['mail_time_check']);
  unset($_SESSION['quests']);
  unset($_SESSION['reputations']);
  unset($_SESSION['skills']);
  unset($_SESSION['runes']);
  unset($_SESSION['time_check']);
  unset($_SESSION['chat_time_check']);
  unset($_SESSION['chat_msg']);
  unset($_SESSION['duel_time_check']);
  unset($_SESSION['ally']);
  unset($_SESSION['battle_cries']);
  unset($_SESSION['json_timestamp']);
  unset($_SESSION['dungeon_run_count']);
}

function getBattleCries($c_id, $group_id) {
  $c_id = intval($c_id);
  $group_id = intval($group_id);
  $ret_obj = array();

  $query = "SELECT * FROM `cries` WHERE (id=$c_id AND is_char=1) OR
      (id=$group_id AND is_char=0) ORDER BY text ASC";
  $results = sqlQuery($query);
  if ($results) {
    while ($o = $results->fetch_assoc()) {
      $ret_obj[] = $o['text'];
    }
  }

  return $ret_obj;
}

function deleteBattleCries($c_id) {
  $c_id = intval($c_id);
  $query = "DELETE FROM `cries` WHERE id=$c_id AND is_char=1";
  sqlQuery($query);
}

function addBattleCries($c_id, $cries_obj) {
  if (count($cries_obj) > 0) {
    $c_id = intval($c_id);
    $add_obj = array();
    foreach ($cries_obj as $x) {
      $add_obj[] = "($c_id,1,'$x')";
    }
    $add_str = join(",", $add_obj);
    $query = "INSERT INTO `cries` (id, is_char, text) VALUES $add_str";
    sqlQuery($query);
  }
}

?>