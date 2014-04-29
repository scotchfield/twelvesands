<?

function getCustomZoneState($zone, $c_obj, $log_obj) {
  $c = $c_obj->c;
  $action = getGetStr('a', '0');

  $output_obj = array();

  $output_obj[] = '<p class="zone_title">' . $zone['name'] . '</p>';
  $output_obj[] = '<p class="zone_description">' . $zone['description'] .
      '</p>';

  if (('0' != $action) && (array_key_exists(7, $c['buffs']))) {
    $output_obj[] = '<p>You try to take a seat, but one of the larger ' .
         'and less friendly employees steps in front of you.  Capital ' .
         'City is known to limit the gambling time for citizens due to ' .
         'problems with crime in the past, and you decide to wait until ' .
         'you\'re allowed to return.</p>';
  } elseif ('m' == $action) {
    if ($c['gold'] < 2500) {
      $output_obj[] = '<p>You make it a point to return later when you have ' .
           'enough gold to play.  The signs indicate a minimum payment of ' .
           '2,500 gold, and as you pat your pockets, you realize you still ' .
           'have some money to make up.</p>';
    } else {
      $output_obj[] = '<p>The casino employee beams brightly at you, and ' .
          'with a flourish, throws his hand up in the air.  He grabs one ' .
          'of the pegs along the Great Wheel of Luck, and with another ' .
          'exaggerated smile, spins the wheel!  Your eyes follow the ' .
          'bright colours, trying to predict what fortune will bring you!</p>';
      $newGold = $c['gold'] - 2500;
      $gambleRoll = rand(1, 100);

      if ($gambleRoll > 33) {
        $output_obj[] = '<p><b>The wheel slows, and rolls to a stop on a ' .
             'red-backed ' .
             'panel.  The casino employee frowns, and the small crowd ' .
             'that gathered behind you lets out a collective groan.  ' .
             'Better luck next time, you realize!</b></p>';
        $log_obj->addLog(
            $c, sg_log_fate_wheel_loss, 2500, $gambleRoll, 0, 0);
      } elseif ($gambleRoll > 5) {
        $output_obj[] = '<p><b>The wheel slows, and rolls to a stop on a ' .
             'blue-backed ' .
             'panel.  You hear a set of cheers and congratulatory ' .
             'statements behind and realize that you are a winner!  The ' .
             'casino employee grabs a pouch of gold, and dumps the ' .
             'contents in front of you.  You\'ve doubled up, earning ' .
             '5,000 gold!</b></p>';
        $newGold += 5000;
        $casino_artifact = getArtifact(142);
        $output_obj[] = awardArtifactString($c_obj, $casino_artifact, 1);
        addBuff($c_obj, 8, 659, 0, TRUE);
        $log_obj->addLog(
            $c, sg_log_fate_wheel_win, 5000, $gambleRoll, 0, 0);
      } elseif ($gambleRoll > 1) {
        $output_obj[] = '<p><b>The wheel slows, and rolls to a stop on a ' .
             'green-backed panel.  The crowd that has gathered behind ' .
             'you lets out a great cheer, and several people clap you ' .
             'on the back in a congratulatory manner.  The casino ' .
             'employee grabs a few pouches of gold from behind the ' .
             'great wheel, and dumps them on the table in front of ' .
             'you with a smile.  You\'ve just earned 12,500 gold!</b></p>';
        $newGold += 12500;
        $casino_artifact = getArtifact(142);
        $output_obj[] = awardArtifactString($c_obj, $casino_artifact, 1);
        addBuff($c_obj, 8, 1259, 0, TRUE);
        $log_obj->addLog(
            $c, sg_log_fate_wheel_win, 12500, $gambleRoll, 0, 0);
      } elseif ($gambleRoll == 1) {
        $output_obj[] = '<p><b>The wheel slows, and rolls to a stop on a ' .
             'gold-backed ' .
             'panel.  Even the casino employee himself lets out a loud ' .
             'cheer, and the throngs of people around you begin to jump ' .
             'and cheer excitedly!  You seem stunned for a minute, and ' .
             'look more closely at the panel that fate chose for you.  ' .
             'Gold-backed?  You just won 50,000 gold!!  The casino ' .
             'employee grabs a small chest from behind the great wheel, ' .
             'and unloads a great deal of gold on to the table in front ' .
             'of you.  Unable to suppress it, you let out ' .
             'a great cheer.  Good fortune, and good luck!</b>';
        $newGold += 50000;
        $casino_artifact = getArtifact(142);
        $output_obj[] = awardArtifactString($c_obj, $casino_artifact, 1);
        $sweet_artifact = getArtifact(203);
        $output_obj[] = awardArtifactString($c_obj, $sweet_artifact, 1);
        addBuff($c_obj, 8, 3659, 0, TRUE);
        $log_obj->addLog(
            $c, sg_log_fate_wheel_win, 50000, $gambleRoll, 0, 0);
        $output_obj[] = awardAchievement($c_obj, 22);
      } else {
        $output_obj[] = '<p>Something very strange happened.  Your money ' .
             'winks at you.</p>';
        $newGold += 2500;
      }
      $c_obj->setGold($newGold);
      addBuff($c_obj, 7, 21659, 0, FALSE);
    }

    $output_obj[] = '<p><a href="main.php?z=' . $zone['id'] .
         '">Go back to the Casino</a></p>';

  } elseif ('d' == $action) {

    if ($c['gold'] < 25000) {
      $output_obj[] = '<p>You make it a point to return later when you ' .
          'have enough gold to play. The signs indicate a minimum payment ' .
          'of 25,000 gold, and as you pat your pockets, you realize you ' .
          'still have some money to make up.</p>';
    } else {
      $output_obj[] = '<p>The casino employee beams brightly at you, and ' .
          'with a flourish, ' .
          'picks up the set of five dice.  He throws them into a ' .
          'multi-coloured cup, and shakes them around furiously.  As if ' .
          'acting to remind you of the cost and reward, he nods his head to ' .
          'a sign on the wall behind him.  Each die has six sides, and ' .
          'costs 5,000 gold to play with.  If a die comes up on one of ' .
          'its two blank sides, the gold for that die is lost.  If it ' .
          'lands on the side showing a single star, you win 500 gold for ' .
          'that die.  If it lands ' .
          'on the side with two stars, or the side with five stars, you win ' .
          '1,000 or 2,500 gold, depending on the stars.  Finally, if a die ' .
          'lands on the sun side, you win 20,000 gold for that die!</p>';
      $output_obj[] = '<p><b>The dice fly in to the air, and land on the ' .
          'table with a clatter!</b></p>';
      $newGold = $c['gold'] - 25000;
      $winGold = 0;

      $output_obj[] = '<font color="blue">';
      for ($x = 1; $x <= 5; $x++) {
        $gambleRoll = rand(1, 6);

        if ($gambleRoll <= 2) {
          $output_obj[] = '<p>Die ' . $x . ' lands on an empty face!  ';
          $output_obj[] = '<i>(0 gold)</i></p>';
        } elseif ($gambleRoll == 3) {
          $output_obj[] = '<p>Die ' . $x . ' lands on a single star!  ';
          $output_obj[] = '<i>(500 gold)</i></p>';
          $winGold = $winGold + 500;
        } elseif ($gambleRoll == 4) {
          $output_obj[] = '<p>Die ' . $x . ' lands on a two-starred side!  ';
          $output_obj[] = '<i>(1000 gold)</i></p>';
          $winGold = $winGold + 1000;
        } elseif ($gambleRoll == 5) {
          $output_obj[] = '<p>Die ' . $x . ' lands on a five-starred side!  ';
          $output_obj[] = '<i>(2500 gold)</i></p>';
          $winGold = $winGold + 2500;
        } elseif ($gambleRoll == 6) {
          $output_obj[] = '<p>Die ' . $x . ' lands on the sun side!!  ';
          $output_obj[] = '<b><i>(20000 gold)</i></b></p>';
          $winGold = $winGold + 20000;
        }
      }
      $output_obj[] = '</font>';

      $output_obj[] = '<p><b>After adding up the results from the dice, you ' .
          'realize you have earned a total of ' . $winGold . ' gold!  ';
      $log_obj->addLog($c, sg_log_casino_dice_win, $winGold, 0, 0, 0);
      if ($winGold > 25000) {
        $output_obj[] = 'Congratulations!!</b></p>';
        $casino_artifact = getArtifact(221);
        $output_obj[] = awardArtifactString($c_obj, $casino_artifact, 1);
        addBuff($c_obj, 8, 3059, 0, TRUE);
      } elseif ($winGold > 0) {
        $output_obj[] = 'A good showing, but better luck in the ' .
            'future!</b></p>';
        addBuff($c_obj, 8, 1859, 0, TRUE);
      } else {
        $output_obj[] = 'Better luck next time!</b></p>';
        $casino_artifact = getArtifact(220);
        $output_obj[] = awardArtifactString($c_obj, $casino_artifact, 1);
      }

      if ($winGold == 100000) {
        $output_obj[] = awardAchievement($c_obj, 23);
      }

      $newGold = $newGold + $winGold;
      $c_obj->setGold($newGold);
      addBuff($c_obj, 7, 36059, 0, FALSE);
    }

    $output_obj[] = '<p><a href="main.php?z=' . $zone['id'] .
        '">Go back to the Casino</a></p>';

  } elseif ('c' == $action) {

    if ($c['gold'] < 250) {
      $output_obj[] = '<p>You make it a point to return later when you ' .
          'have enough gold to play. The signs indicate a minimum payment ' .
          'of 250 gold, and as you pat your pockets, you realize you still ' .
          'have some money to make up.</p>';
    } else {

      $card = getGetInt('c', 0);

      if (($card > 0) && ($card < 4)) {

        $newGold = $c['gold'] - 250;
        $gambleRoll = rand(1, 100);

        if ($gambleRoll <= 49) {
          $newGold += 500;
          $casino_artifact = getArtifact(141);
          $output_obj[] = awardArtifactString($c_obj, $casino_artifact, 1);
          addBuff($c_obj, 8, 659, 0, TRUE);
          $log_obj->addLog($c, sg_log_casino_card_win, 500, $gambleRoll, 0, 0);

          if ($card == 1) {
            $output_obj[] = '<p>The casino employee smiles brightly as ' .
                'she turns over a card labelled with a giant white tower.  ' .
                'On the card, the sun shines down over green-tipped hills, ' .
                'and illuminates the structure magnificently.  The woman ' .
                'claps for you, and reaches for a pile of gold!</p>';
          } elseif ($card == 2) {
            $output_obj[] = '<p>The casino employee smiles brightly as she ' .
                'turns over a card labelled with a giant white tower.  On ' .
                'the card, the sun shines down over green-tipped hills, ' .
                'and illuminates the structure magnificently.  The woman ' .
                'claps for you, and reaches for a pile of gold!</p>';
          } elseif ($card == 3) {
            $output_obj[] = '<p>The casino employee smiles brightly as she ' .
                'turns over a card labelled with a giant white tower.  On ' .
                'the card, the sun shines down over green-tipped hills, and ' .
                'illuminates the structure magnificently.  The woman claps ' .
                'for you, and reaches for a pile of gold!</p>';
          }
          $output_obj[] = '<p><b>You just earned 500 gold!</b></p>';
          $output_obj[] = awardAchievement($c_obj, 21);
        } else {
          $log_obj->addLog(
              $c, sg_log_casino_card_loss, 250, $gambleRoll, 0, 0);

          if ($card == 1) {
            $output_obj[] = '<p>The casino employee begins to frown as she ' .
                'turns over a card labelled with a snow-capped mountain.  ' .
                'The card illustrates an ominous looking storm showering ' .
                'the mountaintop with hail and freezing rain.  Dark clouds ' .
                'fill the sky.</p>';
          } elseif ($card == 2) {
            $output_obj[] = '<p>The casino employee begins to frown as she ' .
                'turns over a card labelled with a snow-capped mountain.  ' .
                'The card illustrates an ominous looking storm showering ' .
                'the mountaintop with hail and freezing rain.  Dark clouds ' .
                'fill the sky.</p>';
          } elseif ($card == 3) {
            $output_obj[] = '<p>The casino employee begins to frown as she ' .
                'turns over a card labelled with a snow-capped mountain.  ' .
                'The card illustrates an ominous looking storm showering ' .
                'the mountaintop with hail and freezing rain.  Dark clouds ' .
                'fill the sky.</p>';
          }
          $output_obj[] = '<p><b>Sorry, friend!  Try again soon!</b></p>';
        }

        $c_obj->setGold($newGold);
        addBuff($c_obj, 7, 3659, 0, FALSE);

      } else {
        $output_obj[] = '<p>You take a seat, as a charming and ' .
            'brightly-dressed casino employee seats herself across the ' .
            'table.  She begins to shuffle a small set of three cards, ' .
            'shifting them across the table, and mixing them up so that ' .
            'you are unable to determine one from the other.  Finally, ' .
            'with a beaming smile, she sets out the three cards face-down ' .
            'in front of you.  You scratch your head and consider which ' .
            'card to choose!</p>';
        $output_obj[] = '<hr width="100" /><p><a href="main.php?z=' .
            $zone['id'] . '&a=c&c=1">Choose the first card</a></p>';
        $output_obj[] = '<p><a href="main.php?z=' .
            $zone['id'] . '&a=c&c=2">Choose the second card</a></p>';
        $output_obj[] = '<p><a href="main.php?z=' .
            $zone['id'] . '&a=c&c=3">Choose the third card</a></p>';
        $output_obj[] = '<hr width="100" />';
      }
    }

    $output_obj[] = '<p><a href="main.php?z=' . $zone['id'] .
        '">Go back to the Casino</a></p>';

  } elseif ('0' == $action) {

    if (array_key_exists(7, $c['buffs'])) {
      $buff = $c['buffs'][7];
      $output_obj[] = '<p><font color="red">You are unable to gamble any ' .
          'more right now!</font><br><font size="-2">';
      $now = time();
      $output_obj[] = renderTimeRemaining($now, $buff['expires']);
      $output_obj[] = ' left until you can test your luck once again.' .
          '</font></p>';
    } else {

      if ($c['gold'] >= 25000) {

        $output_obj[] = '<p><a href="main.php?z=' . $zone['id'] .
            '&a=d">Gamble on the Sun and Stars Dice for 25,000 gold!</a></p>';

      } else {

        $output_obj[] = '<p>You look at the lines of players waiting for ' .
            'their turn to gamble on the dice, and decide to come back ' .
            'when you have the necessary 25,000 gold to play.</p>';
      }

      if ($c['gold'] >= 2500) {

        $output_obj[] = '<p><a href="main.php?z=' . $zone['id'] .
            '&a=m">Gamble on the Great Wheel of Luck for 2,500 gold!</a></p>';

      } else {

        $output_obj[] = '<p>You make it a point to return later when you ' .
            'have enough gold to gamble on the Great Wheel of Luck.  The ' .
            'signs indicate a minimum payment of 2,500 gold, and as you pat ' .
            'your pockets, you realize you still have some money to make ' .
            'up.</p>';

      }

      if ($c['gold'] >= 250) {

        $output_obj[] = '<p><a href="main.php?z=' . $zone['id'] .
            '&a=c">Gamble on the Forbidden Cards for 250 gold!</a></p>';

      } else {

        $output_obj[] = '<p>You notice the dealers laying down plays from ' .
            'the Forbidden Cards, and lament the fact that you don\'t have ' .
            'the 250 gold necessary to play.  You make a mental note to ' .
            'return when you have the funds.</p>';

      }

//      $output_obj[] = '<p><a href="main.php?z=' . $zone['id'] .
//          '&a=r">Gamble on the Roulette tables!</a></p>';

    }

    $output_obj[] = '<p><font size="-2">You can only do this periodically! ' .
        'The city has implemented some tough rules in the casino.</font></p>';

  }

  return $output_obj;
}

function renderCustomZoneState($output_obj) {
  foreach ($output_obj as $x) {
    echo $x;
  }
}

?>