<?

require_once 'include/core.php';

require_once sg_base_path . 'include/sql.php';

class Logger {
    function Logger() {
        $this->logs = array();
    }

    function addLog( $c, $action, $target_id,
                     $status_1, $status_2, $status_3 ) {
        $a = esc( $action );
        $t = esc( $target_id );
        $s1 = esc( $status_1 );
        $s2 = esc( $status_2 );
        $s3 = esc( $status_3 );

        $time = time();
        $ip_addr = $_SERVER[ 'REMOTE_ADDR' ];

        $log = "('$time', '$ip_addr', '$a', '$t', " .
               "'$s1', '$s2', '$s3', " .
               "'" . $c[ 'id' ] . "', '" . $c[ 'current_hp' ] .
               "', '" . $c[ 'level' ] . "')";

        $this->logs[] = $log;
    }

    function save() {
        if ( count( $this->logs ) < 1 ) { return FALSE; }

        $query = "
          INSERT INTO
            `logs` (timestamp, ip_addr, action, target_id,
                    status_1, status_2, status_3,
                    char_id, char_hp, char_level)
          VALUES
        " . join( ',', $this->logs );
        $results = sqlQuery( $query );

        //debugPrint( '<font size="-2">Logger Saved (' . count( $this->logs ) .
        //    ')</font>' );

        return TRUE;
    }
}

?>