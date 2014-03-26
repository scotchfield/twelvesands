<?

require_once 'include/core.php';

require_once sg_base_path . 'include/sql.php';

function getSession( $session_id ) {
    $s = esc( $session_id );

    $query = "
      SELECT
        *
      FROM
        `sessions`
      WHERE
        session_id = '$s'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $session = $results->fetch_assoc();
    return $session;
}

function getSessionByCharId( $char_id ) {
    $c = intval( $char_id );

    $query = "
      SELECT
        *
      FROM
        `sessions`
      WHERE
        char_id = '$c'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $session = $results->fetch_assoc();
    return $session;
}

function addSession( $session_id, $user_id, $username, $password ) {
    $s = esc( $session_id );
    $u_id = intval( $user_id );
    $u = esc( $username );
    $p = esc( $password );
    $time = time();

    $query = "
      DELETE FROM
        `sessions`
      WHERE
        user_id = '$u_id'
    ";
    $results = sqlQuery( $query );

    deleteSession( $s );

    $query = "
      INSERT INTO
        `sessions` (session_id, user_id, username, password, timestamp)
      VALUES
        ('$s', '$u_id', '$u', '$p', '$time')
    ";
    $results = sqlQuery( $query );
}

function updateSession( $session_id, $update_chat ) {
    $s = esc( $session_id );
    $time = time();

    $time_query = '';
    if ( TRUE == $update_chat ) {
        $time_query = "timestamp_chat = '$time'";
    } else {
        $time_query = "timestamp = '$time'";
    }

    $query = "
      UPDATE
        `sessions`
      SET
        $time_query
      WHERE
        session_id = '$s'
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function updateSessionChar( $session_id, $char_name, $char_id ) {
    $s = fixStr( $session_id );
    $c_name = fixStr( $char_name );
    $c_id = intval( $char_id );

    $query = "
      UPDATE
        `sessions`
      SET
        char_name = '$c_name', char_id = '$c_id'
      WHERE
        session_id = '$s'
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function deleteSession( $session_id ) {
    $s = esc( $session_id );

    $query = "
      DELETE FROM
        `sessions`
      WHERE
        session_id = '$s'
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function getOnlineCharacters() {
    $time = time() - 300;

    $query = "
      SELECT
        s.char_id, s.char_name
      FROM
        `sessions` AS s
      WHERE
        (s.timestamp > $time OR s.timestamp_chat > $time) AND s.char_id > 0
      ORDER BY
        s.char_name
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $chars = array();

    while ( $c = $results->fetch_assoc() ) {
        $chars[ $c[ 'char_id' ] ] = $c[ 'char_name' ];
    }

    return $chars;
}

?>