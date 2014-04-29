<?

function resetCharDungeonRun($c_obj, $log_obj, $d_id, $new_level) {
  $time = time();

  $c_obj->setLevel($new_level);
  $c_obj->setDungeonRun($d_id, $time);
  $c_obj->setXp(0);
  $c_obj->setCurrentHp(12);
  $c_obj->setMana(45);
  $c_obj->setTotalCombats(0);
  $c_obj->setTotalFatigue(0);
  $c_obj->setTotalFatigueUses(0);
  $c_obj->setTitledName($c_obj->c['name']);
  $c_obj->setFatigue(0);
  $c_obj->setFatigueReduction(0);
  $c_obj->setFatigueRested(0);

  $c_obj->setGoldBank($c_obj->c['gold_bank'] + $c_obj->c['gold']);
  $c_obj->setGold(0);

  //deleteAllSkills($c_obj->c);
  deleteAllRunes($c_obj);
  deleteAllBuffs($c_obj);

  $query = "
    DELETE FROM
      `char_flags`
    WHERE
      char_id = " . $c_obj->c['id'] . " AND
      flag_id IN (5, 6, 20, 33, 34, 35, 36, 42, 44, 59)
  ";
  $results = sqlQuery($query);

  if ($c_obj->c['weapon']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['weapon'], 1,
        $c_obj->c['weapon_enc']);
    $c_obj->setIdPair('weapon', 0, 0);
  }
  if ($c_obj->c['armour_head']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_head'], 1,
        $c_obj->c['armour_head_enc']);
    $c_obj->setIdPair('armour_head', 0, 0);
  }
  if ($c_obj->c['armour_chest']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_chest'], 1,
        $c_obj->c['armour_chest_enc']);
    $c_obj->setIdPair('armour_chest', 0, 0);
  }
  if ($c_obj->c['armour_legs']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_legs'], 1,
        $c_obj->c['armour_legs_enc']);
    $c_obj->setIdPair('armour_legs', 0, 0);
  }
  if ($c_obj->c['armour_neck']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_neck'], 1,
        $c_obj->c['armour_neck_enc']);
    $c_obj->setIdPair('armour_neck', 0, 0);
  }
  if ($c_obj->c['armour_trinket']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_trinket'], 1,
        $c_obj->c['armour_trinket_enc']);
    $c_obj->setIdPair('armour_trinket', 0, 0);
  }
  if ($c_obj->c['armour_trinket_2']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_trinket_2'], 1,
        $c_obj->c['armour_trinket_2_enc']);
    $c_obj->setIdPair('armour_trinket_2', 0, 0);
  }
  if ($c_obj->c['armour_trinket_3']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_trinket_3'], 1,
        $c_obj->c['armour_trinket_3_enc']);
    $c_obj->setIdPair('armour_trinket_3', 0, 0);
  }
  if ($c_obj->c['armour_hands']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_hands'], 1,
        $c_obj->c['armour_hands_enc']);
    $c_obj->setIdPair('armour_hands', 0, 0);
  }
  if ($c_obj->c['armour_wrists']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_wrists'], 1,
        $c_obj->c['armour_wrists_enc']);
    $c_obj->setIdPair('armour_wrists', 0, 0);
  }
  if ($c_obj->c['armour_belt']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_belt'], 1,
        $c_obj->c['armour_belt_enc']);
    $c_obj->setIdPair('armour_belt', 0, 0);
  }
  if ($c_obj->c['armour_boots']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_boots'], 1,
        $c_obj->c['armour_boots_enc']);
    $c_obj->setIdPair('armour_boots', 0, 0);
  }
  if ($c_obj->c['armour_ring']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_ring'], 1,
        $c_obj->c['armour_ring_enc']);
    $c_obj->setIdPair('armour_ring', 0, 0);
  }
  if ($c_obj->c['armour_ring_2']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['armour_ring_2'], 1,
        $c_obj->c['armour_ring_2_enc']);
    $c_obj->setIdPair('armour_ring_2', 0, 0);
  }
  if ($c_obj->c['mount']['id'] != 0) {
    awardArtifactString($c_obj, $c_obj->c['mount'], 1);
    $c_obj->setMountId(0);
  }

//  $a_obj = getCharArtifactsArray($c_obj->c['id'], $_POST['ids']);

  $new_award_obj = array();
  $bank_obj = array();
  updateBankDepositObjs($c_obj, 2000000000, $c_obj->getInventory(),
                        $bank_obj, $new_award_obj, $log_obj);

  if (count($bank_obj) > 0) {
    addBankArtifacts($c_obj, $bank_obj);
    setCharArtifacts($c_obj, $new_award_obj);
  }

  $c_obj->addFlag(sg_flag_bank_withdrawals, 0);

  $c_obj->clearArtifactAwards();

  wipeQuestData($c_obj->c['id']);

  clearSelect(FALSE);

/*  unset($_SESSION['inventory_obj']);
  unset($_SESSION['buffs']);
  unset($_SESSION['equipped_array']);
  unset($_SESSION['flags']);
  unset($_SESSION['quests']);
  unset($_SESSION['skills']);
  unset($_SESSION['time_check']);
  unset($_SESSION['duel_time_check']);*/
}

function getSkillBitVal($c_obj, $base) {
  $v = 0;
  for ($i = 0; $i < 50; $i++) {
    if (array_key_exists($base + $i, $c_obj->c['skills'])) {
      $v += (1 << $i);
    }
  }
  return $v;
}

function completeDungeonRun($c_obj) {
  $time = time();

  $rune_ids = array();
  foreach ($c_obj->c['runes'] as $rune) {
    $rune_ids[] = $rune['id'];
  }
  while (count($rune_ids) < 5) {
    $rune_ids[] = 0;
  }

  $query = "
    INSERT INTO
      `dungeon_rune_runs`
      (`d_id`, `char_id`, `char_name`, `level`, `xp`,
       `total_fatigue`, `total_combats`, `date_started`, `date_completed`,
       `rune_1`, `rune_2`, `rune_3`, `rune_4`, `rune_5`)
    VALUES
      (" . $c_obj->c['d_id'] . ",
       " . $c_obj->c['id'] . ",
       '" . $c_obj->c['name'] . "',
       " . $c_obj->c['level'] . ",
       " . $c_obj->c['xp'] . ",
       " . $c_obj->c['total_fatigue'] . ",
       " . $c_obj->c['total_combats'] . ",
       " . $c_obj->c['d_run'] . ",
       " . $time . ",
       " . join(',', $rune_ids) . ")
  ";

  if ($c_obj->c['id'] > 1) {
    sqlQuery($query);
  }

  $c_obj->setDungeonRun(0, 0);
  unset($_SESSION['dungeon_run_count']);
}

function getPathfinderState($c_obj, $log_obj) {
  $ret_obj = array();
  $ret_obj['actions'] = array();
  $ret_obj['text'] = '';

  $action = getGetInt('a', 0);

  $target_artifacts = array(
    1 => 587,
  );

  $reward_artifacts = array(
    1 => 585,
  );

  if (11 == $action) {
    if ($c_obj->c['d_run'] != 0) {
      $ret_obj['text'] = '<p class="tip">You\'re already in the middle of ' .
          'a dungeon run!</p>';
    } elseif ($c_obj->c['level'] < 10) {
      $ret_obj['text'] = '<p class="tip">You must be at least level 10 to ' .
          'start this run!</p>';
    } else {
      $log_obj->addLog($c_obj->c, sg_log_dungeon_run_start, 1, 0, 0, 0);
      resetCharDungeonRun($c_obj, $log_obj, 1, 1);
      $ret_obj['text'] = '<p>You close your eyes, and an attendant begins ' .
          'a series of incantations.  Suddenly, your mind is filled with ' .
          'recollections of your past experiences, and you dwell there, ' .
          'reminding yourself of the memories you are about to lose track ' .
          'of.  As the process continues, you begin to feel younger, ' .
          'but certainly more wise..</p>' .
          '<p>After what seems like an eternity, you open your eyes.  As ' .
          'you glance into a mirror placed for you by the attendants, you ' .
          'reflect on your new self, and prepare to begin the next phase ' .
          'of your journey.</p>' .
          '<p><b>You have begun a run on the &Aacute;lmok ' .
          'Crypts Stables!</b></p>';
    }
  } elseif (10 == $action) {
    if ($c_obj->c['d_id'] == 0) {
      $drop_artifact = getArtifact(587);
      $reward_artifact = getArtifact(585);
      $ret_obj['text'] = '<p><b>Are you sure you want to begin a run on ' .
          'the &Aacute;lmok Crypts Stables?</b></p>' .
          '<p>In order to complete this dungeon run, you must reach ' .
          '<b>Level 12</b>, you must defeat S&ouml;t&eacute;t, the ' .
          'Summoned, and you must bring us one ' .
          renderArtifactStr($drop_artifact) .
          ' from S&ouml;t&eacute;t.  Return to us as soon as you are ' .
          'finished, and we will reward you with one ' .
          renderArtifactStr($reward_artifact) .
          ', which can be exchanged for rewards from the Pathfinder\'s Onyx ' .
          'Storefront.</p>';
      $ret_obj['actions'][] = '<a href="main.php?z=107&a=11"><b>Yes, begin ' .
          'the dungeon run!</b></a>';
      $ret_obj['actions'][] = '<a href="main.php?z=107">No, return to the ' .
          'Temporal Laboratory</a>';
    }
  } elseif (2 == $action) {
    if ($c_obj->c['d_id'] == 0) {
      $ret_obj['text'] = '<p class="tip">You aren\'t on a dungeon run! ' .
          'You\'re a phony!  A big fat phony!</p>';
    } elseif (getArtifactQuantity($c_obj,
                                  $target_artifacts[$c_obj->c['d_id']]) == 0) {
      $ret_obj['text'] = '<p class="tip">You don\'t have the artifact ' .
          'required to complete this dungeon run!</p>';
    } else {
      $log_obj->addLog($c_obj->c, sg_log_dungeon_run_end, $c_obj->c['d_id'],
          0, 0, 0);
      $artifact = getArtifact($reward_artifacts[$c_obj->c['d_id']]);
      $ret_obj['text'] = '<p>Congratulations, you have completed the run! ' .
          'Your name has been added to the leaderboards, and you can track ' .
          'how well you\'ve done through your profile.  By completing more ' .
          'runs and improving your time, you will earn more rewards, and ' .
          'perhaps sit atop the leaderboards!</p><p>' .
          awardArtifactString($c_obj, $artifact, 1);
      removeArtifact($c_obj, $target_artifacts[$c_obj->c['d_id']], 1);
      completeDungeonRun($c_obj);
    }
  } elseif (1 == $action) {
    $ret_obj['text'] = '<p><b>Dungeon Runs</b></p>' .
        '<p><i>In ages past, when the races of the world had little to fear ' .
        'from reckless warlords like the ones who shattered the world and ' .
        'created the sands, it was believed that the best way to gain true ' .
        'knowledge was to study and work hard.  When the the Twelve became ' .
        'aware of the workings of their world, they realized that time was ' .
        'just another variable in the struggle for power.</i></p>' .
        '<p><i>We are not bound to a timeline.  It was the Twelve who ' .
        'taught us that.  In bending their own timelines, they were able to ' .
        'spend limitless amounts of energy studying and mastering their ' .
        'abilities.  It may have only been thanks to their mercy that the ' .
        'entire world was not torn asunder during their war.  Nevertheless, ' .
        'they left with us the knowledge that in order to begin the path ' .
        'to greatness, we must revisit our past.</i></p>' .
        '<p>When you choose to take on a dungeon run, you are challenging ' .
        'yourself to advance your level and to clear out a dungeon as ' . 
        'quickly as possible.  You will be reset to level 1, your mind ' .
        'will be wiped of non-permanent skills, and all your artifacts ' .
        'will be stored in the Bank of Nobility.  As you progress through ' .
        'the levels towards your goal, you will be able to remove artifacts ' .
        'from the bank that can help you.  You are unable to wipe your ' .
        'mind of skills over the course of a dungeon run, so choose ' .
        'carefully!  When you defeat the final foe of the dungeon, an ' .
        'artifact will drop.  Bring it to us, and we will record the time ' .
        'and details of your run, and reward you with an artifact that can ' .
        'be exchanged for a true treasure.</p>';
    $ret_obj['actions'][] = '<a href="main.php?z=107">Return to the ' .
        'Temporal Laboratory</a>';
  } else {
    if ($c_obj->c['d_id'] == 0) {
      if ($c_obj->c['level'] < 10) {
        $ret_obj['actions'][] = '<s>Begin an &Aacute;lmok Crypts ' .
            'Stables dungeon run</s> (Level 10 required)<br>';
      } else {
        $ret_obj['actions'][] = '<a href="main.php?z=107&a=10">Begin an ' .
            '&Aacute;lmok Crypts Stables dungeon run</a><br>';
      }
    } else {
      if (getArtifactQuantity($c_obj,
                              $target_artifacts[$c_obj->c['d_id']]) > 0) {
        $artifact = getArtifact($target_artifacts[$c_obj->c['d_id']]);
        $ret_obj['actions'][] = '<b><a href="main.php?z=107&a=2">' .
             'Complete the dungeon run!</a></b><br><font size="-2">(hand ' .
             'in your ' . renderArtifactStr($artifact) . ')</font><br>';
      }
    }

    $ret_obj['actions'][] = '<a href="main.php?z=107&a=1">Learn more about ' .
        'dungeon runs</a>';
  }

  $ret_obj['actions'][] = '<br><a href="main.php?z=104">Return to the ' .
      'Pathfinder\'s Hall</a>';

  return $ret_obj;
}

function renderPathfinderState($zone, $c_obj, $p_obj) {
  echo '<p class="zone_title">' . $zone['name'] . '</p>';
  echo '<p class="zone_description">' . $zone['description'] . '</p>';

  if ($c_obj->c['d_id'] > 0) {
    echo '<p><b>You are currently on ';
    switch ($c_obj->c['d_id']) {
    case 1: echo 'an &Aacute;lmok Crypts Stables'; break;
    }
    echo ' dungeon run.</b></p>';
  }

  echo $p_obj['text'];
  echo '<p>' . join('<br>', $p_obj['actions']) . '</p>';
}


?>