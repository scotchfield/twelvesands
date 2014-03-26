<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/foes.php';
require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/validate.php'; 

$t = getGetStr( 't', '0' );
$q = getGetStr( 'q', '0' );
$handIn = getGetStr( 'c', '0' );

$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$zone = FALSE;

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

// Need to verify that a player has completed the prerequisites.
function canAccessQuest( $c_obj, $quest ) {
    if ( $quest[ 'quest_required' ] > 0 ) {
        if ( ! isset( $quest[ 'quest_required' ], $c_obj->c[ 'quests' ] ) ) {
            return FALSE;
        }
        if ( $c_obj->c[ 'quests' ][ $quest[ 'quest_required' ] ][ 'status' ] != 1 ) {
            return FALSE;
        }
    }
    if ( $quest[ 'artifact_required' ] > 0 ) {
        if ( ! hasArtifact( $c_obj, $quest[ 'artifact_required' ] ) ) {
            return FALSE;
        }
    }
    return TRUE;
}

$output_obj = array();

if ( '0' == $t ) {
    $output_obj[] = '<p>You don\'t see that person.</p>';
} else {
    $npc = getNpc( $char_obj, $t );
    $zone = getZone( $npc[ 'zone' ] );

    $output_obj[] = '<p class="zone_title">' . $npc[ 'name' ] . '</p>';

    if ( $q == 0 ) {
        $output_obj[] = '<p class="zone_description">' .
            $npc[ 'description' ] . '</p>';

        if ( sizeof( $npc[ 'quests' ] ) == 0 ) {
            $output_obj[] = '<p>You realise that you have nothing more to say, ' .
                'and walk away.</p>';
        } else {
            foreach ( $npc[ 'quests' ] as $x ) {
                if ( canAccessQuest( $char_obj, $x ) ) {
                    $output_obj[] = '<p>Quest available: <a href="talk.php?t=' . $t .
                        '&q=' . $x[ 'id' ] . '">' . $x[ 'name' ] . '</a> (<i>Level ' .
                        $x[ 'min_level' ] . '</i>)</p>';
                }
            }
        }
    } else {
        $quest = $npc[ 'quests' ][ $q ];

        $output_obj[] = '<p><span class="section_header">' . $quest[ 'name' ] .
            '</span></p>';

        $qa1 = getArtifactQuantity( $char_obj, $quest[ 'quest_artifact1' ] );
        $qa2 = getArtifactQuantity( $char_obj, $quest[ 'quest_artifact2' ] );
        $qa3 = getArtifactQuantity( $char_obj, $quest[ 'quest_artifact3' ] );
        $c1 = $char_obj->c[ 'quests' ][ $quest[ 'id' ] ][ 'foe_count_1' ];
        $c2 = $char_obj->c[ 'quests' ][ $quest[ 'id' ] ][ 'foe_count_2' ];
        $c3 = $char_obj->c[ 'quests' ][ $quest[ 'id' ] ][ 'foe_count_3' ];

        if ( FALSE == $c1 ) { $c1 = 0; }
        if ( FALSE == $c2 ) { $c2 = 0; }
        if ( FALSE == $c3 ) { $c3 = 0; }

        $awardGift = FALSE;
        if ( ! array_key_exists( $q, $char_obj->c[ 'quests' ] ) ) {
            $awardGift = TRUE;
        }

        if ( ( $qa1 >= $quest[ 'quest_quantity1' ] ) &&
             ( $qa2 >= $quest[ 'quest_quantity2' ] ) &&
             ( $qa3 >= $quest[ 'quest_quantity3' ] ) &&
             ( $c1 >= $quest[ 'quest_foe_quantity1' ] ) &&
             ( $c2 >= $quest[ 'quest_foe_quantity2' ] ) &&
             ( $c3 >= $quest[ 'quest_foe_quantity3' ] ) ) {

            if ( $handIn == 1 ) {
                $output_obj[] = '<p class="zone_description">' .
                     $quest[ 'completed_text' ] . '</p>';

                if ( $quest[ 'quest_artifact1' ] > 0 ) {
                    removeArtifact( $char_obj, $quest[ 'quest_artifact1' ],
                                        $quest[ 'quest_quantity1' ] );
                }
                if ( $quest[ 'quest_artifact2'] > 0 ) {
                    removeArtifact( $char_obj, $quest[ 'quest_artifact2' ],
                                        $quest[ 'quest_quantity2' ] );
                }
                if ( $quest[ 'quest_artifact3' ] > 0 ) {
                    removeArtifact( $char_obj, $quest[ 'quest_artifact3' ],
                                        $quest[ 'quest_quantity3' ] );
                }
                if ( $quest[ 'reward_quantity' ] > 0 ) {
                    $a = getArtifact( $quest[ 'reward_artifact' ] );
                    $output_obj[] = awardArtifactString(
                        $char_obj, $a, $quest[ 'reward_quantity' ] );
                }
                if ( $quest[ 'reward_xp' ] > 0 ) {
                    $char_obj->addXp( $quest[ 'reward_xp' ] );
                    $level_check = levelCheck( $char_obj );

                    $output_obj[] = '<p>You gain ' . $quest[ 'reward_xp' ] . ' XP.</p>';

                    if ( $level_check != FALSE ) {
                        $output_obj[] = $level_check;
                    }
                }
                if ( $quest[ 'reward_rep_amount' ] > 0 ) {
                    $rep = $char_obj->c[ 'reputations' ][ $quest[ 'reward_rep_id' ] ][ 'value' ];
                    if ( $rep < $quest[ 'reward_rep_max' ] ) {
                        $rep_reward = $quest[ 'reward_rep_amount' ] +
                            floor( ( $char_obj->c[ 'rep_bonus' ] / 100.0 ) *
                                  $quest[ 'reward_rep_amount' ] );
                        if ( ( $rep + $rep_reward ) > $quest[ 'reward_rep_max' ] ) {
                            $rep_reward = $quest[ 'reward_rep_max' ] - $rep;
                        }
                        $output_obj[] = awardReputationString( $char_obj,
                            $quest[ 'reward_rep_id' ], $rep_reward );
                    }
                }

                addQuestCompleted( $char_obj->c, $quest[ 'id' ] );

            } else {

                addQuestSeen( $char_obj->c, $q );
                $output_obj[] = '<p class="zone_description">' . $quest[ 'text' ] .
                    '</p>';

                if ( NULL != $quest ) {
                    $output_obj[] = '<p>You have all the required components ' .
                        'to complete this quest!' .
                        '<br><a href="talk.php?t=' . $t . '&q=' . $q .
                        '&c=1">Turn in the components to ' . $npc[ 'name' ] . '</a></p>';
                    $quest[ 'submission_ready' ] = 1;
                }
            }

        } else {

            addQuestSeen( $char_obj->c, $q );
            $output_obj[] = '<p class="zone_description">' . $quest[ 'text' ] . '</p>';

        }

        if ( $handIn == 0 ) {

            $output_obj[] = '<p><i>Level ' . $quest[ 'min_level' ] .
                ' required</i></p>';

            if ( $quest[ 'quest_artifact1' ] > 0 ) {

                $output_obj[] = '<p><span class="section_header">Required Items:' .
                     '</span></p><ul class="char_list">';

                $a = getArtifact( $quest[ 'quest_artifact1' ] );
                $output_obj[] = '<li>' . $quest[ 'quest_quantity1' ] . 'x ';
                $output_obj[] = renderArtifactStr( $a, $quest[ 'quest_quantity1' ] );
                $output_obj[] = ' (owned: ' . $qa1 . ')</li>';

                if ( $quest[ 'quest_artifact2' ] > 0 ) {
                    $a = getArtifact( $quest[ 'quest_artifact2' ] );
                    $output_obj[] = '<li>' . $quest[ 'quest_quantity2' ] . 'x ';
                    $output_obj[] = renderArtifactStr( $a, $quest[ 'quest_quantity2' ] );
                    $output_obj[] = ' (owned: ' . $qa2 . ')</li>';
                }
                if ( $quest[ 'quest_artifact3' ] > 0 ) {
                    $a = getArtifact( $quest[ 'quest_artifact3' ] );
                    $output_obj[] = '<li>' . $quest[ 'quest_quantity3' ] . 'x ';
                    $output_obj[] = renderArtifactStr( $a, $quest[ 'quest_quantity3' ] );
                    $output_obj[] = ' (owned: ' . $qa3 . ')</li>';
                }

                $output_obj[] = '</ul>';

            }

            if ( $quest[ 'quest_foe1' ] > 0 ) {

                $output_obj[] = '<p><span class="section_header">Required ' .
                    'Foes Slain:</span></p><ul class="char_list">';

                $f = getFoe( $char_obj, $quest[ 'quest_foe1' ] );
                $output_obj[] = '<li>' . $quest[ 'quest_foe_quantity1' ] . 'x ' .
                    $f[ 'name' ] . ' (slain: ' . $c1 . ')</li>';

                if ( $quest[ 'quest_foe2' ] > 0 ) {
                    $f = getFoe( $char_obj, $quest[ 'quest_foe2' ] );
                    $output_obj[] = '<li>' . $quest[ 'quest_foe_quantity2' ] . 'x ' .
                        $f[ 'name' ] . ' (slain: ' . $c2 . ')</li>';
                }

                if ( $quest[ 'quest_foe3' ] > 0 ) {
                    $f = getFoe( $char_obj, $quest[ 'quest_foe3' ] );
                    $output_obj[] = '<li>' . $quest[ 'quest_foe_quantity3' ] . 'x ' .
                        $f[ 'name' ] . ' (slain: ' . $c3 . ')</li>';
                }

            }

            $output_obj[] = '<p><span class="section_header">Rewards:</span></p>' .
                '<ul class="char_list">';
            if ( $quest[ 'reward_artifact' ] == 0 && $quest[ 'reward_quantity' ] > 0 ) {
                $output_obj[] = '<li>' . $quest[ 'reward_quantity' ] . ' gold</li>';
            } elseif ( $quest[ 'reward_artifact' ] > 0 ) {
                $a = getArtifact( $quest[ 'reward_artifact' ] );
                $output_obj[] = '<li>' . $quest[ 'reward_quantity' ] . 'x ';
                $output_obj[] = renderArtifactStr( $a, $quest[ 'reward_quantity' ] );
                $output_obj[] = '</li>';
            }
            if ( $quest[ 'reward_xp' ] > 0 ) {
                $output_obj[] = '<li>' . $quest[ 'reward_xp' ] . ' XP</li>';
            }
            if ( $quest[ 'reward_rep_amount' ] > 0 ) {
                $output_obj[] = '<li><br>' . floor( $quest[ 'reward_rep_amount' ] / 1000 ) .
                    ' reputation with ' .
                    getReputationName( $quest[ 'reward_rep_id' ] );
                if ( $quest[ 'reward_rep_max' ] > 0 ) {
                    $score_obj = getReputationScore( $quest[ 'reward_rep_max' ] );
                    $output_obj[] = '<br><font size="-2">maximum: ' .
                        $score_obj[ 'n' ] . '<center>' .
                        renderBarStr( $score_obj[ 'v' ], $score_obj[ 'm' ],
                                      'good', 'neutral', 75 ) . '</font>';
                }
                $output_obj[] = '</li>';
            }
            $output_obj[] = '</ul>';

            if ( $quest[ 'repeatable' ] == 1 ) {
                $output_obj[] = '<p><i>This quest is repeatable.</i></p>';
            }

            if ( ! array_key_exists( 'submission_ready', $quest ) ) {
                if ( ( $awardGift == TRUE ) && ( $quest[ 'gift_artifact' ] > 0 ) ) {
                    if ( getArtifactQuantity( $char_obj, $quest[ 'gift_artifact' ] ) == 0 ) {
                        $gift_artifact = getArtifact( $quest[ 'gift_artifact' ] );
                        $output_obj[] = awardArtifactString(
                            $char_obj, $gift_artifact, $quest[ 'gift_quantity' ] );
                    }
                }
            }

        }

        $output_obj[] = '<p><a href="talk.php?t=' . $npc[ 'id' ] . '">Back to ' .
            $npc[ 'name' ] . '</a></p>';

    }
}


require '_header.php';

foreach ( $output_obj as $output_str ) {
    echo $output_str;
}

echo '<p><a href="main.php?z=' . $zone[ 'id' ] . '">Go back to ' .
     $zone[ 'name' ] . '</a></p>';

require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>