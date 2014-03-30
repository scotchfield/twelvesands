<?

function getAllMail( $c_obj ) {
    $time = time();

    $query = "
      SELECT
        *
      FROM
        `mail`
      WHERE
        to_char_id = '" . $c_obj->c[ 'id' ] . "' AND
        status < 10 AND
        created <= $time
      ORDER BY
        created DESC
    ";

    $results = sqlQuery( $query );
    $mail = array();
    if ( ! $results ) { return $mail; }

    while ( $m = $results->fetch_assoc() ) {
        $mail[ $m[ 'id' ] ] = $m;
    }

    return $mail;
}

function getMail( $c_obj, $m_id ) {
    $m = esc( $m_id );
    $time = time();

    $query = "
      SELECT
        *
      FROM
        `mail`
      WHERE
        to_char_id = '" . $c_obj->c[ 'id' ] . "' AND
        id = $m AND
        status < 10 AND
        created <= $time
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $mail = $results->fetch_assoc();
    $mail[ 'text' ] = str_replace( "\n", '<br>', $mail[ 'text' ] );
    return $mail;
}

function getMailCount( $char_id ) {
    $c_id = esc( $char_id );
    $time = time();

    $query = "
      SELECT
        COUNT(*) AS new_messages
      FROM
        `mail`
      WHERE
        to_char_id = '$c_id' AND
        status = '1' AND
        created <= $time
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $mail = $results->fetch_assoc();
    return $mail[ 'new_messages' ];
}

function sendMail( $to_char_id, $from_char_id, $from_char_name,
                   $subject, $text, $artifact_id, $artifact_quantity,
                   $artifact_enc, $time ) {
    $tc_id = esc( $to_char_id );
    $fc_id = esc( $from_char_id );
    $fc_name = esc( $from_char_name );
    $s = esc( $subject );
    $t = esc( $text );
    $a_id = esc( $artifact_id );
    $a_q = esc( $artifact_quantity );
    $a_e = esc( $artifact_enc );
    $time = intval( $time );

    if ( $a_q < 0 ) { $a_q = 0; }

    $query = "
      INSERT INTO
        `mail`
        (to_char_id, from_char_id, from_char_name, subject, text,
         artifact_id, artifact_quantity, artifact_enc, status, created)
      VALUES
        ('$tc_id', '$fc_id', '$fc_name', '$s', '$t',
         '$a_id', '$a_q', '$a_e', '1', '$time')
    ";
    $results = sqlQuery( $query );
}

function deleteMail( $c_obj, $id ) {
    $i = esc( $id );

    $query = "
      UPDATE
        `mail`
      SET
        status = 10
      WHERE
        to_char_id = '" . $c_obj->c[ 'id' ] . "' AND
        id = '$i' AND
        (((artifact_quantity > 0) AND (status != 1)) OR
         (artifact_quantity = 0))
    ";
    $results = sqlQuery($query);

    return TRUE;
}

function deleteMailArray( $c_obj, $id_obj ) {
    $i = fixStr( join( ',', $id_obj ) );

    $query = "
      UPDATE
        `mail`
      SET
        status = 10
      WHERE
        to_char_id = '" . $c_obj->c[ 'id' ] . "' AND
        id IN ($i) AND
        (((artifact_quantity > 0) AND (status != 1)) OR
         (artifact_quantity = 0))
    ";
    $results = sqlQuery( $query );

    return TRUE;
}

function markMailAsRead( $c_obj, $id ) {
    $i = esc( $id );

    $query = "
      UPDATE
        `mail`
      SET
        status = '0'
      WHERE
        to_char_id = '" . $c_obj->c[ 'id' ] . "' AND
        id = '$i'
    ";
    $results = sqlQuery( $query );

    unset( $_SESSION[ 'mail_time_check' ] );

    return TRUE;
}

?>