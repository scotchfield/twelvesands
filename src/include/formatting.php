<?

require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/runes.php';
require_once sg_base_path . 'include/skills.php';

function getDisplayXp( $c ) {
    if ( $c[ 'level' ] > 1 ) {
        $curXp = $c[ 'xp' ] - levelXp( $c[ 'level' ] );
        $levelXp = levelXp( $c[ 'level' ] + 1 ) - levelXp( $c[ 'level' ] );
    } else {
        $curXp = $c[ 'xp' ];
        $levelXp = levelXp( $c[ 'level' ] + 1 );
    }

    if ( $curXp < 0 ) { $curXp = 0; }
    if ( $levelXp < 0 ) { $levelXp = 1; }
    if ( $curXp > $levelXp ) { $curXp = $levelXp; }

    return $curXp . ' / ' . $levelXp;
}

function renderHp( $c ) {
    $f = round( ( 100 * $c[ 'current_hp' ] ) / $c[ 'base_hp' ] );
    if ( $f <= 20 ) {
        echo '<span class="alert">' . $c[ 'current_hp' ] . '</span>';
    } else {
        echo $c[ 'current_hp' ];
    }
}

function renderFatigue( $fatigue ) {
    $f = floor( $fatigue / 1000 );
    if ( $f > 100 ) { $f = 100; }
    if ( $f >= 90 ) {
        echo '<span class="alert">' . $f . '%</span>';
    } else {
        echo $f . '%';
    }
}

function renderTimeRemaining( $currentTime, $expiryTime ) {
    $t = $expiryTime - $currentTime;
    if ( $t < 0 ) {
        return '0m';
    } else if ( $t < 60 ) {
        return $t . 's';
    } else if ( $t < 60 * 60 ) {
        return floor( $t / 60) . 'm';
    } else if ( $t < 60 * 60 * 24 ) {
        return floor( $t / ( 60 * 60 ) ) . "h";
    } else {
        return floor( $t / ( 60 * 60 * 24 ) ) . 'd';
    }

    return '';
}

function getModifierString( $type, $amount ) {
    $st = '';
    switch ( $type ) {
    case sg_skills_bonus_str: $st = $st . '+' . $amount . ' str'; break;
    case sg_skills_bonus_dex: $st = $st . '+' . $amount . ' dex'; break;
    case sg_skills_bonus_int: $st = $st . '+' . $amount . ' int'; break;
    case sg_skills_bonus_cha: $st = $st . '+' . $amount . ' cha'; break;
    case sg_skills_bonus_con: $st = $st . '+' . $amount . ' con'; break;
    case sg_skills_bonus_level: $st = $st . '+' . $amount .
        ' effective level'; break;
    case sg_skills_bonus_max_health: $st = $st . '+' . $amount .
        ' max health'; break;
    case sg_skills_fatigue_reduction: $st = $st . '-' . $amount .
        '% fatigue taken'; break;
    case sg_skills_gold_drop_boost: $st = $st . '+' . $amount .
        '% gold drops'; break;
    case sg_skills_bonus_melee_damage: $st = $st . '+' . $amount .
        ' melee damage'; break;
    case sg_skills_bonus_defend_damage: $st = $st . '+' . $amount .
        ' damage defend'; break;
    case sg_skills_bonus_xp_award_percent: $st = $st . '+' . $amount .
        '% xp award'; break;
    case sg_skills_bonus_rep_award_percent: $st = $st . '+' . $amount .
        '% rep award'; break;
    case sg_skills_bonus_fishing: $st = $st . 'Basic Fishing'; break;
    case sg_skills_bonus_mining: $st = $st . 'Basic Mining'; break;
    case sg_skills_item_drop_boost: $st = $st . '+' . $amount .
        '% item drop rate'; break;
    case sg_skills_bonus_buff_duration: $st = $st . '+' . $amount .
        '% buff duration'; break;
    case sg_skills_bonus_food_reduction: $st = $st . '-' . $amount .
        '% fullness cost when eating'; break;
    case sg_skills_resist_fire: $st = $st . '+' . $amount .
        ' fire resistance'; break;
    case sg_skills_resist_water: $st = $st . '+' . $amount .
        ' water resistance'; break;
    case sg_skills_resist_earth: $st = $st . '+' . $amount .
        ' earth resistance'; break;
    case sg_skills_resist_air: $st = $st . '+' . $amount .
        ' air resistance'; break;
    case sg_skills_resist_arcane: $st = $st . '+' . $amount .
        ' arcane resistance'; break;
    case sg_skills_resist_electric: $st = $st . '+' . $amount .
        ' electric resistance'; break;
    case sg_skills_resist_necro: $st = $st . '+' . $amount .
        ' necromancy resistance'; break;
    case sg_skills_bonus_armour: $st = $st . '+' . $amount . ' armour'; break;
    case sg_skills_bonus_dodge: $st = $st . '+' . $amount . '% dodge'; break;
    case sg_skills_bonus_initiative: $st = $st . '+' . $amount .
        '% combat initiative'; break;
    case sg_skills_bonus_hunger: $st = $st . 'Extra eating room'; break;
    case sg_skills_bonus_crafting_xp: $st = $st . '+' . $amount .
        '% XP when crafting'; break;
    case sg_skills_bonus_noncombat_freq_boost: $st = $st . '+' . $amount .
        '% non-combat frequency'; break;
    case sg_skills_bonus_crit: $st = $st . '+' . $amount .
        '% critical strike'; break;
    case sg_skills_bonus_to_hit: $st = $st . '+' . $amount .
        ' accuracy'; break;
    case sg_skills_bonus_mana_percent: $st = $st . '+' . $amount .
        '% mana'; break;
    case sg_skills_bonus_armour_percent: $st = $st . '+' . $amount .
        '% armour'; break;
    case sg_skills_bonus_health_percent: $st = $st . '+' . $amount .
        '% health'; break;
    case sg_skills_mana_regen: $st = $st . '+' . $amount .
        ' mana regen'; break;
    case sg_skills_bonus_melee_damage_percent: $st = $st . '+' . $amount .
        '% melee damage'; break;
    case sg_skills_bonus_all: $st = $st . '+' . $amount .
        ' all attributes'; break;
    case sg_skills_bonus_spell_damage: $st = $st . '+' . $amount .
        ' spell damage'; break;
    case sg_skills_hp_regen: $st = $st . '+' . $amount . ' health regen'; break;
    case sg_skills_rested_eating_bonus: $st = $st . '+' . $amount .
        '% rested fatigue when eating'; break;
    case sg_skills_resist_magical: $st = $st . '+' . $amount .
        ' magic resistance'; break;
    case sg_skills_track_goodies: $st = $st . 'Occasionally finds goodies ' .
        'after combat'; break;
    case sg_skills_xp_combat_bonus: $st = $st . '+' . $amount .
        '% combat XP'; break;
    case sg_skills_fishing_fatigue_percent: $st = $st . '-' . $amount .
        '% fishing fatigue'; break;
    case sg_skills_convert_magic_to_dmg_percent: $st = $st . $amount .
        '% magic damage taken converted to foe damage'; break;
    case sg_skills_reduce_cook_craft_fatigue_percent: $st = $st . '-' .
        $amount . '% cooking/crafting fatigue'; break;
    default: return ''; break;
    }
    return $st;
}

function getArtifactHoverStr( $artifact, $quantity = 1, $no_desc = FALSE ) {
    $r = '<b><span class=&quot;item_t' . $artifact[ 'rarity' ] . '&quot;>' .
         getEscapeQuoteStr( $artifact[ 'name' ] ) . '</span></b><br>';
    if ( ( isset( $artifact[ 'o_id' ] ) ) && ( $artifact[ 'o_id' ] > 0 ) ) {
        $r = $r . '<font size=&quot;-2&quot;>' .
             getEscapeQuoteStr( $artifact[ 'o_name' ] ) .
             '</font><br>';
    }
    if ( ( isset( $artifact[ 'flags' ] ) ) && ( getBit( $artifact[ 'flags' ], sg_artifact_flag_nosell ) ) ) {
        $r = $r . '<i>Cannot be sold</i><br>';
    }
    if ( ( isset( $artifact[ 'flags' ] ) ) && ( getBit( $artifact[ 'flags' ], sg_artifact_flag_notrade ) ) ) {
        $r = $r . '<i>Cannot be traded</i><br>';
    }

    if ( $no_desc == FALSE ) {
        $r = $r . getEscapeQuoteStr( $artifact[ 'text' ] ) . '<br>';
        switch ( $artifact[ 'type' ] ) {
        case sg_artifact_weapon: $r = $r . '<b>Weapon</b><br>'; break;
        case sg_artifact_combat_usable: $r = $r.'<b>Combat Item</b><br>'; break;
        case sg_artifact_armour_head: $r = $r . '<b>Head</b><br>'; break;
        case sg_artifact_armour_chest: $r = $r . '<b>Chest</b><br>'; break;
        case sg_artifact_armour_legs: $r = $r . '<b>Pants</b><br>'; break;
        case sg_artifact_armour_neck: $r = $r . '<b>Neck</b><br>'; break;
        case sg_artifact_armour_trinket: $r = $r . '<b>Trinket</b><br>'; break;
        case sg_artifact_armour_hands: $r = $r . '<b>Hands</b><br>'; break;
        case sg_artifact_armour_wrists: $r = $r . '<b>Wrists</b><br>'; break;
        case sg_artifact_armour_belt: $r = $r . '<b>Belt</b><br>'; break;
        case sg_artifact_armour_boots: $r = $r . '<b>Boots</b><br>'; break;
        case sg_artifact_armour_ring: $r = $r . '<b>Ring</b><br>'; break;
        default: break;
        }
    }
    $invis_obj = array( 54 );
    if ( ( isset( $artifact[ 'm_enc' ] ) ) && ( $artifact[ 'm_enc' ] > 0 ) ) {
        $enchant = getEnchant( $artifact[ 'm_enc' ] );
        $r = $r . '<b><font color=#00BFFF>' .
             getModifierString( $enchant[ 'm' ], $enchant[ 'v' ] ) . '</font></b><br>';
    }
    if ( $artifact[ 'modifier_type_1' ] > 0 ) {
        if ( ! in_array( $artifact[ 'modifier_type_1' ], $invis_obj ) ) {
            $r = $r . '<b><span class=mod_highlight>' .
                 getModifierString( $artifact[ 'modifier_type_1' ],
                                    $artifact[ 'modifier_amount_1' ] ) .
                 '</span></b><br>';
        }
    }
    if ( $artifact[ 'modifier_type_2' ] > 0 ) {
        $r = $r . '<b><span class=mod_highlight>' .
             getModifierString( $artifact[ 'modifier_type_2' ],
                                $artifact[ 'modifier_amount_2' ] ) . '</span></b><br>';
    }
    if ( $artifact[ 'modifier_type_3' ] > 0 ) {
        $r = $r . '<b><span class=mod_highlight>' .
             getModifierString( $artifact[ 'modifier_type_3' ],
                                $artifact[ 'modifier_amount_3' ] ) . '</span></b><br>';
    }

    if ( $artifact[ 'type' ] == 1 ) {
        $r = $r . '<b>' . $artifact[ 'base_damage' ] . ' - ' .
            ( $artifact[ 'base_damage' ] + $artifact[ 'random_damage' ] ) .
            ' damage</b><br>';
    }
    if ( $artifact[ 'armour' ] > 0 ) {
        $r = $r . '<b>' . $artifact[ 'armour' ] . ' Armour</b><br>';
    }
    if ( $artifact[ 'min_level' ] > 0 ) {
        $r = $r . '<b>Level ' . $artifact[ 'min_level' ] . ' required</b><br>';
    }
    if ( $artifact[ 'skill_required' ] > 0 ) {
        $skill = getSkill( $artifact[ 'skill_required' ] );
        $r = $r . '<b>' . $skill[ 'name' ] . ' required</b><br>';
    }
    if ( ( isset( $artifact[ 'quantity' ] ) ) && ( $artifact[ 'quantity' ] > 0 ) ) {
        $r = $r . '<b>Quantity you own:</b> ' . $artifact[ 'quantity' ] . '<br>';
    }
    if ( ( isset( $artifact[ 'filename' ] ) ) && ( $artifact[ 'filename' ] != '' ) ) {
        $r = $r . '<img src=&quot;' . sg_app_root . '/images/wp-dagger.gif' .
             '&quot; width=&quot;25&quot; height=&quot;25&quot;><br>';
    }

    if ( ( sg_debug ) && ( $_SESSION[ 'u' ] == 1 ) ) {
      $r = $r . 'ID: ' . $artifact[ 'id' ];
    }

    if ( $artifact[ 'ra' ] > 0 ) {
        if ( $artifact[ 'type' ] == 8 ) {
            $render_artifact = getRune( $artifact[ 'ra' ] );
        } else {
            $render_artifact = getArtifact( $artifact[ 'ra' ] );
        }
        $r = $r . '<hr>' . getArtifactHoverStr( $render_artifact );
    }

    return $r;
}

function renderArtifact( $artifact, $quantity = 1 ) {
    echo renderArtifactStr( $artifact, $quantity );
}

function renderArtifactStr( $artifact, $quantity = 1 ) {
    $r = '';
    $r = $r . '<a href="#" onmouseover="popup(\'';
    $r = $r . getArtifactHoverStr( $artifact, $quantity );
    $r = $r . '\')" onmouseout="popout()" class="item" ' .
        'onclick="window.open(\'artifact.php?a=' . $artifact[ 'desc_id' ] .
        '\',\'Artifact\',\'width=300,height=300\');">';
    $r = $r . '<span class="item_t' . $artifact[ 'rarity' ] . '">';
    if ( $quantity == 1 ) {
        $r = $r . $artifact[ 'name' ];
    } else {
        $r = $r . $artifact[ 'plural_name' ];
    }
    $r = $r . '</span></a>';

    return $r;
}

function getCostStr( $c_obj, $artifact ) {
    $cost_array = array();
    if ( $artifact[ 'gold_cost' ] > 0 ) {
        $cost_array[] = $artifact[ 'gold_cost' ] . ' gold';
    }
    if ( $artifact[ 'artifact_cost_1' ] > 0 ) {
        $a = getArtifact( $artifact[ 'artifact_cost_1' ] );
        $name = $a[ 'name' ];
        if ( $artifact[ 'artifact_quantity_1' ] > 1 ) {
            $name = $a[ 'plural_name' ];
        }
        $cost_array[] = $artifact[ 'artifact_quantity_1' ] . ' ' . $name .
            ' (' . getCharArtifactQuantity( $c_obj, $a[ 'id' ] ) . '&nbsp;owned)';
      }
    if ( $artifact[ 'artifact_cost_2' ] > 0 ) {
        $a = getArtifact( $artifact[ 'artifact_cost_2' ] );
        $name = $a[ 'name' ];
        if ( $artifact[ 'artifact_quantity_2' ] > 1 ) {
            $name = $a[ 'plural_name' ];
        }
        $cost_array[] = $artifact[ 'artifact_quantity_2' ] . ' ' . $name .
            ' (' . getCharArtifactQuantity( $c_obj, $a[ 'id' ] ) . '&nbsp;owned)';
    }
    if ( $artifact[ 'artifact_cost_3' ] > 0 ) {
        $a = getArtifact( $artifact[ 'artifact_cost_3' ] );
        $name = $a[ 'name' ];
        if ( $artifact[ 'artifact_quantity_3' ] > 1 ) {
            $name = $a[ 'plural_name' ];
        }
        $cost_array[] = $artifact[ 'artifact_quantity_3' ] . ' ' . $name .
            ' (' . getCharArtifactQuantity( $c_obj, $a[ 'id' ] ) . '&nbsp;owned)';
    }
    if ( $artifact[ 'reputation_id' ] > 0 ) {
        $score_obj = getReputationScore( $artifact[ 'reputation_required' ] );
        $cost_array[] = $score_obj[ 'n' ] .
          ' rep with ' . getReputationName( $artifact[ 'reputation_id' ] );
    }
    return join( ', ', $cost_array );
}

function getRecipeRequirementsStr( $c_obj, $r ) {
    $st = '';

    $a = ''; $b = '';
    $quantity = getArtifactQuantity( $c_obj, $r[ 'artifact_id_1' ] );
    if ( $quantity < $r[ 'artifact_quantity_1' ] ) {
        $a = '<font color="red">'; $b = ' (' . $quantity . ')</font>';
    } else {
        $b = ' (' . $quantity . ')';
    }
    $st = '<font size="-2">' . $a .
        $r[ 'artifact_quantity_1' ] . 'x ' . $r[ 'artifact_name_1' ] . $b;

    if ( $r[ 'artifact_id_2' ] > 0 ) {
        $a = ''; $b = '';
        $quantity = getArtifactQuantity( $c_obj, $r[ 'artifact_id_2' ] );
        if ( $quantity < $r[ 'artifact_quantity_2' ] ) {
            $a = '<font color="red">'; $b = ' (' . $quantity . ')</font>';
        } else {
            $b = ' (' . $quantity . ')';
        }
        $st = $st . ', ' . $a . $r[ 'artifact_quantity_2' ] .
            'x ' . $r[ 'artifact_name_2' ] . $b;
    }

    if ( $r[ 'artifact_id_3' ] > 0 ) {
        $a = ''; $b = '';
        $quantity = getArtifactQuantity( $c_obj, $r[ 'artifact_id_3' ] );
        if ( $quantity < $r[ 'artifact_quantity_3' ] ) {
            $a = '<font color="red">'; $b = ' (' . $quantity . ')</font>';
        } else {
            $b = ' (' . $quantity . ')';
        }
        $st = $st . ', ' . $a . $r[ 'artifact_quantity_3' ] .
            'x ' . $r[ 'artifact_name_3' ] . $b;
    }

    if ( $r[ 'artifact_id_4' ] > 0 ) {
        $a = ''; $b = '';
        $quantity = getArtifactQuantity( $c_obj, $r[ 'artifact_id_4' ] );
        if ( $quantity < $r[ 'artifact_quantity_4' ] ) {
            $a = '<font color="red">'; $b = ' (' . $quantity . ')</font>';
        } else {
            $b = ' (' . $quantity . ')';
        }
        $st = $st . ', ' . $a . $r[ 'artifact_quantity_4' ] .
            'x ' . $r[ 'artifact_name_4' ] . $b;
    }

    if ( $r[ 'trade_skill_required' ] > 0 ) {
        $a = ''; $b = '';
        if ( $r[ 'trade_skill_required' ] == 1 ) {
            $skill_name = 'Enchanting';
            if ( getFlagValue( $c_obj, sg_flag_enchanting ) == 0 ) {
                $a = '<font color="red">'; $b = '</font>';
            }
        } else {
            $skill = getSkill( $r[ 'trade_skill_required' ] );
            $skill_name = $skill[ 'name' ];
            if ( ! array_key_exists( $r[ 'trade_skill_required' ], $c_obj->c[ 'skills' ] ) ) {
                $a = '<font color="red">'; $b = '</font>';
            }
        }

        $st = $st . ', ' . $a . $skill_name . ' required' . $b;
    }
    $st = $st . '</font>';
    return $st;
}

function getStateIconStr( $name, $img ) {
    return '<img src="' . $img . '" ' .
        'onmouseover="popup(\'<b>' . $name . '</b>\')" ' .
        'onmouseout="popout()" ' .
        'alt="' . $name . '" height="24" width="24">';
}

function renderBarStr( $val, $val_max, $td_left, $td_right, $width ) {
    $st = '<table class="stat"><tr>';

    $left_width = ( $val * $width ) / $val_max;
    if ( $left_width > 0 ) {
        $st = $st . '<td class="' . $td_left . '" width="' .
            ceil($left_width) . '"></td>';
    }
    if ( $left_width < $width ) {
        $st = $st . '<td class="' . $td_right . '" width="' .
            ceil( $width - $left_width ) . '"></td>';
    }
    $st = $st . '</tr></table>';

    return $st;
}

function renderArtifactWithEquippedStr( $c_obj, $artifact, $quantity = 1 ) {
    $r = '<a href="#" onclick="window.open(\'artifact.php?a=' .
        $artifact[ 'desc_id' ] .
        '\',\'Artifact\',\'width=300,height=300\');" ' .
        'onmouseover="popup(\'' .
        getArtifactHoverStr( $artifact, $quantity );

    $equip_array = array(
        sg_artifact_weapon,
        sg_artifact_armour_head,
        sg_artifact_armour_chest,
        sg_artifact_armour_legs,
        sg_artifact_armour_neck,
        sg_artifact_armour_trinket,
        sg_artifact_armour_hands,
        sg_artifact_armour_wrists,
        sg_artifact_armour_belt,
        sg_artifact_armour_boots,
        sg_artifact_armour_ring);

    if ( in_array( $artifact[ 'type' ], $equip_array ) ) {
        $r = $r . '<hr><i>Currently Equipped</i><br>';
    }

    switch ( $artifact[ 'type' ] ) {
    case sg_artifact_weapon:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'weapon' ],
            $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_head:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_head' ],
            $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_chest:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_chest' ],
            $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_legs:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_legs' ],
            $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_neck:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_neck' ],
            $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_trinket:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_trinket' ],
            $quantity = 1, $no_desc = TRUE ) . '<hr>' .
            getArtifactHoverStr( $c_obj->c[ 'armour_trinket_2' ],
                $quantity = 1, $no_desc = TRUE ) . '<hr>' .
            getArtifactHoverStr( $c_obj->c[ 'armour_trinket_3' ],
                $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_hands:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_hands' ],
            $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_wrists:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_wrists' ],
            $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_belt:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_belt' ],
            $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_boots:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_boots' ],
            $quantity = 1, $no_desc = TRUE );
        break;
    case sg_artifact_armour_ring:
        $r = $r . getArtifactHoverStr( $c_obj->c[ 'armour_ring' ],
            $quantity = 1, $no_desc = TRUE ) . '<hr>' .
            getArtifactHoverStr( $c_obj->c[ 'armour_ring_2' ],
                $quantity = 1, $no_desc = TRUE );
        break;
    }

    $r = $r . '\')" onmouseout="popout()" class="item">';
    $r = $r . '<span class="item_t' . $artifact[ 'rarity' ] . '">';
    if ( $quantity == 1 ) {
        $r = $r . $artifact[ 'name' ];
    } else {
        $r = $r . $artifact[ 'plural_name' ];
    }
    $r = $r . '</span></a>';

    return $r;
}

?>