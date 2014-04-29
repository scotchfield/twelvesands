<?

function getOpenTrades() {
  $time = time();
  $query = "SELECT * FROM `trade_auctions`
      WHERE (expires - expires_delta) > $time AND dev IN (0," . sg_debug . ")
      ORDER BY expires ASC";
  $results = sqlQuery($query);
  return getResourceAssocById($results);
}

function getClosedTrades() {
  $time = time();
  $oldest_time = $time - (60 * 60 * 24 * 7);
  $query = "SELECT * FROM `trade_auctions`
      WHERE completed = 1 AND expires > $oldest_time
          AND dev IN (0," . sg_debug . ")
      ORDER BY expires ASC";
  $results = sqlQuery($query);
  return getResourceAssocById($results);
}

function updateOpenTrade($auction_id, $char_id, $char_name,
                         $quantity, $reserve) {
  $auction_id = intval($auction_id);
  $char_id = intval($char_id);
  $char_name = fixStr($char_name);
  $quantity = intval($quantity);
  $reserve = intval($reserve);

  $query = "UPDATE `trade_auctions` SET bid_char_id=$char_id,
      bid_char_name='$char_name', bid_quantity=$quantity,
      bid_reserve=$reserve, bid_count=bid_count+1
      WHERE id=$auction_id";
  sqlQuery($query);
}




function getCustomState($c_obj, $log_obj) {
  $ret_obj = array();
  $ret_obj['actions'] = array();
  $ret_obj['text'] = array();

  $action = getGetStr('a', '');
  $a_id = getGetInt('i', 0);
  $trades = getOpenTrades();

  if ('v' == $action) {

    $trades = getClosedTrades();
    $ret_obj['text'][] = '<p><b>Recently completed trades:</b></p>';
    $ret_obj['text'][] = '<center><table width="100%" class="plain" ' .
        'cellspacing="0" cellpadding="3"><tr>' .
        '<th>Artifact</th><th>Bid Type</th><th>Current Bid</th>' .
        '<th># bids</th></tr>';

    foreach ($trades as $x) {
      $bg_color = 'F0F0F0';
      if ($tr == 1) { $bg_color = 'FFFFFF'; }
      $tr = ($tr + 1) % 2;

      $artifact = getArtifact($x['artifact_id']);
      if ($x['bid_artifact'] == 0) {
        $bid_st = 'Gold';
      } else {
        $bid_artifact = getArtifact($x['bid_artifact']);
        $bid_st = renderArtifactStr($bid_artifact);
      }

      $bid_quantity = $x['bid_quantity'];
      if ($x['bid_quantity'] == 0) {
        $bid_quantity = 'None';
      }

      $ret_obj['text'][] = '<tr align="center" bgcolor="#' .
          $bg_color . '">' .
          '<td>' . $x['quantity'] . 'x ' .
          renderArtifactStr($artifact) . '</td>' .
          '<td>' . $bid_st . '</td>' .
          '<td>' . $bid_quantity;
      if ($x['bid_char_id'] > 0) {
        $ret_obj['text'][] = ' (by <a href="char.php?i=' .
          $x['bid_char_id'] . '">' . $x['bid_char_name'] . '</a>)';
      }
      $ret_obj['text'][] = '</td><td>' . $x['bid_count'] . '</td></tr>';
    }
    $ret_obj['text'][] = '</table>';
    $ret_obj['text'][] = '<p><a href="main.php?z=116">Back to the Starfall ' .
        'Auction Courts</a></p>';

  } elseif ('r' == $action) {

    if (!isset($trades[$a_id])) {
      $ret_obj['text'][] = '<p class="tip">That auction doesn\'t exist!</p>';
    } elseif ($trades[$a_id]['bid_char_id'] != $c_obj->c['id']) {
      $ret_obj['text'][] = '<p class="tip">That\'s not your auction!</p>';
    } else {
      $x = $trades[$a_id];
      if ($x['bid_artifact'] == 0) {
        $bid_st = 'Gold';
        $bid_max = $c_obj->c['gold'];
      } else {
        $bid_artifact = getArtifact($x['bid_artifact']);
        $bid_st = renderArtifactStr($bid_artifact, $quantity=2);
        $bid_max = getCharArtifactQuantity($c_obj, $x['bid_artifact']);
      }

      $artifact = getArtifact($x['artifact_id']);

      $ret_obj['text'][] = '<p class="tip">' .
          'Auction: ' . $x['quantity'] . 'x ' .
          renderArtifactStr($artifact) .
          '<br>Current bid: ' . $x['bid_quantity'] . 'x ' . $bid_st .
          '<br>Your existing reserve price: ' . $x['bid_reserve'] .
          '<br>Your maximum reserve increase: ' . $bid_max . '</p>';

      if ($bid_max < 1) {
        $ret_obj['text'][] = '<p class="tip">You don\'t have enough to ' .
            'place a higher reserve on this auction!</p>';
      } else {
        $bid_amount = getPostInt('bid', 0);

        if ($bid_amount == 0) {
          $ret_obj['text'][] = '<p>If you\'re interested in shutting out ' .
              'other bidders from this auction, you can place more of your ' .
              'artifacts on reserve.  If anyone else attempts to bid, our ' .
              'system will use your reserve to automatically rebid for you. ' .
              'Once you leave artifacts on reserve though, they remain with ' .
              'us until the auction is over!</p>';
          $ret_obj['text'][] = '<form method="post" action="main.php' .
              '?z=116&a=r&i=' . $x['id'] . '"><p>';
          $ret_obj['text'][] = 'What is the amount of ' . $bid_st .
              ' you would like to increase your reserve by? ';
          $ret_obj['text'][] = '<input type="text" name="bid" value="' .
              $bid_min . '"><br>' .
              '<input type="submit" value="Place a bid!"></p></form>';
          $ret_obj['text'][] = '<p>Please note, your full deposit will be ' .
              'held as an initial payment, and the next minimum bid will be ' .
              'used at any time someone attempts to outbid you.  If you ' .
              'win an auction, and some of your artifacts remain unused, ' .
              'the excess will be returned to you.</p>';
        } elseif ($bid_amount > $bid_max) {
          $ret_obj['text'][] = '<p class="tip">You don\'t have that much to ' .
              'bid!<br>Your current maximum bid is ' . $bid_max . '.</p>';
        } elseif ($bid_amount > 0) {
          updateOpenTrade($x['id'], $x['bid_char_id'], $x['bid_char_name'],
              $x['bid_quantity'], $x['bid_reserve'] + $bid_amount);
          if ($x['bid_artifact'] > 0) {
            removeArtifact($c_obj, $x['bid_artifact'], $bid_amount);
          } else {
            $c_obj->setGold($c_obj->c['gold'] - $bid_amount);
          }
          $ret_obj['text'][] = '<p class="tip">Your reserve addition was ' .
              'accepted! Thanks for giving us a hand, and good luck!</p>';
          $log_obj->addLog($c_obj->c, sg_log_trading_reserve, $x['id'],
              $x['bid_quantity'], $x['bid_reserve'], $bid_amount);
        }
      }

    }

    $ret_obj['text'][] = '<p><a href="main.php?z=116">Back to the Starfall ' .
        'Auction Courts</a></p>';

  } elseif ('b' == $action) {
    if (!isset($trades[$a_id])) {
      $ret_obj['text'][] = '<p class="tip">That auction doesn\'t exist!</p>';
    } else {
      $x = $trades[$a_id];

      $bid_increment_min = max(1, floor($x['bid_quantity'] * 0.05));
      $bid_min = $x['bid_quantity'] + $bid_increment_min;
      $reserve_bid_increment_min = max(1, floor($x['bid_reserve'] * 0.05));
      $reserve_bid_min = $x['bid_reserve'] + $reserve_bid_increment_min;

      if ($x['bid_artifact'] == 0) {
        $bid_st = 'Gold';
        $bid_max = $c_obj->c['gold'];
      } else {
        $bid_artifact = getArtifact($x['bid_artifact']);
        $bid_st = renderArtifactStr($bid_artifact, $quantity=2);
        $bid_max = getCharArtifactQuantity($c_obj, $x['bid_artifact']);
      }

      $artifact = getArtifact($x['artifact_id']);

      $ret_obj['text'][] = '<p class="tip">' .
          'Auction: ' . $x['quantity'] . 'x ' .
          renderArtifactStr($artifact) .
          '<br>Current bid: ' . $x['bid_quantity'] . 'x ' . $bid_st .
          '<br>Your minimum bid: ' . $bid_min .
          '<br>Your maximum bid: ' . $bid_max . '</p>';

      if ($bid_max < $bid_min) {
        $ret_obj['text'][] = '<p class="tip">You don\'t have enough to ' .
            'place a higher bid on this auction!</p>';
      } else {
        $bid_amount = getPostInt('bid', 0);

        if ($bid_amount == 0) {
          $ret_obj['text'][] = '<form method="post" action="main.php' .
              '?z=116&a=b&i=' . $x['id'] . '"><p>';
          $ret_obj['text'][] = 'What is the maximum amount of ' . $bid_st .
              ' you would like to bid? ';
          $ret_obj['text'][] = '<input type="text" name="bid" value="' .
              $bid_min . '"><br>' .
              '<input type="submit" value="Place a bid!"></p></form>';
          $ret_obj['text'][] = '<p>Please note, your full deposit will be ' .
              'held as an initial payment, and the next minimum bid will be ' .
              'used at any time someone attempts to outbid you.  If you ' .
              'win an auction, and some of your artifacts remain unused, ' .
              'the excess will be returned to you.</p>';
        } elseif ($bid_amount > $bid_max) {
          $ret_obj['text'][] = '<p class="tip">You don\'t have that much to ' .
              'bid!<br>Your current maximum bid is ' . $bid_max . '.</p>';
        } elseif ($bid_amount < $bid_min) {
          $ret_obj['text'][] = '<p class="tip">You need to bid more than ' .
              'the existing bid!<br>The minimum acceptable bid is ' .
              'currently ' . $bid_min . '.</p>';
        } elseif (($x['bid_reserve'] > 0) &&
                  ($bid_amount < $reserve_bid_min)) {
          updateOpenTrade($x['id'], $x['bid_char_id'], $x['bid_char_name'],
              min($bid_amount, $x['bid_reserve']), $x['bid_reserve']);
          $ret_obj['text'][] = '<p class="tip">You attempt to place a bid, ' .
              'but your bid falls below the reserve that the previous ' .
              'bidder left.  The trade has been updated, and you can ' .
              'make another attempt to win the auction if you\'re ' .
              'interested.</p>';
/*          sendMail($x['bid_char_id'], 1, 'Starfall Auctioneer',
              'Auction Price Updated',
              'Someone has attempted to outbid you on one of our auctions, ' .
              'but your reserve was high enough to retain the top bid.',
              0, 0, 0, time());*/
          $log_obj->addLog($c_obj->c, sg_log_trading_insufficient, $x['id'],
              $bid_amount, 0, 0);
        } else {
          if ($x['bid_char_id'] > 0) {
            sendMail($x['bid_char_id'], 0, 'Starfall Auctioneer',
                'Outbid on Auction',
                'Sorry, you\'ve been outbid on one of our auctions!',
                $x['bid_artifact'],
                max($x['bid_quantity'], $x['bid_reserve']),
                0, time());
          }
          updateOpenTrade($x['id'], $c_obj->c['id'], $c_obj->c['name'],
              max($bid_min, $reserve_bid_min), $bid_amount);
          if ($x['bid_artifact'] > 0) {
            removeArtifact($c_obj, $x['bid_artifact'], $bid_amount);
          } else {
            $c_obj->setGold($c_obj->c['gold'] - $bid_amount);
          }
          $ret_obj['text'][] = '<p class="tip">Your bid was accepted! ' .
              'Thanks for giving us a hand, and good luck!</p>';
          $log_obj->addLog($c_obj->c, sg_log_trading_bid, $x['id'],
              $bid_min, $bid_amount, 0);
        }

      }

    }

    $ret_obj['text'][] = '<p><a href="main.php?z=116">Back to the Starfall ' .
        'Auction Courts</a></p>';

  } else {
    if (count($trades) > 0) {
      $bg = 0;
      $time = time();
      $ret_obj['text'][] = '<p><b>Current available auctions:</b></p>';
      $ret_obj['text'][] = '<center><table width="100%" class="plain" ' .
          'cellspacing="0" cellpadding="3"><tr>' .
          '<th>Artifact</th><th>Bid Type</th><th>Current Bid</th>' .
          '<th># bids</th><th>Time Left</th></tr>';

      foreach ($trades as $x) {
        $bg_color = 'F0F0F0';
        if ($tr == 1) { $bg_color = 'FFFFFF'; }
        $tr = ($tr + 1) % 2;

        $artifact = getArtifact($x['artifact_id']);
        if ($x['bid_artifact'] == 0) {
          $bid_st = 'Gold';
        } else {
          $bid_artifact = getArtifact($x['bid_artifact']);
          $bid_st = renderArtifactStr($bid_artifact);
        }
        $time_left = $x['expires'] - $time;
        $time_st = 'several days';
        if ($time_left < 3600) {
          $time_st = '&lt; an hour';
        } elseif ($time_left < 21600) {
          $time_st = '&lt; six hours';
        } elseif ($time_left < 86400) {
          $time_st = '&lt; one day';
        }

        $bid_quantity = $x['bid_quantity'];
        if ($x['bid_quantity'] == 0) {
          $bid_quantity = 'None';
        }

        $ret_obj['text'][] = '<tr align="center" bgcolor="#' .
            $bg_color . '">' .
            '<td>' . $x['quantity'] . 'x ' .
            renderArtifactStr($artifact) . '</td>' .
            '<td>' . $bid_st . '</td>' .
            '<td>' . $bid_quantity;
        if ($x['bid_char_id'] > 0) {
          $ret_obj['text'][] = ' (by <a href="char.php?i=' .
            $x['bid_char_id'] . '">' . $x['bid_char_name'] . '</a>)';
        }
        $ret_obj['text'][] = '</td><td>' . $x['bid_count'] . '</td>' .
            '<td>' . $time_st . '</td></tr>';

        $place_st = '(<a href="main.php?z=116&a=b&i=' . $x['id'] .
            '">place a bid</a>)';
        if ($x['bid_char_id'] == $c_obj->c['id']) {
          $place_st = 'You hold the top bid on this auction, with a ' .
              'reserve price of ' . $x['bid_reserve'] . '.<br>' .
              '(<a href="main.php?z=116&a=r&i=' . $x['id'] .
              '">increase your reserve</a>)';
        }

        $ret_obj['text'][] = '<tr align="center" bgcolor="#' .
            $bg_color . '">' .
            '<td colspan="5">' . $x['text'] . '<br><font size="-2">' .
            $place_st . '</font></td></tr>';


      }

      $ret_obj['text'][] = '</table></center>';

    } else {
      $ret_obj['text'][] = '<p><b>There are no auctions available at the ' .
          'moment!</b></p>';
    }

    $ret_obj['text'][] = '<p><a href="main.php?z=116&a=v">View recently ' .
        'completed trades</a></p>';
  }

  return $ret_obj;
}

function renderCustomState($zone, $c_obj, $state_obj) {
  echo '<p class="zone_title">' . $zone['name'] . '</p>';
  echo '<p class="zone_description">' . $zone['description'] . '</p>';

  foreach ($state_obj['text'] as $x) {
    echo $x;
  }
  echo '<p>' . join('<br>', $state_obj['actions']) . '</p>';
}

?>