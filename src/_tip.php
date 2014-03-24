<?
echo '<p class="tip">Tip: ';
$tip_id = rand( 1, 9 );
if ( 1 == $tip_id ) {
    echo 'Eating food should only be done when you\'re fatigued!  When ' .
         'you eat food, your fatigue will lower.  However, keep an eye ' .
         'on your fullness from your profile; you can only eat a certain ' .
         'amount each day!  If you don\'t know where to get food, visit ' .
         'Barnabus Bidwell in the Market District, and look at your ' .
         'cooking skill from your profile page.';
} elseif ( 2 == $tip_id ) {
    echo 'If you get low on health, you can always visit the Infirmary ' .
         'in Capital City.  They\'ll heal you right up for a small ' .
         'price!  Keep an eye out for other ways to heal though, and note ' .
         'that you will recover a bit of health after each combat.';
} elseif ( 3 == $tip_id ) {
    echo 'If you find yourself visiting certain locations over and over ' .
         'again, click on the "Manage Account" link at the bottom of ' .
         'the page, and add links to the quick navigation bar.  It might ' .
         'save you some time!';
} elseif ( 4 == $tip_id ) {
    echo 'Did you know that you have access to a combat bar while fighting? ' .
         'Visit the <a href="account.php">Manage Account</a> link at the ' . 
         'bottom of each page, and click on <a href="account?a=cb">Change ' .
         'combat bar values</a>.';
} elseif ( 5 == $tip_id ) {
    echo 'Have you visited the Starfall Bay Auction Company?  You can find ' .
         'rare artifacts from around the world here, and if you\'ve got ' .
         'some spare gold or artifacts laying around, you might be able to ' .
         'trade them in for something new and exciting!  <a href="' .
         'main.php?z=116">Click here to visit</a>!';
} elseif ( 6 == $tip_id ) {
    echo '<a href="main.php?z=39">The Capital City Casino</a> is a great ' .
         'place to gamble some of your gold at the end of the day.  Fancy a ' .
         'chance to win big?  Play the lottery, or try your hand at one of ' .
         'the single player games!';
} elseif ( 7 == $tip_id ) {
    echo 'If you\'re ever unsure of what to do, try <a href="char.php?a=ql">' .
         'visiting your quest log</a>!  Any available quests will show up, ' .
         'and you can check the progress of your existing quests.';
} elseif ( 8 == $tip_id ) {
    echo 'If you\'re up for a challenge, why not try working on some of ' .
         '<a href="char.php?a=ac">your achievements</a>?  There\'s a wide ' .
         'range of goals to complete, and any achievements that you manage ' .
         'to complete will show up immediately on your profile with the ' .
         'date and time that you finished it.';
} elseif ( 9 == $tip_id ) {
    echo 'Runes are the way to gain spellcasting abilities in Twelve Sands. ' .
         'Visit the "<a href="http://www.twelvesands.com/main.php?z=133">' .
         'Runes and Relics</a> storefront to get started!';
}
echo '</p>';
?>