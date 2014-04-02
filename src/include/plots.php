<?

require_once 'include/core.php';

require_once sg_base_path . 'include/flag.php';
require_once sg_base_path . 'include/sql.php';

function getPlot( $plot_id ) {
    $plot_id = intval( $plot_id );
    $query = "SELECT p.*, c.name AS char_name
        FROM `plots` AS p, `characters` AS c
        WHERE p.id=$plot_id AND c.id=p.char_id";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }
    $plot = $results->fetch_assoc();

    $query = "SELECT * FROM plot_flags WHERE plot_id=$plot_id";
    $results = sqlQuery( $query );
    $obj = array();
    if ( $results ) {
        while ( $o = $results->fetch_assoc() ) {
            $obj[ $o[ 'flag' ] ] = $o[ 'value' ];
        }
    }
    $plot[ 'flags' ] = $obj;

    $query = "SELECT * FROM plot_players WHERE plot_id=$plot_id";
    $results = sqlQuery( $query );
    $obj = array( $plot[ 'char_id' ] => TRUE );
    if ( $results ) {
        while ( $o = $results->fetch_assoc() ) {
            $obj[ $o[ 'char_id' ] ] = TRUE;
        }
    }
    $plot[ 'players' ] = $obj;

    return $plot;
}

function getAllPlots( $char_id ) {
    $char_id = intval( $char_id );
    $query = "SELECT * FROM `plots` WHERE char_id=$char_id";

    $results = sqlQuery( $query );
    $obj = array();
    if ( ! $results ) { return $obj; }

    while ( $o = $results->fetch_assoc() ) {
        $obj[ $o[ 'id' ] ] = $o;
    }

    return $obj;
}

function getAllZonePlots( $plot_zone_id ) {
    $id = intval( $plot_zone_id );
    $query = "SELECT p.*, c.name AS char_name
        FROM `plots` AS p, `characters` AS c
        WHERE plot_zone=$id AND c.id=p.char_id
        ORDER BY value";

    $results = sqlQuery( $query );
    $obj = array();
    if ( ! $results ) { return $obj; }

    while ( $o = $results->fetch_assoc() ) {
        $obj[ $o[ 'id' ] ] = $o;
    }

    return $obj;
}

function updatePlot( $plot_id, $title, $description ) {
    $plot_id = intval( $plot_id );
    $title = esc( $title );
    $description = esc( $description );
    $query = "UPDATE `plots` SET title='$title', description='$description'
        WHERE id=$plot_id";
    $results = sqlQuery( $query );
}

function addPlot( $c_obj, $plot_zone, $title, $description ) {
    $c_id = $c_obj->c[ 'id' ];
    $plot_zone = intval( $plot_zone );
    $title = esc( $title );
    $description = esc( $description );
    $query = "INSERT INTO `plots` (char_id, plot_zone, title, description)
        VALUES ($c_id, $plot_zone, '$title', '$description')";
    $results = sqlQuery( $query );
    return sqlInsertId();
}

function getPlotFlagValue( $plot, $flag_id ) {
    if ( ! array_key_exists( $flag_id, $plot[ 'flags' ] ) ) {
        return 0;
    }
    return $plot[ 'flags' ][ $flag_id ];
}

function getPlotFlagBit( $plot, $flag_id, $flag_bit ) {
    if ( getPlotFlagValue( $plot, $flag_id ) & ( 1 << $flag_bit ) ) {
        return TRUE;
    }
    return FALSE;
}

function setPlotFlag( $plot_id, $flag_id, $value ) {
    $plot_id = intval( $plot_id );
    $flag_id = intval( $flag_id );
    $value = intval( $value );

    $query = "DELETE FROM `plot_flags`
        WHERE plot_id=$plot_id AND flag=$flag_id";
    sqlQuery( $query );

    $query = "INSERT INTO `plot_flags` (plot_id, flag, value)
        VALUES ($plot_id, $flag_id, $value)";
    sqlQuery( $query );
}

function addPlotGuestbook( $plot_id, $char_id, $message ) {
    $plot_id = intval( $plot_id );
    $char_id = intval( $char_id );
    $message = esc( $message );
    $query = "INSERT INTO `plot_guestbooks` (plot_id, char_id, message)
        VALUES ($plot_id, $char_id, '$message')";
    sqlQuery( $query );
}

function getPlotGuestbooks( $plot_id ) {
    $plot_id = intval( $plot_id );
    $query = "SELECT p.*, c.name AS char_name
        FROM `plot_guestbooks` AS p, `characters` AS c
        WHERE plot_id=$plot_id AND c.id=p.char_id";
    $results = sqlQuery( $query );
    $obj = array();
    if ( ! $results ) { return $obj; }
    while ( $o = $results->fetch_assoc() ) {
        $obj[] = $o;
    }
    return $obj;
}

?>