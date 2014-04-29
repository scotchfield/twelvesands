<?

require_once 'include/core.php';

require_once sg_base_path . 'include/achieve.php';
require_once sg_base_path . 'include/bank.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/combats.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/foes.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/runes.php';
require_once sg_base_path . 'include/sql.php';

define('ts_combat_active',             1);
define('ts_combat_begin',              2);
define('ts_combat_initiative_failed',  4);
define('ts_combat_char_attacked',      8);
define('ts_combat_char_ran',          16);
define('ts_combat_char_defeated',     32);
define('ts_combat_char_victory',      64);
define('ts_combat_char_ongoing',     128);


function getCombat($state_params, $log_obj) {
  $a = $state_params['a'];
  $i = $state_params['i'];
  $a_t = $state_params['t'];

  $ret_obj = array();
  $ret_obj['state'] = 0;
  $ret_obj['output_obj'] = array();

  $c_obj = new Char($_SESSION['c']);
  $ret_obj['char_obj'] = $c_obj;
  $foe = array();

  if (($c_obj->c['current_hp'] < 1) || ($c_obj->c['encounter_id'] == 0)) {

    if ($c_obj->c['encounter_id'] != 0) {
      $c_obj->setEncounterId(0);
      $c_obj->setEncounterType(0);
    }

    $last_zone = getZone(getFlagValue($c_obj, sg_flag_last_combat_zone));
    if ($last_zone != NULL) {
      $ret_obj['header'] = 'Location: main.php?z=' . $last_zone['parent_id'];
    } else {
      $ret_obj['header'] = 'Location: main.php';
    }

  } elseif ($c_obj->c['encounter_id'] == 0) {

    // Visiting the combat page without an encounter set.
    $ret_obj['header'] = 'Location: main.php';

  } elseif ($c_obj->c['encounter_type'] != sg_encountertype_foe) {

    // Visiting the combat page while in another type of combat.
    $ret_obj['header'] = 'Location: main.php';

  } else {

    $char_attack = array();
    $foe_attack = array();

    // Get the foe information, with state modifiers.
    $foe = getFoe($c_obj, $c_obj->c['encounter_id']);
    $foe['hp'] = $c_obj->c['encounter_hp'];
    $foe = getFoeWithEncounterState($c_obj, $foe);
    $new_foe_hp = $foe['hp'];

    $ret_obj['state'] = ts_combat_active;

    // Combat begins, perform initiative check.
    $player_turn = getCharacterInitiative($c_obj, $foe);
    if ($player_turn == FALSE) {
        $ret_obj['output_obj'][] =
            '<p><font color="red">You are ambushed!</font></p>';
    }
    $combat_done = FALSE;
    $combat_round = 0;
    while (($combat_done == FALSE) && ($combat_round < 30)) {
      $attack_obj = array();
      if ($player_turn == TRUE) {
        $ret_obj['output_obj'][] = '<p class="no_space b">You attack!</p>';
        $attack_obj[] = getAttackFoeArray($c_obj, $foe, 1);

        $attack_obj[] = getMountAttackArray($c_obj, $foe);
        if (($c_obj->c['ally_id'] > 0) &&
            ($c_obj->c['ally_fatigue'] < 100000)) {
          $attack_obj[] = getAllyAttackArray($c_obj, $foe);
        }
      } else {
        $ret_obj['output_obj'][] = '<p class="no_space b">' . $foe['name'] .
            ' attacks!</p>';
        $attack_obj[] = getFoeAttackArray($c_obj, $foe);
      }

      foreach ($attack_obj as $attack) {
        $foe['hp'] -= $attack['oppo_hp_lost'];

        $ret_obj['output_obj'][] = '<p class="no_space">' .
            $attack['text'] . '</p>' .
            '<p class="no_space_top">' . $attack['hit_text'];
        if (strlen($attack['block_text'] > 0)) {
          $ret_obj['output_obj'][] = '<br><small><span class="greyed">(' .
              $attack['block_text'] . ')</span></small></p>';
        }
      }

      $state_effect_list = applyCombatStateEffects($c_obj, $foe);
      foreach ($state_effect_list['text'] as $state_text) {
        $ret_obj['output_obj'][] = $state_text;
      }

      if ($c_obj->c['current_hp'] <= 0) {
        $ret_obj['output_obj'][] = '<p>You pass out from the pain!</p>' .
            '<p><font color="red">You feel extremely fatigued!</font></p>' .
            '<p><a href="main.php">Go back to Capital City</a></p>';

        $combat_done = TRUE;
        $ret_obj['state'] |= ts_combat_char_defeated;

        $new_fatigue = $c_obj->addFatigue(
            sg_fatigue_defeat * $c_obj->c['burden']);
        $c_obj->setTotalCombats($c_obj->c['total_combats'] + 1);
        $c_obj->setEncounterId(0);
        $c_obj->setEncounterType(0);
        $log_obj->addLog($c_obj->c, sg_log_killed_by_foe, $foe['id'],
                         $foe['hp'], 0, 0);
        addAllyFatigue($c_obj);
      } elseif ($foe['hp'] <= 0) {
        $ret_obj['state'] |= ts_combat_char_victory;
        $combat_done = TRUE;

        $c_obj->setEncounterId(0);
        $c_obj->setEncounterType(0);
        $c_obj->addFatigue(sg_fatigue_combat * $c_obj->c['burden']);
        $c_obj->setTotalCombats($c_obj->c['total_combats'] + 1);
        $log_obj->addLog($c_obj->c, sg_log_defeat_foe, $foe['id'],
                         $foe['hp'], 0, 0);
        addAllyFatigue($c_obj);

        addTrackingData($c_obj, $foe['id'], sg_track_foe, 1);
        $achieve_obj = checkAchievementFoe($c_obj, $foe['id']);
        foreach ($achieve_obj as $achieve) {
          $ret_obj['output_obj'][] = $achieve;
        }

        $xp_gain = $foe['xp'] +
            floor(($c_obj->c['xp_bonus'] / 100.0) * $foe['xp']);
        $xp_gain = applyMultiplier($xp_gain, $c_obj->c['xp_combat_bonus']);

        $c_obj->addXp($xp_gain);
        $ret_obj['output_obj'][] =
            '<p><img src="images/recv_xp.gif"> ' .
            $xp_gain . ' experience points awarded.</p>';

        $level_check = levelCheck($c_obj);
        if ($level_check != FALSE) {
          $ret_obj['output_obj'][] = '<p>' . $level_check . '</p>';
        }

        // Character was victorious, award artifacts.
        $g_award = baseRandomNumber($foe['base_gold'], $foe['random_gold']);
        $g_award = applyMultiplier($g_award, $c_obj->c['gold_bonus']);
        $ret_obj['output_obj'][] =
            awardArtifactString($c_obj, 0, $g_award);
        addTrackingData($c_obj, 0, sg_track_loot, $g_award);
        $achieve_obj = checkAchievementLoot($c_obj, 0);
        foreach ($achieve_obj as $achieve) {
          $ret_obj['output_obj'][] = $achieve;
        }

        $foe_artifacts = $foe['artifacts'];
        $ensured_artifacts = array();
        foreach ($foe_artifacts as $artifact) {

          if ($artifact['ensure_group_id'] > 0) {

            if (!array_key_exists($artifact['ensure_group_id'],
                                  $ensured_artifacts)) {
              $ensured_artifacts[$artifact['ensure_group_id']] = array();
            }
            $ensured_artifacts[$artifact['ensure_group_id']][] =
                $artifact['id'];

          } elseif ($artifact['artifact_droprate'] > 0) {

            $check_drop = TRUE;

            if (($artifact['d_id'] > 0) &&
                ($artifact['d_id'] != $c_obj->c['d_id'])) {
              $check_drop = FALSE;
            }
            if (($artifact['d_id'] > 0) &&
                ($artifact['d_id'] == $c_obj->c['d_id'])) {
              $artifact_count =
                  getArtifactQuantity($c_obj, $artifact['id']) +
                  getBankArtifactQuantity($c_obj, $artifact['id']);
              if ($artifact_count >= 1) {
                $check_drop = FALSE;
              }
            }
            if (($artifact['weapon_required'] > 0) &&
                ($c_obj->c['weapon']['id'] != $artifact['weapon_required'])) {
              $check_drop = FALSE;
            }
            if ($artifact['quest_required'] > 0) {
              if (!array_key_exists($artifact['quest_required'],
                                    $c_obj->c['quests'])) {
                $check_drop = FALSE;
              } else {
                $char_quest = $c_obj->c['quests'][$artifact['quest_required']];
                if (($char_quest['status'] != sg_quest_in_progress) &&
                    ($char_quest['repeatable'] == 0)) {
                  $check_drop = FALSE;
                }
              }
            }
            if ($artifact['max_quantity'] > 0) {
              $artifact_count =
                  getArtifactQuantity($c_obj, $artifact['id']) +
                  getBankArtifactQuantity($c_obj, $artifact['id']);
              if ($artifact_count >= $artifact['max_quantity']) {
                $check_drop = FALSE;
              }
            }
            if (TRUE == $check_drop) {
              $droprate = $artifact['artifact_droprate'] +
                  $artifact['artifact_droprate'] *
                      ($c_obj->c['item_bonus'] / 100);
              if (rand(0, 100000) < $droprate) {
                $ret_obj['output_obj'][] =
                    awardArtifactString($c_obj, $artifact, 1);
                addTrackingData($c_obj, $artifact['id'], sg_track_loot, 1);
                $achieve_obj = checkAchievementLoot($c_obj, $artifact['id']);
                foreach ($achieve_obj as $achieve) {
                  $ret_obj['output_obj'][] = $achieve;
                }
              }
            }
          }
        }

        foreach ($ensured_artifacts as $ensured_obj) {
          $a_id = $ensured_obj[array_rand($ensured_obj)];
          $artifact = getArtifact($a_id);
          $ret_obj['output_obj'][] =
              awardArtifactString($c_obj, $artifact, 1);
          addTrackingData($c_obj, $artifact['id'], sg_track_loot, 1);
          $achieve_obj = checkAchievementLoot($c_obj, $artifact['id']);
          foreach ($achieve_obj as $achieve) {
            $ret_obj['output_obj'][] = $achieve;
          }
        }

        if ($c_obj->c['mount']['id'] == 742) {
          $tracking_chance = rand(1, 1000);
          if ($tracking_chance <= 30) {
            $tracking_id = 0;
            switch ($foe['creature_type']) {
            case sg_foetype_humanoid: $tracking_id = 798; break;
            case sg_foetype_beast: $tracking_id = 799; break;
            case sg_foetype_undead:
              if (rand(0, 1) == 0) { $tracking_id = 805; }
              else { $tracking_id = 806; }
              break;
            case sg_foetype_elemental: $tracking_id = 800; break;
            case sg_foetype_demon: $tracking_id = 801; break;
            case sg_foetype_ooze: $tracking_id = 802; break;
            }

            $tracking_special = rand(1, 20);
            if ($tracking_special == 1) { $tracking_id = 803; }
            elseif ($tracking_special == 2) { $tracking_id = 804; }

            if ($tracking_id > 0) {
              $ret_obj['output_obj'][] = '<p class="tip">Your Silver ' .
                  'Tracking Wolf perks up its ears, and races to the fallen ' .
                  'corpse.<br>It returns with an artifact for you!</p>';
              $tracking_artifact = getArtifact($tracking_id);
              $ret_obj['output_obj'][] =
                  awardArtifactString($c_obj, $tracking_artifact, 1);
              addTrackingData($c_obj, $tracking_id, sg_track_loot, 1);
              $achieve_obj = checkAchievementLoot($c_obj, $tracking_id);
              foreach ($achieve_obj as $achieve) {
                $ret_obj['output_obj'][] = $achieve;
              }
            }
          }
        }

        if ($foe['reputation_id'] != 0) {
          $rep = $c_obj->c['reputations'][$foe['reputation_id']]['value'];
          if ($rep < $foe['reputation_max_award']) {
            $rep_reward = $foe['reputation_value'] +
                floor(($c_obj->c['rep_bonus']/100.0) *
                       $foe['reputation_value']);
            if (($rep + $rep_reward) > $foe['reputation_max_award']) {
              $rep_reward = $foe['reputation_max_award'] - $rep;
            }
            $ret_obj['output_obj'][] = '<p>' . 
                awardReputationString($c_obj, $foe['reputation_id'],
                                      $rep_reward) . '</p>';
          }
        }

        if ($c_obj->c['current_hp'] < $c_obj->c['base_hp']) {
          $hp_recover = ceil(sg_fatigue_combat / 1000) +
              $c_obj->c['hp_regen'];
          $hp_injured = $c_obj->c['base_hp'] - $c_obj->c['current_hp'];
          $hp_bonus = min($hp_recover, $hp_injured);
          $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);

          $ret_obj['output_obj'][] =
              '<p><img src="images/recv_health.gif"> ' .
              'You recover ' . $hp_bonus .' health.</p>';
        }

        if ($c_obj->c['mana_regen'] > 0) {
          $mana_missing = $c_obj->c['mana_max'] - $c_obj->c['mana'];
          if ($mana_missing > 0) {
            $mana_regen = min($c_obj->c['mana_regen'],
                              $mana_missing);
            if ($mana_regen > 0) {
              $c_obj->setMana($c_obj->c['mana'] + $mana_regen);
              $ret_obj['output_obj'][] =
                  '<p><img src="images/recv_mana.gif"> '.
                  'You recover ' . $mana_regen . ' mana.</p>';
            }
          }
        }

        foreach ($c_obj->c['quests'] as $quest) {
          if ((sg_quest_in_progress == $quest['status']) ||
              ($quest['repeatable'] > 0)) {
            if ($foe['id'] == $quest['quest_foe1']) {
              if ($quest['foe_count_1'] < $quest['quest_foe_quantity1']) {
                updateQuestCounts($c_obj->c, $quest['id'],
                    $quest['foe_count_1'] + 1,
                    $quest['foe_count_2'],
                    $quest['foe_count_3']);
                $ret_obj['output_obj'][] = '<p>Quest update: ' .
                    $foe['name'] . ' slain.  (' .
                    ($quest['foe_count_1'] + 1) .
                    '/' . $quest['quest_foe_quantity1'] . ')' .
                    '<br><font size="-2">' .
                    '<a href="talk.php?t=' . $quest['npc_id'] . '&q=' .
                    $quest['id'] . '">' . $quest['name'] . '</a></font></p>';
              }
            } elseif ($foe['id'] == $quest['quest_foe2']) {
              if ($quest['foe_count_2'] < $quest['quest_foe_quantity2']) {
                updateQuestCounts($c_obj->c, $quest['id'],
                    $quest['foe_count_1'],
                    $quest['foe_count_2'] + 1,
                    $quest['foe_count_3']);
                $ret_obj['output_obj'][] = '<p>Quest update: ' .
                    $foe['name'] . ' slain.  (' .
                    ($quest['foe_count_2'] + 1) .
                    '/' . $quest['quest_foe_quantity2'] . ')' .
                    '<br><font size="-2">' .
                    '<a href="talk.php?t=' . $quest['npc_id'] . '&q=' .
                    $quest['id'] . '">' . $quest['name'] . '</a></font></p>';
              }
            } elseif ($foe['id'] == $quest['quest_foe3']) {
              if ($quest['foe_count_3'] < $quest['quest_foe_quantity3']) {
                updateQuestCounts($c_obj->c, $quest['id'],
                    $quest['foe_count_1'],
                    $quest['foe_count_2'],
                    $quest['foe_count_3'] + 1);
                $ret_obj['output_obj'][] = '<p>Quest update: ' .
                    $foe['name'] . ' slain.  (' .
                    ($quest['foe_count_3'] + 1) .
                    '/' . $quest['quest_foe_quantity3'] . ')' .
                    '<br><font size="-2">' .
                    '<a href="talk.php?t=' . $quest['npc_id'] . '&q=' .
                    $quest['id'] . '">' . $quest['name'] . '</a></font></p>';
              }
            }
          }
        }

        if ($c_obj->c['encounter_artifact'] > 0) {
          $encounter_artifact = getArtifact($c_obj->c['encounter_artifact']);
          $ret_obj['output_obj'][] = '<p>Your ' .
              $encounter_artifact['name'] . ' is consumed.</p>';
          removeArtifact($c_obj, $c_obj->c['encounter_artifact'], 1);
          $c_obj->setEncounterArtifact(0);
        }

        if (sg_scalingfoe == $foe['id']) {
          $flag_val = 1;
          if (array_key_exists(sg_flag_scalingfoe, $c_obj->c['flags'])) {
            $flag_val = $c_obj->c['flags'][sg_flag_scalingfoe];
          }
          $flag_val = $flag_val + 1;

          $c_obj->addFlag(sg_flag_scalingfoe, $flag_val);
          if ($flag_val > getFlagValue($c_obj, sg_flag_scalingfoe_max)) {
            $c_obj->addFlag(sg_flag_scalingfoe_max, $flag_val);
          }
        } elseif (235 == $foe['id']) {
          $c_obj->addFlag(sg_flag_pravokan_reveler_count,
              getFlagValue($c_obj, sg_flag_pravokan_reveler_count) + 1);
        }

        $ret_obj['output_obj'][] = '<p><font size="-2">You have slain your ' .
            getIntWithSuffix(
                $_SESSION['tracking'][sg_track_foe][$foe['id']]) .
            ' ' . $foe['name'] . '.</font></p>';

        if (getFlagValue($c_obj, sg_flag_combat_flag_id_set) != 0) {
          $c_obj->enableFlagBit(
              getFlagValue($c_obj, sg_flag_combat_flag_id_set),
              getFlagValue($c_obj, sg_flag_combat_flag_bit_set));
        }

        if (getFlagValue($c_obj, sg_flag_game_flag_decrease) != 0) {
          decreaseGameFlag(getFlagValue($c_obj, sg_flag_game_flag_decrease));
        }

      }

      $player_turn = !$player_turn;
      $combat_round += 1;
    }

    if ($combat_done == FALSE) { // exhausted
      $ret_obj['output_obj'][] = '<p>You try to continue combat, but realize ' .
              'you are too tired!  This encounter has gone on for too long, ' .
              'and you hastily retreat from battle!</p>' .

          '<p><font color="red">You feel extremely fatigued!</font></p>' .
          '<p><a href="main.php">Go back to Capital City</a></p>';

      $combat_done = TRUE;
      $ret_obj['state'] |= ts_combat_char_defeated;

      $new_fatigue = $c_obj->addFatigue(
          sg_fatigue_defeat * $c_obj->c['burden']);
      $c_obj->setTotalCombats($c_obj->c['total_combats'] + 1);
      $c_obj->setEncounterId(0);
      $c_obj->setEncounterType(0);
      $log_obj->addLog($c_obj->c, sg_log_killed_by_foe, $foe['id'],
                         $foe['hp'], 0, 0);
      addAllyFatigue($c_obj);
    }    

    $ret_obj['char_attack'] = $char_attack;
    $ret_obj['foe_attack'] = $foe_attack;
  }

  $ret_obj['foe'] = $foe;

  $save = $c_obj->save();
  return $ret_obj;
}

?>
