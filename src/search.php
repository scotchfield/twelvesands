<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/user.php';
require_once sg_base_path . 'include/validate.php'; 

$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$s_text = fixStr( getPostStr( 'n', '' ) );
$time = time();
$render_str = '';

if ( ( $time - $_SESSION[ 'last_search_time' ] ) < 5 ) {
    $chars = array();
    $render_str = '<p class="tip">Please restrict your searches to one every ' .
        'five seconds.</p>';
} elseif ( strlen( $s_text ) > 0 ) {
    $chars = getCharactersByName( $s_text );
    $_SESSION[ 'last_search_time' ] = $time;
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss( $char_obj->c ); ?>
</head>
<body>

<? renderPopupText(); ?>

<div class="container">

<?

require '_header.php';
require '_charmenu.php';

echo $render_str;

?>

<h3>Search for a character</h3>

<form method="post">
<p>Enter part of the character name you'd like to search for:<br>
<input type="text" size="30" id="n" name="n" value="<?= $s_text ?>">
</form>

<?

if ( count( $chars ) > 0 ) {
    echo '<p><b>Characters found:</b></p>';

    echo '<p>';
    foreach ( $chars as $k => $v ) {
        echo '<a href="char.php?i=' . $k . '">' . $v . '</a><br>';
    }
    echo '</p>';
}

require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>