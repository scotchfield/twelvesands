<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/mails.php';
require_once sg_base_path . 'include/session.php';
require_once sg_base_path . 'include/sql.php';

function getCharIdForUser( $user_id ) {
    $u = intval( $user_id );
    $query = "SELECT id FROM `characters` WHERE user_id=$u";
    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }
    $user = $results->fetch_assoc();
    return $user[ 'id' ];
}

function getUserIdForCharId( $char_id ) {
    $char_id = intval( $char_id );
    $query = "SELECT user_id FROM `characters` WHERE id = $char_id";
    $results = sqlQuery( $query );
    if ( ! $results ) { return 0; }
    $obj = $results->fetch_assoc();
    return $obj[ 'user_id' ];
}

function getTitledNameById( $c_id ) {
    $c_id = intval( $c_id );
    $query = "SELECT titled_name FROM `characters` WHERE id=$c_id";
    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }
    $char = $results->fetch_assoc();
    return $char[ 'titled_name' ];
}

function getCharNameById( $c_id ) {
    $c_id = intval( $c_id );
    $query = "SELECT name FROM `characters` WHERE id=$c_id";
    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }
    $char = $results->fetch_assoc();
    return $char[ 'name' ];
}

function getCharactersForUser( $user_id ) {
    $u = intval( $user_id );

    $query = "
      SELECT
        *
      FROM
        `characters`
      WHERE
        user_id = '$u'
    ";

    $results = sqlQuery( $query );
    $chars = array();
    if ( ! $results ) { return $chars; }

    while ( $c = $results->fetch_assoc() ) {
        $chars[ $c[ 'id' ] ] = $c;
    }

    return $chars;
}

function getCharIdForCharName( $char_name ) {
    $cn = esc( $char_name );

    $query = "
      SELECT
        id
      FROM
        `characters`
      WHERE
        name = '$cn'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $user = $results->fetch_assoc();
    return $user[ 'id' ];
}

function getCharIdAndUserIdForCharName( $char_name ) {
    $cn = esc( $char_name );
    $query = "SELECT id, user_id FROM `characters` WHERE name = '$cn'";
    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }
    $user = $results->fetch_assoc();
    return $user;
}

function getCharactersByName( $char_name ) {
    $cn = fixStr( $char_name );
    $chars = array();

    if ( strlen( $cn ) < 3 ) {
        return $chars;
    }

    $query = "
      SELECT
        id, titled_name
      FROM
        `characters`
      WHERE
        name LIKE '%$cn%'
      ORDER BY
        titled_name ASC
      LIMIT 20;
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return $chars; }

    while ( $c = $results->fetch_assoc() ) {
        $chars[ $c[ 'id' ] ] = $c[ 'titled_name' ];
    }

    return $chars;
}

function getUser( $user_name ) {
    $u = esc( $user_name );

    $query = "
      SELECT
        *
      FROM
        `users`
      WHERE
        name = '$u'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $user = $results->fetch_assoc();
    return $user;
}

function getUserByEmail( $email ) {
    $e = esc( $email );

    $query = "
      SELECT
        *
      FROM
        `users`
      WHERE
        email = '$e'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $user = $results->fetch_assoc();
    return $user;
}

function setUserPassword( $user_id, $pass_hash ) {
    $u = intval( $user_id );
    $p = esc( $pass_hash );

    $query = "
      UPDATE
        `users`
      SET
        password = '$p'
      WHERE
        id = '$u'
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function setUserMaxChars( $user_id, $max_chars ) {
    $u = intval( $user_id );
    $m = intval( $max_chars );
    $query = "UPDATE `users` SET max_chars=$max_chars WHERE id=$u";
    $results = sqlQuery( $query );
    return TRUE;
}

function updateUserLastLogin( $user_id ) {
    $u = intval( $user_id );
    $time = time();
    $ip_addr = $_SERVER[ 'REMOTE_ADDR' ];

    $query = "
      UPDATE
        `users`
      SET
        last_login = $time, last_ip_addr = '$ip_addr'
      WHERE
        id = '$u'
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function updateCharLastLogin( $char_id ) {
    $c = intval( $char_id );
    $time = time();

    $query = "
      UPDATE
        `characters`
      SET
        last_login = $time
      WHERE
        id = '$c'
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function getUserById( $user_id ) {
    $u = intval( $user_id );

    $query = "
      SELECT
        *
      FROM
        `users`
      WHERE
        id = '$u'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $user = $results->fetch_assoc();
    return $user;
}

function setActiveCharacter( $char_id ) {
    $c_id = intval( $char_id );

    $char_obj = new Char( $c_id );

    $_SESSION[ 'c' ] = $c_id;
    $_SESSION[ 'n' ] = $char_obj->c[ 'name' ];

    $_SESSION[ 'cc' ] = $char_obj->c[ 'chat_channel' ];
    $_SESSION[ 'cc_type' ] = $char_obj->c[ 'chat_channel_type' ];
    $_SESSION[ 'time_check' ] = time();

    $char_obj->enableFlagBit( sg_flag_ui, sg_flagui_show_tip );
    $save = $char_obj->save();

    updateSessionChar( session_id(), $char_obj->c[ 'name' ], $char_obj->c[ 'id' ] );
}

function createCharacter( $user_id, $char_name ) {
    $u_id = intval( $user_id );
    $c_name = fixStr( $char_name );
    $time = time();

    $query = "
      INSERT INTO
        `characters`
        (`user_id`, `name`, `titled_name`, `level`,
         `str`, `dex`, `int`, `cha`, `con`,
         `base_hp`, `current_hp`, `fatigue`, `gold`,
         `weapon`, `armour_chest`, `armour_legs`)
      VALUES
        ('" . $u_id . "', '" . $c_name . "', '" .
         $c_name . "', '1',
         '9', '9', '9', '9', '9',
         '12', '12', '0', '2000',
         '3', '8', '9')
    ";
    $results = sqlQuery( $query );

    $char_id = sqlInsertId();

    $query = "
      INSERT INTO
        `char_flags`
        (`char_id`, `flag_id`, `flag_value`)
      VALUES
        ($char_id, 4, 2147483647)
    ";
    $results = sqlQuery( $query );

    sendMail( $char_id, 0, 'Guardsman Grant', 'Welcome to Capital City!',
        'Hello, and welcome!  My name is Guardsman Grant, and it\'s my job ' .
        'to greet the new adventurers in town.  Please accept this token of ' .
        'our gratitude -- the finest loaves from our local baker.  When ' .
        'you\'re feeling fatigued after a few combats, you can eat one of ' .
        'these to restore your strength and set you back on the path to ' .
        'clearing up the lands.  Take good care, adventurer!',
        45, 5, 0, $time );
}

?>