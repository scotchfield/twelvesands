<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/constants.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/session.php';
require_once sg_base_path . 'include/user.php';

$file_name = sg_base_path . 'down';
if ( ( file_exists( $file_name ) ) && ( $_POST[ 'u' ] != 'swrittenb' ) ) {
    header( 'Location: ' . sg_app_root . '?i=6' );
    exit;
}

$log_obj = new Logger();

if ( ! isset( $_POST[ 'u' ] ) || ! isset( $_POST[ 'p' ] ) ) {
    header( 'Location: ' . sg_app_root );
    exit;
}

$u = trim( fixStr( $_POST[ 'u' ] ) );
$p = $_POST[ 'p' ];
$h = $_POST[ 'h' ];

if ( ! strlen( $p ) ) {
    $p = md5( md5( $_POST[ 'a' ] ) . $h );
}

if ( ! strlen( $u ) || ! strlen( $p ) || ! strlen( $h ) ) {
    header( 'Location: ' . sg_app_root );
    exit;
}

$user = getUser( $u );

if ( ! $user ) {
    header( 'Location: ' . sg_app_root . '?i=1' );
    exit;
}

if ( $user[ 'email_verified' ] < sg_valid_login_number ) {
    header( 'Location: ' . sg_app_root . '?i=2' );
    exit;
}

$md5_pass = md5( $user[ 'password' ] . $h );
if ( $md5_pass != $p ) {
    header( 'Location: ' . sg_app_root . '?i=1' );

    $c = array();
    $c[ 'id' ] = getCharIdForUser( $user[ 'id' ] );
    $log_obj->addLog( $c, sg_log_login_failed, 0, 0, 0, 0 );
    $log_save = $log_obj->save();

    exit;
}

// check for temporary ban

if ( $user[ 'ban_timestamp' ] > 0 ) {
    header( 'Location: ' . sg_app_root . '?i=3' );
    exit;
}

$time = time();
if ( ( ( $time - $user[ 'last_login' ] ) < 60 ) && ( $user[ 'id' ] > 1 ) ) {
    header( 'Location: ' . sg_app_root . '?i=5' );
    exit;
}

// success, go to char select

session_start();
$_SESSION[ 'u' ] = $user[ 'id' ];

updateUserLastLogin( $user[ 'id' ] );
clearSelect( TRUE );

addSession( session_id(), $user[ 'id' ], $u, $p );
//$log_obj->addLog( $char_obj->c, sg_log_login_success, 0, 0, 0, 0 );
$log_save = $log_obj->save();

header( 'Location: select.php' );

?>