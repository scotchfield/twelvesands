<p class="zone_title"><?= $char_obj->c[ 'titled_name' ] ?><br>
<font size="-2">
(<a href="char.php">Character Profile</a>)
(<a href="inventory.php">View your Artifacts</a>)
(<a href="char.php?a=ma">Cast a Spell</a>)
<? if ( sg_allies_enabled ) { echo '(<a href="char.php?a=al">View your Allies</a>) '; } ?>
(<a href="http://profiles.twelvesands.com/?i=<?= $char_obj->c[ 'id' ] ?>" target="_blank">Link to Profile</a>)
<br>
(<a href="mail.php">View your Mailbox</a>)
(<a href="char.php?a=ql">View your Quest Log</a>)
(<a href="char.php?a=t">Change your Title</a>)
(<a href="char.php?a=av">Change your Avatar</a>)
<br>
<?
if ( getFlagValue( $char_obj, sg_flag_enchanting ) > 0 ) {
?>
(<a href="enchant.php">Enchant Something</a>)
<?
}
?>
(<a href="recipe.php?t=1">Cook Something</a>)
(<a href="recipe.php?t=2">Craft Something</a>)
(<a href="outfit.php">View Outfits</a>)
<br>
(<a href="char.php?a=ac">View Achievements</a>)
(<a href="char.php?a=tf">Foe Kill Count</a>)
(<a href="char.php?a=tu">Artifact Use Count</a>)
(<a href="char.php?a=pl">View Land Plots</a>)
<?
if ( $char_obj->c[ 'user_id' ] == 1 ) {
?>
<br>(<a href="char.php?a=admin">Admin Panel</a>)
<?
}
?>
</font></p>
