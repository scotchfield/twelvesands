<?

function getZoneSql( $zone_id ) {
    $z = intval( $zone_id );

    $query = "
      SELECT
        *
      FROM
        `zones`
      WHERE
        id = '$z'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $zone = $results->fetch_assoc();
    $zone[ 'name' ] = fixStr( $zone[ 'name' ] );
    if ( ! isset( $zone[ 'parent_name' ] ) ) {
        $zone[ 'parent_name' ] = '';
    }
    if ( ! isset( $zone[ 'description' ] ) ) {
        $zone[ 'description' ] = '';
    }
    $zone[ 'parent_name' ] = fixStr( $zone[ 'parent_name' ] );
    $zone[ 'description' ] = fixStr( $zone[ 'description' ] );

    $zone[ 'npcs' ] = array();
    $zone[ 'children' ] = array();

    $query = "
      SELECT
        id, name, description, zone
      FROM
        `npcs`
      WHERE
        zone = '$z'
    ";

    $results = sqlQuery( $query );

    if ( $results ) {
        while ( $npc = $results->fetch_assoc() ) {
            $npc[ 'name' ] = fixStr( $npc[ 'name' ] );
            $npc[ 'description' ] = fixStr( $npc[ 'description' ] );
            $zone[ 'npcs' ][ $npc[ 'id' ] ] = $npc;
        }
    }

    $query = "
      SELECT
        zt.source_zone, zt.dest_zone, z.*
      FROM
        zone_transitions AS zt, zones AS z
      WHERE
        z.id = zt.dest_zone AND source_zone = $z
      UNION
      SELECT
        zt.source_zone, zt.dest_zone, z.*
      FROM
        zone_transitions AS zt, zones AS z
      WHERE
        z.id = zt.source_zone AND dest_zone = $z
      ORDER BY ui_order ASC, name ASC
    ";
    $child_results = sqlQuery( $query );

    if ( $child_results ) {
        while ( $zc = $results->fetch_assoc() ) {
            $zc[ 'name' ] = fixStr( $zc[ 'name' ] );
            $zc[ 'description' ] = fixStr( $zc[ 'description' ] );
            $zone[ 'children' ][ $zc[ 'id' ] ] = $zc;
        }
    }

    return $zone;
}

function getZone( $zone_id ) {
    $z = intval( $zone_id );

    if ( ( isset( $_SESSION[ 'zone_cache' ] ) ) &&
         ( array_key_exists( $z, $_SESSION[ 'zone_cache' ] ) ) ) {
        $_SESSION[ 'zone_cache' ][ $z ][ 'cached' ] = time();
        return $_SESSION[ 'zone_cache' ][ $z ];
    }

    $zone = getZoneSql( $z );

    if ( ( $zone == FALSE ) || ( ( $zone[ 'dev' ] == 1 ) && ( sg_debug != 1 ) ) ) {
        return FALSE;
    } else {
        if ( ( ! isset( $_SESSION[ 'zone_cache' ] ) ) ||
             ( count( $_SESSION[ 'zone_cache' ] ) > 8 ) ) {
            $_SESSION[ 'zone_cache' ] = array();
        }

        $_SESSION[ 'zone_cache' ][ $z ] = $zone;
        return $zone;
    }
}

function getZoneArtifacts( $zone_id ) {
    $z = intval( $zone_id );

    $query = "
      SELECT
        a.*, za.id AS row_id
      FROM
        `artifacts` AS a, `zone_artifacts` AS za
      WHERE
        za.zone_id = '$z' AND a.id = za.artifact_id
      ORDER BY
        a.min_level ASC, a.name ASC
    ";

    $zone_artifacts = array();

    $results = sqlQuery( $query );
    if ( ! $results ) { return $zone_artifacts; }

    while ( $za = $results->fetch_assoc() ) {
        $za[ 'name' ] = fixStr( $za[ 'name' ] );
        $za[ 'plural_name' ] = fixStr( $za[ 'plural_name' ] );
        $za[ 'text' ] = fixStr( $za[ 'text' ] );

        $zone_artifacts[ $za[ 'row_id' ] ] = $za;
    }

    return $zone_artifacts;
}

function getStoreArtifacts( $zone_id ) {
    $z = intval( $zone_id );

    $query = "
      SELECT
        a.*, sa.id AS row_id, sa.gold_cost,
        sa.artifact_cost_1, sa.artifact_quantity_1,
        sa.artifact_cost_2, sa.artifact_quantity_2,
        sa.artifact_cost_3, sa.artifact_quantity_3
      FROM
        `artifacts` AS a, `store_artifacts` AS sa
      WHERE
        sa.zone_id = '$z' AND a.id = sa.artifact_id
      ORDER BY
        a.name ASC, a.min_level ASC
    ";

    $zone_artifacts = array();

    $results = sqlQuery( $query );
    if ( ! $results ) { return $zone_artifacts; }

    while ( $za = $results->fetch_assoc() ) {
        $za[ 'name' ] = fixStr( $za[ 'name' ] );
        $za[ 'plural_name' ] = fixStr( $za[ 'plural_name' ] );
        $za[ 'text' ] = fixStr( $za[ 'text' ] );
        $za[ 'o_name' ] = fixStr( $za[ 'o_name' ] );
        $zone_artifacts[ $za[ 'row_id' ] ] = $za;
    }

    return $zone_artifacts;
}

function canVisitZone( $c_obj, $zone ) {
    if ( $zone[ 'min_level' ] <= $c_obj->c[ 'level' ] ) {
        if ( ( $zone[ 'artifact_required' ] == 0 ) ||
             ( ( $zone[ 'artifact_required' ] > 0 ) &&
               ( hasArtifact( $c_obj, $zone[ 'artifact_required' ] ) ) ) ) {
            return TRUE;
        }
    }
    return FALSE;
}

function getAllAvailableZones( $c_obj ) {
    $query = "SELECT * FROM `zones` WHERE dev = 0
        ORDER BY min_level ASC, name ASC";
    $obj = array();
    $results = sqlQuery( $query );
    if ( ! $results ) { return $obj; }

    while ( $zone = $results->fetch_assoc() ) {
        if ( ! canVisitZone( $c_obj, $zone) ) {
            continue;
        }
        $zone[ 'name' ] = fixStr( $zone[ 'name' ] );
        $zone[ 'parent_name' ] = fixStr( $zone[ 'parent_name' ] );
        $zone[ 'description' ] = fixStr( $zone[ 'description' ] );
        $obj[ $zone[ 'id' ] ] = $zone;
    }

    return $obj;
}

?>