<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/validate.php'; 

$char_obj = new Char( $_SESSION[ 'c' ] );

if ( $char_obj->c[ 'encounter_type' ] != sg_encountertype_choice ) {
    header( 'Location: main.php' );
    exit;
}

$zone = getZone( getFlagValue( $char_obj, sg_flag_last_combat_zone ) );
$encounter = getChoiceEncounter( $char_obj->c[ 'encounter_id' ] );
if ( $encounter == FALSE ) {
    header( 'Location: main.php' );
    exit;
}

$render_obj = array();

$i = getGetInt( 'i', 0 );
if ( ( $i > 0 ) && ( $i <= 3 ) ) {

    if ( $encounter[ 'c_type_' . $i ] == 1 ) { // foe

        $foe_encounter = getFoe( $char_obj, $encounter[ 'c_id_' . $i ] );
        $foe_encounter[ 'type' ] = sg_encountertype_foe;
        initiateCombat( $char_obj, $foe_encounter, $zone );
        $char_obj->save();
        exit;

    } elseif ( $encounter[ 'c_type_' . $i ] == 2 ) { // treasure

        $char_obj->setEncounterId( 0 );

        $t_encounter = getTreasure( $encounter[ 'c_id_' . $i ] );

        $render_obj[] = '<p class="zone_title">' . $t_encounter[ 'name' ] . '</p>';
        $render_obj[] = '<p>' . $t_encounter[ 'text' ] . '</p>';

        if ( $t_encounter[ 'reward' ] == 1 ) {
            $artifact = getArtifact( $t_encounter[ 'artifact' ] );
            $render_obj[] = awardArtifactString(
                $char_obj, $artifact, $t_encounter[ 'quantity' ] );
            $char_obj->addFatigue( $t_encounter[ 'fatigue' ] );
        }

        if ( $encounter[ 'c_artifact_required_' . $i ] > 0 ) {
            $e_artifact = getArtifact( $encounter[ 'c_artifact_required_' . $i ] );
            $render_obj[] = '<p>Your ' . $e_artifact[ 'name' ] . ' is consumed.</p>';
            removeArtifact( $char_obj, $e_artifact[ 'id' ], 1 );
        }

        if ( $t_encounter[ 'flag_id' ] > 0 ) {
            $char_obj->enableFlagBit(
                $t_encounter[ 'flag_id' ], $t_encounter[ 'flag_bit' ] );
        }

        $render_obj[] = '<p><a href="main.php?z=' . $zone[ 'id' ] .
             '">Adventure again</a></p>';

    } elseif ( $encounter[ 'c_type_' . $i ] == 4 ) { // custom flag swap

        function setLabyrinthFlagLocation( $c_obj, $loc ) {
            $c_obj->addFlag( sg_flag_great_labyrinth,
                ( getFlagValue( $c_obj, sg_flag_great_labyrinth ) & ( ~63 ) ) + $loc );
        }

        if ( $encounter[ 'c_id_' . $i ] <= 20 ) {
            $char_obj->setEncounterId( 0 );
            setLabyrinthFlagLocation( $char_obj, $encounter[ 'c_id_' . $i ] );
            header( 'Location: main.php?z=131' );
        }

        $save = $char_obj->save();
        exit;

    }

} else {
    $render_obj[] = '<p class="zone_title">' . $encounter[ 'name' ] . '</p>';
    $render_obj[] = '<p>' . $encounter[ 'text' ] . '</p>';

    $keys_disabled = getFlagBit( $char_obj, sg_flag_account_bit_options, 1 );

    if ( $encounter[ 'c_type_1' ] > 0 ) {
        $a_id = 'id="bar_1" ';
        if ( $keys_disabled ) { $a_id = ''; }
        $render_obj[] = '<p><font size="-2">Choice 1:</font><br>';
        if ( ( $encounter[ 'c_artifact_required_1' ] > 0 ) &&
             ( getArtifactQuantity(
                 $char_obj, $encounter[ 'c_artifact_required_1' ] ) < 1 ) ) {
            $render_obj[] = '<s>' . $encounter[ 'c_choice_1' ] . '</s></p>';
        } else {
            $render_obj[] = '<a ' . $a_id . 'href="choice.php?i=1">' .
               $encounter[ 'c_choice_1' ] . '</a></p>';
        }
    }
    if ( $encounter[ 'c_type_2' ] > 0 ) {
        $a_id = 'id="bar_2" ';
        if ( $keys_disabled ) { $a_id = ''; }
        $render_obj[] = '<p><font size="-2">Choice 2:</font><br>' .
             '<a ' . $a_id . 'href="choice.php?i=2">' .
             $encounter[ 'c_choice_2' ] . '</a></p>';
    }
    if ( $encounter[ 'c_type_3' ] > 0 ) {
        $a_id = 'id="bar_3" ';
        if ( $keys_disabled ) { $a_id = ''; }
        $render_obj[] = '<p><font size="-2">Choice 3:</font><br>' .
             '<a ' . $a_id . 'href="choice.php?i=3">' .
             $encounter[ 'c_choice_3' ] . '</a></p>';
    }

    if ( getFlagValue( $char_obj, sg_flag_combat_flag_id_set ) != 0 ) {
        $char_obj->enableFlagBit(
            getFlagValue( $char_obj, sg_flag_combat_flag_id_set ),
            getFlagValue( $char_obj, sg_flag_combat_flag_bit_set ) );
    }
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

echo '<p><a href="#" onclick="document.location=document.' .
     'getElementById(\'bar_default\').href;">' .
     '<img src="images/buff-green.gif" width="24" height="24" ' .
     'border="0" onmouseover="popup(\'<b>(`) Adventure again</b>\')" ' .
     'onmouseout="popout()"></a>&nbsp;';
for ( $i = 0; $i < 10; $i++ ) {
    echo '<img src="images/buff-empty.gif" width="24" height="24">';
}
echo '</p>';

foreach ( $render_obj as $x ) {
    echo $x;
}

require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>