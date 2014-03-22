<?

// session validation start

require_once 'include/core.php';

require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/session.php';
require_once sg_base_path . 'include/user.php';

session_start();

if ( ! isset( $_SESSION[ 'u' ] ) ) {
    header( 'Location: ' . sg_app_root );
    exit;
}

$session = getSession( session_id() );
if ( $session[ 'user_id' ] != $_SESSION[ 'u' ] ) {
    deleteSession( session_id() );
    header( 'Location: ' . sg_app_root );
    exit;
}

$time = time();
if ( ( $session[ 'timestamp' ] > 0 ) && ( $time - $session[ 'timestamp' ] > 1800 ) ) {
    deleteSession( session_id() );
    header( 'Location: ' . sg_app_root );
    exit;
}

if ( ( $time - $session[ 'timestamp' ] ) > 30 ) {
    updateSession( session_id(), FALSE );
}

// session validation end

clearSelect( TRUE );

$user = getUserById( $_SESSION[ 'u' ] );

$output_obj = array();

$char_id = getGetInt( 'i', 0 );
if ( $char_id > 0 ) {
    $chars = getCharactersForUser( $_SESSION[ 'u' ] );
    if ( isset( $chars[ $char_id ] ) ) {
        $time = time();
        if ( ( $time - $chars[ $char_id ][ 'last_login' ] ) < 15 ) {
            $output_obj[] = '<p class="tip">Sorry, you need to wait a little ' .
                'bit before you can sign in with that character.<br>' .
                'Please try again in a few seconds!</p>';
        } else {
            $log_obj = new Logger();
            $log_obj->addLog( $chars[ $char_id ], sg_log_login_success, 0, 0, 0, 0 );
            $log_save = $log_obj->save();

            setActiveCharacter( $char_id );
            updateCharLastLogin( $char_id );

            header( 'Location: main.html' );
            exit;
        }
    } else {
        $output_obj[] = '<p class="tip">That character isn\'t yours!</p>';
    }
}

$a_action = getGetStr( 'a', '' );

if ( 'c' == $a_action ) {
    $chars = getCharactersForUser( $_SESSION[ 'u' ] );
    if ( count( $chars ) < $user[ 'max_chars' ] ) {
        $char_name = getBasicStr( $_GET[ 'n' ] );
        if ( ( '' == $char_name ) ||
            ( strlen( $char_name ) < 3 ) ||
            ( strlen( $char_name ) > 32 ) ) {
            $output_obj[] = '<p class="tip">A character name can only use basic ' .
                'letters, numbers, spaces, dashes, and underscores.<br>' .
                'Anything else will be stripped!</p>' .
                '<p><form method="get" action="select.php">What ' .
                'character name will you use?<br>' .
                '<input name="n" size="32" type="text"><br>' .
                '<input type="hidden" name="a" value="c">' .
                '<input value="Create!" type="submit"></form></p>';
        } else {
            $c_id = getCharIdForCharName( $char_name );
            if ( FALSE == $c_id ) {
                createCharacter( $_SESSION[ 'u' ], $char_name );
                header( 'Location: select.php' );
                exit;
            } else {
                $output_obj[] = '<p class="tip">That name is already in use!</p>';
            }
        }
    } else {
        $output_obj[] = '<p class="tip">You already have three characters!</p>';
    }
}


?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title>Twelve Sands</title>
<link rel="stylesheet" type="text/css" href="css/site.css?2"></head>
<body onload="if (self != top) { top.location = self.location; }">

<? renderPopupText(); ?>

<div class="container">

<img src="images/ts_logo.gif" width="640" height="149">

<br><br>

<p class="zone_title">Character Selection</p>

<?

if ( $user[ 'max_chars' ] == 3 ) {
    echo '<p class="tip">Each basic account is allowed to have up to three ' .
         'characters associated with it.</p>';
} else {
    echo '<p class="tip">Your account has been upgraded to allow up to ' .
         $user[ 'max_chars' ] . ' characters.</p>';
}


foreach ( $output_obj as $x ) {
    echo $x;
}

?>

<center><table class="leaderboard">
<tr>
<th width="100">Character</th>
<th width="100">Level</th>
<th width="100">Gold</th>
<th width="100">Experience</th>
<th width="100">Fatigue</th>
</tr>
<?

$chars = getCharactersForUser( $_SESSION[ 'u' ] );
foreach ( $chars as $c ) {
    echo '<tr><td><a href="select.php?i=' . $c[ 'id' ] . '">' .
         $c[ 'titled_name' ] . '</a></td><td>' . $c[ 'level' ] .
         '</td><td>' . $c[ 'gold' ] . '</td><td>';

    if ( $c[ 'level' ] > 1 ) {
        $curXp = $c[ 'xp' ] - levelXp( $c[ 'level' ] );
        $levelXp = levelXp( $c[ 'level' ] + 1 ) - levelXp( $c[ 'level' ] );
    } else {
        $curXp = $c[ 'xp' ];
        $levelXp = levelXp( $c[ 'level' ] + 1 );
    }

    if ( $curXp < 0 ) {
        $curXp = 0;
    }
    if ( $levelXp < 0 ) {
        $levelXp = 1;
    }
    if ( $curXp > $levelXp ) {
        $curXp = $levelXp;
    }

    echo $curXp . ' / ' . $levelXp;

    echo '</td><td>';
    renderFatigue( $c[ 'fatigue' ] );
    echo '</td></tr>';
}

?>
</table></center>

<?

if ( count( $chars ) < $user[ 'max_chars' ] ) {
    echo '<p><a href="select.php?a=c">Create a new character</a></p>';
} else {
    echo '<p>&nbsp;</p>';
}

?>


<p class="action_footer">
<a href="/">Twelve Sands</a> |
<a href="/faq/" target="_blank">Help</a> |
<a href="logout.php" target="_top">Logout</a>
</p>

</div>
</body></html>