<?

require_once 'include/core.php';
require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/pvp.php';

$log_obj = new Logger();
$char_obj = new Char( $_SESSION[ 'c' ] );
$c = $char_obj->c;
forceCombatCheck( $char_obj );

$action = getGetStr( 'a', '' );

if ( 'v' == $action ) {
    $match_id = getGetInt( 'i', 0 );
    $match = getPvpMatch( $match_id );

    $disp_array = array();

    if ( FALSE == $match ) {
        $disp_array[] = '<p>This isn\'t your match!</p>';
    } elseif ( ( $match[ 'p1_id' ] != $char_obj->c[ 'id' ] ) &&
               ( $match[ 'p2_id' ] != $char_obj->c[ 'id' ] ) ) {
        $disp_array[] = '<p>This isn\'t your match!</p>';
    } else {
        $params_array = array();
        if ( $match[ 'game_type' ] == 1 ) {
            require_once sg_base_path . 'include/pvp/ticktack.php';
            $params_array[ 'x' ] = getGetInt( 'x', 0 );
        } elseif ( $match[ 'game_type' ] == 2 ) {
            require_once sg_base_path . 'include/pvp/brawl.php';
        }
        $disp_array = getPvpDisplay( $char_obj, $match, $params_array );
    }
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss( $c ); ?>
</head>
<body>

<? renderPopupText(); ?>

<div class="container">

<?

require '_header.php';

echo '<p class="zone_title">Player Combat</p>';

if ( '' == $action ) {

  $matches = getPvpMatches( $char_obj );

?>

  <p class="zone_description">This venue offers you a way to test your
  skills against other combatants from across the land.</p>

  <p><font size="-2">
    (<a href="pvp.php?a=a">Advertise a challenge</a>) |
    (<a href="pvp.php?a=o">View open matches</a>)
  </font></p>

  <center>
  <table class="profession">
  <tr>
    <th width="50">ID</th>
    <th width="100">Opponent</th>
    <th width="100">Rating</th>
    <th width="100">Match Type</th>
    <th width="100">Current State</th>
  </tr>

<?

    if ( count( $matches ) == 0 ) {
        echo '<tr><td colspan="5" align="center"><i>You have no matches!' .
             '</i></td></tr>';
    } else {
        foreach ( $matches as $x ) {
            $p1_self = TRUE;
            if ( $x[ 'p2_id' ] == $char_obj->c[ 'id' ] ) {
                $p1_self = FALSE;
            }
            echo '<tr><td><a href="pvp.php?a=v&i=' . $x[ 'id' ] . '">' .
                 $x[ 'id' ] . '</a></td>';
            if ( $p1_self ) {
                if ( $x[ 'p2_id' ] == 0 ) {
                    echo '<td colspan="2">No opponent yet!</td>';
                } else {
                    echo '<td><a href="char.php?i=' . $x[ 'p2_id' ] . '">' .
                         $x[ 'p2_name' ] . '</a></td><td>' . $x[ 'p2_elo' ] . '</td>';
                }
            } else {
                echo '<td><a href="char.php?i=' . $x[ 'p1_id' ] . '">' .
                     $x[ 'p1_name' ] . '</a></td><td>' . $x[ 'p1_elo' ] . '</td>';
            }
            echo '<td>' . $pvp_types[ $x[ 'game_type' ] ] . '</td>';
            if ( $x[ 'game_state' ] & ( 1 << sg_pvp_match_started ) ) {
                if ( getMyTurn( $char_obj, $x ) ) {
                    echo '<td><a href="pvp.php?a=v&i=' . $x[ 'id' ] . '">Your turn' .
                         '</a></td>';
                } else {
                    echo '<td>Opponent\'s turn</td>';
                }
            }
            echo '</tr>';
        }
    }

?>

  </table>
  </center>

<?

} elseif ( 'a' == $action ) {

  $game_type = getGetInt('t', 0);

  if (0 == $game_type) {

    echo '<p>Which game would you like to list an open advertisement ' .
         'for?<br>';
    echo '<form method="get"><input type="hidden" name="a" value="a">';
    echo '<select name="t">';
    foreach ($pvp_types as $k => $v) {
      echo '<option value="' . $k . '">' . $v . '</option>';
    }
    echo '</select> <input type="submit" value="Post!"></form></p>';

  } else {

    if (!isset($game_type, $pvp_types)) {
      echo '<p>That isn\'t a valid game type!</p>';
    } else {
      $match_count = getPvpMatchCount($char_obj->c['id']);
      if ($match_count >= pvp_maximum_matches) {
        echo '<p>You\'re already involved in your maximum allotment of ' .
             'PVP matches!</p>';
      } else {
        addPvpMatch($char_obj, $game_type);
        echo '<p>Match posted!</p>';
      }
    }
  }

  echo '<p><a href="pvp.php">Return to your match listing</a></p>';

} elseif ('o' == $action) {

  $matches = getPvpOpenAdverts($char_obj);
  $match_count = getPvpMatchCount($char_obj->c['id']);

?>

  <p>Available open games:</p>

  <center>
  <table class="profession">
  <tr>
    <th width="50">ID</th>
    <th width="100">Opponent</th>
    <th width="100">Rating</th>
    <th width="100">Match Type</th>
    <th width="100">Action</th>
  </tr>

<?

  if (count($matches) == 0) {
    echo '<tr><td colspan="5" align="center"><i>There are no available ' .
         'matches!</i></td></tr>';
  } else {

    foreach ($matches as $x) {
      echo '<td>' . $x['id'] . '</td>';
      echo '<td><a href="char.php?i=' . $x['p1_id'] . '">' .
           $x['p1_name'] . '</a></td><td>' . $x['p1_elo'] . '</td>';
      echo '<td>' . $pvp_types[$x['game_type']] . '</td>';
      if ($match_count >= pvp_maximum_matches) {
        echo '<td>Open</td>';
      } else {
        echo '<td>(<a href="pvp.php?a=c&i=' . $x['id'] . '">accept</a>)</td>';
      }
      echo '</tr>';
    }

  }

?>

  </table>
  </center>

<?

  echo '<p><a href="pvp.php">Return to your match listing</a></p>';

} elseif ('c' == $action) {

  $match_id = getGetInt('i', 0);
  $match = getPvpMatch($match_id);
  $match_count = getPvpMatchCount($char_obj->c['id']);

  debugPrint($match);

  if ((FALSE == $match) ||
      (0 != $match['p2_id']) ||
      ($match_count >= pvp_maximum_matches) ||
      ($char_obj->c['id'] == $match['p1_id'])) {
    echo '<p>That match is not available!</p>';
  } else {
    if (claimPvpMatch($char_obj, $match_id)) {
      echo '<p>You accept the challenge!</p>';
    } else {
      echo '<p>Sorry, the match has been claimed!</p>';
    }
  }

  echo '<p><a href="pvp.php">Return to your match listing</a></p>';

} elseif ('v' == $action) {

  foreach ($disp_array as $x) {
    echo $x;
  }

  debugPrint($match);

  echo '<p><a href="pvp.php">Return to your match listing</a></p>';

}

require '_footer.php';
$save = $char_obj->save();
$log_save = $log_obj->save();

?>

</div>
</body>
</html>
