<?

function getCustomState($c_obj, $log_obj) {
  $ret_obj = array();
  $ret_obj['actions'] = array();
  $ret_obj['text'] = array();

  $action = getGetInt('a', 0);
  $game_flags = getGameFlags();
  $combats = 10 - getFlagValue($c_obj, sg_flag_sandstorm_combats);

  $can_fight = (($combats > 0) &&
      (checkIfFatigued($c_obj) == FALSE) &&
      (checkIfWounded($c_obj) == FALSE) && (checkIfBurdened($c_obj) == FALSE));

  if (11 == $action) {
    if (($can_fight) && ($game_flags[1] > 0)) {
      $c_obj->addFlag(sg_flag_sandstorm_combats, 10 - $combats + 1);
      $zone = getZone(109);
      $encounter = getFoe($c_obj, 200 + rand(0, 2));
      $encounter['game_flag_decrease'] = 1;
      initiateCombat($c_obj, $encounter, $zone);
    } else {
      $ret_obj['actions'][] = '<a href="main.php?z=109">Return to the ' .
          'Sandstorm Fortification</a>';
    }
  } elseif (12 == $action) {
    if (($can_fight) && ($game_flags[2] > 0)) {
      $c_obj->addFlag(sg_flag_sandstorm_combats, 10 - $combats + 1);
      $zone = getZone(109);
      $encounter = getFoe($c_obj, 203 + rand(0, 2));
      $encounter['game_flag_decrease'] = 2;
      initiateCombat($c_obj, $encounter, $zone);
    } else {
      $ret_obj['actions'][] = '<a href="main.php?z=109">Return to the ' .
          'Sandstorm Fortification</a>';
    }
  } elseif (13 == $action) {
    if (($can_fight) && ($game_flags[3] > 0)) {
      $c_obj->addFlag(sg_flag_sandstorm_combats, 10 - $combats + 1);
      $zone = getZone(109);
      $encounter = getFoe($c_obj, 206 + rand(0, 2));
      $encounter['game_flag_decrease'] = 3;
      initiateCombat($c_obj, $encounter, $zone);
    } else {
      $ret_obj['actions'][] = '<a href="main.php?z=109">Return to the ' .
          'Sandstorm Fortification</a>';
    }
  } else {
    $ret_obj['text'][] = '<p>The base is still being constructed, but as ' .
        'each hour passes, more and more work is completed!  You stand ' .
        'cautiously at a distance, and observe the number of Sandstorm ' .
        'combatants.</p>';
    if ($combats == 1) { $plural = ''; } else { $plural = 's'; }
    $ret_obj['text'][] = '<p><b>You can make ' . $combats . ' more assault' .
        $plural . ' on the Sandstorm Fortification today.</b></p>';
    $ret_obj['text'][] = '<p>Number of low-level labourers remaining: <b>' .
        $game_flags[1] . '</b>';
    if (($combats > 0) && ($game_flags[1] > 0)) {
      $ret_obj['text'][] = '<br><font size="-2">(<a href="main.php?z=109&' .
          'a=11">Attack</a>) (levels 4-6)</font></p>';
    }
    $ret_obj['text'][] = '</p><p>Number of mid-level guards remaining: <b>' .
        $game_flags[2] . '</b>';
    if (($combats > 0) && ($game_flags[2] > 0)) {
      $ret_obj['text'][] = '<br><font size="-2">(<a href="main.php?z=109&' .
          'a=12">Attack</a>) (levels 8-10)</font></p>';
    }
    $ret_obj['text'][] = '</p><p>Number of senior-level officers remaining: ' .
        '<b>' . $game_flags[3] . '</b>';
    if (($combats > 0) && ($game_flags[3] > 0)) {
      $ret_obj['text'][] = '<br><font size="-2">(<a href="main.php?z=109&' .
          'a=13">Attack</a>) (levels 12-14)</font></p>';
    }
    $ret_obj['text'][] = '</p>';
  }

  return $ret_obj;
}

function renderCustomState($zone, $c_obj, $state_obj) {
  echo '<p class="zone_title">' . $zone['name'] . '</p>';
  echo '<p class="zone_description">' . $zone['description'] . '</p>';

  foreach ($state_obj['text'] as $x) {
    echo $x;
  }
  echo '<p>' . join('<br>', $state_obj['actions']) . '</p>';
}

?>