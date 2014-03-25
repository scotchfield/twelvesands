<?

require_once 'include/core.php';

require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/log.php';

$log_obj = new Logger();

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?>: Thank you for your support!</title>
<? renderCharCss( $c ); ?>
</head>
<body>

<div class="container">

<?

$i = getGetStr( 'i', '0' );

$c = array();
$c[ 'id' ] = $i;
$c[ 'current_hp' ] = 0;
$c[ 'level' ] = 0;
$log_obj->addLog( $c, sg_log_donate_success, i, 0, 0, 0 );

?>

<h3>Thank you for donating to Twelve Sands!</h3>

<p>Thank you for your payment.  Your transaction has been completed, and
a receipt for your purchase has been emailed to you. You may log into your
account at www.paypal.com/row to view details of this transaction.</p>

<p>Now that the mandatory legalese is aside, I want to thank you
for your support.  It honestly makes all the difference, and
encourages both active development of the game, and servers that
can support the community.  Once the payment has been processed, and
I've received the email, you'll receive an in-game mail with
your new artifacts!  If there is any significant delay, or any
problems, please email me at
<a href="mailto:scott@scootah.com">scott@scootah.com</a> with as much
information about the transaction as you received (no credit card
information; PayPal should take care of all that), and I'll look
in to it immediately.  :)  I'm processing everything manually, so
there will be a slight delay before receiving your artifacts in the
in-game mail.</p>

<p><a href="http://www.twelvesands.com">Return to Twelve Sands</a></p>

<p>Thanks again for supporting the Twelve Sands!</p>

<?

$log_save = $log_obj->save();

?>

</div>
</body>
</html>