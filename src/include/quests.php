<?

function getQuest( $quest_id ) {
    $q = esc( $quest_id );

    $filename = '/home/swrittenb/ts_util/quests/' . $q . '.inc';
    if ( file_exists( $filename ) ) {
        include $filename;
        $quest[ 'name' ] = utf8_encode( $quest[ 'name' ] );
        return $quest;
    }
    return NULL;
}

function updateQuestCounts( $c, $q_id, $foe_1, $foe_2, $foe_3 ) {
    $q = esc( $q_id );
    $f1 = esc( $foe_1 );
    $f2 = esc( $foe_2 );
    $f3 = esc( $foe_3 );

    if ( array_key_exists( $q_id, $c[ 'quests' ] ) ) {
        $query = "
          DELETE FROM
            `char_quests`
          WHERE
            char_id = " . $c[ 'id' ] . " AND quest_id = $q
        ";
        $results = sqlQuery( $query );

        $query = "
          INSERT INTO
            `char_quests`
            (char_id, quest_id, status, foe_count_1, foe_count_2, foe_count_3)
          VALUES
            ('" . $c[ 'id' ] . "', '" . $q . "', '0', '$f1', '$f2', '$f3')
        ";

        $results = sqlQuery( $query );
    } else { return FALSE; }

    unset( $_SESSION[ 'quests' ] );

    return TRUE;
}

function addQuestSeen( $c, $q_id ) {
    $q = esc( $q_id );

    if ( ! array_key_exists( $q_id, $c[ 'quests' ] ) ) {
      $query = "
        INSERT INTO
          `char_quests` (char_id, quest_id, status)
        VALUES
          ('" . $c[ 'id' ] . "', '" . $q . "', '0')
      ";

      $results = sqlQuery( $query );
    }

    unset( $_SESSION[ 'quests' ] );
}

function getQuestListGifts( $q_obj ) {
    $query = 'SELECT gift_artifact, gift_quantity FROM `quests` WHERE id IN (' .
        join( ',', $q_obj ) . ') AND gift_quantity > 0';
    $results = sqlQuery( $query );

    $ret_obj = array();
    if ( $results ) {
        while ( $q = $results->fetch_assoc() ) {
            $ret_obj[ $q[ 'gift_artifact' ] ] += $q[ 'gift_quantity' ];
        }
    }

    return $ret_obj;
}

function addQuestListSeen( $c_obj, $q_obj ) {
    $quest_obj = array();
    foreach ( $q_obj as $q_id ) {
        if ( ! array_key_exists( intval( $q_id ), $c_obj->c[ 'quests' ] ) ) {
            $quest_obj[] = '(' . $c_obj->c[ 'id' ] . ',' . intval( $q_id ) . ',0)';
        }
    }

    if ( count( $quest_obj ) > 0 ) {
        $gift_obj = getQuestListGifts( $q_obj );
        $artifact_obj = array();
        foreach ( $gift_obj as $k => $v ) {
            $artifact_obj[ 'id' ] = $k;
            awardArtifactString( $c_obj, $artifact_obj, $v, 0 );
        }

        $query = "
          INSERT INTO
            `char_quests` (char_id, quest_id, status)
          VALUES " . join( ',', $quest_obj );

        $results = sqlQuery($query);
    }

    unset( $_SESSION[ 'quests' ] );
}

function addQuestCompleted( $c, $q_id ) {
    $q = esc( $q_id );

    $query = "
      DELETE FROM
        `char_quests`
      WHERE
        char_id = " . $c[ 'id' ] . " AND quest_id = $q
    ";

    $results = sqlQuery( $query );

    $query = "
      INSERT INTO
        `char_quests` (char_id, quest_id, status)
      VALUES
        ('" . $c[ 'id' ] . "', '" . $q . "', '1')
    ";

    $results = sqlQuery( $query );

    unset( $_SESSION[ 'quests' ] );
}

function toggleQuestHidden( $char_obj, $q_id ) {
    $q = intval( $q_id );

    if ( ! array_key_exists( $q, $char_obj->c[ 'quests' ] ) ) {
        return FALSE;
    }

    $h = 1;
    if ( $char_obj->c[ 'quests' ][ $q ][ 'hidden' ] > 0 ) {
        $h = 0;
    }

    $query = "
      UPDATE
        `char_quests`
      SET
        hidden = $h
      WHERE
        char_id = " . $char_obj->c[ 'id' ] . " AND quest_id = $q
    ";

    $results = sqlQuery( $query );

    unset( $_SESSION[ 'quests' ] );
}

function getCharQuests( $char_id ) {
    $c = intval( $char_id );

    $query = "
      SELECT
        q.id, q.npc_id, q.name, q.text, c.status, c.hidden,
        q.min_level, q.repeatable,
        q.quest_artifact1, q.quest_quantity1,
        q.quest_artifact2, q.quest_quantity2,
        q.quest_artifact3, q.quest_quantity3,
        q.quest_foe1, q.quest_foe_quantity1, c.foe_count_1,
        q.quest_foe2, q.quest_foe_quantity2, c.foe_count_2,
        q.quest_foe3, q.quest_foe_quantity3, c.foe_count_3,
        q.reward_artifact, q.reward_quantity, q.reward_xp,
        q.reward_rep_amount, q.reward_rep_id, q.reward_rep_max
      FROM
        char_quests AS c, quests AS q
      WHERE
        c.char_id = '$c' AND c.quest_id = q.id
      ORDER BY
        q.min_level ASC, q.name ASC
    ";
    $results = sqlQuery( $query );

    $quests = array();

    if ( $results ) {
        while ( $quest = $results->fetch_assoc() ) {
            $quest[ 'name' ] = utf8_encode( $quest[ 'name' ] );
            $quest[ 'text' ] = utf8_encode( $quest[ 'text' ] );
            $quests[ $quest[ 'id' ] ] = $quest;
        }
    }

    return $quests;
}

function wipeQuestData( $char_id ) {
    $c = intval( $char_id );

    $query = "
      DELETE FROM
        `char_quests`
      WHERE
        char_id = $c
    ";
    $results = sqlQuery( $query );

    $query = "
      SELECT
        id
      FROM
        `artifacts`
      WHERE
        dr_destroy = 1
    ";
    $results = sqlQuery( $query );

    $a_obj = array();
    while ( $a = $results->fetch_assoc() ) {
        $a_obj[] = $a[ 'id' ];
    }

    $a_st = join( ',', $a_obj );

    $query = "
      DELETE FROM
        `char_artifacts`
      WHERE
        char_id = $c AND
        artifact_id IN ($a_st)
    ";
    $results = sqlQuery( $query );

    $query = "
      DELETE FROM
        `char_bank`
      WHERE
        char_id = $c AND
        artifact_id IN ($a_st)
    ";
    $results = sqlQuery( $query );
}

?>
