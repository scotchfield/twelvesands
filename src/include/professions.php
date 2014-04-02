<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/constants.php';

function getTrueProfessionSkill( $x ) {
    $skill = $x;

    if ( $x >= 30 ) {
        $skill = $x - floor( ( $x - 14 ) / 1.5 );
    } elseif ( $x >= 10 ) {
        $skill = $x - floor( ( $x - 10 ) / 2.0 );
    }

    return $skill;
}

function getMultipleZoneEncounters( $c_obj, $zone_encounters, $n ) {
    $unset_obj = array();
    foreach ( $zone_encounters as $x ) {
        if ( ( $x[ 'artifact_required' ] > 0 ) &&
             ( getCharArtifactQuantity( $c_obj, $x[ 'artifact_required' ] ) == 0 ) ) {
            $unset_obj[] = $x[ 'id' ];
        }
    }
    foreach ( $unset_obj as $x ) {
        unset( $zone_encounters[ $x ] );
    }

    $max_val = 0;
    foreach ( $zone_encounters as $ze ) {
        $max_val = $max_val + $ze[ 'odds_of_occurring' ];
    }

    $encounters = array();
    $artifact_remove = array();
    for ( $i = 1; $i <= $n; $i++ ) {
        $e = getChoiceId( $zone_encounters, $max_val );
        $a_req = $zone_encounters[ $e ][ 'artifact_required' ];
        if ( $a_req > 0 ) {
            $q = getCharArtifactQuantity( $c_obj, $a_req ) - $artifact_remove[ $a_req ];
            if ( $q > 0 ) {
                $encounters[ $e ] += 1;
                $artifact_remove[ $a_req ] = $artifact_remove[ $a_req ] + 1;
            } else {
                $i -= 1;
            }
        } else {
            $encounters[ $e ] += 1;
        }
    }
    foreach ( $artifact_remove as $k => $v ) {
        $artifact = getArtifact( $k );
        removeArtifact( $c_obj, $k, $v );
        echo 'You used ' . $v . 'x ' . $artifact[ 'name' ] . ' during your time ' .
             'here.';
    }

    return $encounters;
}

function goFishingInZone( $c_obj, $z_id, $n ) {
    $fishing_fatigue = applyMultiplier(
        sg_fatigue_fishing, -$c_obj->c[ 'fishing_fatigue' ] );
    $fishing_left = ceil(
        ( 100000 + $c_obj->c[ 'fatigue_rested' ] - $c_obj->c[ 'fatigue' ] ) /
        applyMultiplier( $fishing_fatigue, -$c_obj->c[ 'fatigue_reduction_bonus' ] ) );
    $n = min( $n, $fishing_left );
    if ( $n < 1 ) { $n = 1; }

    $zone_encounters = getAllZoneEncounters( $z_id );

    $encounters = getMultipleZoneEncounters( $c_obj, $zone_encounters, $n );

    $c_obj->addFatigue( $fishing_fatigue * $n );

    $output_obj = array();

    $artifact_xp = 0;
    foreach ( $encounters as $e => $v ) {
        $artifact = getArtifact( $zone_encounters[ $e ][ 'encounter_id' ] );
        $output_obj[] = awardArtifactString( $c_obj, $artifact, $v );
        $artifact_xp += ( $artifact[ 'xp' ] * $v );
    }

    echo '<p>You catch something!</p>';
    foreach ( $output_obj as $x ) {
        echo $x;
    }

    if ( $artifact_xp > 0 ) {
        $add_xp = $c_obj->addXp( $artifact_xp );
        echo '<p>' . $add_xp . ' experience point';
        if ( $add_xp > 1 ) { echo 's'; }
        echo ' awarded.</p>';
    }

    $output_skill = '';
    $skill = getTrueProfessionSkill( $c_obj->c[ 'prof_fishing' ] );
    if ( $skill < $c_obj->c[ 'level' ] * 5 ) {
        for ( $i = 0; $i < $n; $i++ ) {
            if ( getTrueProfessionSkill( $c_obj->c[ 'prof_fishing' ] + 1 ) <=
                   ( $c_obj->c[ 'level' ] * 5 ) ) {
                $c_obj->setProfFishing( $c_obj->c[ 'prof_fishing' ] + 1 );
            } else {
                $i = $n;
            }
        }
        $new_skill = getTrueProfessionSkill( $c_obj->c[ 'prof_fishing' ] );
        if ( $new_skill > $skill ) {
            $output_skill = '<p>Your skill in Fishing has increased to ' .
                $new_skill . '.</p>';
        }
    }
    echo $output_skill;

    $level_check = levelCheck( $c_obj );
    if ( $level_check != FALSE ) {
        echo $level_check;
    }

    return $n;
}

function goMiningInZone( $c_obj, $z_id, $n ) {
    $mining_left = floor( ( 100000 - $c_obj->c[ 'fatigue' ] ) / sg_fatigue_mining );
    $n = min( $n, $mining_left );
    if ( $n < 1 ) { $n = 1; }

    $zone_encounters = getAllZoneEncounters( $z_id );

    $encounters = getMultipleZoneEncounters( $c_obj, $zone_encounters, $n );
    $c_obj->addFatigue( sg_fatigue_mining * $n );

    $output_obj = array();

    $artifact_xp = 0;
    foreach ( $encounters as $e => $v ) {
        $artifact = getArtifact( $zone_encounters[ $e ][ 'encounter_id' ] );
        $output_obj[] = awardArtifactString( $c_obj, $artifact, $v );
        $artifact_xp += ( $artifact[ 'xp' ] * $v );
    }

    echo '<p>You retrieve something!</p>';
    foreach ( $output_obj as $x ) {
        echo $x;
    }

    if ( $artifact_xp > 0 ) {
        $add_xp = $c_obj->addXp( $artifact_xp );
        echo '<p>' . $add_xp . ' experience point';
        if ( $add_xp > 1 ) { echo 's'; }
        echo ' awarded.</p>';
    }

    $output_skill = '';
    $skill = getTrueProfessionSkill( $c_obj->c[ 'prof_mining' ] );
    if ( $skill < $c_obj->c[ 'level' ] * 5 ) {
        for ( $i = 0; $i < $n; $i++ ) {
            if ( getTrueProfessionSkill( $c_obj->c[ 'prof_mining' ] + 1 ) <=
                   ( $c_obj->c[ 'level' ] * 5 ) ) {
                $c_obj->setProfMining( $c_obj->c[ 'prof_mining' ] + 1 );
            } else {
                $i = $n;
            }
        }
        $new_skill = getTrueProfessionSkill( $c_obj->c[ 'prof_mining' ] );
        if ( $new_skill > $skill ) {
            $output_skill = '<p>Your skill in Mining has increased to ' .
                $new_skill . '.</p>';
        }
    }
    echo $output_skill;

    $level_check = levelCheck( $c_obj );
    if ( $level_check != FALSE ) {
        echo $level_check;
    }

    return $n;
}

function checkIncreaseProfessionSkill( $c_obj, $min_skill,
                                       $prof_id, $prof_verb ) {
    $ret_str = '';
    $skill = getTrueProfessionSkill( $c_obj->c[ $prof_id ] );
    if ( ( $skill < $c_obj->c[ 'level' ] * 5) && ( $skill < ( $min_skill + 25 ) ) ) {
        if ( $prof_id == 'prof_crafting' ) {
            $c_obj->setProfCrafting( $c_obj->c[ $prof_id ] + 1 );
        } elseif ( $prof_id == 'prof_cooking' ) {
            $c_obj->setProfCooking( $c_obj->c[ $prof_id ] + 1 );
        }
        $new_skill = getTrueProfessionSkill( $c_obj->c[ $prof_id ] );
        if ( $new_skill > $skill ) {
            $ret_str = '<p>Your skill in ' . $prof_verb . 'ing has increased to ' .
                $new_skill . '.</p>';
        }
    }
    return $ret_str;
}

function getSingleRecipe( $recipe_type, $recipe_id ) {
    $i = esc( $recipe_id );

    $query = "
      SELECT
        c.*,
        a.name, a.plural_name, a.text, a.type, a.rarity, a.armour,
        a.base_damage, a.random_damage, a.min_level,
        a.modifier_type_1, a.modifier_amount_1,
        a.modifier_type_2, a.modifier_amount_2,
        a.modifier_type_3, a.modifier_amount_3,
        a.skill_required,
        a1.name AS artifact_name_1, a2.name AS artifact_name_2,
        a3.name AS artifact_name_3, a4.name AS artifact_name_4
      FROM
        `recipes` AS c, `artifacts` AS a,
        `artifacts` AS a1, `artifacts` AS a2,
        `artifacts` AS a3, `artifacts` AS a4
      WHERE
        c.recipe_type = $recipe_type AND
        c.id = $i AND c.output_id = a.id AND
        c.artifact_id_1 = a1.id AND
        c.artifact_id_2 = a2.id AND
        c.artifact_id_3 = a3.id AND
        c.artifact_id_4 = a4.id
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $recipe = $results->fetch_assoc();
    return $recipe;
}

function getRecipes( $recipe_type, $min_skill ) {
    $skill = intval( $min_skill ) + 20;

    $query = "
      SELECT
        c.*,
        a.name, a.plural_name, a.text, a.type, a.rarity, a.armour,
        a.base_damage, a.random_damage, a.min_level,
        a.modifier_type_1, a.modifier_amount_1,
        a.modifier_type_2, a.modifier_amount_2,
        a.modifier_type_3, a.modifier_amount_3,
        a.skill_required, a.ra,
        a1.name AS artifact_name_1, a2.name AS artifact_name_2,
        a3.name AS artifact_name_3, a4.name AS artifact_name_4
      FROM
        `recipes` AS c, `artifacts` AS a,
        `artifacts` AS a1, `artifacts` AS a2,
        `artifacts` AS a3, `artifacts` AS a4
      WHERE
        c.recipe_type = $recipe_type AND
        c.min_skill <= $skill AND c.output_id = a.id AND
        c.artifact_id_1 = a1.id AND
        c.artifact_id_2 = a2.id AND
        c.artifact_id_3 = a3.id AND
        c.artifact_id_4 = a4.id
      ORDER BY
        c.min_skill DESC
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $recipes = array();

    while ( $r = $results->fetch_assoc() ) {
        $r[ 'name' ] = fixStr( $r[ 'name' ] );
        $r[ 'plural_name' ] = fixStr( $r[ 'plural_name' ] );
        $r[ 'text' ] = fixStr( $r[ 'text' ] );
        $r[ 'artifact_name_1' ] = fixStr( $r[ 'artifact_name_1' ] );
        $r[ 'artifact_name_2' ] = fixStr( $r[ 'artifact_name_2' ] );
        $r[ 'artifact_name_3' ] = fixStr( $r[ 'artifact_name_3' ] );
        $r[ 'artifact_name_4' ] = fixStr( $r[ 'artifact_name_4' ] );

        $recipes[ $r[ 'id' ] ] = $r;
    }

    return $recipes;
}

?>