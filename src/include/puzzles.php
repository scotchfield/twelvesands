<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/log.php';


function getPuzzleArray($c, $artifact_id) {
  $puzzle = array();

  switch($artifact_id) {
  case 320: // Card 1
    $puzzle['text'] = '<p>What is the first name of the bread maker who ' .
        'operates out of the Market District in Capital City?</p>';
    $puzzle['solutions'] = array('barnabus', 'barnabus bidwell');
    $puzzle['buff'] = 31;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 0);
    break;
  case 321: // Card 2
    $puzzle['text'] = '<p>What is the first name of the kobold leader who ' .
        ' lives in the Disorganized Kobold Camp?</p>';
    $puzzle['solutions'] = array('drask', 'drask the snarling',
                                 'drask, the snarling');
    $puzzle['buff'] = 32;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 1);
    break;
  case 322: // Card 3
    $puzzle['text'] = '<p>What is the first name of the Capital City ' .
        'defector who left to join the Iron Hand Rebels as their ' .
        'cartographer, and now lives in the Fortified Rebel Camp?</p>';
    $puzzle['solutions'] = array('andras', 'andras the cartographer');
    $puzzle['buff'] = 33;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 2);
    break;
  case 323: // Card 4
    $puzzle['text'] = '<p>What is the first name of the great bear hunter ' .
        'who spends much of his time in the House of Defense smithing ' .
        'new armour pieces?</p>';
    $puzzle['solutions'] = array('uldor', 'uldor the hunter');
    $puzzle['buff'] = 34;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 3);
    break;
  case 324: // Card 5
    $puzzle['text'] = '<p>The Capital Barracks is home to a number of the ' .
        'military leaders.  In that area, there is a prominent Major with ' .
        'a little-known love of red wine.  What is her full name?</p>';
    $puzzle['solutions'] = array('major eva vorosbor', 'eva vorosbor');
    $puzzle['buff'] = 35;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 4);
    break;
  case 325: // Card 6
    $puzzle['text'] = '<p>If you hold 10,000 gold, and suddenly develop a ' .
        'desire to spend all of your money on Miniature Wooden Kobolds ' .
        'from Turagon\'s Trinkets, how many would you be able to buy?</p>';
    $puzzle['solutions'] = array('66', 'sixty-six', 'sixty six');
    $puzzle['buff'] = 36;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 5);
    break;
  case 326: // Card 7
    $puzzle['text'] = '<p>What is the largest number (other than itself) ' .
        'that divides in to 123,454,321 without a remainder?</p>';
    $puzzle['solutions'] = array('3011081');
    $puzzle['buff'] = 37;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 6);
    break;
  case 327: // Card 8
    $puzzle['text'] = '<p>A young warrior bought two blades recently, but ' .
        'decided they did not fit what she was looking for.  She sold them ' .
        'for 1500 gold each, making a profit of 20% on one, and a loss of ' .
        '20% on the other.  What was her overall net difference in gold ' .
        'after the transaction?</p>' .
        '<p>(<i>Note: if she earned 1000 gold ' .
        'through the sales, your answer should be 1000.  If she lost 1000 ' .
        'gold, your answer should be -1000.  You are expected to ' .
        'provide the positive or negative number difference.</i>)</p>';
    $puzzle['solutions'] = array('-125');
    $puzzle['buff'] = 38;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 7);
    break;
  case 328: // Card 9
    $puzzle['text'] = '<p>When you purchase a pack of Sandstorm Wizard ' .
        'Cards, you receive five individual cards.  There are 32 ' .
        'cards in total, and they are not unique.  Assume it is entirely ' .
        'possible that you could receive five copies of the same card when ' .
        'opening a deck.  If each of the 32 cards is equally likely, what ' .
        'are the odds you\'d find yourself with a pack where all five cards ' .
        'were the same?</p><p>(<i>Note: if you believe the correct response ' .
        'is something like 1 in x, your answer should be x.  So, if you ' .
        'think it is a 1 in 5 chance, just answer 5.</i>)</p>';
    $puzzle['solutions'] = array('1048576', '1 in 1048576');
    $puzzle['buff'] = 39;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 8);
    break;
  case 329: // Card 10
    $puzzle['text'] = '<p>Imagine that you have 100,000 gold in a ' .
        'bank.  At the end of every month, you collect 2% interest.  How ' .
        'much gold would you have after 24 months?</p>' .
        '<p><i>Note: The bank is greedy, and rounds down on your interest ' .
        'payments!  If you have 175 gold, 2% interest is 3.5 gold, of which ' .
        'you will only see 3.  At the end of the month, you would have 178 ' .
        'gold.</i></p>';
    $puzzle['solutions'] = array('160831');
    $puzzle['buff'] = 40;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 9);
    break;
  case 330: // Card 11
    $puzzle['text'] = '<p>Put the following terms in order:</p>' .
        '<p>A. k&#233;t<br>B. &#246;t<br>C. egy<br>' .
        'D. n&#233;gy<br>E. h&#225;rom</p>' .
        '<p><i>Note: If you think the order is A, B, C, D, E, your answer ' .
        'should be ABCDE with no spaces.</i></p>';
    $puzzle['solutions'] = array('caedb');
    $puzzle['buff'] = 41;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 10);
    break;
  case 331: // Card 12
    $puzzle['text'] = '<p>Put the following helms in order from weakest ' .
        'to strongest:</p>' .
        '<p>A. Padded Cap<br>B. Hide Helm of the Bear<br>' .
        'C. Interlaced Cap<br>D. Patron\'s Cap<br>' .
        'E. Studded Leather Cap</p>' .
        '<p><i>Note: If you think the order is A, B, C, D, E, your answer ' .
        'should be ABCDE with no spaces.</i></p>';
    $puzzle['solutions'] = array('acdbe');
    $puzzle['buff'] = 42;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 11);
    break;
  case 332: // Card 13
    $puzzle['text'] = '<p>Put the following weapons in order from weakest ' .
        'to strongest:</p>' .
        '<p>A. Fanged Poker<br>B. Curved Dagger<br>' .
        'C. Apprentice\'s Dagger<br>D. Runed Dagger of Fortitude<br>' .
        'E. Bat Fang Knuckle</p>' .
        '<p><i>Note: If you think the order is A, B, C, D, E, your answer ' .
        'should be ABCDE with no spaces.</i></p>';
    $puzzle['solutions'] = array('ecbad');
    $puzzle['buff'] = 43;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 12);
    break;
  case 333: // Card 14
    $puzzle['text'] = '<p>Put the following zones in order from easiest ' .
        'to hardest:</p>' .
        '<p>A. The Rushing Rapids<br>' .
        'B. Deteriorated Forest Trail<br>' .
        'C. Abandoned Farmhouse<br>' .
        'D. Sandstorm Outpost<br>' .
        'E. Disorganized Kobold Camp<br>' .
        'F. Fortified Iron Hand Rebel Camp<br>' .
        'G. The Undying Timepiece (Mid-Day)<br>' .
        'H. The Sand Mines (Earth Tear)<br>' .
        'I. Enchanted Nook<br>' .
        'J. Cavernous Bear Cave</p>' .
        '<p><i>Note: If you think the order is A, B, C, D, E, your answer ' .
        'should be ABCDE with no spaces.</i></p>';
    $puzzle['solutions'] = array('ecfbjgidah');
    $puzzle['buff'] = 44;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 13);
    break;
  case 334: // Card 15
    $puzzle['text'] = '<p>It looks like a hooligan has been at work, ' .
        'tagging the walls of the academy.  All we can find are painted ' .
        'messages like the one below.  Can you decode it?  (<i>Don\'t ' .
        'add spaces!</i>)</p>' .
        '<p><pre>tbseboxbtifsf</pre></p>';
    $puzzle['solutions'] = array('sardanwashere');
    $puzzle['buff'] = 45;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 14);
    break;
  case 335: // Card 16
    $puzzle['text'] = '<p>An old fishing lament has been written in order ' .
        'among the names of some frequently-caught fish.  What is the ' .
        'hidden phrase?  (<i>Don\'t add spaces!</i>)</p>' .
        '<p><pre>ANGLERFISH<br>RALBACOREU<br>' .
        'HAGFISHSLA<br>MPREYTMACK<br>ERELYSALTS<br>CALELHAGFI<br>' .
        'SHUPERCHRL<br>IONFISHEMU<br>DSNAPPERS</pre></p>';
    $puzzle['solutions'] = array('rustylures', 'rusty lures');
    $puzzle['buff'] = 46;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 15);
    break;
  case 336: // Card 17
    $puzzle['text'] = '<p>Word scrambles are clearly used by those who ' .
        'wish to keep their messages secret.  Can you decypher this one, ' .
        'found on an old scrap of paper?  (<i>Don\'t add spaces!</i>)</p>' .
        '<p><pre>DPPWJTXNMCDSQZSPT</pre></p>';
    $puzzle['solutions'] = array('dontforgetthemead');
    $puzzle['buff'] = 47;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 16);
    break;
  case 337: // Card 18
    $puzzle['text'] = '<p>In order to train academics who long to make a ' .
        'career out of books and writing, puzzles are offered to help ' .
        'break up the dreary monotony.  One such example is below.  Can ' .
        'you solve the puzzle?  (<i>Don\'t add spaces!</i>)</p>' .
        '<p><pre>0000100100101100000010001<br>' .
        '0010010011001110010000011<br>' .
        '0000010001010100010101110<br>' .
        '10001001001001010011</pre></p>';
    $puzzle['solutions'] = array('bewarethedarkforest',
        'beware the dark forest');
    $puzzle['buff'] = 48;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 17);
    break;
  case 338: // Card 19
    $puzzle['text'] = '<p>Can you guess the next number in the sequence?</p>' .
        '<p><pre>9, 16, 25, 36, 49, 14, 31, 0, ?</pre></p>';
    $puzzle['solutions'] = array('21');
    $puzzle['buff'] = 49;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 18);
    break;
  case 339: // Card 20
    $puzzle['text'] = '<p>Can you guess the next number in the sequence?</p>' .
        '<p><pre>2, -4, 2, -10, 0, 12, -9, 7, -20, 0, ?</pre></p>';
    $puzzle['solutions'] = array('22');
    $puzzle['buff'] = 50;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 19);
    break;
  case 340: // Card 21
    $puzzle['text'] = '<p>Can you guess the next number in the sequence?</p>' .
        '<p><pre>0, 1, 1, 2, 3, 5, 8, ?</pre></p>';
    $puzzle['solutions'] = array('13');
    $puzzle['buff'] = 51;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 20);
    break;
  case 341: // Card 22
    $puzzle['text'] = '<p>Can you guess the next number in the sequence?</p>' .
        '<p><pre>141, 592, 653, 589, 793, ?</pre></p>';
    $puzzle['solutions'] = array('238');
    $puzzle['buff'] = 52;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 21);
    break;
  case 342: // Card 23
    $puzzle['text'] = '<p>Can you guess the next number in the sequence?</p>' .
        '<p><pre>514229, 28657, 1597, 233, ?</pre></p>';
    $puzzle['solutions'] = array('89');
    $puzzle['buff'] = 53;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 22);
    break;
  case 343: // Card 24
    $puzzle['text'] = '<p>Can you guess the next number in the sequence?</p>' .
        '<p><pre>20, 23, 5, 12, 22, 5, 19, 1, 14, 4, ?</pre></p>';
    $puzzle['solutions'] = array('19');
    $puzzle['buff'] = 54;
    $puzzle['flag_id'] = 1;
    $puzzle['flag_value'] = (1 << 23);
    break;
  case 344: // Card 25
    $puzzle['text'] = '<p><pre>Always hungry, must always be fed,' .
        '<br>The finger I lick will soon turn red.</pre></p>';
    $puzzle['solutions'] = array('fire');
    $puzzle['buff'] = 55;
    $puzzle['flag_id'] = 2;
    $puzzle['flag_value'] = (1 << 0);
    break;
  case 345: // Card 26
    $puzzle['text'] = '<p><pre>TOVOTRAFGENCOSBNVONDDOSSJANSPOTTJBNJSOJSJBOXGBNIUSNMTNZO.  WBNJXSJBNJSPOTTDNTTOU.</pre></p>';
    $puzzle['solutions'] = array('fiery hands', 'fieryhands');
    $puzzle['buff'] = 56;
    $puzzle['flag_id'] = 2;
    $puzzle['flag_value'] = (1 << 1);
    break;
  case 346: // Card 27
    $puzzle['text'] = '<p>What is the missing number?</p>' .
        '<p><pre>3455 718 2673<br>1500 2282 3064<br>' .
        '1891 <b>&nbsp;?&nbsp;</b> 1109</pre></p>';
    $puzzle['solutions'] = array('3846');
    $puzzle['buff'] = 57;
    $puzzle['flag_id'] = 2;
    $puzzle['flag_value'] = (1 << 2);
    break;
  case 347: // Card 28
    $puzzle['text'] = '<p>What is the sum of the two missing numbers?</p>' .
        '<p><pre>322 2163 3215 3478<br>3741 2952 1900 585<br>' .
        '4004 2689 <b>&nbsp;?&nbsp;</b> 848<br>' .
        '1111 1374 2426 <b>&nbsp;?&nbsp;</b><br>' .
        '</pre></p>';
    $puzzle['solutions'] = array('5904');
    $puzzle['buff'] = 58;
    $puzzle['flag_id'] = 2;
    $puzzle['flag_value'] = (1 << 3);
    break;
  case 348: // Card 29
    $puzzle['text'] = '<p><pre>1937462738174637<br>' .
        '3627498473652164<br>3746981711317627</pre></p>';
    $puzzle['solutions'] = array('9311942923144428');
    $puzzle['buff'] = 59;
    $puzzle['flag_id'] = 2;
    $puzzle['flag_value'] = (1 << 4);
    break;
  case 349: // Card 30
    $puzzle['text'] = '<p><pre>TENW ROHS<br>' .
        'IDES HMSP<br>' .
        'LEWR CTDL<br>' .
        'HASE TTIR<br>' .
        'DLIT EISE<br>' .
        'LDOD IAEL</pre></p>';
    $puzzle['solutions'] = array('citadell');
    $puzzle['buff'] = 60;
    $puzzle['flag_id'] = 2;
    $puzzle['flag_value'] = (1 << 5);
    break;
  case 350: // Card 31
    $puzzle['text'] = '<p>In order to train academics who long to make a ' .
        'career out of books and writing, puzzles are offered to help ' .
        'break up the dreary monotony.  One such example is below.  Can ' .
        'you solve the puzzle?  (<i>Don\'t add spaces!</i>)</p>' .
        '<p><pre>0 13 13 21 16 0 3 22 13 4 0 3 23 16 23 7 ' .
        '4 2 10 23 0 3 4 13</pre></p>';
    $puzzle['solutions'] = array('allroadsleadtothecitadel');
    $puzzle['buff'] = 61;
    $puzzle['flag_id'] = 2;
    $puzzle['flag_value'] = (1 << 6);
    break;
  case 351: // Card 32
    $puzzle['text'] = '<p>In order to train academics who long to make a ' .
        'career out of books and writing, puzzles are offered to help ' .
        'break up the dreary monotony.  One such example is below.  Can ' .
        'you solve the puzzle?  (<i>Don\'t add spaces!</i>)</p>' .
        '<p><pre>efsbqfsqupofsbvpz</pre></p>';
    $puzzle['solutions'] = array('youarenotprepared');
    $puzzle['buff'] = 62;
    $puzzle['flag_id'] = 2;
    $puzzle['flag_value'] = (1 << 7);
    break;
  }

  if (!array_key_exists('text', $puzzle)) {
    return FALSE;
  }

  return $puzzle;
}

function readPuzzle($c_obj, $artifact_id) {
  $c = $c_obj->c;

  $artifact = hasArtifact($c_obj, $artifact_id);

  if (FALSE == $artifact) {
    echo '<p>You don\'t have that!</p>';
  } elseif (1 > $artifact['quantity']) {
    echo '<p>You don\'t have that!</p>';
  } elseif ($c['level'] < $artifact['min_level']) {
    echo '<p>Your level isn\'t high enough to use that artifact!</p>';
  } else {

    $puzzle = getPuzzleArray($c, $artifact_id);
    if (FALSE == $puzzle) {
      return FALSE;
    }

    echo '<p><b>You read the front of the card..</b></p>';

    echo '<hr width="300">';
    echo $puzzle['text'];
    if (array_key_exists($puzzle['flag_id'], $c['flags'])) {
      if (($c['flags'][$puzzle['flag_id']] & $puzzle['flag_value']) > 0) {
        echo '<p><i>You have solved this card already!</i></p>';
      }
    }
    echo '<hr width="300">';
  }
}

function solvePuzzle($c_obj, $artifact_id, $st_guess) {
  $c = $c_obj->c;

  $artifact = hasArtifact($c_obj, $artifact_id);
  $success = FALSE;

  if (FALSE == $artifact) {
    echo '<p>You don\'t have that!</p>';
  } elseif (1 > $artifact['quantity']) {
    echo '<p>You don\'t have that!</p>';
  } elseif ($c['level'] < $artifact['min_level']) {
    echo '<p>Your level isn\'t high enough to use that artifact!</p>';
  } else {

    $st = strtolower(fixStr($st_guess));

    $puzzle = getPuzzleArray($c, $artifact_id);
    if (FALSE == $puzzle) {
      return FALSE;
    }

    if (array_key_exists($puzzle['buff'], $c['buffs'])) {
      return FALSE;
    }

    addBuff($c_obj, $puzzle['buff'], 10859, 0, FALSE);
    addBuff($c_obj, 63, 359, 0, FALSE);

    foreach ($puzzle['solutions'] as $solution) {
      if (0 == strcmp($st, $solution)) { $success = TRUE; }
    }

    if (TRUE == $success) {
      $new_flag_value = $puzzle['flag_value'];
      if (array_key_exists($puzzle['flag_id'], $c['flags'])) {
        $new_flag_value = ($new_flag_value | $c['flags'][$puzzle['flag_id']]);
      }
      $c_obj->addFlag($puzzle['flag_id'], $new_flag_value);
    }
  }

  return $success;
}

function getAttemptPuzzleBuff($c_obj, $artifact_id) {
  $c = $c_obj->c;
  $buff = FALSE;

  $puzzle = getPuzzleArray($c, $artifact_id);

  if (FALSE == $puzzle) {
    return FALSE;
  }

  $buff = $puzzle['buff'];

  if (FALSE == $buff) {
    return FALSE;
  } elseif (!array_key_exists($buff, $c['buffs'])) {
    if (array_key_exists(63, $c['buffs'])) {
      return $c['buffs'][63];
    }

    return FALSE;
  }

  return $c['buffs'][$buff];
}

// 320..351 inclusive

function getSandstormCompletionArray($c) {
  $artifact_ids = array(320, 321, 322, 323, 324, 325, 326, 327, 328, 329,
                        330, 331, 332, 333, 334, 335, 336, 337, 338, 339,
                        340, 341, 342, 343, 344, 345, 346, 347, 348, 349,
                        350, 351);
  $ret_array = array();

  $i = 1;
  foreach ($artifact_ids as $x) {
    $puzzle = getPuzzleArray($c, $x);

    if (array_key_exists($puzzle['flag_id'], $c['flags'])) {
      if (($c['flags'][$puzzle['flag_id']] & $puzzle['flag_value']) == 0) {
        $puzzle['completed'] = FALSE;
      } else {
        $puzzle['completed'] = TRUE;
      }
    } else {
      $puzzle['completed'] = FALSE;
    }

    $ret_array[$i] = $puzzle;
    $i += 1;
  }

  return $ret_array;
}

?>