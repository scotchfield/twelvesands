<?

require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/constants.php';

class CharInventory {
    function CharInventory( $c ) {
        $c = intval( $c );

        if ( ! isset( $_SESSION[ 'inventory_obj' ] ) ) {
            $this->artifacts = array();
            $this->total_quantity = 0;

            $query = "
              SELECT
                a.id, c.m_enc, SUM(c.quantity) as q
              FROM
                char_artifacts AS c, artifacts AS a
              WHERE
                c.char_id = '$c' AND c.artifact_id = a.id
              GROUP BY
                a.id, c.m_enc
              ORDER BY
                a.name ASC
            ";
            $results = sqlQuery( $query );

            if ( $results ) {
                while ( $a = $results->fetch_assoc() ) {
                    if ( isset( $this->artifacts[ $a[ 'id' ] ] ) ) {
                        $this->artifacts[ $a[ 'id' ] ][ $a[ 'm_enc' ] ] = $a[ 'q' ];
                    } else {
                        $this->artifacts[ $a[ 'id' ] ] = array( $a[ 'm_enc' ] => $a[ 'q' ] );
                    }
                    $this->total_quantity += $a[ 'q' ];
                }
            }

            $_SESSION[ 'inventory_obj' ] = $this->artifacts;
            $_SESSION[ 'inventory_total_quantity' ] = $this->total_quantity;
        } else {
            $this->artifacts = $_SESSION[ 'inventory_obj' ];
            if ( isset( $_SESSION[ 'inventory_total_quantity' ] ) ) {
                $this->total_quantity = $_SESSION[ 'inventory_total_quantity' ];
            } else {
                $this->total_quantity = 0;
            }
        }
    }

    function getInventory() {
        return $this->artifacts;
    }
    function getTotalQuantity() {
        return $this->total_quantity;
    }
}

class ArtifactAwarder {
    function ArtifactAwarder() {
        $this->artifacts = array();
    }

    function awardArtifact( $artifact_id, $quantity, $m_enc = 0 ) {
        $i = intval( $artifact_id );
        $q = intval( $quantity );
        $m = intval( $m_enc );

        if ( ! isset( $this->artifacts[ $i ] ) ) {
            $this->artifacts[ $i ] = array();
        }
        $this->artifacts[ $i ][ $m ] += $q;
    }

    function clearArtifactAwards() {
        $this->artifacts = array();
    }

    function save( $c_id ) {
        if ( count( $this->artifacts ) < 1 ) {
            return FALSE;
        }

        $c_id = intval( $c_id );

        $updates = array();
        foreach ( $this->artifacts as $k => $a ) {
            foreach ( $a as $m => $q ) {
                if ( $q != 0 ) {
                    $updates[] = "('" . $c_id . "', '$k', '$q', '$m')";
                }
            }
        }

        if ( count( $updates ) > 0 ) {
            $query = "
              INSERT INTO
                `char_artifacts` (char_id, artifact_id, quantity, m_enc)
              VALUES
            " . join( ',', $updates );
            $results = sqlQuery( $query );
        }

        unset( $_SESSION[ 'inventory_obj' ] );
        $this->artifacts = array();

        return TRUE;
    }
}


function awardArtifact( $c_obj, $a, $quantity, $m_enc = 0 ) {
    echo awardArtifactString( $c_obj, $a, $quantity, $m_enc );
}

function awardArtifactString( $c_obj, $a, $quantity, $m_enc = 0 ) {
    $ret_str = '';

    if ( $a[ 'id' ] == 0 ) {

        $new_gold = $c_obj->c[ 'gold' ] + $quantity;
        $c_obj->setGold( $new_gold );

        $ret_str = '<p><img src="images/recv_gold.gif"> ' .
            'You receive ' . $quantity . ' gold.</p>';

    } else {

        $c_obj->awardArtifact( $a[ 'id' ], $quantity, $m_enc );
        $c_obj->c[ 'inventory_obj' ]->artifacts[ $a[ 'id' ] ] = array( $m_enc =>
            $quantity + getCharArtifactQuantity( $c_obj, $a[ 'id' ], $m_enc ) );

        $armourArray = array(
            sg_artifact_armour_head,
            sg_artifact_armour_chest,
            sg_artifact_armour_legs,
            sg_artifact_armour_neck,
            sg_artifact_armour_trinket,
            sg_artifact_armour_hands,
            sg_artifact_armour_wrists,
            sg_artifact_armour_belt,
            sg_artifact_armour_boots,
            sg_artifact_armour_ring);

        $m_st = '';
        if ( $m_enc > 0 ) {
            $m_st = '&m=' . $m_enc;
        }

        $ret_str = '<p><img src="images/recv_artifact.gif"> ' .
            'You receive ' . $quantity . ' ' .
            renderArtifactStr( $a, $quantity ) . '.';
        if ( $a[ 'type' ] == sg_artifact_usable ) {
            $ret_str = $ret_str . '  <font size="-2">(' .
                '<a href="inventory.php?a=u&i=' . $a[ 'id' ] . '">use</a>)</font>';
        } elseif ( $a[ 'type' ] == sg_artifact_weapon ) {
            $ret_str = $ret_str . '  <font size="-2">(' .
                '<a href="char.php?a=a&i=' . $a[ 'id' ] . $m_st .
                '">equip</a>)</font>';
        } elseif ( in_array( $a[ 'type' ], $armourArray ) ) {
            $ret_str = $ret_str . ' <font size="-2">(' .
                '<a href="char.php?a=aa&i=' . $a[ 'id' ] .
                '&t=' . $a[ 'type' ] . $m_st . '">equip</a>)</font>';
        }

        $num_artifacts = getCharArtifactQuantity( $c_obj, $a[ 'id' ] );
        foreach ( $c_obj->c[ 'quests' ] as $quest ) {
            if ( ( sg_quest_in_progress == $quest[ 'status' ] ) ||
                 ( 1 == $quest[ 'repeatable' ] ) ) {
                $qa = 0; $qn = 0;
                if ( $quest[ 'quest_artifact1' ] == $a[ 'id' ] ) {
                    $qa = $quest[ 'quest_artifact1' ];
                    $qn = $quest[ 'quest_quantity1' ];
                } elseif ( $quest[ 'quest_artifact2' ] == $a[ 'id' ] ) {
                    $qa = $quest[ 'quest_artifact2' ];
                    $qn = $quest[ 'quest_quantity2' ];
                } elseif ( $quest[ 'quest_artifact3' ] == $a[ 'id' ] ) {
                    $qa = $quest[ 'quest_artifact3' ];
                    $qn = $quest[ 'quest_quantity3' ];
                }
                if ( ( $qa > 0 ) && ( $num_artifacts <= $qn ) ) {
                    $ret_str = $ret_str . '<br><font size="-2">Quest update: ' .
                        $quantity . 'x ' . $a[ 'name' ] . ' received.  (' .
                        $num_artifacts . '/' . $qn . ')' .
                        '<br><a href="talk.php?t=' . $quest[ 'npc_id' ] . '&q=' .
                        $quest[ 'id' ] . '">' . $quest[ 'name' ] . '</a></font>';
                }
            }
        }

        $ret_str = $ret_str . '</p>';
    }

    unset( $_SESSION[ 'inventory_obj' ] );

    return $ret_str;
}

function removeArtifact( $c_obj, $a, $quantity, $m_enc = 0 ) {
    $query = "
      SELECT
        char_id, artifact_id, SUM(quantity) AS quantity
      FROM
        `char_artifacts`
      WHERE
        char_id = " . $c_obj->c['id'] . " AND
        artifact_id = $a AND
        m_enc = $m_enc
      GROUP BY
        artifact_id
    ";
    $results = sqlQuery( $query );

    $artifact_info = $results->fetch_assoc();
    $artifact_info[ 'quantity' ] = $artifact_info[ 'quantity' ] - $quantity;
    if ( $artifact_info[ 'quantity' ] < 0 ) {
        $artifact_info[ 'quantity' ] = 0;
    }

    $query = "
      DELETE FROM
        `char_artifacts`
      WHERE
        char_id = " . $c_obj->c[ 'id' ] . " AND
        artifact_id = $a AND
        m_enc = $m_enc
    ";
    $results = sqlQuery( $query );

    if ( $artifact_info[ 'quantity' ] > 0 ) {
        //$c_obj->awardArtifact( $a, $artifact_info[ 'quantity' ], $m_enc );
        $query = "
          INSERT INTO
            `char_artifacts`
            (char_id, artifact_id, m_enc, quantity)
          VALUES
            (" . $c_obj->c[ 'id' ] . ", $a, $m_enc, " . $artifact_info[ 'quantity' ] . ")
        ";
        $results = sqlQuery( $query );
    }

    $c_obj->c[ 'inventory_obj' ]->artifacts[ $a ] =
        array( $m_enc => $artifact_info[ 'quantity' ] );
    unset( $_SESSION[ 'inventory_obj' ] );

    return TRUE;
}

function hasArtifact( $c_obj, $artifact_id,
                      $m_enc = 0, $get_artifact = TRUE ) {
    if ( 0 == $artifact_id ) { return TRUE; }

    if ( array_key_exists( $artifact_id, $c_obj->c[ 'inventory_obj' ]->artifacts ) ) {
        if ( array_key_exists(
                $m_enc, $c_obj->c[ 'inventory_obj' ]->artifacts[ $artifact_id ] ) ) {
            if ( TRUE == $get_artifact ) {
              $artifact = getArtifact( $artifact_id );
            } else {
              $artifact = array();
            }
            $artifact[ 'quantity' ] =
                $c_obj->c[ 'inventory_obj' ]->artifacts[ $artifact_id ][ $m_enc ];
            $artifact[ 'm_enc' ] = $m_enc;
            return $artifact;
        }
    }

    return FALSE;
}

function getCharArtifacts( $char_id ) {
    $c_id = intval( $char_id );
    $artifacts = array();

    $query = "
      SELECT
        a.*, c.m_enc, SUM(c.quantity) as quantity
      FROM
        char_artifacts AS c, artifacts AS a
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

?>