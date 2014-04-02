<?

function getRune( $rune_id ) {
    $rune_id = intval( $rune_id );
    $query = "SELECT * FROM `runes` WHERE id = $rune_id";
    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $rune = $results->fetch_assoc();
    $rune[ 'name' ] = fixStr( $rune[ 'name' ] );
    $rune[ 'text' ] = fixStr( $rune[ 'text' ] );
    return $rune;
}

function hasRune( $c_obj, $i ) {
    if ( 0 == $i ) { return TRUE; }
    if ( array_key_exists( $i, $c_obj->c[ 'runes' ] ) ) {
        return $c_obj->c[ 'runes' ][ $i ];
    }
    return FALSE;
}

function addRune( $c_obj, $r_id ) {
    $c_id = $c_obj->c[ 'id' ];
    $r_id = intval( $r_id );
    $query = "
      INSERT INTO
        `char_runes` (char_id, rune_id)
      VALUES
        ('$c_id', '$r_id')
    ";
    $results = sqlQuery( $query );
    unset( $_SESSION[ 'runes' ] );
}

function deleteRune( $c_obj, $r_id ) {
    $c_id = $c_obj->c[ 'id' ];
    $r_id = intval( $r_id );
    $query = "
      DELETE FROM `char_runes` WHERE char_id = $c_id AND rune_id = $r_id
    ";
    $results = sqlQuery( $query );
    unset( $_SESSION[ 'runes' ] );
    return TRUE;
}

function deleteAllRunes( $c_obj ) {
    $c_id = $c_obj->c[ 'id' ];
    $query = "
      DELETE FROM `char_runes` WHERE char_id = $c_id
    ";
    $results = sqlQuery( $query );
    unset( $_SESSION[ 'runes' ] );
    return TRUE;
}

?>