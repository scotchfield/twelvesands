<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/validate.php'; 

$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$chars_online = getOnlineCharacters();
$id_obj = array();
foreach ( $chars_online as $k => $v ) {
    $id_obj[] = $k;
}
$status_obj = getCharListStatus( $id_obj );

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

echo '<p>Players currently online <b>(' . count( $chars_online ) . ')</b>:</p>';

echo '<p>';
foreach ( $chars_online as $k => $v ) {
    echo '<a href="char.php?i=' . $k . '">' . $v . '</a>';
    if ( isset( $status_obj[ $k ] ) ) {
        echo ' (<i>' . $status_obj[ $k ][ 'status' ] . '</i>)';
    }
    echo '<br>';
}
echo '</p>';

require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>