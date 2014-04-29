<?

function addLotteryTickets($char_id, $quantity) {
  $char_id = intval($char_id);
  $quantity = intval($quantity);
  $query = "INSERT INTO `lottery_tickets` (char_id, quantity)
      VALUES ($char_id, $quantity)";
  sqlQuery($query);
}

function getLotteryTickets($char_id) {
  $char_id = intval($char_id);
  $query = "SELECT SUM(quantity) AS c FROM `lottery_tickets`
      WHERE char_id=$char_id";
  $results = sqlQuery($query);
  if (!$results) { return 0; }
  $lottery = $results->fetch_assoc();
  return intval($lottery['c']);
}

function getCustomState($c_obj, $log_obj) {
  $ret_obj = array();
  $ret_obj['text'] = array();

  $action = getGetInt('a', 0);
  $quantity = getPostInt('i', 0);

  if ($quantity > 0) {
    $quantity = min($quantity, floor($c_obj->c['gold'] / 250));
  }

  if ($quantity > 0) {
    $c_obj->setGold($c_obj->c['gold'] - ($quantity * 250));
    addLotteryTickets($c_obj->c['id'], $quantity);
    $log_obj->addLog($c_obj->c, sg_log_lottery_purchase, $quantity, 0, 0, 0);
    $ret_obj['text'][] = '<p class="tip">Tickets purchased: ' . $quantity .
        ' (' . ($quantity * 250) . ' gold)</p>';
  }

  $ticket_count = getLotteryTickets($c_obj->c['id']);

  $ret_obj['text'][] = '<p><b>Welcome to the Capital City Lottery!</b></p>';
  $ret_obj['text'][] = '<p>We run regular draws, every Monday, Wednesday, ' .
      'and Friday, and the more tickets you purchase, the better chance ' .
      'you have to win! Each ticket costs 250 gold pieces, and each ' .
      'ticket is good for a single chance on the next upcoming draw. ' .
      'If you\'re feeling lucky, feel free to test your luck with us!</p>';
  $ret_obj['text'][] = '<p>The house keeps a small percentage of each ' .
      'ticket, in order to help us cover our costs.  Aside from that, we ' .
      'find one winner with every draw, and that winner takes home the ' .
      'entire pot!  Are you ready to win big?</p>';

  include '/home/swrittenb/ts_util/_lottery.inc';

  $ret_obj['text'][] = '<h3>Your current ticket count for the next draw: ' .
      $ticket_count . '</h3>';
  $ret_obj['text'][] = '<form method="post"><p>Would you like to purchase ' .
      'some tickets for the next draw?<br>Purchase how many tickets? ' .
      '<input type="text" size="8" name="i"> ' .
      '<input type="submit" value="Buy tickets!"></p></form>';

  return $ret_obj;
}

function renderCustomState($zone, $c_obj, $state_obj) {
  echo '<p class="zone_title">' . $zone['name'] . '</p>';
  echo '<p class="zone_description">' . $zone['description'] . '</p>';

  foreach ($state_obj['text'] as $x) {
    echo $x;
  }
}

?>