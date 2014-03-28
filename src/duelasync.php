<?

require_once 'include/core.php';

require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/duels.php';


$state_id = intval( $_SESSION[ 'duel_id' ] );

if ( $state_id > 0 ) {
    $c_id = intval( $_SESSION[ 'c' ] );
    $duel_state = getDuelState( $c_id, $state_id );

    if ( ( ( $duel_state[ 'state' ] == 1 ) &&
           ( $duel_state[ 'char_id_1' ] == $c_id ) ) ||
         ( ( $duel_state[ 'state' ] == 2 ) &&
           ( $duel_state[ 'char_id_2' ] == $c_id ) ) ||
           ( $duel_state[ 'state' ] == 3 ) ) {
        echo '1';
    }
}

?>