<?

require_once 'include/core.php';

require_once sg_base_path . 'include/sql.php';

function getChatById( $channel, $channel_type, $id, $c_id ) {
    $chan = intval( $channel );
    $chan_type = intval( $channel_type );
    $i = intval( $id );
    $c = intval( $c_id );

    $first_sql = '';
    if ( $i == 0 ) {
        $first_sql = ' AND char_id > 0 ';
    }

    $time_check = time() - 30;
    $query = "
      SELECT
        *
      FROM
        `chat`
      WHERE
        id > $i AND
          ((channel = 0 AND private_id IN (0, $c) AND timestamp > $time_check)
             OR
           (channel = $chan AND channel_type = $chan_type))
      ORDER BY
        timestamp DESC
      LIMIT
        5
    ";

    $results = sqlQuery( $query );
    $chat = array();

    if ( $results != NULL ) {
        while ( $record = $results->fetch_assoc() ) {
            $chat[] = $record;
        }
    }

    if ( 0 == $i ) {
        $time = time();
        $c_name = 'normal';
        if ( 1 == $chan_type ) {
            $c_name = 'guild';
        } elseif ( 0 == $chan_type ) {
            if ( 2 == $chan ) {
                $c_name = 'trade';
            }
        }
        $chat[] = array(
            'char_name' => 'System',
            'char_id' => 0,
            'message' => 'You are now entering the <b>'.$c_name.'</b> chat ' .
                'channel.  Type /who to see who else is in the channel, and /help ' .
                'for help.',
            'timestamp' => $time,
            'id' => 1,
        );
    }

    return $chat;
}

function addChat( $private_id, $channel, $channel_type,
                  $char_name, $char_id, $message ) {
    $p_id = intval( $private_id );
    $chan = intval( $channel );
    $chan_type = intval( $channel_type );
    $n = fixStr( $char_name );
    $m = fixStr( $message );
    $time = time();

    $query = "
      INSERT INTO
        `chat` (private_id, channel, channel_type,
                timestamp, char_name, char_id, message)
      VALUES
        ('$p_id', '$chan', '$chan_type', '$time', '$n', $char_id, '$message')
    ";

    $results = sqlQuery( $query );
}

function getUsersInChat() {
    $time = time() - 60;

    $query = "
      SELECT
        char_name, char_id
      FROM
        `sessions`
      WHERE
        timestamp_chat > $time
      ORDER BY
        char_name ASC
    ";

    $results = sqlQuery( $query );
    $users = array();

    if ( $results != NULL ) {
        while ( $u = $results->fetch_assoc() ) {
            $users[] = $u;
        }
    }

    return $users;
}

?>