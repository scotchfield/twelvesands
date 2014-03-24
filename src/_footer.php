</div>
</div>

<div class="section_bottom">
<div class="section_wrapper">

<center>

<table border="0" class="stat_footer"><tr>

<?

if ( $char_obj->c[ 'level' ] > 1 ) {
    $curXp = $char_obj->c[ 'xp' ] - levelXp( $char_obj->c[ 'level' ] );
    $levelXp = levelXp( $char_obj->c[ 'level' ] + 1 ) -
        levelXp( $char_obj->c[ 'level' ] );
} else {
    $curXp = $char_obj->c[ 'xp' ];
    $levelXp = levelXp( $char_obj->c[ 'level' ] + 1 );
}

if ( $curXp < 0 ) { $curXp = 0; }
if ( $levelXp < 0 ) { $levelXp = 1; }
if ( $curXp > $levelXp ) { $curXp = $levelXp; }

echo '<td>XP (' . $curXp . '/' . $levelXp . ')</td><td>';
echo renderBarStr( $curXp, $levelXp, 'good', 'neutral', 50.0 );

echo '</td><td>Fatigue (';
renderFatigue( $char_obj->c[ 'fatigue' ] );
echo ')</td><td>';
$fWidth = 50.0;
$fRemainingWidth = ( $char_obj->c[ 'fatigue' ] * $fWidth ) / 100000;
echo '<table class="stat"><tr>';
if ( $fRemainingWidth > 0 ) {
    echo '<td class="bad" width="' . ceil( $fRemainingWidth ) . '"></td>';
}
if ( $fRemainingWidth < $fWidth ) {
    $fRested = min( 100000 - $char_obj->c[ 'fatigue' ],
                    $char_obj->c[ 'fatigue_rested' ] );
    $fRestedWidth = ( $fRested * $fWidth ) / 100000;
    $fRemainingWidth = ( ( 100000 - $char_obj->c[ 'fatigue' ] -
        $char_obj->c[ 'fatigue_rested' ] ) *
        $fWidth ) / 100000;

    if ( $fRestedWidth > 0 ) {
        echo '<td class="rested" width="' . ceil( $fRestedWidth ) . '"></td>';
    }
    if ( $fRemainingWidth > 0 ) {
        echo '<td class="good" width="' . ceil( $fRemainingWidth ) . '"></td>';
    }
}
echo '</tr></table></td><td>Burden: <a href="#" onmouseout="popout()" ' .
     'style="text-decoration: none;" onmouseover="popup(\'<b>' .
     $char_obj->c[ 'inventory_obj' ]->getTotalQuantity() .
     ' / 750</b> artifacts in inventory<br>';
if ( $char_obj->c[ 'burden' ] < 1.0 ) {
    echo '<i>Combats take only 90% of regular fatigue</i>\')">None';
} elseif ( $char_obj->c[ 'burden' ] == 1.0 ) {
    echo '<i>Combats take regular fatigue</i>\')">Light';
} elseif ( $char_obj->c[ 'burden' ] < 2.0 ) {
    echo '<i>Combats take ' . round( $char_obj->c[ 'burden' ] * 100 ) .
         '\% of regular fatigue.  Visit the bank, or sell some ' .
         'artifacts at a vendor!</i>\')">Medium';
} elseif ( $char_obj->c[ 'burden' ] < 3.0 ) {
    echo '<i>Combats take ' . round( $char_obj->c[ 'burden' ] * 100 ) .
         '\% of regular fatigue.  Visit the bank, or sell some ' .
         'artifacts at a vendor!</i>\')">Heavy';
} else {
    echo '<i>No combat possible until you remove some artifacts from your ' .
         'inventory!  Visit the bank, or sell some artifacts at a vendor!' .
         '</i>\')">Overloaded';
}
echo '</a></td></tr>';

$last_zone = getZone( getFlagValue( $char_obj, sg_flag_last_combat_zone ) );
if ( ( $last_zone != FALSE ) && ( isset( $last_zone[ 'id' ] ) ) ) {
    echo '<tr><td colspan="4" align="center">Last Combat: ' .
         '<a id="bar_default" href="' . getCombatLink( $last_zone[ 'id' ] ) .
         '">' . $last_zone[ 'name' ] . '</a>';
    echo '<br>(<a href="main.php?z=' . $last_zone[ 'parent_id' ] . '">' .
         $last_zone[ 'parent_name' ] . '</a>)';
    echo '</td></tr>';
}
?>

</table></center>

<p class="action_footer">
<a href="main.php">Capital&nbsp;City</a> |
<a href="/faq/" target="_blank">Help</a> |
<a href="donate.php">Donate</a> |
<a href="account.php">Manage&nbsp;Account</a> |
<a href="#" onclick="top.location='select.php'">Switch&nbsp;Character</a> |
<a href="online.php">Who's&nbsp;Online</a> |
<a href="logout.php" target="_top">Logout</a>
</p>

<?
if ( sg_debug ) {
    echo '<p><font size="-2">(<a href="action.php?a=zzz">' .
         'reset session vars</a>)</font></p>';
}
?>

<?
debugPrint( '<font size="-2">Memory usage: ' . memory_get_usage() . '</font>' );
?>

<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">
</script>
<script type="text/javascript">
_uacct = "UA-1685371-3";
urchinTracker();
</script>

</div>
</div>