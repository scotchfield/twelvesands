<?

require_once 'include/core.php';
require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/combats.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/sql.php';

$a = getGetStr( 'a', '0' );
$changed = getGetInt( 'c', 0 );
$s = getGetStr( 's', '0' );

$char_obj = new Char( $_SESSION[ 'c' ] );

$render_obj = array();

$combat_bar_array = getAllCombatBarOptions( $char_obj );

define( 'account_navbar', 'nv' );
define( 'account_password', 'p' );
define( 'account_combatbar', 'cb' );
define( 'account_disabletips', 'dt' );
define( 'account_disablechoicekeypress', 'dc' );
define( 'account_battlecries', 'bc' );


if ( account_disabletips == $a ) {

    if ( ! getFlagBit( $char_obj, sg_flag_account_bit_options, 0 ) ) {
        $char_obj->enableFlagBit( sg_flag_account_bit_options, 0 );
        $render_obj[] = '<p class="tip">Login tips disabled!</p>';
    } else {
        $char_obj->disableFlagBit( sg_flag_account_bit_options, 0 );
        $render_obj[] = '<p class="tip">Login tips enabled!</p>';
    }

} elseif ( account_disablechoicekeypress == $a ) {

    if ( ! getFlagBit( $char_obj, sg_flag_account_bit_options, 1 ) ) {
        $char_obj->enableFlagBit( sg_flag_account_bit_options, 1 );
        $render_obj[] = '<p class="tip">Choice adventure keypresses disabled!</p>';
    } else {
        $char_obj->disableFlagBit( sg_flag_account_bit_options, 1 );
        $render_obj[] = '<p class="tip">Choice adventure keypresses enabled!</p>';
    }

} elseif ( account_navbar == $a ) {

    $i = getGetInt( 'i', -1 );
    if ( ( $i >= 0 ) && ( $i <= 30 ) ) {
        $val = getFlagValue( $char_obj, sg_flag_navbar_toggles );
        $val = $val ^ ( 1 << $i );
        $char_obj->addFlag( sg_flag_navbar_toggles, $val );
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

if ( $changed > 0 ) {
    echo '<p class="tip">Account saved!</p>';
}
foreach ( $render_obj as $x ) {
    echo $x;
}

?>

<h2>Account Management</h2>

<p><b>Character:</b> <?= $char_obj->c[ 'name' ] ?>
 (#<?= $char_obj->c[ 'id' ] ?>)</p>

<?

if ( 'p0' == $s ) {
    echo '<p><font color="red">Error: could not change password!</font></p>';
} elseif ( 'p1' == $s ) {
    echo '<p><font color="blue">Password successfully changed!</font></p>';
}

if ( account_password == $a ) {

?>

<script type="text/javascript" src="include/md5.js"></script>
<script type="text/javascript" src="include/ts_account.js"></script>

<center><p>Change Password:</p>
<form action="action.php" method="get" id="account_form">
<table border="0">
<tr>
<td>Password:</td>
<td align="right"><input type="password" name="pa" size="30" /></td>
</tr><tr>
<td>Password (verify):</td>
<td align="right"><input type="password" name="pb" size="30" /></td>
</tr>
<input type="hidden" name="a" value="pc" />
<input type="hidden" name="i" value="" />
</table>
<input type="button" value="Submit" onclick="S_FormSubmit();" />
</form>

<p><a href="account.php">Return to Account Management</a></p>

<?

} elseif ( account_battlecries == $a ) {

    echo '<p class="tip">You can declare a set of battle cries that will ' .
         'be randomly triggered at the beginning of your combat.  Keep it ' .
         'clean!</p>';

    if ( count( $_POST ) > 0 ) {
        $cries_obj = array();
        for ( $i = 0; $i < 5; $i++ ) {
            $st = getPostStr( 'bc_' . $i, '' );
            if ( strlen( $st ) > 0 ) { $cries_obj[] = $st; }
        }
        deleteBattleCries( $char_obj->c[ 'id' ] );
        addBattleCries( $char_obj->c[ 'id' ], $cries_obj );
        $_SESSION[ 'battle_cries' ] = getBattleCries( $char_obj->c[ 'id' ], 0 );
        $char_obj->c[ 'battle_cries' ] = $_SESSION[ 'battle_cries' ];
        echo '<p class="tip">Battle cries updated!</p>';
    }

    echo '<form action="account.php?a=' . account_battlecries .
         '" method="post"><p><b>Add up to five battle cries:</b></p>';
    for ( $i = 0; $i < 5; $i++ ) {
        echo '<p><input type="text" name="bc_' . $i . '" value="' .
             $char_obj->c[ 'battle_cries' ][ $i ] . '" size="80"></p>';
    }
    echo '<p><input type="submit" value="Update Battle Cries"></p></form>';

} elseif ( account_combatbar == $a ) {

    echo '<p class="tip">While fighting, you have access to a static combat ' .
         'bar that ' .
         'remains at the top of the screen.  From this page, you can set the ' .
         'values that appear in each slot of that bar.  Simply change the ' .
         'value in each drop-down box (or leave values as Nothing, if you ' .
         'run out of things to add), and click Submit Changes when you\'re ' .
         'finished.<br><br>';
    echo 'By setting values in each slot, you also gain the ability to ' .
         'press that number on your keyboard during combat.  For example, if ' .
         'you set Slot 1 to "Attack with your weapon", pressing the 1 key in ' .
         'a fight will automatically trigger that attack.</p>';

    $i = getGetInt( 'i' , 0 );

    if ( array_key_exists( $i, $combat_bar_valid_bases ) ) {

        echo '<form action="action.php" method="get" id="account_form">';
        echo '<center><table class="combat_config">';
        echo '<tr><th>Slot</th><th>Icon</th><th>Value</th></tr>';

        for ( $x = 0; $x < 10; $x++ ) {

            $v = $combat_bar_valid_bases[ $i ] + $x;
            $attack = $combat_bar_array[ getFlagValue( $char_obj, $v ) ];
            if ( $attack == NULL ) {
                $attack = $combat_bar_array[ 0 ];
            }

            echo '<tr><td>' . ( ( $x + 1 ) % 10 ) . '</td>';
            echo '<td>' . '<img src="images/' . $attack[ 'i' ] .
                 '" width="24" height="24"><br>' .
                 $attack[ 'n' ] . '</td>';
            echo '<td><select name="v' . $x . '">';
            foreach ( $combat_bar_array as $k => $val ) {
                echo '<option value="' . $k . '"';
                if ( getFlagValue( $char_obj, $v ) == $k ) { echo ' selected'; }
                echo '>' . $val[ 'n' ] . '</option>';
            }
            echo '</select></td></tr>';

        }

        echo '</table></center>';
        echo '<input type="submit" value="Submit Changes" />';
        echo '<input type="hidden" name="i" value="' . $i . '" />';
        echo '<input type="hidden" name="a" value="cb" />';
        echo '</form>';

    }

?>

<p><a href="account.php">Return to Account Management</a></p>

<?

} else {

    $refer_artifact = getArtifact( 864 );

?>

<p>Here you can oversee aspects of your account information, like changing
your email address, modifying your password, and changing user interface
options.</p>

<h3>Account Information</h3>

<p><a href="account.php?a=<?= account_password ?>">Change your password</a></p>

<h3>User Interface</h3>

<p><a href="account.php?a=<?= account_combatbar ?>">Change combat bar
values</a></p>

<p><a href="account.php?a=<?= account_battlecries ?>">Change battle
cries</a></p>

<?

    if ( ! getFlagBit( $char_obj, sg_flag_account_bit_options, 0 ) ) {
        echo '<p><a href="account.php?a=' . account_disabletips .
             '">Disable tips on login</a> (currently: showing tips)</p>';
    } else {
        echo '<p><a href="account.php?a=' . account_disabletips .
             '">Enable tips on login</a> (currently: hiding tips)</p>';
    }

    if ( ! getFlagBit( $char_obj, sg_flag_account_bit_options, 1 ) ) {
        echo '<p><a href="account.php?a=' . account_disablechoicekeypress .
             '">Disable choice adventure keypresses</a> (currently: ' .
             'keystrokes detected)</p>';
    } else {
        echo '<p><a href="account.php?a=' . account_disablechoicekeypress .
             '">Enable choice adventure keypresses</a> (currently: ' .
             'keystrokes ignored)</p>';
    }

?>

<form method="get">
<p>Toggle quick-navigation bar link:
<select name="i">
<option value="0">Character Profile</option>
<option value="1">View your Artifacts</option>
<option value="2">Cast a Spell</option>
<option value="24">View Your Allies</option>
<option value="3">Mailbox</option>
<option value="4">Quest Log</option>
<option value="5">Cook Something</option>
<option value="6">Craft Something</option>
<option value="7">Capital City</option>
<option value="8">Infirmary (Full Heal)</option>
<option value="22">Infirmary (Full Mana Restore)</option>
<option value="23">Infirmary (Self Bandage)</option>
<option value="9">Hall of Records</option>
<option value="10">Auction House</option>
<option value="11">Casino</option>
<? // <option value="12">PVP</option> ?>
<option value="13">Guild</option>
<option value="14">Sell Something</option>
<option value="15">Regional Map</option>
<option value="16">Who's Online</option>
<option value="17">Character Search</option>
<option value="18">Bank of Nobility</option>
<option value="19">The Temporal Laboratory</option>
<option value="20">Enchanting</option>
<option value="21">Starfall Bay Auctions</option>
<option value="22">Zones by Level</option>
</select>
<input type="submit" value="Toggle" />
<input type="hidden" name="a" value="<?= account_navbar ?>" />
</form></p>

<h3>Refer New Users</h3>

<p>If you'd like to refer users to Twelve Sands and earn
<?= renderArtifactStr( $refer_artifact ) ?> rewards, you can use the
following link.  Just copy and paste it, and any users who come to the
game through you and donate will result in rewards for you that can be
spent at the <a href="main.php?z=94">Great Treasury</a>.</p>

<p><a href="http://twelvesands.com/?ref=<?= $char_obj->c[ 'id' ]
  ?>">http://twelvesands.com/?ref=<?= $char_obj->c[ 'id' ] ?></a></p>

<h3>Forum Signature</h3>

<p>If you'd like to show off your progress in Twelve Sands with a forum
signature, that would be great!  Just copy the code in the text box below
into the forum of your choice, and we'll do the rest.  The image is
updated periodically as you play, so you won't need to worry about
coming back here to update it from time to time.</p>

<p><img src="http://images.twelvesands.com/sig/<?= $char_obj->c[ 'id' ] ?>.png" width="400" height="100"><br>
<b>Direct link:</b><br>
<input type="text" size="80" value="http://images.twelvesands.com/sig/<?= $char_obj->c[ 'id' ]?>.png"><br>
<b>BBCode:</b><br>
<input type="text" size="80" value="[url=http://www.twelvesands.com/?ref=<?= $char_obj->c[ 'id' ] ?>][img]http://images.twelvesands.com/sig/<?= $char_obj->c[ 'id' ]?>.png[/img][/url]"><br>
<b>HTML:</b><br>
<input type="text" size="80" value="&lt;a href=&quot;http://www.twelvesands.com/?ref=<?= $char_obj->c[ 'id' ] ?>&quot; target=&quot;_blank&quot;&gt;&lt;img src=&quot;http://images.twelvesands.com/sig/<?= $char_obj->c[ 'id' ] ?>.png&quot; width=&quot;400&quot; height=&quot;100&quot; title=&quot;Twelve Sands&quot; border=&quot;0&quot;&gt;&lt;/a&gt;"</p>

<?

}

require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>