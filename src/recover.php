<?

require_once 'include/core.php';

require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/user.php';

function getRecoverUser( $user_id, $validate_hash ) {
    $user = getUserById( $user_id );
    if ( $user == FALSE ) {
        return FALSE;
    } elseif ( strcmp( $validate_hash, md5( $user[ 'last_login' ] ) ) ) {
        return FALSE;
    }
    return $user;
}

$render_obj = array();

if ( count( $_POST ) > 0 ) {

    $user = getUserByEmail( $_POST[ 'e' ] );
    if ( FALSE == $user ) {
        $render_obj[] = '<p class="tip">That email address wasn\'t found!</p>';
    } else {
        $r_email = "Dear " . $user[ 'name' ] . ",\n\nSomeone visited the " .
            "password recovery page at Twelve Sands and indicated that they " .
            "needed to reset their password.  If this was you, please feel " .
            "free to visit the link below, and your password will be reset.  " .
            "If you feel this email was sent in error, feel free to disregard " .
            "it!\n\nhttp://www.twelvesands.com/recover.php?u=" . $user[ 'id' ] .
            "&v=" . md5( $user[ 'last_login' ] ) . "\n";
        $r_email = wordwrap( $r_email, 70 );

        mail( $user[ 'email' ], 'Twelve Sands Password Recovery', $r_email,
              "From: scott@twelvesands.com\nBcc: scott@twelvesands.com" );

        $render_obj[] = '<p class="tip">An email has been sent to your ' .
            'address!</p><p><a href="/">Back to Twelve Sands</a></p>';
    }

} else {

    $user_id = getGetInt( 'u', 0 );
    $validate_hash = getGetStr( 'v', '' );
    if ( $user_id > 0 ) {
        $user = getRecoverUser( $user_id, $validate_hash );
        if ( $user == FALSE ) {
            $render_obj[] = '<p class="tip">That isn\'t the proper recovery ' .
                'code!</p>';
        } else {
            $chars = 'bcdfghjkmnpqrstvwxyz23456789';
            $st = '';
            for ( $i = 0; $i < 8; $i++ ) {
                $st = $st . $chars[ rand( 0, strlen( $chars ) - 1 ) ];
            }

            setUserPassword( $user_id, md5( $st ) );
            if ( $user[ 'email_verified' ] == 0 ) {
                $query = "UPDATE `users` SET email_verified = 10 WHERE id=$user_id";
                $results = sqlQuery( $query );
            }

            $render_obj[] = '<p class="tip">Your password has been reset!</p>' .
                '<p>Your new password is: <b>' . $st . '</b></p>' .
                '<p>You can use this password immediately, and you can change it ' .
                'to something new by visiting the Manage Account link once ' .
                'you\'re online.</p><p><a href="/">Back to Twelve Sands</a></p>';
        }
    } else {
        $render_obj[] = '<p><b>Recover your password:</b></p>' .
            '<form action="recover.php" method="post"><p>Please enter the ' .
            'email address you used to sign up for the game, and a recovery ' .
            'link will be sent there to help you recover your password.</p><p>' .
            '<input type="text" name="e" size="40"> ' .
            '<input type="submit" value="Submit"></p></form>';
    }

}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title>Twelve Sands</title>
<link rel="stylesheet" type="text/css" href="css/site.css">
</head>
<body onload="if (self != top) { top.location = self.location; }">

<div class="container">
<a href="<? echo sg_app_root; ?>"><img src="images/ts_logo.gif" width="640" height="149" border="0"></a>
<center>

<hr width="350">
<p class="action_footer"><b>
<a href="<? echo sg_app_root; ?>faq/" target="_blank">What is Twelve Sands?</a> |
<a href="register.php">Create an Account</a> |
<a href="http://profiles.twelvesands.com">Character Profiles</a>
</b></p>
<hr width="350">

<?

foreach ( $render_obj as $x ) {
    echo $x;
}

?>

<!--
<table class="plain" width="100%"><tr><td width="280" align="right">
<p><h3>Welcome to Twelve Sands!</h3></p>
<form action="login.php" method="post"
      name="login_form" id="login_form" onSubmit='return loginSubmit();'>
<p>
Username:
<input style="font-size:13px; margin-bottom: 2px;"
       type="text" name="u" id="u">
<br>
Password:
<input style="font-size:13px; margin-bottom: 2px;"
       type="password" name="a" id="a">
<br>
<input style="font-size:12px; border:1px solid; background-color:#87CEEB;"
       type="submit" value="Login">
</p>
<input type="hidden" name="p" id="p" value="" />
<input type="hidden" name="h" id="h" value="<? echo mt_rand(); ?>" />
</form>
-->




</div>

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1685371-3";
urchinTracker();
</script>

</body></html>