<?

require_once 'include/core.php';
$debug_time_start = debugTime();

require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/achieve.php';
require_once sg_base_path . 'include/bank.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/quests.php';
require_once sg_base_path . 'include/plots.php';
require_once sg_base_path . 'include/professions.php';
require_once sg_base_path . 'include/runes.php';
require_once sg_base_path . 'include/sql.php';

$x = 1;
echo($x);


$log_obj = new Logger();
$char_obj = new Char( $_SESSION[ 'c' ] );
$c = $char_obj->c;
forceCombatCheck( $char_obj );

$z = getGetInt( 'z', 1 );
$main_state = getGetInt( 's', 0 );

$zone = getZone( $z );
$zone_children = $zone[ 'children' ];

$zone_available = TRUE;
if ($zone['entry_check'] > 0) {
  $game_flags = getGameFlags();

  switch ($zone['entry_check']) {
  case 1:
    if ($game_flags[4] > 0) {
      $zone_available = FALSE;
    }
    break;
  case 2:
    if ($char_obj->c['pravokan_bonus'] < 2) {
      $zone_available = FALSE;
    }
    break;
  case 3:
    if ($char_obj->c['pravokan_bonus'] < 6) {
      $zone_available = FALSE;
    }
    break;
  case 4:
    if ($char_obj->c['dunnich_bonus'] < 1) {
      $zone_available = FALSE;
    }
    break;
  }

  //if ($char_obj->c['id'] == 1) { $zone_available = TRUE; }
}

if ($zone_available == FALSE) {
  // can't access this zone!
} elseif ($zone['zone_type'] == sg_zone_store) {

  $zone_artifacts = getZoneArtifacts($z);

} elseif ($zone['zone_type'] == sg_zone_itemstore) {

  $zone_artifacts = getStoreArtifacts($z);

} elseif ($zone['zone_type'] == sg_zone_infirmary) {

  include('zones/infirmary.php');
  $infirmary_heal_str = getInfirmaryHealState($char_obj);

} elseif ($zone['zone_type'] == sg_zone_capitalcasino) {

  include('zones/capitalcasino.php');
  $casino_output_obj = getCustomZoneState($zone, $char_obj, $log_obj);

} elseif ($zone['zone_type'] == sg_zone_pathfinder) {

  include('zones/pathfinder.php');
  $pathfinder_output_obj = getPathfinderState($char_obj, $log_obj);

} elseif ($zone['zone_type'] == sg_zone_warfaregame) {

  include('zones/warfaregame.php');
  $custom_output_obj = getCustomState($char_obj, $log_obj);

} elseif ($zone['zone_type'] == sg_zone_sandstorm) {

  include('zones/sandstorm.php');
  $custom_output_obj = getCustomState($char_obj, $log_obj);

} elseif ($zone['zone_type'] == sg_zone_scarshield_stairs) {

  include('zones/scarstairs.php');
  $custom_output_obj = getCustomState($char_obj, $log_obj);

} elseif ($zone['zone_type'] == sg_zone_trading_company) {

  include('zones/tradingcompany.php');
  $custom_output_obj = getCustomState($char_obj, $log_obj);

} elseif ($zone['zone_type'] == sg_zone_lottery) {

  include('zones/lottery.php');
  $custom_output_obj = getCustomState($char_obj, $log_obj);

} elseif ($zone['zone_type'] == sg_zone_pravokan_revelry) {

  include('zones/revelry.php');
  $custom_output_obj = getCustomState($char_obj, $log_obj);

} elseif ($zone['zone_type'] == sg_zone_plotlist) {

  include('zones/plotlist.php');
  $custom_output_obj = getCustomState($char_obj, $log_obj);

} elseif ($zone['zone_type'] == sg_zone_dungeon) {

  if ((checkIfFatigued($char_obj) == FALSE) &&
      (checkIfWounded($char_obj) == FALSE) &&
      (checkIfBurdened($char_obj) == FALSE)) {

    include '_dungeon.php';

    if ($encounter['type'] == sg_encounter_choice) {
      $char_obj->setEncounterId($encounter['id']);
      $char_obj->setEncounterType(sg_encountertype_choice);
      $char_obj->setEncounterArtifact($encounter['artifact_required']);
      $char_obj->addFlag(sg_flag_combat_flag_id_set,
                         $encounter['flag_id_set']);
      $char_obj->addFlag(sg_flag_combat_flag_bit_set,
                         $encounter['flag_bit_set']);
      header('Location: choice.php');
    }

  }

} elseif ($zone['zone_type'] == sg_zone_encounter) {

  if ((checkIfFatigued($char_obj) == FALSE) &&
      (checkIfWounded($char_obj) == FALSE) &&
      (checkIfBurdened($char_obj) == FALSE)) {

    if (sg_newcombat) {



    } else {

      $encounter = getEncounter($char_obj, $zone['id']);
      if ($encounter['type'] == sg_encounter_foe) {
        initiateCombat($char_obj, $encounter, $zone);
      } elseif ($encounter['type'] == sg_encounter_choice) {
        $char_obj->setEncounterId($encounter['id']);
        $char_obj->setEncounterType(sg_encountertype_choice);
        $char_obj->setEncounterArtifact($encounter['artifact_required']);
        header('Location: choice.php');
      }

    }

  }

} elseif ($zone['zone_type'] == sg_zone_bank) {

  header('Location: bank.php');
  exit;

}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss($char_obj->c); ?>
</head>
<body>

<? renderPopupText(); ?>
<script type="text/javascript" src="include/ts_keypress.js"></script>

<div class="container">

<?

checkZoneLevel($zone, $char_obj);

require '_header.php';

if ($zone_available == FALSE) {

  echo '<p class="zone_title">' . $zone['name'] . '</p>';
  echo '<p class="zone_description">' . $zone['description'] . '</p>';
  echo '<p class="tip">This zone isn\'t available for you to access!</p>';

  switch ($zone['id']) {
  case 112:
  case 124:
    echo '<p><b>You need to eliminate the defenders guarding the Lost ' .
         'Storage Halls entrance at the Scarshield Staircases before you ' .
         'can enter this zone.</b></p>';
    break;
  case 120:
  case 121:
    echo '<p><b>As you approach the shore of the island, the pirates ' .
         'begin to point and scream!  Suddenly, the water around you begins ' .
         'to explode in a shower of water and fire, and you realize you are ' .
         'being fired upon!  You make a hasty retreat.</b></p>';
    break;
  case 138:
  case 139:
    echo '<p><b>You attempt to approach the area, but find that you are ' .
         'swarmed by the spirits guarding Dunnich!  In order to get in ' .
         'to the city, you\'ll need some sort of disguise, or to ' .
         'otherwise make yourself unseen!</b></p>';
  }

} elseif (($zone['zone_type'] == sg_zone_encounter) ||
          ($zone['zone_type'] == sg_zone_dungeon)) {

  if (checkIfFatigued($char_obj) == TRUE) {
    echo '<p class="zone_title">' . $zone['name'] . '</p>';
    echo '<p><b>You\'re too tired!</b><br>' .
         'Eat some food to regain your strength, ' .
         'or rest for the remainder of the day and return tomorrow.</p>';
  } elseif (checkIfWounded($char_obj) == TRUE) {
    echo '<p class="zone_title">' . $zone['name'] . '</p>';
    echo '<p><b>You\'re too injured!</b><br>' .
         'Heal up at the infirmary, or consider ' .
         'eating some food to regain your health.</p>';
  } elseif (checkIfBurdened($char_obj) == TRUE) {
    echo '<p class="zone_title">' . $zone['name'] . '</p>';
    echo '<p><b>You\'re too burdened!</b><br>' .
         'Visit the <a href="bank.php">Capital City Bank</a> ' .
         'to deposit some goods, or consider <a href="sell.php">selling ' .
         'something</a> at a local vendor.</p>';
  } else {

    if ($encounter['type'] == sg_encounter_treasure) {
      echo '<p><a href="#" onclick="document.location=document.' .
           'getElementById(\'bar_default\').href;">' .
           '<img src="images/buff-green.gif" width="24" height="24" ' .
           'border="0" onmouseover="popup(\'<b>(`) Adventure again</b>\')" ' .
           'onmouseout="popout()"></a>&nbsp;';
      for ($i = 0; $i < 10; $i++) {
        echo '<img src="images/buff-empty.gif" width="24" height="24">';
      }
      echo '</p>';

      echo '<p class="zone_title">' . $encounter['name'] . '</p>';
      echo '<p>' . $encounter['text'] . '</p>';

      if ($encounter['reward'] == 1) {
        $artifact = getArtifact($encounter['artifact']);
        awardArtifact($char_obj, $artifact, $encounter['quantity']);
        $char_obj->addFatigue($encounter['fatigue']);
      }
      if ($encounter['artifact_required'] > 0) {
        $encounter_artifact = getArtifact($encounter['artifact_required']);
        echo 'Your ' . $encounter_artifact['name'] . ' is consumed.';
        removeArtifact($char_obj, $encounter['artifact_required'], 1);
      }
      if ($encounter['flag_id_set'] > 0) {
        $val = 0;
        if (array_key_exists($encounter['flag_id_set'],
                             $char_obj->c['flags'])) {
          $val = $char_obj->c['flags'][$encounter['flag_id_set']];
        }
        $val = $val | (1 << $encounter['flag_bit_set']);
        $char_obj->addFlag($encounter['flag_id_set'], $val);
      }
    }

    $char_obj->addFlag(sg_flag_last_combat_zone, $zone['id']);

    echo '<p><a href="main.php?z=' . $z .
         '">Adventure again</a></p>';

  }

} elseif ($zone['zone_type'] == sg_zone_infirmary) {

  renderInfirmaryHealState($zone, $infirmary_heal_str);

} elseif ($zone['zone_type'] == sg_zone_capitalcasino) {

  renderCustomZoneState($casino_output_obj);

} elseif ($zone['zone_type'] == sg_zone_pathfinder) {

  renderPathfinderState($zone, $char_obj, $pathfinder_output_obj);

} elseif (($zone['zone_type'] == sg_zone_warfaregame) ||
          ($zone['zone_type'] == sg_zone_sandstorm) ||
          ($zone['zone_type'] == sg_zone_scarshield_stairs) ||
          ($zone['zone_type'] == sg_zone_trading_company) ||
          ($zone['zone_type'] == sg_zone_lottery) ||
          ($zone['zone_type'] == sg_zone_pravokan_revelry) ||
          ($zone['zone_type'] == sg_zone_plotlist)) {

  renderCustomState($zone, $char_obj, $custom_output_obj);

} elseif ($zone['zone_type'] == sg_zone_hallofrecords) {
  include('zones/hallofrecords.php');
} elseif ($zone['zone_type'] == sg_zone_grandacademy) {
  include('zones/grandacademy.php');
} elseif ($zone['zone_type'] == sg_zone_auctionhouse) {
  if ($char_obj->c['d_id'] > 0) {
    echo '<p class="zone_title">' . $zone['name'] . '</p>';
    echo '<p class="zone_description">' . $zone['description'] . '</p>';
    echo '<p class="tip">You can\'t use the auction house while ' .
         'on a dungeon run!</p>';
  } else {
    include('zones/auctionhouse.php');
  }

} elseif ($zone['zone_type'] == sg_zone_fishing) {

  $a = getGetStr('a', '0');

?>
  <p class="zone_title"><?= $zone['name']; ?></p>
  <p class="zone_description"><?= $zone['description']; ?></p>
<?

  if ($char_obj->c['fatigue'] >= 100000) {
    echo '<p>You are far too tired to fish any more today.</p>';
  } else {

    if ($char_obj->c['fishing_bonus'] > 0) {
      $n_fishing = getGetInt('n', 0);
      if ($n_fishing > 0) {
        $n_fishing = goFishingInZone($char_obj, $z, $n_fishing);
        $char_obj->setTotalFatigueUses(
            $char_obj->c['total_fatigue_uses'] + $n_fishing);
        $log_obj->addLog($char_obj->c, sg_log_fishing, $z, $n_fishing, 0, 0);
      }

      echo '<p><a href="main.php?z=' . $z . '&n=1">Fish once!</a></p>';
      echo '<p><form method="get" action="main.php">Fish multiple times: ' .
           '<input type="text" name="n" value="1" size="8"> ' .
           '<input type="hidden" name="z" value="' . $z . '">' .
           '<input type="submit" value="Go fishing!"></form></p>';

    } else {
      echo '<p>You stand at the edge of the water, and realize that you ' .
           'have no way of fishing.  Find a fishing rod, and come back!</p>';
    }
  }

} elseif ($zone['zone_type'] == sg_zone_mining) {

  $a = getGetStr('a', '0');

?>
  <p class="zone_title"><?= $zone['name']; ?></p>
  <p class="zone_description"><?= $zone['description']; ?></p>
<?

  if ($char_obj->c['fatigue'] >= 100000) {
    echo '<p>You are far too tired to mine any more today.</p>';
  } else {

    if ($char_obj->c['mining_bonus'] > 0) {
      $n_mining = getGetInt('n', 0);
      if ($n_mining > 0) {
        $n_mining = goMiningInZone($char_obj, $z, $n_mining);
        $char_obj->setTotalFatigueUses(
            $char_obj->c['total_fatigue_uses'] + $n_mining);
        $log_obj->addLog($char_obj->c, sg_log_mining, $z, $n_mining, 0, 0);
      }

      echo '<p><a href="main.php?z=' . $z . '&n=1">Mine once!</a></p>';
      echo '<p><form method="get" action="main.php">Mine multiple times: ' .
           '<input type="text" name="n" value="1" size="8"> ' .
           '<input type="hidden" name="z" value="' . $z . '">' .
           '<input type="submit" value="Go mining!"></form></p>';

    } else {
      echo '<p>You stand, facing the wall, and come to the realization that ' .
          ' you have no way of mining.  Find a pick, and come back!</p>';
    }
  }

} else {

  if (($char_obj->c['xp'] < 25) && ($char_obj->c['d_id'] == 0)) {
    echo '<p class="tip">As a new player, welcome to the ' .
         'Twelve Sands!<br>If you have any questions, please visit the ' .
         '<a href="/faq/?page=2" target="_blank">New Player\'s ' .
         'Guide</a>.</p>';
    echo '<p class="tip">If you don\'t know what to do, visit your ' .
         '<a href="char.php?a=ql">Quest Log</a>, which is available from ' .
         'your character profile page.  You can click on your name at the ' .
         'top left corner of the screen at any time to access your profile, ' .
         'where you\'ll find lots of helpful information!</p>';
  }

  if (getFlagBit($char_obj, sg_flag_ui, sg_flagui_show_tip)) {
    if (!getFlagBit($char_obj, sg_flag_account_bit_options, 0)) {
      require_once sg_base_path . '_tip.php';
    }
    $char_obj->disableFlagBit(sg_flag_ui, sg_flagui_show_tip);
  }

  if (getFlagValue($char_obj, sg_flag_store_ui) > 0) {
    if (getFlagBit($char_obj, sg_flag_store_ui, sg_store_ui_bought)) {
      $artifact = getArtifact(
          getFlagValue($char_obj, sg_flag_store_artifact),
          getFlagValue($char_obj, sg_flag_store_enc));
      echo '<p class="tip">You purchase ' .
           getFlagValue($char_obj, sg_flag_store_count) . ' ' .
           renderArtifactStr($artifact,
                             getFlagValue($char_obj,
                                          sg_flag_store_count)) . '.';

      if ($artifact['type'] == 1) {
        echo ' <font size="-2">(<a href="char.php?a=a&i=' . $artifact['id'] .
             '">equip</a>)</font></li>';
      } elseif (in_array($artifact['type'], $armourArray)) {
        echo ' <font size="-2">(<a href="char.php?a=aa&i=' . $artifact['id'] .
             '&t=' . $artifact['type'] . '">equip</a>)</font></li>';
      }

      echo '</p>';

      if (getFlagValue($char_obj, sg_flag_store_count) == 1000) {
        if ($artifact['id'] == 24) {
          echo awardAchievement($char_obj, 27);
        } elseif ($artifact['id'] == 25) {
          echo awardAchievement($char_obj, 28);
        } elseif ($artifact['id'] == 193) {
          echo awardAchievement($char_obj, 29);
        }
      }

    } elseif (getFlagBit($char_obj, sg_flag_store_ui, sg_store_ui_sold)) {
      $artifact = getArtifact(
          getFlagValue($char_obj, sg_flag_store_artifact),
          getFlagValue($char_obj, sg_flag_store_enc));
      echo '<p class="tip">You sell ' .
           getFlagValue($char_obj, sg_flag_store_count) . ' ' .
           renderArtifactStr($artifact,
                             getFlagValue($char_obj,
                                          sg_flag_store_count)) .
           '.</p>';
    } elseif (getFlagBit(
        $char_obj, sg_flag_store_ui, sg_store_ui_not_sold_here)) {
      echo '<p class="tip">The store doesn\'t sell that artifact!</p>';
    } elseif (getFlagBit(
        $char_obj, sg_flag_store_ui, sg_store_ui_invalid_amount)) {
      echo '<p class="tip">That amount is not valid!</p>';
    } elseif (getFlagBit(
        $char_obj, sg_flag_store_ui, sg_store_ui_no_money)) {
      echo '<p class="tip">You don\'t have enough to purchase that!</p>';
    } elseif (getFlagBit(
        $char_obj, sg_flag_store_ui, sg_store_ui_no_rep)) {
      echo '<p class="tip">You are not sufficiently known within that ' .
           'faction!</p>';
    } elseif (getFlagBit(
        $char_obj, sg_flag_store_ui, sg_store_ui_no_quantity)) {
      echo '<p class="tip">You don\'t have that many to sell!</p>';
    } elseif (getFlagBit(
        $char_obj, sg_flag_store_ui, sg_store_ui_no_cant_sell)) {
      echo '<p class="tip">You can\'t sell that!</p>';
    }

    $char_obj->addFlag(sg_flag_store_ui, 0);
    $char_obj->addFlag(sg_flag_store_artifact, 0);
    $char_obj->addFlag(sg_flag_store_count, 0);
  }

?>

  <p class="zone_title"><?= $zone['name']; ?></p>
  <p class="zone_description"><?= $zone['description']; ?></p>

<?

  if ($zone['id'] == 94) {
    $artifact = getArtifact(63);
    echo '<p>A ' . renderArtifactStr($artifact) . ' is awarded to players ' .
         'who have helped support ongoing maintenance costs of the game.  ' .
         'Twelve Sands will <i>always</i> be free to play, but if you\'d ' .
         'like to help offset some of the growing server and bandwidth ' .
         'costs, please consider <a href="donate.php">donating</a>. ' .
         'Twelve Sands is kept alive through the support of our players, ' .
         'and every little bit makes a difference!  :)</p>';
    $artifact_2 = getArtifact(864);
    echo '<p>You can also obtain a ' . renderArtifactStr($artifact_2) .
         ' by referring players to the game.  If a player who you referred ' .
         'donates, you will receive one ' . renderArtifactStr($artifact_2) .
         ' in thanks, and five of these can be exchanged for one ' .
         renderArtifactStr($artifact) . '.</p>';
  }

  if (($zone['zone_type'] == sg_zone_store) ||
      ($zone['zone_type'] == sg_zone_itemstore)) {

    $artifact_id = getGetInt('a', 0);

    if ($main_state == 1) {
/*
      echo '<script type="text/javascript" src="include/ts_sell.js"></script>';
      echo '<p>Sell some artifacts:</p>';
      echo '<p><form method="get" action="action.php">';
      echo '<input type="hidden" id="artifact_enchant" name="ae" value="0">';
      echo '<select name="i">';

      $a_obj = getCharArtifacts($char_obj->c['id']);

      foreach($a_obj as $artifact) {
        if ($artifact['sell_price'] > 0) {
          if (!getBit($artifact['flags'], sg_artifact_flag_nosell)) {
            $m_st = '';
            if ($artifact['m_enc'] > 0) {
              $enc = getEnchant($artifact['m_enc']);
              $m_st = ', ' . getModifierString($enc['m'], $enc['v']);
            }
            echo '<option value="' . $artifact['id'] . '" ' .
                 'onclick="setArtifactEnchant(' . $artifact['m_enc'] . ');">' .
                 $artifact['name'] . $m_st . ' (' . $artifact['quantity'] .
                 ' owned) (' . $artifact['sell_price'] .
                 ' gold)</option>' . "\n";
          }
        }
      }

      echo '</select>';
      echo '<br>How many would you like to sell?';
      echo '<input type="text" name="n" value="1" size="8">';
      echo '<input type="hidden" name="a" value="sa">';
      echo '<input type="hidden" name="z" value="' . $z . '">';
      echo '<input type="submit" value="Sell them!">';
      echo '</form></p>';

      echo '<p><a href="main.php?z=' . $z . '">Back to ' . $zone['name'] .
           '</a></p>';
*/
    } elseif ($artifact_id == 0) {

      echo '<p><span class="section_header">Artifacts for sale:</span></p>';

      $ul = TRUE;
      if (count($zone_artifacts) >= 4) {
        $ul = FALSE; $tr = 0;
      }

      if (TRUE == $ul) {
        echo '<ul class="selection_list">';
      } else {
        echo '<center><table width="80%" border="0" class="store"><tr>';
      }

      $rep_id_obj = array();

      foreach ($zone_artifacts as $x) {
        $x['gold_cost'] = $x['buy_price'];

        if ($x['reputation_id'] > 0) {
          $rep_id_obj[$x['reputation_id']] = TRUE;
        }

        if (TRUE == $ul) { echo '<li>'; } else {
          if ($tr == count($zone_artifacts) - 1) {
            echo '<td colspan="2" width="50%">';
          } else {
            echo '<td width="50%">';
          }
        }

        if (canAffordStore($char_obj, $x, 1)) {
          if (($x['min_level'] > $char_obj->c['level']) ||
              (($x['skill_required'] > 0) &&
               (!in_array($x['skill_required'], $char_obj->c['skills'])))) {
            echo '<s>'.renderArtifactWithEquippedStr($char_obj, $x).'</s>';
          } else {
            echo renderArtifactWithEquippedStr($char_obj, $x);
          }
          echo  '<br><font size="-2">(cost: ';
          echo getCostStr($char_obj, $x);
          echo ': ' . '<a href="action.php?a=ba&z=' . $zone['id'] .
              '&i=' . $x['row_id'] . '">buy</a> / <a href="main.php?' .
              'z=' . $zone['id'] . '&a=' . $x['row_id'] .
              '">buy multiple</a>)</font>';
        } else {
          echo '<s>';
          echo renderArtifactWithEquippedStr($char_obj, $x);
          echo '</s><br><font size="-2">(cost: ';
          echo getCostStr($char_obj, $x);
          echo ')</font>';
        }

        if (TRUE == $ul) {
          echo '</li>';
        } else {
          $tr += 1;
          echo '</td>';
          if ($tr % 2 == 0) { echo '</tr><tr>'; }
        }
      }

      if (TRUE == $ul) { echo '</ul>'; } else {
        echo '</tr></table></center>';
      }

      if (count($rep_id_obj) > 0) {
        echo '<p><b>Your reputation related to these artifacts:</b></p>';
        foreach ($rep_id_obj as $k => $v) {
          if (isset($char_obj->c['reputations'][$k])) {
            $rep = $char_obj->c['reputations'][$k];
          } else {
            $rep = array('reputation_id' => $k,
                         'name' => getReputationName($k));
          }

          echo renderReputation($char_obj, $rep, '<br>');
        }
      }

      echo '<p><a href="sell.php?z=' . $z . '">Sell stuff</a></p>';

    } else {

      if (!array_key_exists($artifact_id, $zone_artifacts)) {
        echo '<p>The store doesn\'t sell that artifact.</p>';
      } else {
        $artifact = getArtifact($zone_artifacts[$artifact_id]['id']);

        echo '<p>How many would you like?  (';
        renderArtifact($artifact);
        echo ')</p>';
        echo '<p><form method="get" action="action.php">';
        echo 'Purchase: <input type="text" name="n" value="1" size="8">';
        echo '<input type="hidden" name="z" value="' . $z . '">';
        echo '<input type="hidden" name="i" value="' . $artifact_id . '">';
        echo '<input type="hidden" name="a" value="ba"> ';
        echo '<input type="submit" value="Buy them!">';
        echo '</form></p>';
      }

      echo '<p><a href="main.php?z=' . $z . '">Back to the store</a></p>';

    }
  }

} // encounter zone.

  if (sizeof($zone['npcs']) > 0) { ?>
    <p><span class="section_header">Interesting people in the area</span></p>
    <ul class="selection_list"><?
      foreach ($zone['npcs'] as $x) {
        echo '<li><a href="talk.php?t=' . $x['id'] . '">' . $x['name'] .
             '</a></li>';
      }
    ?></ul><?
  }

  if (count($zone_children) > 0) {

   $local_places = 0;
   $travel_places = 0;

   $special_zone_types = array(
     sg_zone_travel,
     sg_zone_encounter,
     sg_zone_fishing,
     sg_zone_mining,
     sg_zone_dungeon,
     sg_zone_scarshield_stairs,
   );

   foreach ($zone_children as $x) {
     if (in_array($x['zone_type'], $special_zone_types)) {
       $travel_places = 1;
     } else {
       $local_places = 1;
     }
   }

  if ($local_places > 0) {

    $special_zone_types = array(
      sg_zone_travel,
      sg_zone_encounter,
      sg_zone_fishing,
      sg_zone_mining,
      sg_zone_dungeon,
      sg_zone_scarshield_stairs,
    );

?>

    <p><span class="section_header">Places to go</span></p>
    <center><table border="0" class="nav_table"><?
      $last_ui = 0;
      foreach ($zone_children as $x) {
        if (($x['dev'] > 0) && (sg_debug == 0)) {
          continue;
        }
        if (!in_array($x['zone_type'], $special_zone_types)) {
          if (canVisitZone($char_obj, $x)) {
            echo '<tr><td width="100" align="right"><span class="nav_type">';
            if ($x['ui_order'] != $last_ui) {
              $last_ui = $x['ui_order'];
              switch ($last_ui) {
              case 10: echo 'Travel '; break;
              case 20: echo 'Store '; break;
              case 100: echo 'Special '; break;
              }
            }
            echo '&nbsp;</span></td><td>';
            echo '<a href="main.php?z=' . $x['id'] . '" ' .
                 'onmouseover="popup(\'<b>' . getEscapeQuoteStr($x['name']) .
                 '</b><br>' . getEscapeQuoteStr($x['description']);
            if ($x['min_level'] > 1) {
              echo '<br><i>Level ' . $x['min_level'];
              if ($x['artifact_required'] > 0) {
                $a_required = getArtifact($x['artifact_required']);
                echo ', ' . $a_required['name'];
              }
              echo ' required.</i>';
            }
            echo '\')" onmouseout="popout()"' .
                 '>' . $x['name'] . '</a>';
            echo '</td></tr>';
          }
        }
      }
      foreach ($zone_children as $x) {
        if (($x['dev'] > 0) && (sg_debug == 0)) {
          continue;
        }
        if (!in_array($x['zone_type'], $special_zone_types)) {
          if (!canVisitZone($char_obj, $x)) {
            echo '<tr><td width="100" align="right"><span class="nav_type">';
            if ($last_ui != -1) {
              echo 'Inaccessible ';
              $last_ui = -1;
            }
            echo '&nbsp;</span></td><td>';
            echo '<span class="nav_type">' . $x['name'] .
                 ' <font size="-2">(level ' . $x['min_level'];
            if ($x['artifact_required'] > 0) {
              $a_required = getArtifact($x['artifact_required']);
              echo ', ' . $a_required['name'];
            }
            echo ' required)</font></span>';
            echo '</td></tr>';
          }
        }
      }

    echo '</table></center>';

    }
    if ($travel_places > 0) { ?>
    <p><span class="section_header">Combat and Travel</span></p>
    <center><table border="0" class="nav_table"><?
      $last_ui = -1;
      foreach ($zone_children as $x) {
        if (($x['dev'] > 0) && (sg_debug == 0)) {
          continue;
        }
        if (in_array($x['zone_type'], $special_zone_types)) {
          if (canVisitZone($char_obj, $x)) {
            if ($x['min_level'] <= 3) {
              $icon = 'green';
            } elseif ($x['min_level'] <= $char_obj->c['level'] - 2) {
              $icon = 'green';
            } elseif ($x['min_level'] == $char_obj->c['level'] - 1) {
              $icon = 'yellow';
            } elseif ($x['min_level'] == $char_obj->c['level']) {
              $icon = 'orange';
            } else {
              $icon = 'blue';
            }
            $icon = '&nbsp;<img src="images/' . $icon . '-icon.jpg" ' .
                  'width="8" height="8">&nbsp;';

            echo '<tr><td width="100" align="right"><span class="nav_type">';
            if (floor($x['ui_order'] / 10) != $last_ui) {
              $last_ui = floor($x['ui_order'] / 10);
              switch ($x['ui_order']) {
              case 10: case 11:
                echo 'Travel '; break;
              case 20: case 21: case 22: case 23: case 24:
                echo 'Combat '; break;
              case 30: echo 'Fishing '; break;
              case 40: echo 'Mining '; break;
              case 50: echo 'Dungeon '; break;
              }
            }
            echo '&nbsp;</span></td><td>';
            echo '<a href="main.php?z=' . $x['id'] . '" ' .
                 'onmouseover="popup(\'<b>' . getEscapeQuoteStr($x['name']) .
                 '</b><br>' . getEscapeQuoteStr($x['description']);
            if ($x['min_level'] > 1) {
              echo '<br><i>Level ' . $x['min_level'];
              if ($x['artifact_required'] > 0) {
                $a_required = getArtifact($x['artifact_required']);
                echo ', ' . $a_required['name'];
              }
              echo ' required.</i>';
            }
            echo '\')" onmouseout="popout()"' .
                 '>' . $x['name'] . '</a>';
            echo $icon . '</td></tr>';
          }
        }
      }
      foreach ($zone_children as $x) {
        if (($x['dev'] > 0) && (sg_debug == 0)) {
          continue;
        }
        if (in_array($x['zone_type'], $special_zone_types)) {
          if (!canVisitZone($char_obj, $x)) {
            echo '<tr><td width="100" align="right"><span class="nav_type">';
            if ($last_ui != -1) {
              echo 'Inaccessible ';
              $last_ui = -1;
            }
            echo '&nbsp;</span></td><td>';
            echo '<span class="nav_type">' . $x['name'] .
                 ' <font size="-2">(level ' . $x['min_level'];
            if ($x['artifact_required'] > 0) {
              $a_required = getArtifact($x['artifact_required']);
              echo ', ' . $a_required['name'];
            }
            echo ' required)</font></span>';
            echo '</td></tr>';
          }
        }
      }

    echo '</table></center>';

    }

  }

require '_footer.php';
$save = $char_obj->save();
$log_save = $log_obj->save();

$debug_time_diff = debugTime() - $debug_time_start;
debugPrint('<font size="-2">Page rendered in ' .
    number_format($debug_time_diff, 2, ".", ".") . 's</font>');

// hacks coming..
// getArtifactQuantity()
// getBit()
// sendMail()
// hasArtifact()
// getSkillList()
// addBuff()
// deleteAllSkills()
// deleteAllRunes()
// wipeQuestData()
// addBankArtifacts()
// setCharArtifacts()
// deleteAllBuffs()
// getCharWarfareArtifacts()
// getWarfareGame()
// updateBankDepositObjs()
// addBankArtifacts()
// setCharArtifacts()
// getResourceAssocById()
// getPostInt()
// getPostStr()
// deleteBuff()
// awardAchievement()
// clearSelect()
// addPlot()
// getAllZonePlots()
// addTrackingData()

//CODE
//);

?>

</div>
</body>
</html>