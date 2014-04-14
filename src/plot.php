<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/plots.php';
require_once sg_base_path . 'include/validate.php'; 

$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$plot_id = getGetInt( 'i', 0 );
$plot = getPlot( $plot_id );
if ( $plot == FALSE ) {
    header( 'Location: char.php' );
    exit;
}

$can_view = FALSE;
if ( $plot[ 'char_id' ] == $char_obj->c[ 'id' ] ) {
    $can_view = TRUE;

    if ( array_key_exists( 'e', $_POST ) ) {
        updatePlot( $plot_id, $_POST[ 'title' ], $_POST[ 'description' ] );
        $plot = getPlot( $plot_id );
    }
}

$render_obj = array();
$u_id = getGetInt( 'u', 0 );
if ( $can_view == TRUE ) {
    $u_id = getGetInt( 'u', 0 );
    switch ( $u_id ) {
        case 1:
            if ( ! getFlagBit( $char_obj, sg_flag_plot_used, 0 ) ) {
                $render_obj[] = '<p class="tip">You pick an orange from the ' .
                    'tree.  Yum!</p>';
                $artifact = getArtifact( 869 );
                $render_obj[] = awardArtifactString( $char_obj, $artifact, 1, 0 );
                $char_obj->enableFlagBit( sg_flag_plot_used, 0 );
            }
            break;
        case 2:
            if ( ! getFlagBit( $char_obj, sg_flag_plot_used, 1 ) ) {
                $render_obj[] = '<p class="tip">You pick an apple from the ' .
                    'tree.  Yum!</p>';
                $artifact = getArtifact( 870 );
                $render_obj[] = awardArtifactString( $char_obj, $artifact, 1, 0 );
                $char_obj->enableFlagBit( sg_flag_plot_used, 1 );
            }
            break;

    }
}
if ( TRUE ) {  // just for formatting, these are things anyone can do on your plot
    switch ( $u_id ) {
        case 3:
            if ( array_key_exists( 'message', $_POST ) ) {
                addPlotGuestbook( $plot_id, $char_obj->c[ 'id' ], $_POST[ 'message' ] );
                $render_obj[] = '<p class="tip">Message added!</p>';
            }
            $book_obj = getPlotGuestbooks( $plot_id );
            $render_obj[] = '<p><b>Guestbook Entries:</b></p>';
            if ( count( $book_obj ) == 0 ) {
                $render_obj[] = '<p>No entries yet!</p>';
            } else {
                foreach ( $book_obj as $book ) {
                    $render_obj[] = '<p><a href="char.php?i=' . $book[ 'char_id' ] . '">' .
                        $book[ 'char_name' ] . '</a>: ' . $book[ 'message' ] . '</p>';
                }
            }
            $render_obj[] = '<form action="plot.php?i=' . $plot_id . '&u=3" method="post">' .
                '<p><b>Leave a message in the guestbook:</b><br>' .
                '<textarea style="width: 400px" name="message"></textarea><br>' .
                '<input type="submit" value="Leave a message"></p></form>';
        break;
    }
}


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss( $char_obj->c ); ?>
</head>
<body>

<? renderPopupText(); ?>

<div class="container">

<?

$ts_no_keypress = TRUE;
require '_header.php';


if ( ( $plot[ 'char_id' ] == $char_obj->c[ 'id' ] ) &&
     ( array_key_exists( 'e', $_GET ) ) ) {
    echo '<h3>Edit Plot:</h3>
      <form action="plot.php?i=' . $plot_id . '" method="post">
      <p><b>Plot title:</b><br>
         <input type="text" name="title" size="40" value="' .
         $plot[ 'title' ] . '">
      </p><p><b>Description:</b><br>
         <textarea name="description" cols="40" rows="5">' .
         $plot[ 'description' ] . '</textarea>
      </p><p><input type="submit" value="Submit" name="e"></p>
      </form>';
}

echo '<p><font size="+2">' . $plot[ 'title' ] . '</font><br>';
switch ( $plot[ 'plot_zone' ] ) {
case 0: echo '<i>Player Housing</i>'; break;
case 1: echo '<i>Starfall Properties</i> ' .
    '<font size="-2">(<a href="main.php?z=140">visit</a>)</font>'; break;
}
echo '<br>Owner: <a href="char.php?i=' . $plot[ 'char_id' ] . '">' .
     $plot[ 'char_name' ] . '</a>';
if ( $plot[ 'char_id' ] == $char_obj->c[ 'id' ] ) {
  echo '<br><font size="-2">(<a href="plot.php?i=' .
       $plot[ 'id' ] . '&e">edit</a>)</font>';
}
echo '</p>';
echo '<p><i>' . $plot[ 'description' ] . '</i></p>';

foreach ( $render_obj as $x ) {
    echo $x;
}

if ( $can_view == FALSE ) {

    echo '<p>You are unable to look inside this property!</p>';

    echo '<h3>Observed Installations</h3><ul>';
    if ( getPlotFlagBit( $plot, sg_plotflag_installed, 0 ) ) {
        echo '<li>Orange Tree</li>';
    }
    if ( getPlotFlagBit( $plot, sg_plotflag_installed, 1 ) ) {
        echo '<li>Apple Tree</li>';
    }
    if ( getPlotFlagBit( $plot, sg_plotflag_installed, 2 ) ) {
        echo '<li>Guestbook <font size="-2">(<a href="plot.php?i=' .
             $plot[ 'id' ] . '&u=3">' .
             'View the Guestbook</a>)</font></li>';
    }
    echo '</ul>';

} else {

    echo '<h3>Ornamental</h3>';
    if ( getPlotFlagBit( $plot, sg_plotflag_installed, 0 ) ) {
        echo '<p><b>Orange Tree</b>: ' .
             'The great tree is covered in delicious oranges.<br>';
        if ( ! getFlagBit( $char_obj, sg_flag_plot_used, 0 ) ) {
            echo '<font size="-2">(<a href="plot.php?i=' . $plot[ 'id' ] . '&u=1">' .
                 'Pick an orange</a>)</font>';
        }
        echo '</p>';
    }
    if ( getPlotFlagBit( $plot, sg_plotflag_installed, 1 ) ) {
        echo '<p><b>Apple Tree</b>: ' .
             'Bright red apples dot the branches of this large tree.<br>';
        if ( ! getFlagBit( $char_obj, sg_flag_plot_used, 1 ) ) {
            echo '<font size="-2">(<a href="plot.php?i=' . $plot[ 'id' ] . '&u=2">' .
                 'Pick an apple</a>)</font>';
        }
        echo '</p>';
    }
    if ( getPlotFlagBit( $plot, sg_plotflag_installed, 2 ) ) {
        echo '<p><b>Guestbook</b>: ' .
             'A beautiful book that holds records of visitors.<br>' .
             '<font size="-2">(<a href="plot.php?i=' . $plot[ 'id' ] . '&u=3">' .
             'View the Guestbook</a>)</font></p>';
    }

}

//debugPrint( $plot );

require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>