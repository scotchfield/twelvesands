<?

require_once 'include/core.php';

require_once sg_base_path . 'include/achieve.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/plots.php';
require_once sg_base_path . 'include/runes.php';
require_once sg_base_path . 'include/skills.php';

function giveBuffFromItemUse($c_obj, $buff_id, $buff_seconds, $cte_add) {
  $buff_expires = time() + $buff_seconds;
  if ($cte_add > 0) {
    $cte_add += $c_obj->c['total_combats'];
  }
  if (isset($c_obj->c['buffs'][$buff_id])) {
    $buff = updateBuff($c_obj, $buff_id, $buff_seconds, $cte_add, TRUE);
  } else {
    deleteBuff($c_obj, $buff_id);
    $buff = addBuff($c_obj, $buff_id, $buff_seconds, $cte_add, TRUE);

    applyBuff($c_obj, $buff, 'modifier_type', 'modifier_value');
    applyBuff($c_obj, $buff, 'modifier_type_2', 'modifier_value_2');
    $c_obj->calculateHpValues();

    $buff['expires'] = $buff_expires;
    $c_obj->c['buffs'][$buff['id']] = $buff;
  }
  $st = '';
  if ($buff['invisible'] == 0) {
    $st = '<p>You are affected by <b>' . $buff['name'] . '</b>.</p>';
  }
  return $st;
}

// if i try to eat 100 of a food and only the first 5 should reduce
// my fatigue, then this function should return 5 when n = 100.
function getNumFatigueReducers($c_obj, $n, $fatigue_reduction_each) {
  $fat = $c_obj->c['max_fatigue_reduction'] - $c_obj->c['fatigue_reduction'];
  $num = ceil($fat / $fatigue_reduction_each);
  if ($num >= $n) { return $n; }
  if ($num > 0) { return $num; }
  return 0;
}

function giveFatigueReductionFromItemUse($c_obj, $fatigue_reduction_added,
                                         $fatigue_restored) {
  $st = '';
  if ($c_obj->c['fatigue_reduction'] < $c_obj->c['max_fatigue_reduction']) {
    if (isset($c_obj->c['buffs'][95])) {
      $fatigue_restored = $fatigue_restored * 2;
      $st = $st . '<p>The pepper adds a hot spice to the food, and ' .
          'you feel relaxed!</p>';
      deleteBuff($c_obj, 95);
    }

    if (isset($c_obj->c['buffs'][106])) {
      $fatigue_restored = $fatigue_restored * 1.25;
      $st = $st . '<p>The sprinkles add flavour to the food, and ' .
          'you feel relaxed!</p>';
      deleteBuff($c_obj, 106);
    }

    $f_add = applyMultiplier($fatigue_reduction_added,
        -$c_obj->c['food_reduction']);
    $fatigue_reduction = $c_obj->c['fatigue_reduction'] + $f_add;
    $fatigue = max($c_obj->c['fatigue'] - $fatigue_restored, 0);
    $fatigue_diff = round(($c_obj->c['fatigue'] - $fatigue) / 1000);
    $st = $st . '<p><span class="mod_highlight">You feel rested.</span>' .
         '<br><font size="-2">(' . $fatigue_diff . '% fatigue ' .
         'restored)</font></p>';
    if (($c_obj->c['rested_eating_bonus'] > 0) && ($fatigue_restored > 0)) {
      $fatigue_rest =
          floor($fatigue_restored / 100) * $c_obj->c['rested_eating_bonus'];
      $c_obj->setFatigueRested(
          min($c_obj->c['fatigue_rested'] + $fatigue_rest, 50000));
    }
    $c_obj->setFatigue($fatigue);
    $c_obj->setFatigueReduction($fatigue_reduction);
  }
  return $st;
}

function giveHpFromItemUse($c_obj, $hp_healed) {
  $hp_injured = $c_obj->c['base_hp'] - $c_obj->c['current_hp'];
  $hp_bonus = min($hp_healed, $hp_injured);
  $st = '';
  if ($hp_bonus > 0) {
    $st = '<p><img src="images/recv_health.gif"> ' .
          '<span class="mod_highlight">You recover ' . $hp_bonus .
          ' health.</span></p>';
    $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_bonus);
  }
  return $st;
}

function giveXpFromItemUse($c_obj, $xp_awarded) {
  $add_xp = $c_obj->addXp($xp_awarded);
  $st = '<p>' . $add_xp . ' experience point';
  if ($add_xp > 1) { $st = $st . 's'; }
  $st = $st . ' awarded.</p>';

  $level_check = levelCheck($c_obj);
  if ($level_check != FALSE) {
    $st = $st . $level_check;
  }

  return $st;
}

function useRuneAsArtifact($c_obj, $rune_obj, &$ret_obj) {
  if (isset($c_obj->c['runes'][$rune_obj['id']])) {
    $ret_obj[] = '<p>You have a rune of this type alread inscribed on ' .
        'your body!  Only a single instance of each rune can be placed ' .
        'on your person at a time.</p>';
  } elseif ($rune_obj['min_level'] > $c_obj->c['level']) {
    $ret_obj[] = '<p>Your level is not high enough to inscribe this rune!</p>';
  } elseif (count($c_obj->c['runes']) >= 5) {
    $ret_obj[] = '<p>You have already reached your rune limit!  In ' .
        'order to inscribe this new rune, you will need to discard ' .
        'an existing one.</p>';
  } else {
    addRune($c_obj, $rune_obj['id']);
    $ret_obj[] = '<p>You study the artifact, and after a time, you ' .
        'cautiously close your eyes, and inscribe the rune onto your ' .
        'body!</p><p class="tip">You have gained a rune: ' .
        renderArtifactStr($rune_obj) . '</p>';
    return FALSE;
  }
  return TRUE;
}

function usePlotArtifact($c_obj, $artifact_id, $plot_flag, $plot_bit, &$ret_obj, $valid) {
  $keep_artifact = FALSE;
  $plot_id = getGetInt('plot', 0);
  $plot_obj = getAllPlots($c_obj->c['id']);
  if (count($plot_obj) == 0) {
    $ret_obj[] = '<p>You don\'t own any plots!</p>';
    $keep_artifact = TRUE;
  } elseif (!array_key_exists($plot_id, $plot_obj)) {
    $ret_obj[] = '<form action="inventory.php" method="get">' .
        '<p>Which plot would you like to install ' .
        'this at?</p><select name="plot">';
    foreach ($plot_obj as $plot) {
      $ret_obj[] = '<option value="' . $plot['id'] . '">' .
          $plot['title'] . '</option>';
    }
    $ret_obj[] = '</select> <input type="submit" value="Submit">' .
        '<input type="hidden" name="a" value="u">' .
        '<input type="hidden" name="i" value="' . $artifact_id . '">' .
        '<input type="hidden" name="n" value="1"></form>';
    $keep_artifact = TRUE;
  } else{
    $plot = getPlot($plot_id);
    if (getPlotFlagBit($plot, $plot_flag, $plot_bit)) {
      $ret_obj[] = '<p>That plot already has one!</p>';
      $keep_artifact = TRUE;
    } else {
      if (!$keep_artifact) {
        $ret_obj[] = $valid;
        setPlotFlag($plot['id'], $plot_flag,
            getPlotFlagValue($plot, $plot_flag) | (1 << $plot_bit));
      }
    }
  }
  return $keep_artifact;
}

function useArtifact($c_obj, $artifact_id, $n, $log_obj) {
  $c = $c_obj->c;
  $ret_obj = array();

  $artifact = hasArtifact($c_obj, $artifact_id);

  if (FALSE == $artifact) {
    $ret_obj[] = '<p>You don\'t have that many!</p>';
  } elseif ($n > $artifact['quantity']) {
    $ret_obj[] = '<p>You don\'t have that many!</p>';
  } elseif ($c['level'] < $artifact['min_level']) {
    $ret_obj[] = '<p>Your level isn\'t high enough to use that artifact!</p>';
  } elseif ($n < 1) {
    $ret_obj[] = '<p>Come on..</p>';
  } else {

    $keep_artifact = FALSE;

    if ($artifact['use_multiple'] == 0) {
      $n = 1;
    }

    switch($artifact_id) {
    case 40: // weak potion of healing
      $ret_obj[] = giveHpFromItemUse($c_obj, 3 * $n);
      break;
    case 41: // miniature pouch of gold
      $gold_award = rand(8 * $n, 25 * $n);
      $ret_obj[] = '<p>You open the pouch and find some gold coins.</p>';
      $ret_obj[] = awardArtifactString($c_obj, 0, $gold_award);
      break;
    case 42: // tiny pouch of gold
      $gold_award = rand(25 * $n, 50 * $n);
      $ret_obj[] = '<p>You open the pouch and find some gold coins.</p>';
      $ret_obj[] = awardArtifactString($c_obj, 0, $gold_award);
      break;
    case 43: // small cloth pouch
      $gold_award = rand(75 * $n, 135 * $n);
      $ret_obj[] = '<p>You open the pouch and find some gold coins.</p>';
      $ret_obj[] = awardArtifactString($c_obj, 0, $gold_award);
      break;
    case 50: // lesser tonic of bravery
      $ret_obj[] = '<p>You drink the tonic.  Yum!</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 2, 1259, 0);
      break;
    case 51: // bear's blood
      $ret_obj[] = '<p>You ingest the blood. ';
      $ret_obj[] = 'It tastes awful, but you feel stronger.</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 3, 1259, 0);
      break;
    case 52: // lesser oxy-tonic
    case 53: // lesser hydro-tonic
      $ret_obj[] = '<p>You drink the tonic.</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 4, 1259, 0);
      break;
    case 54: // elemental oxyale
      $ret_obj[] = '<p>You drink the mystical tonic.</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 5, 21659, 0);
      break;
    case 107: // Tonic of Slight Durability
      $ret_obj[] = '<p>You consume the tonic.  It has a bitter taste, ' .
          'but you immediately feel more energetic.</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 9, 659, 0);
      break;
    case 108: // Tonic of Lesser Durability
      $ret_obj[] = '<p>You consume the tonic.  It has a bitter taste, ' .
          'but you immediately feel more energetic.</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 9, 1559, 0);
      break;
    case 177: // Arcane Transfer Stone
      if (array_key_exists(19, $c['buffs'])) {
        $ret_obj[] = '<p>You recoil as the arcane energies that course ' .
            'through you are drained in to the stone!</p>';
        $new_artifact = getArtifact(178);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
        deleteBuff($c_obj, 19);
        if ($n > 1) {
          $ret_obj[] = '<p>You find yourself unable to use the rest of ' .
              'the stones right away.</p>';
          $n = 1;
        }
      } else {
        $ret_obj[] = '<p>You look at the stone, but nothing happens.  If ' .
            'you knew how to get arcane energy to flow in to the stone, it ' .
            'might be more helpful!</p>';
        $keep_artifact = TRUE;
      }
      break;
    case 182: // Restorative Mana Water
      $mana = min(200, $c['mana_max'] - $c['mana']);
      $ret_obj[] = '<p>You consume the cool and refreshing liquid.</p><p>' .
          '<img src="images/recv_mana.gif"> You recover ' . $mana .
          ' mana.</p>';
      $c_obj->setMana($c['mana'] + $mana);
      break;
    case 191: // Minor Poison Vial
      $ret_obj[] = '<p>You open the vial.  It begins to evaporate, but ' .
          'you start applying it to your weapon immediately.</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 21, 659, 0);
      break;
    case 207: // Tiny Spore Flask
      if (array_key_exists(22, $c['buffs'])) {
        $ret_obj[] = '<p>You brush off some of the toxic spores that ' .
            'cover you, and knock them in to the flask.</p>';
        $new_artifact = getArtifact(210);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
        if ($n > 1) { $n = 1; }
      } else {
        $ret_obj[] = '<p>You look at the flask, and realize you have no ' .
            'spores.  Perhaps you should hunt around and figure out how ' .
            'to acquire some from the Vile Swamplands?</p>';
        $keep_artifact = TRUE;
      }
      break;
    case 208: // Medium Spore Flask
      if (array_key_exists(23, $c['buffs'])) {
        $ret_obj[] = '<p>You brush off some of the toxic spores that ' .
            'cover you, and knock them in to the flask.</p>';
        $new_artifact = getArtifact(211);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
        if ($n > 1) { $n = 1; }
      } else {
        $ret_obj[] = '<p>You look at the flask, and realize you have no ' .
            'spores.  Perhaps you should hunt around and figure out how ' .
            'to acquire some from the Vile Swamplands?</p>';
        $keep_artifact = TRUE;
      }
      break;
    case 209: // Large Spore Flask
      if (array_key_exists(24, $c['buffs'])) {
        $ret_obj[] = '<p>You brush off some of the toxic spores that ' .
            'cover you, and knock them in to the flask.</p>';
        $new_artifact = getArtifact(212);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
        if ($n > 1) { $n = 1; }
      } else {
        $ret_obj[] = '<p>You look at the flask, and realize you have no ' .
            'spores.  Perhaps you should hunt around and figure out how ' .
            'to acquire some from the Vile Swamplands?</p>';
        $keep_artifact = TRUE;
      }
      break;
    case 304: // Unopened Clam Shell
      $n = 1;
      $clam = rand(1, 10);
      $ret_obj[] = '<p>You crack open the shell and remove the meat.</p>';
      if ($clam == 10) {
        $new_artifact = getArtifact(303);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      } elseif ($clam > 7) {
        $new_artifact = getArtifact(302);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      } else {
        $new_artifact = getArtifact(301);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      }
      break;
    case 352: // Sandstorm Wizard Deck Booster Pack
      $n = 1;
      $base_card_id = 319; // 319 + 1 = card 01, etc.
      $ultra_rare_roll = rand(1, 10);
      $ret_obj[] = '<p>You tear open the booster pack, and find some ' .
          'cards!</p>';
      $cards = array();
      $cards[] = $base_card_id + rand(1, 20);
      $cards[] = $base_card_id + rand(1, 20);
      $cards[] = $base_card_id + rand(1, 24);
      if ($ultra_rare_roll != 10) {
        $cards[] = $base_card_id + rand(8, 24);
      }
      $cards[] = $base_card_id + rand(21, 30);
      if ($ultra_rare_roll == 10) {
        $cards[] = $base_card_id + rand(31, 32);
      }
      foreach ($cards as $card) {
        $new_artifact = getArtifact($card);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      }
      break;
    case 353: // Iron Hand Map Folder
      $ret_obj[] = '<p>You open the folder and take out the maps.</p>';
      $new_artifact = getArtifact(354);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      $new_artifact = getArtifact(355);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      $new_artifact = getArtifact(356);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      break;
    case 358: // Sandstorm Wizard Deck Sample Pack
      $n = 1;
      $base_card_id = 319; // 319 + 1 = card 01, etc.
      $ret_obj[] = '<p>You tear open the booster pack, and find some ' .
          'cards!</p>';
      $cards = array();
      $cards[] = $base_card_id + rand(1, 5);
      $cards[] = $base_card_id + rand(4, 9);
      $cards[] = $base_card_id + rand(8, 12);
      foreach ($cards as $card) {
        $new_artifact = getArtifact($card);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      }
      break;
    case 400: // Tastee Wheat
      $ret_obj[] = '<p>Actually, it tastes like a single cell protein ' .
          'combined with synthetic aminos, vitamins, and minerals.  ' .
          'Everything the body needs.</p>';
      $c_obj->setFatigue(0);
      $c_obj->setFatigueReduction(0);
      break;
    case 435: // Tonic of Dexterity
      $ret_obj[] = '<p>You drink the green liquid, and feel more agile!</p>';
      giveBuffFromItemUse($c_obj, 75, 3659, 3);
      break;
    case 447: // Vak's Tonic of Life
      $ret_obj[] = '<p>You consume the tonic.</p>';
      $ret_obj[] = giveHpFromItemUse($c_obj, 100);
      giveBuffFromItemUse($c_obj, 78, 3659, 3);
      break;
    case 448: // Tonic of Strength
      $ret_obj[] = '<p>You drink the green liquid, and feel stronger!</p>';
      giveBuffFromItemUse($c_obj, 79, 3659, 3);
      break;
    case 449: // Tonic of Intelligence
      $ret_obj[] = '<p>You drink the green liquid, and feel wiser!</p>';
      giveBuffFromItemUse($c_obj, 80, 3659, 3);
      break;
    case 450: // Tonic of Charisma
      $ret_obj[] = '<p>You drink the green liquid, and feel more ' .
          'confident!</p>';
      giveBuffFromItemUse($c_obj, 81, 3659, 3);
      break;
    case 451: // Tonic of Constitution
      $ret_obj[] = '<p>You drink the green liquid, and feel purified!</p>';
      giveBuffFromItemUse($c_obj, 82, 3659, 3);
      break;
    case 495: // Tiny Mystic Flask
      $ret_obj[] = '<p>You consume the contents of the tiny flask, and ' .
          'feel a surge of mystic energy rush through you!</p>';
      $c_obj->setMana($c['mana_max']);
      break;
    case 586: // Vial of Arcane Elixir
      $mana = min(20, $c['mana_max'] - $c['mana']);
      $ret_obj[] = '<p>You consume the cool and refreshing liquid.</p><p>' .
          '<img src="images/recv_mana.gif"> You recover ' . $mana .
          ' mana.</p>';
      $c_obj->setMana($c['mana'] + $mana);
      break;
    case 680: // Enchanting Textbook
      if (getFlagValue($c_obj, sg_flag_enchanting) > 0) {
        $keep_artifact = TRUE;
        $ret_obj[] = '<p>You already know how to enchant artifacts, and ' .
            'decide not to read the textbook.</p>';
      } else {
        $c_obj->addFlag(sg_flag_enchanting, 1);
        $ret_obj[] = '<p>You study the textbook methodically, working your ' .
            'way through each chapter carefully.  As you read, you begin ' .
            'to understand how artifacts gain their enhancements, and how ' .
            'they can be taken away.  After you complete your study, the ' .
            'textbook erupts in a burst of arcane energy.</p>' .
            '<p class="tip">You have learned the Enchanting skill!</p>';
      }
      break;
    case 685: // Impish Tonic of Joviality
      $ret_obj[] = '<p>You drink the strange tonic..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 87, 3659, 5);
      break;
    case 686: // Impish Tonic of Endurance
      $ret_obj[] = '<p>You drink the strange tonic..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 88, 3659, 5);
      break;
    case 687: // Impish Tonic of Rage
      $ret_obj[] = '<p>You drink the strange tonic..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 89, 3659, 5);
      break;
    case 688: // Impish Tonic of Toughness
      $ret_obj[] = '<p>You drink the strange tonic..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 90, 3659, 5);
      break;
    case 689: // Impish Tonic of Coordination
      $ret_obj[] = '<p>You drink the strange tonic..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 91, 3659, 5);
      break;
    case 730: // Holiday Parcel
      $ret_obj[] = '<p>You open the holiday parcel!</p>';
      $new_artifact = getArtifact(729);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      $new_artifact = getArtifact(732);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      $new_artifact = getArtifact(731);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 3);
      break;
    case 735: // Black Peppercorn
      $ret_obj[] = '<p>You eat the spicy black peppercorns, and hunt ' .
          'around for something else to consume!</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 95, 3659, 0);
      break;
    case 741: // Silver Tracking Wolf Deed
      if ($_SESSION['tracking'][sg_track_use][741] > 0) {
        $ret_obj[] = '<p>You head off to the Capital City Treasury, and ' .
            'present your deed to the staff.  They look at you curiously, ' .
            'and shrug.</p><p class="tip">' .
            'You have already used a Silver Tracking Wolf ' .
            'Deed!  Since you can only use one mount at a time, and this ' .
            'mount can not be traded, this might not be what you intended. ' .
            'If you do want to use it, please send an in-game message to ' .
            '<a href="char.php?i=1">swrittenb</a>, who can take care of ' .
            'things for you.</p>';
        $keep_artifact = TRUE;
      } else {
        $ret_obj[] = '<p>You head off to the Capital City Treasury, and ' .
            'present your deed to the staff.  They smile brightly, head ' .
            'into an open field at the rear of the building, and return ' .
            'with a deadly looking wolf.  After some brief lessons, you ' .
            'are handed the reins to your new mount!</p>';
        $new_artifact = getArtifact(742);
        $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      }
      break;
    case 743: // Spiced Cinnamon Candy
      $ret_obj[] = '<p>You pop the candy in your mouth, and wince at the ' .
          'spice.</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 97, 3659, 10);
      break;
    case 744: // Linen Bandage
      $ret_obj[] = '<p>You wrap the bandage around your wounds, and begin ' .
          'to feel better.</p>';
      $ret_obj[] = giveHpFromItemUse($c_obj, 50);
      break;
    case 769: // Bundle of Algas Flora
      $ret_obj[] = '<p>You open the pack of goods.</p>';
      $new_artifact = getArtifact(762);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      $new_artifact = getArtifact(763);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      $new_artifact = getArtifact(764);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      break;
    case 784: // Azure Seasoning
      $ret_obj[] = '<p>You eat the seasoning, and hunt ' .
          'around for something else to consume!</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 104, 3659, 10);
      break;
    case 785: // Cerulean Seasoning
      $ret_obj[] = '<p>You eat the seasoning, and hunt ' .
          'around for something else to consume!</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 105, 3659, 10);
      break;
    case 786: // Chocolate Sprinkles
      $ret_obj[] = '<p>You eat the delicious sprinkles, and hunt ' .
          'around for something else to consume!</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 106, 3659, 0);
      break;
    case 798: // Aloe Salve
      $ret_obj[] = '<p>You apply the soothing salve..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 108, 3659, 25);
      break;
    case 799: // Enraging Potion
      $ret_obj[] = '<p>You consume the small potion..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 109, 3659, 25);
      break;
    case 800: // Magic Resistance Potion
      $ret_obj[] = '<p>You consume the small potion..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 110, 3659, 25);
      break;
    case 801: // Potion of Piercing Vision
      $ret_obj[] = '<p>You consume the small potion..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 111, 3659, 25);
      break;
    case 802: // Sickening Slime Coating
      $ret_obj[] = '<p>You apply the disgusting slime over your body, and ' .
          'gag at the smell.  Yuk!</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 112, 3659, 25);
      break;
    case 803: // Elixir of Golden Sight
      $ret_obj[] = '<p>You consume the small potion..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 113, 3659, 25);
      break;
    case 804: // Elixir of Identification
      $ret_obj[] = '<p>You consume the small potion..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 114, 3659, 25);
      break;
    case 807: // Glowing Arcane Salve
      $ret_obj[] = '<p>You apply the soothing salve..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 115, 3659, 25);
      break;
    case 808: // Bikke's Bitter Ale
      $ret_obj[] = '<p>You crack open the large bottle of brew, and slug ' .
          'it back!  Almost immediately, your head begins to swim..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 116, 3659, 50);
      break;
    case 809: // Brawler's Barley Wine
      $ret_obj[] = '<p>You crack open the large bottle of brew, and slug ' .
          'it back!  Almost immediately, your head begins to swim..</p>';
      $ret_obj[] = giveBuffFromItemUse($c_obj, 116, 3659, 50);
      break;
    case 810: // Blue Crab Cake Recipe
      if (getFlagBit($c_obj, sg_flag_recipes, 0)) {
        $keep_artifact = TRUE;
        $ret_obj[] = '<p>You already know how to make this, and ' .
            'decide not to use the recipe.</p>';
      } else {
        $c_obj->enableFlagBit(sg_flag_recipes, 0);
        $ret_obj[] = '<p>You study the recipe methodically, and carefully ' .
            'work through the steps in your mind.</p>' .
            '<p class="tip">You have learned how to cook Blue Crab Cakes!</p>';
      }
      break;
    case 814:
      break;
    case 815:
      $ally_add = addUserAllyFromId($c_obj->c['user_id'], 3);
      if ($ally_add == FALSE) {
        $keep_artifact = TRUE;
        $ret_obj[] = '<p>You have already contracted the services of this ' .
            'ally, and don\'t need to use another one of these.</p>';
      } else {
        $ret_obj[] = '<p>You head back over to Allied Contracting to ' .
            'present your contract.  After a brief discussion, you shake ' .
            'hands, and meet your new ally!</p>';
        $ret_obj[] = '<p class="tip"><b>Andor, the Capital Warrior</b> is ' .
            'now available!<br><font size="-2">(<a href="char.php?a=al">' .
            'View your allies</a>)</font></p>';
      }
      break;
    case 816:
      $ally_add = addUserAllyFromId($c_obj->c['user_id'], 4);
      if ($ally_add == FALSE) {
        $keep_artifact = TRUE;
        $ret_obj[] = '<p>You have already contracted the services of this ' .
            'ally, and don\'t need to use another one of these.</p>';
      } else {
        $ret_obj[] = '<p>You head back over to Allied Contracting to ' .
            'present your contract.  After a brief discussion, you shake ' .
            'hands, and meet your new ally!</p>';
        $ret_obj[] = '<p class="tip"><b>Lujza, the Capital Medic</b> is ' .
            'now available!<br><font size="-2">(<a href="char.php?a=al">' .
            'View your allies</a>)</font></p>';
      }
      break;
    case 817:
      $ally_add = addUserAllyFromId($c_obj->c['user_id'], 5);
      if ($ally_add == FALSE) {
        $keep_artifact = TRUE;
        $ret_obj[] = '<p>You have already contracted the services of this ' .
            'ally, and don\'t need to use another one of these.</p>';
      } else {
        $ret_obj[] = '<p>You head back over to Allied Contracting to ' .
            'present your contract.  After a brief discussion, you shake ' .
            'hands, and meet your new ally!</p>';
        $ret_obj[] = '<p class="tip"><b>Zolt&aacute;n, the Capital ' .
            'Arcanist</b> is ' .
            'now available!<br><font size="-2">(<a href="char.php?a=al">' .
            'View your allies</a>)</font></p>';
      }
      break;
    case 818:
      $ally_add = addUserAllyFromId($c_obj->c['user_id'], 6);
      if ($ally_add == FALSE) {
        $keep_artifact = TRUE;
        $ret_obj[] = '<p>You have already contracted the services of this ' .
            'ally, and don\'t need to use another one of these.</p>';
      } else {
        $ret_obj[] = '<p>You read the terms of the contract aloud.  Once ' .
            'you are complete, a thunderous crack erupts from the ground ' .
            'in front of you.  Your ally stands in front of you, now bound ' .
            'to serve.</p>';
        $ret_obj[] = '<p class="tip"><b>Juiblax, the Grey Oozeling</b> is ' .
            'now available!<br><font size="-2">(<a href="char.php?a=al">' .
            'View your allies</a>)</font></p>';
      }
      break;
    case 819:
      $ally_add = addUserAllyFromId($c_obj->c['user_id'], 7);
      if ($ally_add == FALSE) {
        $keep_artifact = TRUE;
        $ret_obj[] = '<p>You have already contracted the services of this ' .
            'ally, and don\'t need to use another one of these.</p>';
      } else {
        $ret_obj[] = '<p>You read the terms of the contract aloud.  Once ' .
            'you are complete, a thunderous crack erupts from the ground ' .
            'in front of you.  Your ally stands in front of you, now bound ' .
            'to serve.</p>';
        $ret_obj[] = '<p class="tip"><b>Wagpex, the Kobold Warrior</b> is ' .
            'now available!<br><font size="-2">(<a href="char.php?a=al">' .
            'View your allies</a>)</font></p>';
      }
      break;
    case 823:
      $keep_artifact = TRUE;
      break;
    case 831:
      $ally_add = addUserAllyFromId($c_obj->c['user_id'], 8);
      if ($ally_add == FALSE) {
        $keep_artifact = TRUE;
        $ret_obj[] = '<p>You have already contracted the services of this ' .
            'ally, and don\'t need to use another one of these.</p>';
      } else {
        $ret_obj[] = '<p>You read the terms of the contract aloud.  Once ' .
            'you are complete, a thunderous crack erupts from the ground ' .
            'in front of you.  Your ally stands in front of you, now bound ' .
            'to serve.</p>';
        $ret_obj[] = '<p class="tip"><b>TOR-60, Automaton Sentry</b> is ' .
            'now available!<br><font size="-2">(<a href="char.php?a=al">' .
            'View your allies</a>)</font></p>';
      }
      break;
    case 834: // Holiday Package
      $ret_obj[] = '<p>You open the holiday package!</p>';
      $new_artifact = getArtifact(835);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      $new_artifact = getArtifact(732);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      $new_artifact = getArtifact(731);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 3);
      $new_artifact = getArtifact(352);
      $ret_obj[] = awardArtifactString($c_obj, $new_artifact, 1);
      break;
    case 847:
      $user = getUserById($_SESSION['u']);
      if ($user['max_chars'] > 3) {
        $ret_obj[] = '<p>You have already increased the number of ' .
            'characters on this account - thank you!  If you think this ' .
            'is a mistake, please contact ' .
            '<a href="char.php?i=1">swrittenb</a>, and we\'ll get things ' .
            'sorted out.</p>';
        $keep_artifact = TRUE;
      } else {
        setUserMaxChars($_SESSION['u'], 10);
        $ret_obj[] = '<p>You carefully read the tome, and feel your mind ' .
            'expanding!  Wow, it\'s like there are, like, ten people ' .
            'in there, trying to get out!</p><p class="tip">Your user ' .
            'account now supports up to ten characters.  Thanks for your ' .
            'support!</p>';
      }
      break;
    case 866:
      $valid = '<p>You plant the seed in front of your plot, and watch ' .
          'as it grows into a great and strong tree!</p>';
      $keep_artifact = usePlotArtifact(
          $c_obj, $artifact_id, sg_plotflag_installed, 0, $ret_obj, $valid);
      break;
    case 867:
      $valid = '<p>You plant the seed in front of your plot, and watch ' .
          'as it grows into a great and strong tree!</p>';
      $keep_artifact = usePlotArtifact(
          $c_obj, $artifact_id, sg_plotflag_installed, 1, $ret_obj, $valid);
      break;
    case 871:
      $valid = '<p>You find a nice open area in front of your plot, and ' .
          'set the book down for visitors to find.</p>';
      $keep_artifact = usePlotArtifact(
          $c_obj, $artifact_id, sg_plotflag_installed, 2, $ret_obj, $valid);
      break;

    default:
      $keep_artifact = TRUE;
      //$ret_obj[] = '<p>Ruh?</p>';
      break;
    }

    if ($artifact['type'] == sg_artifact_rune) {
      $rune_obj = getRune($artifact['ra']);
      $keep_artifact = useRuneAsArtifact($c_obj, $rune_obj, $ret_obj);
    }

    if ($keep_artifact == FALSE) {
      removeArtifact($c_obj, $artifact_id, $n);
      addTrackingData($c_obj, $artifact_id, sg_track_use, $n);
      $achieve_obj = checkAchievementUse($c_obj, $artifact_id);
      foreach ($achieve_obj as $achieve) {
        $ret_obj[] = $achieve;
      }

      $ret_obj[] = '<font size="-2">You have used your ' .
          getIntWithSuffix(
              $_SESSION['tracking'][sg_track_use][$artifact_id]) .
          ' ' . $artifact['name'] . '.</font>';

    }

    $log_obj->addLog($c, sg_log_use_item, $artifact_id, $n, 0, 0);
  }

  return $ret_obj;
}

function getEatResultArray($b, $bd, $bt, $hp, $m, $f, $hun) {
  return array(
      'b' => $b, 'bd' => $bd, 'bt' => $bt, // buff id, duration, turns
      'hp' => $hp, 'm' => $m,            // base hp award, mana award
      'f' => $f, 'hun' => $hun);         // base fatigue reduction, hunger use
}

function eatArtifact($c_obj, $artifact_id, $n, $log_obj) {
  $ret_obj = array();

  $artifact = hasArtifact($c_obj, $artifact_id);
  $n = 1;

  if (FALSE == $artifact) {
    $ret_obj[] = '<p>You don\'t have that many!</p>';
  } elseif ($n > $artifact['quantity']) {
    $ret_obj[] = '<p>You don\'t have that many!</p>';
  } elseif ($c_obj->c['level'] < $artifact['min_level']) {
    $ret_obj[] = '<p>Your level isn\'t high enough to use that artifact!</p>';
  } elseif ($n < 1) {
    $ret_obj[] = '<p>Come on..</p>';
  } else {

    $keep_artifact = FALSE;

    if ($artifact['use_multiple'] == 0) {
      $n = 1;
    }

    $result = getEatResultArray(0, 0, 0, 0, 0, 0, 0);

    switch($artifact_id) {
    case 44: // fresh slice of bread
      $ret_obj[] = '<p>You eat the fresh bread.  Yum!</p>';
      $result = getEatResultArray(1, 659, 0, 5, 0, 20, 5000);
      break;
    case 45: // fresh loaf of bread
      $ret_obj[] = '<p>You eat the fresh bread.  Yum!</p>';
      $result = getEatResultArray(1, 659, 0, 10, 0, 60, 15000);
      break;
    case 66: // pale mudsnapper
      $ret_obj[] = '<p>You choke down the raw fish.</p>';
      $result = getEatResultArray(0, 0, 0, 2, 0, 0, 0);
      $ret_obj[] = giveHpFromItemUse($c_obj, 2 * $n);
      break;
    case 67: // yellow slimescale
      $ret_obj[] = '<p>You choke down the disgusting and raw fish.</p>';
      break;
    case 74: // raw bat wing
      $ret_obj[] = '<p>You choke down the raw wing.</p>';
      $result = getEatResultArray(0, 0, 0, 2, 0, 0, 0);
      break;
    case 83: // salted mudsnapper
      $ret_obj[] = '<p>You eat the nicely salted fish.</p>';
      $result = getEatResultArray(1, 659, 0, 15, 0, 40, 12500);
      break;
    case 84: // salted slimescale
      $ret_obj[] = '<p>You eat the nicely salted and somewhat slimy fish.</p>';
      $result = getEatResultArray(1, 659, 0, 15, 0, 40, 12300);
      break;
    case 85: // salted bat wing
      $ret_obj[] = '<p>You eat the delicately salted bat wing.</p>';
      $result = getEatResultArray(1, 659, 0, 25, 0, 15, 5900);
      break;
    case 115: // Grilled Boar Meat
      $ret_obj[] = '<p>You eat the tender boar meat.</p>';
      $result = getEatResultArray(1, 959, 0, 30, 0, 20, 8000);
      break;
    case 116: // Grilled Bear Meat
      $ret_obj[] = '<p>You eat the tender bear meat.</p>';
      $result = getEatResultArray(1, 959, 0, 30, 0, 20, 8000);
      break;
    case 117: // Grilled Snake Skewer
      $ret_obj[] = '<p>You eat the delicate snake skewer.</p>';
      $result = getEatResultArray(1, 659, 0, 20, 0, 15, 6400);
      break;
    case 118: // Hearty Stew
      $ret_obj[] = '<p>You wolf down the delicious stew, and feel ' .
          'quite sated.</p>';
      $result = getEatResultArray(1, 1859, 0, 30, 0, 30, 10500);
      break;
    case 137: // Salted Smallfish
      $ret_obj[] = '<p>You eat the delicate fish.  It tastes salty, ' .
          'but great!</p>';
      $result = getEatResultArray(1, 959, 0, 25, 0, 25, 11000);
      break;
    case 138: // Salted Glowfish Mackerel
      $ret_obj[] = '<p>You eat the delicate fish.  It tastes salty, ' .
          'but great!</p>';
      $result = getEatResultArray(1, 959, 0, 25, 0, 25, 11000);
      break;
    case 194: // Buttered Hagfish
      $ret_obj[] = '<p>You eat the buttered fish.  Delicious!</p>';
      $result = getEatResultArray(1, 1259, 0, 30, 0, 25, 12700);
      break;
    case 195: // Buttered Albacore
      $ret_obj[] = '<p>You eat the buttered fish.  Delicious!</p>';
      $result = getEatResultArray(1, 1259, 0, 30, 0, 25, 13200);
      break;
    case 196: // Buttered Lamprey
      $ret_obj[] = '<p>You eat the buttered fish.  Delicious!</p>';
      $result = getEatResultArray(1, 1259, 0, 30, 0, 25, 13700);
      break;
    case 197: // Sandstorm Loaf
      $ret_obj[] = '<p>You eat the rich and dense bread.</p>';
      $result = getEatResultArray(1, 1259, 0, 50, 0, 20, 18000);
      break;
    case 198: // Iron Hand Rations
      $ret_obj[] = '<p>You eat the small pouch of food.</p>';
      $result = getEatResultArray(1, 1259, 0, 50, 0, 20, 20000);
      break;
    case 201: // Lionfish Skewer
      $ret_obj[] = '<p>You eat the buttered skewer.  Delicious!</p>';
      $result = getEatResultArray(1, 1259, 0, 40, 0, 25, 15000);
      break;
    case 202: // Terrorscale Stew
      $ret_obj[] = '<p>You eat the rich and hearty stew.</p>';
      $result = getEatResultArray(1, 1259, 0, 40, 0, 25, 15000);
      break;
    case 206: // Sweet Lasher Frond
      $ret_obj[] = '<p>You eat the small frond.</p>';
      $result = getEatResultArray(1, 659, 0, 8, 0, 15, 9000);
      break;
    case 275: // Capital City Cookie
      $ret_obj[] = '<p>You eat the delicious cookie.  It has chocolate ' .
           'chips, and is moist and delicious.  This is probably the best ' .
           'cookie you\'ve ever had.  Yay!</p>';
      $result = getEatResultArray(26, 1859, 0, 35, 0, 15, 25000);
      break;
    case 288: // Healer's Loaf
      $ret_obj[] = '<p>You eat the rich and dense bread.</p>';
      $result = getEatResultArray(0, 0, 0, 1000, 0, 10, 1000);
      break;
    case 357: // Capital City Food Pellet
      $ret_obj[] = '<p>You pop the pellet in to your mouth and crunch ' .
           'down.  As you chew, your eyes widen with surprise, and you feel ' .
           'restored.</p>';
      $result = getEatResultArray(0, 0, 0, 35, 0, 15, 30350);
      break;
    case 377: // Frosted Whitecap
      $ret_obj[] = '<p>You pop the small mushroom into your mouth.</p>';
      $result = getEatResultArray(0, 0, 0, 10, 0, 0, 0);
      break;
    case 378: // Buttered Whitecap
      $ret_obj[] = '<p>You slop up the rich mushrooms.  Delicious!</p>';
      $result = getEatResultArray(0, 0, 0, 20, 0, 15, 7000);
      break;
    case 382: // Gilled Meadow Mushroom
      $ret_obj[] = '<p>You pop the small mushroom into your mouth.</p>';
      $result = getEatResultArray(0, 0, 0, 12, 0, 0, 0);
      break;
    case 383: // Buttered Meadow Mushroom
      $ret_obj[] = '<p>You slop up the rich mushrooms.  Delicious!</p>';
      $result = getEatResultArray(0, 0, 0, 35, 0, 15, 7500);
      break;
    case 408: // Fortified Fruit Preserves
      $ret_obj[] = '<p>The fruit tastes sweet and delicious, without any ' .
          'hint of the bitter vitamin taste.  Delicious!</p>';
      $result = getEatResultArray(0, 0, 0, 0, 0, 15, 40000);
      break;
    case 424: // Fisherman's Soup
      if (array_key_exists(72, $c_obj->c['buffs'])) {
        $ret_obj[] = '<p>Your mouth is still a bit spicy from the last ' .
            'Fisherman\'s soup that you ate!</p>';
        $keep_artifact = TRUE;
      } else {
        $ret_obj[] = '<p>The rich soup has a bit of spice, and tastes ' .
            'wonderful!  You feel recharged!</p>';
        $result = getEatResultArray(72, 10800, 0, 0, 0, 25, 25000);
      }
      break;
    case 438: // Baked Cookie
      $ret_obj[] = '<p>You eat the delicious cookie.</p>';
      $result = getEatResultArray(0, 0, 0, 35, 0, 15, 17500);
      break;
    case 439: // Baked Sprinkled Cookie
      $ret_obj[] = '<p>You eat the delicious cookie.</p>';
      $result = getEatResultArray(0, 0, 0, 55, 0, 15, 22500);
      break;
    case 713: // Raw Potato
      $ret_obj[] = '<p>You choke down the raw potato.</p>';
      $result = getEatResultArray(0, 0, 0, 10, 0, 20, 5000);
      break;
    case 714: // Stale Fried Potato
      $ret_obj[] = '<p>You munch away on the stale potato.</p>';
      $result = getEatResultArray(0, 0, 0, 15, 0, 20, 5500);
      break;
    case 715: // Cheesy Potato
      $ret_obj[] = '<p>You start wolfing down the cheesy potato.</p>';
      $result = getEatResultArray(0, 0, 0, 35, 0, 20, 6000);
      break;
    case 716: // Old Preserves
      $ret_obj[] = '<p>You pinch your nose, and begin to eat the old ' .
          'container of preserves.</p>';
      $result = getEatResultArray(0, 0, 0, 35, 0, 20, 8500);
      break;
    case 717: // Baked Potato Cup
      $ret_obj[] = '<p>You pick up the cup, and hastily eat the potatoes.</p>';
      $result = getEatResultArray(0, 0, 0, 50, 0, 20, 8800);
      break;
    case 718: // Spiced Jerky
      $ret_obj[] = '<p>You begin to chew on the delicious jerky.</p>';
      $result = getEatResultArray(1, 659, 0, 25, 0, 20, 9500);
      break;
    case 719: // Scorched Demon Tail
      $ret_obj[] = '<p>You eye the flesh cautiously, and begin to chew..</p>';
      $result = getEatResultArray(92, 1259, 20, 55, 0, 20, 13500);
      break;
    case 720: // Block of Old Cheddar
      $ret_obj[] = '<p>You pull out the block of cheese, and begin to ' .
          'munch away on it.  Yum!</p>';
      $result = getEatResultArray(93, 659, 20, 65, 0, 30, 18500);
      break;
    case 721: // Block of Edam
      $ret_obj[] = '<p>You pull out the block of cheese, and begin to ' .
          'munch away on it.  Yum!</p>';
      $result = getEatResultArray(93, 659, 25, 65, 0, 30, 21500);
      break;
    case 722: // Block of Emmental
      $ret_obj[] = '<p>You pull out the block of cheese, and begin to ' .
          'munch away on it.  Yum!</p>';
      $result = getEatResultArray(93, 1259, 30, 65, 0, 30, 25000);
      break;
    case 723: // Seasoned Rye Bread
      $ret_obj[] = '<p>You eat the fresh bread.  Yum!</p>';
      $result = getEatResultArray(1, 659, 0, 50, 0, 40, 20000);
      break;
    case 724: // Sourdough Loaf
      $ret_obj[] = '<p>You eat the fresh bread.  Yum!</p>';
      $result = getEatResultArray(1, 659, 0, 75, 0, 40, 26000);
      break;
    case 725: // Bidwell's Lavish Bagel
      $ret_obj[] = '<p>You eat the delicious bagel, and feel as though ' .
          'you are consumed by arcane energy.  Yum!</p>';
      $result = getEatResultArray(94, 3059, 20, 0, 100, 60, 15000);
      break;
    case 731: // Gingercrisp Cookie
      $ret_obj[] = '<p>You eat the delicious spiced cookie!</p>';
      $result = getEatResultArray(26, 1859, 0, 1000, 1000, 30, 50000);
      break;
    case 735: // Black Peppercorn
      $ret_obj[] = '<p>You eat the spicy black peppercorns, and hunt ' .
          'around for something else to consume!</p>';
      $result = getEatResultArray(95, 3659, 0, 0, 0, 0, 0);
      break;
    case 738: // Buttered Flatbread
      $ret_obj[] = '<p>You eat the fresh bread.  Yum!</p>';
      $result = getEatResultArray(96, 659, 25, 125, 55, 25, 25000);
      break;
    case 739: // Spicy Vegetable Curry
      $ret_obj[] = '<p>You eat the spicy curry, and your mouth feels like ' .
          'it\'s on fire!  Delicious!</p>';
      $result = getEatResultArray(96, 659, 25, 125, 75, 25, 30000);
      break;
    case 746: // Seared Angelfish Filet
      $ret_obj[] = '<p>You eat the delicious fish filet.</p>';
      $result = getEatResultArray(98, 1259, 20, 35, 35, 15, 17500);
      break;
    case 750: // White Crab Cake
      $ret_obj[] = '<p>You eat the savoury crab cake.</p>';
      $result = getEatResultArray(99, 1259, 20, 50, 35, 15, 15000);
      break;
    case 751: // Red Crab Cake
      $ret_obj[] = '<p>You eat the savoury crab cake.</p>';
      $result = getEatResultArray(100, 1259, 20, 50, 35, 15, 15000);
      break;
    case 755: // Blue Crab Cake
      $ret_obj[] = '<p>You eat the savoury crab cake.</p>';
      $result = getEatResultArray(99, 1259, 40, 50, 35, 15, 15000);
      $ret_obj[] = giveBuffFromItemUse($c_obj, 100, 1259, 40);
      break;
    case 811: // Pravokan Biscuit
      $ret_obj[] = '<p>You eat the tiny biscuit.</p>';
      $result = getEatResultArray(117, 3659, 50, 0, 0, 10, 12000);
      break;
    case 812: // Pravokan Stew
      $ret_obj[] = '<p>You eat the hearty stew.</p>';
      $result = getEatResultArray(117, 3659, 50, 0, 0, 35, 42500);
      break;
    case 833: // Seaweed Salad
      $ret_obj[] = '<p>You stir up the soup, and wolf it down.</p>';
      $result = getEatResultArray(0, 0, 0, 40, 15, 20, 18000);
      break;
    case 869: // Orange Fruit
      $ret_obj[] = '<p>You peel the delicious fruit, and eat it.</p>';
      $result = getEatResultArray(119, 1859, 20, 20, 10, 15, 18000);
      break;
    case 870: // Apple Fruit
      $ret_obj[] = '<p>You crunch away at the tasty apple.  Yum!</p>';
      $result = getEatResultArray(120, 1859, 20, 10, 20, 15, 18000);
      break;


    default:
      $keep_artifact = TRUE;
      $ret_obj[] = '<p>You can\'t eat that!</p>';
      break;
    }

    if ($keep_artifact == FALSE) {
      removeArtifact($c_obj, $artifact_id, $n);
      addTrackingData($c_obj, $artifact_id, sg_track_use, $n);
      $achieve_obj = checkAchievementUse($c_obj, $artifact_id);
      foreach ($achieve_obj as $achieve) {
        $ret_obj[] = $achieve;
      }

      if ($result['b'] > 0) {
        $ret_obj[] = giveBuffFromItemUse($c_obj,
            $result['b'], $result['bd'], $result['bt']);
      }
      if ($result['hp'] > 0) {
        $ret_obj[] = giveHpFromItemUse($c_obj, $result['hp'] * $n);
      }
      if ($result['m'] > 0) {
        $mana_restore = min($result['m'],
            $c_obj->c['mana_max'] - $c_obj->c['mana']);
        if ($mana_restore > 0) {
          $ret_obj[] = '<p><img src="images/recv_mana.gif"> ' .
            'You restore ' . $mana_restore . ' mana.</p>';
          $c_obj->setMana(
              min($c_obj->c['mana'] + $result['m'], $c_obj->c['mana_max']));
        }
      }
      if ($result['f'] > 0) {
        $level_mod = 0;

        if (isset($c_obj->c['buffs'][104])) {
          $level_mod = 3;
          $ret_obj[] = '<p>The seasoning adds a kick to the food!</p>';
          deleteBuff($c_obj, 104);
        } elseif (isset($c_obj->c['buffs'][105])) {
          $level_mod = 6;
          $ret_obj[] = '<p>The seasoning adds a kick to the food!</p>';
          deleteBuff($c_obj, 105);
        }

        $fat_mult = (15 - ($c_obj->c['level'] - $artifact['min_level']) +
            $level_mod) / 10;
        if ($fat_mult > 1.0) {
          $fat_mult = 1.0;
        } elseif ($fat_mult < 0.2) {
          $fat_mult = 0.2;
        }
        $result['hun'] = $result['hun'] * $fat_mult;

        $max_n = getNumFatigueReducers($c_obj, $n, $result['f']);
        $ret_obj[] = giveFatigueReductionFromItemUse(
            $c_obj, $result['f'] * $max_n, $result['hun'] * $max_n);
      }

      $ret_obj[] = '<font size="-2">You have used your ' .
          getIntWithSuffix(
              $_SESSION['tracking'][sg_track_use][$artifact_id]) .
          ' ' . $artifact['name'] . '.</font>';
    }

    $log_obj->addLog($c_obj->c, sg_log_use_item, $artifact_id, $n, 0, 0);
  }

  return $ret_obj;
}

function useRune($c_obj, $rune_id) {
  $ret_obj = array();
  $runes = getRunes($c_obj);
  if (!isset($runes, $rune_id)) {
    $ret_obj[] = '<p>You can\'t use that rune now!</p>';
  } else {
    switch ($rune_id) {
    case 1:
      $ret_obj[] = '<p>You close your eyes, and focus on your wounds.  ' .
          'Within moments, you feel renewed.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 5);
      $ret_obj[] = giveHpFromItemUse($c_obj, 10);
      break;
    case 2:
      $ret_obj[] = '<p>You close your eyes, and focus on your wounds.  ' .
          'Within moments, you feel renewed.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 24);
      $ret_obj[] = giveHpFromItemUse($c_obj, 50);
      break;
    }
  }
  return $ret_obj;
}

function getRunes($c_obj) {
  $spells = array();
  $m = $c_obj->c['mana'];

  if ($m >= 5) {
    $spells[1] = 'Recover 10 health (5 mana)';
  }
  if ($m >= 24) {
    $spells[2] = 'Recover 50 health (24 mana)';
  }

  return $spells;
}

/*
function useSpell($c_obj, $spell_id) {
  $ret_obj = array();

  $spells = getSpells($c_obj);

  if ((!array_key_exists($spell_id, $spells)) ||
      ($spells[$spell_id]['a'] == FALSE)) {
    $ret_obj[] = '<p>You can\'t cast that spell now!</p>';
  } else {

    switch($spell_id) {
    case sg_spell_heal_10:
      $ret_obj[] = '<p>You close your eyes, and focus on your wounds.  ' .
          'Within moments, you feel renewed.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 5);
      $ret_obj[] = giveHpFromItemUse($c_obj, 10);
      break;
    case sg_spell_heal_50:
      $ret_obj[] = '<p>You close your eyes, and focus on your wounds.  ' .
          'Within moments, you feel renewed.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 24);
      $ret_obj[] = giveHpFromItemUse($c_obj, 50);
      break;
    case sg_spell_conjure_water:
      $ret_obj[] = '<p>You close your eyes, and focus on conjuring a set of ' .
          'restorative tonics.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 3);
      $buff_expires = 43200;
      $buff = addBuff($c_obj, 20, $buff_expires, 0, FALSE);
      $artifact = getArtifact(182);
      $ret_obj[] = awardArtifactString($c_obj, $artifact, 3);
      break;
    case sg_spell_avoid_foes:
      $ret_obj[] = '<p>You close your eyes, and focus.  When you open your ' .
          'eyes, you feel as though you could evade combat quite easily, at ' .
          'least for a short time.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 10);
      $ret_obj[] = giveBuffFromItemUse($c_obj, 67, 179, 0);
      break;
    case sg_spell_mage_armour:
      $ret_obj[] = '<p>You close your eyes, and focus.  A shimmering aura ' .
          'begins to form around you, and finally settles into place.  You ' .
          'feel more secure while it remains active.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 15);
      $ret_obj[] = giveBuffFromItemUse($c_obj, 73, 359, 0);
      break;
    case sg_spell_fiery_hands:
      $ret_obj[] = '<p>You close your eyes, and focus.  Suddenly, a blazing ' .
          'flame erupts around your hands!  You become aware of your ' .
          'control over them, and clench your fists confidently.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 6);
      $ret_obj[] = giveBuffFromItemUse($c_obj, 74, 359, 0);
      break;
    case sg_spell_sweets_1:
      $ret_obj[] = '<p>You close your eyes, and bring forth a few sweet ' .
          'treats.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 5);
      $artifact = getArtifact(436);
      $ret_obj[] = awardArtifactString($c_obj, $artifact, 1);
      $artifact = getArtifact(440);
      $ret_obj[] = awardArtifactString($c_obj, $artifact, 3);
      $c_obj->enableFlagBit(sg_flag_daily, sg_dailyflag_sweet_1);
      break;
    case sg_spell_sweets_2:
      $ret_obj[] = '<p>You close your eyes, and bring forth a few sweet ' .
          'treats.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 5);
      $quantity = rand(3, 5);
      $artifact = getArtifact(786);
      $ret_obj[] = awardArtifactString($c_obj, $artifact, $quantity);
      $c_obj->enableFlagBit(sg_flag_daily, sg_dailyflag_sweet_2);
      break;
    case sg_spell_inspiring_song:
      $ret_obj[] = '<p>You close your eyes, and begin to sing.  The lyrics ' .
          'fill you with resolve, and you feel lighter on your feet.</p>';
      $c_obj->setMana($c_obj->c['mana'] - 4);
      $ret_obj[] = giveBuffFromItemUse($c_obj, 76, 359, 20);
      break;
    }

  }

  return $ret_obj;
}

function getSpells($c_obj) {
  $spells = array();
  $m = $c_obj->c['mana'];

  if ($m >= 5) {
    $spells[sg_spell_heal_10] = 'Recover 10 health (5 mana)';
  }
  if ($m >= 24) {
    $spells[sg_spell_heal_50] = 'Recover 50 health (24 mana)';
  }

  if ((hasSkill($c_obj, getSkillId(skill_int, 3))) && ($m >= 15)) {
    $spells[sg_spell_mage_armour] = 'Cast Mage Armour (15 mana)';
  }
  if ((hasSkill($c_obj, getSkillId(skill_int, 4))) && ($m >= 6)) {
    $spells[sg_spell_fiery_hands] = 'Cast Fiery Hands (6 mana)';
  }

  if ((hasSkill($c_obj, getSkillId(skill_cha, 2))) && ($m >= 5) &&
      (!getFlagBit($c_obj, sg_flag_daily, sg_dailyflag_sweet_1))) {
    $spells[sg_spell_sweets_1] = 'Cast Summon Sweets, Rank 1 (5 mana)';
  }
  if ((hasSkill($c_obj, getSkillId(skill_cha, 3))) && ($m >= 4)) {
    $spells[sg_spell_inspiring_song] = 'Cast Inspiring Song (4 mana)';
  }
  if ((hasSkill($c_obj, getSkillId(skill_cha, 8))) && ($m >= 5) &&
      (!getFlagBit($c_obj, sg_flag_daily, sg_dailyflag_sweet_2))) {
    $spells[sg_spell_sweets_2] = 'Cast Summon Sweets, Rank 2 (5 mana)';
  }

/*
  if ((hasSkill($c_obj, getSkillId(skill_int, 4))) && ($m >= 3)) {
    if (!array_key_exists(20, $c_obj->c['buffs'])) {
      $spells[sg_spell_conjure_water] = 'Conjure Mana Water (3 mana)';
    }
  }


  return $spells;
}
*/

?>