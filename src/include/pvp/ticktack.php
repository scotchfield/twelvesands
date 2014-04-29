<?

function getPvpTickMatch($id) {
  $i = esc($id);

  $query = "
    SELECT
      *
    FROM
      `pvp_ticktack`
    WHERE
     id = $i
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $match = $results->fetch_assoc();
  return $match;
}

function addPvpTickMatch($id) {
  $i = esc($id);

  $query = "
    INSERT INTO
      `pvp_ticktack`
      (id, board)
    VALUES
      ('$i', '0')
  ";
  $results = sqlQuery($query);
}

function updatePvpTickMatch($id, $board) {
  $i = esc($id);
  $b = esc($board);

  $query = "
    UPDATE
      `pvp_ticktack`
    SET
      board = '$b'
    WHERE
      id = '$i'
  ";
  $results = sqlQuery($query);

  return TRUE;
}

function renderTickSquareStr($match_id, $pos, $b, $char_turn) {
  $s = '<td width="50">';
  if ($b & (1 << $pos)) {
    $s = $s . 'X';
  } elseif ($b & (1 << ($pos + 9))) {
    $s = $s . 'O';
  } elseif ($char_turn) {
    $s = $s . '<a href="pvp.php?a=v&i=' . $match_id . '&x=' . $pos .
        '">___</a>';
  } else {
    $s = $s . '&nbsp;';
  }
  return $s . '</td>';
}

function getPvpDisplay($char_obj, $match, $params) {
  $ret_array = array();

  $t_match = getPvpTickMatch($match['id']);

  if ($t_match == FALSE) {
    addPvpTickMatch($match['id']);
    $t_match = array();
    $t_match['id'] = $match['id'];
    $t_match['board'] = 0;
    debugPrint('Creating Tick Tack match id #' . $match['id']);
  }

  $b = $t_match['board'];

  $x = $params['x'];
  $char_turn = getMyTurn($char_obj, $match);
  if (($char_turn) && ($x >= 1) && ($x <= 9)) {
    $move_mod = 0;
    if ($match['p2_id'] == $char_obj->c['id']) {
      $move_mod = 9;
    }
    if ($b & (1 << ($x + $move_mod))) {
      $ret_array[] = '<p>Invalid move!</p>';
    } else {
      $b = $b | (1 << ($x + $move_mod));
      updatePvpTickMatch($match['id'], $b);
      flipMatchTurn($match);
      $char_turn = !($char_turn);
    }
  }

  $ret_array[] = '<br><center><table class="profession" border="1"><tr>';
  $ret_array[] = renderTickSquareStr($match['id'], 1, $b, $char_turn);
  $ret_array[] = renderTickSquareStr($match['id'], 2, $b, $char_turn);
  $ret_array[] = renderTickSquareStr($match['id'], 3, $b, $char_turn);
  $ret_array[] = '</tr><tr>';
  $ret_array[] = renderTickSquareStr($match['id'], 4, $b, $char_turn);
  $ret_array[] = renderTickSquareStr($match['id'], 5, $b, $char_turn);
  $ret_array[] = renderTickSquareStr($match['id'], 6, $b, $char_turn);
  $ret_array[] = '</tr><tr>';
  $ret_array[] = renderTickSquareStr($match['id'], 7, $b, $char_turn);
  $ret_array[] = renderTickSquareStr($match['id'], 8, $b, $char_turn);
  $ret_array[] = renderTickSquareStr($match['id'], 9, $b, $char_turn);
  $ret_array[] = '</tr></table></center>';

  return $ret_array;
}

?>