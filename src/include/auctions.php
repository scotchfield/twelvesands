<?

function getAuctions( $char_id, $char_name,
                      $artifact_id, $artifact_name, $artifact_type,
                      $start_pos, $limit, $sort_type, $auction_type ) {
    $where_clause = FALSE;
    $where_sql = array();
    $c_id = esc( $char_id );
    $c_name = esc( $char_name );
    $a_id = esc( $artifact_id );
    $a_name = esc( $artifact_name );
    $a_type = esc( $artifact_type );
    $s_pos = intval( $start_pos );
    $lim = esc( $limit );
    $order_sql = '';
    $auc_type = esc( $auction_type );

    if ( sg_auctionsort_time == $sort_type ) {
        $order_sql = ' ORDER BY created DESC ';
    } elseif ( sg_auctionsort_cost == $sort_type ) {
        $order_sql = ' ORDER BY cost ASC ';
    } elseif ( sg_auctionsort_charname == $sort_type ) {
        $order_sql = ' ORDER BY char_name ASC ';
    } elseif ( sg_auctionsort_artifactname == $sort_type ) {
        $order_sql = ' ORDER BY artifact_name ASC ';
    } else {
        $order_sql = '  ';
    }

    $where_sql[] = "auction_type = '$auc_type'";

    if ( NULL != $char_id ) {
        $where_sql[] = "char_id = '$c_id'";
    } elseif ( NULL != $char_name ) {
        $where_sql[] = "char_name LIKE '%$c_name%'";
    } elseif ( NULL != $artifact_id ) {
        $where_sql[] = "artifact_id = '$a_id'";
    } elseif ( NULL != $artifact_name ) {
        $where_sql[] = "artifact_name LIKE '%$a_name%'";
    } elseif ( NULL != $artifact_type ) {
        $where_sql[] = "artifact_type = '$a_type'";
    }

    $query = "
      SELECT
        *
      FROM
        `auctions`";

    $where_list = join( ' AND ', $where_sql );

    $query = "$query
      WHERE $where_list
      $order_sql
      LIMIT $s_pos, $lim
    ";

    $results = sqlQuery( $query );
    $auctions = array();
    if ( ! $results ) { return $auctions; }

    while ( $a = $results->fetch_assoc() ) {
        $a[ 'artifact_name' ] = utf8_encode( $a[ 'artifact_name' ] );
        $auctions[ $a[ 'id' ] ] = $a;
    }

    return $auctions;
}

function getAuction( $id, $auction_type ) {
    $i = esc( $id );
    $auc_type = esc( $auction_type );

    $query = "
      SELECT
        *
      FROM
        `auctions`
      WHERE
        id = $i AND auction_type = '$auc_type'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $auction = $results->fetch_assoc();
    return $auction;
}

function addAuction( $char_id, $char_name,
                     $artifact_id, $artifact_name, $artifact_type,
                     $quantity, $m_enc, $total_cost, $auction_type ) {
    $c_id = intval( $char_id );
    $c_name = esc( $char_name );
    $a_id = intval( $artifact_id );
    $a_name = esc( $artifact_name );
    $a_type = intval( $artifact_type );
    $q = intval( $quantity );
    $m_enc = intval( $m_enc );
    $cost = intval( $total_cost );
    $time = time();
    $auc_type = intval( $auction_type );

    $query = "
      INSERT INTO
        `auctions`
        (char_id, char_name, artifact_id, artifact_name, artifact_type,
         quantity, m_enc, cost, created, auction_type)
      VALUES
        ('$c_id', '$c_name', '$a_id', '$a_name', '$a_type',
         '$q', '$m_enc', '$cost', '$time', '$auc_type')
    ";
    $results = sqlQuery( $query );
}

function deleteAuction( $id ) {
    $i = esc( $id );

    $query = "
      DELETE FROM
        `auctions`
      WHERE
        id = '$i'
    ";

    $results = sqlQuery( $query );

    return TRUE;
}

function getAuctionCount( $char_id, $auction_type ) {
    $i = esc( $char_id );
    $auc_type = esc( $auction_type );
 
    $query = "
      SELECT
        count(*) AS auction_count
      FROM
        `auctions`
      WHERE
        char_id = $i AND auction_type = '$auc_type'
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $auction = $results->fetch_assoc();
    return $auction[ 'auction_count' ];
}

function getAuctionBidArtifacts() {
    // TODO: hardcoded auction bid list, move this to a variable path
    include '/home/swrittenb/ts_util/artifacts/auction_bid_list.inc';

    return $auction_bid_list;
}

?>