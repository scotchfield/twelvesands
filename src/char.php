<?

require_once 'include/core.php';
$debug_time_start = debugTime();

require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/plots.php';
require_once sg_base_path . 'include/puzzles.php';
require_once sg_base_path . 'include/user.php';

require_once sg_base_path . 'state/char.php';

$log_obj = new Logger();

$state_params = array();
$state_params[ 'a' ] = getGetStr( 'a', '0' );
$state_params[ 'i' ] = getGetInt( 'i', 0 );
$state_params[ 'm' ] = getGetInt( 'm', 0 );
$state_params[ 'n' ] = getGetInt( 'n', 1 );
$state_params[ 's' ] = getGetInt( 's', 0 );
$state_params[ 't' ] = getGetInt( 't', 0 );
$state_params[ 'act' ] = getGetInt( 'act', 0 );

$char_state_obj = getCharState( $state_params, $log_obj );

$a = $state_params[ 'a' ];
$i = $state_params[ 'i' ];
$m = $state_params[ 'm' ];
$n = $state_params[ 'n' ];
$s = $state_params[ 's' ];
$t = $state_params[ 't' ];
$act = $state_params[ 'act' ];

if ( array_key_exists( 'header', $char_state_obj ) ) {
    header( $char_state_obj[ 'header' ] );
    exit;
}

if ( array_key_exists( 'char_obj', $char_state_obj ) ) {
    $char_obj = $char_state_obj[ 'char_obj' ];
    $c = $char_obj->c;
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

  forceCombatCheck( $char_obj );

  require '_header.php';

if ( '0' == $a ) {

    if ( isset( $_GET[ 'i' ] ) ) {
        $i = getGetInt( 'i', 0 );
        $view_obj = new Char( $i );
    } else {
        $i = FALSE;
        $view_obj = $char_obj;
    }
    $char = $view_obj->c;

    if ( FALSE != $char ) {

        if ( FALSE == $i ) {
            include '_charmenu.php';
        } else {
            $titled_name = $char[ 'titled_name' ];
            include '_profilemenu.php';
        }

        if ( getFlagValue( $char_obj, sg_flag_unequip ) > 0 ) {
            $artifact = getArtifact( getFlagValue( $char_obj, sg_flag_unequip ),
                                     getFlagValue( $char_obj, sg_flag_unequip_enc ) );
            echo '<p class="tip">You unequip your ' . renderArtifactStr( $artifact );
            $m_st = '';
            if ( getFlagValue( $char_obj, sg_flag_unequip_enc ) > 0 ) {
                $m_st = '&m=' . getFlagValue( $char_obj, sg_flag_unequip_enc );
            }
            if ( $artifact[ 'type' ] == 1 ) {
                echo ' <font size="-2">(<a href="char.php?a=a&i=' . $artifact[ 'id' ] .
                     $m_st . '">equip</a>)</font></li>';
            } elseif ( in_array( $artifact[ 'type' ], $armourArray ) ) {
                echo ' <font size="-2">(<a href="char.php?a=aa&i=' . $artifact[ 'id' ] .
                     $m_st . '&t=' . $artifact[ 'type' ] . '">equip</a>)</font></li>';
            }
            echo '</p>';
            $char_obj->addFlag( sg_flag_unequip, 0 );
            $char_obj->addFlag( sg_flag_unequip_enc, 0 );
        }

        if ( getFlagValue( $char_obj, sg_flag_equip ) > 0 ) {
            $artifact = getArtifact( getFlagValue( $char_obj, sg_flag_equip ),
                                     getFlagValue( $char_obj, sg_flag_equip_enc ) );
            echo '<p class="tip">You equip your ' . renderArtifactStr( $artifact ) .
                 '.</p>';
            $char_obj->addFlag( sg_flag_equip, 0 );
            $char_obj->addFlag( sg_flag_equip_enc, 0 );

            $achieve_obj = checkAchievementEquip( $char_obj );
            foreach ( $achieve_obj as $x ) {
                echo $x;
            }
        }

        if ( FALSE == $i ) {
            if ( count( $_POST ) > 0 ) {
                $status = getPostStr( 'status', '' );
                if ( strlen( $status ) > 64 ) {
                    $status = substr( $status, 0, 64 );
                }
                updateCharStatus( $char_obj, $status );
                echo '<p class="tip">Status updated!<br>' . $status . '</p>';
            }
            $status = '<i>empty</i>';
            if ( strlen( $_SESSION[ 'char_status' ] ) > 0 ) {
                $status = $_SESSION[ 'char_status' ];
            }
            echo '<div id="status"><p>Your status: <b>' . $status . '</b> ' .
                 '<font size="-2">(<a href="#" ' .
                 'onclick="document.getElementById(\'status_form\').className=\'\'; ' .
                 'document.getElementById(\'status\').className=\'invis\';">' .
                 'change</a>)</font></p></div>';

            echo '<div id="status_form" class="invis"><p>' .
                 '<form action="char.php" method="POST">New status: ' .
                 '<input type="text" name="status" value="' . $_SESSION[ 'char_status' ] .
                 '"> <input type="submit" value="Change Status"></form></p></div>';
        } else {
            if ( strlen( $char[ 'char_status' ] ) > 0 ) {
                echo '<p>Character status: <b>' . $char[ 'char_status' ] . '</b></p>';
            }
        }
?>

<table width="100%"><tr><td width="50%" valign="top">

<div class="table_stat">

  <p><span class="section_header">Attributes</span></p>

<?
        if ( $char[ 'd_id' ] > 0 ) {
            echo '<p><b>Dungeon Run:</b><br>';
            switch ( $char[ 'd_id' ] ) {
                case 1: echo '&Aacute;lmok Crypts Stables'; break;
            }
            echo '</p>';
        }
?>

  <ul class="char_list">
  <li><b>Level</b>: <?= $char[ 'level' ] ?></li>
<?      if ( $char[ 'str_bonus' ] != 0 ) { ?>
  <li><b>Strength</b>: <span class="mod_highlight">
      <?= $char[ 'str' ] + $char[ 'str_bonus' ]
      ?></span> (<?= $char[ 'str' ] ?> + <?= $char[ 'str_bonus' ] ?>)</li>
<?      } else { ?>
  <li><b>Strength</b>: <?= $char['str'] ?></li>
<?      } ?>
<?      if ( $char[ 'dex_bonus' ] != 0 ) { ?>
  <li><b>Dexterity</b>: <span class="mod_highlight">
      <?= $char[ 'dex' ] + $char[ 'dex_bonus' ]
      ?></span> (<?= $char[ 'dex' ] ?> + <?= $char[ 'dex_bonus' ] ?>)</li>
<?      } else { ?>
  <li><b>Dexterity</b>: <?= $char[ 'dex' ] ?></li>
<?      } ?>
<?      if ($char[ 'int_bonus' ] != 0) { ?>
  <li><b>Intelligence</b>: <span class="mod_highlight">
      <?= $char[ 'int' ] + $char[ 'int_bonus' ]
      ?></span> (<?= $char[ 'int' ] ?> + <?= $char[ 'int_bonus' ] ?>)</li>
<?      } else { ?>
  <li><b>Intelligence</b>: <?= $char[ 'int' ] ?></li>
<?      } ?>
<?      if ($char[ 'cha_bonus' ] != 0) { ?>
  <li><b>Charisma</b>: <span class="mod_highlight">
      <?= $char[ 'cha' ] + $char[ 'cha_bonus' ]
      ?></span> (<?= $char[ 'cha' ] ?> + <?= $char[ 'cha_bonus' ] ?>)</li>
<?      } else { ?>
  <li><b>Charisma</b>: <?= $char[ 'cha' ] ?></li>
<?      } ?>
<?      if ($char[ 'con_bonus' ] != 0) { ?>
  <li><b>Constitution</b>: <span class="mod_highlight">
      <?= $char[ 'con' ] + $char[ 'con_bonus' ]
      ?></span> (<?= $char[ 'con' ] ?> + <?= $char[ 'con_bonus' ] ?>)</li>
<?      } else { ?>
  <li><b>Constitution</b>: <?= $char[ 'con' ] ?></li>
<?      } ?>
  <li><b>Health</b>: <?= $char[ 'current_hp' ] ?> / <?= $char[ 'base_hp' ] ?></li>
  <li><b>Mana Points</b>: <?= $char['mana'] ?></li>
  <li><b onmouseover="popup('<b>Fatigue</b> is accumulated by engaging in combats, fishing or mining, and cooking or crafting.  Once you reach one hundred percent, you\'ll need to either eat some food, or wait until the next day.')" onmouseout="popout()">Fatigue</b>: <? renderFatigue( $char[ 'fatigue' ] ); ?> <?
        if ( $char[ 'fatigue_rested' ] > 0 ) {
            echo ' <font color="#ADD8E6">(rested)</font>';
        } ?></li>
  <li><b>XP</b>: <?= getDisplayXp( $char ) ?></li>
  <li><b>Gold</b>: <?= $char[ 'gold' ] ?></li>
  <li><b>Armour</b>: <?= $char[ 'armour' ] ?></li>
<?
        if ( $char[ 'max_fatigue_reduction' ] > 0 ) {
            $fullness = min( 100,
                100 * $char[ 'fatigue_reduction' ] / $char[ 'max_fatigue_reduction' ] );
        } else {
            $fullness = 100;
        }
        $fullness = floor( $fullness );
/*  $hunger_str = '';
  if ($fullness <= 0.5) {
    $hunger_str = 'Not full';
  } elseif ($fullness <= 0.7) {
    $hunger_str = 'Starting to feel it..';
  } elseif ($fullness <= 0.9) {
    $hunger_str = 'Still some room..';
  } elseif ($fullness < 1.0) {
    $hunger_str = 'Almost full..';
  } else {
    $hunger_str = 'Fully sated!';
  }*/
?>
  <li><b onmouseover="popup('<b>Fullness</b> measures the amount of room you have left for eating.  You can find basic foods from Barnabus Bidwell in the Trade District, or you can learn how to cook new recipes by clicking the <i>Cook Something</i> link above.')" onmouseout="popout()">Fullness</b>: <?= $fullness ?>%</li>
  <li><b>Cooking</b>: <?= getTrueProfessionSkill( $char[ 'prof_cooking' ] ) ?></li>
  <li><b>Mining</b>: <?= getTrueProfessionSkill( $char[ 'prof_mining' ] ) ?></li>
  <li><b>Fishing</b>: <?= getTrueProfessionSkill( $char[ 'prof_fishing' ] ) ?></li>
  <li><b>Crafting</b>: <?= getTrueProfessionSkill( $char[ 'prof_crafting' ] ) ?></li>
  <li><b>Sandstorm Solves</b>: <?=$char[ 'sandstorm_wisdom_solves' ] ?><? if ( FALSE == $i ) { ?> <font size="-2">(<a href="char.php?a=p1">view</a>)</font><? } ?></li>
  <li><b>Next Capital Trainer</b>: Tier <?
        $trainer_tier = 1;
        if ( array_key_exists( sg_flag_scalingfoe, $char[ 'flags' ] ) ) {
            $trainer_tier = $char[ 'flags' ][ sg_flag_scalingfoe ];
        }
        echo $trainer_tier;
  ?></li>
  <li><b>Highest Melee Damage</b>: <?= getFlagValue( $view_obj, sg_flag_top_melee_damage ) ?></li>
  <li><b>Highest Rune Spell Damage</b>: <?= getFlagValue( $view_obj, sg_flag_top_rune_damage ) ?></li>
<?      if ( $char[ 'duel_elo' ] > 0 ) { ?>
  <li><b>Duel Ranking</b>: <?= $char[ 'duel_elo' ] ?></li>
<?      } ?>
  <li><b>Dungeon Runs</b>: <?= $char[ 'dungeon_run_count' ] ?> <font size="-2">(<a href="char.php?a=dr&amp;i=<?= $char[ 'id' ] ?>">view</a>)</font></li>

  </ul>

  <p><span class="section_header">Inscribed Runes</span></p>
<?
        if ( count( $char[ 'runes' ] ) > 0 ) {
            echo '<ul class="char_list">';
            foreach ( $char[ 'runes' ] as $rune ) {
                $rune_st = '&nbsp;<font size="-2">(<a href="char.php?a=rmr&i=' .
                    $rune[ 'id' ] . '">x</a>)</font>';
                if ( FALSE != $i ) {
                    $rune_st = '';
                }
                echo '<li>' . renderArtifactStr( $rune ) . $rune_st . '</li>';
            }
            echo '</ul>';
        } else {
            if ( $i == FALSE ) {
                echo '<p><b>None</b><br><font size="-2">(<a href="main.php?z=133">' .
                     'visit Runes and Relics</a>)</font></p>';
            } else {
                echo '<p><b>None</b></p>';
            }
        }
?>

  <p><span class="section_header">Character Buffs</span></p>

<?
        echo '<ul class="char_list">';
        $now = time();
        $buff_count = 0;
        foreach ( $char[ 'buffs' ] as $buff ) {
            if ( $buff[ 'invisible' ] == 0 ) {
                echo '<li>' . utf8_encode( $buff[ 'name' ] ) . ' (' .
                     $buff[ 'description' ] . ') ' .
                     '<font color="blue">' .
                     renderTimeRemaining( $now, $buff[ 'expires' ] ) .
                     '</font></li>';
                $buff_count += 1;
            }
        }
        echo '</ul>';
        if ( $buff_count == 0 ) {
            echo '<p><b>None</b></p>';
        }
?>

</div></td><td valign="top"><div class="table_stat">

  <p><span class="section_header">Equipped Weapon</span><?
        if ( FALSE == $i ) { ?><br>
  <font size="-2">(<a href="inventory.php">equip something else</a>)</font><?
        } ?></p>
  <p><? renderArtifact( $char[ 'weapon' ] ); ?>
  <?    if ( $char[ 'weapon' ][ 'id' ] != 0 ) {
            if ( $i == FALSE ) {
  echo '<font size="-2">(<a href="char.php?a=a&i=0">x</a>)</font>';
            }
        } ?>
  </p>

  <p><span class="section_header">Equipped Armour</span><?
        if ( FALSE == $i ) { ?><br>
  <font size="-2">(<a href="inventory.php">equip something else</a>)</font><?
        } ?></p>

  <ul class="char_list">
  <li>Head: <? renderArtifact( $char[ 'armour_head' ] );
        if ( $char[ 'armour_head' ][ 'id' ] != 0 ) {
            if ( $i == FALSE ) {
                echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                     sg_artifact_armour_head . '&i=0">x</a>)</font>';
            }
        } ?>
  </li>
  <li>Neck: <? renderArtifact( $char[ 'armour_neck' ] );
        if ( $char[ 'armour_neck' ][ 'id' ] != 0 ) {
            if ( $i == FALSE ) {
                echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                    sg_artifact_armour_neck . '&i=0">x</a>)</font>';
            }
        } ?>
  </li>
  <li>Chest: <? renderArtifact( $char[ 'armour_chest' ] );
       if ( $char[ 'armour_chest' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                    sg_artifact_armour_chest . '&i=0">x</a>)</font>';
           }
        } ?>
  </li>
  <li>Hands: <? renderArtifact( $char[ 'armour_hands' ] );
       if ( $char[ 'armour_hands' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                    sg_artifact_armour_hands . '&i=0">x</a>)</font>';
           }
       } ?>
  </li>
  <li>Wrists: <? renderArtifact( $char[ 'armour_wrists' ] ); 
       if ( $char[ 'armour_wrists' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                    sg_artifact_armour_wrists . '&i=0">x</a>)</font>';
           }
       } ?>
  </li>
  <li>Belt: <? renderArtifact( $char[ 'armour_belt' ] );
       if ( $char[ 'armour_belt' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                    sg_artifact_armour_belt . '&i=0">x</a>)</font>';
           }
       } ?>
  </li>
  <li>Pants: <? renderArtifact( $char[ 'armour_legs' ] );
       if ( $char[ 'armour_legs' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                     sg_artifact_armour_legs . '&i=0">x</a>)</font>';
           }
       } ?>
  </li>
  <li>Boots: <? renderArtifact( $char[ 'armour_boots' ] );
       if ( $char[ 'armour_boots' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                    sg_artifact_armour_boots . '&i=0">x</a>)</font>';
           }
       } ?>
  </li>
  <li>Ring: <? renderArtifact( $char[ 'armour_ring' ] );
       if ( $char[ 'armour_ring' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                     sg_artifact_armour_ring . '&i=0&s=1">x</a>)</font>';
           }
       } ?>
  </li>
  <li>Ring: <? renderArtifact( $char[ 'armour_ring_2' ] );
       if ( $char[ 'armour_ring_2' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                     sg_artifact_armour_ring . '&i=0&s=2">x</a>)</font>';
           }
       } ?>
  </li>
  <li>Trinket: <? renderArtifact( $char[ 'armour_trinket' ] );
       if ( $char[ 'armour_trinket' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                    sg_artifact_armour_trinket . '&i=0&s=1">x</a>)</font>';
           }
       } ?>
  </li>
  <li>Trinket: <? renderArtifact( $char[ 'armour_trinket_2' ] );
       if ( $char[ 'armour_trinket_2' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                    sg_artifact_armour_trinket . '&i=0&s=2">x</a>)</font>';
           }
       } ?>
  </li>
  <li>Trinket: <? renderArtifact( $char[ 'armour_trinket_3' ] );
       if ( $char[ 'armour_trinket_3' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
               echo '&nbsp;<font size="-2">(<a href="char.php?a=aa&t=' .
                    sg_artifact_armour_trinket . '&i=0&s=3">x</a>)</font>';
           }
       } ?>
  </li>
  </ul>

  <p><span class="section_header">Equipped Mount</span><?
       if ( FALSE == $i ) { ?><br>
  <font size="-2">(<a href="inventory.php">equip something else</a>)</font><?
       } ?></p>
  <p><? renderArtifact($char['mount']); ?>
  <?   if ( $char[ 'mount' ][ 'id' ] != 0 ) {
           if ( $i == FALSE ) {
  echo '<font size="-2">(<a href="char.php?a=am&i=0">x</a>)</font>';
           }
       } ?>
  </p>

<?
       if ( sg_allies_enabled ) {
           echo '<p><span class="section_header">Current Ally</span>';
           if ( FALSE == $i ) {
               echo '<br><font size="-2">(<a href="char.php?a=al">' .
                    'bring someone else</a>)</font>';
           }
           echo '</p><p>';
           if ( $char[ 'ally_id' ] == 0 ) {
               if ( FALSE == $i ) {
        echo '<b>None</b><br><font size="-2">(<a href="main.php?z=127">' .
           'visit Allied Contracting</a>)</font>';
      } else {
        echo '<b>None</b>';
      }
    } else {
      echo '<b onmouseover="popup(\'' . $char['ally']['description'] .
           '\');" ' . 'onmouseout="popout()">' . $char['ally']['name'] .
           '</b><br>' . $char['ally']['title'] . '<br>Fatigue: ' .
           floor($char['ally_fatigue'] / 1000) . '%';
    }
    echo '</p>';
  }
?>

  <p><span class="section_header">Guild</span></p>

  <?
    if (($char['guild_id'] == 0) || ($char['guild_rank'] > 5)) {
      if (FALSE == $i) {
        echo '<p><b>None</b><br><font size="-2">(<a href="guild.php">' .
           'view guilds</a>)</font></p>';
      } else {
        echo '<p><b>None</b></p>';
      }
    } else {
      echo '<p><a href="guild.php?i=' . $char['guild_id'] . '">' .
           $char['guild_name'] . '</a></p>';
    }
  ?>

  <p><span class="section_header">Reputation</span></p>

  <?
    foreach($char['reputations'] as $rep) {
      $show_stores = ($i == FALSE);
      echo renderReputation($char_obj, $rep, $show_stores) . '<br>';
    }
    if (count($char['reputations']) == 0) {
      echo '<p><b>None</b></p>';
    }
  ?>



<? /*SKILLS
  <p><span class="section_header">Character Skills</span><br>
  <font size="-2">(mouseover for description)</font></p>

< ?
  if (count($char['skills']) > 0) {
    echo '<ul class="char_list">';
    foreach($char['skills'] as $skill) {
      echo '<li><span onmouseover="popup(\'<b>' . $skill['name'] . '</b><br>' .
           $skill['description'] . '\')" onmouseout="popout()">' .
           $skill['name'] . '</span></li>';
    }
    echo '</ul>';
  }
  */
?>

</div></td></tr></table>

<?

    if (FALSE != $i) {
      echo '<p><a href="main.php?z=13">Back to the Hall of Records</a></p>';
    }

  }

} elseif (('a' == $a) || ('aa' == $a) || ('am' == $a)) {

  include '_charmenu.php';

  foreach ($char_state_obj['out'] as $st) {
    echo $st;
  }

  echo '<p><a href="char.php">Go back to your character page</a></p>';

} elseif ('t' == $a) {

  include '_charmenu.php';

?>

  <p><span class="section_header">Titles available to you:</span></p>

  <ul class="char_list">
  <?
    $title_found = false;
    echo '<li><a href="char.php?a=tc&i=0">' . $char_obj->c['name'] .
         '</a> (default)</li>';
    foreach($char_obj->c['skills'] as $skill) {
      if ($skill['title_granted'] != '_') {
        echo '<li><a href="char.php?a=tc&i=' . $skill['id'] . '">' .
             str_replace('_', $char_obj->c['name'], $skill['title_granted']) .
             '</a> (from ' . $skill['name'] . ')</li>';
        $title_found = true;
      }
    }
    if (!$title_found) {
      echo '<li>You don\'t have any skills that would confer a title!</li>';
    }
  ?>
  </ul>

<?

} elseif ('av' == $a) {

  include '_charmenu.php';

  $avatars = getAvatars($char_obj->c['id']);

  $avatar_id = getGetInt('i', 0);
  if (($avatar_id > 0) && (isset($avatars[$avatar_id]))) {
    $char_obj->setAvatar($avatars[$avatar_id]['filename']);
    echo '<p class="tip">Avatar changed!</p>';
  }

  echo '<p><span class="section_header">Avatars available to you:</span></p>';
  echo '<center><table class="plain" width="500">';

  foreach ($avatars as $avatar) {
    echo '<tr><td align="center" width="50%"><img src="/images/avatar/' .
         $avatar['filename'] . '" width="200" height="200"></td>' .
         '<td align="center">' . $avatar['name'] .
         '<br><font size="-2">(<a href="char.php?a=av&i=' .
         $avatar['id'] . '">use this</a>)</font></td></tr>';
  }
  echo '</table></center>';

} elseif (('tc' == $a) || ('rmr' == $a)) {

  include '_charmenu.php';

  foreach ($char_state_obj['out'] as $st) {
    echo $st;
  }

} elseif (('admin' == $a) && ($char_obj->c['user_id'] == 1)) {

  include '_charmenu.php';

  echo '<p><b>Admin Panel</b></p>';

  foreach ($char_state_obj['out'] as $st) {
    echo $st;
  }

?>

  <p><a href="char.php?a=admin&s=1">Increase level by one</a></p>

  <p><form method="get" action="char.php">Give artifacts<br>
  <input type="hidden" name="a" value="admin">
  <input type="hidden" name="s" value="2">
  Artifact ID: <input type="text" name="i"><br>
  Quantity: <input type="text" name="n"><br>
  <input type="submit" value="Give me the stuff"></form></p>

  <p><form method="get" action="char.php">Give some gold<br>
  <input type="hidden" name="a" value="admin">
  <input type="hidden" name="s" value="3">
  Quantity: <input type="text" name="n"><br>
  <input type="submit" value="Give me the gold"></form></p>

  <p><a href="char.php?a=admin&s=4&n=0">Delete all profession points</a><br>
  <a href="char.php?a=admin&s=4&n=300">Set profession points higher</a></p>

  <p><form method="get" action="char.php">Give a buff<br>
  <input type="hidden" name="a" value="admin">
  <input type="hidden" name="s" value="5">
  Buff ID: <input type="text" name="i"><br>
  Number of seconds: <input type="text" name="n"><br>
  <input type="submit" value="Buff me"></form></p>

  <p><a href="char.php?a=admin&s=6">Delete all buffs</a></p>

<?

} elseif ('r' == $a) {

  include '_charmenu.php';

  $i = getGetStr('i', '0');

  $artifact = hasArtifact($char_obj, $i);
//  $artifact = hasArtifactNew($char_obj, $i, 0);

  if (FALSE == $artifact) {
    echo '<p>You don\'t have that artifact!</p>';
  } elseif ($char_obj->c['level'] < $artifact['min_level']) {
    echo '<p>Your level isn\'t high enough to use that artifact!</p>';
  } else {

    echo '<p>' . renderArtifactStr($artifact) . '</p>';

    include '_read.php';

  }

  echo '<p><a href="char.php">Go back to your character page</a></p>';

} elseif ('ql' == $a) {

  include '_charmenu.php';

  $status = getGetInt('ss', 0);
  if (1 == $status) {
    echo '<p class="tip">All available quests accepted!</p>';
  } elseif (2 == $status) {
    echo '<p class="tip">You\'ve already accepted all available quests.</p>';
  }

  echo '<p><span class="section_header">Quests in progress:</span><br>';
  if ($s == 0) {
    echo '<font size="-2">(<a href="char.php?a=ql&s=1">full view</a>)</font>';
  } else {
    echo '<font size="-2">(<a href="char.php?a=ql">brief view</a>)</font>';
  }
  echo '</p>';

  $quests = getCharQuests($char_obj->c['id']);

  $artifact_id_obj = array();
  foreach ($quests as $q) {
    $artifact_id_obj[$q['quest_artifact1']] = True;
    $artifact_id_obj[$q['quest_artifact2']] = True;
    $artifact_id_obj[$q['quest_artifact3']] = True;
    $artifact_id_obj[$q['reward_artifact']] = True;
  }

  $artifact_retrieve = array();
  foreach ($artifact_id_obj as $k => $v) {
    $artifact_retrieve[] = intval($k);
  }

  if (count($artifact_retrieve) > 0) {
    $artifact_obj = getArtifactArray($artifact_retrieve);
  } else {
    $artifact_obj = array();
  }

  foreach ($quests as $q) {
    if (sg_quest_in_progress == $q['status']) {
      if (($s == 0) && ($q['hidden'] != 0)) {
        continue;
      }

      echo '<p><b>' . $q['name'] . '</b> ';
      if ((($s != 0) && ($q['hidden'] == 0)) || ($s == 0)) {
        echo '(<a href="action.php?a=qh&i=' . $q['id'] . '">hide quest</a>) ';
      } else {
        echo '(<a href="action.php?a=qh&i=' . $q['id'] . '">show quest</a>) ';
      }
      echo '(<i>Level ' . $q['min_level'] .
          '</i>, <a href="talk.php?t=' .
          $q['npc_id'] . '&q=' . $q['id'] . '">quest giver</a>): ' .
          $q['text'];
      if ($q['quest_artifact1'] > 0) {
        echo '<br><u>Artifacts to retrieve:</u>';
        $artifact = $artifact_obj[$q['quest_artifact1']];
        $c_count = min(getArtifactQuantity($char_obj, $q['quest_artifact1']),
                       $q['quest_quantity1']);
        echo '<br>' . $c_count . ' / ' . $q['quest_quantity1'] . ': ';
        echo renderArtifactStr($artifact, $q['quest_quantity1']);

        if ($q['quest_artifact2'] > 0) {
          $artifact = $artifact_obj[$q['quest_artifact2']];
          $c_count = min(getArtifactQuantity($char_obj, $q['quest_artifact2']),
                         $q['quest_quantity2']);
          echo '<br>' . $c_count . ' / ' . $q['quest_quantity2'] . ': ';
          echo renderArtifactStr($artifact, $q['quest_quantity2']);
        }

        if ($q['quest_artifact3'] > 0) {
          $artifact = $artifact_obj[$q['quest_artifact3']];
          $c_count = min(getArtifactQuantity($char_obj, $q['quest_artifact3']),
                         $q['quest_quantity3']);
          echo '<br>' . $c_count . ' / ' . $q['quest_quantity3'] . ': ';
          echo renderArtifactStr($artifact, $q['quest_quantity3']);
        }

      }
      if ($q['quest_foe1'] > 0) {
        echo '<br><u>Foes slain:</u>';
        $f = getFoe($char_obj, $q['quest_foe1']);
        echo '<br>' . $q['foe_count_1'] . ' / ' .
             $q['quest_foe_quantity1'] . ': ' . $f['name'];

        if ($q['quest_foe2'] > 0) {
          $f = getFoe($char_obj, $q['quest_foe2']);
          echo '<br>' . $q['foe_count_2'] . ' / ' .
               $q['quest_foe_quantity2'] . ': ' . $f['name'];
        }

        if ($q['quest_foe3'] > 0) {
          $f = getFoe($char_obj, $q['quest_foe3']);
          echo '<br>' . $q['foe_count_3'] . ' / ' .
               $q['quest_foe_quantity3'] . ': ' . $f['name'];
        }
      }
      if ($q['reward_quantity'] > 0) {
        echo '<br><u>Rewards:</u>';
        if ($q['reward_artifact'] == 0) {
          echo '<br>' . $q['reward_quantity'] . ' gold';
        } elseif ($q['reward_artifact'] > 0) {
          $a = $artifact_obj[$q['reward_artifact']];
          echo '<br>' . $q['reward_quantity'] . 'x ' .
               renderArtifactStr($a, $q['reward_quantity']);
        }
      }
      if ($q['reward_xp'] > 0) {
        echo '<br>' . $q['reward_xp'] . ' XP';
      }
      if ($q['reward_rep_amount'] > 0) {
        echo '<br>' . floor($q['reward_rep_amount'] / 1000) .
            ' reputation with ' . getReputationName($q['reward_rep_id']);
        if ($q['reward_rep_max'] > 0) {
          $score_obj = getReputationScore($q['reward_rep_max']);
          echo '<br><font size="-2">maximum: ' .
              $score_obj['n'] . '</font>';
        }
      }

      echo '</p>' . "\n";
    }
  }

  echo '<p><span class="section_header">Quests available:</span>';
  $quest_todo = getAvailableQuests($char_obj);
  if (count($quest_todo) > 0) {
    echo '<br><font size="-2">(<a href="action.php?a=aaq">accept all ' .
         'available quests</a>)</font>';
  }
  echo '</p><p><ul class="char_list">';
  foreach ($quest_todo as $q) {
    if (($q['repeatable'] == 0) ||
        (($q['repeatable'] == 1) &&
         (!isset($char_obj->c['quests'][$q['id']])))) {
      echo '<li><a href="talk.php?t=' . $q['npc_id'] . '&q=' . $q['id'] .
           '">' . $q['name'] . '</a></b> (<i>Level ' . $q['min_level'] .
           '</i>)</li>' . "\n";
    }
  }
  echo '</ul></p>';

  echo '<p><span class="section_header">Repeatable quests ' .
       'available:</span></p>';
  echo '<p><ul class="char_list">';
  foreach ($quest_todo as $q) {
    if (($q['repeatable'] == 1) &&
        (isset($char_obj->c['quests'][$q['id']]))) {
      echo '<li><a href="talk.php?t=' . $q['npc_id'] . '&q=' . $q['id'] .
           '">' . $q['name'] .
           '</a></b> (<i>Level ' . $q['min_level'] . '</i>)</li>' . "\n";
    }
  }
  echo '</ul></p>';

  if ($s > 0) {
    echo '<p><span class="section_header">Quests completed:</span></p>';
    echo '<p><ul class="char_list">';
    foreach ($quests as $q) {
      if (sg_quest_done == $q['status']) {
        echo '<li><b>' . $q['name'] . "</b></li>\n";
      }
    }
    echo '</ul></p>';
  }

  echo '<p><a href="char.php">Go back to your character page</a></p>';

} elseif ('ma' == $a) {

  include '_charmenu.php';

  foreach ($char_state_obj['out'] as $st) {
    echo $st;
  }

  echo '<p><a href="char.php">Go back to your character page</a></p>';

} elseif ('p1' == $a) {

  include '_charmenu.php';

  echo '<p><span class="section_header">Sandstorm Wisdom Deck: Cards ' .
       'Completed</span></p>';

  $cards = getSandstormCompletionArray($char_obj->c);

  echo '<p>';
  foreach ($cards as $k => $card) {
    if (FALSE == $card['completed']) {
      echo '<s>Card ' . $k . '</s><br>';
    } else {
      echo 'Card ' . $k . ' complete!<br>';
    }
  }
  echo '</p>';

} elseif ('du' == $a) {

  include '_charmenu.php';

  echo '<h3>Duel Requests:</h3>';

  foreach ($char_obj->c['duel_requests'] as $duel) {
    if ($duel['status'] == sg_duel_challenge_recv) {
      echo '<p>Challenge received from <a href="char.php?i=' .
           $duel['target_id'] . '">' . $duel['titled_name'] . '</a> (Level ' .
           $duel['level'] . ')</a><br><font size="-2">' .
           '(<a href="action.php?a=dua&i=' .
           $duel['id'] . '">Accept the challenge</a>) ' .
           '(<a href="action.php?a=dur&i=' .
           $duel['id'] . '">Reject the challenge</a>)</font>' .
           '</p>';
    } elseif ($duel['status'] == sg_duel_challenge_sent) {
      echo '<p>Challenge sent to <a href="char.php?i=' .
           $duel['target_id'] . '">' . $duel['titled_name'] . '</a> (Level ' .
           $duel['level'] . ')</a><br><font size="-2">' .
           '(<a href="action.php?a=dur&i=' .
           $duel['id'] . '">Abort the challenge</a>)</font>' .
           '</p>';
    }
  }

} elseif ('dr' == $a) {

  include '_charmenu.php';

  echo '<h3>Dungeon Run History:</h3>';

  if (isset($_GET['i'])) {
    $i = getGetInt('i', 0);
    $c_obj = new Char($i);
  } else {
    $i = FALSE;
  }

  if (FALSE != $i) {

    $d_obj = getDungeonRunHistory($i);

    if (count($d_obj) == 0) {
      echo '<p>No dungeon runs completed!</p>';
    } else {
      echo '<p>' . count($d_obj) . ' runs completed.</p>';
      echo '<center><table border="0" width="100%"><tr>' .
           '<th>Type</th><th>Level</th>' .
           '<th>XP</th><th>Fatigue</th><th>Combats</th><th>Started</th>' .
           '<th>Completed</th><th>STR</th><th>DEX</th><th>INT</th>' .
           '<th>CHA</th><th>CON</th></tr>';
      foreach ($d_obj as $run) {
        $d_name = '';
        switch ($run['d_id']) {
        case 1: $d_name = '&Aacute;lmok'; break;
        }
        echo '<tr align="center"><td>' . $d_name .
             '</td><td>' . $run['level'] . '</td><td>' .
             $run['xp'] . '</td><td>' . floor($run['total_fatigue'] / 1000) .
             '</td><td>' . $run['total_combats'] . '</td><td>' .
             date('M j y', $run['date_started']) . '</td><td>' .
             date('M j y', $run['date_completed']) . '</td><td>' .
             bitCount($run['skills_str']) . '</td><td>' .
             bitCount($run['skills_dex']) . '</td><td>' .
             bitCount($run['skills_int']) . '</td><td>' .
             bitCount($run['skills_cha']) . '</td><td>' .
             bitCount($run['skills_con']) . '</td></tr>';
      }
      echo '</table></center>';
    }

    if ($c_obj->c['d_id'] > 0) {
      echo '<h3>Current Dungeon Run Statistics:</h3>';
      echo '<p><b>Dungeon Run:</b> ';
      switch ($c_obj->c['d_id']) {
      case 1: echo '&Aacute;lmok'; break;
      }
      echo '<br><b>Total Combats:</b> ' . $c_obj->c['total_combats'] . '<br>';
      echo '<b>Total Fatigue:</b> ' .
           floor($c_obj->c['total_fatigue'] / 1000) . '%</p>';
    }

  }

} elseif ('ac' == $a) {

  $i = getGetInt('i', 0);
  if ($i > 0) {
    $titled_name = getTitledNameById($i);
    $achieve_obj = getAchievements($i);
    include '_profilemenu.php';
  } else {
    $achieve_obj = getAchievements($char_obj->c['id']);
    include '_charmenu.php';
  }

  echo '<h3>Achievements</h3>';

  if (count($achieve_obj) == 0) {
    echo '<p><b>No achievements have been earned yet!</b></p>';
  } else {
    foreach ($achieve_obj as $x) {
      echo '<center><table class="achievement"><tr><td width="36">' .
        '<img src="/images/achieve.gif" width="32" height="32"></td>' .
        '<td width="100%"><b>' . $x['title'] . '</b><br>' .
        $x['description'] . '<br><i>' .
        date('F j, Y, g:i a', $x['timestamp']) .
        '</i></td><td width="36"><img src="/images/achieve.gif" width="32" ' .
        'height="32"></td></tr></table></center>';

    }
  }

//  if ((sg_debug) || ($i == 0)) {
    echo '<h3><b>Incomplete Achievements:</b></h3><p>';
    $a_obj = getAllAchievements();
    foreach ($a_obj as $x) {
      if (!isset($achieve_obj[$x['id']])) {
        echo '<b>' . $x['title'] . '</b><br><i>' .
             $x['description'] .'</i><br>';
      }
    }
//  }

} elseif ('tf' == $a) {

  $i = getGetInt('i', 0);
  if ($i > 0) {
    $titled_name = getTitledNameById($i);
    $foe_count = getTrackingData($i, sg_track_foe);
    include '_profilemenu.php';
  } else {
    $foe_count = $_SESSION['tracking'][sg_track_foe];
    include '_charmenu.php';
  }

  echo '<h3>Top foes slain:</h3>';

  arsort($foe_count);
  $foe_count = array_slice($foe_count, 0, 20, $preserve_keys=TRUE);
  $foe_names = getFoeNames(array_keys($foe_count));

  echo '<p>';
  foreach ($foe_count as $k => $v) {
    echo '<b>' . utf8_encode($foe_names[$k]['name']) . '</b>: ' .
         $v . ' killed<br>';
  }
  echo '</p>';

} elseif ('tu' == $a) {

  $i = getGetInt('i', 0);
  if ($i > 0) {
    $titled_name = getTitledNameById($i);
    $use_count = getTrackingData($i, sg_track_use);
    include '_profilemenu.php';
  } else {
    $use_count = $_SESSION['tracking'][sg_track_use];
    include '_charmenu.php';
  }

  echo '<h3>Top artifacts used:</h3>';

  arsort($use_count);
  $use_count = array_slice($use_count, 0, 20, $preserve_keys=TRUE);
  $use_obj = getArtifactArray(array_keys($use_count));

  echo '<p>';
  foreach ($use_count as $k => $v) {
    echo renderArtifactStr($use_obj[$k]) . ': ' . $v . ' used<br>';
  }
  echo '</p>';

} elseif ('al' == $a) {

  include '_charmenu.php';

  if (sg_allies_enabled) {

  $allies = getUserAllies($_SESSION['u']);

  $ally_id = getGetInt('i', -1);
  $action_id = getGetInt('action', 0);
  if (($ally_id > -1) && ($action_id == $char_obj->c['action_id'])) {
    if (($ally_id == 0) || (isset($allies[$ally_id]))) {
      if ($char_obj->c['ally_id'] > 0) {
        addUserAlly($char_obj);
      }
      if ($ally_id > 0) {
        $char_obj->setIntVar('ally_id', $allies[$ally_id]['id']);
        $char_obj->setIntVar('ally_fatigue', $allies[$ally_id]['fatigue']);
        deleteUserAlly($char_obj->c['user_id'], $ally_id);
        $char_obj->c['ally'] = getAlly($char_obj);
        echo '<p class="tip">You take a new ally!</p>';
      } else {
        $char_obj->setIntVar('ally_id', 0);
        echo '<p class="tip">You send your ally back to their residence.</p>';
      }
      unset($_SESSION['ally']);

      $allies = getUserAllies($_SESSION['u']);
    }
  }

  echo '<h3>Current Ally</h3>';
  if ($char_obj->c['ally_id'] == 0) {
    echo '<p><b>None</b></p>';
  } else {
    echo '<p><b onmouseover="popup(\'' . $char_obj->c['ally']['description'] .
         '\');" ' . 'onmouseout="popout()">' . $char_obj->c['ally']['name'] .
         '</b><br>' .
         $char_obj->c['ally']['title'] . '<br>Fatigue: ' .
         floor($char_obj->c['ally_fatigue'] / 1000) . '%<br>' .
         '<font size="-2">(<a href="char.php?a=al&i=0&action=' .
         $char_obj->c['action_id'] . '">dismiss for now</a>)</font></p>';
    echo awardAchievement($char_obj, 48);
  }

  echo '<h3>Your Allies:</h3>';

  if (count($allies) == 0) {
    echo '<p>You have no allies waiting in your barracks!</p>';
  } else {
    echo '<center><table cellpadding="3">';
    echo '<tr><th>Name</th><th>Class</th><th>Fatigue</th><th>Action</th></tr>';
    foreach ($allies as $x) {
      $x['description'] = str_replace('\'', '&#039;', $x['description']);
      $x['description'] = getEscapeQuoteStr($x['description']);
      echo '<tr align="center"><td><b onmouseover="popup(\'' .
           $x['description'] .
           '\');" ' . 'onmouseout="popout()">' . $x['name'] . '</b></td>' .
           '<td>' . $x['title'] . '</td><td>' . floor($x['fatigue'] / 1000) .
           '%</td><td><font size="-2">(<a href="char.php?a=al&i=' . $x['id'] .
           '&action=' . $char_obj->c['action_id'] .
           '">take with you</a>)</font></td></tr>';
    }
    echo '</table></center>';
  }

  }

} elseif ('pl' == $a) {

  $i = getGetInt('i', 0);
  if ($i > 0) {
    $titled_name = getTitledNameById($i);
    include '_profilemenu.php';
    $plots = getAllPlots($i);
  } else {
    include '_charmenu.php';
    echo '<h3>Land Plots:</h3>';
    $plots = getAllPlots($char_obj->c['id']);
  }

  if (count($plots) == 0) {
    echo '<p>No land plots owned!</p>';
  } else {
    foreach ($plots as $plot) {
      echo '<p><a href="plot.php?i=' . $plot['id'] . '">' . $plot['title'] .
           '</a><br>' . $plot['description'] . '</p>';
    }
  }

}

require '_footer.php';
$save = $char_obj->save();
$log_save = $log_obj->save();

if (sg_debug) {
  debugPrint('<!--'); debugPrint($char); debugPrint('-->');
}

$debug_time_diff = debugTime() - $debug_time_start;
debugPrint('<font size="-2">Page rendered in ' .
    number_format($debug_time_diff, 2, ".", ".") . 's</font>');

?>

</div>
</body>
</html>