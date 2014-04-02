<?

require_once 'include/core.php';

require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/constants.php';
require_once sg_base_path . 'include/flag.php';
require_once sg_base_path . 'include/sql.php';

/*class Foe {
    function Foe( $foe_id ) {
        $foe_id = intval( $foe_id );
        $query = "SELECT * FROM `foes` WHERE id=$foe_id";
        $results = sqlQuery( $query );
        if ( ! $results ) { return FALSE; }
        $this->c = $results->fetch_assoc();
    }
}*/


function getFoeSql( $foe_id ) {
    return FALSE;
    $f = esc( $foe_id );

    $query = "
      SELECT
        f.*
      FROM
        `foes` AS f
      WHERE
        id = '$f'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $foe = $results->fetch_assoc();
    return $foe;
}

function getFoe( $c_obj, $foe_id ) {
    $f = esc( $foe_id );
    $foe = FALSE;

    if ( ( sg_scalingfoe != $f ) && ( 235 != $f ) ) {
        if ( ( isset( $_SESSION[ 'foe_obj' ] ) ) && ( $_SESSION[ 'foe_obj' ][ 'id' ] == $f ) ) {
            return $_SESSION[ 'foe_obj' ];
        }
    }

    $filename = '/home/swrittenb/ts_util/foes/' . $f . '.inc';
    if ( file_exists( $filename ) ) {
        include $filename;

        if ( 235 == $f ) {
            include sg_base_path . '/include/_gen_pravokan.php';
            $foe[ 'generated_name' ] = getGeneratedFoeName(
                getFlagValue( $c_obj, sg_flag_pravokan_reveler_srand ) );
        }

        if ( sg_scalingfoe == $f ) {
            $v = 1;
            if ( ( array_key_exists( 'flags', $c_obj->c ) ) &&
                 ( array_key_exists( sg_flag_scalingfoe, $c_obj->c[ 'flags' ] ) ) ) {
                $v = $c_obj->c[ 'flags' ][ sg_flag_scalingfoe ];
            }

            $stat_val = 9 + $v;
            $base_dmg = $v;
            $rand_dmg = round( $v / 2 );

            $foe[ 'name' ] = 'Capital Trainer (Tier ' . $v . ')';
            if ( 0 == ( $v % 2 ) ) {
                $foe[ 'text' ] = 'The trainer prepares his sword for combat.';
            } else {
                $foe[ 'text' ] = 'The trainer prepares her sword for combat.';
            }
            $foe[ 'hp' ] = $v * 12;
            $foe[ 'armour' ] = $v * 100;
            $foe[ 'level' ] = $v;
            $foe[ 'xp' ] = $v * 5;
            $foe[ 'str' ] = $stat_val;
            $foe[ 'dex' ] = $stat_val;
            $foe[ 'int' ] = $stat_val;
            $foe[ 'cha' ] = $stat_val;
            $foe[ 'con' ] = $stat_val;
            $foe[ 'attacks' ][ 1 ][ 'base_damage' ] = $base_dmg;
            $foe[ 'attacks' ][ 1 ][ 'random_damage' ] = $rand_dmg;
            $foe[ 'attacks' ][ 2 ][ 'base_damage' ] = $base_dmg;
            $foe[ 'attacks' ][ 2 ][ 'random_damage' ] = $rand_dmg;
            $foe[ 'attacks' ][ 3 ][ 'base_damage' ] = $base_dmg;
            $foe[ 'attacks' ][ 3 ][ 'random_damage' ] = $rand_dmg;
        }
    } else {
        $foe = getFoeSql( $f );
    }

    $foe[ 'name' ] = utf8_encode( $foe[ 'name' ] );
    foreach ( $foe[ 'attacks' ] as &$a ) {
        $a[ 'text_1' ] = utf8_encode( $a[ 'text_1' ] );
        $a[ 'text_2' ] = utf8_encode( $a[ 'text_2' ] );
        $a[ 'text_3' ] = utf8_encode( $a[ 'text_3' ] );
    }
    foreach ( $foe[ 'artifacts' ] as &$a ) {
        $a[ 'name' ] = utf8_encode( $a[ 'name' ] );
        $a[ 'plural_name' ] = utf8_encode( $a[ 'plural_name' ] );
        $a[ 'text' ] = utf8_encode( $a[ 'text' ] );
    }

    if ( sg_scalingfoe != $f ) {
        $_SESSION[ 'foe_obj' ] = $foe;
    }

    return $foe;
}

function getFoeArtifacts( $foe_id ) {
    $f = esc( $foe_id );

    $filename = '/home/swrittenb/ts_util/foes/' . $f . '.inc';
    if ( file_exists( $filename ) ) {
        include $filename;
        return $foe[ 'artifacts' ];
    } else {
        return array();
    }
}

function getFoeNames( $a_obj ) {
    $id_obj = array();
    foreach ( $a_obj as $x ) {
        $id_obj[] = intval( $x );
    }
    $query = 'SELECT id, name FROM `foes` WHERE id IN (' .
        join( ',', $id_obj ) . ')';
    $results = sqlQuery( $query );
    $foe_names = getResourceAssocById( $results );
    return $foe_names;
}

?>