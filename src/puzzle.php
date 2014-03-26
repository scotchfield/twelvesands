<?

require_once 'include/core.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/puzzles.php';
require_once sg_base_path . 'include/validate.php'; 

$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$log_obj = new Logger();

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

require '_header.php';
require '_charmenu.php';

$i = getGetStr( 'i', '0' );
$st = getGetStr( 's', '' );

$artifact = hasArtifact( $char_obj, $i );

if ( FALSE == $artifact ) {
    echo '<p>You don\'t have that artifact!</p>';
} elseif ( $char_obj->c[ 'level' ] < $artifact[ 'min_level' ] ) {
    echo '<p>Your level isn\'t high enough to use that artifact!</p>';
} elseif ( $artifact[ 'type' ] != sg_artifact_puzzle_1 ) {
    echo '<p>You can\'t do that!</p>';
} else {

    readPuzzle( $char_obj, $i );
    $puzzle = getPuzzleArray( $char_obj->c, $i );

    $already_solved = FALSE;
    if ( array_key_exists( $puzzle[ 'flag_id'], $char_obj->c[ 'flags' ] ) ) {
        if ( ( $char_obj->c[ 'flags' ][ $puzzle[ 'flag_id' ] ] &
             $puzzle[ 'flag_value' ] ) > 0 ) {
            $already_solved = TRUE;
        }
    }

    if ( $st == '' ) {

        $buff = getAttemptPuzzleBuff( $char_obj, $i );
        if ( FALSE == $buff ) {
            if ( FALSE == $already_solved ) {
?>
        <p>Guess the solution:<br>
        <form method="get">
        <input name="s" size="20" type="text">
        <input name="i" value="<?= $i ?>" type="hidden">
        <input value="Guess!" type="submit"></form></p>
        <p><font size="-1">Note: If you guess, and are incorrect, you must
          wait before trying again!</font></p>
<?
            }
        } else {
            if ( FALSE == $already_solved ) {
                echo '<p><font color="red">';
                if ( $buff[ 'id' ] == 63 ) {
                    echo 'You can only make a card guess every five minutes!<br>';
                }
                $now = time();
                echo renderTimeRemaining( $now, $buff[ 'expires' ] );
                echo ' left until you can take a guess at this card.' .
                     '</font></p>';
            }
        }

    } else {

        $buff = getAttemptPuzzleBuff( $char_obj, $i );
        if ( FALSE == $buff ) {

            if ( FALSE == $already_solved ) {
                $solve = solvePuzzle( $char_obj, $i, $st );
                if ( FALSE == $solve ) {
                    echo '<p><font color="red">Unfortunately your answer is not ' .
                         'correct.  Give it some time, and come back to ' .
                         'try again!</font></p>';
                    $log_obj->addLog( $char_obj->c, sg_log_sandstorm_wisdom_loss,
                                      $i, 0, 0, 0 );
                } else {
                    echo '<p><font color="blue">That\'s correct!  You\'ve solved ' .
                         'this card\'s puzzle!</font></p>';
                    $log_obj->addLog( $char_obj->c, sg_log_sandstorm_wisdom_win,
                                      $i, 0, 0, 0 );
                }
            }

        } else {
            echo '<p><font color="red">';
            $now = time();
            echo renderTimeRemaining( $now, $buff[ 'expires' ] );
            echo ' left until you can take another guess at this card.</font></p>';
        }

        echo '<p><a href="puzzle.php?i=' . $i .
             '">Go back to the card description</a></p>';

    }

}

echo '<p><a href="inventory.php">Back to your artifacts page</a></p>';

require '_footer.php';
$save = $char_obj->save();
$log_obj->save();

?>

</div>
</body>
</html>