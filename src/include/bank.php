<?

function getBankArtifactQuantity( $char_id, $artifact_id ) {
    $c_id = intval( $char_id );
    $a_id = intval( $artifact_id );

    $query = "
      SELECT
        a.*, c.m_enc, SUM(c.quantity) as quantity
      FROM
        char_bank AS c, artifacts AS a
      WHERE
        c.char_id = '$c_id' AND
        c.artifact_id = a.id AND
        a.id = $a_id
      GROUP BY
        a.id
    ";
    $results = sqlQuery( $query );
    if ( ! $results ) { return 0; }

    $artifact = $results->fetch_assoc();
    return $artifact[ 'quantity' ];
}

function getBankArtifacts( $char_id ) {
    $c_id = intval( $char_id );
    $artifacts = array();

    $query = "
      SELECT
        a.*, c.m_enc, SUM(c.quantity) as quantity
      FROM
        char_bank AS c, artifacts AS a
      WHERE
        c.char_id = '$c_id' AND c.artifact_id = a.id
      GROUP BY
        a.id, c.m_enc
      ORDER BY
        a.name ASC, c.m_enc ASC
    ";
    $results = sqlQuery( $query );

    if ( $results ) {
        while ( $artifact = $results->fetch_assoc() ) {
            $artifact[ 'name' ] = fixStr( $artifact[ 'name' ] );
            $artifact[ 'plural_name' ] = fixStr( $artifact[ 'plural_name' ] );
            $artifact[ 'text' ] = fixStr( $artifact[ 'text' ] );
            $artifact[ 'o_name' ] = fixStr( $artifact[ 'o_name' ] );
            $artifacts[] = $artifact;
        }
    }

    return $artifacts;
}

function getBankArtifactsArray( $char_id, $a_obj ) {
    $c_id = intval( $char_id );
    $artifacts = array();
    $get_obj = array();
    foreach ( $a_obj as $a ) {
      $a_val = explode( ',', $a, 2 );
      $get_obj[] = 'artifact_id=' . intval( $a_val[ 0 ] ) . ' AND m_enc=' .
          intval( $a_val[ 1 ] );
    }
    $a_st = '(' . join( ')OR(', $get_obj ) . ')';

    $query = "
      SELECT
        artifact_id, m_enc, SUM(quantity) AS quantity
      FROM
        char_bank
      WHERE
        char_id = '$c_id' AND ($a_st)
      GROUP BY
        artifact_id, m_enc
    ";
    $results = sqlQuery( $query );

    if ( $results ) {
        while ( $artifact = $results->fetch_assoc() ) {
            if ( isset( $artifacts[ $artifact[ 'artifact_id' ] ] ) ) {
                $artifacts[ $artifact[ 'artifact_id' ] ][ $artifact[ 'm_enc' ] ] =
                    $artifact[ 'quantity' ];
            } else {
                $artifacts[ $artifact[ 'artifact_id' ] ] = array(
                    $artifact[ 'm_enc' ] => $artifact[ 'quantity' ] );
            }
        }
    }

    return $artifacts;
}

function addBankArtifacts( $c_obj, $a_obj ) {
    $c = $c_obj->c[ 'id' ];
    $query_obj = array();
    foreach ( $a_obj as $k => $a ) {
        foreach ( $a as $m => $q ) {
            if ( intval( $q ) > 0 ) {
                $query_obj[] = '(' . $c . ', ' . intval( $k ) . ', ' . intval( $q ) .
                    ', ' . intval( $m ) . ')';
            }
        }
    }
    $a_st = join( ',', $query_obj );

    $query = "
      INSERT INTO
        `char_bank`
        (char_id, artifact_id, quantity, m_enc)
      VALUES
        $a_st
    ";
    $results = sqlQuery( $query );
}

function setBankArtifacts( $c_obj, $a_obj ) {
    $c = $c_obj->c[ 'id' ];

    $a_ids = array();
    $delete_obj = array();
    foreach ( $a_obj as $k => $a ) {
        foreach ( $a as $m => $q ) {
            if ( isset( $a_ids[ $k ] ) ) {
                $a_ids[ $k ][ $m ] = $q;
            } else {
                $a_ids[ $k ] = array( $m => $q );
            }
            $delete_obj[] = 'artifact_id=' . $k . ' AND m_enc=' . $m;
        }
    }

    $delete_st = '(' . join( ')OR(', $delete_obj ) . ')';

    $query = "
      DELETE FROM
        `char_bank`
      WHERE
        char_id = $c AND ($delete_st)
    ";
    sqlQuery( $query );

    addBankArtifacts( $c_obj, $a_obj );
}


function getCharArtifactsArray( $char_id, $a_obj ) {
    $c_id = intval( $char_id );
    $artifacts = array();
    $get_obj = array();
    foreach ( $a_obj as $a ) {
      $a_val = explode( ',', $a, 2 );
      $get_obj[] = 'artifact_id=' . intval( $a_val[ 0 ] ) . ' AND m_enc=' .
          intval( $a_val[ 1 ] );
    }
    $a_st = '(' . join( ')OR(', $get_obj ) . ')';

    $query = "
      SELECT
        artifact_id, m_enc, SUM(quantity) AS quantity
      FROM
        char_artifacts
      WHERE
        char_id = '$c_id' AND ($a_st)
      GROUP BY
        artifact_id, m_enc
    ";
    $results = sqlQuery( $query );

    if ( $results ) {
        while ( $artifact = $results->fetch_assoc() ) {
            if ( isset( $artifacts[ $artifact[ 'artifact_id' ] ] ) ) {
                $artifacts[ $artifact[ 'artifact_id' ] ][ $artifact[ 'm_enc' ] ] =
                    $artifact[ 'quantity' ];
            } else {
                $artifacts[ $artifact[ 'artifact_id' ] ] = array(
                    $artifact[ 'm_enc' ] => $artifact[ 'quantity' ] );
            }
        }
    }

    return $artifacts;
}

function setCharArtifacts( $c_obj, $a_obj ) {
    $c = $c_obj->c[ 'id' ];

    $a_ids = array();
    foreach ( $a_obj as $k => $a ) {
        foreach ( $a as $m => $q ) {
            if ( isset( $a_ids[ $k ] ) ) {
                $a_ids[ $k ][ $m ] = $q;
            } else {
                $a_ids[ $k ] = array( $m => $q );
            }
            $delete_obj[] = 'artifact_id=' . $k . ' AND m_enc=' . $m;
        }
    }

    $delete_st = '(' . join( ')OR(', $delete_obj ) . ')';

    $query = "
      DELETE FROM
        `char_artifacts`
      WHERE
        char_id = $c AND ($delete_st)
    ";
    sqlQuery( $query );

    foreach ( $a_obj as $k => $a ) {
        foreach ( $a as $m => $q ) {
          $c_obj->awardArtifact( $k, $q, $m );
        }
    }
}

function updateBankDepositObjs( $c_obj, $n_base, $a_obj,
                                &$bank_obj, &$new_award_obj, $log_obj ) {

    foreach ( $a_obj as $k => $a ) {
        foreach ( $a as $m => $v ) {
            if ( $v > 0 ) {
                $n = $n_base;
                if ( $n < 0 ) { $n = 0; }
                $n = min( $n, $v );

                if ( isset( $bank_obj[ $k ] ) ) {
                    $bank_obj[ $k ][ $m ] = $n;
                } else {
                    $bank_obj[ $k ] = array( $m => $n );
                }
                if ( isset( $new_award_obj[ $k ] ) ) {
                    $new_award_obj[ $k ][ $m ] = $v - $bank_obj[ $k ][ $m ];
                } else {
                    $new_award_obj[ $k ] = array( $m => $v - $bank_obj[ $k ][ $m ] );
                }

                $log_obj->addLog( $c_obj->c, sg_log_bank_deposit,
                                  $k, $bank_obj[ $k ][ $m ], 0, 0 );
            }
        }
    }

}

function updateBankAwardObjs( $c_obj, $n_base, $a_obj,
                              &$award_obj, &$new_bank_obj,
                              $max_wd, $log_obj ) {

    foreach ( $a_obj as $k => $a ) {
        foreach ( $a as $m => $v ) {
            if ( ( $v > 0 ) && ( ( $max_wd == -1 ) || ( $max_wd > 0 ) ) ) {
                $n = $n_base;
                if ( $n < 0 ) { $n = 0; }
                if ( $max_wd == -1 ) {
                    $n = min( $n, $v );
                } else {
                    $n = min( $n, $v, $max_wd );
                }

                if ( isset( $award_obj[ $k ] ) ) {
                    $award_obj[ $k ][ $m ] = $n;
                } else {
                    $award_obj[ $k ] = array( $m => $n );
                }
                if ( isset( $new_bank_obj[ $k ] ) ) {
                    $new_bank_obj[ $k ][ $m ] = $v - $award_obj[ $k ][ $m ];
                } else {
                    $new_bank_obj[ $k ] = array( $m => $v - $award_obj[ $k ][ $m ] );
                }

                $log_obj->addLog( $c_obj->c, sg_log_bank_withdraw,
                                  $k, $award_obj[ $k ][ $m ], 0, 0 );
                if ( $max_wd > 0 ) {
                    $c_obj->addFlag( sg_flag_bank_withdrawals,
                        getFlagValue( $c_obj, sg_flag_bank_withdrawals ) +
                            $award_obj[ $k ][ $m ] );
                    $max_wd -= $award_obj[ $k ][ $m ];
                }
            }
        }
    }
}

?>