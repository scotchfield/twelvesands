<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/validate.php'; 
require_once sg_base_path . 'include/professions.php';

$a = getGetStr( 'a', '0' );
$t = getGetInt( 't', 0 );

$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$t_obj = array(
    sg_recipetype_cooking => array(
        'id'   => sg_recipetype_cooking,
        'type' => 'prof_cooking',
        'verb' => 'cook',
        'verb_cap' => 'Cook',
        'n1' => 'Meat',
        't1' => sg_cooktype_meat,
        'n2' => 'Baked',
        't2' => sg_cooktype_baked,
        'n3' => 'Fish',
        't3' => sg_cooktype_fish,
        'n4' => 'Vegetable',
        't4' => sg_cooktype_mushroom,
    ),
    sg_recipetype_crafting => array(
        'id'   => sg_recipetype_crafting,
        'type' => 'prof_crafting',
        'verb' => 'craft',
        'verb_cap' => 'Craft',
        'n1' => 'Weapons',
        't1' => sg_crafttype_weapon,
        'n2' => 'Armour',
        't2' => sg_crafttype_armour,
        'n3' => 'Metals',
        't3' => sg_crafttype_metal,
        'n4' => 'Usable',
        't4' => sg_crafttype_usable,
    ),
);

$t_valid = FALSE;
if ( array_key_exists( $t, $t_obj ) ) {
    $t_valid = TRUE;
    $recipes = getRecipes( $t,
        getTrueProfessionSkill( $char_obj->c[ $t_obj [ $t ][ 'type' ] ] ) );
}

function canCreate( $c_obj, $t_array, $r, $n ) {
    if ( ( getTrueProfessionSkill( $c_obj->c[ $t_array[ 'type' ] ] ) >=
             $r[ 'min_skill' ] ) &&
         ( getCharArtifactQuantity( $c_obj, $r[ 'artifact_id_1' ] ) >=
         $r[ 'artifact_quantity_1' ] * $n ) &&
         ( getCharArtifactQuantity( $c_obj, $r[ 'artifact_id_2' ] ) >=
         $r[ 'artifact_quantity_2' ] * $n ) &&
         ( getCharArtifactQuantity( $c_obj, $r[ 'artifact_id_3' ] ) >=
         $r[ 'artifact_quantity_3' ] * $n ) &&
         ( getCharArtifactQuantity( $c_obj, $r[ 'artifact_id_4' ] ) >=
         $r[ 'artifact_quantity_4' ] * $n )
       ) {
        if ( $c_obj->c[ 'fatigue' ] + ( $r[ 'fatigue' ] * $n ) < sg_max_fatigue ) {
            if ( ( $r[ 'trade_skill_required' ] == 0 ) ||
                   ( array_key_exists( $r[ 'trade_skill_required' ],
                                   $c_obj->c[ 'skills' ] ) ) ||
                 ( ( $r[ 'trade_skill_required' ] == 1 ) &&
                   ( getFlagValue( $c_obj, sg_flag_enchanting ) > 0 ) ) ) {
                if ( ( $r[ 'flag_id' ] == 0 ) ||
                     ( ( $r[ 'flag_id' ] > 0 ) &&
                       ( getFlagBit( $c_obj, $r[ 'flag_id' ], $r[ 'flag_bit' ] ) ) ) ) {
                    return TRUE;
                }
            }
        }
    }
    return FALSE;
}

function renderRecipe( $c_obj, $t_array, $r ) {
    $r_tmp = $r[ 'id' ];
    $r[ 'id' ] = $r[ 'output_id' ];
    $recipe_str = renderArtifactStr( $r );
    $r[ 'id' ] = $r_tmp;

    if ( canCreate( $c_obj, $t_array, $r, 1 ) ) {
        echo '<p>' . $recipe_str;
        echo ' (skill ' . $r[ 'min_skill' ] . ') ';
        echo '<font size="-2">(<a href="recipe.php?t=' . $t_array[ 'id' ] .
             '&a=c&i=' . $r[ 'id' ] . '&n=1">' . $t_array[ 'verb' ] . '</a>&nbsp;/' .
             '&nbsp;<a href="recipe.php?t=' . $t_array[ 'id' ] .
             '&a=c&i=' . $r[ 'id' ] . '">' . $t_array[ 'verb' ] .
             '&nbsp;multiple</a>)</font>';
        echo '<br>';
        echo getRecipeRequirementsStr( $c_obj, $r );
        echo '</p>' . "\n";
    } else {
        $show_recipe = ( $r[ 'default_hide' ] != 1 ) || ( ( $r[ 'default_hide' ] == 1 ) &&
            ( ( array_key_exists( $r[ 'trade_skill_required' ],
                                  $c_obj->c[ 'skills' ] ) ) ||
              ( ( $r[ 'trade_skill_required' ] == 1 ) &&
                ( getFlagValue( $c_obj, sg_flag_enchanting ) > 0 ) ) ) ) ||
              ( ( $r[ 'flag_id' ] > 0 ) &&
                ( getFlagBit( $c_obj, $r[ 'flag_id' ], $r[ 'flag_bit' ] ) ) );

        if ( $show_recipe ) {
            $skill = getTrueProfessionSkill(
                $c_obj->c[ $t_array[ 'type' ] ] ) < $r[ 'min_skill' ];
            echo '<p><s>' . $recipe_str;
            echo '</s> (';
            if ( $skill == TRUE ) {
                echo '<font color="red">skill ' . $r[ 'min_skill' ] . ' required</font>';
            } else {
                echo 'skill ' . $r[ 'min_skill' ];
            }
            echo ')<br>';
            echo getRecipeRequirementsStr( $c_obj, $r );
            echo '</p>' . "\n";
        }
    }
}

$output_text = array();

if ( 'c' == $a ) {
    $i = getGetStr( 'i', '0' );
    $n = getGetInt( 'n', 0 );
    $r = getSingleRecipe( $t, $i );

    if ( $n < 0 ) { $n = 0; }

    if ( FALSE == $r ) {
        $output_text[] = '<p>You can\'t ' . $t_obj[ $t ][ 'verb' ] .
            ' that right now!</p>';
    } elseif ( $n < 1 ) {
        $output_text[] = '<p><b>Recipe:</b> ' . renderArtifactStr( $r ) . '</p>';
        $output_text[] = '<form method="get" action="recipe.php">' .
            '<input type="hidden" name="i" value="' . $i . '">' .
            '<input type="hidden" name="a" value="c">' .
            '<input type="hidden" name="t" value="' . $t . '">' .
            '<p>How many do you want to create? ' .
            '<input type="text" name="n" size="3" value="1">' .
            '&nbsp;<input type="submit" value="Create!">' .
            '</p></form>';

    } elseif ( ! canCreate( $char_obj, $t_obj[ $t ], $r, $n ) ) {
        $output_text[] = '<p>You can\'t ' . $t_obj[ $t ][ 'verb' ] .
            ' that right now!</p>';
    } else {
        $output_text[] = '<p><b>You combine the artifacts..</b></p>';
        if ( $r[ 'artifact_id_1' ] > 0) {
            removeArtifact( $char_obj,
                            $r[ 'artifact_id_1' ], $r[ 'artifact_quantity_1' ] * $n );
        }
        if ( $r[ 'artifact_id_2' ] > 0) {
            removeArtifact( $char_obj,
                            $r[ 'artifact_id_2' ], $r[ 'artifact_quantity_2' ] * $n );
        }
        if ( $r[ 'artifact_id_3' ] > 0) {
            removeArtifact( $char_obj,
                            $r[ 'artifact_id_3' ], $r[ 'artifact_quantity_3' ] * $n );
        }
        if ( $r[ 'artifact_id_4' ] > 0) {
            removeArtifact( $char_obj,
                            $r[ 'artifact_id_4' ], $r[ 'artifact_quantity_4' ] * $n );
        }

        $award = getArtifact( $r[ 'output_id' ] );
        $output_text[] = awardArtifactString(
            $char_obj, $award, $r[ 'output_quantity' ] * $n );
        $output_skill = '';
        for ( $i = 0; $i < $n; $i++ ) {
            $output_skill = checkIncreaseProfessionSkill(
                $char_obj, $r[ 'min_skill' ], $t_obj[ $t ][ 'type' ], $t_obj[ $t ][ 'verb' ] );
        }
        $output_text[] = $output_skill;
        $fatigue = $char_obj->c[ 'fatigue' ] + ( $r[ 'fatigue' ] * $n *
            ( 1.0 - ( $char_obj->c[ 'reduce_craft_fatigue' ] / 100.0 ) ) );
        $char_obj->setFatigue( $fatigue );
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
require '_charmenu.php';

if ( TRUE == $t_valid ) {

    if ( count( $output_text ) > 0 ) {
        foreach ( $output_text as $x ) {
            echo $x;
        }
    }

    echo '<p><span class="section_header">' . $t_obj[ $t ][ 'verb_cap' ] .
         ' something:</span><br>';
    echo 'Your current ' . $t_obj[ $t ][ 'verb' ] . 'ing skill is <b>' .
         getTrueProfessionSkill( $char_obj->c[ $t_obj[ $t ][ 'type' ] ] ) .
         '</b>.</p>';

    echo '<table class="profession" width="100%">' .
         '<tr><td valign="top" width="50%">';

    echo '<p><span class="section_header">' .
         $t_obj[ $t ][ 'n1' ] . '</span></p>';
    foreach ( $recipes as $r ) {
        if ( $r[ 'recipe_subtype' ] == $t_obj[ $t ][ 't1' ] ) {
            renderRecipe( $char_obj, $t_obj[ $t ], $r );
        }
    }

    echo '<p><span class="section_header">' .
         $t_obj[ $t ][ 'n2' ] . '</span></p>';
    foreach ( $recipes as $r ) {
        if ( $r[ 'recipe_subtype' ] == $t_obj[ $t ][ 't2' ] ) {
            renderRecipe( $char_obj, $t_obj[ $t ], $r );
        }
    }

    echo '</td><td valign="top" width="50%">';

    echo '<p><span class="section_header">' .
         $t_obj[ $t ][ 'n3' ] . '</span></p>';
    foreach ( $recipes as $r ) {
        if ( $r[ 'recipe_subtype' ] == $t_obj[ $t ][ 't3' ] ) {
            renderRecipe( $char_obj, $t_obj[ $t ], $r );
        }
    }

    echo '<p><span class="section_header">' .
         $t_obj[ $t ][ 'n4' ] . '</span></p>';
    foreach ( $recipes as $r ) {
        if ( $r[ 'recipe_subtype' ] == $t_obj[ $t ][ 't4' ] ) {
            renderRecipe( $char_obj, $t_obj[ $t ], $r );
        }
    }

    echo '</td></tr></table>';

} else {

    echo '<p><h3>You don\'t know how to create ' .
         'that type of artifact!</h3></p>';

}

require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>