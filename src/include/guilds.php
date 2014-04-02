<?

require_once 'include/core.php';

require_once sg_base_path . 'include/sql.php';

function getGuildById( $guild_id ) {
    $g = intval( $guild_id );

    $query = "
      SELECT
        *
      FROM
        `guilds`
      WHERE
        id = '$g'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $guild = $results->fetch_assoc();
    $guild[ 'motto' ] = str_replace( '&amp;', '&', $guild[ 'motto' ] );
    $guild[ 'url' ] = str_replace( '&amp;', '&', $guild[ 'url' ] );
    $guild[ 'message' ] = str_replace( '&amp;', '&', $guild[ 'message' ] );
    return $guild;
}

function getGuildByName( $guild_name ) {
    $g = esc( $guild_name );

    $query = "
      SELECT
        *
      FROM
        `guilds`
      WHERE
        name = '$g'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $guild = $results->fetch_assoc();
    $guild[ 'motto' ] = str_replace( '&amp;', '&', $guild[ 'motto' ] );
    $guild[ 'url' ] = str_replace( '&amp;', '&', $guild[ 'url' ] );
    $guild[ 'message' ] = str_replace( '&amp;', '&', $guild[ 'message' ] );
    return $guild;
}

function getGuilds() {
    $query = "
      SELECT
        id, name, leader_id, leader_name, motto
      FROM
        `guilds`
      LIMIT
        40
    ";

    $results = sqlQuery( $query );
    $guilds = array();
    if ( ! $results ) { return $guilds; }

    while ( $g = $results->fetch_assoc() ) {
        $g[ 'motto' ] = str_replace( '&amp;', '&', $g[ 'motto' ] );
        $guilds[ $g[ 'id' ] ] = $g;
    }

    return $guilds;
}

function addGuild( $guild_name, $c_obj ) {
    $g_name = esc( $guild_name );
    $leader_id = $c_obj->c[ 'id' ];
    $leader_name = $c_obj->c[ 'name' ];

    if ( $c_obj->c[ 'guild_id' ] != 0 ) {
        return FALSE;
    }

    $query = "
      INSERT INTO
        `guilds` (name, leader_id, leader_name)
      VALUES
        ('$g_name', '$leader_id', '$leader_name')
    ";
    $results = sqlQuery( $query );

    $guild = getGuildByName( $g_name );
    if ( FALSE != $guild ) {
        addGuildMember( $c_obj, $guild[ 'id' ], 1 );
    } else {
        return FALSE;
    }

    return TRUE;
}

function updateGuildLeader( $guild_id, $leader_id, $leader_name ) {
    $g_id = intval( $guild_id );
    $l_id = intval( $leader_id );
    $l_name = esc( $leader_name );

    $query = "
      UPDATE
        `guilds`
      SET
        leader_id = $l_id, leader_name = '$l_name'
      WHERE
        id = $g_id
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function addGuildMember( $c_obj, $guild_id, $rank ) {
    $g_id = intval( $guild_id );
    $rank = intval( $rank );
    $char_id = $c_obj->c[ 'id' ];
    $char_name = $c_obj->c[ 'name' ];

    $query = "
      INSERT INTO
        `guild_chars` (guild_id, char_id, char_name, rank)
      VALUES
        ('$g_id', '$char_id', '$char_name', '$rank')
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function removeGuildMember( $c_obj, $guild_id ) {
    $g_id = intval( $guild_id );
    $char_id = $c_obj->c[ 'id' ];

    $query = "
      DELETE FROM
        `guild_chars`
      WHERE
        char_id = $char_id AND guild_id = $g_id
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function updateGuildMember( $c_obj, $guild_id, $rank ) {
    $g_id = intval( $guild_id );
    $rank = intval( $rank );
    $char_id = $c_obj->c[ 'id' ];

    $query = "
      UPDATE
        `guild_chars`
      SET
        rank = $rank
      WHERE
        guild_id = $g_id AND char_id = $char_id
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function getGuildMembers( $guild_id ) {
    $g_id = intval( $guild_id );

    $query = "
      SELECT
        g.*, c.level, c.d_id
      FROM
        `guild_chars` AS g, `characters` AS c
      WHERE
        c.id = g.char_id AND
        g.guild_id = $g_id
      ORDER BY
        g.rank ASC
    ";

    $results = sqlQuery( $query );
    $chars = array();
    if ( ! $results ) { return $chars; }

    while ( $c = $results->fetch_assoc() ) {
        $chars[] = $c;
    }

    return $chars;
}

function updateGuildRanks( $guild_id, $r1, $r2, $r3, $r4, $r5 ) {
    $g_id = intval( $guild_id );
    $r1 = esc( $r1 );
    $r2 = esc( $r2 );
    $r3 = esc( $r3 );
    $r4 = esc( $r4 );
    $r5 = esc( $r5 );

    $query = "
      UPDATE
        `guilds`
      SET
        rank_1 = '$r1',
        rank_2 = '$r2',
        rank_3 = '$r3',
        rank_4 = '$r4',
        rank_5 = '$r5'
      WHERE
        id = $g_id
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function updateGuildMessages( $guild_id, $motto, $url, $message ) {
    $g_id = intval( $guild_id );
    $motto = fixStr( $motto );
    $url = fixStr( $url );
    $message = fixStr( $message );

    $query = "
      UPDATE
        `guilds`
      SET
        motto = '$motto',
        url = '$url',
        message = '$message'
      WHERE
        id = $g_id
    ";
    $results = sqlQuery( $query );
}

?>