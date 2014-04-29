<?

require_once 'include/core.php';

require_once '../recaptchalib.php';

require_once sg_base_path . 'include/constants.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/user.php';

session_start();

if (!isset($_GET['u'])) {
  $user_to_verify = '0';
} else {
  $user_to_verify = getGetStr('u', '0');
  $verification = getGetStr('r', '0');
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<link rel="stylesheet" type="text/css" href="css/site.css" />
</head>
<body>

<script type="text/javascript" language="javascript" src="include/md5.js"></script>
<script type="text/javascript">

var passThroughFormSubmit = false;

function S_FormSubmit()
{
  if (passThroughFormSubmit) {
    return true;
  }

  var register_form = document.forms.register;
  if (register_form.u.value.length < 3) {
    alert('Username is too short!');
    return false;
  }
  if (register_form.a.value.length < 8) {
    alert('Password is too short!');
    return false;
  }
  if (register_form.e.value.length < 8) {
    alert('Email is too short!');
    return false;
  }
  if (register_form.a.value != register_form.b.value) {
    alert('Passwords do not match!');
    return false;
  }
  if (register_form.e.value != register_form.f.value) {
    alert('Email addresses do not match!');
    return false;
  }
  //var pass = hex_md5(hex_md5(register_form.a.value) + register_form.h.value);
  var pass = hex_md5(register_form.a.value);
  register_form.p.value = pass;
  register_form.a.value = '';
  register_form.b.value = '';

  register_form.submit();
}

</script>

<div class="container">

<img src="images/ts_logo.gif" width="640" height="149">

&nbsp;<br>

<?

if ( isset( $_POST['recaptcha_response_field'] ) ) {

  function main() {

    $privatekey = "6LeQTQMAAAAAAG55oWn5AZLbjY14KeQ7Y0WahvNw";
    $resp = recaptcha_check_answer($privatekey,
        $_SERVER["REMOTE_ADDR"],
        $_POST["recaptcha_challenge_field"],
        $_POST["recaptcha_response_field"]);

    if (!$resp->is_valid) {
      echo '<p class="tip">The reCAPTCHA wasn\'t entered correctly. Please ' .
           'go back and try it again.<br>(reCAPTCHA said: ' .
           $resp->error . ')';
      echo '<p><a href="register.php">Return to the registration ' .
           'page.</a></p>';
    } else {
      $u = trim(fixStr($_POST['u']));
      $e = htmlspecialchars($_POST['e']);
      $p = htmlspecialchars($_POST['p']);
      $h = htmlspecialchars($_POST['h']);
      $apple = htmlspecialchars($_POST['ap']);

      $user = getUser($u);

      if ($user) {

        echo '<p class="tip">A user with that name already exists! ' .
             'Please try again.</p>';
        echo '<p><a href="register.php">Return to the registration ' .
             'page.</a></p>';

      } elseif (getUserByEmail($e) != FALSE) {

        echo '<p class="tip">A user with that email address already exists! ' .
             'It is strictly against the terms of use to create multiple ' .
             'accounts; violating this can result in all accounts being ' .
             'banned!  Please try again.</p>';
        echo '<p><a href="register.php">Return to the registration ' .
             'page.</a></p>';

      } elseif (strlen($u) < 3) {

        echo '<p class="tip">That username is too short! ' .
             'Please try again.</p>';
        echo '<p><a href="register.php">Return to the registration ' .
             'page.</a></p>';

      } elseif ($apple != "apple") {

        echo '<p class="tip">Sorry, please read	the fields closely!<br>' .
             'Please try again.</p>';
        echo '<p><a href="register.php">Return to the registration ' .
             'page.</a></p>';

      } else {

        $md5_pass = md5($user['password'] . $h);
        $ver = mt_rand();
        $time = time();
        $refer_id = 0;
        if ( isset( $_SESSION[ 'ref_id' ] ) ) {
            $refer_id = intval( $_SESSION[ 'ref_id' ] );
        }

        $query = "
          INSERT INTO
            `users`
            (`name`, `password`, `email`, `email_verified`,
             `verification`, `created`, `refer_id`)
          VALUES
            ('$u', '$p', '$e', '0', '$ver', '$time', '$refer_id')
        ";
        $results = sqlQuery($query);

        $user = getUser($u);

        $r_email = "Dear $u,\n\nWelcome to Twelve Sands!\n\n" .
        "Before your account can be activated, it is necessary for you to " .
        "verify the validity of your email account.  You will only need to " .
        "do this once.\n\n" .
        "To complete your registration, please visit this URL:\n" .
        sg_app_root . "register.php?u=" . $user['id'] . "&r=" .
            $user['verification'] . "\n\n" .
        "Thank you for registering, and we wish you all the best!\n\n" .
        "If you are unable to register for some reason, please contact me " .
        "directly at scott@twelvesands.com, and I can check it out.";
        $r_email = wordwrap($r_email, 70);

        mail($e, 'Twelve Sands Registration', $r_email,
             "From: scott@twelvesands.com\nBcc: scott@twelvesands.com");

        echo '<p>Welcome to Twelve Sands, ' . $u . '!</p>';
        echo '<p>An email has been sent to ' . $e .
             ' with instructions on how ' .
             ' to complete the registration process.</p>';

      }

    }

    echo '<p><a href="' . sg_app_root . '">Return to the Twelve Sands main page.</a></p>';

  }

  main();

} elseif ($user_to_verify > 0) {

  $user = getUserById($user_to_verify);
  if (!$user) {
    echo '<p>Invalid user, sorry.</p>';
  } elseif ($user['email_verified'] != 0) {
    echo '<p>It looks like you\'ve already verified your account!  You ' .
         'should be able to sign in at this point.  If you can\'t, for some ' .
         'reason, please contact me at scott@twelvesands.com and I\'ll check ' .
         'it out.</p>';
  } elseif ($user['verification'] != $verification) {
    echo '<p>Something seems to have gone wrong with verification!  ' .
         'Please contact me at scott@twelvesands.com and I\'ll check ' .
         'it out.</p>';
  } else {
    $c = getCharIdForUser($user_to_verify);
    if ($c != FALSE) {
      echo '<p>It looks like you\'ve already verified your account!  You ' .
           'should be able to sign in at this point.  If you can\'t, ' .
           'for some reason, please contact me at scott@twelvesands.com ' .
           'and I\'ll check it out.</p>';
      exit;
    }

    echo '<p>Successful registration!  Grats!</p>';

    $query = "
      UPDATE
        `users`
      SET
        email_verified = 10
      WHERE
        id = '$user_to_verify' AND verification = '$verification'
    ";
    $results = sqlQuery($query);

  }

} else {

?>

<p>Create a new character on Twelve Sands!</p>

<center>
<form action="register.php" method="post" id="register">
<p>&nbsp;</p>
<table border="0">
<tr>
<td>Username:</td>
<td align="right"><input type="text" name="u" size="30" /></td>
</tr><tr>
<td>Password:</td>
<td align="right"><input type="password" name="a" size="30" /></td>
</tr><tr>
<td>Password (verify):</td>
<td align="right"><input type="password" name="b" size="30" /></td>
</tr><tr>
<td>Email:</td>
<td align="right"><input type="text" name="e" size="30" /></td>
</tr><tr>
<td>Email (verify):</td>
<td align="right"><input type="text" name="f" size="30" /></td>
</tr>
<tr>
<td>Type the word <b>apple</b>:</td>
<td align="right"><input type="text" name="ap" size="30" /></td>
</tr>
<td colspan="2"><center>
<input type="hidden" name="p" value="" />
<input type="hidden" name="h" value="<? echo mt_rand(); ?>" />

<?
$publickey = "6LeQTQMAAAAAAFDY_DNXy44Cp8ewwdxhCLGwWa8i";
echo recaptcha_get_html($publickey);
?>

<input type="button" value="Register" onclick="S_FormSubmit();" />
</center></tr></td>
</center>

</table></form>

<p><b>But I've already got an account!</b><br>
Multiple user accounts are forbidden, so please don't register with
multiple accounts, or they'll get banned.  Nobody wants that!  Instead,
you'll be able to use several characters after logging in, so please
take that route instead of adding lots of different users.</p>

<?

}

?>

<br>

<hr width="500">

<p class="action_footer">
<a href="<? echo sg_app_root; ?>">Twelve Sands</a> |
<a href="<? echo sg_app_root; ?>faq/" target="_blank">What is Twelve Sands?</a>
</p>

</div>

</body></html>