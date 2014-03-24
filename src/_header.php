<?
if ( ( isset( $ts_no_keypress) ) && ( ! ( $ts_no_keypress == TRUE ) ) ) {
    echo '<script type="text/javascript" src="include/ts_keypress.js"></script>';
}
?>

<div class="section_top">
<div class="section_wrapper">
<table class="header_table" width="100%"><tr>
<td class="header_left" width="60%" valign="top">

<?

echo '<a href="char.php">' .
    $char_obj->c[ 'titled_name' ] .
    '</a>, Level ' . $char_obj->c[ 'level' ] . ', Gold: ' .
    $char_obj->c[ 'gold' ];
if ( $char_obj->c[ 'new_mail_count' ] > 1 ) {
    echo ' (<a href="mail.php">' . $char_obj->c[ 'new_mail_count' ] .
         '&nbsp;new&nbsp;messages</a>)';
} elseif ( $char_obj->c[ 'new_mail_count' ] == 1 ) {
    echo ' (<a href="mail.php">' . $char_obj->c[ 'new_mail_count' ] .
         '&nbsp;new&nbsp;message</a>)';
}
$duel_requests = count( $char_obj->c[ 'duel_requests' ] );
if ( $duel_requests > 0 ) {
    echo ' (<a href="char.php?a=du">' . $duel_requests .
         '&nbsp;duel&nbsp;request';
    if ( $duel_requests > 1 ) { echo 's'; }
    echo '</a>)';
}

echo '<table border="0" class="stat_header" cellspacing="0"><tr><td>';
echo 'Health (';
renderHp( $char_obj->c );
echo '/' . $char_obj->c[ 'base_hp' ] . ')</td><td>';
$hpWidth = 80.0;
$hpRemainingWidth = ( $char_obj->c[ 'current_hp' ] * $hpWidth ) /
    $char_obj->c[ 'base_hp' ];
echo '<table class="stat"><tr>';
if ( $hpRemainingWidth > 0 ) {
    echo '<td class="good" width="' . round( $hpRemainingWidth ) . '"></td>';
}
if ( $hpRemainingWidth < $hpWidth ) {
    echo '<td class="bad" width="' . round( $hpWidth - $hpRemainingWidth ) . '"></td>';
}
echo '</tr></table></td>';

echo '<td>'; //'<tr><td>';
echo 'Mana (' . $char_obj->c[ 'mana' ] . '/' .
     $char_obj->c[ 'mana_max' ] . ')</td><td>';
$mWidth = 80.0;
$mRemainingWidth = ( $char_obj->c[ 'mana' ] * $hpWidth ) /
    $char_obj->c[ 'mana_max' ];
echo '<table class="stat"><tr>';
if ( $mRemainingWidth > 0 ) {
    echo '<td class="m_good" width="' . round( $mRemainingWidth ) . '"></td>';
}
if ( $mRemainingWidth < $mWidth ) {
    echo '<td class="m_bad" width=' . round( $mWidth-$mRemainingWidth ) . '"></td>';
}

echo '</tr></table></td></tr></table>';

?>

</td><td class="header_right" width="40%" valign="top">
<?

$buff_count = 0;
$buff_array = array();
foreach ( $char_obj->c[ 'buffs' ] as $buff ) {
    if ( $buff[ 'invisible' ] == 0 ) {
        $buff[ 'name' ] = getEscapeQuoteStr( fixStr( $buff[ 'name' ] ) );
        $buff[ 'description' ] = getEscapeQuoteStr( fixStr( $buff[ 'description' ] ) );

        $buff_array[] = $buff;
        $buff_count += 1;
    }
}
if ( ( $buff_count == 0 ) && ( isset( $zone ) ) ) {
    echo sg_name . '<br>' . $zone[ 'name' ];
} else {
    $now = time();
    ksort( $buff_array );
    foreach ( $buff_array as $buff ) {
        $expire_st = renderTimeRemaining( $now, $buff[ 'expires' ] );
        if ( $buff[ 'combat_turn_expires' ] > 0 ) {
            $expire_st = $expire_st . '<br>' .
                ( $buff[ 'combat_turn_expires' ] - $char_obj->c[ 'total_combats' ] ) .
                ' combats';
        }
        echo '<img src="images/' . $buff[ 'image' ] .
             '" height="24" width="24" onmouseover="popup(\'<b>' .
             $buff[ 'name' ] . '</b><br>' . $buff[ 'description' ] .
             '<br><font color=blue>' . $expire_st . '</font>' .
             '\')" onmouseout="popout()" ' .
             'alt="' . $buff[ 'name' ] . ': ' . $buff[ 'description' ] . '" />';
    }
}

?>
</td></tr>
<?
/*
if ((array_key_exists(sg_flag_navbar_toggles, $char_obj->c['flags'])) &&
    ($char_obj->c['flags'][sg_flag_navbar_toggles] > 0)) {
    $v = $char_obj->c['flags'][sg_flag_navbar_toggles];
    echo '<tr><td colspan="2" align="center"><font size="-2">Quick Links: ';
    $loc_array = array();
    if (($v & (1 << 0)) > 0) {
        $loc_array[] = '<a href="char.php">Character&nbsp;Profile</a>';
    }
    if (($v & (1 << 1)) > 0) {
        $loc_array[] = '<a href="inventory.php">Artifacts</a>';
    }
    if (($v & (1 << 2)) > 0) {
        $loc_array[] = '<a href="char.php?a=ma">Cast&nbsp;a&nbsp;Spell</a>';
    }
    if (($v & (1 << 24)) > 0) {
        $loc_array[] = '<a href="char.php?a=al">View&nbsp;Allies</a>';
    }
    if (($v & (1 << 3)) > 0) {
        $loc_array[] = '<a href="mail.php">Mailbox</a>';
    }
    if (($v & (1 << 4)) > 0) {
        $loc_array[] = '<a href="char.php?a=ql">Quest&nbsp;Log</a>';
    }
    if (($v & (1 << 5)) > 0) {
        $loc_array[] = '<a href="recipe.php?t=1">Cook&nbsp;Something</a>';
    }
    if (($v & (1 << 6)) > 0) {
        $loc_array[] = '<a href="recipe.php?t=2">Craft&nbsp;Something</a>';
    }
    if (($v & (1 << 7)) > 0) {
        $loc_array[] = '<a href="main.php">Capital&nbsp;City</a>';
    }
    if (($v & (1 << 8)) > 0) {
        $loc_array[] = '<a href="main.php?z=7&a=1">Infirmary&nbsp;' .
            '(Full&nbsp;Heal)</a>';
    }
    if (($v & (1 << 22)) > 0) {
        $loc_array[] = '<a href="main.php?z=7&a=2">Infirmary&nbsp;' .
            '(Full&nbsp;Mana&nbsp;Restore)</a>';
    }
    if (($v & (1 << 23)) > 0) {
        $loc_array[] = '<a href="main.php?z=7&a=3">Infirmary&nbsp;' .
            '(Self&nbsp;Bandage)</a>';
    }
    if (($v & (1 << 9)) > 0) {
        $loc_array[] = '<a href="main.php?z=13">Hall&nbsp;of&nbsp;Records</a>';
    }
    if (($v & (1 << 10)) > 0) {
        if ($char_obj->c['level'] < 3) {
            $loc_array[] = '<s>Auction&nbsp;House</s>';
        } else {
            $loc_array[] = '<a href="main.php?z=44">Auction&nbsp;House</a>';
        }
    }
    if (($v & (1 << 11)) > 0) {
        if ($char_obj->c['level'] < 4) {
            $loc_array[] = '<s>Casino</s>';
        } else {
            $loc_array[] = '<a href="main.php?z=39">Casino</a>';
        }
    }
    if (($v & (1 << 13)) > 0) {
        $loc_array[] = '<a href="guild.php">Guild</a>';
    }
    if (($v & (1 << 14)) > 0) {
        $loc_array[] = '<a href="sell.php">Sell&nbsp;Something</a>';
    }
    if (($v & (1 << 15)) > 0) {
        if (getCharArtifactQuantity($char_obj, 463, 0, FALSE) > 0) {
            $loc_array[] = '<a href="char.php?a=r&i=463">Regional&nbsp;Map</a>';
        } else {
            $loc_array[] = '<s>Regional&nbsp;Map</s>';
        }
    }
    if (($v & (1 << 16)) > 0) {
        $loc_array[] = '<a href="online.php">Who\'s&nbsp;Online</a>';
    }
    if (($v & (1 << 17)) > 0) {
        $loc_array[] = '<a href="search.php">Character&nbsp;Search</a>';
    }
    if (($v & (1 << 18)) > 0) {
        $loc_array[] = '<a href="bank.php">Bank&nbsp;of&nbsp;Nobility</a>';
    }
    if (($v & (1 << 19)) > 0) {
        $loc_array[] = '<a href="main.php?z=107">Temporal&nbsp;Laboratory</a>';
    }
    if (($v & (1 << 20)) > 0) {
        if (getFlagValue($char_obj, sg_flag_enchanting) > 0) {
            $loc_array[] = '<a href="enchant.php">Enchant&nbsp;Something</a>';
        }
    }
    if (($v & (1 << 21)) > 0) {
        $loc_array[] = '<a href="main.php?z=116">Starfall&nbsp;Bay&nbsp;' .
            'Auctions</a>';
    }
    if (($v & (1 << 22)) > 0) {
        $loc_array[] = '<a href="zonelevel.php">Zones&nbsp;by&nbsp;Level</a>';
    }
    echo join(', ', $loc_array);
    echo '</font></td></tr>';
}
*/
?>

<tr><td colspan="2" align="center"><center>
  <ul class="char_nav">
    <li><span><a href="#">Character</a></span>
      <ul class="char_sub_nav">
        <li><a href="char.php">Character Profile</a></li>
        <li><a href="inventory.php">Artifact List</a></li>
        <li><a href="char.php?a=al">My Allies</a></li>
        <li><a href="mail.php">Mailbox</a></li>
        <li><a href="char.php?a=ql">Quest Log</a></li>
        <li><a href="guild.php">My Guild</a></li>
      </ul>
    </li>
    <li><span><a href="#">Navigate</a></span>
      <ul class="char_sub_nav">
        <li><a href="main.php">Capital City</a></li>
        <li><a href="main.php?z=44">Auction House</a></li>
        <li><a href="main.php?z=39">Casino</a></li>
        <li><a href="main.php?z=7">Infirmary</a></li>
        <li><a href="main.php?z=13">Hall of Records</a></li>
        <li><a href="bank.php">Bank of Nobility</a></li>
        <li><a href="main.php?z=107">Temporal Laboratory</a></li>
        <li><a href="main.php?z=116">Starfall Bay Auctions</a></li>
        <li><a href="char.php?a=r&i=463">Regional Map</a></li>
        <li><a href="zonelevel.php">Zones by Level</a></li>
      </ul>
    </li>
    <li><span><a href="#">Actions</a></span>
      <ul class="char_sub_nav">
        <li><a href="char.php?a=ma">Cast a Spell</a></li>
        <li><a href="recipe.php?t=1">Cooking</a></li>
        <li><a href="recipe.php?t=2">Crafting</a></li>
        <li><a href="enchant.php">Enchanting</a></li>
        <li><a href="sell.php">Sell Something</a></li>
        <li><a href="online.php">Online Players</a></li>
        <li><a href="search.php">Character Search</a></li>
      </ul>
    </li>
    <li><span><a href="#">Builder</a></span>
      <ul class="char_sub_nav">
        <li><a href="builder.php?v">View all</a></li>
        <li><a href="builder.php?a=1">Artifact Builder</a></li>
      </ul>
    </li>
  </ul>
</center></td></tr>

</table>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function(){  
    //$("ul.char_sub_nav").parent().append("<span></span>");
    $("ul.char_nav li span").click(function() {
        $(this).parent().find("ul.char_sub_nav").slideDown('fast').show();
        $(this).parent().hover(function() {  
            }, function(){  
                $(this).parent().find("ul.char_sub_nav").slideUp('slow');
            });
    }).hover(function() {  
        $(this).addClass("subhover");
    }, function(){
        $(this).removeClass("subhover");
    });  
});
</script>

</div>
</div>

<?

$date = time();
$date_roll = mktime( 21, 0, 0,
    date( 'm' , $date ), date( 'd', $date ), date( 'Y', $date ) );
$date_diff = $date_roll - $date;

if ( ( $date_diff < 300 ) && ( $date_diff > 0 ) ) {
    $r_mins = round( $date_diff / 60 );
    echo '<p class="tip">' . $r_mins . ' minute';
    if ( $r_mins > 1 ) { echo 's'; }
    echo ' until rollover.</p>';
}

if ( array_key_exists( 'alert_text', $_SESSION ) ) {
    echo '<p class="tip">' . $_SESSION[ 'alert_text' ] . '</p>';
    unset( $_SESSION[ 'alert_text' ] );
}

?>

<div class="section_middle">
<div class="section_wrapper">
