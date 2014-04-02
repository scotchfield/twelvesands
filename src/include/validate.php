<?

require_once 'include/core.php';

require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/session.php';

session_start();

if ( ! isset( $_SESSION[ 'u' ] ) ) {
    header( 'Location: ' . sg_app_root );
    exit;
}

if ( ! isset( $_SESSION[ 'c' ] ) ) {
    header( 'Location: select.php' );
    exit;
}

$time = time();

if ( ( $time - $_SESSION[ 'time_check' ] ) > 10 ) {

    $session = getSession( session_id() );

    if ( $session[ 'user_id' ] != $_SESSION[ 'u' ] ) {
        deleteSession( session_id() );
        header( 'Location: ' . sg_app_root );
        exit;
    }

/*
    if ( $_SESSION[ 'u' ] > 1 ) {
        if ( ( $session[ 'timestamp' ] > 0 ) &&
            ( $time - $session[ 'timestamp' ] > 1800 ) ) {
            deleteSession( session_id() );
            header( 'Location: ' . sg_app_root );
            exit;
        }
    }
*/

    if ( ( $time - $session[ 'timestamp' ] ) > 30 ) {
        updateSession( session_id(), FALSE );
    }

    $_SESSION[ 'time_check' ] = $time;
}

?>