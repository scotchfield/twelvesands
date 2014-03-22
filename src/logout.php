<?php

require_once 'include/core.php';

require_once sg_base_path . 'include/session.php';

session_start();

deleteSession( session_id() );

if ( session_id() ) {
    $_SESSION = array();
    if ( isset( $_COOKIE[ session_name() ] ) ) {
        setcookie( session_name(), '', time() - 42000, '/' );
    }
    session_destroy();
}

header( 'Location: ' . sg_app_root );

?> 