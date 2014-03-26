<?

require_once 'include/core.php';
$debug_time_start = debugTime();

require_once sg_base_path . 'include/validate.php';


require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/use.php';
require_once sg_base_path . 'include/user.php';

$log_obj = new Logger();
$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );


$render_obj = array();
$action_id = getGetStr( 'a', '' );
$i = getGetInt( 'i', 0 );
$n = getGetInt( 'n', 1 );

if ( ( 'u' == $action_id ) && ( $i > 0 ) ) {
    $artifact = getArtifact( $i );

    if ( ( $artifact != FALSE ) &&
         ( getArtifactQuantity( $char_obj, $artifact[ 'id' ] ) ) ) {
        $render_obj[] = '<p class="tip">Using: ' .
            renderArtifactStr( $artifact ) . '</p>';
    }

    if ( 0 == $n ) {
        if ( $artifact[ 'use_multiple' ] == 1 ) {
            $render_obj[] = '<p>Use multiple ' . $artifact[ 'plural_name' ] .
                ' (you have ' . getArtifactQuantity( $char_obj, $artifact[ 'id' ] ) .
                '):</p><p><form method="get">' .
                '<input type="hidden" name="a" value="u">' .
                '<input type="hidden" name="i" value="' . $i . '">' .
                '<br>How many would you like to use? ' .
                '<input type="text" name="n" value="1" size="8">' .
                '<input type="submit" value="Use Multiple">' .
                '</form></p>';
        } else {
            $render_obj[] = '<p>You can\'t use several of those at once!</p>';
        }
    } else {
        if ( $artifact[ 'use_multiple' ] > 1 ) {
            $render_obj[] = '<p>You can only use one of those at a time!</p>';
            $n = 1;
        }

        if ( ( $artifact[ 'type' ] == sg_artifact_edible ) &&
             ( $char_obj->c[ 'fatigue_reduction' ] >=
                   $char_obj->c[ 'max_fatigue_reduction' ] ) ) {
            $render_obj[] = '<p>You are too full to eat that!</p>';
        } else {
            if ( $artifact[ 'type' ] == sg_artifact_edible ) {
                $use_array = eatArtifact( $char_obj, $i, $n, $log_obj );
            } else {
                $use_array = useArtifact( $char_obj, $i, $n, $log_obj );
            }
            foreach ( $use_array as $use_str ) {
                $render_obj[] = $use_str;
            }
            $artifact = hasArtifact( $char_obj, $i, 0 );
            if ( $artifact[ 'quantity' ] > 0 ) {
                $render_obj[] = '<p><a href="inventory.php?a=u&i=' . $i . '&n=' . $n .
                     '">Use another ' . $artifact[ 'name' ] . '</a></p>';
            }
        }
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
include '_charmenu.php';


if ( 'u' == $action_id ) {
    foreach ( $render_obj as $st ) {
        echo $st;
    }
}


$a_obj = getCharArtifacts( $char_obj->c[ 'id' ] );

$achieve_obj = checkAchievementInventory( $char_obj );
foreach ( $achieve_obj as $achieve ) {
    echo $achieve;
}

$char_armour_array = array();
foreach( $a_obj as $artifact ) {
    if ( in_array( $artifact[ 'type' ], $armourArray ) ) {
      $char_armour_array[] = $artifact;
    }
}
// sortArmourArray(); // force the preprocessor to pick this method up
usort( $char_armour_array, "sortArmourArray" );

?>

<table width="100%"><tr><td width="50%" valign="top"><div class="table_stat">

  <p><span class="section_header">Weapons</span></p>

  <ul class="char_list">
  <?
$counter = 0;
foreach ( $a_obj as $artifact ) {
    if ( $artifact[ 'type' ] == 1 ) {
        $m_st = '';
        if ( $artifact[ 'm_enc' ] > 0 ) {
            $m_st = '&m=' . $artifact[ 'm_enc' ];
        }
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
             renderArtifactWithEquippedStr(
                 $char_obj, $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
             '<font size="-2">(<a href="char.php?a=a&i=' . $artifact[ 'id' ] .
             $m_st . '">equip</a>)</font></li>';
        $counter += 1;
    }
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

  <p><span class="section_header">Armour</span></p>

  <ul class="char_list">
  <?
$counter = 0;
$last_armour_type = 0;
foreach ( $char_armour_array as $artifact ) {
    if ( $last_armour_type != $artifact[ 'type' ] ) {
        $last_armour_type = $artifact[ 'type' ];
        switch ( $last_armour_type ) {
        case sg_artifact_armour_head: echo '<li><i>Head</i></li>'; break;
        case sg_artifact_armour_chest: echo '<li><i>Chest</i></li>'; break;
        case sg_artifact_armour_legs: echo '<li><i>Pants</i></li>'; break;
        case sg_artifact_armour_neck: echo '<li><i>Neck</i></li>'; break;
        case sg_artifact_armour_trinket: echo '<li><i>Trinket</i></li>'; break;
        case sg_artifact_armour_hands: echo '<li><i>Hands</i></li>'; break;
        case sg_artifact_armour_wrists: echo '<li><i>Wrists</i></li>'; break;
        case sg_artifact_armour_belt: echo '<li><i>Belt</i></li>'; break;
        case sg_artifact_armour_boots: echo '<li><i>Boots</i></li>'; break;
        case sg_artifact_armour_ring: echo '<li><i>Ring</i></li>'; break;
        }
    }
    $m_st = '';
    if ( $artifact[ 'm_enc' ] > 0 ) {
        $m_st = '&m=' . $artifact[ 'm_enc' ];
    }
    echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
         renderArtifactWithEquippedStr(
             $char_obj, $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
         '<font size="-2">(<a href="char.php?a=aa&i=' . $artifact[ 'id' ] .
         '&t=' . $artifact[ 'type' ] . $m_st . '">equip</a>)</font></li>';
    $counter += 1;
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

  <p><span class="section_header">Mounts</span></p>

  <ul class="char_list">
  <?
$counter = 0;
foreach ( $a_obj as $artifact ) {
    if ( $artifact[ 'type' ] == sg_artifact_mount ) {
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
             renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
             '<font size="-2">(<a href="char.php?a=am&i=' . $artifact[ 'id' ] .
             '">equip</a>)</font></li>';
        $counter += 1;
    }
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

  <p><span class="section_header">Runes</span></p>

  <ul class="char_list">
  <?
$counter = 0;
foreach ( $a_obj as $artifact ) {
    if ( $artifact[ 'type' ] == sg_artifact_rune ) {
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
             renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
            '<font size="-2">(<a href="inventory.php?a=u&i=' .
            $artifact[ 'id' ] . '">use</a>)</font></li>';
        $counter += 1;
    }
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

  <p><span class="section_header">Cards</span></p>

  <ul class="char_list">
  <?
$counter = 0;
foreach ( $a_obj as $artifact ) {
    if ( $artifact[ 'type' ] == sg_artifact_puzzle_1 ) {
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
             renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
             '<font size="-2">(<a href="puzzle.php?i=' . $artifact[ 'id' ] .
             '">read</a>)</font></li>';
        $counter += 1;
    }
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

  <p><span class="section_header">Readable</span></p>

  <ul class="char_list">
  <?
$counter = 0;
foreach ( $a_obj as $artifact ) {
    if ( $artifact[ 'type' ] == sg_artifact_readable ) {
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
            renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
            '<font size="-2">(<a href="char.php?a=r&i=' . $artifact[ 'id' ] .
            '">read</a>)</font> ';
        $counter += 1;
    }
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

  <p><span class="section_header">Quest Related</span></p>

  <ul class="char_list">
  <?
$counter = 0;
foreach ( $a_obj as $artifact ) {
    if ( $artifact[ 'type' ] == sg_artifact_quest ) {
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
            renderArtifactStr( $artifact, $artifact[ 'quantity' ] );
        $counter += 1;
    }
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

</div></td><td valign="top"><div class="table_stat">

  <p><span class="section_header">Food</span></p>

  <ul class="char_list">
  <?
$counter = 0;
foreach ( $a_obj as $artifact ) {
    if ( $artifact[ 'type' ] == 3 ) {
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
            renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
            '<font size="-2">(<a href="inventory.php?a=u&i=' .
            $artifact[ 'id' ] . '">eat</a>)</font> ';
        $counter += 1;
    }
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

  <p><span class="section_header">Usable</span></p>

  <ul class="char_list">
  <?
$counter = 0;
foreach ( $a_obj as $artifact ) {
    if ( $artifact[ 'type' ] == sg_artifact_usable ) {
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
            renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
            '<font size="-2">(<a href="inventory.php?a=u&i=' .
            $artifact[ 'id' ] . '">use</a>)</font>';
        if ( $artifact[ 'quantity' ] > 1 ) {
            if ( $artifact[ 'use_multiple' ] == 1 ) {
                echo '&nbsp;<font size="-2">(<a href="inventory.php?a=u&i=' .
                     $artifact[ 'id' ] . '&n=0">use&nbsp;multiple</a>)</font>';
            }
        }
        echo '</li>';
        $counter += 1;
    }
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

  <p><span class="section_header">Other Artifacts</span></p>

  <ul class="char_list">
  <?
$non_other_artifacts = array(
    sg_artifact_weapon,
    sg_artifact_rune,
    sg_artifact_mount,
    sg_artifact_usable,
    sg_artifact_edible,
    sg_artifact_readable,
    sg_artifact_quest,
    sg_artifact_puzzle_1 );

$counter = 0;
foreach ( $a_obj as $artifact ) {
    if ( ( ! in_array( $artifact[ 'type' ], $non_other_artifacts ) ) &&
         ( ! in_array( $artifact[ 'type' ], $armourArray ) ) ) {
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
            renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '</li>';
        $counter += 1;
    }
}
if ( $counter == 0 ) { echo '<li><b>None</b></li>'; }
  ?>
  </ul>

<?

  echo '</div></td></tr></table>';



require '_footer.php';
$save = $char_obj->save();
$log_save = $log_obj->save();




$debug_time_diff = debugTime() - $debug_time_start;
debugPrint( '<font size="-2">Page rendered in ' .
    number_format( $debug_time_diff, 2, ".", "." ) . 's</font>' );

?>

</div>
</body>
</html>