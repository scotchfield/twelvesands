<?

require_once 'include/core.php';

require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/achieve.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/foes.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/professions.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/skills.php';
require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/use.php';
require_once sg_base_path . 'include/validate.php';

function getCharState($state_params, $log_obj) {
  $a = $state_params['a'];
  $i = $state_params['i'];
  $m = $state_params['m'];
  $n = $state_params['n'];
  $s = $state_params['s'];
  $t = $state_params['t'];
  $act = $state_params['act'];

  $ret_obj = array();
  $ret_obj['state'] = 0;
  $ret_obj['out'] = array();

  $char_obj = new Char($_SESSION['c']);
  $c = $char_obj->c;

  if ('a' == $a) {

    $artifact = hasArtifact($char_obj, $i, $m);
    if (FALSE == $artifact) {
      $ret_obj['out'][] = '<p>You don\'t have that artifact!</p>';
    } elseif ($c['level'] < $artifact['min_level']) {
      $ret_obj['out'][] = '<p>Your level isn\'t high enough to use that ' .
          'artifact!</p>';
    } elseif (($artifact['skill_required'] > 0) &&
              (!array_key_exists($artifact['skill_required'], $c['skills']))) {
      $ret_obj['out'][] = '<p>You don\'t have the necessary skill required ' .
          'to use that artifact!</p>';
    } else {
      if ($c['weapon']['id'] != 0) {
        $ret_obj['out'][] =
            awardArtifactString($char_obj, $c['weapon'], 1, $c['weapon_enc']);
        $char_obj->addFlag(sg_flag_unequip, $c['weapon']['id']);
        $char_obj->addFlag(sg_flag_unequip_enc, $c['weapon_enc']);
      }
      if ($i != 0) {
        removeArtifact($char_obj, $i, 1, $m);
      }
      if ($artifact['type'] == 1) {
        $char_obj->setIdPair('weapon', $artifact['id'], $m);
        $char_obj->addFlag(sg_flag_equip, $artifact['id']);
        $char_obj->addFlag(sg_flag_equip_enc, $m);
        if ('0' == $i) {
          $ret_obj['out'][] = '<p>You have nothing equipped.</p>';
        } else {
          $ret_obj['out'][] = '<p>You equip the ' .
              renderArtifactStr($artifact) . '.</p>';
        }
      } else {
        $char_obj->setIdPair('weapon', 0, 0);
        $ret_obj['out'][] = '<p>You have nothing equipped.</p>';
      }

      unset($_SESSION['equipped_array']);
      $ret_obj['header'] = 'Location: char.php';
    }

  } elseif ('am' == $a) {

    $artifact = hasArtifact($char_obj, $i);
    if (FALSE == $artifact) {
      $ret_obj['out'][] = '<p>You don\'t have that artifact!</p>';
    } elseif ($c['level'] < $artifact['min_level']) {
      $ret_obj['out'][] = '<p>Your level isn\'t high enough to use that ' .
          'artifact!</p>';
    } elseif (($artifact['skill_required'] > 0) &&
              (!array_key_exists($artifact['skill_required'], $c['skills']))) {
      $ret_obj['out'][] = '<p>You don\'t have the necessary skill required ' .
          'to use that artifact!</p>';
    } else {
      if ($c['mount']['id'] != 0) {
        $ret_obj['out'][] = awardArtifactString($char_obj, $c['mount'], 1);
        $char_obj->addFlag(sg_flag_unequip, $c['mount']['id']);
      }
      if ($i != 0) {
        removeArtifact($char_obj, $i, 1);
      }
      if ($artifact['type'] == sg_artifact_mount) {
        $char_obj->setMountId($artifact['id']);
        $char_obj->addFlag(sg_flag_equip, $artifact['id']);
        if ('0' == $i) {
          $ret_obj['out'][] = '<p>You have no mount prepared.</p>';
        } else {
          $ret_obj['out'][] = '<p>You prepare your ' .
              renderArtifactStr($artifact) . '.</p>';
        }
      } else {
        $char_obj->setMountId(0);
        $ret_obj['out'][] = '<p>You have no mount prepared.</p>';
      }

      unset($_SESSION['equipped_array']);
      $ret_obj['header'] = 'Location: char.php';
    }

  } elseif ('aa' == $a) {

    $artifact = hasArtifact($char_obj, $i, $m);
    if ('0' == $t) {
      $ret_obj['out'][] = '<p>What?</p>';
    } elseif (FALSE == $artifact) {
      $ret_obj['out'][] = '<p>You don\'t have that artifact!</p>';
    } elseif ($c['level'] < $artifact['min_level']) {
      $ret_obj['out'][] = '<p>Your level isn\'t high enough to use that ' .
          'artifact!</p>';
    } elseif (($artifact['skill_required'] > 0) &&
              (!array_key_exists($artifact['skill_required'], $c['skills']))) {
      $ret_obj['out'][] = '<p>You don\'t have the necessary skill required ' .
          'to use that artifact!</p>';
    } elseif (($i > 0) && ($artifact['type'] != $t)) {
      $ret_obj['out'][] = '<p>You can\'t equip that there!</p>';
    } else {
      $award = 0;
      switch ($t) {
      case sg_artifact_armour_head: $award = $c['armour_head']; break;
      case sg_artifact_armour_chest: $award = $c['armour_chest']; break;
      case sg_artifact_armour_legs: $award = $c['armour_legs']; break;
      case sg_artifact_armour_neck: $award = $c['armour_neck']; break;
      case sg_artifact_armour_hands: $award = $c['armour_hands']; break;
      case sg_artifact_armour_wrists: $award = $c['armour_wrists']; break;
      case sg_artifact_armour_belt: $award = $c['armour_belt']; break;
      case sg_artifact_armour_boots: $award = $c['armour_boots']; break;
      case sg_artifact_armour_ring:
        if (1 == $s) {
          $award = $c['armour_ring'];
        } elseif (2 == $s) {
          $award = $c['armour_ring_2'];
        } else {
          if ($c['armour_ring']['id'] == 0) {
            $award = $c['armour_ring']; $s = 1;
          } elseif ($c['armour_ring_2']['id'] == 0) {
            $award = $c['armour_ring_2']; $s = 2;
          } else {
            $award = $c['armour_ring']; $s = 1;
          }
        }
        break;
      case sg_artifact_armour_trinket:
        if (1 == $s) {
          $award = $c['armour_trinket'];
        } elseif (2 == $s) {
          $award = $c['armour_trinket_2'];
        } elseif (3 == $s) {
          $award = $c['armour_trinket_3'];
        } else {
          if ($c['armour_trinket']['id'] == 0) {
            $award = $c['armour_trinket']; $s = 1;
          } elseif ($c['armour_trinket_2']['id'] == 0) {
            $award = $c['armour_trinket_2']; $s = 2;
          } elseif ($c['armour_trinket_3']['id'] == 0) {
            $award = $c['armour_trinket_3']; $s = 3;
          } else {
            $award = $c['armour_trinket']; $s = 1;
          }
        }
        break;
      default: break;
      }

      if (($award != 0) && ($award['id'] != 0)) {
        $ret_obj['out'][] = awardArtifactString(
            $char_obj, $award, 1, $award['m_enc']);
        $char_obj->addFlag(sg_flag_unequip, $award['id']);
        $char_obj->addFlag(sg_flag_unequip_enc, $award['m_enc']);
      }
      if ($i != 0) {
        removeArtifact($char_obj, $i, 1, $m);
      }

      if ($t == sg_artifact_armour_ring) {
        if ($s == 1) {
          $char_obj->setIdPair('armour_ring', $i, $m);
        } elseif ($s == 2) {
          $char_obj->setIdPair('armour_ring_2', $i, $m);
        }
      } elseif ($t == sg_artifact_armour_trinket) {
        if ($s == 1) {
          $char_obj->setIdPair('armour_trinket', $i, $m);
        } elseif ($s == 2) {
          $char_obj->setIdPair('armour_trinket_2', $i, $m);
        } elseif ($s == 3) {
          $char_obj->setIdPair('armour_trinket_3', $i, $m);
        }
      } else {
        $armour_set = setArmour($char_obj, $t, $i, $m);
      }

      if ('0' == $i) {
        $ret_obj['out'][] = '<p>You have nothing equipped.</p>';
      } else {
        $ret_obj['out'][] = '<p>You equip the ' .
            renderArtifactStr($artifact) . '.</p>';
        $char_obj->addFlag(sg_flag_equip, $artifact['id']);
        $char_obj->addFlag(sg_flag_equip_enc, $m);
      }

      unset($_SESSION['equipped_array']);
      $ret_obj['header'] = 'Location: char.php';
    }

  } elseif ('ma' == $a) {

    $ret_obj['out'][] =
        '<p><span class="section_header">Cast a Spell</span></p>';

    if (0 != $i) {
      $use_array = useRune($char_obj, $i);
      $log_obj->addLog($c, sg_log_spell_cast, $i, 0, 0, 0);
      foreach ($use_array as $use_str) {
        $ret_obj['out'][] = $use_str;
      }
    }

    $usableSpells = getRunes($char_obj);
    if (count($usableSpells) > 0) {

      $ret_obj['out'][] = '<p><form method="get" action="char.php">' .
          '<input type="hidden" name="a" value="ma" />' .
          '<select name="i">';

      foreach ($usableSpells as $s_key => $s_name) {
        if ($s_key == $i) {
          $ret_obj['out'][] = '<option value="' . $s_key . '" selected>' .
              $s_name . '</option>';
        } else {
          $ret_obj['out'][] = '<option value="' . $s_key . '">' .
              $s_name . '</option>';
        }
      }

      $ret_obj['out'][] = '</select>' .
          '<input type="submit" value="Cast a Spell" />' .
          '</form></p>';

    } else {
      $ret_obj['out'][] = '<p>Unfortunately you don\'t have enough ' .
          'mana to cast any spells right now.  You\'ll need to either ' .
          'recover some mana today, or wait until you\'ve had a proper ' .
          'night\'s sleep and have recovered your mana through rest.</p>';
    }

  } elseif ('tc' == $a) {

    $skill = hasSkill($char_obj, $i);
    if (FALSE == $skill) {
      $ret_obj['out'][] = '<p>You don\'t know that skill!</p>';
    } elseif ($i < 1) {
      $char_obj->setTitledName($char_obj->c['name']);
      $ret_obj['out'][] = '<p>You are now known as ' . $char_obj->c['name'] .
          '.</p>';
    } else {
      $titled = str_replace('_', $char_obj->c['name'],
                            $skill['title_granted']);
      $char_obj->setTitledName($titled);
      $ret_obj['out'][] = '<p>You are now known as ' . $titled . '.</p>';
    }

  } elseif ('rmr' == $a) {

    $ret_obj['out'][] = '<p><b>Abandon an inscribed rune</b></p>';
    $rune = hasRune($char_obj, $i);
    if (FALSE == $rune) {
      $ret_obj['out'][] = '<p>You don\'t have that rune inscribed!</p>';
    } elseif ($char_obj->c['d_id'] != 0) {
      $ret_obj['out'][] = '<p>You are unable to remove a rune while on a ' .
          'dungeon run!</p>';
    } elseif ($act == 0) {
      $ret_obj['out'][] = '<p>Are you sure you want to discard ' .
          renderArtifactStr($rune) . '?</p><p><b><a href="char.php?a=rmr&i=' .
          $rune['id'] . '&act=' . $char_obj->c['action_id'] .
          '">Yes, discard the rune!</a></b></p>' .
          '<p><a href="char.php">No, never mind..</a></p>';
    } elseif ($act == $char_obj->c['action_id']) {
      $char_obj->resetActionId();
      deleteRune($char_obj, $rune['id']);
      $ret_obj['out'][] = '<p>You close your eyes, and focus on the symbol ' .
          'etched upon your flesh..  With gritted teeth and clenched fists, ' .
          'you draw upon your willpower, and cast the symbol from your body!</p>';
    }

  } elseif (('admin' == $a) && ($char_obj->c['user_id'] == 1)) {

    if (1 == $s) {
      $char_obj->setLevel($char_obj->c['level'] + 1);
      $ret_obj['out'][] = '<p class="tip">Level increased.</p>';
    } elseif (2 == $s) {
      if ($n < 1) { $n = 1; }
      if ($n > 10000) { $n = 10000; }
      $artifact = getArtifact($i);
      if (FALSE == $artifact) {
        $ret_obj['out'][] = '<p class="tip">That artifact doesn\'t exist!</p>';
      } else {
        $ret_obj['out'][] = awardArtifactString($char_obj, $artifact, $n);
      }
    } elseif (3 == $s) {
      if ($n < 1) { $n = 1; }
      if ($n > 10000000) { $n = 10000000; }
      $char_obj->setGold($char_obj->c['gold'] + $n);
      $ret_obj['out'][] = '<p class="tip">Awarded ' . $n . ' gold.</p>';
    } elseif (4 == $s) {
      $char_obj->setProfCooking($n);
      $char_obj->setProfMining($n);
      $char_obj->setProfFishing($n);
      $char_obj->setProfCrafting($n);
      $ret_obj['out'][] = '<p class="tip">Set professions to ' . $n . '</p>';
    } elseif (5 == $s) {
      $time = time();
      addBuff($char_obj, $i, $n, 0, 0);
      $ret_obj['out'][] = '<p class="tip">Gave buff for ' . $n .
          ' seconds</p>';
    } elseif (6 == $s) {
      deleteAllBuffs($char_obj);
      $ret_obj['out'][] = '<p class="tip">Deleted all buffs</p>';
    }

  }

  $ret_obj['char_obj'] = $char_obj;
  $save = $char_obj->save();

  return $ret_obj;
}

?>