<?

define('sg_app_root', 'http://localhost:8888/twelvesands/src/');
define('sg_name', 'Twelve Sands');
define('sg_author', 'Scott Grant');
define('sg_version', 'v0.01');
define('sg_base_path', '/Users/scott/Sites/twelvesands/src/');

define('sg_db_address', 'localhost');
define('sg_db_port', 8889);
define('sg_db_name', 'ts_game');
define('sg_db_user', 'root');
define('sg_db_password', 'root');

define('sg_valid_login_number', '10');

define('sg_debug', 0);

function debugPrint($x) {
  //if ($_SESSION['c'] == 1) {
    if (is_array($x)) {
      echo '<p>'; print_r($x); echo '</p>';
    } else {
      echo '<p>' . $x . '</p>';
    }
  //}
}

function debugTime() {
  list($usec, $sec) = explode(" ", microtime());
  return ((float)$usec + (float)$sec);
}

?>