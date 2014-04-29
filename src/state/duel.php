<?

require_once 'include/core.php';

require_once sg_base_path . 'include/achieve.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/combats.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/log.php';

define('ts_combat_active',             1);
define('ts_combat_begin',              2);
define('ts_combat_initiative_failed',  4);
define('ts_combat_char_attacked',      8);
define('ts_combat_char_ran',          16);
define('ts_combat_char_defeated',     32);
define('ts_combat_char_victory',      64);
define('ts_combat_waiting',          128);

define('ts_duelstate_turn_1', 1);
define('ts_duelstate_turn_2', 2);
define('ts_duelstate_done',   3);

function getDuel($state_params, $log_obj) {
  $a = $state_params['a'];
  $i = $state_params['i'];
  $a_t = $state_params['t'];

  $ret_obj = array();
  $ret_obj['state'] = 0;
  $ret_obj['status_list'] = array();

  $c_obj = new Char($_SESSION['c']);

  if (($c_obj->c['current_hp'] < 1) && ($c_obj->c['encounter_id'] > 0)) {

    $duel_state = getDuelState($c_obj->c['id'], $c_obj->c['encounter_id']);
    if (FALSE == $duel_state) {
      $ret_obj['header'] = 'Location: char.php?a=du';
    } else {
      $ret_obj['render_text'] = $duel_state['render_text'];
    }

    $c_obj->setEncounterId(0);
    $c_obj->setEncounterType(0);
    $_SESSION['duel_id'] = 0;

    $foe_obj = new Char($duel_state['target_id']);
    $ret_obj['foe'] = array();
    $ret_obj['foe']['name'] = $foe_obj->c['name'];

    $ret_obj['state'] = ts_combat_active | ts_combat_char_defeated;
    $ret_obj['char_obj'] = $c_obj;

    $ret_obj['state_effect_list'][] = awardAchievement($c_obj, 45);

  } elseif (($c_obj->c['current_hp'] < 1) ||
            ($c_obj->c['encounter_id'] == 0)) {

    if ($c_obj->c['encounter_id'] != 0) {
      $c_obj->setEncounterId(0);
      $c_obj->setEncounterType(0);
      $_SESSION['duel_id'] = 0;
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

  } elseif ($c_obj->c['encounter_type'] != sg_encountertype_duel) {

    // Visiting the combat page while in another type of combat.
    $ret_obj['header'] = 'Location: main.php';

  } else {

    $char_attack = array();
    $foe_attack = array();

    // Get the foe information, with state modifiers.
    $duel_state = getDuelState($c_obj->c['id'], $c_obj->c['encounter_id']);
    if (FALSE == $duel_state) {
      $ret_obj['header'] = 'Location: char.php?a=du';
    } else {

      $_SESSION['duel_id'] = $c_obj->c['encounter_id'];

      $foe_obj = new Char($duel_state['target_id']);
      $foe = getFoeWithEncounterState($c_obj, $foe_obj->c);

      $ret_obj['state'] = ts_combat_active;
      $ret_obj['render_text'] = $duel_state['render_text'];

      $ret_obj['foe_base_hp'] = $foe_obj->c['base_hp'];
      $ret_obj['foe_current_hp'] = $foe_obj->c['current_hp'];

      if ((($duel_state['state'] == 1) &&
           ($duel_state['char_id_1'] == $c_obj->c['id'])) ||
          (($duel_state['state'] == 2) &&
           ($duel_state['char_id_2'] == $c_obj->c['id']))) {

        if (('a' == $a) || ('u' == $a) || ('m' == $a) || ('p' == $a)) {
          $ret_obj['state'] |= ts_combat_char_attacked;

          if (FALSE != $foe) {

            $state_effect_list = applyCombatStateEffects($c_obj, $foe);
            $ret_obj['state_effect_list'] = $state_effect_list['text'];
            $foe_obj->setCurrentHp($foe_obj->c['current_hp'] -
                $state_effect_list['oppo_hp_lost']);

            if (getFlagValue($foe_obj, sg_flag_es1)&(1 << sg_es1_stun_1)) {
              $a = 'p';
              $foe_obj->disableFlagBit(sg_flag_es1, sg_es1_stun_1);
            }

            $char_attack = array();
            if ('a' == $a) {
              $char_attack = getAttackFoeArray($c_obj, $foe, $a_t);
            } elseif ('u' == $a) {
              $char_attack = getCombatItemUseArray($c_obj, $i, $foe);
            } elseif ('m' == $a) {
              //$char_attack = getMagicUseArray($c_obj, $i, $foe);
              $char_attack = getRuneUseArray($c_obj, $i, $foe);
            } elseif ('p' == $a) {
              $char_attack['text'] = 'You take no action this round!';
            }
            if (array_key_exists('oppo_hp_lost', $char_attack)) {
              $foe_obj->setCurrentHp($foe_obj->c['current_hp'] -
                  $char_attack['oppo_hp_lost']);
              $ret_obj['foe_current_hp'] = $foe_obj->c['current_hp'];
            }

            $render_text = $char_attack['text'] . '***' .
                $char_attack['hit_text'];

            $c_obj->addFlag(sg_flag_combat_round,
                            getFlagValue($c_obj, sg_flag_combat_round) + 1);

            if ($foe_obj->c['current_hp'] <= 0) {

              $ret_obj['status_list'][] = awardAchievement($c_obj, 45);

              if (getFlagValue($c_obj, sg_flag_combat_round) == 1) {
                $ret_obj['status_list'][] = awardAchievement($c_obj, 24);
              }

              $c_obj->setEncounterId(0);
              $c_obj->setEncounterType(0);

              $foe_obj->setCurrentHp(0);

              updateDuelState($duel_state['id'], ts_duelstate_done,
                  $render_text);

              addDuelPlayers($c_obj->c['id'], $foe_obj->c['id']);

              $ret_obj['state'] |= ts_combat_char_victory;

              $log_obj->addLog($c_obj->c, sg_log_duel_win,
                  $foe_obj->c['id'], 0, 0, 0);

            } else {

              $new_state = ($duel_state['state'] == 1) ? 2 : 1;

              $cry_count = count($c_obj->c['battle_cries']);
              if ((rand(1, 10) < 9) && ($cry_count > 0)) {
                $cry = '<i>You scream out at your opponent!</i><br><b>' .
                    $c_obj->c['battle_cries'][rand(0, $cry_count - 1)] .
                    '</b>';
                $ret_obj['status_list'][] = $cry;
                $render_text = $render_text . '<br><br>' . $cry;
              }

              updateDuelState($duel_state['id'], $new_state, $render_text);
              $ret_obj['state'] |= ts_combat_waiting;

            }

          }
        }

        // Combat is ongoing, fill out the object with UI data.
        $ret_obj['attack_list'] = array();

        if (!(getFlagValue($foe_obj, sg_flag_es1) & (1 << sg_es1_stun_1))) {

          $attacks = getCombatAttacks($c_obj);
          foreach ($attacks as $attack) {
            if ($attack[a] == TRUE) {
              $ret_obj['attack_list'][] = '<a href="duel.php' . $attack['u'] .
                  '">' . $attack['n'] . '</a>';
            }
          }

          // Usable spells and artifacts
          $spells = getCombatRunes($c_obj);
          foreach ($spells as $spell) {
            if ($spell[a] == TRUE) {
              $ret_obj['spell_list'][] = '<a href="' . $spell['u'] .
                  '">' . $spell['n'] . '</a>';
            }
          }

          $ret_obj['artifact_list'] = getCombatArtifactsList($c_obj);

        }

      } elseif ((($duel_state['state'] == 2) &&
                 ($duel_state['char_id_1'] == $c_obj->c['id'])) ||
                (($duel_state['state'] == 1) &&
                 ($duel_state['char_id_2'] == $c_obj->c['id']))) {

        $ret_obj['state'] |= ts_combat_waiting;
        $ret_obj['render_text'] = $duel_state['render_text'];
        $ret_obj['time_delay'] = time() - $duel_state['timestamp'];

        if ('z' == $a) {
          if ($ret_obj['time_delay'] > 120) {
            $c_obj->setEncounterId(0);
            $c_obj->setEncounterType(0);

            $render_text = '<b>You declare that your opponent has fled ' .
                'shamefully from combat, and claim victory!</b>';

            $ret_obj['render_text'] = $render_text;
            updateDuelState($duel_state['id'], ts_duelstate_done,
                $render_text);

            addDuelPlayers($c_obj->c['id'], 1);

            $ret_obj['state'] = ts_combat_active | ts_combat_char_attacked |
                                ts_combat_char_victory;

            $log_obj->addLog($c_obj->c, sg_log_duel_timeout,
                $foe_obj->c['id'], 0, 0, 0);
          }
        }

      } else { // timeout victory claimed

        $ret_obj['render_text'] = $duel_state['render_text'];

        $c_obj->setEncounterId(0);
        $c_obj->setEncounterType(0);
        $_SESSION['duel_id'] = 0;

        $ret_obj['state'] = ts_combat_active | ts_combat_char_defeated;

      }

      $foe_obj->save();
    }

    $ret_obj['char_obj'] = $c_obj;
    $ret_obj['foe'] = array();
    $ret_obj['foe']['name'] = $foe['name'];
    $ret_obj['foe']['text'] = $foe['text'];
    $ret_obj['foe']['hp'] = $foe['hp'];
    $ret_obj['foe_avatar'] = str_replace('.jpg', '_r.jpg', $foe['avatar']);

    if ($c_obj->c['encounter_max_hp'] > 0) {
      $ret_obj['foe']['hp'] = round(100.0 *
          ($c_obj->c['encounter_hp'] / $c_obj->c['encounter_max_hp']));
    } else {
      $ret_obj['foe']['hp'] = 0;
    }

    $ret_obj['char_attack'] = $char_attack;
    $ret_obj['foe_attack'] = $foe_attack;

  }

  $ret_obj['render_text'] =
      str_replace('***', '<br><br>', $ret_obj['render_text']);
  $ret_obj['render_text'] = str_replace('&amp;', '&', $ret_obj['render_text']);
  $ret_obj['render_text'] = str_replace('&lt;', '<', $ret_obj['render_text']);
  $ret_obj['render_text'] = str_replace('&gt;', '>', $ret_obj['render_text']);
  $ret_obj['render_text'] = str_replace('&quot;', '"',$ret_obj['render_text']);

  $c_obj->save();
  return $ret_obj;
}

?>
