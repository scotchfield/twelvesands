<?

require_once sg_base_path . 'include/constants.php';

function getCombatBarArray( $char_obj ) {
    return array(
        0 => array(
            'n' => 'Nothing',
            'u' => 'combat.php',
            'i' => 'buff-empty.gif'
        ),
        1 => array(
            'n' => 'Attack with ' . $char_obj->c[ 'weapon' ][ 'name' ],
            'u' => 'combat.php?a=a',
            'i' => 'buff-green.gif'
        )
    );
}

$combat_bar_valid_bases = array(
    0 => sg_flag_cb1_start
);

?>