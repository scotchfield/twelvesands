<?

require_once 'include/core.php';

require_once sg_base_path . 'include/constants.php';
require_once sg_base_path . 'include/runes.php';
require_once sg_base_path . 'include/sql.php';

$combat_bar_valid_bases = array(
  0 => sg_flag_cb1_start
);

define('sg_combat_physicalbase', 0);
define('sg_combat_spellbase', 1000000);
define('sg_combat_artifactbase', 2000000);
define('sg_combat_system', 3000000);

define('sg_combatspecial_always_hit', 0);

define('sg_foespecial_never_hit_by_crit', 0);

function getAllCombatBarOptions($char_obj) {
  $combat_bar_array = getCombatAttacks($char_obj);
  $spells = getCombatRunes($char_obj);
  foreach ($spells as $k => $v) {
    $combat_bar_array[$k] = $v;
  }
  $artifacts = getCombatArtifacts($char_obj);
  foreach ($artifacts as $k => $v) {
    $combat_bar_array[$k] = $v;
  }

  $last_zone = getZone(getFlagValue($char_obj, sg_flag_last_combat_zone));
  $combat_bar_array[2] = array(
    'n' => 'Adventure Again',
    'u' => getCombatLink($last_zone['id']),
    'i' => 'buff-green.gif',
    'a' => !inCombat($char_obj),
  );
  return $combat_bar_array;
}

function attackDistribution($a) {
  $val = 0.6 + (0.05 * $a);
  if ($val > 0.95) { $val = 0.95; }
  elseif ($val < 0.10) { $val = 0.10; }
  return $val;
}

function get_sign($x) {
   return $x ? ($x > 0 ? 1 : -1) : 0;
}

function getCharacterInitiative($c_obj, $f) {
  $init_roll = rand(0, 100) + $c_obj->c['initiative_bonus'];
  if ($init_roll < 40) { return FALSE; }
  return TRUE;
}

function hitProbability($to_hit, $to_dodge) {
  $x = $to_hit - $to_dodge;
  $x_squared = $x * $x;
  $success_mod = 0.5 * ((get_sign($x) * $x_squared) / (50.0 + $x_squared));
  $success = 0.8 + $success_mod;
  return $success;
}

function criticalProbability($attacker, $defender) {
  $crit_chance = 5 + ($attacker - $defender);
  if (rand(0, 100) < $crit_chance) { return TRUE; }
  return FALSE;
}

function armourDamageResist($armour, $level) {
  $resist = $armour / ($armour + 50.0 + (100.0 * $level));
  return $resist;
}

function resistanceDamageResist($target_base, $target_level,
                                $caster_level, $caster_penetration) {
  $r_val = $target_base + max(($target_level - $caster_level) * 5, 0) -
      min($caster_penetration, $target_base);
  $resist = min(($r_val / ($caster_level * 5)) * 75, 75) / 100.0;
/*  debugPrint($target_base . ', ' .
             $target_level . ', ' .
             $caster_level . ', ' .
             $caster_penetration);
  debugPrint('r_val: ' . $r_val . ', resist: ' . $resist);*/
  return $resist;
}

function getResistanceModifier($attack_type, $resist_array, $vuln_array) {
  $r = 100;

  foreach ($resist_array as $s_key => $s_val) {
    if (($attack_type & (1 << $s_key)) > 0) {
      $r -= $s_val;
    }
  }

  foreach ($vuln_array as $s_key => $s_val) {
    if (($attack_type & (1 << $s_key)) > 0) {
      $r += $s_val;
    }
  }

  if ($r < 10) {
    $r = 10;
  }

  return $r / 100.0;
}

function getResistanceArray($a, $val) {
  $ret_obj = array();

  for ($x = 0; $x < 31; $x++) {
    if (($a & (1 << $x)) > 0) {
      $ret_obj[$x] = $val;
    }
  }

  return $ret_obj;
}

function getDamageType($v) {
  $v_obj = array();
  if (($v & (1 << sg_attacktype_physical))) { $v_obj[] = 'Physical'; }
  if (($v & (1 << sg_attacktype_crush))) { $v_obj[] = 'Crushing'; }
  if (($v & (1 << sg_attacktype_stab))) { $v_obj[] = 'Stabbing'; }
  if (($v & (1 << sg_attacktype_slam))) { $v_obj[] = 'Slamming'; }
  if (($v & (1 << sg_attacktype_acidic))) { $v_obj[] = 'Acidic'; }
  if (($v & (1 << sg_attacktype_slashing))) { $v_obj[] = 'Slashing'; }
  if (($v & (1 << sg_attacktype_poison))) { $v_obj[] = 'Poison'; }
  if (($v & (1 << sg_attacktype_leech))) { $v_obj[] = 'Leech'; }
  //if (($v & (1 << sg_attacktype_zone))) { $v_obj[] = 'Zone'; }
  if (($v & (1 << sg_attacktype_magical))) { $v_obj[] = 'Magical'; }
  if (($v & (1 << sg_attacktype_fire))) { $v_obj[] = 'Fire'; }
  if (($v & (1 << sg_attacktype_water))) { $v_obj[] = 'Water'; }
  if (($v & (1 << sg_attacktype_earth))) { $v_obj[] = 'Earth'; }
  if (($v & (1 << sg_attacktype_air))) { $v_obj[] = 'Air'; }
  if (($v & (1 << sg_attacktype_arcane))) { $v_obj[] = 'Arcane'; }
  if (($v & (1 << sg_attacktype_electric))) { $v_obj[] = 'Electric'; }
  if (($v & (1 << sg_attacktype_necromancy))) { $v_obj[] = 'Necromancy'; }
  if (($v & (1 << sg_attacktype_spectral))) { $v_obj[] = 'Spectral'; }
  return join(' ', $v_obj);
}

function addFlagConsecutive($c_obj, $flag, $flag_min, $flag_max, $n) {
  for ($f = $flag_min; $f <= $flag_max; $f++) {
    if ((!getFlagBit($c_obj, $flag, $f)) && ($n > 0)) {
      $n = $n - 1;
      $c_obj->enableFlagBit($flag, $f);
    }
  }
}

function clearFlagConsecutive($c_obj, $flag, $flag_min, $flag_max, $n) {
  for ($f = $flag_max; $f >= $flag_min; $f--) {
    if ((getFlagBit($c_obj, $flag, $f)) && ($n > 0)) {
      $n = $n - 1;
      $c_obj->disableFlagBit($flag, $f);
    }
  }
}

function applyCombatStateEffects($c_obj, $foe) {
  $ret_array = array();
  $ret_array['text'] = array();
  $ret_array['oppo_hp_lost'] = 0;

  if (getFlagBit($c_obj, sg_flag_es2, sg_es2_healthsiphon_1)) {
    $siphon = bitCount(getFlagValue($c_obj, sg_flag_es2) & 31744);
    $c_obj->setCurrentHp($c_obj->c['current_hp'] + $siphon);
    $ret_array['oppo_hp_lost'] = $siphon;
    $ret_array['text'][] = '<p>Your health siphon drains ' . $siphon .
        ' health from your foe, and sends it to you!</p>';

    if (rand(1, 4) == 4) {
      clearFlagConsecutive($c_obj, sg_flag_es2,
            sg_es2_healthsiphon_1, sg_es2_healthsiphon_3, 1);
      $ret_array['text'][] = '<p>Health siphon begins to fade!</p>';
    }
  }
  if (array_key_exists(83, $c_obj->c['buffs'])) {
    if ($c_obj->c['armour_neck']['id'] == 474) {
      $ret_array['text'][] = '<p>The Curse of the Summoned Rider ' .
          'causes you to recoil in pain, but does not inflict any damage!</p>';
    } else {
      $c_obj->setCurrentHp($c_obj->c['current_hp'] - 75);
      $ret_array['text'][] = '<p><font color="red">The Curse of the ' .
          'Summoned Rider forces you to writhe in pain, causing 75 ' .
          'damage!</font></p>';
    }
  }
  if (array_key_exists(84, $c_obj->c['buffs'])) {
    $resist_val = resistanceDamageResist(
        $c_obj->c['resist_' . sg_attacktype_fire], $c_obj->c['level'], 20, 0);
    $dmg_resisted = max(0, floor(10 * $resist_val));
    $damage = 10 - $dmg_resisted;

    $c_obj->setCurrentHp($c_obj->c['current_hp'] - $damage);
    $ret_array['text'][] = '<p><font color="red">You writhe in pain, ' .
        'scorched from Logi\'s attack!  You suffer ' . $damage .
        ' damage!</font><br><font size="-2">(10 damage, ' . $dmg_resisted .
        ' resisted)</font></p>';
  }
  if (array_key_exists(86, $c_obj->c['buffs'])) {
    $resist_val = min(50, $c_obj->c['resist_' . sg_attacktype_fire]) / 50;
    $dmg_resisted = max(0, floor(30 * $resist_val));
    $damage = 30 - $dmg_resisted;

    $c_obj->setCurrentHp($c_obj->c['current_hp'] - $damage);
    $ret_array['text'][] = '<p><font color="red">You writhe in pain, ' .
        'scorched from Varon\'s attack!  You suffer ' . $damage .
        ' damage!</font><br><font size="-2">(10 damage, ' . $dmg_resisted .
        ' resisted)</font></p>';
  }
  if (array_key_exists(107, $c_obj->c['buffs'])) {
    $dmg_val = floor($c_obj->c['base_hp'] * 0.1);
    $resist_val = min(50, $c_obj->c['resist_' . sg_attacktype_fire]) / 50;
    $dmg_resisted = max(0, floor($dmg_val * $resist_val));
    $damage = $dmg_val - $dmg_resisted;

    $c_obj->setCurrentHp($c_obj->c['current_hp'] - $damage);
    $ret_array['text'][] = '<p><font color="red">You writhe in pain, ' .
        'scorched from the Delann curse!  You suffer ' . $damage .
        ' damage!</font><br><font size="-2">(' . $dmg_val . ' fire damage, ' .
        $dmg_resisted . ' resisted)</font></p>';
  }
  return $ret_array;
}

function getAttackFoeArray($c_obj, $f, $type) {
  $ret_array = array();
  $ret_array['oppo_hp_lost'] = 0;

  $weapon = $c_obj->c['weapon'];
  $skip_attack = FALSE;

  if (getFlagValue($c_obj, sg_flag_es2) & (1 << sg_es2_enraged_1)) {
    clearFlagConsecutive($c_obj, sg_flag_es2,
          sg_es2_enraged_1, sg_es2_enraged_10, 1);
  }

  $foe_resist = getResistanceArray($f['attack_resistance'], 
      $f['attack_resistance_amount']);
  $foe_vuln = getResistanceArray($f['attack_vulnerable'], 100);
  $resist_val = getResistanceModifier(
      $weapon['attack_type'], $foe_resist, $foe_vuln);

  if ($f['creature_type'] == sg_foetype_spectral) {
    if (!getBit($weapon['attack_type'], sg_attacktype_spectral)) {
      $resist_val = 0;
    }
  }

  $base_damage = baseRandomNumber($weapon['base_damage'],
                                  $weapon['random_damage']);
  $base_damage = applyMultiplier($base_damage,
                                 $c_obj->c['bonus_melee_dmg_percent']);
  $base_damage = $base_damage + $c_obj->c['melee_dmg_bonus'];
  $damage = round($base_damage * $resist_val);

  $damage = $damage * 2;
  $crit_skill_bonus = 0;

  $primary_stat_obj = array();
  $primary_stat_obj[] = $c_obj->c['str'] + $c_obj->c['str_bonus'];
  $primary_stat_obj[] = $c_obj->c['dex'] + $c_obj->c['dex_bonus'];
  $primary_stat_obj[] = $c_obj->c['int'] + $c_obj->c['int_bonus'];
  $primary_stat_obj[] = $c_obj->c['cha'] + $c_obj->c['cha_bonus'];
  $primary_stat_obj[] = $c_obj->c['con'] + $c_obj->c['con_bonus'];
  $primary_stat = max($primary_stat_obj);

  $to_hit = $primary_stat + $c_obj->c['level'];
  $to_dodge = $f['dex'] + $f['level'];

  $ret_array['text'] = $weapon['attack_text'];

  $attacks = getCombatAttacks($c_obj);
  if ((array_key_exists($type, $attacks)) && ($attacks[$type]['a'] == TRUE)) {
    switch ($type) {
    case 2:
      $ret_array['text'] = 'You bare your teeth, and rush in to strike!';
      $damage = round($damage * 1.5);
      $to_hit = $to_hit - 3;
      break;
    case 3:
      $ret_array['text'] = 'You inhale deeply, and begin to scream, filling ' .
          'yourself with fury!';
      addFlagConsecutive($c_obj, sg_flag_es2,
          sg_es2_enraged_1, sg_es2_enraged_10, 10);
      $skip_attack = TRUE;
      break;
    case 4:
      $ret_array['text'] = 'You attempt to target the weaknesses of your foe!';
      $damage = round($damage * 0.4);
      break;
    case 5:
      $ret_array['text'] = 'You attempt to locate a weak spot!';
      $damage = round($damage * 0.7);
      $to_hit = $to_hit + 1;
      break;
    case 6:
      $ret_array['text'] = 'You attempt to gouge!';
      $damage = round($damage * 3.3);
      $to_hit = $to_hit + 4;
      break;
    case 7:
      $ret_array['text'] = 'You attempt to perform a crippling strike!';
      $damage = round($damage * 6.1);
      $to_hit = $to_hit + 3;
      break;
    case 8:
      $ret_array['text'] = 'You attempt to bypass your opponents resistances!';
      $resist_val = 1;
      $damage = $base_damage;
      break;
    case 9:
      $ret_array['text'] = 'You cautiously line up your shot, and move in ' .
          'to strike!';
      $damage = round($damage * 0.6);
      $to_hit = $to_hit + 8;
      break;
    case 10:
      $ret_array['text'] = 'You lunge forward with all your strength!';
      $damage = round($damage * 1.6);
      $to_hit = $to_hit - 3;
      break;
    case 11:
      $ret_array['text'] = 'You take a wild swing, aiming to score a ' .
          'critical blow!';
      $damage = round($damage * 2.4);
      $crit_skill_bonus += 25;
      $to_hit = $to_hit - 6;
      break;

    default:
      // safeguard if people are URL hacking without the skill
      $type = 0;
    }
  } else {
    // safeguard if people are URL hacking without the skill
    $type = 0;
  }

  if ($skip_attack == FALSE) {
    $hit_chance = hitProbability($to_hit, $to_dodge) * 1000;
    $hit_roll = rand(0, 1000);
    $crit_chance = $c_obj->c['dex'] + $c_obj->c['dex_bonus'] +
        $crit_skill_bonus;
    $crit_chance = floor($crit_chance * ($c_obj->c['bonus_crit'] / 100.0)) +
        $crit_chance;
    $crit_success = criticalProbability($crit_chance, $f['dex']);
    $crit_text = '';

    if (getBit($f['special'], sg_foespecial_never_hit_by_crit)) {
      $crit_success = FALSE;
    }

    if ($crit_success == TRUE) {
      $crit_text = 'You <font color="red">critically strike</font>!<br>';
      $damage = $damage * 3;
    }

    if ($hit_roll <= $hit_chance) {
      $armourVal = armourDamageResist($f['armour'], $f['level']);
      $dmgBlocked = floor($damage * $armourVal);
      $damage = $damage - $dmgBlocked;

      $ret_array['hit_text'] = $crit_text . 'You hit for ' .$damage.' damage!';
      $ret_array['block_text'] = ($damage + $dmgBlocked) . ' total, ' .
          $dmgBlocked . ' blocked';

      if ($resist_val < 1) {
        $ret_array['block_text'] = $ret_array['block_text'] .
            ', resistant to your attack type';
      } elseif ($resist_val > 1) {
        $ret_array['block_text'] = $ret_array['block_text'] . ', vulnerable ' .
            'to your attack type';
      }

      // Special attack effects for standard attack types

      $valid_attack_types = array('0', '5');
      if (in_array($type, $valid_attack_types)) {
        $sp = $c_obj->c['weapon']['attack_special'];
        if ($sp & (1 << sg_attackspecial_bleed)) {
          $bleed_chance = rand(1, 20);
          if (1 == $bleed_chance) {
            $ret_array['hit_text'] = $ret_array['hit_text'] . '<br>' .
                '<font color="red">Your weapon causes your foe to start ' .
                'bleeding badly!</font>';
            $c_obj->enableFlagBit(sg_flag_es1, sg_es1_bleed_3);
          }
        }
        if ($sp & (1 << sg_attackspecial_stun)) {

        }
      }

      if (4 == $type) {
        $ret_array['hit_text'] = $crit_text . 'You strike at your foe\'s ' .
            'armour, causing ' . $damage . ' damage, and opening a weakness!';
        addFlagConsecutive($c_obj, sg_flag_es1,
            sg_es1_shatter_1, sg_es1_shatter_3, 1);
      } elseif (5 == $type) {
        $ret_array['hit_text'] = $crit_text . 'You manage to target a weak ' .
            'area, strike for ' . $damage . ' damage, and ' .
            'your foe begins to limp!';
        addFlagConsecutive($c_obj, sg_flag_es1,
            sg_es1_expose_1, sg_es1_expose_5, 1);
      } elseif (7 == $type) {
        $c_obj->enableFlagBit(sg_flag_es1, sg_es1_stun_1);
      }

      if (6 == $type) {
        clearFlagConsecutive($c_obj, sg_flag_es1,
            sg_es1_expose_1, sg_es1_expose_5, 3);
      } elseif (7 == $type) {
        clearFlagConsecutive($c_obj, sg_flag_es1,
            sg_es1_expose_1, sg_es1_expose_5, 4);
      } elseif (8 == $type) {
        clearFlagConsecutive($c_obj, sg_flag_es1,
            sg_es1_expose_1, sg_es1_expose_5, 1);
      }

      $ret_array['oppo_hp_lost'] = $damage;
    } else {
      $ret_array['hit_text'] = 'You miss!';
    }
  }

  if (hasSkill($c_obj, getSkillId(skill_dex, 8))) {
    $extra_attack_roll = rand(1, 8);
    if ($extra_attack_roll == 5) {
      $c_obj->enableFlagBit(sg_flag_es1, sg_es1_char_extra_attack);
    }
  }

  if ($ret_array['oppo_hp_lost'] >
      getFlagValue($c_obj, sg_flag_top_melee_damage)) {
    $c_obj->addFlag(sg_flag_top_melee_damage, $ret_array['oppo_hp_lost']);
    $ret_array['hit_text'] = $ret_array['hit_text'] .
        '<br><font color="red"><b>New maximum melee damage record! (' .
        $ret_array['oppo_hp_lost'] . ')</b></font>';
  }

  return $ret_array;
}

function getFoeAttackArray($c_obj, $f) {
  $ret_array = array();
  $ret_array['char_hp_lost'] = 0;
  $ret_array['oppo_hp_lost'] = 0;

  if (getFlagValue($c_obj, sg_flag_es1) & (1 << sg_es1_stun_0)) {
    $c_obj->disableFlagBit(sg_flag_es1, sg_es1_stun_0);
  }

  if (getFlagValue($c_obj, sg_flag_es1) & (1 << sg_es1_stun_1)) {
    $ret_array['hit_text'] = 'Your foe is stunned!';
    $c_obj->enableFlagBit(sg_flag_es1, sg_es1_stun_0);
    $c_obj->disableFlagBit(sg_flag_es1, sg_es1_stun_1);
    return $ret_array;
  }

  if (getFlagValue($c_obj, sg_flag_es1) & (1 << sg_es1_char_extra_attack)) {
    $ret_array['hit_text'] = '<font color="blue">You move with such speed ' .
        'that you gain another attack opportunity!</font>';
    $c_obj->disableFlagBit(sg_flag_es1, sg_es1_char_extra_attack);
    return $ret_array;
  }

  $foeAttackMax = 0;
  foreach ($f['attacks'] as $fa) {
    $foeAttackMax += $fa['odds_of_occurring'];
  }
  $foeAttackChoice = getChoiceId($f['attacks'], $foeAttackMax);
  $foeAttack = $f['attacks'][$foeAttackChoice];

  $char_armour = $c_obj->c['armour'];
  if (getFlagValue($c_obj, sg_flag_es1) & (1 << sg_es1_chararmour_500)) {
    $char_armour = $char_armour + 500;
  }
  if (getFlagValue($c_obj, sg_flag_es2) & (1 << sg_es2_enraged_1)) {
    $armour_add = 5 * bitCount(getFlagValue($c_obj, sg_flag_es2) & 1023);
    $char_armour = applyMultiplier($char_armour, $armour_add);
  }

  $damage = baseRandomNumber(
      $foeAttack['base_damage'], $foeAttack['random_damage']);

  if ($foe['level'] > 4) {
    $damage = $damage * 3;
  } elseif ($foe['level'] > 2) {
    $damage = $damage * 2;
  }

  $foeAttackTextChoice = rand(0, 2);
  switch ($foeAttackTextChoice) {
    case 0: $attackText = $foeAttack['text_1']; break;
    case 1: $attackText = $foeAttack['text_2']; break;
    case 2: $attackText = $foeAttack['text_3']; break;
  }

  $to_hit = $f['str'] + $f['level'];
  $to_dodge = $c_obj->c['dex'] + $c_obj->c['dex_bonus'] + $c_obj->c['level'];

  $hit_chance = hitProbability($to_hit, $to_dodge) * 1000;
  $hit_val = rand(0, 1000);
  $hit_roll = floor($hit_val * ($c_obj->c['dodge_bonus'] / 100.0)) + $hit_val;

  $attackTextFull = str_replace('_', $f['name'], $attackText);
  $ret_array['attack_text'] = $attackTextFull;

  if (getBit($foeAttack['special'], sg_combatspecial_always_hit)) {
    $hit_roll = $hit_chance;
  }

  if ($hit_roll <= $hit_chance) {
    $dmg_resisted = 0;
    $armourVal = armourDamageResist($char_armour, $c_obj->c['level']);
    $dmgBlocked = max(0, floor($damage * $armourVal) +
        $c_obj->c['defend_dmg_bonus']);
    $damage = $damage - $dmgBlocked;

    $foe_attack_type_str = '';
    if ($foeAttack['attack_type'] > 0) {
      for ($r = sg_attacktype_fire; $r <= sg_attacktype_necromancy; $r++) {
        if (($foeAttack['attack_type'] & (1 << $r)) &&
            ($c_obj->c['resist_' . $r] > 0)) {
          $resist_val = resistanceDamageResist(
              $c_obj->c['resist_' . $r], $c_obj->c['level'],
              $f['level'], $f['attack_penetration']);
          $dmg_resisted = max(0, floor($damage * $resist_val));
          $damage = $damage - $dmg_resisted;
          $foe_attack_type_str = ', ' . $dmg_resisted . ' resisted';
        }
      }
      $foe_attack_type_str = $foe_attack_type_str . ', ' .
          getDamageType($foeAttack['attack_type']);
    }

    $ret_array['hit_text'] = 'You are hit for ' . $damage . ' damage.';
    $ret_array['block_text'] = ($damage + $dmgBlocked + $dmg_resisted) .
        ' total, ' . $dmgBlocked . ' blocked' . $foe_attack_type_str;
    $hp = max(0, $c_obj->c['current_hp'] - $damage);
    $c_obj->setCurrentHp($hp);
    $ret_array['char_hp_lost'] = $damage;

    if (getBit($foeAttack['attack_type'], sg_attacktype_leech)) {

    }
    if (getBit($foeAttack['attack_type'], sg_attacktype_zone)) {

    }

    if (($c_obj->c['convert_magic_dmg'] > 0) &&
        ($foeAttack['attack_type'] & (1 << sg_attacktype_magical))) {
      $convert_dmg =
          round($damage * ($c_obj->c['convert_magic_dmg'] / 100.0));
      $ret_array['oppo_hp_lost'] += $convert_dmg;
      $ret_array['hit_text'] = $ret_array['hit_text'] .
          ' Your elemental aura causes ' .
          '<font color="red">' . $convert_dmg .
          '</font> damage in return!';
    }

    if ($foeAttack['buff'] > 0) {
      $buffRoll = rand(0, 100);
      if ($buffRoll < $foeAttack['buff_chance']) {
        $buff_expires = time() + $foeAttack['buff_time'];
        $cte = 0;
        if ($foeAttack['buff_turns'] > 0) {
          $cte = $foeAttack['buff_turns'] + $c_obj->c['total_combats'];
        }
        if (isset($c_obj->c['buffs'][$foeAttack['buff']])) {
          $foe_buff = updateBuff($c_obj,
              $foeAttack['buff'], $foeAttack['buff_time'], $cte, FALSE);
        } else {
          $foe_buff = addBuff($c_obj,
              $foeAttack['buff'], $foeAttack['buff_time'], $cte, FALSE);
          applyBuff($c_obj, $foe_buff, 'modifier_type', 'modifier_value');
          applyBuff($c_obj, $foe_buff, 'modifier_type_2', 'modifier_value_2');
        }

        $c_obj->calculateHpValues();

        $now = time();
        $buff = getBuff($foeAttack['buff']);
        $ret_array['buff_text'] = '<p>You are affected by <b>' .
            $buff['name'] . '</b> (' .
            renderTimeRemaining($now, $buff_expires) . ')</p>';
      }
    }
  } else {
    $ret_array['hit_text'] = 'It misses!';
  }

  return $ret_array;
}

function getCombatItemUseArray($c_obj, $a_id, $f) {
  $ret_array = array();
  $ret_array['oppo_hp_lost'] = 0;

  $artifact = hasArtifact($c_obj, $a_id);
  if (FALSE == $artifact) {
    $ret_array['text'] = 'You don\'t have that artifact!';
  } elseif ($artifact['quantity'] < 1) {
    $ret_array['text'] = 'You don\'t have that artifact!';
  } elseif ($c_obj->c['level'] < $artifact['min_level']) {
    $ret_array['text'] = 'Your level isn\'t high enough to use that artifact!';
  } else {

    $foe_resist = getResistanceArray($f['attack_resistance'],
        $f['attack_resistance_amount']);
    $foe_vuln = getResistanceArray($f['attack_vulnerable'], 100);

    $to_hit = 10 + $c_obj->c['level'];
    $to_dodge = $f['level'];

    $hit_chance = hitProbability($to_hit, $to_dodge) * 1000;
    $hit_roll = rand(0, 1000);

    $hp_injured = max($c_obj->c['base_hp'] - $c_obj->c['current_hp'], 0);
    $mana_injured = max($c_obj->c['mana_max'] - $c_obj->c['mana'], 0);

    switch($a_id) {
    case 56: // rusted throwing knife
      removeArtifact($c_obj, $a_id, 1);

      $damage = baseRandomNumber(2, 4);
      $ret_array['oppo_hp_lost'] = $damage;
      $ret_array['text'] = 'You strike with the throwing blade for ' .
          $damage . ' damage.';

      break;
    case 60: // Simple Combat Bandage
      removeArtifact($c_obj, $a_id, 1);

      $hp_restore = rand(5, 13);
      $hp_bonus = min($hp_restore, $hp_injured);
      $ret_array['text'] = 'You quickly bandage a wound, and recover ' .
          $hp_bonus . ' health.';
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);

      break;
    case 165: // Combat Bandage
      removeArtifact($c_obj, $a_id, 1);

      $hp_bonus = min(8, $hp_injured);
      $ret_array['text'] = 'You quickly bandage a wound, and recover ' .
          $hp_bonus . ' health.';
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);

      break;
    case 188: // Explosive Bronze Canister
      removeArtifact($c_obj, $a_id, 1);

      $resist_val = getResistanceModifier(1, $foe_resist, $foe_vuln);
      $base_damage = baseRandomNumber(12, 35);
      $damage = round($base_damage * $resist_val);

      $ret_array['text'] = 'You pull out the tiny canister and throw it at ' .
          'the base of your foe!<br>';

      if ($hit_roll <= $hit_chance) {
        $ret_array['oppo_hp_lost'] = $damage;
        $ret_array['text'] = $ret_array['text'] . '<font color="red">' .
            'It explodes, causing ' . $damage . ' damage.</font>';

        if ($base_damage != $damage) {
          $ret_array['text'] = $ret_array['text'] . '<br>' .
              '<small><span class="greyed">(' . $base_damage .
              ' total, ' . ($base_damage - $damage) . ' blocked, ' .
              ' resistant to your attack type)</span></small>';
        }
      } else {
        $ret_array['text'] = $ret_array['text'] . '<font color="red">' .
            'Unfortunately, your foe evades the blast!</font>';
      }

      break;
    case 289: // Healer's Food Pellet
      removeArtifact($c_obj, $a_id, 1);

      $hp_bonus = min(25, $hp_injured);
      $ret_array['text'] = 'You toss the pellet in to your mouth, crunch ' .
          'down, and immediately feel much better!  You recover ' .
          $hp_bonus . ' health.';
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);

      break;
    case 359: // Webweaver Trapping Net
      removeArtifact($c_obj, $a_id, 1);

      $damage = baseRandomNumber(3, 8);
      $ret_array['oppo_hp_lost'] = $damage;

      $trap = rand(1, 100);
      if ($trap < 51) {
        $ret_array['text'] = 'You launch the net at your foe and attempt to ' .
            'entangle them!<br><font color="red">They stumble, ' .
            'causing ' . $damage . ' damage, but manage to elude the ' .
            'net!</font>';
      } else {
        $c_obj->enableFlagBit(sg_flag_es1, sg_es1_stun_1);
        $c_obj->enableFlagBit(sg_flag_es1, sg_es1_dex_loss_5);

        $ret_array['text'] = 'You launch the net at your foe and attempt to ' .
            'entangle them!<br><font color="red">The net falls over top ' .
            'of them, limiting their movement, and causing ' . $damage .
            ' damage!</font>';
      }

      break;
    case 362: // Illumation Bomb
      removeArtifact($c_obj, $a_id, 1);

      $damage = baseRandomNumber(2, 5);
      $ret_array['oppo_hp_lost'] = $damage;

      $c_obj->enableFlagBit(sg_flag_es1, sg_es1_stun_1);
      $ret_array['text'] = 'You throw the bomb at the ground and avert ' .
          'your eyes.  A gigantic flash of light erupts from the device, ' .
          'and your foe recoils from the action, stunned!  ' .
          '<font color="red">You cause ' . $damage . ' damage!</font>';

      break;
    case 393: // Undeath Prism
      if ($f['creature_type'] == sg_foetype_undead) {
        removeArtifact($c_obj, $a_id, 1);
        $damage = 50;

        $ret_array['oppo_hp_lost'] = $damage;
        $ret_array['text'] = 'You hold the prism up high, and your undead ' .
            'foe recoils in pain, suffering ' . $damage . ' damage!';
      } else {
        $ret_array['text'] = 'You hold the prism up high, but nothing ' .
            'happens!';
      }
      break;
    case 707: // Explosive Cap
      removeArtifact($c_obj, $a_id, 1);

      $resist_val = getResistanceModifier(1, $foe_resist, $foe_vuln);
      $base_damage = baseRandomNumber(65, 65);
      $damage = round($base_damage * $resist_val);

      $ret_array['text'] = 'You pull out the explosive cap and throw it at ' .
          'the base of your foe!<br>';

      if ($hit_roll <= $hit_chance) {
        $ret_array['oppo_hp_lost'] = $damage;
        $ret_array['text'] = $ret_array['text'] . '<font color="red">' .
            'It explodes, causing ' . $damage . ' damage.</font>';

        if ($base_damage != $damage) {
          $ret_array['text'] = $ret_array['text'] . '<br>' .
              '<small><span class="greyed">(' . $base_damage .
              ' total, ' . ($base_damage - $damage) . ' blocked, ' .
              ' resistant to your attack type)</span></small>';
        }
      } else {
        $ret_array['text'] = $ret_array['text'] . '<font color="red">' .
            'Unfortunately, your foe evades the blast!</font>';
      }

      break;
    case 745: // Linen Combat Wrap
      removeArtifact($c_obj, $a_id, 1);

      $hp_bonus = min(25, $hp_injured);
      $ret_array['text'] = 'You quickly bandage a wound, and recover ' .
          $hp_bonus . ' health.';
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);
      break;
    case 765: // Sweetened Health Salve
      removeArtifact($c_obj, $a_id, 1);

      $hp_bonus = min(75, $hp_injured);
      $ret_array['text'] = 'You consume the salve, and recover ' .
          $hp_bonus . ' health.';
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);
      break;
    case 766: // Violet Mana Salve
      removeArtifact($c_obj, $a_id, 1);

      $mana_bonus = min(50, $mana_injured);
      $ret_array['text'] = 'You consume the salve, and recover ' .
          $mana_bonus . ' mana.';
      $c_obj->setMana($c_obj->c['mana'] + $mana_bonus);
      break;
    case 767: // Yellow Restorative Salve
      removeArtifact($c_obj, $a_id, 1);

      $hp_bonus = min(60, $hp_injured);
      $mana_bonus = min(40, $mana_injured);
      $ret_array['text'] = 'You consume the salve, recovering ' .
          $hp_bonus . ' health and ' . $mana_bonus . ' mana.';
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);
      $c_obj->setMana($c_obj->c['mana'] + $mana_bonus);
      break;
    case 829: // Metallic Dust Fragments
      removeArtifact($c_obj, $a_id, 1);

      $damage = baseRandomNumber(4, 8);
      $ret_array['oppo_hp_lost'] = $damage;

      $c_obj->enableFlagBit(sg_flag_es1, sg_es1_stun_1);
      $ret_array['text'] = 'You throw the fragments toward your foe, who ' .
          'recoils, stunned! ' .
          '<font color="red">You cause ' . $damage . ' damage!</font>';
      break;
    case 830: // Sharp Metallic Plate
      removeArtifact($c_obj, $a_id, 1);

      $resist_val = getResistanceModifier(1, $foe_resist, $foe_vuln);
      $base_damage = baseRandomNumber(65, 65);
      $damage = round($base_damage * $resist_val);

      $ret_array['text'] = 'You pull out the razor-sharp plate, and throw ' .
          'it at your foe!<br>';

      if ($hit_roll <= $hit_chance) {
        $ret_array['oppo_hp_lost'] = $damage;
        $ret_array['text'] = $ret_array['text'] . '<font color="red">' .
            'It hits them, causing ' . $damage . ' damage.</font>';

        if ($base_damage != $damage) {
          $ret_array['text'] = $ret_array['text'] . '<br>' .
              '<small><span class="greyed">(' . $base_damage .
              ' total, ' . ($base_damage - $damage) . ' blocked, ' .
              ' resistant to your attack type)</span></small>';
        }
      } else {
        $ret_array['text'] = $ret_array['text'] . '<font color="red">' .
            'Unfortunately, your foe evades the attack!</font>';
      }
      break;
    }

    if (($ret_array['oppo_hp_lost'] > 0) &&
        ($f['creature_type'] == sg_foetype_spectral)) {
      $ret_array['text'] = 'Your attack has no effect!';
      $ret_array['oppo_hp_lost'] = 0;
    }

    addTrackingData($c_obj, $a_id, sg_track_use, 1);
    $achieve_obj = checkAchievementUse($c_obj, $a_id);
    foreach ($achieve_obj as $achieve) {
      $ret_array['text'] = $ret_array['text'] . $achieve;
    }

    $artifact = getArtifact($a_id);
    $ret_array['text'] = $ret_array['text'] .
        '<br><br><font size="-2">You have used your ' .
        getIntWithSuffix(
            $_SESSION['tracking'][sg_track_use][$a_id]) .
        ' ' . $artifact['name'] . '.</font>';
  }

  return $ret_array;
}

function applyMagicAttack($c_obj, $f, $attack_type, $damage) {
  $ret_obj = array();

  $foe_resist = getResistanceArray($f['attack_resistance'],
      $f['attack_resistance_amount']);
  $foe_vuln = getResistanceArray($f['attack_vulnerable'], 100);

  $to_hit = $c_obj->c['int'] + $c_obj->c['int_bonus'];
  $to_dodge = $f['int'] + $f['int_bonus'];

  $hit_chance = hitProbability($to_hit, $to_dodge) * 1000;
  $hit_roll = rand(0, 1000);
  $hit_success = FALSE;
  if ($hit_roll <= $hit_chance) {
    $hit_success = TRUE;
  }

  if ($f['creature_type'] == sg_foetype_spectral) {
    $hit_success = FALSE;
  }

  if (TRUE == $hit_success) {
    if ($f['resist_' . sg_attacktype_magical] > 0) {
      $resist_val = 1 - resistanceDamageResist(
          $f['resist_' . sg_attacktype_magical], $f['level'],
          $c_obj->c['level'], 0);
    } else {
      $resist_val = getResistanceModifier(
          $attack_type, $foe_resist, $foe_vuln);
    }

    $base_damage = ($damage + $c_obj->c['spell_dmg_bonus']) * 2;
    $base_damage = applyMultiplier($base_damage,
                                   $c_obj->c['bonus_magic_dmg_percent']);
    $real_damage = round($base_damage * $resist_val);

    $ret_obj['damage'] = $real_damage;

    $ret_obj['resist'] = '';
    if ($base_damage > $real_damage) {
      $ret_obj['resist'] = '<br><small><span class="greyed">(' . $base_damage .
          ' total, ' . ($base_damage - $real_damage) . ' resisted, resistant' .
          ' to your attack type)</span></small>';
    } elseif ($base_damage < $real_damage) {
      $ret_obj['resist'] = '<br><small><span class="greyed">(' . $base_damage .
          ' total, vulnerable to your attack type)</span></small>';
    }

    return $ret_obj;
  }

  return FALSE;
}

function getRuneUseArray($c_obj, $r_id, $f) {
  $ret_array = array();
  $ret_array['oppo_hp_lost'] = 0;

  $runes = getCombatRunes($c_obj);
  if ((array_key_exists(sg_combat_spellbase + $r_id, $runes)) &&
      ($runes[sg_combat_spellbase + $r_id]['a'] == TRUE)) {
    switch($r_id) {
    case 1:
      $c_obj->setMana($c_obj->c['mana'] - 5);

      $hp_restore = 5;
      $hp_injured = $c_obj->c['base_hp'] - $c_obj->c['current_hp'];
      $hp_bonus = min($hp_restore, $hp_injured);
      $hp_bonus = max($hp_bonus, 0);
      $ret_array['text'] = 'You close your eyes and focus on your wounds, ' .
          'which begin to heal!  You recover ' . $hp_bonus . ' health.';
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);

      break;
    case 2:
      $c_obj->setMana($c_obj->c['mana'] - 5);

      $attack_type = (1 << sg_attacktype_magical) |
                     (1 << sg_attacktype_fire);
      $damage = rand(6, 15);

      $damage = applyMagicAttack($c_obj, $f, $attack_type, $damage);
      if ($damage != FALSE) {
        $ret_array['oppo_hp_lost'] = $damage['damage'];
        $ret_array['text'] = 'You close your eyes, and visualize a searing ' .
            'burst of flame touching your foe!  Your opponent recoils in ' .
            'pain, and suffers ' . $damage['damage'] . ' damage.';
        $ret_array['text'] = $ret_array['text'] . $damage['resist'];
      } else {
        $ret_array['text'] = 'Your foe manages to resist!';
      }

      break;
    case 3:
      $c_obj->setMana($c_obj->c['mana'] - 5);

      $attack_type = (1 << sg_attacktype_magical) |
                     (1 << sg_attacktype_fire);
      $damage = rand(7, 18);

      $damage = applyMagicAttack($c_obj, $f, $attack_type, $damage);
      if ($damage != FALSE) {
        $ret_array['oppo_hp_lost'] = $damage['damage'];
        $ret_array['text'] = 'You close your eyes, and send a scorching hot ' .
            'ball of flame towards your foe!  It crashes in to them, ' .
            'causing ' . $damage['damage'] . ' damage.';
        $ret_array['text'] = $ret_array['text'] . $damage['resist'];
      } else {
        $ret_array['text'] = 'Your foe manages to resist!';
      }
      break;
    case 4:
      $c_obj->setMana($c_obj->c['mana'] - 8);

      $attack_type = (1 << sg_attacktype_magical) |
                     (1 << sg_attacktype_electric);
      $damage = rand(20, 50);

      $damage = applyMagicAttack($c_obj, $f, $attack_type, $damage);
      if ($damage != FALSE) {
        $ret_array['oppo_hp_lost'] = $damage['damage'];
        $ret_array['text'] = 'You close your eyes, and send a raging bolt ' .
            'of electricity towards your foe!  It crashes in to them, ' .
            'causing ' . $damage['damage'] . ' damage.';
        $ret_array['text'] = $ret_array['text'] . $damage['resist'];
      } else {
        $ret_array['text'] = 'Your foe manages to resist!';
      }
      break;
    case 5:
      $c_obj->setMana($c_obj->c['mana'] - 8);

      $attack_type = (1 << sg_attacktype_magical);
      $damage = rand(20, 35);
      $hp_restore_mult = 0.3;

      $damage = applyMagicAttack($c_obj, $f, $attack_type, $damage);
      if ($damage != FALSE) {
        $hp_restore = round($damage['damage'] * $hp_restore_mult);
        $hp_injured = $c_obj->c['base_hp'] - $c_obj->c['current_hp'];
        $hp_bonus = min($hp_restore, $hp_injured);
        $hp_bonus = max($hp_bonus, 0);
        $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);

        $ret_array['oppo_hp_lost'] = $damage['damage'];
        $ret_array['text'] = 'You close your eyes, and visualize a ' .
            'concentrated bolt of light engulfing your foe!  They recoil ' .
            'in pain, and suffer ' . $damage['damage'] .
            ' damage.  You recover ' .
            $hp_bonus . ' health as a result of your attack!';
        $ret_array['text'] = $ret_array['text'] . $damage['resist'];
      } else {
        $ret_array['text'] = 'Your foe manages to resist!';
      }
      break;

    }
  }

  if ($ret_array['oppo_hp_lost'] >
      getFlagValue($c_obj, sg_flag_top_rune_damage)) {
    $c_obj->addFlag(sg_flag_top_rune_damage, $ret_array['oppo_hp_lost']);
    $ret_array['text'] = $ret_array['text'] .
        '<br><font color="red"><b>New maximum rune damage record! (' .
        $ret_array['oppo_hp_lost'] . ')</b></font>';
  }

  return $ret_array;
}

/*
function getMagicUseArray($c_obj, $a_id, $f) {
  $ret_array = array();
  $ret_array['oppo_hp_lost'] = 0;

  $spells = getCombatSpells($c_obj);
  if ((!array_key_exists(sg_combat_spellbase + $a_id, $spells)) ||
      ($spells[sg_combat_spellbase + $a_id]['a'] == FALSE)) {
    $ret_array['text'] = 'You can\'t cast that now!';
  } else {
    switch($a_id) {
    case sg_combatspell_searing_1:
      $c_obj->setMana($c_obj->c['mana'] - 2);

      $attack_type = (1 << sg_attacktype_magical) |
                     (1 << sg_attacktype_fire);
      $damage = rand(3, 8);

      $damage = applyMagicAttack($c_obj, $f, $attack_type, $damage);
      if ($damage != FALSE) {
        $ret_array['oppo_hp_lost'] = $damage['damage'];
        $ret_array['text'] = 'You close your eyes, and visualize a searing ' .
            'burst of flame touching your foe!  Your opponent recoils in ' .
            'pain, and suffers ' . $damage['damage'] . ' damage.';
        $ret_array['text'] = $ret_array['text'] . $damage['resist'];
      } else {
        $ret_array['text'] = 'Your foe manages to resist!';
      }

      break;
    case sg_combatspell_fireball_1:
      $c_obj->setMana($c_obj->c['mana'] - 3);

      $attack_type = (1 << sg_attacktype_magical) |
                     (1 << sg_attacktype_fire);
      $damage = rand(5, 14);

      $damage = applyMagicAttack($c_obj, $f, $attack_type, $damage);
      if ($damage != FALSE) {
        $ret_array['oppo_hp_lost'] = $damage['damage'];
        $ret_array['text'] = 'You close your eyes, and send a scorching hot ' .
            'ball of flame towards your foe!  It crashes in to them, ' .
            'causing ' . $damage['damage'] . ' damage.';
        $ret_array['text'] = $ret_array['text'] . $damage['resist'];
      } else {
        $ret_array['text'] = 'Your foe manages to resist!';
      }
      break;
    case sg_combatspell_lightning_1:
      $c_obj->setMana($c_obj->c['mana'] - 5);

      $attack_type = (1 << sg_attacktype_magical) |
                     (1 << sg_attacktype_electric);
      $damage = rand(20, 50);

      $damage = applyMagicAttack($c_obj, $f, $attack_type, $damage);
      if ($damage != FALSE) {
        $ret_array['oppo_hp_lost'] = $damage['damage'];
        $ret_array['text'] = 'You close your eyes, and send a raging bolt ' .
            'of electricity towards your foe!  It crashes in to them, ' .
            'causing ' . $damage['damage'] . ' damage.';
        $ret_array['text'] = $ret_array['text'] . $damage['resist'];
      } else {
        $ret_array['text'] = 'Your foe manages to resist!';
      }
      break;
    case sg_combatspell_chilling_1:
      $c_obj->setMana($c_obj->c['mana'] - 4);

      $c_obj->enableFlagBit(sg_flag_es1, sg_es1_chilling_1);
      $ret_array['text'] = 'You close your eyes, and visualize a storm of ' .
          'chilling waves towards your foe!  The air feels noticeably ' .
          'colder, and you see your foe struggling to move!';

      break;
    case sg_combatspell_healing_1:
      $c_obj->setMana($c_obj->c['mana'] - 5);

      if (hasSkill($c_obj, getSkillId(skill_con, 8))) {
        $hp_restore = rand(20, 40);
      } else {
        $hp_restore = rand(10, 20);
      }
      $hp_injured = $c_obj->c['base_hp'] - $c_obj->c['current_hp'];
      $hp_bonus = min($hp_restore, $hp_injured);
      $hp_bonus = max($hp_bonus, 0);
      $ret_array['text'] = 'You close your eyes and focus on your wounds, ' .
          'which begin to heal!  You recover ' . $hp_bonus . ' health.';
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);

      break;
    case sg_combatspell_blindinglight_1:
      $c_obj->setMana($c_obj->c['mana'] - 5);

      $attack_type = (1 << sg_attacktype_magical);
      if (hasSkill($c_obj, getSkillId(skill_con, 8))) {
        $damage = rand(20, 35);
        $hp_restore_mult = 0.3;
      } else {
        $damage = rand(5, 14);
        $hp_restore_mult = 0.15;
      }

      $damage = applyMagicAttack($c_obj, $f, $attack_type, $damage);
      if ($damage != FALSE) {
        $hp_restore = round($damage['damage'] * $hp_restore_mult);
        $hp_injured = $c_obj->c['base_hp'] - $c_obj->c['current_hp'];
        $hp_bonus = min($hp_restore, $hp_injured);
        $hp_bonus = max($hp_bonus, 0);
        $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);

        $ret_array['oppo_hp_lost'] = $damage['damage'];
        $ret_array['text'] = 'You close your eyes, and visualize a ' .
            'concentrated bolt of light engulfing your foe!  They recoil ' .
            'in pain, and suffer ' . $damage['damage'] .
            ' damage.  You recover ' .
            $hp_bonus . ' health as a result of your attack!';
        $ret_array['text'] = $ret_array['text'] . $damage['resist'];
      } else {
        $ret_array['text'] = 'Your foe manages to resist!';
      }
      break;
    case sg_combatspell_healthsiphon_1:
      $c_obj->setMana($c_obj->c['mana'] - 4);

      addFlagConsecutive($c_obj, sg_flag_es2,
          sg_es2_healthsiphon_1, sg_es2_healthsiphon_3, 1);
      $ret_array['text'] = 'You close your eyes, and focus on siphoning ' .
          'life from your foe into yourself.';

      break;
    case sg_combatspell_tragicwail_1:
      $cha_total = $c_obj->c['cha'] + $c_obj->c['cha_bonus'];
      $c_obj->setMana($c_obj->c['mana'] - $cha_total);

      $attack_type = (1 << sg_attacktype_magical) |
                     (1 << sg_attacktype_necromancy);
      $damage = rand($cha_total, ($cha_total * 4));

      $damage = applyMagicAttack($c_obj, $f, $attack_type, $damage);
      if ($damage != FALSE) {
        $ret_array['oppo_hp_lost'] = $damage['damage'];
        $ret_array['text'] = 'You close your eyes, and begin to chant. ' .
            'Before your foe can react, you utter a series of necromantic ' .
            'phrases, assaulting your foe\'s mind, and causing ' .
            $damage['damage'] . ' damage.';
        $ret_array['text'] = $ret_array['text'] . $damage['resist'];
      } else {
        $ret_array['text'] = 'Your foe manages to resist!';
      }

      break;
    }
  }

  if ($ret_array['oppo_hp_lost'] >
      getFlagValue($c_obj, sg_flag_top_spell_damage)) {
    $c_obj->addFlag(sg_flag_top_spell_damage, $ret_array['oppo_hp_lost']);
    $ret_array['text'] = $ret_array['text'] .
        '<br><font color="red"><b>New maximum spell damage record! (' .
        $ret_array['oppo_hp_lost'] . ')</b></font>';
  }

  return $ret_array;
}
*/

function getMountAttackArray($c_obj, $foe) {
  $ret_array = array();

  switch ($c_obj->c['mount']['id']) {
  case 522: // Savage Warsteed
    if (rand(1, 5) == 1) {
      $foe_resist = getResistanceArray($foe['attack_resistance'],
          $f['attack_resistance_amount']);
      $foe_vuln = getResistanceArray($foe['attack_vulnerable'], 100);
      $resist_val = getResistanceModifier(
          (1 << sg_attacktype_physical), $foe_resist, $foe_vuln);

      $base_damage = rand(max(1, floor($foe['level'] / 2)), $foe['level'] * 2);
      $damage = round($base_damage * $resist_val);

      $resist_text = '';
      if ($damage != $base_damage) {
        $resist_text = '<br><small><span class="greyed">(' . $base_damage .
            ' total, ' . ($base_damage - $damage) . ' blocked, resistant' .
            ' to your attack type)</span></small>';
      }

      $ret_array['text'] = '<p><b>Mount attack!</b><br>' .
          'Your Savage Warsteed kicks up on its hind ' .
          'legs and delivers a kick to your foe!  They take ' .
          '<font color="red">' . $damage . '</font> damage!' . $resist_text .
          '</p>';
      $ret_array['oppo_hp_lost'] = $damage;
    }
    break;
  }

  if (($ret_array['oppo_hp_lost'] > 0) &&
      ($foe['creature_type'] == sg_foetype_spectral)) {
    $ret_array['text'] = '<p><b>Mount attack!</b><br>' .
        'Your mount\'s attack has no effect!</p>';
    $ret_array['oppo_hp_lost'] = 0;
  }

  return $ret_array;
}

function getResistText($damage, $base_damage) {
  if ($damage != $base_damage) {
    return '<br><small><span class="greyed">(' . $base_damage .
           ' total, ' . ($base_damage - $damage) . ' blocked, resistant' .
           ' to your attack type)</span></small>';
  }
  return '';
}

function addAllyFatigue($c_obj) {
  if (($c_obj->c['ally_id'] > 0) &&
      ($c_obj->c['ally_fatigue'] < 100000)) {
    $c_obj->setIntVar('ally_fatigue', min(100000,
        $c_obj->c['ally_fatigue'] + $c_obj->c['ally']['combat_fatigue']));
  }
}

function getAllyAttackArray($c_obj, $foe) {
  $ret_array = array();

  $foe_resist = getResistanceArray($foe['attack_resistance'],
      $f['attack_resistance_amount']);
  $foe_vuln = getResistanceArray($foe['attack_vulnerable'], 100);

  $resist_val = getResistanceModifier(
      (1 << sg_attacktype_physical), $foe_resist, $foe_vuln);

  switch ($c_obj->c['ally_id']) {
  case 1: // Divine Warrior
    if (rand(1, 3) == 1) {
      $base_damage = rand(1, $c_obj->c['level']) +
          floor(($c_obj->c['level'] * 2) / 2);
      $damage = round($base_damage * $resist_val);

      $resist_text = getResistText($damage, $base_damage);
      $ret_array['text'] = '<p><b>Ally attack!</b><br>' .
          'Torm pulls out a vicious looking sword, and ' .
          'lunges at your foe!  They take ' . '<font color="red">' .
          $damage . '</font> damage!' . $resist_text . '</p>';
      $ret_array['oppo_hp_lost'] = $damage;
    }
    break;
  case 2: // Divine Treasure Hunter
    break;
  case 3: // Capital Warrior
    if (rand(1, 3) == 1) {
      $base_damage = rand(1, $c_obj->c['level']) +
          floor(($c_obj->c['level'] * 2) / 2);
      $damage = round($base_damage * $resist_val);

      $resist_text = getResistText($damage, $base_damage);
      $ret_array['text'] = '<p><b>Ally attack!</b><br>' .
          'Andor pulls out a vicious looking sword, and ' .
          'lunges at your foe!  They take ' . '<font color="red">' .
          $damage . '</font> damage!' . $resist_text . '</p>';
      $ret_array['oppo_hp_lost'] = $damage;
    }
    break;
  case 4: // Capital Medic
    if (rand(1, 3) == 1) {
      $hp_restore = rand(1, $c_obj->c['level'] * 2);
      $hp_injured = $c_obj->c['base_hp'] - $c_obj->c['current_hp'];
      $hp_bonus = min($hp_restore, $hp_injured);
      $hp_bonus = max($hp_bonus, 0);
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);

      $ret_array['text'] = '<p><b>Ally attack!</b><br>' .
          'Lujza closes her eyes, and points her hands in your direction! ' .
          'You recover ' . $hp_bonus . ' health.</p>';
    }
    break;
  case 5: // Capital Arcanist
    if (rand(1, 3) == 1) {
      $resist_val = getResistanceModifier(
          (1 << sg_attacktype_fire), $foe_resist, $foe_vuln);

      $base_damage = rand(1, $c_obj->c['level']) +
          floor(($c_obj->c['level'] * 2) / 2);
      $damage = round($base_damage * $resist_val);

      $resist_text = getResistText($damage, $base_damage);
      $ret_array['text'] = '<p><b>Ally attack!</b><br>' .
          'Zolt&aacute;n rubs his hands together, and thrusts a ' .
          'pair of fiery palms out at your foe! ' .
          'They take ' . '<font color="red">' .
          $damage . '</font> damage!' . $resist_text . '</p>';
      $ret_array['oppo_hp_lost'] = $damage;
    }
    break;
  case 6: // Grey Oozeling
    if (rand(1, 3) == 1) {
      $resist_val = getResistanceModifier(
          (1 << sg_attacktype_acidic), $foe_resist, $foe_vuln);

      $base_damage = rand(1, $c_obj->c['level']) +
          floor(($c_obj->c['level'] * 2) / 2);
      $damage = round($base_damage * $resist_val);

      $resist_text = getResistText($damage, $base_damage);
      $ret_array['text'] = '<p><b>Ally attack!</b><br>' .
          'Juiblax lunges out at your foe with an acidic pseudopod! ' .
          'They take ' . '<font color="red">' .
          $damage . '</font> damage!' . $resist_text . '</p>';
      $ret_array['oppo_hp_lost'] = $damage;
    }
    break;
  case 7: // Kobold Warrior
    if (rand(1, 8) == 1) {
      $base_damage = rand(1, 3);
      $damage = round($base_damage * $resist_val);

      $resist_text = getResistText($damage, $base_damage);
      $ret_array['text'] = '<p><b>Ally attack!</b><br>' .
          'Wagpex nervously pulls out his sword, and swings wildly ' .
          'at your foe!  They take ' . '<font color="red">' .
          $damage . '</font> damage!' . $resist_text . '</p>';
      $ret_array['oppo_hp_lost'] = $damage;
    }
    break;
  case 8: // TOR-60
    if (rand(1, 3) == 1) {
      $base_damage = rand(1, $c_obj->c['level']) +
          floor(($c_obj->c['level'] * 2.5) / 2);
      $damage = round($base_damage * $resist_val);

      $resist_text = getResistText($damage, $base_damage);
      $ret_array['text'] = '<p><b>Ally attack!</b><br>' .
          'TOR-60 beeps, and suddenly shoots out toward your foe!  It ' .
          'springs out a whirling razor blade, and attacks! ' .
          'Your foe takes ' . '<font color="red">' .
          $damage . '</font> damage!' . $resist_text . '</p>';
      $ret_array['oppo_hp_lost'] = $damage;
    }
    break;
  }

  if (($ret_array['oppo_hp_lost'] > 0) &&
      ($foe['creature_type'] == sg_foetype_spectral)) {
    $ret_array['text'] = '<p><b>Ally attack!</b><br>' .
        'Your ally\'s attack has no effect!</p>';
    $ret_array['oppo_hp_lost'] = 0;
  }

  return $ret_array;
}

function getCombatAttacks($c_obj) {
  $weapon_name = $c_obj->c['weapon']['name'];
  if ($c_obj->c['weapon']['id'] == 0) { $weapon_name = 'fists'; }
  $attacks = array(
    0 => array(
      'n' => 'Nothing',
      'u' => '',
      'i' => 'buff-empty.gif',
      'a' => FALSE
    ),
    1 => array(
      'n' => 'Attack with your ' . $weapon_name,
      'u' => 'fight.php?a=a',
      'i' => 'buff-green.gif',
      'a' => TRUE
    )
  );

  if (hasRune($c_obj, 11)) {
    $attacks[2] = array(
      'n' => 'Use a Furious Strike',
      'u' => 'fight.php?a=a&t=2',
      'i' => 'buff-green.gif',
      'a' => TRUE
    );
  }
  if (hasRune($c_obj, 12)) {
    $attacks[5] = array(
      'n' => 'Attempt to Target Weakness',
      'u' => 'fight.php?a=a&t=5',
      'i' => 'buff-green.gif',
      'a' => TRUE
    );
    $attacks[6] = array(
      'n' => 'Attack with Gouge Weakness',
      'u' => 'fight.php?a=a&t=6',
      'i' => 'buff-green.gif',
      'a' => (getFlagBit($c_obj, sg_flag_es1, sg_es1_expose_3))
    );
    $attacks[7] = array(
      'n' => 'Attack with Crippling Strike',
      'u' => 'fight.php?a=a&t=7',
      'i' => 'buff-green.gif',
      'a' => (getFlagBit($c_obj, sg_flag_es1, sg_es1_expose_5))
    );
    $attacks[8] = array(
      'n' => 'Attack with Bypass Resistances',
      'u' => 'fight.php?a=a&t=8',
      'i' => 'buff-green.gif',
      'a' => (getFlagBit($c_obj, sg_flag_es1, sg_es1_expose_1))
    );
  }
  if (hasRune($c_obj, 13)) {
    $attacks[9] = array(
      'n' => 'Attack Cautiously and Accurately',
      'u' => 'fight.php?a=a&t=9',
      'i' => 'buff-green.gif',
      'a' => TRUE
    );
    $attacks[10] = array(
      'n' => 'Attack Lunge',
      'u' => 'fight.php?a=a&t=10',
      'i' => 'buff-green.gif',
      'a' => TRUE
    );
    $attacks[11] = array(
      'n' => 'Attack with a Wild Swing',
      'u' => 'fight.php?a=a&t=11',
      'i' => 'buff-green.gif',
      'a' => TRUE
    );
  }


/*  if (hasSkill($c_obj, getSkillId(skill_str, 5))) {
    $attacks[3] = array(
      'n' => 'Use an Enraged Shout',
      'u' => 'fight.php?a=a&t=3',
      'i' => 'buff-green.gif',
      'a' => TRUE
    );
  }
  if (hasSkill($c_obj, getSkillId(skill_str, 7))) {
    $attacks[4] = array(
      'n' => 'Attempt to Shatter Armour',
      'u' => 'fight.php?a=a&t=4',
      'i' => 'buff-green.gif',
      'a' => TRUE
    );
  }*/

  return $attacks;
}

function getCombatRunes($c_obj) {
  $runes = array();
  $m = $c_obj->c['mana'];

  if (hasRune($c_obj, 1)) {
    $runes[sg_combat_spellbase + 1] = array(
      'n' => 'Cast Mark of Lesser Healing (5 mana)',
      'u' => 'fight.php?a=m&i=1',
      'i' => 'buff-green.gif',
      'a' => ($m >= 5),
    );
  }
  if (hasRune($c_obj, 2)) {
    $runes[sg_combat_spellbase + 2] = array(
      'n' => 'Cast Smite (5 mana)',
      'u' => 'fight.php?a=m&i=2',
      'i' => 'buff-green.gif',
      'a' => ($m >= 5)
    );
  }
  if (hasRune($c_obj, 14)) {
    $runes[sg_combat_spellbase + 3] = array(
      'n' => 'Cast Fireball (5 mana)',
      'u' => 'fight.php?a=m&i=3',
      'i' => 'buff-green.gif',
      'a' => ($m >= 5)
    );
  }
  if (hasRune($c_obj, 15)) {
    $runes[sg_combat_spellbase + 4] = array(
      'n' => 'Cast Lightning Bolt (8 mana)',
      'u' => 'fight.php?a=m&i=4',
      'i' => 'buff-green.gif',
      'a' => ($m >= 8)
    );
  }
  if (hasRune($c_obj, 16)) {
    $runes[sg_combat_spellbase + 5] = array(
      'n' => 'Cast Blinding Light (8 mana)',
      'u' => 'fight.php?a=m&i=5',
      'i' => 'buff-green.gif',
      'a' => ($m >= 8)
    );
  }

  return $runes;
}

function getCombatArtifacts($c_obj) {
  $artifacts = array();

  $a_names = array(
    165 => 'Combat Bandage',
    188 => 'Explosive Bronze Canister',
    707 => 'Explosive Cap',
    289 => 'Healer\'s Food Pellet',
    359 => 'Webweaver Trapping Net',
    362 => 'Illumination Bomb',
    745 => 'Linen Combat Wrap',
    829 => 'Metallic Dust Fragments',
    56  => 'Rusted Throwing Knife',
    830 => 'Sharp Metallic Plate',
    60  => 'Simple Combat Bandage',
    765 => 'Sweetened Health Salve',
    393 => 'Undeath Prism',
    766 => 'Violet Mana Salve',
    767 => 'Yellow Restorative Salve',
  );

  foreach ($a_names as $k => $v) {
    if (getArtifactQuantity($c_obj, $k) > 0) {
      $artifacts[sg_combat_artifactbase + $k] = array(
        'n' => 'Use ' . $v . ' (' . getArtifactQuantity($c_obj, $k) .
               ' left)',
        'u' => 'fight.php?a=u&i=' . $k,
        'i' => 'buff-green.gif',
        'a' => TRUE,
      );
    }
  }

  return $artifacts;
}

function getCombatArtifactsList($c_obj) {
  $artifacts = array();

  $a_names = array(
    165 => 'Combat Bandage',
    188 => 'Explosive Bronze Canister',
    707 => 'Explosive Cap',
    289 => 'Healer\'s Food Pellet',
    359 => 'Webweaver Trapping Net',
    362 => 'Illumination Bomb',
    745 => 'Linen Combat Wrap',
    829 => 'Metallic Dust Fragments',
    56  => 'Rusted Throwing Knife',
    830 => 'Sharp Metallic Plate',
    60  => 'Simple Combat Bandage',
    765 => 'Sweetened Health Salve',
    393 => 'Undeath Prism',
    766 => 'Violet Mana Salve',
    767 => 'Yellow Restorative Salve',
  );

  foreach ($a_names as $k => $v) {
    if (getArtifactQuantity($c_obj, $k) > 0) {
      $artifacts[$k] = $v;
    }
  }

  return $artifacts;
}

function getFoeWithEncounterState($c_obj, $foe) {
  $e = getFlagValue($c_obj, sg_flag_es1);

  if ($e & (1 << sg_es1_statreduce_2_points)) {
    $foe['str'] = $foe['str'] - 2;
    $foe['dex'] = $foe['dex'] - 2;
    $foe['int'] = $foe['int'] - 2;
    $foe['cha'] = $foe['cha'] - 2;
    $foe['con'] = $foe['con'] - 2;
  }
  if ($e & (1 << sg_es1_double_gold_drop)) {
    $foe['base_gold'] = $foe['base_gold'] * 2;
    $foe['random_gold'] = $foe['random_gold'] * 2;
  }
  if ($e & (1 << sg_es1_str_loss_3)) {
    $foe['str'] = $foe['str'] - 3;
  }
  if ($e & (1 << sg_es1_str_loss_5)) {
    $foe['str'] = $foe['str'] - 5;
  }
  if ($e & (1 << sg_es1_dex_loss_3)) {
    $foe['dex'] = $foe['dex'] - 3;
  }
  if ($e & (1 << sg_es1_dex_loss_5)) {
    $foe['dex'] = $foe['dex'] - 5;
  }
  if ($e & (1 << sg_es1_int_loss_3)) {
    $foe['int'] = $foe['int'] - 3;
  }
  if ($e & (1 << sg_es1_int_loss_5)) {
    $foe['int'] = $foe['int'] - 5;
  }
  if ($e & (1 << sg_es1_shatter_3)) {
    $foe['armour'] = applyMultiplier($foe['armour'], -12);
  } elseif ($e & (1 << sg_es1_shatter_2)) {
    $foe['armour'] = applyMultiplier($foe['armour'], -12);
  } elseif ($e & (1 << sg_es1_shatter_1)) {
    $foe['armour'] = applyMultiplier($foe['armour'], -12);
  }
  if ($e & (1 << sg_es1_chilling_1)) {
    $foe['str'] = $foe['str'] - 5;
  }

  return $foe;
}

?>