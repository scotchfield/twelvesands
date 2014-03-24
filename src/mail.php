<?

require_once 'include/core.php';

require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/formatting.php';
require_once sg_base_path . 'include/mails.php';

$a = getGetStr( 'a', '0' );
$s = getGetStr( 's', '0' );

$char_obj = new Char( $_SESSION[ 'c' ] );
$c = $char_obj->c;
forceCombatCheck( $char_obj );

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html><head>
<meta http-equiv="Content-type" content="text/html;charset=UTF-8">
<title><? echo sg_name; ?></title>
<? renderCharCss( $c ); ?>
</head>
<body>
<style type="text/css"><!--
.auction_highlight {
  background: #F0F0F0;
}
--></style>

<? renderPopupText(); ?>
<script type="text/javascript" src="include/ts_mail.js"></script>

<div class="container">

<?

$ts_no_keypress = TRUE;
require '_header.php';
require '_charmenu.php';

if ( '0' == $a ) {

    if ( '1' == $s ) {
        echo '<p class="tip">Message sent successfully!</p>';
    } elseif ( '100' == $s ) {
        echo '<p class="tip">No user specified!</p>';
    } elseif ( '101' == $s ) {
        echo '<p class="tip">No text specified!</p>';
    } elseif ( '102' == $s ) {
        echo '<p class="tip">That user wasn\'t found!</p>';
    } elseif ( '103' == $s ) {
        echo '<p class="tip">Not enough gold!</p>';
    } elseif ( '104' == $s ) {
        echo '<p class="tip">You don\'t have that many to send!</p>';
    } elseif ( '105' == $s ) {
        echo '<p class="tip">No messages selected!</p>';
    } elseif ( '106' == $s ) {
        echo '<p class="tip">Messages deleted!</p>';
    } elseif ( '107' == $s ) {
        echo '<p class="tip">Hmm..</p>';
    } elseif ( '108' == $s ) {
        echo '<p class="tip">You can\'t send non-epic quality artifacts to ' .
             'your other characters!<br>Sharing these artifacts between ' .
             'your own characters is not permitted.  Sorry!</p>';
    }

    echo '<p><span class="section_header">Your Mailbox</span></p>';
    echo '<p><font size="-2">(<a href="mail.php?a=c">Send a ' .
         'message</a>)<br>';
    echo '(<a href="javascript:toggleAllMail(true);">Select all</a>) ';
    echo '(<a href="javascript:toggleAllMail(false);">Select none</a>) ';
    echo '</font></p>';

    echo '<form method="post" name="mail" action="action.php?a=md">';
    echo '<center><table width="100%"><tr align="left">';
    echo '<th>&nbsp;</th>';
    echo '<th width="20%">From</th>';
    echo '<th width="60%">Subject</th>';
    echo '<th width="15%" align="right">Date</th>';
    echo '</tr>';
    $mail = getAllMail( $char_obj );
    foreach ( $mail as $m ) {
        $y = ''; $z = '';
        if ( 1 == $m[ 'status' ] ) { $y = '<b>'; $z = '</b>'; }
        $m_date = date( 'M j G:i', $m[ 'created' ] + 10800 );
        echo '<tr align="left" ' .
             'onmouseover="this.className=\'auction_highlight\'" ' .
             'onmouseout="this.className=\'\'">';
        echo '<td><input type="checkbox" name="ids[]" value="' . $m[ 'id' ] . '"></td>';
        if ( $m[ 'from_char_id' ] > 0 ) {
            echo '<td>' . $y . '<font size="-1"><a href="char.php?i=' .
                 $m[ 'from_char_id' ] . '">' .
                 $m[ 'from_char_name' ] . '</a></font>' . $z . '</td>';
        } else {
            echo '<td>' . $y . '<font size="-1">' .
                 $m[ 'from_char_name' ] . '</font>' . $z . '</td>';
        }
        if ( $m[ 'subject' ] == '' ) {
            $m[ 'subject' ] = '(no subject)';
        }
        echo '<td>' . $y . '<font size="-1"><a href="mail.php?a=' . $m[ 'id' ] .
             '">' . $m[ 'subject' ] . '</a></font>' . $z . '</td>';
        echo '<td align="right">' . $y . '<font size="-1">' .
             $m_date . '</font>' . $z . '</td>';
        echo "</tr>\n";
    }
    echo '</table>';
    echo '<p><input type="submit" value="Delete selected"></p>';
    echo '</center></form>';

} elseif ( 'c' == $a ) {

    echo '<p><b>Send a Message</b></p>';

    $subj = getGetStr( 's', '' );
    if ( '' != $subj ) {
        $re_pos = strpos( $subj, 're: ' );
        if ( ( $re_pos === false ) || ( $re_pos > 0 ) ) {
            $subj = 're: ' . $subj;
        }
    }

    $n = getGetStr( 'n', '0' );
    if ( '0' != $n ) { $send_obj = new Char( $n ); }

?>

  <p><form method="post" action="action.php?a=p">
  Which user are you sending to?
  <br>
<?  if ( '0' == $n ) { ?>
  <input type="text" name="n" />
<?  } else { ?>
  <input type="text" name="n" value="<?= $send_obj->c[ 'name' ] ?>" />
<?  } ?>
  <br><br>Subject:
  <input type="text" name="s" value="<?= $subj ?>" size="40" />
  <br><br>Message:
  <br><textarea name="t" rows="12" cols="50"></textarea>
<div id="mail_attach_link">
  <font size="-2">(<a href="#" onclick="showAttachToMail()">Attach
      something to this message</a>)</font>
</div>
<div id="mail_attach" class="invis">
  <br>Attach something to this message:<br>
<?
    echo '<input type="hidden" id="artifact_enchant" name="ae" value="0">';
    echo '<select name="i">';
    echo '<option value="0" onclick="setArtifactEnchant(0);">Gold</option>' .
         "\n";
    $a_obj = getCharArtifacts( $char_obj->c[ 'id' ] );
    foreach( $a_obj as $artifact ) {
        if ( $artifact[ 'sell_price' ] > 0 ) {
            if ( ! getBit( $artifact[ 'flags' ], sg_artifact_flag_notrade ) ) {
                echo '<option value="' . $artifact[ 'id' ] . '" ' .
                     'onclick="setArtifactEnchant(' . $artifact[ 'm_enc' ] . ');">' .
                     $artifact[ 'name' ] . ' (' . $artifact[ 'quantity' ] .
                     ' owned)</option>' . "\n";
            }
        }
    }
    echo '</select>';
?>
  <br>Quantity: <input type="text" name="aq" value="0" size="5" />
  <br><br><font size="-2">Please note: Sending artifacts through the mail
    results in a delay of one hour,<br>while our diligent Capital City mail
    staff sorts through the goods!</font>
</div>
  <br><br><input type="submit" value="Send Mail!">
  </form></p>

<?

    echo '<p><a href="mail.php">Go back to your mailbox</a></p>';

} elseif ( 'd' == $a ) {

    $i = getGetStr( 'i', '0' );

    if ( $i > 0 ) {
        $mail = getMail( $char_obj, $i );
        if ( FALSE != $mail ) {
            deleteMail( $char_obj, $i );
            echo '<p>Message deleted!</p>';
        } else {
            echo '<p>You can\'t delete that message!</p>';
        }
    }

    echo '<p><a href="mail.php">Go back to your mailbox</a></p>';

} elseif ( $a > 0 ) {

    echo '<p><b>Your Mailbox</b></p>';

    $mail = getMail( $char_obj, $a );
    if ( FALSE != $mail ) {

        echo '<p><font size="-2">(<a href="mail.php?a=d&i=' . $mail[ 'id' ] .
             '">delete message</a>)</font></p>';
        echo '<p><b>To:</b> ' . $c[ 'name' ] . '<br>';
        if ( $mail[ 'from_char_id' ] > 0 ) {
            echo '<b>From:</b> <a href="char.php?i=' . $mail[ 'from_char_id' ] .
                 '">' . $mail[ 'from_char_name' ] . '</a> ';
            echo '<font size="-2">(<a href="mail.php?a=c&n=' .
                 $mail[ 'from_char_id' ] . '&s=' . urlencode( $mail[ 'subject' ] ) .
                 '">reply</a>)</font>';
            echo '<br>';
        } else {
            echo '<b>From:</b> ' . $mail[ 'from_char_name' ] . '<br>';
        }
        $m_date = date( 'D M j G:i:s', $mail[ 'created' ] + 10800 );
        echo '<b>Date:</b> ' . $m_date . '<br>';
        echo '<b>Subject:</b> ' . $mail[ 'subject' ] . '</p>';
        echo '<p>' . $mail[ 'text' ] . '</p>';

        if ( 1 == $mail[ 'status' ] ) {
            if ( ( $char_obj->c[ 'd_id' ] > 0 ) &&
                 ( $mail[ 'from_char_id' ] != 1 ) &&
                 ( $mail[ 'artifact_quantity' ] > 0 ) ) {
                $artifact = getArtifact( $mail[ 'artifact_id' ], $mail[ 'artifact_enc' ] );
                echo '<p class="tip">This message has ' . $mail[ 'artifact_quantity' ];
                if ( $artifact[ 'id' ] > 0 ) {
                    echo 'x ';
                    renderArtifact( $artifact, $mail[ 'artifact_quantity' ] );
                } else {
                    echo ' gold';
                }
                echo ' attached, but you are on a dungeon run, and cannot ' .
                     'receive artifacts through the mail until you finish.</p>';
            } else {
                if ( $mail[ 'artifact_quantity' ] > 0 ) {
                    $artifact = getArtifact( $mail[ 'artifact_id' ], $mail[ 'artifact_enc' ] );
                    awardArtifact( $char_obj, $artifact, $mail[ 'artifact_quantity' ],
                                   $mail[ 'artifact_enc' ] );
                }
                markMailAsRead( $char_obj, $mail[ 'id' ] );
            }
        } else {
            if ( $mail[ 'artifact_quantity' ] > 0 ) {
                $artifact = getArtifact( $mail[ 'artifact_id' ], $mail[ 'artifact_enc' ] );
                echo '<p>When it was sent, this message included ' .
                     $mail[ 'artifact_quantity' ];
                if ( $artifact[ 'id' ] > 0 ) {
                    echo 'x ';
                    renderArtifact( $artifact, $mail[ 'artifact_quantity' ] );
                } else {
                    echo ' gold';
                }
                echo '.</p>';
            }
        }

    } else {
        echo '<p>That message isn\'t for you!</p>';
    }

    echo '<p><a href="mail.php">Go back to your mailbox</a></p>';

}


require '_footer.php';
$save = $char_obj->save();

?>

</div>
</body>
</html>