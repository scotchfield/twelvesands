<?

function getAllDuelChallenges( $char_id ) {
    $c_id = intval( $char_id );
    $time = time() - 300;

    $query = "
      SELECT
        d.*, c.titled_name, c.level
      FROM
        `duel_challenges` AS d, `characters` AS c
      WHERE
        char_id = '$c_id' AND
        created > '$time' AND
        d.target_id = c.id
      ORDER BY
        created
    ";

    $results = sqlQuery( $query );
    $cs = array();
    if ( ! $results ) { return $cs; }

    while ( $c = $results->fetch_assoc() ) {
        $cs[ $c[ 'id' ] ] = $c;
    }

    return $cs;
}

function deleteAllDuelChallenges( $char_id ) {
    $c_id = intval( $char_id );

    $query = "
      DELETE FROM
        `duel_challenges`
      WHERE
        char_id = '$c_id' OR
        target_id = '$c_id'
    ";

    $results = sqlQuery( $query );
}

function deleteDuelChallenge( $challenge_id ) {
    $challenge_id = intval( $challenge_id );

    $query = "
      DELETE FROM
        `duel_challenges`
      WHERE
        id = $challenge_id
    ";

    $results = sqlQuery( $query );
}

function deleteDuelChallengeByValues( $char_id, $target_id, $created ) {
    $char_id = intval( $char_id );
    $target_id = intval( $target_id );
    $created = intval( $created );

    $query = "
      DELETE FROM
        `duel_challenges`
      WHERE
        char_id = $char_id AND
        target_id = $target_id AND
        created = $created
    ";

    $results = sqlQuery( $query );
}

function getDuelChallenge( $c_obj, $challenge_id ) {
    $c = intval( $challenge_id );
    $time = time() - 300;

    $query = "
      SELECT
        *
      FROM
        `duel_challenges`
      WHERE
        char_id = '" . $c_obj->c[ 'id' ] . "' AND
        id = $c AND
        created > '$time'
    ";

    $results = sqlQuery( $query );
    if (!$results) { return FALSE; }

    $challenge = $results->fetch_assoc();
    return $challenge;
}

function getRawDuelChallenge( $challenge_id ) {
    $c = intval( $challenge_id );
    $time = time() - 300;

    $query = "
      SELECT
        *
      FROM
        `duel_challenges`
      WHERE
        id = $c AND
        created > '$time'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $challenge = $results->fetch_assoc();
    return $challenge;
}

function sendDuelChallenge( $to_char_id, $from_char_id ) {
    $tc_id = intval( $to_char_id );
    $fc_id = intval( $from_char_id );
    $time = time();

    $query = "
      INSERT INTO
        `duel_challenges`
        (char_id, target_id, status, created)
      VALUES
        ('$tc_id', '$fc_id', '" . sg_duel_challenge_sent . "', '$time'),
        ('$fc_id', '$tc_id', '" . sg_duel_challenge_recv . "', '$time')
    ";
    $results = sqlQuery( $query );

    unset( $_SESSION[ 'duel_time_check' ] );
}

function getDuelState( $c_id, $state_id ) {
    $s = intval( $state_id );

    $query = "
      SELECT
        *
      FROM
        `duel_states`
      WHERE
        id = '$s'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $state = $results->fetch_assoc();

    if ( $state[ 'char_id_1' ] == $c_id ) {
      $state[ 'target_id' ] = $state[ 'char_id_2' ];
      $state[ 'target_rank' ] = $state[ 'char_rank_2' ];
    } elseif ( $state['char_id_2'] == $c_id ) {
      $state[ 'target_id' ] = $state[ 'char_id_1' ];
      $state[ 'target_rank' ] = $state[ 'char_rank_1' ];
    } else {
      return FALSE;
    }

    return $state;
}

function createDuelState( $char_id_1, $char_rank_1,
                          $char_id_2, $char_rank_2 ) {
    $c_id_1 = intval( $char_id_1 );
    $c_rank_1 = intval( $char_rank_1 );
    $c_id_2 = intval( $char_id_2 );
    $c_rank_2 = intval( $char_rank_2 );
    $state = rand( 1, 2 );
    $time = time();

    $query = "
      INSERT INTO
        `duel_states`
        (char_id_1, char_rank_1, char_id_2, char_rank_2, state, timestamp)
      VALUES
        ('$c_id_1', '$c_rank_1', '$c_id_2', '$c_rank_2', '$state', '$time')
    ";
    $results = sqlQuery( $query );
    return sqlInsertId();
}

function updateDuelState( $state_id, $state_val, $render_text ) {
    $s_id = intval( $state_id );
    $s_val = intval( $state_val );
    $r_text = fixStr( $render_text );
    $time = time();

    $query = "
      UPDATE
        `duel_states`
      SET
        state = '$s_val', render_text = '$r_text', timestamp = '$time'
      WHERE
        id = '$s_id'
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function getDuelRank( $char_id ) {
    $c = intval( $char_id );

    $query = "
      SELECT
        rank
      FROM
        `duel_ladder`
      WHERE
        char_id = '$c'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return 0; }

    $ladder = $results->fetch_assoc();
    return $ladder[ 'rank' ];
}

function addDuelPlayers( $char_id_1, $char_id_2 ) {
    $c1 = intval( $char_id_1 );
    $c2 = intval( $char_id_2 );

    $query = "INSERT INTO `duel_players` (char_id) VALUES ($c1), ($c2)";
    sqlQuery( $query );
}

?>