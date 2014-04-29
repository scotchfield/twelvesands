<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';

define('sg_pvp_match_started', 0);
define('sg_pvp_p1_turn', 1);
define('sg_pvp_p2_turn', 2);
define('sg_pvp_match_over', 3);

$pvp_types = array(
  1 => 'Tick Tack',
  2 => 'Brawl'
);

define('pvp_maximum_matches', 5);

function getPvpMatches($c_obj) {
  $c = esc($c_obj->c['id']);

  $query = "
    SELECT
      *
    FROM
      `pvp`
    WHERE
      p1_id = $c OR p2_id = $c
    ORDER BY
     id
  ";

  $results = sqlQuery($query);
  $matches = array();
  if (!$results) { return $matches; }

  while ($a = $results->fetch_assoc()) {
    $matches[$a['id']] = $a;
  }

  return $matches;
}

function getPvpOpenAdverts($char_obj) {
  $c_id = esc($char_obj->c['id']);

  $query = "
    SELECT
      *
    FROM
      `pvp`
    WHERE
      game_state = 0 AND
      p1_id != $c_id AND
      p2_id = 0
    ORDER BY
     id
  ";

  $results = sqlQuery($query);
  $matches = array();
  if (!$results) { return $matches; }

  while ($a = $results->fetch_assoc()) {
    $matches[$a['id']] = $a;
  }

  return $matches;
}

function getPvpMatch($id) {
  $i = esc($id);

  $query = "
    SELECT
      *
    FROM
      `pvp`
    WHERE
     id = $i
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $match = $results->fetch_assoc();
  return $match;
}

function addPvpMatch($c_obj, $game_type) {
  $c_id = esc($c_obj->c['id']);
  $c_name = esc($c_obj->c['name']);
  $gt = esc($game_type);

  $query = "
    INSERT INTO
      `pvp`
      (p1_id, p1_name, p1_elo, game_type, game_move, game_state)
    VALUES
      ('$c_id', '$c_name', '1200', '$gt', '0', '0')
  ";
  $results = sqlQuery($query);
}

function deletePvpMatch($id) {
  $i = esc($id);

  $query = "
    DELETE FROM
      `pvp`
    WHERE
      id = '$i'
  ";
  $results = sqlQuery($query);

  return TRUE;
}

function getPvpMatchCount($char_id) {
  $c_id = esc($char_id);

  $query = "
    SELECT
      COUNT(*) AS matches
    FROM
      `pvp`
    WHERE
      p1_id = $c_id OR p2_id = $c_id
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $x = $results->fetch_assoc();
  return $x['matches'];
}

function claimPvpMatch($char_obj, $match_id) {
  $c_id = esc($char_obj->c['id']);
  $c_name = esc($char_obj->c['name']);
  $m_id = esc($match_id);

  $query = "
    UPDATE
      `pvp`
    SET
      p2_id = '$c_id',
      p2_name = '$c_name',
      p2_elo = '1200',
      game_state = 3
    WHERE
      id = '$m_id' AND p2_id = '0' AND game_state = 0
  ";
  $results = sqlQuery($query);

  return TRUE;
}

function flipMatchTurn($match) {
  $m_id = intval($match['id']);

  $game_state = $match['game_state'];
  if ($game_state & (1 << sg_pvp_p1_turn)) {
    $game_state -= (1 << sg_pvp_p1_turn);
    $game_state |= (1 << sg_pvp_p2_turn);
  } else {
    $game_state -= (1 << sg_pvp_p2_turn);
    $game_state |= (1 << sg_pvp_p1_turn);
  }

  $query = "
    UPDATE
      `pvp`
    SET
      game_state = $game_state
    WHERE
      id = '$m_id'
  ";
  $results = sqlQuery($query);

  return TRUE;
}

function getMyTurn($char_obj, $match) {
  if (($match['p1_id'] != $char_obj->c['id']) &&
      ($match['p2_id'] != $char_obj->c['id'])) {
    return FALSE;
  }

  $p1_self = TRUE;
  if ($match['p2_id'] == $char_obj->c['id']) {
    $p1_self = FALSE;
  }

  if ($match['game_state'] & (1 << sg_pvp_match_started)) {
    if ((($p1_self == TRUE) &&
         ($match['game_state'] & (1 << sg_pvp_p1_turn))) ||
        (($p1_self == FALSE) &&
         ($match['game_state'] & (1 << sg_pvp_p2_turn)))) {
      return TRUE;
    }
  }

  return FALSE;
}

?>