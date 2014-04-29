<?

function getInfirmaryHealState($c_obj) {
  $a = getGetInt('a', 0);

  if (3 == $a) {

    if ($c_obj->c['fatigue'] >= 100000) {
      return '<p>You\'re too tired to bandage your own wounds!  You can ' .
          'come back tomorrow when you\'ve had some rest.</p>';
    } else {
      $c_obj->addFatigue(1000);

      $v = $c_obj->c['level'] * 5;
      $hp_restore = min($c_obj->c['base_hp'] - $c_obj->c['current_hp'], $v);
      $c_obj->setCurrentHp($c_obj->c['current_hp'] + $hp_restore);
      $mana_restore = min($c_obj->c['mana_max'] - $c_obj->c['mana'], $v);
      $c_obj->setMana($c_obj->c['mana'] + $mana_restore);

      return '<p><img src="images/recv_health.gif"> ' .
          'You heal ' . $hp_restore . ' health.</p>' .
          '<p><img src="images/recv_mana.gif"> ' .
          'You heal ' . $mana_restore . ' mana.</p>';
    }

  } elseif (2 == $a) {

    $GOLD_COST = 25;
    $injuries = $c_obj->c['mana_max'] - $c_obj->c['mana'];
    $max_afforded = floor($c_obj->c['gold'] / $GOLD_COST);
    $mana_heal = min($injuries, $max_afforded);
    $cost_heal = $mana_heal * $GOLD_COST;

    if ($mana_heal > 0) {
      $new_mana = $c_obj->c['mana'] + $mana_heal;
      $new_gold = $c_obj->c['gold'] - $cost_heal;
      $c_obj->setMana($new_mana);
      $c_obj->setGold($new_gold);

      return '<p><img src="images/recv_mana.gif"> ' .
          'You heal ' . $mana_heal . ' mana at a cost of ' .
          $cost_heal . ' gold.</p>';
    } else {
      return '<p>No mana restoration is needed!</p>';
    }

  } elseif (1 == $a) {
    $GOLD_COST = 2;
    $injuries = $c_obj->c['base_hp'] - $c_obj->c['current_hp'];
    $max_afforded = floor($c_obj->c['gold'] / $GOLD_COST);
    $hpToHeal = min($injuries, $max_afforded);
    $costToHeal = $hpToHeal * $GOLD_COST;

    if ($hpToHeal > 0) {
      $achieve_st = '';
      if ($c_obj->c['current_hp'] == 1) {
        $achieve_st = awardAchievement($c_obj, 30);
      }

      $newHp = $c_obj->c['current_hp'] + $hpToHeal;
      $newGold = $c_obj->c['gold'] - $costToHeal;
      $c_obj->setCurrentHp($newHp);
      $c_obj->setGold($newGold);

      return '<p><img src="images/recv_health.gif"> ' .
          'You heal ' . $hpToHeal . ' health at a cost of ' .
          $costToHeal . ' gold.</p>' . $achieve_st;
    } else {
      if ($injuries > 0) {
        return '<p>You can\'t afford any more healing!</p>';
      } else {
        return '<p>No healing is needed!</p>';
      }
    }
  }

  return FALSE;
}

function renderInfirmaryHealState($zone, $st) {
  echo '<p class="zone_title">' . $zone['name'] . '</p>';
  echo '<p class="zone_description">' . $zone['description'] . '</p>';

  if ($st != FALSE) {
    echo $st;
  }

  echo '<ul class="selection_list">';
  echo '<li><a href="main.php?z=' . $zone['id'] . '&a=1">Pay for healing (' .
       '2 gold per health)</a></li>';
  echo '<li><a href="main.php?z=' . $zone['id'] . '&a=2">Pay for mana (' .
       '25 gold per mana)</a></li>';
  echo '<li><a href="main.php?z=' . $zone['id'] . '&a=3">Spend some ' .
       'fatigue, and bandage yourself for free (1% fatigue)</a></li>';
  echo '</ul>';
}

?>