<?

require_once 'include/core.php';
require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/guilds.php';
require_once sg_base_path . 'include/log.php';

$log_obj = new Logger();
$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$action = getGetStr( 'a', '' );
$output_obj = array();

if ( 'c' == $action ) {

    $a_str = '';

    if ( $char_obj->c[ 'guild_id' ] != 0 ) {
        $a_str =  '<p>You\'re already in a guild!  If you want to create a new ' .
            'guild, you will need to leave your existing one first.</p>';
    } elseif ( $char_obj->c[ 'gold' ] < 25000 ) {
        $a_str = '<p>You don\'t have that much gold available!  The charters ' .
            'and the legalese come at a steep price, you know..</p>';
    } else {
        $action_confirm = getGetInt( 'c', 0 );
        if ( 1 == $action_confirm ) {
            $guild_name = esc( getGetStr( 'n', '' ) );
            if ( ( '' != $guild_name ) &&
                 ( strlen( $guild_name ) > 5 ) &&
                 ( strlen( $guild_name ) < 60 ) ) {

                $guild_exists = getGuildByName( $guild_name );
                if ( FALSE == $guild_exists ) {
                    addGuild( $guild_name, $char_obj );
                    $char_obj->setGold( $char_obj->c[ 'gold' ] - 25000 );
                    $guild = getGuildByName( $guild_name );
                    $char_obj->setGuildId( $guild[ 'id' ] );
                    $char_obj->setGuildName( $guild[ 'name' ] );
                    $char_obj->setGuildRank( 1 );
                    $a_str = '<p>Congratulations!  We\'ll get this paperwork through ' .
                        'the system as fast as we can.  You\'re now a guild leader!  ' .
                        'We hope you enjoy it!</p>';
                } else {
                    $a_str = '<p>A guild with that name already exists!  You\'re ' .
                        'going to have to find something else to call yourselves.</p>';
                }

            } else {
                $a_str = '<p>That guild name is not acceptable!  It must be greater ' .
                   'than 5 characters, and less than 60.</p>';
            }
        } else {
            $a_str = '<p>Are you sure you want to create a guild for 25,000 gold? ' .
                 '<br>If so, choose a guild name below, and we can get you ' .
                 'registered post-haste!</p>' .
                 '<p><form method="get" action="guild.php">' .
                 '<input type="hidden" name="a" value="c">' .
                 '<input type="hidden" name="c" value="1">' .
                 '<input type="text" name="n" size="40"><br>' .
                 '<input type="submit" value="Register Guild!">' .
                 '</form></p>';
        }
    }

} elseif ( 'ap' == $action ) {

    if ( $char_obj->c[ 'guild_id' ] != 0 ) {
        $output_obj[] = '<p>You are already guilded!</p>';
    } else {
        $apply_guild = getGetInt( 'y', 0 );
        $guild_id = getGetInt( 'i', 0 );
        $guild = FALSE;
        if ( $guild_id > 0 ) {
            $guild = getGuildById( $guild_id );
        }
        if ( FALSE != $guild ) {
            if ( $apply_guild == 1 ) {
                $char_obj->setGuildId( $guild[ 'id' ] );
                $char_obj->setGuildName( $guild[ 'name' ] );
                $char_obj->setGuildRank( 6 );
                addGuildMember( $char_obj, $guild[ 'id' ], 6 );

                sendMail( $guild[ 'leader_id' ], 0, 'Guild Management',
                    'Guild Applicant', $char_obj->c[ 'name' ] .
                    ' has applied to your guild.',
                    0, 0, 0, time() );

                $output_obj[] = '<p>Alright, we\'ve submitted your application.  ' .
                    'You\'ll need to wait to hear from the guild before you\'re ' .
                    'a full member though.  Good luck!</p>';
            } else {
                $output_obj[] = '<p>Are you sure you want to apply to <b>' .
                    $guild[ 'name' ] . '</b>?<br>' .
                    '<a href="guild.php?a=ap&i=' . $guild_id . '&y=1">Submit ' .
                    'an application</a></p>';
            }
        }
    }

} elseif ( 'lg' == $action ) {

    if ( $char_obj->c[ 'guild_id' ] == 0 ) {
        $output_obj[] = '<p>You aren\'t a member of any guild right now.</p>';
    } else {
        $leave_guild = getGetInt( 'y', 0 );
        if ( 1 == $leave_guild ) {
            removeGuildMember( $char_obj, $char_obj->c[ 'guild_id' ] );
            $output_obj[] = '<p>You resign from the guild!</p>';
            if ( $char_obj->c[ 'guild_rank' ] == 1 ) {
                updateGuildLeader( $char_obj->c[ 'guild_id' ], 0, '' );
                $output_obj[] = '<p>Your guild has been left without a leader!</p>';
            }
            $char_obj->setGuildId( 0 );
            $char_obj->setGuildName( '' );
            $char_obj->setGuildRank( 0 );
        } else {
            $output_obj[] = '<p>Are you sure you want to leave your guild?<br>' .
                '<a href="guild.php?a=lg&y=1">Leave my guild!</a></p>';
        }
    }

} elseif ( 'p' == $action ) {

    $guild = FALSE;
    if ( $char_obj->c[ 'guild_id' ] > 0 ) {
        if ( $char_obj->c[ 'guild_rank' ] == 1 ) {
            $guild = getGuildById( $char_obj->c[ 'guild_id' ] );
        }
    }

    if ( FALSE != $guild ) {
        $i_val = getGetInt( 'i', 0 );
        $r_val = getGetInt( 'r', 0 );

        $c_obj = new Char( $i_val );
        if ( ( $char_obj->c[ 'guild_id' ] > 0 ) && ( $char_obj->c[ 'guild_rank' ] == 1 ) &&
             ( $c_obj->c[ 'guild_id' ] == $char_obj->c[ 'guild_id' ] ) ) {
            if ( ( $r_val > 1 ) && ( $r_val < 6 ) ) {
                if ( $c_obj->c[ 'guild_rank' ] > 1 ) {
                    updateGuildMember( $c_obj, $char_obj->c[ 'guild_id' ], $r_val );
                    $c_obj->setGuildRank( $r_val );
                    $c_obj->save();
                    $output_obj[] = '<p class="tip">Modified rank for ' .
                        $c_obj->c[ 'name' ] . '.</p>';
                }
            } elseif ( $r_val == 0 ) {
                if ( count( $_POST ) > 0 ) {
                    removeGuildMember( $c_obj, $c_obj->c[ 'guild_id' ] );
                    $c_obj->setGuildId( 0 );
                    $c_obj->setGuildName( '' );
                    $c_obj->setGuildRank( 0 );
                    $c_obj->save();
                    $output_obj[] = '<p class="tip">' . $c_obj->c[ 'name' ] . ' has ' .
                        'been removed from the guild.</p>';
                } else {
                    $output_obj[] = '<p class="tip">Are you sure you want to remove ' .
                        $c_obj->c[ 'name' ] . ' from the guild?</p>' .
                        '<p><form method="post" action="guild.php?a=p&r=0&i=' .
                        $i_val . '">' . '<input type="submit" name="remove" ' .
                        'value="Remove from guild"></form></p>';

                }
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

$guild_id = getGetInt( 'i', $char_obj->c[ 'guild_id' ] );

echo '<p style="font-size: 90%">' .
     '(<a href="guild.php?a=l">View all guilds</a>) ';
if ( $char_obj->c[ 'guild_id' ] > 0 ) {
    echo '(<a href="guild.php?i=' . $char_obj->c[ 'guild_id' ] .
         '">View your guild</a>) ';
} else {
    echo '(<a href="guild.php?a=c">Create a guild</a>) ';
}
if ( ( $char_obj->c[ 'guild_rank' ] == 1 ) &&
     ( $guild_id == $char_obj->c[ 'guild_id' ] ) ) {
    echo '<br>' .
         '(<a href="guild.php?a=u">Update messages</a>) ' .
         '(<a href="guild.php?a=p">Promote/demote members</a>) ' .
         '(<a href="guild.php?a=r">Rename ranks</a>) ';
}
echo '</p>';

if ( 'l' == $action ) {

    $guilds = getGuilds();
    echo '<h2>Guild List:</h2>';
    echo '<p>';
    foreach ( $guilds as $g ) {
        echo '<a href="guild.php?i=' . $g[ 'id' ] . '">' . $g[ 'name' ] . '</a> ';
        if ( $g[ 'leader_id' ] > 0 ) {
            echo '(led by <a href="char.php?i=' . $g[ 'leader_id' ] . '">' .
                 $g[ 'leader_name' ] . '</a>)';
            if ( $char_obj->c[ 'guild_id' ] == 0 ) {
              echo ' <font size="-2">(<a href="guild.php?a=ap&i=' . $g[ 'id' ] .
                   '">apply</a>)</font>';
            }
        } else {
            echo '(no leader!)';
        }
        if ( $g[ 'motto' ] != '' ) {
            echo '<br><span style="font-size: 75%">' . $g[ 'motto' ] . '</span><br>';
        }
        echo '<br>';
    }
    echo '</p>';

    echo '<p><a href="guild.php">Back to Guild Management</a></p>';

} elseif ( 'c' == $action ) {

    echo $a_str;

    echo '<p><a href="guild.php">Back to Guild Management</a></p>';

} elseif ( 'v' == $action ) {

    $guild_id = getGetInt( 'i', 0 );
    if ( $guild_id < 1 ) {
        echo '<p>That guild doesn\'t exist!</p>';
    } else {
        $guild = getGuildById( $guild_id );
        if ( FALSE == $guild ) {
            echo '<p>That guild doesn\'t exist!</p>';
        } else {
            echo '<h3>Members of ' . $guild[ 'name' ] . '</h3>';

            $guild_members = getGuildMembers( $guild_id );
            echo '<p>';
            foreach ( $guild_members as $guild_member ) {
                if ( $guild_member[ 'rank' ] <= 5 ) {
                    echo '<a href="char.php?i=' . $guild_member[ 'char_id' ] .
                         '">' . $guild_member[ 'char_name' ] . '</a>, ';
                    switch ( $guild_member[ 'rank' ] ) {
                    case 1: echo $guild[ 'rank_1' ]; break;
                    case 2: echo $guild[ 'rank_2' ]; break;
                    case 3: echo $guild[ 'rank_3' ]; break;
                    case 4: echo $guild[ 'rank_4' ]; break;
                    case 5: echo $guild[ 'rank_5' ]; break;
                    default: echo $guild[ 'rank_5' ]; break;
                    }
                    echo '<br>';
                }
            }
            echo '</p>';
        }
    }

    echo '<p><a href="guild.php">Back to Guild Management</a></p>';

} elseif ( 'p' == $action ) {

    foreach ( $output_obj as $output_str ) {
        echo $output_str;
    }

    if ( FALSE != $guild ) {
        echo '<h3>Promotions and Demotions: ' . $guild[ 'name' ] . '</h3>';

        $guild_members = getGuildMembers( $char_obj->c[ 'guild_id' ] );
        $applicant_seen = FALSE;
        echo '<p><b>Full members:</b></p><p>';
        foreach ( $guild_members as $guild_member ) {
            if ( ( FALSE == $applicant_seen ) && ( 6 == $guild_member[ 'rank' ] ) ) {
                $applicant_seen = TRUE;
                echo '</p><hr width="300"><p><b>Applicants:</b></p><p>';
            }
            echo '<a href="char.php?i=' . $guild_member[ 'char_id' ] .
                 '">' . $guild_member[ 'char_name' ] . '</a>, ';
            switch ( $guild_member[ 'rank' ] ) {
            case 1: echo $guild[ 'rank_1' ]; break;
            case 2: echo $guild[ 'rank_2' ]; break;
            case 3: echo $guild[ 'rank_3' ]; break;
            case 4: echo $guild[ 'rank_4' ]; break;
            case 5: echo $guild[ 'rank_5' ]; break;
            case 6: echo 'Applicant'; break;
            default: echo 'Applicant'; break;
            }
            if ( $guild_member[ 'rank' ] > 2 ) {
                echo ' <font size="-2">(<a href="guild.php?a=p&i=' .
                     $guild_member[ 'char_id' ] . '&r=' .
                     ( $guild_member[ 'rank' ] - 1 ) . '">promote</a>)</font>';
            }
            if ( ( $guild_member[ 'rank' ] > 1 ) && ( $guild_member[ 'rank' ] < 5 ) ) {
                echo ' <font size="-2">(<a href="guild.php?a=p&i=' .
                     $guild_member[ 'char_id' ] . '&r=' .
                     ( $guild_member[ 'rank' ] + 1 ) . '">demote</a>)</font>';
            }
            if ( $guild_member[ 'rank' ] > 1 ) {
                echo ' <font size="-2">(<a href="guild.php?a=p&r=0&i=' .
                       $guild_member[ 'char_id' ] . '">remove</a>)</font>';
            }
            echo '<br>';
        }
        echo '</p>';

    } else {
        echo '<p>You can\'t do that.</p>';
    }

    echo '<p><a href="guild.php">Back to Guild Management</a></p>';

} elseif ( 'r' == $action ) {

    $guild = FALSE;
    if ( $char_obj->c[ 'guild_id' ] > 0 ) {
        if ( $char_obj->c[ 'guild_rank' ] == 1 ) {
            $guild = getGuildById( $char_obj->c[ 'guild_id' ] );
        }
    }

    if ( FALSE != $guild ) {
        echo '<p class="tip">Guild titles must be between 3 and 32 characters ' .
             'long.</p>';

        echo '<p><form action="action.php" method="get">';
        echo '<center><table class="leaderboard">';
        echo '<tr><th width="50">Rank</th><th>Title</th></tr>';

        echo '<tr><td>1</td><td><input type="text" size="32" name="r1" value="' .
             $guild[ 'rank_1' ] . '"></td></tr>';
        echo '<tr><td>2</td><td><input type="text" size="32" name="r2" value="' .
             $guild[ 'rank_2' ] . '"></td></tr>';
        echo '<tr><td>3</td><td><input type="text" size="32" name="r3" value="' .
             $guild[ 'rank_3' ] . '"></td></tr>';
        echo '<tr><td>4</td><td><input type="text" size="32" name="r4" value="' .
             $guild[ 'rank_4' ] . '"></td></tr>';
        echo '<tr><td>5</td><td><input type="text" size="32" name="r5" value="' .
             $guild[ 'rank_5' ] . '"></td></tr>';

        echo '</table></center>';
        echo '<input type="submit" value="Submit Changes" />';
        echo '<input type="hidden" name="a" value="gr" />';
        echo '</form></p>';
    } else {
        echo '<p>You are not currently a member of any guild!</p>';
    }

    echo '<p><a href="guild.php">Back to Guild Management</a></p>';

} elseif ( 'cg' == $action ) {

    if ( ( $char_obj->c[ 'guild_id' ] > 0 ) && ( $char_obj->c[ 'guild_rank' ] == 6 ) ) {
        removeGuildMember( $char_obj, $char_obj->c[ 'guild_id' ] );
        $char_obj->setGuildId( 0 );
        $char_obj->setGuildName( '' );
        $char_obj->setGuildRank( 0 );
        echo '<p>Application cancelled!</p>';
    } else {
        echo '<p>You don\'t have any applications at the moment.</p>';
    }

    echo '<p><a href="guild.php">Back to Guild Management</a></p>';

} elseif ( ( 'ap' == $action ) || ( 'lg' == $action ) ) {

    foreach ( $output_obj as $output_str ) {
        echo $output_str;
    }

    echo '<p><a href="guild.php">Back to Guild Management</a></p>';

} elseif ( 'u' == $action ) {

    $guild = FALSE;
    if ( $char_obj->c[ 'guild_id' ] > 0 ) {
        if ( $char_obj->c[ 'guild_rank' ] == 1 ) {
            $guild = getGuildById( $char_obj->c[ 'guild_id' ] );
        }
    }

    if ( FALSE != $guild ) {
        echo '<p class="tip">You can leave a message for your guildmates and ' .
             'visitors here.</p>';

        echo '<form action="action.php?a=gm" method="post">' .
             '<p><b>Motto:</b><br><input type="text" size="50" ' .
             'name="motto" value="' . $guild[ 'motto' ] . '"></p>' .
             '<p><b>Website:</b><br><input type="text" size="50" ' .
             'name="url" value="' . $guild[ 'url' ] . '"></p>' .
             '<p><b>Message:</b><br><textarea name="message" cols="50">' .
             $guild[ 'message' ] . '</textarea></p>' .
             '<p><input type="submit" value="Submit Changes"></p>' .
             '</form>';
    } else {
        echo '<p>You are not currently a member of any guild!</p>';
    }

    echo '<p><a href="guild.php">Back to Guild Management</a></p>';

} else {

    $guild_id = getGetInt( 'i', $char_obj->c[ 'guild_id' ] );
    $guild = getGuildById( $guild_id );

    if ( FALSE != $guild ) {

        echo '<table border="0" cellpadding="0" class="plain"><tr>';

        echo '<td width="120">' .
             '<img src="/images/guild-left.gif" width="120" height="120">' .
             '</td>';

        echo '<td width="400" align="center">' .
             '<p><span style="font-size: 150%">' .
             $guild[ 'name' ] . '</span><br>' .
             '<b>Leader:</b> <a href="char.php?i=' . $guild[ 'leader_id' ] .
             '">' . $guild[ 'leader_name' ] . '</a>';
        if ( $guild[ 'motto' ] != '' ) {
            echo '<br><b>Motto:</b> ' . $guild[ 'motto' ];
        }
        if ( $guild[ 'url' ] != '' ) {
            echo '<br><b>Website:</b> <a href="' . $guild[ 'url' ] . '">' .
                 $guild[ 'url' ] . '</a>';
        }
        if ( $guild[ 'id' ] == $char_obj->c[ 'guild_id' ] ) {
            echo '</p><p><i>';
            if ( $char_obj->c[ 'guild_rank' ] == 1 ) {
                echo 'You are the leader of this guild.';
            } elseif ( $char_obj->c[ 'guild_rank' ] == 6 ) {
                echo 'You have applied to this guild.<br>' .
                     '<a href="guild.php?a=cg">Cancel guild application</a>';
            } else {
                echo 'You are a member of this guild.<br>' .
                     '<span style="font-size: 75%"><a href="guild.php?a=lg">' .
                     'Leave the guild</a></span>';
            }
            echo '</i>';
        }
        echo '</p></td>';

        echo '<td width="120">' .
             '<img src="/images/guild-right.gif" width="120" height="120">' .
             '</td>';

        echo '</tr></table>';

        if ( $guild[ 'message' ] != '' ) {
            echo '<p><b>Guild Message:</b> ' . $guild[ 'message' ] . '</p>';
        }

        echo '<p style="font-size: 133%"><b>Member List:</b></p>';
        $last_rank = 0;
        $guild_members = getGuildMembers( $guild_id );
        foreach ( $guild_members as $m ) {
            if ( $m[ 'rank' ] <= 5 ) {
                if ( $m[ 'rank' ] != $last_rank ) {
                    echo '<p><b>' . $guild[ 'rank_' . $m[ 'rank' ] ] . '</b><br>';
                    $last_rank = $m[ 'rank' ];
                }
                echo '<a href="char.php?i=' . $m[ 'char_id' ] . '">' . $m[ 'char_name' ] .
                     '</a>, Level ' . $m[ 'level' ];
                if ( $m[ 'd_id' ] > 0 ) {
                    echo ' (Dungeon Run: ';
                    switch ( $m[ 'd_id' ] ) {
                        case 1: echo '&Aacute;lmok Crypts Stables'; break;
                    }
                    echo ')';
                }
                echo '<br>';
            }
        }
        echo '</p>';

    } else {

        echo '<p><b>You are currently unguilded!</b></p>' .
             '<p>You can view the list of established guilds and consider ' .
             'applying to one, or perhaps even start your own!</p>';

    }

}


require '_footer.php';
$save = $char_obj->save();
$log_save = $log_obj->save();

?>

</div>
</body>
</html>
