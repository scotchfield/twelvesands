<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/validate.php'; 

$log_obj = new Logger();
$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

function getOutfits( $c_id ) {
    $c_id = intval( $c_id );
    $query = "SELECT * FROM `outfits` WHERE char_id=$c_id";
    $results = sqlQuery( $query );
    $obj = getResourceAssocById( $results );
    return $obj;
}

function getCharOutfitPair( $c_obj, $k ) {
    return $c_obj->c[ $k ][ 'id' ] . ',' . intval( $c_obj->c[ $k ][ 'm_enc' ] );
}

function saveOutfit( $c_obj, $name ) {
    $name = fixStr( $name );

    $query = "
      INSERT INTO
        `outfits`
        (char_id, name,
         weapon, weapon_e, a_head, a_head_e,
         a_chest, a_chest_e, a_legs, a_legs_e,
         a_neck, a_neck_e, a_t1, a_t1_e,
         a_t2, a_t2_e, a_t3, a_t3_e,
         a_hands, a_hands_e, a_wrists, a_wrists_e,
         a_belt, a_belt_e, a_boots, a_boots_e,
         a_r1, a_r1_e, a_r2, a_r2_e, mount, mount_e)
      VALUES
        ('" . $c_obj->c[ 'id' ] . "', '$name', " .
        getCharOutfitPair( $c_obj, 'weapon' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_head' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_chest' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_legs' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_neck' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_trinket' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_trinket_2' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_trinket_3' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_hands' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_wrists' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_belt' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_boots' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_ring' ) . "," .
        getCharOutfitPair( $c_obj, 'armour_ring_2' ) . "," .
        getCharOutfitPair( $c_obj, 'mount' ) . ")";
    $results = sqlQuery( $query );
}

function renameOutfit( $c_id, $outfit_id, $name ) {
    $c_id = intval( $c_id );
    $outfit_id = intval( $outfit_id );
    $name = fixStr( $name );

    $query = "UPDATE `outfits` SET name='$name'
        WHERE char_id=$c_id AND id=$outfit_id";
    sqlQuery( $query );
}

function deleteOutfit( $c_id, $outfit_id ) {
    $c_id = intval( $c_id );
    $outfit_id = intval( $outfit_id );

    $query = "DELETE FROM `outfits` WHERE char_id=$c_id AND id=$outfit_id";
    sqlQuery( $query );
}

function equipArtifact( $c_obj, $set_flags, $a_id, $a_enc ) {
    $return_st = '';
    $artifact = hasArtifact( $c_obj, $a_id, $a_enc );

    if ( ( FALSE == $artifact ) || ( $artifact[ 'quantity' ] < 1 ) ) {
        if ( $set_flags == TRUE ) {
            $return_st = '<p>You don\'t have that artifact!</p>';
        } else {
            //$artifact = getArtifact( $a_id, $a_enc );
            //$return_st = '<p>' . renderArtifactStr( $artifact ) .
            //    '<br>You don\'t have that artifact in your inventory!</p>';
        }
    } elseif ( $c_obj->c[ 'level' ] < $artifact[ 'min_level' ] ) {
        $return_st = '<p>Your level isn\'t high enough to use that artifact!</p>';
    } elseif ( ( $artifact[ 'skill_required' ] > 0 ) &&
               ( ! array_key_exists( $artifact[ 'skill_required' ],
                 $c_obj->c[ 'skills' ] ) ) ) {
        $return_st = '<p>You don\'t have the necessary skill required to use ' .
            'that artifact!</p>';
    } else {
        $a_key = '';
        switch ( $artifact[ 'type' ] ) {
        case sg_artifact_weapon: $a_key = 'weapon'; break;
        case sg_artifact_armour_head: $a_key = 'armour_head'; break;
        case sg_artifact_armour_chest: $a_key = 'armour_chest'; break;
        case sg_artifact_armour_legs: $a_key = 'armour_legs'; break;
        case sg_artifact_armour_neck: $a_key = 'armour_neck'; break;
        case sg_artifact_armour_hands: $a_key = 'armour_hands'; break;
        case sg_artifact_armour_wrists: $a_key = 'armour_wrists'; break;
        case sg_artifact_armour_belt: $a_key = 'armour_belt'; break;
        case sg_artifact_armour_boots: $a_key = 'armour_boots'; break;
        case sg_artifact_armour_ring:
            if ( $c_obj->c[ 'armour_ring' ][ 'id' ] == 0 ) {
                $a_key = 'armour_ring';
            } elseif ( $c_obj->c[ 'armour_ring_2' ][ 'id' ] == 0 ) {
                $a_key = 'armour_ring_2';
            } else {
                return FALSE; //$a_key = 'armour_ring';
            }
            break;
        case sg_artifact_armour_trinket:
            if ( $c_obj->c[ 'armour_trinket' ][ 'id' ] == 0 ) {
                $a_key = 'armour_trinket';
            } elseif ( $c_obj->c[ 'armour_trinket_2' ][ 'id' ] == 0 ) {
                $a_key = 'armour_trinket_2';
            } elseif ( $c_obj->c[ 'armour_trinket_3' ][ 'id' ] == 0 ) {
                $a_key = 'armour_trinket_3';
            } else {
                return FALSE; //$a_key = 'armour_trinket';
            }
            break;
        case sg_artifact_mount: $a_key = 'mount_id'; break;
        default:
            return FALSE; break;
        }

        if ( $c_obj->c[ $a_key ][ 'id' ] > 0 ) {
            $return_st = awardArtifactString(
                $c_obj, $c_obj->c[ $a_key ], 1, $c_obj->c[ $a_key . '_enc' ] );
            if ( $set_flags == TRUE ) {
                $c_obj->addFlag( sg_flag_unequip, $c_obj->c[ $a_key ][ 'id' ] );
                $c_obj->addFlag( sg_flag_unequip_enc, $c_obj->c[ $a_key . '_enc' ] );
            }
        }

        if ( $a_id > 0 ) {
            removeArtifact( $c_obj, $a_id, 1, $a_enc );
        }

        $c_obj->setIdPair( $a_key, $a_id, $a_enc );
        if ( $set_flags == TRUE ) {
            $c_obj->addFlag( sg_flag_equip, $a_id );
            $c_obj->addFlag( sg_flag_equip_enc, $a_enc );
        }
        if ( 0 == $a_id ) {
            $return_st = $return_st . '<p>You have nothing equipped.</p>';
        } else {
            $return_st = $return_st . '<p>You equip the ' .
                renderArtifactStr( $artifact ) . '.</p>';
        }

        unset( $_SESSION[ 'equipped_array' ] );
    }

    return $return_st;
}


$outfit_id = getGetInt( 'i', 0 );
$action = getGetStr( 'a', '' );

$outfit_obj = getOutfits( $char_obj->c[ 'id' ] );

$output_obj = array();

if ( 's' == $action ) {
    if ( count( outfit_obj ) > 10 ) {
        $output_obj[] = '<p class="tip">You can only have ten outfits at a time!' .
            ' If you delete some, you can add more.</p>';
    } else {
        $output_obj[] = '<p class="tip">Outfit saved!</p>';
        saveOutfit( $char_obj, 'New Outfit' );
        $outfit_obj = getOutfits( $char_obj->c[ 'id' ] );
    }
}

function outfitEquip( $c_obj, $outfit, $k ) {
    if ( $outfit[ $k ] > 0 ) {
        return equipArtifact( $c_obj, FALSE, $outfit[ $k ], $outfit[ $k . '_e' ] );
    }
}

if ( ( 'e' == $action ) && ( isset( $outfit_obj[ $outfit_id ] ) ) ) {
    $trinkets =
        ( ( $outfit_obj[ $outfit_id ][ 'a_t1' ] > 0 ) ? 1 : 0 ) +
        ( ( $outfit_obj[ $outfit_id ][ 'a_t2' ] > 0 ) ? 1 : 0 ) +
        ( ( $outfit_obj[ $outfit_id ][ 'a_t3' ] > 0 ) ? 1 : 0 );
    $rings =
        ( ( $outfit_obj[ $outfit_id ][ 'a_r1' ] > 0 ) ? 1 : 0 ) +
        ( ( $outfit_obj[ $outfit_id ][ 'a_r2' ] > 0 ) ? 1 : 0 );

    if ( $trinkets > 1 ) {
        if ( $char_obj->c[ 'armour_trinket' ][ 'id' ] > 0 ) {
            $output_obj[] = awardArtifactString( $char_obj,
                $char_obj->c[ 'armour_trinket' ], 1,
                $char_obj->c[ 'armour_trinket_enc' ] );
            $char_obj->setIdPair( 'armour_trinket', 0, 0 );
            $char_obj->saveInventory();
        }
        if ( $char_obj->c[ 'armour_trinket_2' ][ 'id' ] > 0 ) {
            $output_obj[] = awardArtifactString( $char_obj,
                $char_obj->c[ 'armour_trinket_2' ], 1,
                $char_obj->c[ 'armour_trinket_2_enc' ] );
            $char_obj->setIdPair( 'armour_trinket_2', 0, 0 );
            $char_obj->saveInventory();
        }
    }
    if ( $trinkets > 2 ) {
        if ( $char_obj->c[ 'armour_trinket_3' ][ 'id' ] > 0 ) {
            $output_obj[] = awardArtifactString( $char_obj,
                $char_obj->c[ 'armour_trinket_3' ], 1,
                $char_obj->c[ 'armour_trinket_3_enc' ] );
            $char_obj->setIdPair( 'armour_trinket_3', 0, 0 );
            $char_obj->saveInventory();
        }
    }

    if ( $rings > 1 ) {
        if ( $char_obj->c[ 'armour_ring' ][ 'id' ] > 0 ) {
            $output_obj[] = awardArtifactString( $char_obj,
                $char_obj->c[ 'armour_ring' ], 1,
                $char_obj->c[ 'armour_ring_enc' ] );
            $char_obj->setIdPair( 'armour_ring', 0, 0 );
            $char_obj->saveInventory();
        }
        if ( $char_obj->c[ 'armour_ring_2' ][ 'id' ] > 0 ) {
            $output_obj[] = awardArtifactString( $char_obj,
                $char_obj->c[ 'armour_ring_2' ], 1,
                $char_obj->c[ 'armour_ring_2_enc' ] );
            $char_obj->setIdPair( 'armour_ring_2', 0, 0 );
            $char_obj->saveInventory();
        }
    }

    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'weapon' );
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_head' );
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_chest' );
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_legs' );
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_neck' );
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_t1' );
    $char_obj->saveInventory();
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_t2' );
    $char_obj->saveInventory();
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_t3' );
    $char_obj->saveInventory();
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_hands' );
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_wrists' );
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_belt' );
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_boots' );
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_r1' );
    $char_obj->saveInventory();
    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'a_r2' );
    $char_obj->saveInventory();
//    $output_obj[] = outfitEquip( $char_obj, $outfit_obj[ $outfit_id ], 'mount' );
//    $char_obj->saveInventory();

    // change into outfit
    $output_obj[] = '<p class="tip">Outfit changed!</p>';
    $log_obj->addLog( $char_obj->c, sg_log_outfit, $outfit_id, 0, 0, 0 );
} elseif ( ( 'r' == $action ) && ( isset( $outfit_obj[ $outfit_id ] ) ) ) {
    $name = getPostStr( 'x', '' );
    if ( $name != '' ) {
        renameOutfit( $char_obj->c[ 'id' ], $outfit_id, $name );
        $outfit_obj[ $outfit_id ][ 'name' ] = $name;
        $output_obj[] = '<p class="tip">Outfit renamed!</p>';
    } else {
        $output_obj[] = '<form method="post" action="outfit.php?a=r&i=' .
            $outfit_id . '"><p>What would you like to call this outfit?<br>' .
            '<input type="text" name="x" value="' .
            $outfit_obj[ $outfit_id ][ 'name' ] . '" size="40"> ' .
            '<input type="submit" value="Rename"></p></form>';
    }
} elseif ( ( 'd' == $action ) && ( isset( $outfit_obj[ $outfit_id ] ) ) ) {
    deleteOutfit( $char_obj->c[ 'id' ], $outfit_id );
    unset( $outfit_obj[ $outfit_id ] );
    $output_obj[] = '<p class="tip">Outfit deleted!</p>';
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

foreach ( $output_obj as $x ) {
    echo $x;
}

echo '<p><font size="-2">(<a href="outfit.php?a=s">Save Current ' . 
     'Outfit</a>)</font></p>';

function renderOutfitPair( $a_obj, $outfit, $n, $k ) {
    if ( $outfit[ $k ] > 0 ) {
        echo '<b>' . $n . '</b>: ';
        $a_obj[ $outfit[ $k ] ][ 'm_enc' ] = $outfit[ $k . '_e' ];
        echo renderArtifactStr( $a_obj[ $outfit[ $k ] ] );
        echo '<br>';
    }
}

echo '<p><b>Your Saved Outfits:</b></p>';
foreach ( $outfit_obj as $x ) {
    $a_ids = array( $x[ 'weapon' ], $x[ 'a_head' ], $x[ 'a_chest' ], $x[ 'a_legs' ],
        $x[ 'a_neck' ], $x[ 'a_t1' ], $x[ 'a_t2' ], $x[ 'a_t3' ], $x[ 'a_hands' ],
        $x[ 'a_wrists' ], $x[ 'a_belt' ], $x[ 'a_boots' ],
        $x[ 'a_r1' ], $x[ 'a_r2' ], $x[ 'mount' ] );
    $a_obj = getArtifactArray( $a_ids );
    echo '<p><b>' . $x[ 'name' ] . '</b><br>';
    echo '<font size="-2">' .
         '(<a href="outfit.php?a=e&i=' . $x[ 'id' ] . '">Equip</a>) ' .
         '(<a href="outfit.php?a=r&i=' . $x[ 'id' ] . '">Rename</a>) ' .
         '(<a href="outfit.php?a=d&i=' . $x[ 'id' ] . '">Delete</a>)' .
         '</font><br>';

    renderOutfitPair( $a_obj, $x, 'Weapon', 'weapon' );
    renderOutfitPair( $a_obj, $x, 'Head', 'a_head' );
    renderOutfitPair( $a_obj, $x, 'Neck', 'a_neck' );
    renderOutfitPair( $a_obj, $x, 'Chest', 'a_chest' );
    renderOutfitPair( $a_obj, $x, 'Hands', 'a_hands' );
    renderOutfitPair( $a_obj, $x, 'Wrists', 'a_wrists' );
    renderOutfitPair( $a_obj, $x, 'Belt', 'a_belt' );
    renderOutfitPair( $a_obj, $x, 'Pants', 'a_legs' );
    renderOutfitPair( $a_obj, $x, 'Boots', 'a_boots' );
    renderOutfitPair( $a_obj, $x, 'Ring', 'a_r1' );
    renderOutfitPair( $a_obj, $x, 'Ring', 'a_r2' );
    renderOutfitPair( $a_obj, $x, 'Trinket', 'a_t1' );
    renderOutfitPair( $a_obj, $x, 'Trinket', 'a_t2' );
    renderOutfitPair( $a_obj, $x, 'Trinket', 'a_t3' );
    renderOutfitPair( $a_obj, $x, 'Mount', 'mount' );

    echo '</p>';
}

require '_footer.php';
$save = $char_obj->save();
$log_save = $log_obj->save();

?>

</div>
</body>
</html>