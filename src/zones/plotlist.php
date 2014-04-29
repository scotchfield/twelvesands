<?

function getCustomState($c_obj, $log_obj) {
  $ret_obj = array();
  $ret_obj['actions'] = array();
  $ret_obj['text'] = array();

  $zone_id = getGetInt('z', 0);
  $artifact = hasArtifact($c_obj, 865);

  $ret_obj['text'][] = '<h3>Purchase a Plot:</h3>';
  if ($artifact['quantity'] > 0) {
    $action_id = getGetInt('action', 0);
    if ((array_key_exists('buy', $_GET)) &&
        ($c_obj->c['action_id'] == $action_id)) {
      addPlot($c_obj, 1, $c_obj->c['name'], 'An undeveloped piece of land.');
      $c_obj->resetActionId();
      removeArtifact($c_obj, 865, 1);
      addTrackingData($c_obj, 865, sg_track_use, 1);
      $achieve_obj = checkAchievementUse($c_obj, 865);
      foreach ($achieve_obj as $achieve) {
        $ret_obj['text'][] = $achieve;
      }
      $ret_obj['text'][] = '<font size="-2">You have used your ' .
          getIntWithSuffix(
              $_SESSION['tracking'][sg_track_use][865]) .
          ' ' . $artifact['name'] . '.</font>';
      $log_obj->addLog($c_obj->c, sg_log_use_item, 865, 1, 0, 0);
    } else {
      $ret_obj['text'][] = '<p>You are in possession of a ' .
          renderArtifactStr($artifact) .
          ' that can be redeemed for a plot of ' .
          'land!  Would you like to purchase a plot here?</p>' .
          '<p><a href="main.php?z=' . $zone_id . '&buy&action=' .
          $c_obj->c['action_id'] . '">Purchase a plot!</a></p>';
    }
  } else {
    $artifact = getArtifact(865);
    $ret_obj['text'][] = '<p>You don\'t have a ' .
        renderArtifactStr($artifact) .
        ' to redeem for a plot!  If you watch the auctions at ' .
        'the Starfall Bay Auction House, you\'ll have a chance to ' .
        'purchase your own land.</p>';
  }

  $ret_obj['text'][] = '<h3>Existing Properties:</h3>';
  $plot_obj = getAllZonePlots(1);

  $ret_obj['text'][] = '<p>';
  foreach ($plot_obj as $plot) {
    $ret_obj['text'][] = '<a href="plot.php?i=' . $plot['id'] .
        '">' . $plot['title'] . '</a> (owned by <a href="char.php?i=' .
        $plot['char_id'] . '">' . $plot['char_name'] . '</a>)<br>';
  }
  $ret_obj['text'][] = '</p>';

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