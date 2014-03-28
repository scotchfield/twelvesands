<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/validate.php'; 

$a = getGetStr( 'a', '0' );
$t = getGetInt( 't', 0 );

$log_obj = new Logger();
$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

if ( getFlagValue( $char_obj, sg_flag_enchanting ) == 0 ) {
    header( 'Location: char.php' );
    exit;
}

function getCharDisenchantArtifacts( $char_id ) {
    $c_id = intval( $char_id );
    $artifacts = array();

    $query = "
      SELECT
        a.*, c.m_enc, SUM(c.quantity) as quantity
      FROM
        char_artifacts AS c, artifacts AS a
      WHERE
        c.char_id = '$c_id' AND c.artifact_id = a.id AND
        (a.flags & 4) = 4
      GROUP BY
        a.id, c.m_enc
      ORDER BY
        a.name ASC, c.m_enc ASC
    ";
    $results = sqlQuery( $query );

    if ( $results ) {
        while ( $artifact = $results->fetch_assoc() ) {
            $artifact[ 'name' ] = fixStr( $artifact[ 'name' ] );
            $artifact[ 'plural_name' ] = fixStr( $artifact[ 'plural_name' ] );
            $artifact[ 'text' ] = fixStr( $artifact[ 'text' ] );
            $artifact[ 'o_name' ] = fixStr( $artifact[ 'o_name' ] );
            $artifacts[] = $artifact;
        }
    }

    return $artifacts;
}

function getCharEnchantables( $char_id ) {
    $c_id = intval( $char_id );
    $artifacts = array();

    $query = "
      SELECT
        a.*, c.m_enc, SUM(c.quantity) as quantity
      FROM
        char_artifacts AS c, artifacts AS a
      WHERE
        c.char_id = '$c_id' AND c.artifact_id = a.id AND
        a.type IN (1, 100, 101, 102, 103, 104, 105, 106, 107, 108, 109)
      GROUP BY
        a.id, c.m_enc
      ORDER BY
        a.name ASC, c.m_enc ASC
    ";
    $results = sqlQuery( $query );

    if ( $results ) {
        while ( $artifact = $results->fetch_assoc() ) {
            $artifact[ 'name' ] = fixStr( $artifact[ 'name' ] );
            $artifact[ 'plural_name' ] = fixStr( $artifact[ 'plural_name' ] );
            $artifact[ 'text' ] = fixStr( $artifact[ 'text' ] );
            $artifact[ 'o_name' ] = fixStr( $artifact[ 'o_name' ] );
            $artifacts[] = $artifact;
        }
    }

    return $artifacts;
}

function getDisenchantReward( $level, $rarity ) {
    $reward = array( 'a' => 0, 'n' => 0 );
    $level += rand( -3, 3 );

    if ( $rarity == sg_artifact_rarity_uncommon ) {
        switch ( $level ) {
            case 5: $reward = array( 'a' => 671, 'n' => 2 ); break;
            case 6: $reward = array( 'a' => 671, 'n' => 2 ); break;
            case 7: $reward = array( 'a' => 671, 'n' => 3 ); break;
            case 8: $reward = array( 'a' => 671, 'n' => 3 ); break;
            case 9: $reward = array( 'a' => 672, 'n' => 1 ); break;
            case 10: $reward = array( 'a' => 672, 'n' => 1 ); break;
            case 11: $reward = array( 'a' => 672, 'n' => 2 ); break;
            case 12: $reward = array( 'a' => 672, 'n' => 2 ); break;
            case 13: $reward = array( 'a' => 672, 'n' => 3 ); break;
            default:
                if ( $level < 5 ) {
                    $reward = array( 'a' => 671, 'n' => 1 );
                } else {
                    $reward = array( 'a' => 673, 'n' => 1 );
                }
                break;
        }
    } elseif ( $rarity == sg_artifact_rarity_rare ) {
        switch ( $level ) {
            case 1: $reward = array( 'a' => 671, 'n' => 1 ); break;
            case 2: $reward = array( 'a' => 672, 'n' => 1 ); break;
            case 3: $reward = array( 'a' => 671, 'n' => 2 ); break;
            case 4: $reward = array( 'a' => 671, 'n' => 1 ); break;
            case 5: $reward = array( 'a' => 672, 'n' => 2 ); break;
            case 6: $reward = array( 'a' => 675, 'n' => 2 ); break;
            case 7: $reward = array( 'a' => 675, 'n' => 2 ); break;
            case 8: $reward = array( 'a' => 675, 'n' => 2 ); break;
            case 9: $reward = array( 'a' => 675, 'n' => 1 ); break;
            case 10: $reward = array( 'a' => 675, 'n' => 2 ); break;
            case 11: $reward = array( 'a' => 675, 'n' => 2 ); break;
            case 12: $reward = array( 'a' => 675, 'n' => 3 ); break;
            case 13: $reward = array( 'a' => 676, 'n' => 1 ); break;
            default:
                if ( $level < 8 ) {
                    $reward = array( 'a' => 672, 'n' => 2 );
                } else {
                    $reward = array( 'a' => 676, 'n' => 1 );
                }
                break;
        }
    } elseif ( $rarity == sg_artifact_rarity_epic ) {
        $reward = array( 'a' => 679, 'n' => 1 );
    }

    return $reward;
}

function getEnchants() {
    $query = "
      SELECT
        c.*,
        a1.name AS artifact_name_1, a2.name AS artifact_name_2,
        a3.name AS artifact_name_3, a4.name AS artifact_name_4
      FROM
        `enchants` AS c,
        `artifacts` AS a1, `artifacts` AS a2,
        `artifacts` AS a3, `artifacts` AS a4
      WHERE
        c.a1 = a1.id AND
        c.a2 = a2.id AND
        c.a3 = a3.id AND
        c.a4 = a4.id
      ORDER BY
        c.min_level ASC
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $recipes = array();

    while ( $r = $results->fetch_assoc() ) {
        $r[ 'artifact_name_1' ] = fixStr( $r[ 'artifact_name_1' ] );
        $r[ 'artifact_name_2' ] = fixStr( $r[ 'artifact_name_2' ] );
        $r[ 'artifact_name_3' ] = fixStr( $r[ 'artifact_name_3' ] );
        $r[ 'artifact_name_4' ] = fixStr( $r[ 'artifact_name_4' ] );

        $recipes[ $r[ 'id' ] ] = $r;
    }

    return $recipes;
}

function getEnchantRecipeStr( $c_obj, $r ) {
    $st = '';
    $enc = getEnchant( $r[ 'enc_id' ] );
    $st = $st . '<b>' . getModifierString( $enc[ 'm' ], $enc[ 'v' ] ) .
          getEnchantTypeStr( $r[ 'artifact_type' ] ) . '</b><br>';
    $st = $st . '<i>Level ' . $r[ 'min_level' ] . ' artifact required</i><br>';

    $a = ''; $b = '';
    $quantity = getArtifactQuantity( $c_obj, $r[ 'a1' ] );
    if ( $quantity < $r[ 'q1' ] ) {
        $a = '<font color="red">'; $b = ' (' . $quantity . ')</font>';
    } else {
        $b = ' (' . $quantity . ')';
    }
    $st = $st . '<font size="-2">' . $a .
          $r[ 'q1' ] . 'x ' . $r[ 'artifact_name_1' ] . $b;

    if ( $r[ 'a2' ] > 0 ) {
        $a = ''; $b = '';
        $quantity = getArtifactQuantity( $c_obj, $r[ 'a2' ] );
        if ( $quantity < $r[ 'q2' ] ) {
            $a = '<font color="red">'; $b = ' (' . $quantity . ')</font>';
        } else {
            $b = ' (' . $quantity . ')';
        }
        $st = $st . ', ' . $a . $r[ 'q2' ] .
            'x ' . $r[ 'artifact_name_2' ] . $b;
    }

    if ( $r[ 'a3' ] > 0 ) {
        $a = ''; $b = '';
        $quantity = getArtifactQuantity( $c_obj, $r[ 'a3' ] );
        if ( $quantity < $r[ 'q3' ] ) {
            $a = '<font color="red">'; $b = ' (' . $quantity . ')</font>';
        } else {
            $b = ' (' . $quantity . ')';
        }
        $st = $st . ', ' . $a . $r[ 'q3' ] .
            'x ' . $r[ 'artifact_name_3' ] . $b;
    }

    if ( $r[ 'a4' ] > 0 ) {
        $a = ''; $b = '';
        $quantity = getArtifactQuantity( $c_obj, $r[ 'a4' ] );
        if ( $quantity < $r[ 'q4' ] ) {
            $a = '<font color="red">'; $b = ' (' . $quantity . ')</font>';
        } else {
            $b = ' (' . $quantity . ')';
        }
        $st = $st . ', ' . $a . $r[ 'q4' ] .
            'x ' . $r[ 'artifact_name_4' ] . $b;
    }

    $st = $st . '</font>';
    return $st;
}

function getEnchantTypeStr( $type ) {
    $type_st = '';
    switch ( $type ) {
        case sg_artifact_weapon: $type_st = ', Enchant Weapon'; break;
        case sg_artifact_armour_head: $type_st = ', Enchant Head'; break;
        case sg_artifact_armour_chest: $type_st = ', Enchant Chest'; break;
        case sg_artifact_armour_legs: $type_st = ', Enchant Pants'; break;
        case sg_artifact_armour_neck: $type_st = ', Enchant Neck'; break;
        case sg_artifact_armour_trinket: $type_st = ', Enchant Trinket'; break;
        case sg_artifact_armour_hands: $type_st = ', Enchant Hands'; break;
        case sg_artifact_armour_wrists: $type_st = ', Enchant Wrists'; break;
        case sg_artifact_armour_belt: $type_st = ', Enchant Belt'; break;
        case sg_artifact_armour_boots: $type_st = ', Enchant Boots'; break;
        case sg_artifact_armour_ring: $type_st = ', Enchant Ring'; break;
    }
    return $type_st;
}

$recipes = getEnchants();

$output_text = array();

if ( count( $_POST ) > 0 ) {
    if ( isset( $_POST[ 'd' ] ) ) {

        $a_id = intval( $_POST[ 'd' ] );
        $ae_id = intval( $_POST[ 'ae' ] );

        $n_max = getCharArtifactQuantity( $char_obj, $a_id, $ae_id );
        $n = min( 1, $n_max );
        if ( isset( $_POST[ 'dea' ] ) ) { $n = $n_max; }
        if ( isset( $_POST[ 'deabo' ] ) ) { $n = max( 0, $n_max - 1 ); }

        if ( $n > 0 ) {
            $reward_obj = array();
            $artifact = getArtifact( $a_id, $ae_id );
            removeArtifact( $char_obj, $a_id, $n, $ae_id );
            $log_obj->addLog( $char_obj->c, sg_log_disenchant, $a_id, $ae_id, $n, 0 );
            for ( $i = 0; $i < $n; $i++ ) {
                $reward = getDisenchantReward(
                    $artifact[ 'min_level' ], $artifact[ 'rarity' ] );
                $reward_obj[ $reward[ 'a' ] ] += $reward[ 'n' ];
            }
            foreach ( $reward_obj as $k => $v ) {
                $a_obj = getArtifact( $k, 0 );
                $output_text[] = awardArtifactString( $char_obj, $a_obj, $v, 0 );
            }

            $char_obj->save(); // trigger an early artifact save
        }

    } elseif ( isset( $_POST[ 'e' ] ) ) {

        $a_id = intval( $_POST[ 'a' ] );
        $ae_id = intval( $_POST[ 'ae' ] );
        $e_id = intval( $_POST[ 'e' ] );

        $r = $recipes[ $e_id ];

        $artifact = hasArtifact( $char_obj, $a_id, $ae_id );
        if ( FALSE == $artifact ) {
            $output_text[] = '<p class="tip">You don\'t have that!</p>';
        } elseif ( $artifact[ 'quantity' ] < 1 ) {
            $output_text[] = '<p class="tip">You don\'t have that!</p>';
        } elseif ( ! isset( $recipes[ $e_id ] ) ) {
            $output_text[] = '<p class="tip">That recipe doesn\'t exist!</p>';
        } elseif ( $artifact[ 'type' ] != $r[ 'artifact_type' ] ) {
            $output_text[] = '<p class="tip">That recipe can\'t be used on an ' .
                'artifact of that type!</p>';
        } elseif ( $artifact[ 'min_level' ] < $r[ 'min_level' ] ) {
            $output_text[] = '<p class="tip">The level requirement on that ' .
                'artifact is too low for this enchantment!</p>';
        } elseif ( ( getArtifactQuantity( $char_obj, $r[ 'a1' ] ) < $r[ 'q1' ] ) ||
                   ( getArtifactQuantity( $char_obj, $r[ 'a2' ] ) < $r[ 'q2' ] ) ||
                   ( getArtifactQuantity( $char_obj, $r[ 'a3' ] ) < $r[ 'q3' ] ) ||
                   ( getArtifactQuantity( $char_obj, $r[ 'a4' ] ) < $r[ 'q4' ] ) ) {
            $output_text[] = '<p class="tip">You don\'t have enough ' .
                'ingredients!</p>';
        } else {
            removeArtifact( $char_obj, $a_id, 1, $ae_id );
            if ( $r[ 'q1' ] > 0 ) { removeArtifact( $char_obj, $r[ 'a1' ], $r[ 'q1' ], 0 ); }
            if ( $r[ 'q2' ] > 0 ) { removeArtifact( $char_obj, $r[ 'a2' ], $r[ 'q2' ], 0 ); }
            if ( $r[ 'q3' ] > 0 ) { removeArtifact( $char_obj, $r[ 'a3' ], $r[ 'q3' ], 0 ); }
            if ( $r[ 'q4' ] > 0 ) { removeArtifact( $char_obj, $r[ 'a4' ], $r[ 'q4' ], 0 ); }
            $a_obj = getArtifact( $a_id, $r[ 'enc_id' ] );
            $output_text[] = '<p class="tip">You combine the ingredients, and ' .
                'combine them into a new artifact!</p>';
            $output_text[] = awardArtifactString( $char_obj, $a_obj, 1, $r[ 'enc_id' ] );

            $log_obj->addLog( $char_obj->c, sg_log_enchant,
                $a_id, $ae_id, $r[ 'enc_id' ], 0 );
            $char_obj->save(); // trigger an early artifact save
        }

    }
}

$de_obj = getCharDisenchantArtifacts( $char_obj->c[ 'id' ] );

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss( $char_obj->c ); ?>
</head>
<body onload="showEnchantRecipe(document.getElementById('enchant_select').value);">

<? renderPopupText(); ?>

<div class="container">

<?

require '_header.php';
require '_charmenu.php';

if ( count( $output_text ) > 0 ) {
    foreach ( $output_text as $x ) {
        echo $x;
    }
}

echo '<h2>Enchanting</h2>' .
     '<script type="text/javascript" src="include/ts_enchant.js"></script>';

echo '<script type="text/javascript">function hideEnchantRecipes() { ';
foreach ( $recipes as $r ) {
    echo 'document.getElementById(\'recipe_' . $r[ 'id' ] . '\').className = ' .
         '\'invis\'; ';
}
echo '} </script>';

echo '<hr width="300">' .
     '<p><b>Enchant an artifact:</b><br>' .
     '<i>Note: Enchanting an artifact that already has an enchant will ' .
     'replace the old one!</i></p>' .
     '<form action="enchant.php" method="post"><p>' .
     '<input type="hidden" id="artifact_enchant" name="ae" value="0">' .
     '<select name="e" id="enchant_select">';
;
foreach ( $recipes as $r ) {
    $enc = getEnchant( $r[ 'enc_id' ] );
    $type_st = getEnchantTypeStr( $r[ 'artifact_type' ] );
    echo '<option value="' . $r[ 'id' ] . '" ' .
         'onclick="showEnchantRecipe( ' . $r[ 'id' ] . ');"';
    if ( $r[ 'id' ] == $_POST[ 'e' ] ) { echo ' selected'; }
    echo '>' . getModifierString( $enc[ 'm' ], $enc[ 'v' ] ) . $type_st . '</option>';
}
echo '</select></p><p><select name="a">';

$artifact_obj = getCharEnchantables( $char_obj->c[ 'id' ] );
foreach ( $artifact_obj as $a ) {
    $m_st = '';
    if ( $a[ 'm_enc' ] > 0 ) {
        $enc = getEnchant( $a[ 'm_enc' ] );
        $m_st = ', ' . getModifierString( $enc[ 'm' ], $enc[ 'v' ] );
    }
    echo '<option value="' . $a[ 'id' ] . '" ' .
         'onclick="setArtifactEnchant(' . $a[ 'm_enc' ] . ');"';
    if ( $a[ 'id' ] == $_POST[ 'a' ] ) { echo ' selected'; }
    echo '>' . $a[ 'name' ] . $m_st . '</option>';
}
echo '</select><br>';
echo '<input type="submit" name="en" value="Enchant">' .
     '</p></form>';

echo '<p>';
foreach ( $recipes as $r ) {
    echo '<div class="invis" id="recipe_' . $r[ 'id' ] . '">' .
         getEnchantRecipeStr( $char_obj, $r ) . '</div>';
}
echo '</p>';

echo '<hr width="300">' .
     '<p><b>Disenchant some of your artifacts:</b></p>' .
     '<p><form action="enchant.php" method="post">' .
     '<input type="hidden" id="artifact_enchant" name="ae" value="0">' .
     '<select name="d">';
foreach( $de_obj as $artifact ) {
    $m_st = '';
    if ( $artifact[ 'm_enc' ] > 0 ) {
        $enc = getEnchant( $artifact[ 'm_enc' ] );
        $m_st = ', ' . getModifierString( $enc[ 'm' ], $enc[ 'v' ] );
    }
    echo '<option value="' . $artifact[ 'id' ] . '" ' .
         'onclick="setArtifactEnchant(' . $artifact[ 'm_enc' ] . ');"';
    if ( $artifact[ 'id' ] == $_POST[ 'd' ] ) { echo ' selected'; }
    echo '>' . $artifact[ 'name' ] . $m_st . ' (' . $artifact[ 'quantity' ] .
         ' owned)</option>';
}
echo '</select><br>' .
     '<input type="submit" name="deo" value="Disenchant one"> ' .
     '<input type="submit" name="deabo" value="Disenchant all but one"> ' .
     '<input type="submit" name="dea" value="Disenchant all">' .
     '</form></p><p><i>Note: Disenchanting an artifact will <b>destroy</b> ' .
     'it, and reward you with an enchanting component.<br>This can not be ' .
     'undone!</i></p>';

require '_footer.php';
$save = $char_obj->save();
$log_save = $log_obj->save();

?>

</div>
</body>
</html>