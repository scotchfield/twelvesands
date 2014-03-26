<?

require_once 'include/core.php';

require_once sg_base_path . 'include/builders.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/validate.php'; 

$char_obj = new Char( $_SESSION[ 'c' ] );
forceCombatCheck( $char_obj );

$post_action = getPostInt( 'a', 0 );
$post_err = '';

if ( $post_action == 1 ) {
    $add_builder = TRUE;
    $valid_types = array( 1, 2, 3, 4, 5, 6, 7, 8, 9 );
    if ( ! in_array( intval( $_POST[ 'type' ]), $valid_types ) ) {
        $post_err = '<p class="tip">Your artifact type is invalid! ' .
            'Are you being sneaky?</p>';
        $add_builder = FALSE;
    }
    if ( ( $_POST[ 'title' ] == '' ) ||
         ( $_POST[ 'description' ] == '' ) ||
         ( $_POST[ 'value' ] == '' ) ) {
        $post_err = '<p class="tip">A required field is missing!</p>';
        $add_builder = FALSE;
    }
    $char_count = getCharBuilderCount( $char_obj );
    if ( $char_count >= 10 ) {
        $post_err = '<p class="tip">You can only have 10 active creations ' .
            'at a time!</p>';
        $add_builder = FALSE;
    }

    if ( $add_builder == TRUE ) {
        $b_obj = array(
            'type' => 1,
            'subtype' => $_POST[ 'type' ],
            'title' => $_POST[ 'title' ],
            'description' => $_POST[ 'description' ],
            'attack' => $_POST[ 'attack' ],
            'resistances' => $_POST[ 'resistances' ],
            'damage' => $_POST[ 'damage' ],
            'value' => $_POST[ 'value' ],
            'misc' => $_POST[ 'misc' ],
        );
        $b_id = addBuilder( $char_obj, $b_obj );
        header( 'Location: builder.php?v&i=' . $b_id );
        exit;
    } else {
        $_GET[ 'a' ] = 1;
    }
}

if ( array_key_exists( 'd', $_GET ) ) {
    $id = getGetInt( 'i', 0 );
    $action_id = getGetInt( 'action', 0 );
    if ( ( $id > 0 ) && ( $action_id == $char_obj->c[ 'action_id' ] ) ) {
        $b = getBuilder( $id );
        if ( $b != FALSE ) {
            zeroBuilderUserScore( $char_obj, $b[ 'id' ] );
            updateBuilderScore( $b[ 'id' ] );
            $char_obj->resetActionId();
            $char_obj->save();
        }
    }
    header( 'Location: builder.php?v&i=' . $id );
    exit;
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

$action = getGetInt( 'a', 0 );

if ( array_key_exists( 'v', $_GET ) ) {

    $id = getGetInt( 'i', 0 );
    if ( $id > 0 ) {
        $b = getBuilder( $id );
        if ( $b != FALSE ) {

            if ( array_key_exists( 'comment', $_POST ) ) {
                if ( ( array_key_exists( $char_obj->c[ 'user_id' ], $b[ 'voted' ] ) ) ||
                     ( $b[ 'user_id' ] == $char_obj->c[ 'user_id' ] ) ) {
                    $_POST[ 'score' ] = 0;
                }
                addBuilderComment( $char_obj, $id,
                  $_POST[ 'score' ], $_POST[ 'comment' ] );
                updateBuilderScore( $id );
                $b = getBuilder( $id );
            }

?>
<center><h3><?= $b[ 'title' ] ?></h3>
<table width="80%" border="0">
<tr>
  <td align="right">Created by:</td>
  <td align="left"><a href="char.php?i=<?= $b[ 'char_id' ] ?>"><?=
      $b[ 'char_name' ] ?></a></td>
</tr>
<tr>
  <td align="right">Artifact Type:</td>
  <td align="left"><?
            if ( $b[ 'type' ] == 1 ) {
                switch ( $b[ 'subtype' ] ) {
                case 1: echo 'Weapon'; break;
                case 2: echo 'Armour'; break;
                case 3: echo 'Usable Artifact'; break;
                case 4: echo 'Usable Combat'; break;
                case 5: echo 'Food'; break;
                case 6: echo 'Book'; break;
                case 7: echo 'Enchanting'; break;
                case 8: echo 'Rune'; break;
                case 9: echo 'Mount'; break;
                }
            }
?></td>
</tr>
<tr>
  <td align="right">Description:</td>
  <td align="left"><?= $b[ 'description' ] ?></td>
</tr>
<tr>
  <td align="right">Resistances or Bonuses:</td>
  <td align="left"><?= $b[ 'resistances' ] ?></td>
</tr>
<tr>
  <td align="right">Attack Text (if necessary):</td>
  <td align="left"><?= $b[ 'attack' ] ?></td>
</tr>
<tr>
  <td align="right">Damage (if necessary):</td>
  <td align="left"><?= $b[ 'damage' ] ?></td>
</tr>
<tr>
  <td align="right">Gold Value:</td>
  <td align="left"><?= $b[ 'value' ] ?></td>
</tr>
<tr>
  <td align="right">Other Information:</td>
  <td align="left"><?= $b[ 'misc' ] ?></td>
</tr>
<tr>
  <td align="right"><b>Current Score:</b></td>
  <td align="left"><b><?= $b[ 'score' ] ?> out of 5.0</b></td>
</tr>
</table></center>

<form action="builder.php?v&i=<?= $b[ 'id' ] ?>" method="post">
<?
            if ( ( $b[ 'user_id' ] != $char_obj->c[ 'user_id' ] ) &&
                 ( ! array_key_exists( $char_obj->c[ 'user_id' ], $b[ 'voted' ] ) ) ) {
?>
<p><b>Vote on this creation:</b></p>
<p>Score (out of 5): <select name="score">
<option value="0">No vote</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
</select></p>
<?
            } else {
?>
<p><b>Leave a comment on this creation:</b></p>
<?
            }
?>
<p>Comment:<br><textarea name="comment" style="width: 400px; height: 100px;"></textarea></p>
<input type="submit" value="Vote">
</form>
<?
            if ( array_key_exists( $char_obj->c[ 'user_id' ], $b[ 'voted' ] ) ) {
?>
<p><b>Want to remove your old vote score?</b>
<font size="-2">(<a href="builder.php?d&i=<?= $b[ 'id' ] ?>&action=<?=
    $char_obj->c[ 'action_id' ] ?>">remove score</a>)</font></p>
<?
            }

            echo '<p><b>Comments on this creation:</b></p>';
            if ( count( $b[ 'comments' ] ) == 0 ) {
                echo '<p>No comments yet!</p>';
            } else {
                foreach ( $b[ 'comments' ] as $comment ) {
                    echo '<p><a href="char.php?i=' . $comment[ 'char_id' ] . '">' .
                         $comment[ 'char_name' ] . '</a> ';
                    if ( $comment[ 'score' ] > 0 ) {
                        echo 'gave this a <b>' . $comment[ 'score' ] . '</b>.';
                    } else {
                        echo 'commented on this.';
                    }
                    if ( $comment[ 'comment' ] != '' ) {
                        echo '<br><i>' . $comment[ 'comment' ] . '</i>';
                    }
                    echo '</p>';
                }
            }
        }
    } else {
        echo '<h3>Character Creations</h3>';
        echo '<table width="100%" border="0">';
        echo '<tr><th>Type</th><th>Subtype</th><th>Name</th>' .
             '<th>Creator</th><th>Score</th></tr>';
        $b_obj = getAllBuilders();
        foreach ( $b_obj as $b ) {
            echo '<tr><td>';
            if ( $b[ 'type' ] == 1 ) {
                echo 'Artifact</td><td>';
                switch ( $b[ 'subtype' ] ) {
                case 1: echo 'Weapon'; break;
                case 2: echo 'Armour'; break;
                case 3: echo 'Usable Artifact'; break;
                case 4: echo 'Usable Combat'; break;
                case 5: echo 'Food'; break;
                case 6: echo 'Book'; break;
                case 7: echo 'Enchanting'; break;
                case 8: echo 'Rune'; break;
                case 9: echo 'Mount'; break;
                }
                echo '</td>';
            }
            echo '<td><a href="builder.php?v&i=' . $b[ 'id' ] .
                 '">' . $b[ 'title' ] . '</a></td><td>' .
                 '<a href="char.php?i=' . $b[ 'char_id' ] . '">' .
                 $b[ 'char_name' ] . '</a></td><td>' .
                 $b[ 'score' ] . '</td></tr>';
        }
        echo '</table>';
        echo '<p><a href="builder.php">Back to the World Builder</a></p>';
    }

} elseif ( $action == 1 ) {

    echo $post_err;

?>
<h3>Artifact Builder</h3>

<p>Construct a new artifact, and put it up for public voting.
The best ideas will rise to the top, and will be considered for
addition into the game!</p>

<center><form action="builder.php" method="post">
<table width="80%" border="0">
<tr>
  <td align="right">Artifact Name:</td>
  <td align="left">
    <input type="text" name="title" style="width: 300px;" value="<?= $_POST[ 'title' ] ?>"></td>
</tr>
<tr>
  <td align="right">Artifact Type:</td>
  <td align="left"><select name="type">
    <option value="1">Weapon</option>
    <option value="2">Armour</option>
    <option value="3">Usable Artifact</option>
    <option value="4">Usable Combat Weapon</option>
    <option value="5">Consumable Food</option>
    <option value="6">Book</option>
    <option value="7">Enchanting</option>
    <option value="8">Rune</option>
    <option value="9">Mount</option>
  </select></td>
</tr>
<tr>
  <td align="right">Description:</td>
  <td align="left">
    <textarea name="description" style="width: 304px;"><?= $_POST[ 'description' ] ?></textarea></td>
</tr>
<tr>
  <td align="right">Resistances or Bonuses:</td>
  <td align="left">
    <input type="text" name="resistances" style="width: 300px;" value="<?= $_POST[ 'resistances' ] ?>"></td>
</tr>
<tr>
  <td align="right">Attack Text (if necessary):</td>
  <td align="left">
    <input type="text" name="attack" style="width: 300px;" value="<?= $_POST[ 'attack' ] ?>"></td>
</tr>
<tr>
  <td align="right">Damage (if necessary):</td>
  <td align="left">
    <input type="text" name="damage" style="width: 300px;" value="<?= $_POST[ 'damage' ] ?>"></td>
</tr>
<tr>
  <td align="right">Gold Value:</td>
  <td align="left">
    <input type="text" name="value" style="width: 300px;" value="<?= $_POST[ 'value' ] ?>"></td>
</tr>
<tr>
  <td align="right">Other Information:</td>
  <td align="left">
    <input type="text" name="misc" style="width: 300px;" value="<?= $_POST[ 'misc' ] ?>"></td>
</tr>
<tr><td colspan="2" align="center"><input type="submit" value="Create Artifact"></td></tr>
</table>
<input type="hidden" name="a" value="1">
</form></center>

<?
    echo '<p><a href="builder.php">Back to the World Builder</a></p>';

} elseif ( $action == 2 ) {

} else {

    echo '<h3>World Builder</h3>';
    echo '<p>You can use this tool to create your own pieces of the ' .
         'Twelve Sands world, and to vote on pieces that other players ' .
         'have created.  The best creations have a shot at being added ' .
         'to the game, and will be attributed to the creator in the ' .
         'popup description!  Care to give it a shot?</p>' .
         '<p>Each account can have 10 active creations at a time, so ' .
         'feel free to experiment!  Creations will be retired after a few ' .
         'weeks in the queue, and will be removed from active voting, giving ' .
         'you another chance to make something new.<hr>';

    echo '<p><a href="builder.php?v">View creations, and vote</a><br>' .
         'From here, you can see all of the ideas that the players have had, ' .
         'and you can have your say on whether or not they would make good ' .
         'additions to the world.</p><hr>';

    echo '<p><a href="builder.php?a=1">Artifact Builder</a><br>Create new ' .
         'artifacts, including custom weapons and armour, or new and exotic ' .
         'foods and trinkets.</p>';

}


require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>