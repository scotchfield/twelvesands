<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/validate.php'; 

$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$zone_obj = getAllAvailableZones( $char_obj );

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


echo '<h3>Available Zones by Level</h3>';
echo '<p>';

$i = -1;
foreach ( $zone_obj as $zone ) {
    if ( $zone[ 'min_level' ] > $i ) {
        $i = $zone[ 'min_level' ];
        $last_ui = 0;
        echo '</p><p><b>Level ' . $i . '</b></p><p>';
    }
    echo '<a href="main.php?z=' . $zone[ 'id' ] . '" ' .
         'onmouseover="popup(\'<b>' . getEscapeQuoteStr( $zone[ 'name' ] ) .
         '</b><br>' . getEscapeQuoteStr( $zone[ 'description' ] );
    if ( $zone[ 'min_level' ] > 1 ) {
        echo '<br><i>Level ' . $zone[ 'min_level' ];
        if ( $zone[ 'artifact_required' ] > 0 ) {
            $a_required = getArtifact( $zone[ 'artifact_required' ] );
            echo ', ' . $a_required[ 'name' ];
        }
        echo ' required.</i>';
    }
    echo '\')" onmouseout="popout()"' .
         '>' . $zone[ 'name' ] . '</a><br>';
}


require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>