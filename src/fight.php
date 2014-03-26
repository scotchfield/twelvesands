<?

require_once 'include/core.php';
$debug_time_start = debugTime();

require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/formatting.php';

require_once sg_base_path . 'state/fight.php';

$log_obj = new Logger();

$state_params = array();
$state_params[ 'a' ] = getGetStr( 'a', '0' );
$state_params[ 'i' ] = getGetStr( 'i', '0' );
$state_params[ 't' ] = getGetStr( 't', '0' );
$state_params[ 'x' ] = getGetInt( 'x', 0 );

$combat_obj = getCombat( $state_params, $log_obj );

if ( array_key_exists( 'header', $combat_obj ) ) {
    header( $combat_obj[ 'header' ] );
    exit;
}

if ( array_key_exists( 'char_obj', $combat_obj ) ) {
    $char_obj = $combat_obj[ 'char_obj' ];
    $c = $char_obj->c;
}

$combat_bar_array = getAllCombatBarOptions( $char_obj );

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss( $c ); ?>
</head>
<body>

<? renderPopupText(); ?>
<script type="text/javascript" src="include/ts_keypress.js"></script>

<div class="container">

<?

require '_header.php';


if ( isset( $combat_obj[ 'foe' ][ 'generated_name' ] ) ) {
    echo '<p class="zone_title">' . $combat_obj[ 'foe' ][ 'generated_name' ] .'</p>';
} else {
    echo '<p class="zone_title">' . $combat_obj[ 'foe' ][ 'name' ] . '</p>';
}


$cry_count = count( $char_obj->c[ 'battle_cries' ] );
if ( $cry_count > 0 ) {
    echo '<p><i>You cry out as the combat begins!</i><br><b>' .
        $char_obj->c[ 'battle_cries' ][ rand( 0, $cry_count - 1 ) ] . '</b></p>';
}

echo '<p class="zone_description">' . $combat_obj[ 'foe' ][ 'text' ] . '</p>';

foreach ( $combat_obj[ 'output_obj' ] as $x ) {
    echo $x;
}



require '_footer.php';

$log_save = $log_obj->save();

$combat_obj[ 'char_obj' ] = '';
debugPrint( '<font size="-2">' );
//debugPrint( $combat_obj );
debugPrint( '</font>' );

$debug_time_diff = debugTime() - $debug_time_start;
debugPrint( '<font size="-2">Page rendered in ' .
    number_format( $debug_time_diff, 2, ".", "." ) . 's</font>' );

?>

</div>
</body>
</html>