<?

function getWarfareGameList($char_id) {
  $c_id = intval($char_id);

  $query = "
    SELECT
      *
    FROM
      `warfare_games`
    WHERE
      status = 0
  ";

  $results = sqlQuery($query);
  $ret_obj = array();

  if ($results) {
    while ($game = $results->fetch_assoc()) {
      $ret_obj[$game['id']] = $game;
    }
  }

  return $ret_obj;
}

function getWarfareGameHistory($char_id) {
  $c_id = intval($char_id);

  $query = "
    SELECT
      *
    FROM
      `warfare_games`
    WHERE
      status > 0 AND (char_id_1 = $char_id OR char_id_2 = $char_id)
  ";

  $results = sqlQuery($query);
  $ret_obj = array();

  if ($results) {
    while ($game = $results->fetch_assoc()) {
      $ret_obj[$game['id']] = $game;
    }
  }

  return $ret_obj;
}

function getRecentWarfareGames() {
  $query = "
    SELECT
      *
    FROM
      `warfare_games`
    WHERE
      status > 0
    ORDER BY
      modified DESC
    LIMIT 20
  ";

  $results = sqlQuery($query);
  $ret_obj = array();

  if ($results) {
    while ($game = $results->fetch_assoc()) {
      $ret_obj[$game['id']] = $game;
    }
  }

  return $ret_obj;
}

function renderWarfareOptionList($a_obj) {
  $st = '';
  foreach ($a_obj as $k => $v) {
    $st = $st . '<option value="' . $k . '">' . $v['name'] . ' (power ' .
          $v['base_damage'] . ')</option>';
  }
  return $st;
}

function getWarfareResultTr($a1, $a2, $winner) {
  $red = ' bgcolor="#FFC0C0"';
  $grn = ' bgcolor="#C0FFC0"';

  $td1 = $red; $td2 = $red;
  if ($winner == -1) { $td1 = $grn; }
  else { $td2 = $grn; }
  return '<tr align="center"><td' . $td1 . '>' .
         renderArtifactStr($a1) . '</td><td' . $td2 . '>' .
         renderArtifactStr($a2) . '</td></tr>';
}

function getCustomState($c_obj, $log_obj) {
  $ret_obj = array();
  $ret_obj['actions'] = array();
  $ret_obj['text'] = array();

  $action = getGetInt('a', 0);

  if (6 == $action) {

    $ret_obj['text'][] = '<p><b>Recent Capital Warfare Games</b></p>';

    $games = getRecentWarfareGames();

    $ret_obj['text'][] = '<center><table class="plain" cellpadding="5" ' .
        'border="0"><tr><th>&nbsp;</th><th>Player 1</th>' .
        '<th>Player 2</th><th>Wager</th><th>Date</th></tr>';

    $red = ' bgcolor="#FFC0C0"';
    $grn = ' bgcolor="#C0FFC0"';

    foreach ($games as $game) {
      if ($game['status'] == 1) {
        $ret_obj['text'][] = '<tr align="center"><td><font size="-2">(' .
            '<a href="main.php?z=108&a=4&i=' . $game['id'] . '">view</a>' .
            ')</font></td><td' . $grn .
            '><a href="char.php?i=' .
            $game['char_id_1'] . '">' . $game['char_name_1'] .
            '</a></td><td' . $red . '><a href="char.php?i=' .
            $game['char_id_2'] . '">' . $game['char_name_2'] .
            '</a></td><td>' . $game['wager'] . '</td><td>' .
            date('m.d.y g:ia', $game['modified']) . '</td></tr>';
      } else {
        $ret_obj['text'][] = '<tr align="center"><td><font size="-2">(' .
            '<a href="main.php?z=108&a=4&i=' . $game['id'] . '">view</a>' .
            ')</font></td><td' . $red .
            '><a href="char.php?i=' .
            $game['char_id_1'] . '">' . $game['char_name_1'] .
            '</a></td><td' . $grn . '><a href="char.php?i=' .
            $game['char_id_2'] . '">' . $game['char_name_2'] .
            '</a></td><td>' . $game['wager'] . '</td><td>' .
            date('m.d.y g:ia', $game['modified']) . '</td></tr>';
      }
    }

    $ret_obj['text'][] = '</table></center>';

    $ret_obj['actions'][] = '<a href="main.php?z=108">Return to the Games ' .
        'Room</a>';

  } elseif (5 == $action) {

    $ret_obj['text'][] = '<p><b>Capital Warfare Game History</b></p>';

    $games = getWarfareGameHistory($c_obj->c['id']);
    if (count($games) > 0) {
      $ret_obj['text'][] = '<center><table class="plain" cellpadding="5" ' .
          'border="0"><tr><th>&nbsp;</th><th>Player 1</th>' .
          '<th>Player 2</th><th>Wager</th><th>Date</th></tr>';

      $red = ' bgcolor="#FFC0C0"';
      $grn = ' bgcolor="#C0FFC0"';

      foreach ($games as $game) {
        if ($game['status'] == 1) {
          $ret_obj['text'][] = '<tr align="center"><td><font size="-2">(' .
              '<a href="main.php?z=108&a=4&i=' . $game['id'] . '">view</a>' .
              ')</font></td><td' . $grn .
              '><a href="char.php?i=' .
              $game['char_id_1'] . '">' . $game['char_name_1'] .
              '</a></td><td' . $red . '><a href="char.php?i=' .
              $game['char_id_2'] . '">' . $game['char_name_2'] .
              '</a></td><td>' . $game['wager'] . '</td><td>' .
              date('m.d.y g:ia', $game['modified']) . '</td></tr>';
        } else {
          $ret_obj['text'][] = '<tr align="center"><td><font size="-2">(' .
              '<a href="main.php?z=108&a=4&i=' . $game['id'] . '">view</a>' .
              ')</font></td><td' . $red .
              '><a href="char.php?i=' .
              $game['char_id_1'] . '">' . $game['char_name_1'] .
              '</a></td><td' . $grn . '><a href="char.php?i=' .
              $game['char_id_2'] . '">' . $game['char_name_2'] .
              '</a></td><td>' . $game['wager'] . '</td><td>' .
              date('m.d.y g:ia', $game['modified']) . '</td></tr>';
        }
      };
      $ret_obj['text'][] = '</table></center>';

    } else {
      $ret_obj['text'][] = '<p><b>You haven\'t finished any games!</b></p>';
    }

    $ret_obj['actions'][] = '<a href="main.php?z=108">Return to the Games ' .
        'Room</a>';

  } elseif (4 == $action) {

    $ret_obj['text'][] = '<p><b>Capital Warfare Game Results</b></p>';

    $id = getGetInt('i', -1);
    $game = getWarfareGame($id);

    if (FALSE == $game) {
      $ret_obj['text'][] = '<p class="tip">That game doesn\'t exist!</p>';
    } elseif ($game['status'] < 1) {
      $ret_obj['text'][] = '<p class="tip">That game isn\'t finished!</p>';
    } else {
      $a_obj = getArtifactArray(array(
          $game['a1'], $game['a2'], $game['a3'], $game['a4'], $game['a5'],
          $game['b1'], $game['b2'], $game['b3'], $game['b4'], $game['b5']));

      $ret_obj['text'][] = '<p><a href="char.php?i=' . $game['char_id_1'] .
          '">' . $game['char_name_1'] . '</a> vs. <a href="char.php?i=' .
          $game['char_id_2'] . '">' . $game['char_name_2'] . '</a></p>';
      $ret_obj['text'][] = '<p><b>Wager: ' . $game['wager'] . ' gold</b></p>';
      $ret_obj['text'][] = '<center><table class="plain" cellpadding="5" ' .
          'border="0">';
      $ret_obj['text'][] = getWarfareResultTr(
          $a_obj[$game['a1']], $a_obj[$game['b1']], $game['s1']);
      $ret_obj['text'][] = getWarfareResultTr(
          $a_obj[$game['a2']], $a_obj[$game['b2']], $game['s2']);
      $ret_obj['text'][] = getWarfareResultTr(
          $a_obj[$game['a3']], $a_obj[$game['b3']], $game['s3']);
      $ret_obj['text'][] = getWarfareResultTr(
          $a_obj[$game['a4']], $a_obj[$game['b4']], $game['s4']);
      $ret_obj['text'][] = getWarfareResultTr(
          $a_obj[$game['a5']], $a_obj[$game['b5']], $game['s5']);
      $ret_obj['text'][] = '</table></center>';

      if ($game['status'] == 1) {
        $ret_obj['text'][] = '<p><b><a href="char.php?i=' .
            $game['char_id_1'] .
            '">' . $game['char_name_1'] . '</a> wins!</b></p>';
      } elseif ($game['status'] == 2) {
        $ret_obj['text'][] = '<p><b><a href="char.php?i=' .
            $game['char_id_2'] .
            '">' . $game['char_name_2'] . '</a> wins!</b></p>';
      }
    }

    $ret_obj['actions'][] = '<a href="main.php?z=108">Return to the Games ' .
        'Room</a>';

  } elseif (3 == $action) {
    $ret_obj['text'][] = '<p><b>Accept a Capital Warfare Game</b></p>';

    $id = getGetInt('i', -1);
    $game = getWarfareGame($id);

    if (FALSE == $game) {
      $ret_obj['text'][] = '<p class="tip">That game doesn\'t exist!</p>';
    } elseif ($game['status'] != 0) {
      $ret_obj['text'][] = '<p class="tip">That game isn\'t available!</p>';
    } elseif ($game['char_id_1'] == $c_obj->c['id']) {
      $ret_obj['text'][] = '<p class="tip">That\'s your game!</p>';
    } elseif ($game['wager'] > $c_obj->c['gold']) {
      $ret_obj['text'][] = '<p class="tip">You don\'t have enough gold!</p>';
    } else {
      $artifacts = getCharWarfareArtifacts($c_obj->c['id']);

      if (count($artifacts) > 0) {
        $ret_obj['text'][] = '<script type="text/javascript" ' .
            'src="/include/ts_warfare.js"></script>';

        $ret_obj['text'][] = '<p>Game advertised by: <a href="char.php?i=' .
            $game['char_id_1'] . '">' . $game['char_name_1'] . '</a></p>';

        $option_text = renderWarfareOptionList($artifacts);
        $ret_obj['text'][] = '<p>Total power: <b><span id="warfare_power">' .
            ' </span> / 20</b></p>';
        $ret_obj['text'][] = '<form action="action.php?a=cwp" method="post">' .
            '<input type="hidden" name="id" value="' . $id . '">';
        $ret_obj['text'][] = '<p><b>First piece:</b><br><select name="a1" ' .
            'id="a1" onchange="getWarfareTotalPower()">' .
            $option_text . '</select></p>';
        $ret_obj['text'][] = '<p><b>Second piece:</b><br><select name="a2" ' .
            'id="a2" onchange="getWarfareTotalPower()">' .
            $option_text . '</select></p>';
        $ret_obj['text'][] = '<p><b>Third piece:</b><br><select name="a3" ' .
            'id="a3" onchange="getWarfareTotalPower()">' .
            $option_text . '</select></p>';
        $ret_obj['text'][] = '<p><b>Fourth piece:</b><br><select name="a4" ' .
            'id="a4" onchange="getWarfareTotalPower()">' .
            $option_text . '</select></p>';
        $ret_obj['text'][] = '<p><b>Fifth piece:</b><br><select name="a5" ' .
            'id="a5" onchange="getWarfareTotalPower()">' .
            $option_text . '</select></p>';
        $ret_obj['text'][] = '<p><b>Wager amount:</b> ' . $game['wager'] .
            ' gold</p><input type="submit" value="Accept!"></form>';
        $ret_obj['text'][] = '<script type="text/javascript">' .
            'getWarfareTotalPower();</script>';

      } else {
        $ret_obj['text'][] = '<p>You don\'t have any Capital Warfare pieces ' .
            'to play with!</p>';
      }
    }

    $ret_obj['actions'][] = '<a href="main.php?z=108">Return to the Games ' .
        'Room</a>';
  } elseif (2 == $action) {
    $ret_obj['text'][] = '<p><b>Advertise a Capital Warfare Game</b></p>';

    $artifacts = getCharWarfareArtifacts($c_obj->c['id']);

    if (count($artifacts) > 0) {
      $ret_obj['text'][] = '<script type="text/javascript" ' .
          'src="/include/ts_warfare.js"></script>';

      $option_text = renderWarfareOptionList($artifacts);
      $ret_obj['text'][] = '<p>Total power: <b><span id="warfare_power">' .
          ' </span> / 20</b></p>';
      $ret_obj['text'][] = '<form action="action.php?a=cwa" method="post">';
      $ret_obj['text'][] = '<p><b>First piece:</b><br><select name="a1" ' .
          'id="a1" onchange="getWarfareTotalPower()">' .
          $option_text . '</select></p>';
      $ret_obj['text'][] = '<p><b>Second piece:</b><br><select name="a2" ' .
          'id="a2" onchange="getWarfareTotalPower()">' .
          $option_text . '</select></p>';
      $ret_obj['text'][] = '<p><b>Third piece:</b><br><select name="a3" ' .
          'id="a3" onchange="getWarfareTotalPower()">' .
          $option_text . '</select></p>';
      $ret_obj['text'][] = '<p><b>Fourth piece:</b><br><select name="a4" ' .
          'id="a4" onchange="getWarfareTotalPower()">' .
          $option_text . '</select></p>';
      $ret_obj['text'][] = '<p><b>Fifth piece:</b><br><select name="a5" ' .
          'id="a5" onchange="getWarfareTotalPower()">' .
          $option_text . '</select></p>';
      $ret_obj['text'][] = '<p><b>Wager amount:</b><br><input type="text" ' .
          'name="g"></p><input type="submit" value="Advertise!"></form>';
      $ret_obj['text'][] = '<script type="text/javascript">' .
          'getWarfareTotalPower();</script>';

    } else {
      $ret_obj['text'][] = '<p>You don\'t have any Capital Warfare pieces ' .
          'to play with!</p>';
    }

    $ret_obj['actions'][] = '<a href="main.php?z=108">Return to the Games ' .
        'Room</a>';
  } elseif (1 == $action) {
    $ret_obj['text'][] = '<p><b>Open Games</b></p>';

    $game_list = getWarfareGameList($c_obj->c['id']);
    if (count($game_list) == 0) {
      $ret_obj['text'][] = '<p>There are no games advertised!</p>';
    } else {
      $ret_obj['text'][] = '<center><table cellpadding="2" border="0"><tr>' .
          '<th>Status</th><th>Opponent</th><th>Wager</th><th>Date</th></tr>';
      foreach ($game_list as $game) {
        if ($game['char_id_1'] == $c_obj->c['id']) {
          $ret_obj['text'][] = '<tr align="center"><td><font size="-2">Your ' .
              'advert</font></td>';
        } else {
          $ret_obj['text'][] = '<tr align="center"><td><font size="-2">(' .
              '<a href="main.php?z=108&a=3&i=' . $game['id'] .
              '">accept</a>)</font></td>';
        }
        $ret_obj['text'][] = '<td><a href="char.php?i=' . $game['char_id_1'] .
            '">' . $game['char_name_1'] . '</a></td><td>' . $game['wager'] .
            '</td><td>' . date('m.d.y g:ia', $game['modified']) . '</td></tr>';
      }
      $ret_obj['text'][] = '</table></center>';
    }

    $ret_obj['actions'][] = '<a href="main.php?z=108">Return to the Games ' .
        'Room</a>';
  } elseif (0 == $action) {
    $ret_obj['actions'][] = '<a href="main.php?z=108&a=1">View the list ' .
        'of open games</a>';
    $ret_obj['actions'][] = '<a href="main.php?z=108&a=2">Advertise a ' .
        'Capital Warfare Game</a>';
    $ret_obj['actions'][] = '<a href="main.php?z=108&a=5">Review your ' .
        'completed games</a>';
    $ret_obj['actions'][] = '<a href="main.php?z=108&a=6">See the most ' .
        'recent matches</a>';
  }

  return $ret_obj;
}

function renderCustomState($zone, $c_obj, $state_obj) {
  echo '<p class="zone_title">' . $zone['name'] . '</p>';
//  echo '<p class="zone_description">' . $zone['description'] . '</p>';

  echo '<p class="zone_description">This room is set up in the rear of the ' .
       'casino, in order to allow citizens to engage in games against each ' .
       'other.  People sit at tables in groups, shifting cards and ' .
       'miniatures back and forth between one another.</p>';

  foreach ($state_obj['text'] as $x) {
    echo $x;
  }
  echo '<p>' . join('<br>', $state_obj['actions']) . '</p>';
}

?>