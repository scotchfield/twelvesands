<?

require_once 'include/core.php';

require_once sg_base_path . 'include/sql.php';
require_once sg_base_path . 'include/constants.php';

function getReputationName( $r ) {
    if ( 1 == $r ) { return 'Capital City'; }
    elseif ( 2 == $r ) { return 'Guardians of the Light'; }
    elseif ( 3 == $r ) { return 'The Sandguard'; }
    elseif ( 4 == $r ) { return 'The Collectors'; }

    return 'wtf.';
}

function getReputationScore( $r_val ) {
    if ( $r_val > 83500000 ) {
        return array( 'n' => 'Ambassador',
                      'v' => ( $r_val - 83500000 ), 'm' => 100000 );
    } elseif ( $r_val > 33500000 ) {
        return array( 'n' => 'Hero, ' . ( floor( ( $r_val - 33500000 ) / 1000 ) .
                      '/50000' ),
                      'v' => ( $r_val - 33500000 ), 'm' => 50000000 );
    } elseif ( $r_val > 13500000 ) {
        return array( 'n' => 'Honoured, ' . ( floor( ( $r_val - 13500000 ) / 1000 ) .
                      '/20000' ),
                      'v' => ( $r_val - 13500000 ), 'm' => 20000000 );
    } elseif ( $r_val > 3500000 ) {
        return array( 'n' => 'Patron, ' . ( floor( ( $r_val - 3500000 ) / 1000 ) .
                      '/10000' ),
                      'v' => ( $r_val - 3500000 ), 'm' => 10000000 );
    } elseif ( $r_val > 1000000 ) {
        return array( 'n' => 'Friendly, ' . ( floor( ( $r_val - 1000000 ) / 1000 ) . '/2500' ),
                      'v' => ( $r_val - 1000000 ), 'm' => 2500000 );
    }
    return array( 'n' => 'Neutral, ' . ( floor( $r_val / 1000 ) . '/1000' ),
                  'v' => $r_val, 'm' => 1000000 );
}

function renderReputation( $c_obj, $r_obj, $show_stores ) {
    $score_obj = getReputationScore( $r_obj[ 'value' ] );
    $st = '<b>' . $r_obj[ 'name' ] . '</b>: ' . $score_obj[ 'n' ] . '<center>' .
          renderBarStr( $score_obj[ 'v' ], $score_obj[ 'm' ],
                        'good', 'neutral', 150 ) . '<font size="-2">';

    if ( TRUE == $show_stores ) {
        switch ( $r_obj[ 'reputation_id' ] ) {
        case 1:
        case 2: $st = $st .
            '(<a href="main.php?z=66">Capital City Rewards Hall</a>)'; break;
        case 3: $st = $st .
            '(<a href="main.php?z=68">The Sandguard Storehouse</a>)'; break;
        case 4:
            if ( $c_obj->c[ 'level' ] >= 11 ) {
                $st = $st . '(<a href="main.php?z=97">The Reclaimed Vault</a>)';
            }
            break;
        default: break;
        }
    }

    $st = $st . '</font></center>';
    return $st;
}

function awardReputationString( $c_obj, $r, $quantity ) {
    $rep = $c_obj->c[ 'reputations' ][ $r ][ 'value' ];

    if ( $rep > 0 ) {

        $new_rep = $rep + $quantity;
        $c_obj->c[ 'reputations' ][ $r ][ 'value' ] = $new_rep;

        $query = "
          UPDATE
            `char_reputations`
          SET
             value = $new_rep
          WHERE
            char_id = " . $c_obj->c[ 'id' ] . " AND reputation_id = $r
        ";
        $results = sqlQuery( $query );

    } else {

        $query = "
          INSERT INTO
            `char_reputations` (char_id, reputation_id, value)
          VALUES
            ('" . $c_obj->c[ 'id' ] . "', '$r', '$quantity')
        ";
        $results = sqlQuery( $query );

    }

    $quantity = floor( $quantity / 1000 );

    unset( $_SESSION[ 'reputations' ] );

    return '<div class="reputation"><img src="images/recv_rep.gif"> ' .
           'You receive ' . $quantity .
           ' reputation with ' . getReputationName( $r ) . '.<br>' .
           '<font size="-2">' .
           renderReputation( $c_obj, $c_obj->c[ 'reputations' ][ $r ], FALSE ) .
           '</font></div>';
}

?>