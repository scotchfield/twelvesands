<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/validate.php'; 

$log_obj = new Logger();
$char_obj = new Char( $_SESSION[ 'c' ] );

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?>: Support the Sands</title>
<? renderCharCss( $char_obj->c ); ?>
</head>
<body>

<? renderPopupText(); ?>

<div class="container">

<?

require '_header.php';
$log_obj->addLog( $char_obj->c, sg_log_donate_view, 0, 0, 0, 0 );

?>

<p>Hi there!  First off, I want to let you know that Twelve Sands is a
free-to-play browser game, and it's my goal to keep it that way.  If you
don't have the funds to donate, or have any reservations, it's important
that you understand that you are <i>not required</i> to give any money.
I enjoy running this game, and honestly, the most important thing to me
is to feel that people are enjoying their time here.  It's a really
cool feeling.  :)</p>

<p>That said, thank you for even considering a donation!  Twelve Sands is kept
alive by the support of its users.  We appreciate the time that you spend
in game, supporting the world and the community, and it means a lot that
you're thinking about also supporting the development and maintenance of
the world!</p>

<p>To show our thanks, any player who makes a donation of 10 Canadian
dollars or more (yup, we're based in the frozen tundra of the Great White
North) will receive an in-game trinket:</p>

<p><a href="#" onmouseover="popup('<b><span class=&quot;item_t4&quot;>Star of the Sands</span></b><br>This ornately decorated artifact fills you with awe.  A single red gemstone the size of an eye sits inside of a polished gold setting.  From what you understand of it, the materials were pulled from the ancient Sands themselves.  You feel stronger holding this.<br><b>Trinket</b><br><b><span class=mod_highlight>-15% fatigue taken</span></b><br/><b><span class=mod_highlight>+3 all attributes</span></b><br/><b>125 Armour</b><br>')" onmouseout="popout()" class="item"><span class="item_t4">Star of the Sands</span></a></p>

<p>You'll get one Star of the Sands for every $10 donation you make, so
a $20 donation will result in two Star of the Sands artifacts, three for $30,
etc.</p>

<p>The easiest way to handle donations is through Paypal.  Please note
that after you complete your transaction, you will be brought back to the
Twelve Sands website.  This is important, both so that you can verify that
everything worked on the Paypal side of things, and so that we can track
your support of the site.  Be sure to click on the link that comes up after
you're finished!</p>

<form target="_blank" action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_xclick">
<input type="hidden" name="business" value="scott@scootah.com">
<input type="hidden" name="undefined_quantity" value="1">
<input type="hidden" name="item_name" value="Twelve Sands">
<input type="hidden" name="amount" value="10.00">
<input type="hidden" name="no_shipping" value="1">
<input type="hidden" name="return" value="http://www.twelvesands.com/donated.php?i=<?= $char_obj->c[ 'id' ] ?>">
<input type="hidden" name="no_note" value="1">
<input type="hidden" name="currency_code" value="CAD">
<input type="hidden" name="lc" value="CA">
<input type="hidden" name="bn" value="PP-BuyNowBF">
<input type="hidden" name="custom" value="<?= $char_obj->c[ 'id' ] ?>">
<input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>

<p>If you experience any problems or delays with your donation, please
contact me at <a href="mailto:scott@scootah.com">scott@scootah.com</a> so
I can look in to it.  Please include as much information about the
transaction as possible, including the date, the transaction ID number, and
the receipt number.</p>

<p>Thanks again, <?= $char_obj->c[ 'name' ] ?>, for supporting the Twelve Sands!</p>

<?

require '_footer.php';

$log_save = $log_obj->save();

?>

</div>
</body>
</html>