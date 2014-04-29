<?

require_once sg_base_path . 'include/auctions.php';

$a_action = getGetStr('a', '0');
$start_id = getGetInt('s', 0);

function printAuctions($c_obj, $auctions, $url, $start_pos, $list_type) {
  $c = $c_obj->c;

  $artifact_ids = array();
  foreach ($auctions as $a) {
    $artifact_ids[] = $a['artifact_id'];
  }
  if (count($artifact_ids) > 0) {
    $artifact_array = getArtifactArray($artifact_ids);
  }

  echo '<center><table class="profession"><tr>';
  echo '<th width="80">&nbsp;</th>';
  echo '<th>Artifact</th>';
  echo '<th width="80">Quantity</th>';
  echo '<th width="80">Cost</th>';
  if ($list_type == sg_auction_sell) {
    echo '<th width="120">Seller</th>';
  } elseif ($list_type == sg_auction_request) {
    echo '<th width="120">Bidder</th>';
  }
  echo '</tr>';
  foreach ($auctions as $a) {
    echo '<tr align="center">';

    if ($list_type == sg_auction_sell) {
      if ($a['char_id'] == $c['id']) {
        echo '<td><font size="-2">(<a href="main.php?z=44&a=u&n=' .
             $a['id'] . '">unlist</a>)</font></td>';
      } else {
        echo '<td><font size="-2">(<a href="main.php?z=44&a=b&n=' .
             $a['id'] . '">buy</a>)</font></td>';
      }
    } elseif ($list_type == sg_auction_request) {
      if ($a['char_id'] == $c['id']) {
        echo '<td><font size="-2">(<a href="main.php?z=44&a=u&t=2&n=' .
             $a['id'] . '">unlist</a>)</font></td>';
      } else {
        echo '<td><font size="-2">(<a href="main.php?z=44&a=b&t=2&n=' .
             $a['id'] . '">trade</a>)</font></td>';
      }
    }
    echo '<td>';
    $x = $artifact_array[$a['artifact_id']];
    $x['quantity'] = getArtifactQuantity($c_obj, $a['artifact_id']);
    $x['m_enc'] = $a['m_enc'];
    renderArtifact($x, $a['quantity']);
    echo '</td>';
    echo '<td>' . $a['quantity'] . '</td>';
    echo '<td>' . $a['cost'] . '</td>';
    echo '<td><a href="char.php?i=' . $a['char_id'] . '">' . $a['char_name'] .
         '</a>&nbsp;<font size="-2">(<a href="main.php?z=44&a=sc&n=' .
         urlencode($a['char_name']) . '">sell</a>)&nbsp;(' .
         '<a href="main.php?z=44&a=u&t=2&i=' . $a['char_id'] .
         '">req</a>)</font>';
    echo '</td></tr>';
  }
  echo '<tr><td align="left">';
  if ($start_pos > 0) {
    $prev_start_pos = max(0, $start_pos - 20);
    echo '<font size="-2">&nbsp;&nbsp;<a href="' . $url . $prev_start_pos .
        '">&lt;&lt; prev page</a></font>';
  } else {
    echo '&nbsp;';
  }
  echo '</td><td colspan="3">&nbsp;</td><td align="right">';
  if (count($auctions) == 20) {
    $next_start_pos = $start_pos + 20;
    echo '<font size="-2"><a href="' . $url . $next_start_pos .
        '">next page &gt;&gt;</a>&nbsp;&nbsp;</font>';
  } else {
    echo '&nbsp;';
  }

  echo '</td></tr>';
  echo '</table></center>';
}

?>

<script type="text/javascript" src="include/ts_auction.js"></script>

<p class="zone_title"><?= $zone['name']; ?></p>
<p class="zone_description"><?= $zone['description']; ?></p>

<?

if ('v' == $a_action) {

?>
<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('bd' == $a_action) {

  $bid_artifacts = getAuctionBidArtifacts();
  $bid_count = getAuctionCount($c['id'], sg_auction_request);

  if ($bid_count < 5) {

?>

  <p>Place a bid offer:</p>

  <p><form method="get">
  <input type="hidden" name="a" value="bdq">
  <input type="hidden" name="z" value="<?= $zone['id'] ?>">
  <select name="i">
  <?
    foreach($bid_artifacts as $artifact) {
      if ($artifact['sell_price'] > 0) {
        echo '<option value="' . $artifact['id'] . '">' .
             $artifact['name'] . ' ('
             . $artifact['sell_price'] .
             ' gold minimum)</option>' . "\n";
      }
    }
  ?>
  </select>
  <br>How many would you like to request?
  <input type="text" name="n" size="8" value="<?= $n_action ?>">
  <br>What is the <b>total</b> cost of your bid?
  <input type="text" name="c" size="8" value="<?= $c_action ?>">
  <br>
  <input type="submit" value="Post the bid request!">
  </form></p>

<?
  } else {
?>

  <p>You can only have five bids open at a time!<br>
  If you'd like to bid for some other artifacts, you'll have to revoke
  some of your earlier bids, or wait until somebody fills them for you!</p>

<?
  }
?>

<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>

<?

} elseif ('bdq' == $a_action) {

  $i_action = getGetInt('i', 0);
  $n_action = getGetInt('n', 0);
  $c_action = getGetInt('c', 0);
  $bid_artifacts = getAuctionBidArtifacts();
  $bid_count = getAuctionCount($c['id'], sg_auction_request);

  if ($i_action < 1) {
    echo '<p>I\'m not sure what you\'re trying to do.</p>';
  } elseif ($n_action < 1) {
    echo '<p>I\'m not sure what you\'re trying to do.</p>';
  } elseif ($n_action > 100) {
    echo '<p>You can only make a bid on 100 artifacts of a certain type!</p>';
  } elseif ($c_action < 1) {
    echo '<p>I\'m not sure what you\'re trying to do.</p>';
  } elseif (!array_key_exists($i_action, $bid_artifacts)) {
    echo '<p>You can\'t make a bid on that artifact!</p>';
  } elseif ($c['gold'] < $c_action) {
    echo '<p>You don\'t have that much gold to bid with!</p>';
  } elseif ($bid_count >= 5) {
    echo '<p>You can only have five bids open at a time!</p>';
  } else {

    $a = getArtifact($bid_artifacts[$i_action]['id']);
    $minimum_bid = $a['sell_price'] * $n_action;
    if ($c_action < $minimum_bid) {
      echo '<p>Sorry, the minimum bid for your request is ' . $minimum_bid .
           ' gold!</p>';
    } else {
      $log_obj->addLog($c, sg_log_auction_list, $a['id'],
                       $n_action, $c_action, sg_auction_request);
      addAuction($c['id'], $c['name'],
                 $a['id'], $a['name'], $a['type'],
                 $n_action, 0, $c_action, sg_auction_request);
      $char_obj->setGold($char_obj->c['gold'] - $c_action);
      echo '<p>You post a bid request for ' . $n_action . ' ';
      renderArtifact($a, $n_action);
      echo ' at a request of ' . $c_action . ' gold.</p>';
    }
  }

?>
<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('p' == $a_action) {

  $n_action = getGetInt('n', '1');
  $c_action = getGetInt('c', '0');

  $a_obj = getCharArtifacts($char_obj->c['id']);

?>

  <p>Auction off some artifacts:</p>

  <p><form method="get">
  <input type="hidden" id="artifact_enchant" name="ae" value="0">
  <input type="hidden" name="a" value="q">
  <input type="hidden" name="z" value="<?= $zone['id'] ?>">
  <select name="i">
  <?
    foreach($a_obj as $artifact) {
      if ($artifact['sell_price'] > 0) {
        if (!getBit($artifact['flags'], sg_artifact_flag_notrade)) {
          $m_st = '';
          if ($artifact['m_enc'] > 0) {
            $enc = getEnchant($artifact['m_enc']);
            $m_st = ', ' . getModifierString($enc['m'], $enc['v']);
          }
          echo '<option value="' . $artifact['id'] . '" ' .
               'onclick="setArtifactEnchant(' . $artifact['m_enc'] . ');">' .
               $artifact['name'] . $m_st . ' (' . $artifact['quantity'] .
               ' owned) (' . $artifact['sell_price'] .
               ' gold minimum)</option>' . "\n";
        }
      }
    }
  ?>
  </select>
  <br>How many would you like to post?
  <input type="text" name="n" size="8" value="<?= $n_action ?>">
  <br>What is the <b>total</b> cost of the auction?
  <input type="text" name="c" size="8" value="<?= $c_action ?>">
  <br>
  <input type="submit" value="Post the auction!">
  </form></p>

<p>Please note: The total cost of each artifact in your auction must be
at least the cost of selling the artifact in a store.  This is
given beside the artifact in the list above.</p>

<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('q' == $a_action) {

$i_action = getGetInt('i', 0);
$n_action = getGetInt('n', 0);
$c_action = getGetInt('c', 0);
$m_action = getGetInt('ae', 0);

if ($i_action < 1) {
  echo '<p>I\'m not sure what you\'re trying to do.</p>';
} elseif ($n_action < 1) {
  echo '<p>I\'m not sure what you\'re trying to do.</p>';
} elseif ($c_action < 1) {
  echo '<p>I\'m not sure what you\'re trying to do.</p>';
} else {
  $a = hasArtifact($char_obj, $i_action, $m_action);
  if (FALSE == $a) {
    echo '<p>You don\'t have that artifact, and therefore can\'t sell it.</p>';
  } elseif ($a['quantity'] < $n_action) {
    echo '<p>You don\'t have that many of the artifact!</p>';
  } elseif ($a['sell_price'] * $n_action > $c_action) {
    echo '<p>In order to sell ' . $n_action . ' ' . $a['plural_name'] . ', ' .
         'you must list them for at least ' .
         $a['sell_price'] * $n_action . ' gold!</p>';
    echo '<p><a href="main.php?z=' . $zone['id'] . '&a=q&n=' . $n_action .
         '&c=' . $a['sell_price'] * $n_action .
         '&i=' . $i_action . '">Post with these settings</a></p>';
  } elseif ($a['reputation_required'] > 0) {
    echo '<p>You can\'t sell an artifact that you\'ve earned through your ' .
         'reputation with another faction!  What would they say?  The ' .
         'horror!  The shame!</p>';
  } elseif ($a['sell_price'] == 0) {
    echo '<p>You can\'t sell that!</p>';
  } elseif (getBit($a['flags'], sg_artifact_flag_notrade)) {
    echo '<p>You can\'t sell that!</p>';
  } else {
    $log_obj->addLog($c, sg_log_auction_list, $a['id'],
                     $n_action, $c_action, sg_auction_sell);
    addAuction($c['id'], $c['name'],
               $a['id'], $a['name'], $a['type'],
               $n_action, $m_action, $c_action, sg_auction_sell);
    removeArtifact($char_obj, $a['id'], $n_action, $m_action);
    echo '<p>You post ' . $n_action . ' ';
    renderArtifact($a, $n_action);
    echo ' up for auction at a total cost of ' . $c_action . ' gold.</p>';
  }
}

?>
<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('sa' == $a_action) {

  $n_action = getGetStr('n', '0');

  if ('0' == $n_action) {

?>

  <p>Search by Artifact Name:</p>

  <p><form method="get">
  <input type="hidden" name="a" value="sa">
  <input type="hidden" name="z" value="<?= $zone['id'] ?>">
  Which artifact are you searching for?
  <input type="text" name="n" />
  <input type="submit" value="Search!">
  </form></p>

<?

  } else {

    $auctions = getAuctions(NULL, NULL, NULL, $n_action,
                            NULL, $start_id, 20, sg_auctionsort_time,
                            sg_auction_sell);
    $url = 'main.php?z=' . $zone['id'] . '&a=sa&n=' . $n_action . '&s=';
    printAuctions($char_obj, $auctions, $url, $start_id, sg_auction_sell);

  }

?>

<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('st' == $a_action) {

  $n_action = getGetInt('n', 0);

  if (0 == $n_action) {

?>

  <p>Search by Artifact Type:</p>

  <p><form method="get">
  <input type="hidden" name="a" value="st">
  <input type="hidden" name="z" value="<?= $zone['id'] ?>">
  Which artifact type are you searching for?<br>
  <select name="n">
  <option value="<?= sg_artifact_weapon ?>">Weapon</option>
  <option value="<?= sg_artifact_usable ?>">Usable</option>
  <option value="<?= sg_artifact_edible ?>">Edible</option>
  <option value="<?= sg_artifact_readable ?>">Readable</option>
  <option value="<?= sg_artifact_armour_belt ?>">Armour (Belt)</option>
  <option value="<?= sg_artifact_armour_boots ?>">Armour (Boots)</option>
  <option value="<?= sg_artifact_armour_chest ?>">Armour (Chest)</option>
  <option value="<?= sg_artifact_armour_hands ?>">Armour (Hands)</option>
  <option value="<?= sg_artifact_armour_head ?>">Armour (Head)</option>
  <option value="<?= sg_artifact_armour_legs ?>">Armour (Legs)</option>
  <option value="<?= sg_artifact_armour_neck ?>">Armour (Neck)</option>
  <option value="<?= sg_artifact_armour_ring ?>">Armour (Ring)</option>
  <option value="<?= sg_artifact_armour_trinket ?>">Armour (Trinket)</option>
  <option value="<?= sg_artifact_armour_wrists ?>">Armour (Wrists)</option>
  </select>
  <input type="submit" value="Search!">
  </form></p>

<?

  } else {

    $auctions = getAuctions(NULL, NULL, NULL, NULL, $n_action,
                            $start_id, 20, sg_auctionsort_time,
                            sg_auction_sell);
    $url = 'main.php?z=' . $zone['id'] . '&a=st&n=' . $n_action . '&s=';
    printAuctions($char_obj, $auctions, $url, $start_id, sg_auction_sell);

  }

?>

<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('sc' == $a_action) {

  $n_action = getGetStr('n', '0');

  if ('0' == $n_action) {

?>

  <p>Search by Character:</p>

  <p><form method="get">
  <input type="hidden" name="a" value="sc">
  <input type="hidden" name="z" value="<?= $zone['id'] ?>">
  Which character are you searching for?
  <input type="text" name="n" />
  <input type="submit" value="Search!">
  </form></p>

<?

  } else {

    $auctions = getAuctions(NULL, $n_action, NULL, NULL,
                            NULL, $start_id, 20, sg_auctionsort_time,
                            sg_auction_sell);
    $url = 'main.php?z=' . $zone['id'] . '&a=sc&n=' . $n_action . '&s=';
    printAuctions($char_obj, $auctions, $url, $start_id, sg_auction_sell);

  }

?>
<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('u' == $a_action) {

  $i_action = getGetInt('i', $c['id']);
  $n_action = getGetInt('n', 0);
  $t_action = getGetInt('t', sg_auction_sell);

  if (($t_action != sg_auction_sell) && ($t_action != sg_auction_request)) {
    $t_action = sg_auction_sell;
  }

  if (0 == $n_action) {

    $auctions = getAuctions($i_action, NULL, NULL, NULL,
                            NULL, $start_id, 20, sg_auctionsort_time,
                            $t_action);
    $url = 'main.php?z=' . $zone['id'] . '&a=u&s=';
    printAuctions($char_obj, $auctions, $url, $start_id, $t_action);

  } else {

    if ($n_action > 0) {
      $auction = getAuction($n_action, $t_action);
      if (FALSE == $auction) {
        echo '<p>This auction doesn\'t exist!</p>';
      } elseif ($auction['char_id'] != $c['id']) {
        echo '<p>This isn\'t your auction!</p>';
      } else {
        deleteAuction($n_action);
        $log_obj->addLog($c, sg_log_auction_revoke,
            $auction['artifact_id'], $auction['quantity'],
            $auction['cost'], $t_action);
        if ($t_action == sg_auction_sell) {
          $artifact = getArtifact($auction['artifact_id'], $auction['m_enc']);
          awardArtifact($char_obj, $artifact, $auction['quantity'],
                        $auction['m_enc']);
        } elseif ($t_action == sg_auction_request) {
          awardArtifact($char_obj, 0, $auction['cost']);
        }
      }
    }
  }

?>
<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('r' == $a_action) { 

  $auctions = getAuctions(NULL, NULL, NULL, NULL,
                          NULL, $start_id, 20, sg_auctionsort_time,
                          sg_auction_sell);
  $url = 'main.php?z=' . $zone['id'] . '&a=r&s=';
  printAuctions($char_obj, $auctions, $url, $start_id, sg_auction_sell);

?>
<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('rb' == $a_action) {

  $auctions = getAuctions(NULL, NULL, NULL, NULL,
                          NULL, $start_id, 20, sg_auctionsort_time,
                          sg_auction_request);
  $url = 'main.php?z=' . $zone['id'] . '&a=rb&s=';
  printAuctions($char_obj, $auctions, $url, $start_id, sg_auction_request);

?>
<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('b' == $a_action) {

  $n_action = getGetInt('n', 0);
  $y_action = getGetInt('y', 0);
  $t_action = getGetInt('t', sg_auction_sell);

  if (($t_action != sg_auction_sell) && ($t_action != sg_auction_request)) {
    $t_action = sg_auction_sell;
  }

  if ($n_action > 0) {

    $auction = getAuction($n_action, $t_action);
    if (FALSE == $auction) {
      echo '<p>This auction doesn\'t exist!</p>';
    } elseif ($auction['char_id'] == $c['id']) {
      echo '<p>This is your auction!</p>';
    } elseif (($t_action == sg_auction_sell) &&
              ($auction['cost'] > $c['gold'])) {
      echo '<p>You can\'t afford it!</p>';
    } elseif (($t_action == sg_auction_request) &&
              !(getArtifactQuantity($char_obj, $auction['artifact_id']) >=
                     $auction['quantity'])) {
      echo '<p>You don\'t have enough artifacts to trade!</p>';
    } elseif ('0' == $y_action) {
      $artifact = getArtifact($auction['artifact_id'], $auction['m_enc']);
      $artifact_str = renderArtifactStr(
          $artifact, $auction['quantity'], $auction['m_enc']);
      if ($t_action == sg_auction_sell) {
        echo '<p>Are you sure you want to purchase ' . $auction['quantity'] .
             ' ' . $artifact_str . ' for ' . $auction['cost'] .
             ' gold from ' . $auction['char_name'] . '?  ' .
             '<br>This can <b>not</b> be undone!</p>';
      } elseif ($t_action == sg_auction_request) {
        echo '<p>Are you sure you want to offer ' . $auction['quantity'] .
             ' ' . $artifact_str . ' in return for ' .
             $auction['cost'] . ' gold from ' . $auction['char_name'] . '?  ' .
             '<br>This can <b>not</b> be undone!</p>';
      }
      echo '<p><b><a href="main.php?z=44&a=b&y=1&n=' . $n_action .
           '&t=' . $t_action . '">Complete the purchase!</a></b></p>';
    } else {
      $seller_obj = new Char($auction['char_id']);
      if (FALSE == $seller_obj) {
        echo '<p class="tip">Something looks wrong with that seller! ' .
             'My apologies, but until I can verify their status, I can\'t ' .
             'sell this to you.</p>';
      } elseif ($seller_obj->c['user_id'] == $char_obj->c['user_id']) {
        echo '<p class="tip">You can\'t buy your own auctions!</p>';
      } else {
        $log_obj->addLog($c, sg_log_auction_buy, $auction['artifact_id'],
                         $auction['quantity'], $auction['cost'], $t_action);

        if ($t_action == sg_auction_sell) {
          $time = time();
          sendMail($seller_obj->c['id'], 0, 'Auctioneer Dragert',
                   'Auction Successful',
                   'You sold ' . $auction['quantity'] . ' of your ' .
                       $auction['artifact_name'] . ', here\'s what I owe you!',
                   0, $auction['cost'], 0, $time);
          $char_obj->setGold($c['gold'] - $auction['cost']);
/*          sendMail($char_obj->c['id'], 0, 'Auctioneer Dragert',
                   'Auction Successful',
                   'You purchased ' . $auction['quantity'] . 'x ' .
                       $auction['artifact_name'] . '.  I\'m preparing your ' .
                       'goods now, and they\'ll arrive in your mailbox in ' .
                       'about an hour.',
                   0, 0, 0, $time);*/
          sendMail($char_obj->c['id'], 0, 'Auctioneer Dragert',
                   'Auction Successful',
                   'Here\'s your completed order from the Auction House! ' .
                       'Enjoy!',
                   $auction['artifact_id'], $auction['quantity'],
                   $auction['m_enc'], $time);
          echo '<p><b>Your purchase was successful!  You should see the ' .
               'package arrive in your in-game mail shortly.</b></p>';
          echo awardAchievement($char_obj, 31);
        } elseif ($t_action == sg_auction_request) {
          $time = time();
/*          sendMail($seller_obj->c['id'], 0, 'Auctioneer Kovacs',
                   'Auction Successful',
                   'Your ' . $auction['artifact_name'] . ' bid was ' .
                       'successful!  I\'m preparing your goods now, and ' .
                       'they\'ll arrive in your mailbox in about an hour.',
                   0, 0, 0, $time);*/
          sendMail($seller_obj->c['id'], 0, 'Auctioneer Kovacs',
                   'Auction Successful',
                   'Your ' . $auction['artifact_name'] . ' bid was ' .
                       'successful, so here\'s what I owe you!',
                   $auction['artifact_id'], $auction['quantity'],
                   $auction['m_enc'], $time);
          removeArtifact($char_obj,
                         $auction['artifact_id'], $auction['quantity']);
          awardArtifact($char_obj, 0, $auction['cost']);
        }

        deleteAuction($n_action);
      }
    }
  }

?>
<p><a href="main.php?z=<?= $zone['id'] ?>">Go back to the Auction House</a></p>
<?

} elseif ('0' == $a_action) {

?>

<p>
<h3><a href="main.php?z=<?= $zone['id'] ?>&a=r">View auction listings</a><br>
<a href="main.php?z=<?= $zone['id'] ?>&a=rb">View auction requests</a></h3>
</p>

<p>
<h3><a href="main.php?z=<?= $zone['id'] ?>&a=p">Post an auction</a><br>
<a href="main.php?z=<?= $zone['id'] ?>&a=bd">Post a request</a></h3>
</p>

<p>
<a href="main.php?z=<?= $zone['id'] ?>&a=u">View your auctions</a><br>
<a href="main.php?z=<?= $zone['id'] ?>&a=u&t=2">View your requests</a>
</p>

<p>
<a href="main.php?z=<?= $zone['id'] ?>&a=sa">Search by artifact name</a><br>
<a href="main.php?z=<?= $zone['id'] ?>&a=st">Search by artifact type</a><br>
<a href="main.php?z=<?= $zone['id'] ?>&a=sc">Search by character</a>
</p>

<?

}

?>
