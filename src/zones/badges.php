<p class="zone_title"><?= $zone['name']; ?></p>
<p class="zone_description"><?= $zone['description']; ?></p>

<p><b>Badges that you've been awarded:</b></p>

<?

echo '<ul class="char_list">';
foreach ($c['badges'] as $b) {
  echo '<li>' . $b['name'] . '</li>';
}
echo '</ul>';

?>

<p><b>Badges that you're eligible for:</b></p>

<?

$badges = getBadges();
$badge_count = 0;

echo '<ul class="char_list">';
foreach ($badges as $b) {
  if (badgeQualified($char_obj, $b['id'])) {
    $badge_count += 1;
    if ($b['cost'] <= $c['gold']) {
      echo '<li><a href="action.php?z=45&a=bd&i=' . $b['id'] . '">' .
           $b['name'] . '</a> (cost: ' . $b['cost'] . ' gold)</li>';
    } else {
      echo '<li><s>' . $b['name'] . '</s> (cost: ' .
           $b['cost'] . ' gold)</li>';
    }
  }
}
echo '</ul>';

if ($badge_count == 0) {
  echo '<p>Nothing right now, come back later!</p>';
}
?>

