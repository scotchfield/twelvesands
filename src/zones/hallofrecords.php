<p class="zone_title"><?= $zone['name']; ?></p>
<p class="zone_description"><?= $zone['description']; ?></p>

<?

require_once sg_base_path . 'include/auctions.php';

$t = getGetStr('t', '0');
$l = getGetInt('l', 0);

if ('1' == $t) { // artifact leaderboards

  $valid_list = getAuctionBidArtifacts();
  $valid_list[141] = True;
  $valid_list[142] = True;
  unset($valid_list[63]);

  if (!array_key_exists($l, $valid_list)) {
    echo '<p>That artifact isn\'t being tracked.</p>';
  } elseif (($l > 0) && ($l <= 2000)) {
    $filename = '/home/swrittenb/ts_util/leaderboards/' . $l . '.inc';
    if (file_exists($filename)) {
      $artifact = getArtifact($l);
      echo '<p>Characters who have the largest ';
      echo renderArtifactStr($artifact) . ' collection:</p>';

      echo '<center>';
      include $filename;
      echo '</center>';
    } else {
      echo '<p>That artifact isn\'t being tracked.</p>';
    }
  } else {
    echo '<p>That artifact isn\'t being tracked.</p>';
  }

} elseif ('2' == $t) { // stat leaderboards

  if (($l >= 0) && ($l <= 2000)) {
    $filename = '/home/swrittenb/ts_util/leaderboards/s_' . $l . '.inc';
    if (file_exists($filename)) {
      echo '<center>';
      include $filename;
      echo '</center>';
    } else {
      echo '<p>That isn\'t being tracked.</p>';
    }
  } else {
    echo '<p>That isn\'t being tracked.</p>';
  }

} elseif ('3' == $t) { // flag leaderboards

  $filename = '/home/swrittenb/ts_util/leaderboards/f_' . $l . '.inc';
  if (file_exists($filename)) {
    if ($l == 52) {
      echo '<p><b>Most Training Fields Progress</b></p>';
    } elseif ($l == 37) {
      echo '<p><b>Highest Melee Damage</b></p>';
    } elseif ($l == 38) {
      echo '<p><b>Highest Spell Damage</b></p>';
    }
    echo '<center>';
    include $filename;
    echo '</center>';
  } else {
    echo '<p>That isn\'t being tracked.</p>';
  }

} elseif ('4' == $t) { // rep leaderboards

  $filename = '/home/swrittenb/ts_util/leaderboards/r_' . $l . '.inc';
  if (file_exists($filename)) {
    echo '<p>Highest reputation with <b>' . getReputationName($l) .
         '</b></p>';
    echo '<center>';
    include $filename;
    echo '</center>';
  } else {
    echo '<p>That isn\'t being tracked.</p>';
  }

}

?>

<p><a href="search.php">Search for a player</a></p>

<hr width="300" />

<p><b>Daily random leaderboards</b></p>

<?
  include '/home/swrittenb/ts_util/leaderboards/daily_artifacts.inc';
?>

<hr width="300" />

<p><b>Stat-based leaderboards</b></p>

<center><table class="leaderboard">
<tr>
  <td width="45%" class="l">
    <a href="main.php?z=13&t=2&l=1">Most Armour</a>
  </td>
  <td width="45%" class="r">
    <a href="main.php?z=13&t=2&l=2">Most Strength</a>
  </td>
</tr>
<tr>
  <td class="l">
    <a href="main.php?z=13&t=2&l=3">Most Dexterity</a>
  </td>
  <td class="r">
    <a href="main.php?z=13&t=2&l=4">Most Intelligence</a>
  </td>
</tr>
<tr>
  <td class="l">
    <a href="main.php?z=13&t=2&l=5">Most Charisma</a>
  </td>
  <td class="r">
    <a href="main.php?z=13&t=2&l=6">Most Constitution</a>
  </td>
</tr>
<tr>
  <td class="l">
    <a href="main.php?z=13&t=2">Most Experienced Adventurers</a></p>
  </td>
  <td class="r">
    <a href="main.php?z=13&t=2&l=11">Most Sandstorm Wisdom Solves</a>
  </td>
</tr>
<tr>
  <td colspan="2">
    <a href="main.php?z=13&t=2&l=16">Best Duel (PVP) Rankings</a></p>
  </td>
</tr>
</table></center>

<hr width="300" />

<p><b>Artifact leaderboards</b></p>

<?

function renderDoubleLeaderboardLink($x, $y) {
  $link_array = array(
    1 => 'z=13&t=1&l=51">Most Bear\'s Blood</a>',
    2 => 'z=13&t=1&l=15">Most Meat Shanks</a>',
    3 => 'z=13&t=1&l=25">Most Miniature Wooden Kobolds</a>',
    4 => 'z=13&t=1&l=1">Most Musty Old Textbooks</a>',
    5 => 'z=13&t=1&l=24">Most Portraits of the King</a>',
    6 => 'z=13&t=1&l=70">Most Sharpened Rusted Daggers</a>',
    7 => 'z=13&t=1&l=10">Most Zombie Teeth</a>',
    8 => 'z=13&t=1&l=131">Most Bloodied Teeth</a>',
    9 => 'z=13&t=1&l=141">Most Casino Card Shark Badges</a>',
    10 => 'z=13&t=1&l=142">Most Casino Good Fate Badges</a>',
  );

  echo '<tr><td width="45%" class="l"><a href="main.php?' . $link_array[$x] .
       '</td><td width="45%" class="r"><a href="main.php?' . $link_array[$y] .
       '</td></tr>';
}

echo '<center><table class="leaderboard">';
renderDoubleLeaderboardLink(1, 2);
renderDoubleLeaderboardLink(3, 4);
renderDoubleLeaderboardLink(5, 6);
renderDoubleLeaderboardLink(7, 8);
renderDoubleLeaderboardLink(9, 10);
echo '</table></center>';

?>

<hr width="300" />

<p><b>Reputation leaderboards</b></p>

<center><table class="leaderboard">
<tr>
  <td width="45%" class="l">
    <a href="main.php?z=13&t=4&l=1"><?= getReputationName(1); ?> Reputation</a>
  </td>
  <td width="45%" class="r">
    <a href="main.php?z=13&t=4&l=2"><?= getReputationName(2); ?> Reputation</a>
  </td>
</tr>
<tr>
  <td width="45%" class="l">
    <a href="main.php?z=13&t=4&l=3"><?= getReputationName(3); ?> Reputation</a>
  </td>
  <td width="45%" class="r">
    <a href="main.php?z=13&t=4&l=4"><?= getReputationName(4); ?> Reputation</a>
  </td>
</tr>
</table></center>

<hr width="300" />

<p><b>Special leaderboards</b></p>

<center><table class="leaderboard">
<tr>
  <td width="45%" class="l">
    <a href="main.php?z=13&t=2&l=12">Dungeon Runs by XP</a>
  </td>
  <td width="45%" class="r">
    <a href="main.php?z=13&t=2&l=13">Dungeon Runs by Fatigue</a>
  </td>
</tr>
<tr>
  <td width="45%" class="l">
    <a href="main.php?z=13&t=2&l=14">Dungeon Runs by Combats</a>
  </td>
  <td width="45%" class="r">
    <a href="main.php?z=13&t=2&l=15">Recent Dungeon Runs</a>
  </td>
</tr>
<tr>
  <td width="45%" class="l">
    <a href="main.php?z=13&t=3&l=37">Highest Melee Damage</a>
  </td>
  <td width="45%" class="r">
    <a href="main.php?z=13&t=3&l=38">Highest Spell Damage</a>
  </td>
</tr>
<tr>
  <td colspan="2">
    <a href="main.php?z=13&t=3&l=52">Most Training Fields Progress</a>
    <font size="-2">(<a href="main.php?z=50">where?</a>)</font>
  </td>
</tr>
</table></center>

<hr width="300" />
