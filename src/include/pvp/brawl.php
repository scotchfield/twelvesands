<?

function getPvpBrawlMatch($id) {
/*  $i = esc($id);

  $query = "
    SELECT
      *
    FROM
      `pvp_brawl`
    WHERE
     id = $i
  ";

  $results = sqlQuery($query);
  if (!$results) { return FALSE; }

  $match = $results->fetch_assoc();
  return $match;*/
}

function addPvpBrawlMatch($id) {
/*  $i = esc($id);

  $query = "
    INSERT INTO
      `pvp_brawl`
      (id, board)
    VALUES
      ('$i', '0')
  ";
  $results = sqlQuery($query);*/
}

/*function updatePvpBrawlMatch($id, $board) {
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
}*/

function getPvpDisplay($char_obj, $match, $params) {
  $ret_array = array();

  $t_match = getPvpBrawlMatch($match['id']);

  if ($t_match == FALSE) {
/*    addPvpTickMatch($match['id']);
    $t_match = array();
    $t_match['id'] = $match['id'];
    $t_match['board'] = 0;*/
    debugPrint('Creating Brawl match id #' . $match['id']);
  }

  return $ret_array;
}

?>