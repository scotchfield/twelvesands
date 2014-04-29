<?

require_once 'include/core.php';

require_once sg_base_path . 'include/bank.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/validate.php'; 

$char_obj = new Char($_SESSION['c']);
forceCombatCheck($char_obj);

$log_obj = new Logger();

$z = getGetInt('z', 0);
$zone = getZone($z);

function canSell($artifact) {
  $can_sell = TRUE;

  if ($artifact['sell_price'] <= 0) { $can_sell = FALSE; }
  if (getBit($artifact['flags'], sg_artifact_flag_nosell)) {
    $can_sell = FALSE;
  }
  if ($artifact['type'] == sg_artifact_quest) {
    $can_sell = FALSE;
  }
  if ($artifact['rarity'] == sg_artifact_rarity_epic) {
    $can_sell = FALSE;
  }

  return $can_sell;
}

$x_obj = array();
if (count($_POST) > 0) {

  $gold = 0;
  $v_type = getPostInt('v', 0);
  $n = getPostInt('n', 0);

  if ($v_type == 1) { $n = 1; }
  elseif ($v_type == 2) { $n = 100000; }
  elseif ($v_type == 3) { $n = abs($n); }
  else { $n = 0; }

  if (isset($_POST['ids'])) {
    $a_obj = getCharArtifactsArray($char_obj->c['id'], $_POST['ids']);
    $artifact_obj = getArtifactArray(array_keys($a_obj));

    $sell_obj = array();

    foreach ($a_obj as $k => $a) {
      foreach ($a as $m => $v) {
        $can_sell = canSell($artifact_obj[$k]);
        if ($v <= 0) { $can_sell = FALSE; }
        if ($v_type == 5) { $n = $v - 1; }
        if ($n <= 0) { $can_sell = FALSE; }

        if ($can_sell) {
          $n_sell = min($v, $n);
          removeArtifact($char_obj, $k, $n_sell, $m);
          $sell_obj[] = $n_sell . ' ' .
              renderArtifactStr($artifact_obj[$k], $n_sell);
          $gold += $artifact_obj[$k]['sell_price'] * $n_sell;
          $log_obj->addLog($char_obj->c, sg_log_sell_item, $k, $n_sell,
                           $artifact_obj[$k]['sell_price'] * $n_sell, 0);
        }
      }
    }
  }

  if (count($sell_obj) > 1) {
    $last = array_pop($sell_obj);
    $last = 'and ' . $last;
    $sell_obj[] = $last;
  }

  if (count($sell_obj) > 0) {
    $char_obj->setGold($char_obj->c['gold'] + $gold);
    $x_obj[] = '<p class="tip">You sell ' . join(', ', $sell_obj) . ' for ' .
        $gold . ' gold!</p>';
  }
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss($char_obj->c); ?>
</head>
<body>

<? renderPopupText(); ?>
<script type="text/javascript" src="include/ts_sellnew.js"></script>

<div class="container">

<?

require '_header.php';

function renderInventoryList($a_obj, $title, $type_obj) {
  echo '<p><span class="section_header">' . $title . '</span></p>';
  echo '<ul class="char_list">';

  foreach($a_obj as $artifact) {
    if (canSell($artifact)) {
      if (array_key_exists($artifact['type'], $type_obj)) {
        echo '<li><input type="checkbox" name="ids[]" value="' .
             $artifact['id'] . ',' . $artifact['m_enc'] . '">' .
             $artifact['quantity'] . 'x&nbsp;' .
             renderArtifactStr($artifact, $artifact['quantity']) . '&nbsp;' .
             '<font size="-2">(' . $artifact['sell_price'] .
             '&nbsp;gold)</font></li>';
      }
    }
  }

  echo '</ul>';
}

foreach ($x_obj as $x) {
  echo $x;
}

?>

<p class="zone_title">Sell some artifacts:</p>

<p><font size="-2">
(<a href="javascript:toggleAllSell(true);">Select all</a>) 
(<a href="javascript:toggleAllSell(false);">Select none</a>) 
</font></p>


<center><form method="post" action="sell.php" id="sell" name="sell">
<table width="100%"><tr><td width="50%" valign="top"><div class="table_stat">

<?

  $a_obj = getCharArtifacts($char_obj->c['id']);

  $char_armour_array = array();
  foreach($a_obj as $artifact) {
    if (in_array($artifact['type'], $armourArray)) {
      $char_armour_array[] = $artifact;
    }
  }
  // sortArmourArray(); // force the preprocessor to pick this method up
  usort($char_armour_array, "sortArmourArray");

  renderInventoryList($a_obj, 'Weapons',
      array(sg_artifact_weapon => True));

  echo '<p><span class="section_header">Armour</span></p>';
  echo '<ul class="char_list">';
  $last_armour_type = 0;
  foreach($char_armour_array as $artifact) {
    if (!canSell($artifact)) { continue; }
    if ($last_armour_type != $artifact['type']) {
      $last_armour_type = $artifact['type'];
      $y = '<li style="padding-top: 10px;"><i>';
      $z = '</i></li>';
      switch ($last_armour_type) {
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
/*    echo '<li>' . $artifact['quantity'] . 'x&nbsp;' .
         renderArtifactStr($artifact, $artifact['quantity']) . '&nbsp;' .
         '<input type="checkbox" name="ids[]" value="' . $artifact['id'] .
         ',' . $artifact['m_enc'] . '"></li>';*/
    echo '<li><input type="checkbox" name="ids[]" value="' .
         $artifact['id'] . ',' . $artifact['m_enc'] . '">' .
         $artifact['quantity'] . 'x&nbsp;' .
         renderArtifactStr($artifact, $artifact['quantity']) . '&nbsp;' .
         '<font size="-2">(' . $artifact['sell_price'] .
         '&nbsp;gold)</font></li>';
  }
  echo '</ul>';

  renderInventoryList($a_obj, 'Mounts',
      array(sg_artifact_mount => True));
  renderInventoryList($a_obj, 'Cards',
      array(sg_artifact_puzzle_1 => True));
  renderInventoryList($a_obj, 'Readable',
      array(sg_artifact_readable => True));
  renderInventoryList($a_obj, 'Quest Related',
      array(sg_artifact_quest => True));

  echo '</div></td><td valign="top"><div class="table_stat">';

  renderInventoryList($a_obj, 'Food',
      array(sg_artifact_edible => True));
  renderInventoryList($a_obj, 'Usable',
      array(sg_artifact_usable => True));
  renderInventoryList($a_obj, 'Other Artifacts', array(
      sg_artifact_none => True, sg_artifact_combat_usable => True,
      sg_artifact_warfare_1 => True, sg_artifact_enchanting => True));

?>

</div></td></tr></table>
<p>
<input type="radio" name="v" value="1" checked="on"> Sell 1<br>
<input type="radio" name="v" value="2"> Sell all<br>
<input type="radio" name="v" value="5"> Sell all except 1<br>
<input type="radio" name="v" value="3"> Sell how many:
<input type="text" name="n" size="4"></p>
<input type="submit" value="Sell">
</form></center>

<?

if ($zone != FALSE) {
  echo '<p><a href="main.php?z=' . $zone['id'] . '">Go back to ' .
       $zone['name'] . '</a></p>';
}

require '_footer.php';
$save = $char_obj->save();
$log_save = $log_obj->save();

?>

</div>
</body>
</html>