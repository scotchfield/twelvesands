<?

require_once 'include/core.php';

require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/auctions.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/user.php';

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss( $char_obj->c ); ?>
</head>
<body>

<?

function setHtmlEntities( $st ) {
    $st = str_replace( '&lt;', '<', $st );
    $st = str_replace( '&gt;', '>', $st );
    return $st;
}

$a_id = getGetInt( 'a', 0 );
$artifact = getArtifactByDesc( $a_id );

$dev = getGetInt( 'dev', 0 );

if ( FALSE == $artifact ) {
    echo '<p>You don\'t know about that artifact!</p>';
} else {
    echo setHtmlEntities( getArtifactHoverStr( $artifact ) );
    if ( 1 == $dev ) {
        echo '<font color="#C0C0C0">Artifact ID: ' . $artifact[ 'id' ] . '</font>';
    }
    if ( $artifact[ 'maker_id' ] > 0 ) {
        $m_name = getCharNameById( $artifact[ 'maker_id' ] );
        echo '<p><font size="-2">Initial design: ' . $m_name . '</p>';
    }
}

?>

</body>
</html>
