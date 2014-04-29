<?

require_once 'include/core.php';

require_once sg_base_path . 'include/constants.php';
require_once sg_base_path . 'include/sql.php';

class CharMini {
  function CharMini($char_id) {
    $time = time();

    $use_session = TRUE;
    if ($_SESSION['c'] != $char_id) {
      $use_session = FALSE;
    }

    $c = intval($char_id);
    $this->changed = array();

    $query = "
      SELECT
        *
      FROM
        `characters`
      WHERE
        id = $c
    ";

    $results = sqlQuery($query);
    if (!$results) { return FALSE; }

    $this->c = $results->fetch_assoc();
  }

  function save() {
    if ($this->c['id'] == 0) { return FALSE; }
    if ($this->flag_obj != NULL) {
      $this->flag_obj->save($this);
    }
    if ($this->artifact_obj != NULL) {
      $this->artifact_obj->save($this->c['id']);
    }
    if (count($this->changed) == 0) { return FALSE; }

    $char_updates = array();
    foreach ($this->changed as $k => $v) {
      $char_updates[] = $k . ' = \'' . esc($v) . '\'';
    }

    $query = "
      UPDATE
        `characters`
      SET
    " . join(', ', $char_updates) . "
      WHERE
        id = '" . $this->c['id'] . "'
    ";
    $results = sqlQuery($query);

    $this->changed = array();

    debugPrint('<font size="-2">Character Saved</font>');

    return TRUE;
  }

  function setChatChannel($x) {
    $this->c['chat_channel'] = htmlspecialchars($x);
    $this->changed['chat_channel'] = $this->c['chat_channel'];
    $_SESSION['cc'] = $this->c['chat_channel'];
  }
  function setChatChannelType($x) {
    $this->c['chat_channel_type'] = htmlspecialchars($x);
    $this->changed['chat_channel_type'] = $this->c['chat_channel_type'];
    $_SESSION['cc_type'] = $this->c['chat_channel_type'];
  }
}

?>