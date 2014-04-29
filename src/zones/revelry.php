<?

function getCustomState($c_obj, $log_obj) {
  $ret_obj = array();
  $ret_obj['actions'] = array();
  $ret_obj['text'] = array();

  $action = getGetInt('a', 0);

  $can_fight = ((checkIfFatigued($c_obj) == FALSE) &&
      (checkIfWounded($c_obj) == FALSE) && (checkIfBurdened($c_obj) == FALSE));
  $reveler_count = 50 - getFlagValue($c_obj, sg_flag_pravokan_reveler_count);

  if (11 == $action) {
    if (($can_fight) && ($reveler_count > 0)) {
      $zone = getZone(126);

      list($usec, $sec) = explode(' ', microtime());
      $new_srand = (float) $sec + ((float) $usec * 100000);
      $c_obj->addFlag(sg_flag_pravokan_reveler_srand, $new_srand);

      $encounter = getFoe($c_obj, 235);
      initiateCombat($c_obj, $encounter, $zone);
    } else {
      $ret_obj['actions'][] = '<a href="main.php?z=125">Return to The ' .
          'Starfall Plains</a>';
    }
  } else {
    $ret_obj['text'][] = '<p>The Pravokan goons have taken a liking to ' .
        'these open fields, returning here after a long day of causing ' .
        'trouble.  Broken bottles and various pieces of trash litter ' .
        'the fields.</p>';
    $ret_obj['text'][] = '<p>Number of hooligans in the fields' .
        ': <b>' . $reveler_count . '</b>';
    if (isset($c_obj->c['quests'][119])) {
      if ($reveler_count > 0) {
        $ret_obj['text'][] = '<br><font size="-2">(<a href="main.php?z=126&' .
            'a=11">Attack</a>)</font></p>';
      }
    } else {
      $ret_obj['text'][] = '</p><p class="tip">&Aacute;ron Sipos at the ' .
          'Starfall Bay Coastlines should probably hear about this!</p>';
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