<?

define('sg_gameflag_scar_1', 4);
define('sg_gameflag_scar_2', 5);
define('sg_gameflag_scar_3', 6);
define('sg_gameflag_scar_4', 7);


function getCustomState($c_obj, $log_obj) {
  $ret_obj = array();
  $ret_obj['actions'] = array();
  $ret_obj['text'] = array();

  $action = getGetInt('a', 0);
  $game_flags = getGameFlags();

  $can_fight = ((checkIfFatigued($c_obj) == FALSE) &&
      (checkIfWounded($c_obj) == FALSE) && (checkIfBurdened($c_obj) == FALSE));

  if (11 == $action) {
    if (($can_fight) && ($game_flags[sg_gameflag_scar_1] > 0)) {
      $zone = getZone(111);
      if (rand(0, 10000) != 0) {
        $encounter = getFoe($c_obj, 210 + rand(0, 1));
        $encounter['game_flag_decrease'] = sg_gameflag_scar_1;
      } else {
        $encounter = getFoe($c_obj, 212);
      }
      initiateCombat($c_obj, $encounter, $zone);
    } else {
      $ret_obj['actions'][] = '<a href="main.php?z=111">Return to the ' .
          'Scarshield Staircases</a>';
    }
  } else {
    $ret_obj['text'][] = '<p>The Scarshield Guardians appear to collect ' .
        'here, defending the lower corridors from anyone trying to ' .
        'interfere with the business of the demons.  You stand ' .
        'cautiously at a distance, and observe the number of demons.</p>';
    $ret_obj['text'][] = '<p>Number of defenders at the Lost Storage ' .
        'Halls entrance: <b>' . $game_flags[sg_gameflag_scar_1] . '</b>';
    if ($game_flags[sg_gameflag_scar_1] > 0) {
      $ret_obj['text'][] = '<br><font size="-2">(<a href="main.php?z=111&' .
          'a=11">Attack</a>) (levels 11-13)</font></p>';
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