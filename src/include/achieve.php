<?

require_once 'include/core.php';

require_once sg_base_path . 'include/constants.php';
require_once sg_base_path . 'include/flag.php';


function isArrayEquipped( $c_obj, $a_obj ) {
    $e_obj = array(
        $c_obj->c[ 'armour_head' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_chest' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_legs' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_neck' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_trinket' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_trinket_2' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_trinket_3' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_hands' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_wrists' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_belt' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_boots' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_ring' ][ 'id' ] => TRUE,
        $c_obj->c[ 'armour_ring_2' ][ 'id' ] => TRUE,
        $c_obj->c[ 'mount' ][ 'id' ] => TRUE,
    );
    $ret_val = TRUE;
    foreach ( $a_obj as $x ) {
        if ( ! array_key_exists( $x, $e_obj ) ) { $ret_val = FALSE; }
    }
    return $ret_val;
}

function isFoeListCompleted( $f_id, $f_obj ) {
    if ( ! array_key_exists( $f_id, $f_obj ) ) {
        return FALSE;
    }
    foreach ( $f_obj as $k => $v ) {
        if ( $_SESSION[ 'tracking' ][ sg_track_foe ][ $k ] < $v ) {
            return FALSE;
        }
    }
    return TRUE;
}

function isUseListCompleted( $a_id, $a_obj ) {
    if ( ! array_key_exists( $a_id, $a_obj ) ) {
        return FALSE;
    }
    foreach ( $a_obj as $k => $v ) {
        if ( $_SESSION[ 'tracking' ][ sg_track_use ][ $k ] < $v ) {
            return FALSE;
        }
    }
    return TRUE;
}

function isLootListCompleted( $a_id, $a_obj ) {
    if ( ! array_key_exists( $a_id, $a_obj ) ) {
        return FALSE;
    }
    foreach ( $a_obj as $k => $v ) {
        if ( $_SESSION[ 'tracking' ][ sg_track_loot ][ $k ] < $v ) {
            return FALSE;
        }
    }
    return TRUE;
}

function awardAchievement( $c_obj, $a_id ) {
    $c_id = intval( $c_obj->c[ 'id' ] );
    $a_id = intval( $a_id );

    if ( array_key_exists( $a_id, $c_obj->c[ 'achievements' ] ) ) {
        return;
    }

    $time = time() + 10800;
    $query = "INSERT INTO char_achievements (char_id, achievement_id, timestamp)
        VALUES ($c_id, $a_id, $time)";
    sqlQuery( $query );
    $c_obj->c[ 'achievements' ][ $a_id ] = TRUE;
    $_SESSION[ 'achievements' ][ $a_id ] = TRUE;

    $query = "SELECT * FROM achievements WHERE id=$a_id";
    $results = sqlQuery( $query );
    $a_obj = $results->fetch_assoc();
    $a_obj[ 'title' ] = utf8_encode( $a_obj[ 'title' ] );
    $a_obj[ 'description' ] = utf8_encode( $a_obj[ 'description' ] );
    $st = '<center><table class="achievement"><tr><td width="36">' .
          '<img src="/images/achieve.gif" width="32" height="32"></td>' .
          '<td width="100%"><b>You have completed a new achievement!</b>' .
          '<br><br>' . $a_obj[ 'title' ] . '<br><i>' . $a_obj[ 'description' ] .
          '</i></td><td width="36"><img src="/images/achieve.gif" width="32" ' .
          'height="32"></td></tr></table></center>';
    return $st;
}

function getAchievements( $c_id ) {
    $c_id = intval( $c_id );
    $query = "SELECT a.*, c.timestamp
        FROM `char_achievements` AS c, `achievements` AS a
        WHERE c.char_id=$c_id AND c.achievement_id=a.id
              AND dev IN (0, " . sg_debug . ")
        ORDER BY timestamp DESC";
    $results = sqlQuery( $query );
    return getResourceAssocById(
        $results, $utf8_obj = array( 'title', 'description' ) );
}

function getAllAchievements() {
    $query = "SELECT * FROM `achievements` WHERE dev IN (0, " . sg_debug . ")
        ORDER BY title ASC";
    $results = sqlQuery( $query );
    return getResourceAssocById(
        $results, $utf8_obj = array( 'title', 'description' ) );
}

function checkAchievementEquip( $c_obj ) {
    $ret_obj = array();

    if ( sg_achievements_enabled != TRUE ) {
      return $ret_obj;
    }

    $equip_keys = array( 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 46 );
    foreach ( $equip_keys as $x ) {
        if ( ! array_key_exists( $x, $c_obj->c[ 'achievements' ] ) ) {
            switch ( $x ) {
            case 3:
                if ( isArrayEquipped( $c_obj, array( 63 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 4:
                if ( $c_obj->c[ 'mount' ][ 'id' ] == 471 ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 5:
                if ( $c_obj->c[ 'mount' ][ 'id' ] == 522 ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 6:
                if ( $c_obj->c[ 'pravokan_bonus' ] >= 6 ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 7:
                if ( isArrayEquipped( $c_obj, array( 514, 515, 516, 517, 518 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 8:
                if ( isArrayEquipped( $c_obj, array( 555, 556, 557, 558, 559, 560 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 9:
                if ( isArrayEquipped( $c_obj, array( 561, 562, 563, 564, 565, 566 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 10:
                if ( isArrayEquipped( $c_obj, array( 567, 568, 569, 570, 571, 572 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 11:
                if ( isArrayEquipped( $c_obj, array( 573, 574, 575, 576, 577, 578 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 12:
                if ( isArrayEquipped( $c_obj, array( 579, 580, 581, 582, 583, 584 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 13:
                if ( isArrayEquipped( $c_obj, array( 457, 458, 459, 460, 461 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 14:
                if ( isArrayEquipped( $c_obj, array( 443, 444, 445 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 15:
                if ( isArrayEquipped( $c_obj, array( 702 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 46:
                if ( $c_obj->c[ 'mount' ][ 'id' ] == 742 ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            }
        }
    }

    return $ret_obj;
}

function checkAchievementFoe( $c_obj, $f_id ) {
    $ret_obj = array();

    if ( sg_achievements_enabled != TRUE ) {
        return $ret_obj;
    }

    $equip_keys = array( 1, 2, 16, 17, 18, 19, 20, 25, 26, 43 );
    foreach ( $equip_keys as $x ) {
        if ( ! array_key_exists( $x, $c_obj->c[ 'achievements' ] ) ) {
            switch ( $x ) {
            case 1:
                if ( isFoeListCompleted( $f_id, array( 1 => 20 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 2:
                if ( isFoeListCompleted( $f_id, array( 85 => 25, 83 => 25, 86 => 25,
                        82 => 25, 84 => 25 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 16:
                if ( isFoeListCompleted( $f_id, array( 166 => 50 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 17:
                if ( isFoeListCompleted( $f_id, array( 15 => 50, 16 => 50, 17 => 50 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 18:
                if ( isFoeListCompleted( $f_id, array( 200 => 25, 201 => 25, 202 => 25 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 19:
                if ( isFoeListCompleted( $f_id, array( 203 => 25, 204 => 25, 205 => 25 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 20:
                if ( isFoeListCompleted( $f_id, array( 206 => 25, 207 => 25, 208 => 25 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 25:
                if ( ( $f_id == 179 ) &&
                     ( $c_obj->c[ 'current_hp' ] == $c_obj->c[ 'base_hp' ] ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 26:
                if ( ( $f_id == 179 ) &&
                     ( $c_obj->c[ 'current_hp' ] < round( 0.05 * $c_obj->c[ 'base_hp' ] ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 43:
                if ( isFoeListCompleted( $f_id, array( 235 => 250 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            }
        }
    }

    return $ret_obj;
}

function checkAchievementUse( $c_obj, $a_id ) {
    $ret_obj = array();

    if ( sg_achievements_enabled != TRUE ) {
        return $ret_obj;
    }

    $equip_keys = array( 32, 33, 34, 35, 36, 37, 38, 39, 40, 44, 49, 50, 51 );
    foreach ( $equip_keys as $x ) {
        if ( ! array_key_exists( $x, $c_obj->c[ 'achievements' ] ) ) {
            switch ( $x ) {
            case 32:
                if ( isUseListCompleted( $a_id, array( 408 => 250 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 33:
                if ( isUseListCompleted( $a_id, array( 424 => 250 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 34:
                if ( isUseListCompleted( $a_id, array( 41 => 100, 42 => 100, 43 => 100 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 35:
                if ( isUseListCompleted( $a_id, array( 352 => 100 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 36:
                if ( isUseListCompleted( $a_id, array( 744 => 100 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 37:
                if ( isUseListCompleted( $a_id, array( 66 => 100, 67 => 100 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 38:
                if ( isUseListCompleted( $a_id, array( 721 => 50, 722 => 50 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 39:
                if ( isUseListCompleted( $a_id, array( 735 => 50, 743 => 50 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 40:
                if ( isUseListCompleted( $a_id, array( 750 => 50, 751 => 50, 755 => 50 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 44:
                if ( isUseListCompleted( $a_id, array( 811 => 50, 812 => 50 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 49:
                if ( isUseListCompleted( $a_id, array( 56 => 100 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 50:
                if ( isUseListCompleted( $a_id, array( 165 => 100 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 51:
                if ( isUseListCompleted( $a_id, array( 707 => 100 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;

            }
        }
    }

    return $ret_obj;
}

function checkAchievementLoot( $c_obj, $a_id ) {
    $ret_obj = array();

    if ( sg_achievements_enabled != TRUE ) {
        return $ret_obj;
    }

    $equip_keys = array( 52, 53, 54, 55 );
    foreach ( $equip_keys as $x ) {
        if ( ! array_key_exists( $x, $c_obj->c[ 'achievements' ] ) ) {
            switch ( $x ) {
            case 52:
                if ( isLootListCompleted( $a_id, array( 131 => 50 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 53:
                if ( isLootListCompleted( $a_id, array( 713 => 25 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 54:
                if ( isLootListCompleted( $a_id, array( 519 => 1, 520 => 1, 521 => 1 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            case 55:
                if ( isLootListCompleted( $a_id, array( 409 => 1 ) ) ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            }
        }
    }

    return $ret_obj;
}

function checkAchievementInventory($c_obj) {
    $ret_obj = array();

    if ( sg_achievements_enabled != TRUE ) {
        return $ret_obj;
    }

    $equip_keys = array( 47 );
    foreach ( $equip_keys as $x ) {
        if ( ! array_key_exists( $x, $c_obj->c[ 'achievements' ] ) ) {
            switch ( $x ) {
            case 47:
                if ( getCharArtifactQuantity( $c_obj, 174 ) > 0 ) {
                    $ret_obj[] = awardAchievement( $c_obj, $x );
                }
                break;
            }
        }
    }

    return $ret_obj;
}



?>