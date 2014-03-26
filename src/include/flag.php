<?

require_once 'include/core.php';

require_once sg_base_path . 'include/sql.php';

class FlagUpdater {
    function FlagUpdater() {
        $this->flags = array();
    }

    function addFlag( $c_obj, $flag_id, $flag_value ) {
        $i = esc( $flag_id );
        $v = esc( $flag_value );

        $this->flags[ $i ] = $v;
        $c_obj->c[ 'flags' ][ $flag_id ] = $flag_value;
    }

    function enableFlagBit( $c_obj, $flag_id, $flag_bit ) {
        $s_bit = ( 1 << $flag_bit );
        $new_value = getFlagValue( $c_obj, $flag_id ) | $s_bit;
        $this->addFlag( $c_obj, $flag_id, $new_value );
    }

    function disableFlagBit( $c_obj, $flag_id, $flag_bit ) {
        $s_bit = ( 1 << $flag_bit );
        $new_value = getFlagValue( $c_obj, $flag_id ) & ( ~$s_bit );
        $this->addFlag( $c_obj, $flag_id, $new_value );
    }

    function save( $c_obj ) {
        if ( count( $this->flags ) < 1 ) { return FALSE; }

        $query = "
          DELETE FROM
            `char_flags`
          WHERE
            char_id = " . $c_obj->c[ 'id' ] . " AND
            flag_id IN (" . join( ',', array_keys( $this->flags ) ) . ")
        ";
        $results = sqlQuery( $query );

        $flag_updates = array();
        foreach ( $this->flags as $k => $v ) {
            if ( $v != 0 ) {
                $flag_updates[] = "('" . $c_obj->c[ 'id' ] . "', '$k', '$v')";
            }
        }

        if ( count( $flag_updates ) > 0 ) {
            $query = "
              INSERT INTO
                `char_flags` (char_id, flag_id, flag_value)
              VALUES
            " . join( ',', $flag_updates );
            $results = sqlQuery( $query );
        }

        unset( $_SESSION[ 'flags' ] );

        return TRUE;
    }
}

function getFlagValue( $c_obj, $flag_id ) {
    if ( ! array_key_exists( $flag_id, $c_obj->c[ 'flags' ] ) ) {
        return 0;
    }
    return $c_obj->c[ 'flags' ][ $flag_id ];
}

function getFlagBit( $c_obj, $flag_id, $flag_bit ) {
    if ( getFlagValue( $c_obj, $flag_id ) & ( 1 << $flag_bit ) ) {
        return TRUE;
    }
    return FALSE;
}

function getBit( $val, $bit ) {
    if ( $val & ( 1 << $bit ) ) {
        return TRUE;
    }
    return FALSE;
}

function bitCount( $val ) {
    $v = ( int ) $val;
    $c = 0;

    for ( $c = 0; $v; $c++ ) {
        $v &= $v - 1;
    }

    return $c;
}

function decreaseGameFlag( $flag_id ) {
    $flag_id = intval( $flag_id );

    $query = "
      UPDATE
        `game_flags`
      SET
        flag_value = flag_value - 1
      WHERE
        flag_id = $flag_id AND flag_value > 0
    ";
    sqlQuery( $query );
}

?>