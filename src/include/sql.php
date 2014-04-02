<?

require_once 'include/core.php';

class DB {
    function DB() {
        $this->host = sg_db_address;
        $this->db = sg_db_name;
        $this->user = sg_db_user;
        $this->pass = sg_db_password;
        $this->port = sg_db_port;

        $this->link = new mysqli( $this->host, $this->user, $this->pass, $this->db, $this->port );

        if ( ! $this->link ) {
            die( "Error!  Could not connect to server: " . mysql_error() );
        }
    }
}

$GLOBALS[ 'ts_db' ] = new DB();

define( 'sg_sql_debug', 0 );

function sqlQuery( $q ) {
    global $ts_db;

    if ( 1 == sg_sql_debug ) {
        $debug_time_start = debugTime();
    }

    $results = $ts_db->link->query( $q );

    if ( 1 == sg_sql_debug ) {
      $debug_time_diff = debugTime() - $debug_time_start;
      debugPrint( 'Query: ' . $q . ' (' .
          number_format( $debug_time_diff, 2, ".", "." ) . 's)' );
    }

    if ( ( ! $results ) ) { // || ( ! is_resource( $results ) ) ) {
        return FALSE;
    }
    // ... TODO: replace this
    //if ( ! mysql_num_rows( $results ) ) {
    //    return FALSE;
    //}

    return $results;
}

function sqlInsertId() {
    global $ts_db;

    return $ts_db->link->insert_id;
}

function esc( $st ) {
    global $ts_db;

    if ( is_string( $st ) ) {
        $st = $ts_db->link->real_escape_string( $st );
    }

    return $st;
}

?>