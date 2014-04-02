<?

require_once 'include/core.php';

require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/session.php';

session_start();

if ( ! isset( $_SESSION[ 'u' ] ) ) {
    echo ' ';
    exit;
}

$time = time();

if ( ( $time - $_SESSION[ 'chat_time_check' ] ) > 20 ) {

    $session = getSession( session_id() );
    if ( $session[ 'user_id' ] != $_SESSION[ 'u' ] ) {
        deleteSession( session_id() );
        echo ' ';
        exit;
    }

/*    if ( $_SESSION[ 'u' ] > 1 ) {
        if ( ( $session[ 'timestamp' ] > 0) &&
             ( $time - $session[ 'timestamp' ] > 1800 ) ) {
            deleteSession( session_id() );
            echo ' ';
            exit;
        }
}*/

    updateSession( session_id(), TRUE );

    $_SESSION[ 'chat_time_check' ] = $time;
}

?>