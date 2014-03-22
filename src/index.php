<?

require_once 'ts_core.php';

session_start();

function getGetInt($id, $default) {
  $i = $default;
  if (isset($_GET[$id])) { $i = intval($_GET[$id]); }
  return $i;
}

$ref_id = getGetInt('ref', 0);
if ($ref_id > 0) {
  $_SESSION['ref_id'] = $ref_id;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title>Twelve Sands: A Free, Browser-Based Fantasy RPG</title>
<link rel="stylesheet" type="text/css" href="css/site.css">
</head>
<body class="title" onload="if (self != top) { top.location = self.location; }">

<script type="text/javascript" language="javascript" src="include/md5.js">
</script>
<script type="text/javascript">

function loginSubmit() {
  var login_form = document.getElementById('login_form');
  var pass = hex_md5(hex_md5(login_form.a.value) + login_form.h.value);
  login_form.p.value = pass;
  login_form.a.value = '';

  return true;
}

</script>

<div class="container">
<img src="images/ts_logo.gif" width="640" height="149">
<center>

<?

$error_id = getGetInt('i', 0);

if ($error_id > 0) {
  echo '<p class="tip">';

  switch ($error_id) {
  case 1:
    echo 'Sorry, that username and password don\'t match.';
    break;
  case 2:
    echo 'You need to verify your account before you can log in!';
    break;
  case 3:
    echo 'Sorry, you\'re going to have to wait for a while ' .
         'before you can log in again.';
    break;
  case 4:
    echo 'The game is under maintenance - please try again in ten minutes!';
    break;
  case 5:
    echo 'Sorry, you need to wait a little bit between login attempts.<br>' .
         'Please try again in one minute!';
    break;
  case 6:
    echo 'Twelve Sands is down for a few minutes for maintenance.<br>Please ' .
         'try again in a minute or so!';
    break;
  default:
    echo 'Invalid error code detected.  Hacker alert!<br>Formatting hard ' .
         'drive and wiping database tables as a precaution.<br>How you ' .
         'like them apples, hacker?<br>HACKER!!</p>';
    echo '<p class="tip">ATH0</p><p class="tip">NO CARRIER';
    break;
  }

  echo '</p>';
}

?>

<hr width="350">
<p class="action_footer"><b>
<a href="faq/" target="_blank">What is Twelve Sands?</a> |
<a href="register.php">Create an Account</a> |
<a href="http://profiles.twelvesands.com">Character Profiles</a>
</b></p>
<hr width="350">

<table class="plain" width="100%">
<tr>
<td width="280" align="right">

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

</td>

<td align="center" style="padding: 35px;">

<b>Twelve Sands is a free-to-play browser-based fantasy RPG.  Create an
account, and join the action!</b>

</td></tr></table>


<center>
<h3><a href="register.php">Create an account!</a></h3>

<p>(<a href="recover.php">Need to reset your password?</a>)</p>
</center>


</div>

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1685371-3";
urchinTracker();
</script>

</body></html>