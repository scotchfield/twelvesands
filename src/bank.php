<?

require_once 'include/core.php';

require_once sg_base_path . 'include/bank.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/validate.php'; 

$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$action = getGetStr( 'a', '' );
$max_withdrawals = -1;
if ( $char_obj->c[ 'd_run' ] > 0 ) {
    $max_withdrawals = 5 + floor( $char_obj->c[ 'total_fatigue' ] / 50000 ) -
        getFlagValue( $char_obj, sg_flag_bank_withdrawals );
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
<script type="text/javascript" src="include/ts_bank.js"></script>

<div class="container">

<?

require '_header.php';

function renderBankInventoryList( $a_obj, $title, $type_obj ) {
    echo '<p><span class="section_header">' . $title . '</span></p>';
    echo '<ul class="char_list">';

    foreach ( $a_obj as $artifact ) {
      if ( array_key_exists( $artifact[ 'type' ], $type_obj ) ) {
          echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
               renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
               '<input type="checkbox" name="ids[]" value="' . $artifact[ 'id' ] .
               ',' . $artifact[ 'm_enc' ] . '"></li>';
        }
    }

    echo '</ul>';
}

?>

<p class="zone_title">Bank of Nobility</p>

<?

$s = getGetInt( 's', 0 );
switch ( $s ) {
case 1:
    echo '<p class="tip">You deposit some gold.</p>';
    break;
case 2:
    echo '<p class="tip">You withdraw some gold.</p>';
    break;
case 3:
    echo '<p class="tip">You can\'t transfer gold during a dungeon run!</p>';
    break;
}

if ( 'bl' == $action ) {

    echo '<p><b>Withdraw artifacts:</b></p>';

    echo '<p><font size="-2">';
    echo '(<a href="javascript:toggleAllBank(true);">Select all</a>) ';
    echo '(<a href="javascript:toggleAllBank(false);">Select none</a>) ';
    echo '</font></p>';

?>

<center><form method="post" action="action.php?a=zw" id="bank" name="bank">
<table width="100%"><tr><td width="50%" valign="top"><div class="table_stat">
<?

    $a_obj = getBankArtifacts( $char_obj->c[ 'id' ] );

    $char_armour_array = array();
    foreach ( $a_obj as $artifact ) {
        if ( in_array( $artifact[ 'type' ], $armourArray ) ) {
            $char_armour_array[] = $artifact;
        }
    }
    // sortArmourArray(); // force the preprocessor to pick this method up
    usort( $char_armour_array, "sortArmourArray" );

    renderBankInventoryList( $a_obj, 'Weapons',
        array( sg_artifact_weapon => True ) );

    echo '<p><span class="section_header">Armour</span></p>';
    echo '<ul class="char_list">';
    $last_armour_type = 0;
    foreach ( $char_armour_array as $artifact ) {
        if ( $last_armour_type != $artifact[ 'type' ] ) {
            $last_armour_type = $artifact[ 'type' ];
            $y = '<li style="padding-top: 10px;"><i>';
            $z = '</i></li>';
            switch ( $last_armour_type ) {
            case sg_artifact_armour_head:    echo "$y Head $z"; break;
            case sg_artifact_armour_chest:   echo "$y Chest $z"; break;
            case sg_artifact_armour_legs:    echo "$y Pants $z"; break;
            case sg_artifact_armour_neck:    echo "$y Neck $z"; break;
            case sg_artifact_armour_trinket: echo "$y Trinket $z"; break;
            case sg_artifact_armour_hands:   echo "$y Hands $z"; break;
            case sg_artifact_armour_wrists:  echo "$y Wrists $z"; break;
            case sg_artifact_armour_belt:    echo "$y Belt $z"; break;
            case sg_artifact_armour_boots:   echo "$y Boots $z"; break;
            case sg_artifact_armour_ring:    echo "$y Ring $z"; break;
            }
        }
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
             renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
             '<input type="checkbox" name="ids[]" value="' . $artifact[ 'id' ] .
             ',' . $artifact[ 'm_enc' ] . '"></li>';
    }
    echo '</ul>';

    renderBankInventoryList( $a_obj, 'Mounts',
        array( sg_artifact_mount => True ) );
    renderBankInventoryList( $a_obj, 'Cards',
        array( sg_artifact_puzzle_1 => True ) );
    renderBankInventoryList( $a_obj, 'Runes',
        array( sg_artifact_rune => True ) );
    renderBankInventoryList( $a_obj, 'Readable',
        array( sg_artifact_readable => True ) );
    renderBankInventoryList( $a_obj, 'Quest Related',
        array( sg_artifact_quest => True ) );

    echo '</div></td><td valign="top"><div class="table_stat">';

    renderBankInventoryList( $a_obj, 'Food',
        array( sg_artifact_edible => True ) );
    renderBankInventoryList( $a_obj, 'Usable',
        array( sg_artifact_usable => True ) );
    renderBankInventoryList( $a_obj, 'Other Artifacts', array(
        sg_artifact_none => True, sg_artifact_combat_usable => True,
        sg_artifact_warfare_1 => True, sg_artifact_enchanting => True ) );

?>
</div></td></tr></table>
<p>
<input type="radio" name="v" value="1" checked="on"> Withdraw 1<br>
<input type="radio" name="v" value="2"> Withdraw all<br>
<input type="radio" name="v" value="3"> Withdraw how many: 
<input type="text" name="n" size="4"></p>
<input type="submit" value="Withdraw">
</form></center>

<p><a href="bank.php">Return to the Bank of Nobility</a></p>

<?

} elseif ( 'bd' == $action ) {

    echo '<p><b>Deposit artifacts:</b></p>';

    echo '<p><font size="-2">';
    echo '(<a href="javascript:toggleAllBank(true);">Select all</a>) ';
    echo '(<a href="javascript:toggleAllBank(false);">Select none</a>) ';
    echo '</font></p>';

?>

<center><form method="post" action="action.php?a=zd" id="bank" name="bank">
<table width="100%"><tr><td width="50%" valign="top"><div class="table_stat">

<?

    $a_obj = getCharArtifacts( $char_obj->c[ 'id' ] );

    $char_armour_array = array();
    foreach ( $a_obj as $artifact ) {
        if ( in_array( $artifact[ 'type' ], $armourArray ) ) {
            $char_armour_array[] = $artifact;
        }
    }
    // sortArmourArray(); // force the preprocessor to pick this method up
    usort( $char_armour_array, "sortArmourArray" );

    renderBankInventoryList( $a_obj, 'Weapons',
        array( sg_artifact_weapon => True ) );

    echo '<p><span class="section_header">Armour</span></p>';
    echo '<ul class="char_list">';
    $last_armour_type = 0;
    foreach ( $char_armour_array as $artifact ) {
        if ( $last_armour_type != $artifact[ 'type' ] ) {
            $last_armour_type = $artifact[ 'type' ];
            $y = '<li style="padding-top: 10px;"><i>';
            $z = '</i></li>';
            switch ( $last_armour_type ) {
            case sg_artifact_armour_head:    echo "$y Head $z"; break;
            case sg_artifact_armour_chest:   echo "$y Chest $z"; break;
            case sg_artifact_armour_legs:    echo "$y Pants $z"; break;
            case sg_artifact_armour_neck:    echo "$y Neck $z"; break;
            case sg_artifact_armour_trinket: echo "$y Trinket $z"; break;
            case sg_artifact_armour_hands:   echo "$y Hands $z"; break;
            case sg_artifact_armour_wrists:  echo "$y Wrists $z"; break;
            case sg_artifact_armour_belt:    echo "$y Belt $z"; break;
            case sg_artifact_armour_boots:   echo "$y Boots $z"; break;
            case sg_artifact_armour_ring:    echo "$y Ring $z"; break;
            }
        }
        echo '<li>' . $artifact[ 'quantity' ] . 'x&nbsp;' .
             renderArtifactStr( $artifact, $artifact[ 'quantity' ] ) . '&nbsp;' .
             '<input type="checkbox" name="ids[]" value="' . $artifact[ 'id' ] .
             ',' . $artifact[ 'm_enc' ] . '"></li>';
    }
    echo '</ul>';

    renderBankInventoryList( $a_obj, 'Mounts',
        array( sg_artifact_mount => True ) );
    renderBankInventoryList( $a_obj, 'Cards',
        array( sg_artifact_puzzle_1 => True ) );
    renderBankInventoryList( $a_obj, 'Runes',
        array( sg_artifact_rune => True ) );
    renderBankInventoryList( $a_obj, 'Readable',
        array( sg_artifact_readable => True ) );
    renderBankInventoryList( $a_obj, 'Quest Related',
        array( sg_artifact_quest => True ) );

    echo '</div></td><td valign="top"><div class="table_stat">';

    renderBankInventoryList( $a_obj, 'Food',
        array( sg_artifact_edible => True ) );
    renderBankInventoryList( $a_obj, 'Usable',
        array( sg_artifact_usable => True ) );
    renderBankInventoryList( $a_obj, 'Other Artifacts', array(
        sg_artifact_none => True, sg_artifact_combat_usable => True,
        sg_artifact_warfare_1 => True, sg_artifact_enchanting => True ) );

?>

</div></td></tr></table>
<p>
<input type="radio" name="v" value="1" checked="on"> Deposit 1<br>
<input type="radio" name="v" value="2"> Deposit all<br>
<input type="radio" name="v" value="3"> Deposit how many:
<input type="text" name="n" size="4"></p>
<input type="submit" value="Deposit">
</form></center>

<?

    echo '<p><a href="bank.php">Return to the Bank of Nobility</a></p>';

} elseif ( 'g' == $action ) {

    if ( $char_obj->c[ 'd_id' ] == 0 ) {

        echo '<p><b>You have ' . $char_obj->c[ 'gold_bank' ] .
             ' gold in the bank.</b></p>';

?>
<hr width="300">
<p><b>Deposit gold:</b></p>
<p><form method="post" action="action.php?a=zgd" id="bank" name="bank">
Amount: <input type="text" name="g">
<input type="submit" value="Deposit"><br><br>
</form></p>
<hr width="300">
<p><b>Withdraw gold:</b></p>
<p><form method="post" action="action.php?a=zgw" id="bank" name="bank">
Amount: <input type="text" name="g">
<input type="submit" value="Withdraw"><br><br>
</form></p>
<hr width="300">
<?

    } else {
        echo '<p class="tip">You can\'t transfer gold on a dungeon run!</p>';
    }

    echo '<p><a href="bank.php">Return to the Bank of Nobility</a></p>';

} else {

    echo '<p class="zone_description">Finely dressed businessfolk stroll ' .
         'around the floors of this stunning marble building.  You notice a ' .
         'massive vault behind a force of the finest city guards.  If you\'re ' .
         'looking to lock up your goods, this is the place to do it!</p>';

    if ( $max_withdrawals > 0 ) {
        echo '<p class="tip">You can currently make ' . $max_withdrawals .
             ' more withdrawals.</p>';
    } elseif ( $max_withdrawals == 0 ) {
        echo '<p class="tip">You can\'t make any more withdrawals until ' .
             'you\'ve done more adventuring on this dungeon run!<br>You ' .
             'can withdraw five artifacts initially, and another artifact for ' .
             'every fifty fatigue points after that.</p>';
    }

    echo '<p><b>You have ' . $char_obj->c[ 'gold_bank' ] .
         ' gold in the bank.</b></p>';

?>

<h3>Bank functions</h3>

<p>
<a href="bank.php?a=bl">List your bank contents</a><br>
<a href="bank.php?a=bd">Deposit artifacts</a><br>
<a href="bank.php?a=g">Transfer gold at the bank</a>
</p>

<?
/*
<h3>Public Exhibit</h3>

<p>
<a href="bank.php?a=el">List your exhibit contents</a><br>
<a href="bank.php?a=ed">Place artifacts in your exhibit</a>
</p>
*/
?>

<p><span class="section_header">Places to go</span></p>
<center><table class="nav_table" border="0"><tbody><tr><td align="right" width="100"><span class="nav_type">Travel &nbsp;</span></td><td><a href="main.php?z=1" onmouseover="popup('<b>Capital City</b><br>You stand at the center of town.  The city bustles with activity, as people move between districts.  Up ahead lies the Market District, where you can purchase things like armour and weapons, trade goods, and other artifacts you may need in your travels.  The Academy towers lie off to the west, the source of knowledge and skills in the land.  A collection of other shops and stores lie around you.')" onmouseout="popout()">Capital City</a></td></tr></tbody></table></center>

<?

}

require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>