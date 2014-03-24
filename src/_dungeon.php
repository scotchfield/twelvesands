<?

function setLabyrinthFlagLocation( $c_obj, $loc ) {
    $c_obj->addFlag( sg_flag_great_labyrinth,
        ( getFlagValue( $c_obj, sg_flag_great_labyrinth ) & ( ~63 ) ) + $loc );
}

switch ( $zone[ 'id' ] ) {
    case 88: // Almok Crypts Chapel
        $f = getFlagValue( $char_obj, sg_flag_almok_chapel );
        if ( ! getBit( $f, 0 ) ) {
            $encounter = getTreasure( 18 );
            $encounter[ 'flag_id_set' ] = sg_flag_almok_chapel;
            $encounter[ 'flag_bit_set' ] = 0;
        } elseif ( ! getBit( $f, 1 ) ) {
            $encounter = getFoe( $char_obj, 153 );
            $encounter[ 'flag_id_set' ] = sg_flag_almok_chapel;
            $encounter[ 'flag_bit_set' ] = 1;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 2 ) ) {
            $encounter = getFoe( $char_obj, 153 );
            $encounter[ 'flag_id_set' ] = sg_flag_almok_chapel;
            $encounter[ 'flag_bit_set' ] = 2;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 3 ) ) {
            $encounter = getFoe( $char_obj, 154 );
            $encounter[ 'flag_id_set' ] = sg_flag_almok_chapel;
            $encounter[ 'flag_bit_set' ] = 3;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 4 ) ) {
            $encounter = getFoe( $char_obj, 153 );
            $encounter[ 'flag_id_set' ] = sg_flag_almok_chapel;
            $encounter[ 'flag_bit_set' ] = 4;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 5 ) ) {
            $encounter = getFoe( $char_obj, 153 );
            $encounter[ 'flag_id_set' ] = sg_flag_almok_chapel;
            $encounter[ 'flag_bit_set' ] = 5;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 6 ) ) {
            $encounter = getFoe( $char_obj, 155 );
            $encounter[ 'flag_id_set' ] = sg_flag_almok_chapel;
            $encounter[ 'flag_bit_set' ] = 6;
            initiateCombat( $char_obj, $encounter, $zone );
        } else {
            $encounter = getTreasure( 19 );
        }
        break;

    case 92:
        $f = getFlagValue( $char_obj, sg_flag_emerald_caves );
        if ( ! getBit( $f, 0 ) ) {
            $encounter = getTreasure( 21 );
            $encounter[ 'flag_id_set' ] = sg_flag_emerald_caves;
            $encounter[ 'flag_bit_set' ] = 0;
        } elseif ( ! getBit( $f, 1 ) ) {
            $encounter = getFoe( $char_obj, 164 );
            $encounter[ 'flag_id_set' ] = sg_flag_emerald_caves;
            $encounter[ 'flag_bit_set' ] = 1;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 2 ) ) {
            $encounter = getFoe( $char_obj, 165 );
            $encounter[ 'flag_id_set' ] = sg_flag_emerald_caves;
            $encounter[ 'flag_bit_set' ] = 2;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 3 ) ) {
            $encounter = getFoe( $char_obj, 166 );
            $encounter[ 'flag_id_set' ] = sg_flag_emerald_caves;
            $encounter[ 'flag_bit_set' ] = 3;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 4 ) ) {
            $encounter = getTreasure( 22 );
        }
        break;

    case 89: // Almok Crypts Burial Vaults
        $f = getFlagValue( $char_obj, sg_flag_almok_vaults );
        if ( ! getBit( $f, 0 ) ) {
          $encounter = getFoe( $char_obj, 167 );
          $encounter[ 'flag_id_set' ] = sg_flag_almok_vaults;
          $encounter[ 'flag_bit_set' ] = 0;
          initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 1 ) ) {
          $encounter = getFoe( $char_obj, 168 );
          $encounter[ 'flag_id_set' ] = sg_flag_almok_vaults;
          $encounter[ 'flag_bit_set' ] = 1;
          initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 2 ) ) {
          $encounter = getFoe( $char_obj, 169 );
          $encounter[ 'flag_id_set' ] = sg_flag_almok_vaults;
          $encounter[ 'flag_bit_set' ] = 2;
          initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 3 ) ) {
          $encounter = getFoe( $char_obj, 170 );
          $encounter[ 'flag_id_set' ] = sg_flag_almok_vaults;
          $encounter[ 'flag_bit_set' ] = 3;
          initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 4 ) ) {
          $encounter = getFoe( $char_obj, 167 );
          $encounter[ 'flag_id_set' ] = sg_flag_almok_vaults;
          $encounter[ 'flag_bit_set' ] = 4;
          initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 5 ) ) {
          $encounter = getFoe( $char_obj, 168 );
          $encounter[ 'flag_id_set' ] = sg_flag_almok_vaults;
          $encounter[ 'flag_bit_set' ] = 5;
          initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 6 ) ) {
          $encounter = getFoe( $char_obj, 169 );
          $encounter[ 'flag_id_set' ] = sg_flag_almok_vaults;
          $encounter[ 'flag_bit_set' ] = 6;
          initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 7 ) ) {
          $encounter = getFoe( $char_obj, 171 );
          $encounter[ 'flag_id_set' ] = sg_flag_almok_vaults;
          $encounter[ 'flag_bit_set' ] = 7;
          initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 8 ) ) {
          $encounter = getTreasure( 23 );
        }
        break;

    case 102: // Almok Crypts Stables
        // getEncounter for this instance, up to the seventh..
        $f = getFlagValue( $char_obj, sg_flag_almok_stables );
        if ( ! getBit( $f, 0 ) ) {
            $encounter = getEncounter( $char_obj, $zone[ 'id' ] );
            if ( ( $encounter[ 'type' ] == sg_encounter_treasure ) &&
                 ( $encounter[ 'flag_id_set' ] > 0 ) ) {
                $s_bit = ( 1 << $encounter[ 'flag_bit_set' ] );
                $new_value = getFlagValue(
                    $char_obj, $encounter[ 'flag_id_set' ] ) | $s_bit;
                $char_obj->addFlag( $encounter[ 'flag_id_set' ], $new_value );
            }
            $encounter[ 'flag_id_set' ] = sg_flag_almok_stables;
            $encounter[ 'flag_bit_set' ] = 0;
            if ( $encounter[ 'type' ] == sg_encounter_foe ) {
                initiateCombat( $char_obj, $encounter, $zone );
            }
        } elseif ( ! getBit( $f, 1 ) ) {
            $encounter = getEncounter( $char_obj, $zone[ 'id' ] );
            if ( ( $encounter[ 'type' ] == sg_encounter_treasure ) &&
                 ( $encounter[ 'flag_id_set' ] > 0 ) ) {
                $s_bit = ( 1 << $encounter[ 'flag_bit_set' ] );
                $new_value = getFlagValue(
                    $char_obj, $encounter[ 'flag_id_set' ] ) | $s_bit;
                $char_obj->addFlag( $encounter[ 'flag_id_set' ], $new_value );
            }
            $encounter[ 'flag_id_set' ] = sg_flag_almok_stables;
            $encounter[ 'flag_bit_set' ] = 1;
            if ( $encounter[ 'type' ] == sg_encounter_foe ) {
                initiateCombat( $char_obj, $encounter, $zone );
            }
        } elseif ( ! getBit( $f, 2 ) ) {
            $encounter = getEncounter( $char_obj, $zone[ 'id' ] );
            if ( ( $encounter[ 'type' ] == sg_encounter_treasure ) &&
                 ( $encounter[ 'flag_id_set' ] > 0 ) ) {
                $s_bit = ( 1 << $encounter[ 'flag_bit_set' ] );
                $new_value = getFlagValue(
                    $char_obj, $encounter[ 'flag_id_set' ] ) | $s_bit;
                $char_obj->addFlag( $encounter[ 'flag_id_set' ], $new_value );
            }
            $encounter[ 'flag_id_set' ] = sg_flag_almok_stables;
            $encounter[ 'flag_bit_set' ] = 2;
            if ( $encounter[ 'type' ] == sg_encounter_foe ) {
                initiateCombat( $char_obj, $encounter, $zone );
            }
        } elseif ( ! getBit( $f, 3 ) ) {
            $encounter = getEncounter( $char_obj, $zone[ 'id' ] );
            if ( ( $encounter[ 'type' ] == sg_encounter_treasure ) &&
                 ( $encounter[ 'flag_id_set' ] > 0)) {
                $s_bit = ( 1 << $encounter[ 'flag_bit_set' ] );
                $new_value = getFlagValue(
                    $char_obj, $encounter[ 'flag_id_set' ] ) | $s_bit;
                $char_obj->addFlag( $encounter[ 'flag_id_set' ], $new_value );
            }
            $encounter[ 'flag_id_set' ] = sg_flag_almok_stables;
            $encounter[ 'flag_bit_set' ] = 3;
            if ( $encounter[ 'type' ] == sg_encounter_foe ) {
                initiateCombat( $char_obj, $encounter, $zone );
            }
        } elseif ( ! getBit( $f, 4 ) ) {
            $encounter = getEncounter( $char_obj, $zone[ 'id' ] );
            if ( ( $encounter[ 'type' ] == sg_encounter_treasure ) &&
                 ( $encounter[ 'flag_id_set' ] > 0 ) ) {
                $s_bit = ( 1 << $encounter[ 'flag_bit_set' ] );
                $new_value = getFlagValue(
                    $char_obj, $encounter[ 'flag_id_set' ] ) | $s_bit;
                $char_obj->addFlag( $encounter[ 'flag_id_set' ], $new_value );
            }
            $encounter[ 'flag_id_set' ] = sg_flag_almok_stables;
            $encounter[ 'flag_bit_set' ] = 4;
            if ( $encounter[ 'type' ] == sg_encounter_foe ) {
                initiateCombat( $char_obj, $encounter, $zone );
            }
        } elseif ( ! getBit( $f, 5 ) ) {
            $encounter = getEncounter( $char_obj, $zone[ 'id' ] );
            if ( ( $encounter[ 'type' ] == sg_encounter_treasure ) &&
                 ( $encounter[ 'flag_id_set' ] > 0 ) ) {
                $s_bit = ( 1 << $encounter[ 'flag_bit_set' ] );
                $new_value = getFlagValue(
                    $char_obj, $encounter[ 'flag_id_set' ] ) | $s_bit;
                $char_obj->addFlag( $encounter[ 'flag_id_set' ], $new_value );
            }
            $encounter[ 'flag_id_set' ] = sg_flag_almok_stables;
            $encounter[ 'flag_bit_set' ] = 5;
            if ( $encounter[ 'type' ] == sg_encounter_foe ) {
                initiateCombat( $char_obj, $encounter, $zone );
            }
        } elseif ( ! getBit( $f, 6 ) ) {
            $encounter = getFoe( $char_obj, 179 );
            $encounter[ 'flag_id_set' ] = sg_flag_almok_stables;
            $encounter[ 'flag_bit_set' ] = 6;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 7 ) ) {
            $encounter = getTreasure( 29 );
        }
        break;

    case 101: // Depths of the Goldstone Tower
        $encounter = getTreasure( 33 );
        break;

    case 124: // Lost Treasury
        $f = getFlagValue( $char_obj, sg_flag_lost_treasury );
        if ( ! getBit( $f, 0 ) ) {
            $encounter = getTreasure( 42 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 0;
        } elseif ( ! getBit( $f, 1 ) ) {
            $encounter = getFoe( $char_obj, 229 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 1;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 2 ) ) {
            $encounter = getFoe( $char_obj, 229 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 2;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 3 ) ) {
            $encounter = getFoe( $char_obj, 230 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 3;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 4 ) ) {
            $encounter = getFoe( $char_obj, 230 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 4;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 5 ) ) {
            $encounter = getChoiceEncounter( 12 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 5;
        } elseif ( ! getBit( $f, 6 ) ) {
            $encounter = getFoe( $char_obj, 231 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 6;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 7 ) ) {
            $encounter = getFoe( $char_obj, 231 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 7;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 8 ) ) {
            $encounter = getFoe( $char_obj, 231 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 8;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 9 ) ) {
            $encounter = getFoe( $char_obj, 231 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 9;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 10 ) ) {
            $encounter = getFoe( $char_obj, 210 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 10;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 11 ) ) {
            $encounter = getFoe( $char_obj, 210 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 11;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 12 ) ) {
            $encounter = getFoe( $char_obj, 211 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 12;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 13 ) ) {
            $encounter = getFoe( $char_obj, 211 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 13;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 14 ) ) {
            $encounter = getFoe( $char_obj, 211 );
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 14;
            initiateCombat( $char_obj, $encounter, $zone );
        } elseif ( ! getBit( $f, 15 ) ) {
            if ( getBit( $f, 28 ) ) {
                $encounter = getFoe( $char_obj, 232 );
            } elseif ( getBit( $f, 29 ) ) {
                $encounter = getFoe( $char_obj, 233 );
            } elseif ( getBit( $f, 30 ) ) {
                $encounter = getFoe( $char_obj, 234 );
            }
            $encounter[ 'flag_id_set' ] = sg_flag_lost_treasury;
            $encounter[ 'flag_bit_set' ] = 15;
            initiateCombat( $char_obj, $encounter, $zone );
        } else {
            $encounter = getTreasure( 46 );
        }
        break;

    case 131: // The Great Labyrinth (first floor)
        $f = getFlagValue( $char_obj, sg_flag_great_labyrinth );
        $f_location = $f & 63;

        switch ( $f_location ) {
            case 0:
                if ( $f & ( 1 << 6 ) ) {
                    $encounter = getTreasure( 54 );
                    setLabyrinthFlagLocation( $char_obj, 1 );
                } else {
                    $encounter = getChoiceEncounter( 13 );
                }
            break;
        case 1:
            $encounter = getChoiceEncounter( 14 );
            break;
        case 2:
            if ( $f & ( 1 << 8 ) ) {
                $char_obj->disableFlagBit( 60, 7 );
                $char_obj->disableFlagBit( 60, 8 );
                $f = getFlagValue( $char_obj, sg_flag_great_labyrinth );
            }

            if ( $f & ( 1 << 7 ) ) {
                $encounter = getChoiceEncounter( 30 );
            } else {
                $encounter = getChoiceEncounter( 29 );
            }
            break;
        case 3:
            if ( $f & ( 1 << 16 ) ) {
                $encounter = getChoiceEncounter( 15 );
            } else {
                $encounter = getFoe( $char_obj, 238 );
                $encounter[ 'flag_id_set' ] = 60;
                $encounter[ 'flag_bit_set' ] = 16;
                initiateCombat( $char_obj, $encounter, $zone );
            }
            break;
        case 4:
            if ( $f & ( 1 << 17 ) ) {
                $encounter = getChoiceEncounter( 17 );
            } else {
                $encounter = getFoe( $char_obj, 238 );
                $encounter[ 'flag_id_set' ] = 60;
                $encounter[ 'flag_bit_set' ] = 17;
                initiateCombat( $char_obj, $encounter, $zone );
            }
            break;
        case 5:
            if ( $f & ( 1 << 10 ) ) {
                $char_obj->disableFlagBit( 60, 9 );
                $char_obj->disableFlagBit( 60, 10 );
                $f = getFlagValue( $char_obj, sg_flag_great_labyrinth );
            }

            if ( $f & ( 1 << 9 ) ) {
                $encounter = getChoiceEncounter( 32 );
            } else {
                $encounter = getChoiceEncounter( 31 );
            }
            break;
        case 6:
            $encounter = getChoiceEncounter( 18 );
            break;
        case 7:
            $encounter = getChoiceEncounter( 16 );
            break;
        case 8:
            $encounter = getChoiceEncounter( 20 );
            break;
        case 9:
            if ( $f & ( 1 << 12 ) ) {
                $char_obj->disableFlagBit( 60, 11 );
                $char_obj->disableFlagBit( 60, 12 );
                $f = getFlagValue( $char_obj, sg_flag_great_labyrinth );
            }

            if ( $f & ( 1 << 11 ) ) {
                $encounter = getChoiceEncounter( 34 );
            } else {
                $encounter = getChoiceEncounter( 33 );
            }
            break;
        case 10:
            if ( $f & ( 1 << 18 ) ) {
                $encounter = getChoiceEncounter( 21 );
            } else {
                $encounter = getFoe( $char_obj, 238 );
                $encounter[ 'flag_id_set' ] = 60;
                $encounter[ 'flag_bit_set' ] = 18;
                initiateCombat( $char_obj, $encounter, $zone );
            }
            break;
        case 11:
            if ( $f & ( 1 << 19 ) ) {
                $encounter = getChoiceEncounter( 19 );
            } else {
                $encounter = getFoe( $char_obj, 238 );
                $encounter[ 'flag_id_set' ] = 60;
                $encounter[ 'flag_bit_set' ] = 19;
                initiateCombat( $char_obj, $encounter, $zone );
            }
            break;
        case 12:
            if ( $f & ( 1 << 20 ) ) {
                $encounter = getChoiceEncounter( 23 );
            } else {
                $encounter = getFoe( $char_obj, 238 );
                $encounter[ 'flag_id_set' ] = 60;
                $encounter[ 'flag_bit_set' ] = 20;
                initiateCombat( $char_obj, $encounter, $zone );
            }
            break;
        case 13:
            if ( $f & ( 1 << 14 ) ) {
                $char_obj->disableFlagBit( 60, 13 );
                $char_obj->disableFlagBit( 60, 14 );
                $f = getFlagValue( $char_obj, sg_flag_great_labyrinth );
            }

            if ( $f & ( 1 << 13 ) ) {
                $encounter = getChoiceEncounter( 36 );
            } else {
                $encounter = getChoiceEncounter( 35 );
            }
            break;
        case 14:
            $encounter = getChoiceEncounter( 24 );
            break;
        case 15:
            if ( $f & ( 1 << 21 ) ) {
                $encounter = getChoiceEncounter( 22 );
            } else {
                $encounter = getFoe( $char_obj, 238 );
                $encounter[ 'flag_id_set' ] = 60;
                $encounter[ 'flag_bit_set' ] = 21;
                initiateCombat( $char_obj, $encounter, $zone );
            }
            break;
        case 16:
            $encounter = getChoiceEncounter( 26 );
            break;
        case 17:
            if ( $f & ( 1 << 22 ) ) {
                $encounter = getChoiceEncounter( 27 );
            } else {
                $encounter = getFoe( $char_obj, 238 );
                $encounter[ 'flag_id_set' ] = 60;
                $encounter[ 'flag_bit_set' ] = 22;
                initiateCombat( $char_obj, $encounter, $zone );
            }
            break;
        case 18:
            $encounter = getChoiceEncounter( 25 );
            break;
        case 19:
            if ( $f & ( 1 << 23 ) ) {
                $encounter = getChoiceEncounter( 28 );
            } else {
                $encounter = getFoe( $char_obj, 238 );
                $encounter[ 'flag_id_set' ] = 60;
                $encounter[ 'flag_bit_set' ] = 23;
                initiateCombat( $char_obj, $encounter, $zone );
            }
            break;
        case 20:
            $lever_count = 0;
            if ( $f & ( 1 << 7 ) ) { $lever_count += 1; }
            if ( $f & ( 1 << 9 ) ) { $lever_count += 1; }
            if ( $f & ( 1 << 11 ) ) { $lever_count += 1; }
            if ( $f & ( 1 << 13 ) ) { $lever_count += 1; }
            if ( $lever_count < 4 ) {
                $encounter = getChoiceEncounter( 37 );
            } else {
                if ( $f & ( 1 << 15 ) ) {
                    $encounter = getChoiceEncounter( 38 );
                } else {
                    $encounter = getFoe( $char_obj, 237 );
                    $encounter[ 'flag_id_set' ] = 60;
                    $encounter[ 'flag_bit_set' ] = 15;
                    initiateCombat( $char_obj, $encounter, $zone );
                }
            }
            break;
        }
        break;
}
?>