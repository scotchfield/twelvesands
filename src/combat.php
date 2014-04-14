<?

require_once 'include/core.php';
$debug_time_start = debugTime();

require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/formatting.php';

require_once sg_base_path . 'state/combat.php';

$log_obj = new Logger();

$state_params = array();
$state_params[ 'a' ] = getGetStr( 'a', '0' );
$state_params[ 'i' ] = getGetStr( 'i', '0' );
$state_params[ 't' ] = getGetStr( 't', '0' );
$state_params[ 'x' ] = getGetInt( 'x', 0 );

$combat_obj = getCombat( $state_params, $log_obj );

if ( array_key_exists( 'header', $combat_obj ) ) {
    header( $combat_obj[ 'header' ] );
    exit;
}

if ( array_key_exists( 'char_obj', $combat_obj ) ) {
    $char_obj = $combat_obj[ 'char_obj' ];
    $c = $char_obj->c;
}

$combat_bar_array = getAllCombatBarOptions( $char_obj );

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss( $c ); ?>
</head>
<body>

<? renderPopupText(); ?>
<script type="text/javascript" src="include/ts_keypress.js"></script>

<div class="container">

<?

require '_header.php';

echo '<p>';
if ( count( $combat_obj[ 'attack_list' ] ) == 0 ) {
    echo '<a href="#" onclick="document.location=document.getElementById(' .
         '\'bar_default\').href;">' .
         '<img src="images/buff-green.gif" width="24" height="24" ' .
         'border="0" onmouseover="popup(\'<b>(`) Adventure again' .
         '</b>\')" onmouseout="popout()"></a>&nbsp;';
} else {
    echo '<img src="images/buff-empty.gif" width="24" height="24">&nbsp;';
}
$combat_empty = TRUE;
for ( $x = 0; $x < 10; $x++ ) {

    $v = $combat_bar_valid_bases[ 0 ] + $x;
    $v_flag = getFlagValue( $char_obj, $v );
    $attack = $combat_bar_array[ $v_flag ];

  //if ( ( count( $combat_obj[ 'attack_list' ] ) > 0 ) && ( $attack[ 'a' ] > 0 ) ) {
    if ( ( ( ( $combat_obj[ 'state' ] & ts_combat_char_ongoing ) > 0 ) &&
             ( $attack[ 'a' ] > 0 ) && ( $v_flag != 2 ) ) ||
         ( ( ( $combat_obj[ 'state' ] & ts_combat_char_ongoing ) == 0 ) &&
             ( $v_flag == 2 ) ) ) {
      $combat_empty = FALSE;
      $action_st = '&x=' . $char_obj->c[ 'action_id' ];
      if ( $v_flag == 2 ) {
          $action_st = '';
      }
      echo '<a id="bar_' . ( $x + 1 ) . '" href="' . $attack[ 'u' ] .
           $action_st . '" ' .
           'onclick="detectKeypress(\'' . ( $x + 1 ) . '\');">' .
           '<img src="images/' . $attack[ 'i' ] . '" width="24" height="24" ' .
           'border="0" onmouseover="popup(\'<b>(' . ( ( $x + 1 ) % 10 ) . ') ' . $attack[ 'n' ] .
           '</b>\')" onmouseout="popout()"></a>';
    } else {
        echo '<img src="images/buff-empty.gif" width="24" height="24">';
    }
}
if ( ( count( $combat_obj[ 'attack_list' ] ) > 0 ) && ( $combat_empty == TRUE ) ) {
    echo '<br><font size="-2">(<a href="account.php?a=cb">What is ' .
         'this?</a>)</font>';
}
echo '</p>';

if ( isset( $combat_obj[ 'foe' ][ 'generated_name' ] ) ) {
    echo '<p class="zone_title">' . $combat_obj[ 'foe' ][ 'generated_name' ];
} else {
    echo '<p class="zone_title">' . $combat_obj[ 'foe' ][ 'name' ];
}

if ( ( $combat_obj[ 'state' ] & ts_combat_begin ) == 0 ) {
    echo '<br><font size="-2">Combat Round #' .
           getFlagValue( $char_obj, sg_flag_combat_round ) . '</font>';
}
echo '</p>';

if ( ( $combat_obj[ 'state' ] & ts_combat_active ) > 0 ) {

    if ( ( $combat_obj[ 'state' ] & ts_combat_begin ) > 0 ) {
        $cry_count = count( $char_obj->c[ 'battle_cries' ] );
        if ( $cry_count > 0 ) {
            echo '<p><i>You cry out as the combat begins!</i><br><b>' .
                $char_obj->c[ 'battle_cries' ][ rand( 0, $cry_count - 1 ) ] . '</b></p>';;
        }

        echo '<p class="zone_description">' . $combat_obj[ 'foe' ][ 'text' ] . '</p>';
    }

    if ( ( $combat_obj[ 'state' ] & ts_combat_initiative_failed ) > 0 ) {
        echo '<p><font color="red">You are ambushed!</font></p>';
    }

    if ( ( $combat_obj[ 'state' ] & ts_combat_char_defeated ) > 0 ) {

        if ( count( $combat_obj[ 'state_effect_list' ] ) > 0 ) {
            foreach ( $combat_obj[ 'state_effect_list' ] as $x ) {
                echo $x;
            }
        }

        if ( count( $combat_obj[ 'char_attack' ] ) > 0 ) {
            echo '<p>' . $combat_obj[ 'char_attack' ][ 'text' ] . '</p>';
            if ( array_key_exists( 'hit_text', $combat_obj[ 'char_attack' ] ) ) {
                echo '<p>' . $combat_obj[ 'char_attack' ][ 'hit_text' ];
                if ( strlen( $combat_obj[ 'char_attack' ][ 'block_text' ] > 0 ) ) {
                    echo '<br><small><span class="greyed">(' .
                        $combat_obj[ 'char_attack' ][ 'block_text' ] .
                        ')</span></small></p>';
                } else {
                    echo '<br><small><span class="greyed">&nbsp;</span></small></p>';
                }
            }
        }

        if ( $combat_obj[ 'foe' ][ 'hp' ] > 30 ) {
            // do nothing.
        } elseif ( $combat_obj[ 'foe' ][ 'hp' ] > 10 ) {
            echo '<p><span class="alert">Your enemy looks fatigued!</span></p>';
        } else {
            echo '<p><span class="alert">Your enemy is almost dead!</span></p>';
        }

        if ( count( $combat_obj[ 'foe_attack' ] ) > 0 ) {
            echo '<p>' . $combat_obj[ 'foe_attack' ][ 'attack_text' ] . '</p>';
            echo '<p>' . $combat_obj[ 'foe_attack' ][ 'hit_text' ];
            if ( strlen( $combat_obj[ 'foe_attack' ][ 'block_text' ] > 0 ) ) {
                echo '<br><small><span class="greyed">(' .
                    $combat_obj[ 'foe_attack' ][ 'block_text' ] . ')</span></small></p>';
            } else {
                echo '<br><small><span class="greyed">&nbsp;</span></small></p>';
            }
            echo $combat_obj[ 'foe_attack' ][ 'buff_text' ];
        }

        // todo: refactor above

        echo '<p>You pass out from the pain!</p>';
        echo '<p><font color="red">You feel extremely fatigued!</font></p>';
        echo '<p><a href="main.php">Go back to Capital City</a></p>';

      } elseif ( ( $combat_obj[ 'state' ] & ts_combat_char_ran ) > 0 ) {

        foreach ( $combat_obj[ 'status_list' ] as $status ) {
            echo '<p>' . $status . '</p>';
        }

        $last_zone = getZone(getFlagValue($char_obj, sg_flag_last_combat_zone));
        echo '<p><a href="' . getCombatLink($last_zone['id']) .
             '">Adventure again</a></p>';
        echo '<p><a href="main.php?z=' . $last_zone['parent_id'] .
             '">Go back to ' . $last_zone['parent_name'] . '</a></p>';

    } else {

        // render current encounter state buffs/debuffs

        $state_bits = array();
        $img_good = '/images/buff-green.gif';
        $img_bad = '/images/buff-red.gif';

        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_chararmour_500 ) ) {
            $state_bits[] = getStateIconStr( 'Defensive Guile', $img_good );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_double_gold_drop ) ) {
            $state_bits[] = getStateIconStr( 'Gold Rush', $img_good );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_statreduce_2_points ) ) {
            $state_bits[] = getStateIconStr( 'Inspire Fear', $img_bad );
        }
        if ( ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_stun_0 ) ) ||
             ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_stun_1 ) ) ||
             ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_stun_2 ) ) ||
             ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_stun_3 ) ) ) {
            $state_bits[] = getStateIconStr( 'Stunned!', $img_bad );
        }
        if ( ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_bleed_0 ) ) ||
             ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_bleed_1 ) ) ||
             ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_bleed_2 ) ) ||
             ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_bleed_3 ) ) ) {
            $state_bits[] = getStateIconStr( 'Bleeding!', $img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_expose_1 ) ) {
            $state_bits[] = getStateIconStr( 'Expose Weakness (1)', $img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_expose_2 ) ) {
            $state_bits[] = getStateIconStr( 'Expose Weakness (2)', $img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_expose_3 ) ) {
            $state_bits[] = getStateIconStr( 'Expose Weakness (3)', $img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_expose_4 ) ) {
            $state_bits[] = getStateIconStr( 'Expose Weakness (4)', $img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_expose_5 ) ) {
            $state_bits[] = getStateIconStr( 'Expose Weakness (5)', $img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_shatter_1 ) ) {
            $state_bits[] = getStateIconStr( 'Shattered Armour (1)', $img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_shatter_2 ) ) {
            $state_bits[] = getStateIconStr( 'Shattered Armour (2)', $img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es1, sg_es1_shatter_3 ) ) {
            $state_bits[] = getStateIconStr( 'Shattered Armour (3)', $img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es2, sg_es2_enraged_1 ) ) {
            $enraged = bitCount( getFlagValue( $char_obj, sg_flag_es2 ) & 1023 );
            $state_bits[] = getStateIconStr( 'Enraged Shout (' . $enraged . ')',$img_bad );
        }
        if ( getFlagBit( $char_obj, sg_flag_es2, sg_es2_healthsiphon_1 ) ) {
            $siphon = bitCount( getFlagValue( $char_obj, sg_flag_es2 ) & 31744 );
            $state_bits[] = getStateIconStr( 'Health Siphon (' . $siphon . ')', $img_bad );
        }

        foreach ( $state_bits as $x ) {
            echo $x;
        }

        if ( count( $combat_obj[ 'state_effect_list' ] ) > 0 ) {
            foreach ( $combat_obj[ 'state_effect_list' ] as $x ) {
                echo $x;
            }
        }

        if ( count( $combat_obj[ 'char_attack' ] ) > 0 ) {
            echo '<p>' . $combat_obj[ 'char_attack' ][ 'text' ] . '</p>';
            if ( array_key_exists( 'hit_text', $combat_obj[ 'char_attack' ] ) ) {
                echo '<p>' . $combat_obj[ 'char_attack' ][ 'hit_text' ];
                if ( strlen( $combat_obj[ 'char_attack' ][ 'block_text' ] > 0 ) ) {
                    echo '<br><small><span class="greyed">(' .
                        $combat_obj[ 'char_attack' ][ 'block_text' ] .
                        ')</span></small></p>';
                } else {
                    echo '<br><small><span class="greyed">&nbsp;</span></small></p>';
                }
            }
        }

        if ( $combat_obj[ 'foe' ][ 'hp' ] > 30 ) {
            // do nothing.
        } elseif ( $combat_obj[ 'foe' ][ 'hp' ] > 10 ) {
            echo '<p><span class="alert">Your enemy looks fatigued!</span></p>';
        } elseif ( $combat_obj[ 'foe' ][ 'hp' ] > 0 ) {
            echo '<p><span class="alert">Your enemy is almost dead!</span></p>';
        }

        if ( count( $combat_obj[ 'foe_attack' ] ) > 0 ) {
            echo '<p>' . $combat_obj[ 'foe_attack' ][ 'attack_text' ] . '</p>';
            echo '<p>' . $combat_obj[ 'foe_attack' ][ 'hit_text' ];
            if ( strlen( $combat_obj[ 'foe_attack' ][ 'block_text' ] > 0 ) ) {
                echo '<br><small><span class="greyed">(' .
                    $combat_obj[ 'foe_attack' ][ 'block_text' ] . ')</span></small></p>';
            } else {
                echo '<br><small><span class="greyed">&nbsp;</span></small></p>';
            }
            echo $combat_obj[ 'foe_attack' ][ 'buff_text' ];
        }

        if ( ( $combat_obj[ 'state' ] & ts_combat_char_victory ) == 0 ) {

/*
            echo '<table width="100%" border="0" class="plain"><tr><td width="200">';
            $avatar_file = 'base_f.jpg';
            if ( $char_obj->c[ 'avatar' ] != '' ) {
                $avatar_file = $char_obj->c[ 'avatar' ];
            }
            echo '<img src="/images/avatar/' . $avatar_file .
                 '" width="200" height="200">';
            echo '</td><td>';
*/

            echo '<p><b>What do you want to do?</b></p>';

            $attacks = join( '<br>', $combat_obj[ 'attack_list' ] );
            echo '<p>' . $attacks . '</p>';

            if ( count( $combat_obj[ 'spell_list' ] ) > 0 ) {
                $spells = join( '<br>', $combat_obj[ 'spell_list' ] );
                echo '<p>' . $spells . '</p>';
            }

            if ( count( $combat_obj[ 'artifact_list' ] ) > 0 ) {
                $last_use = getFlagValue( $char_obj, sg_flag_last_combat_artifact );
                echo '<form method="get" action="combat.php">';
                echo '<input type="hidden" name="x" value="' .
                     $char_obj->c[ 'action_id' ] . '">';
                echo '<input type="hidden" name="a" value="u">';
                echo '<select name="i">';
                foreach ( $combat_obj[ 'artifact_list' ] as $a_key => $a_name ) {
                    echo '<option value="' . $a_key . '"';
                    if ( $a_key == $last_use ) {
                        echo ' selected';
                    }
                    echo '>' . $a_name . ' (' .
                         getArtifactQuantity($char_obj, $a_key) . ')</option>';
                }
                echo '</select>';
                echo '<input type="submit" value="Use Item" />';
                echo '</form>';
            }

            echo '<p><a href="combat.php?a=p">Pass your turn</a></p>';

            echo '<p><a href="combat.php?a=r">Run away!</a></p>';

/*
            echo '</td><td width="200">';
            //$avatar_file = 'base_f_r.jpg';
            //if ( FALSE ) {
            //  $avatar_file = $char_obj->c[ 'avatar' ];
            //}
            //echo '<img src="/images/avatar/' . $avatar_file .
            //     '" width="200" height="200">';
            echo '&nbsp;';
            echo '</td></tr></table>';*/

        } else {

            echo '<p>You win!</p>';

            foreach ( $combat_obj[ 'status_list' ] as $status ) {
                echo '<p>' . $status . '</p>';
            }

            foreach ( $combat_obj[ 'reward_list' ] as $reward ) {
                echo '<p>' . $reward . '</p>';
            }

            $last_zone = getZone( getFlagValue( $char_obj, sg_flag_last_combat_zone ) );
            echo '<p><a href="' . getCombatLink( $last_zone[ 'id' ] ) .
                 '">Adventure again</a></p>';
            echo '<p><a href="main.php?z=' . $last_zone[ 'parent_id' ] .
                 '">Go back to ' . $last_zone[ 'parent_name' ] . '</a></p>';

        }

    }

}

require '_footer.php';

$log_save = $log_obj->save();

$combat_obj[ 'char_obj' ] = '';
debugPrint( '<font size="-2">' );
debugPrint( $combat_obj );
debugPrint( '</font> ');

$debug_time_diff = debugTime() - $debug_time_start;
debugPrint( '<font size="-2">Page rendered in ' .
    number_format( $debug_time_diff, 2, ".", "." ) . 's</font>' );

?>

</div>
</body>
</html>