<?

require_once 'include/core.php';

require_once sg_base_path . 'include/sql.php';

function addBuilder( $c_obj, $b_obj ) {
    $char_id = intval( $c_obj->c[ 'id' ] );
    $user_id = intval( $c_obj->c[ 'user_id' ] );
    $type = intval( $b_obj[ 'type' ] );
    $subtype = intval( $b_obj[ 'subtype' ] );
    $title = esc( $b_obj[ 'title' ] );
    $desc = esc( $b_obj[ 'description' ] );
    $attack = esc( $b_obj[ 'attack' ] );
    $res = esc( $b_obj[ 'resistances' ] );
    $damage = esc( $b_obj[ 'damage' ] );
    $value = esc( $b_obj[ 'value' ] );
    $misc = esc( $b_obj[ 'misc' ] );

    $query = "INSERT INTO `builders`
        (char_id, user_id, type, subtype, title, description, attack,
         resistances, damage, value, misc, score, state)
        VALUES
        ($char_id, $user_id, $type, $subtype, '$title', '$desc', '$attack',
         '$res', '$damage', '$value', '$misc', 0, 1)";
    sqlQuery( $query );
    return sqlInsertId();
}

function getBuilder( $id ) {
    $id = intval( $id );
    $query = "SELECT b.*, c.name AS char_name
        FROM `builders` AS b, `characters` AS c
        WHERE b.id=$id AND b.char_id=c.id";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $b_obj = $results->fetch_assoc();

    $query = "SELECT b.*, c.name AS char_name
        FROM `builder_votes` AS b, `characters` AS c
        WHERE b.builder_id=$id AND b.char_id=c.id";
    $results = sqlQuery( $query );
    $obj = array();
    $b_obj[ 'voted' ] = array();
    if ( $results ) {
        while ( $o = $results->fetch_assoc() ) {
            $obj[] = $o;
            if ( $o[ 'score' ] > 0 ) {
                $b_obj[ 'voted' ][ $o[ 'user_id' ] ] = TRUE;
            }
        }
    }
    $b_obj['comments'] = $obj;

    return $b_obj;
}

function getAllBuilders() {
    $query = "SELECT b.*, c.name AS char_name
        FROM `builders` AS b, `characters` AS c
        WHERE b.char_id=c.id ORDER BY b.score DESC";

    $results = sqlQuery( $query );
    $obj = array();
    if ( ! $results ) { return $obj; }

    while ( $o = $results->fetch_assoc() ) {
        $obj[ $o[ 'id' ] ] = $o;
    }

    return $obj;
}

function addBuilderComment( $c_obj, $b_id, $score, $comment ) {
    $char_id = intval( $c_obj->c[ 'id' ] );
    $user_id = intval( $c_obj->c[ 'user_id' ] );
    $b_id = intval( $b_id );
    $score = intval( $score );
    $comment = esc( $comment );
    $time = time();
    if ( ( $score < 0 ) || ( $score > 5 ) ) {
        return FALSE;
    }
    $query = "INSERT INTO `builder_votes`
        (builder_id, char_id, user_id, score, comment, timestamp)
        VALUES ($b_id, $char_id, $user_id, $score, '$comment', $time)";
    sqlQuery( $query );
}

function zeroBuilderUserScore( $c_obj, $b_id ) {
    $user_id = intval( $c_obj->c[ 'user_id' ] );
    $b_id = intval( $b_id );
    $query = "UPDATE `builder_votes` SET score=0
        WHERE builder_id=$b_id AND user_id=$user_id";
    sqlQuery( $query );
}

function updateBuilderScore( $b_id ) {
    $b_id = intval( $b_id );
    $query = "SELECT AVG(score) AS average
        FROM `builder_votes` WHERE builder_id=$b_id AND score>0";
    $results = sqlQuery( $query );
    $obj = $results->fetch_assoc();
    $score = $obj[ 'average' ];

    $query = "UPDATE `builders` SET score=$score WHERE id=$b_id";
    sqlQuery( $query );
}

function getCharBuilderCount( $c_obj ) {
    $user_id = intval( $c_obj->c[ 'user_id' ] );
    $query = "SELECT COUNT(*) AS count FROM `builders`
        WHERE user_id=$user_id AND state=1";
    $results = sqlQuery( $query );
    $obj = $results->fetch_assoc();
    return $obj[ 'count' ];
}

?>