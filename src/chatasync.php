<?

require_once 'include/core.php';

require_once sg_base_path . 'include/validatechat.php';

header("Content-Type: text/xml; charset=utf-8");

require_once sg_base_path . 'include/charmini.php';
require_once sg_base_path . 'include/chat.php';
require_once sg_base_path . 'include/sql.php';


function fixStr( $st ) {
    $st = htmlentities( $st, ENT_QUOTES );
    $st = utf8_encode( $st );
    $st = str_replace( '\'', '&#039;', $st );
    return $st;
}

function getGetInt( $id, $default ) {
    $i = $default;
    if ( isset( $_GET[ $id ] ) ) { $i = intval( $_GET[ $id ] ); }
    return $i;
}

function getGetStr( $id, $default ) {
    $st = $default;
    if ( isset( $_GET[ $id ] ) ) { $st = fixStr( $_GET[ $id ] ); }
    return $st;
}

function getPostStr( $id, $default ) {
    $st = $default;
    if ( isset( $_POST[ $id ] ) ) { $st = fixStr( $_POST[ $id ] ); }
    return $st;
}

function getCharIdForCharName( $char_name ) {
    $cn = esc( $char_name );
    $query = "SELECT id FROM `characters` WHERE name = '$cn'";
    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }
    $user = $results->fetch_assoc();
    return $user[ 'id' ];
}

function addLinks( $x ) {
    $text = preg_replace( "/([a-zA-Z]+:\/\/[a-z0-9\_\.\-]+".
        "[a-z]{2,6}[a-zA-Z0-9\/\*\-\?\&\%\=\,\.\_\:]+)/",
        " [<a href=\"$1\" target=\"_blank\">link</a>]", $x );
    return $text;
}

function breakLongWords( $x ) {
    $text = preg_replace( "/([\w\,\.\_\:\!]{80})/","$1 ", $x );
    return $text;
}

function checkBuffExists( $b_id ) {
    if ( isset( $_SESSION[ 'buffs' ] ) ) {
        if ( isset( $_SESSION[ 'buffs' ][ $b_id ] ) ) { return TRUE; }
    }
    return FALSE;
}

if ( ! isset( $_POST[ 'm' ] ) ) {
    if ( ! isset( $_GET[ 'm' ] ) ) {
        $m = '';
    } else {
        $m = getGetStr( 'm', '' );
    }
} else {
    $m = getPostStr( 'm', '' );
}


if ( strlen( $m ) > 0 ) {
    $chan = $_SESSION[ 'cc' ];
    $chan_type = $_SESSION[ 'cc_type' ];
    $m = substr( getPostStr( 'm', '' ), 0, 256 );

    if ( '/' == $m[ 0 ] ) {
        $m_array = explode( ' ', $m, 3 ); // "/m1 m2 m3_etc_all_the_rest"
        $m_upper = strtoupper( $m_array[ 0 ] );

        if ( ! strcmp( $m_upper, '/WHO' ) ) {

            $users = getUsersInChat();
            $chat_names = array(); //join(', ', $users);
            foreach ( $users as $user ) {
                $chat_names[] = '<span onclick="parent.parent.main_frame.location' .
                    '=&quot;char.php?i=' . $user[ 'char_id' ] .
                    '&quot;" class="mod_highlight hand">' .
                    $user[ 'char_name' ] . '</span>';
            }
            $chat_msg = join( ', ', $chat_names );
            addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0,
                     'Users online: ' . $chat_msg );

        } elseif ( ! strcmp( $m_upper, '/HELP' ) ) {

            $message = 'Twelve Sands Chat Help!  Valid commands: help, who';
            $message = htmlentities( $message, ENT_QUOTES );
            addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0, $message );

        } elseif ( ! strcmp( $m_upper, '/W' ) ) {

            if ( strlen( $m_array[ 2 ] ) > 0 ) {
                $w_id = getCharIdForCharName( $m_array[ 1 ] );
                if ( ( $w_id == FALSE ) || ( $w_id == 0 ) ) {
                    addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0, "Unknown user!" );
                } else {
                    addChat( $w_id, 0, 0, $_SESSION[ 'n' ] . ' (whisper)',
                             $_SESSION[ 'c' ], $m_array[ 2 ] );
                }
            }

        } elseif ( ! strcmp( $m_upper, '/ME' ) ) {

            addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0,
                     "Patience - this is coming.  :)" );

        } elseif ( ! strcmp( $m_upper, '/C' ) ) {

            $char_obj = new CharMini( $_SESSION[ 'c' ] );

            $chan_upper = strtoupper( $m_array[ 1 ] );
            if ( ! strcmp( $chan_upper, 'NORMAL' ) ) {
                $char_obj->setChatChannel( 1 );
                $char_obj->setChatChannelType( 0 );
                addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0,
                    "Switching to the <b>normal</b> chat channel." );
            } elseif ( ! strcmp( $chan_upper, 'TRADE' ) ) {
                $char_obj->setChatChannel( 2 );
                $char_obj->setChatChannelType( 0 );
                addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0,
                    "Switching to the <b>trade</b> chat channel." );
            } elseif ( ! strcmp( $chan_upper, 'DEV' ) ) {
                if ( $_SESSION[ 'c' ] == 1 ) {
                    $char_obj->setChatChannel( 3 );
                    $char_obj->setChatChannelType( 0 );
                    addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0,
                        "Switching to the <b>dev</b> chat channel." );
                } else {
                    addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0, "Unknown chat channel!" );
                }
            } elseif ( ! strcmp( $chan_upper, 'GUILD' ) ) {
                if ( ( $char_obj->c[ 'guild_id' ] > 0 ) &&
                     ( $char_obj->c[ 'guild_rank' ] <= 5 ) ) {
                    $char_obj->setChatChannel( $char_obj->c[ 'guild_id' ] );
                    $char_obj->setChatChannelType( 1 );
                    addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0,
                        "Switching to your <b>guild</b> chat channel." );
                } else {
                    addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0,
                        "You are not a full member in any guild!" );
                }
            } else {
                addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0, "Unknown chat channel!" );
            }

            $char_obj->save();

        } else {

            addChat( $_SESSION[ 'c' ], 0, 0, 'System', 0, "Unknown command!" );

        }

    } else {
        $char_obj = new CharMini( $_SESSION[ 'c' ] );

        if ( strlen( $m ) > 0 ) {
            if ( checkBuffExists( 116 ) ) {
                $m = str_replace( 's', 'ssh', $m );
                if ( rand( 1, 3 ) == 1 ) {
                    $m = $m . ' .. urp!';
                }
            }
            if ( checkBuffExists( 117 ) ) {
                if ( rand( 1, 3 ) == 1 ) {
                    $m = $m . ' .. arr!';
                }
            }

            addChat( 0, $chan, $chan_type,
                     $char_obj->c[ 'name' ], $char_obj->c[ 'id' ], $m );
        }
    }

} else {
    $chan = $_SESSION[ 'cc' ];
    $chan_type = $_SESSION[ 'cc_type' ];

    $t = getGetInt( 't', 0 );
    if ( $t > 0 ) {
        if ( isset( $_SESSION[ 'chat_msg' ] ) ) {
            $t = intval( $_SESSION[ 'chat_msg' ] );
        }
    }

    $xml = '<?xml version="1.0" ?><root>' . "\n";
    $chat = getChatById( $chan, $chan_type, $t, $_SESSION[ 'c' ] );
    foreach ( $chat as $ch ) {
        if ( ( $ch[ 'private_id' ] == 0 ) || ( $ch[ 'private_id' ] == $_SESSION[ 'c' ] ) ) {
            if ( $ch[ 'char_id' ] == 0 ) {
                $message = "<b>" . $ch[ 'char_name' ] .
                    "</b> (" . date( "H:i:s", $ch[ 'timestamp' ] + 10800 ) .
                    "): ";
                $message = htmlentities( $message, ENT_QUOTES );
                $message = $message . htmlentities( $ch[ 'message' ], ENT_QUOTES );
            } else {
                $message = "<b><span onclick=\"parent.parent.main_frame.location" .
                    "='char.php?i=" . $ch[ 'char_id' ] .
                    "'\" class=\"mod_highlight hand\">" .
                    $ch[ 'char_name' ] . "</span></b> (" .
                    date( "H:i:s", $ch[ 'timestamp' ] + 10800 ) . "): ";
                $message = htmlentities( $message, ENT_QUOTES );
                $message = $message .
                    htmlentities( breakLongWords( addLinks( $ch[ 'message' ], ENT_QUOTES ) ) );
            }
            $xml = $xml . '  <msg id="' . $ch[ 'id' ] . '">' . "\n";
            $xml = $xml . "    <message>$message</message>\n";
            $xml = $xml . "</msg>\n";

            if ( $ch[ 'id' ] > $_SESSION[ 'chat_msg' ] ) {
                $_SESSION[ 'chat_msg' ] = $ch[ 'id' ];
            }
        }
    }
    $xml = $xml . '</root>';
    echo $xml;
}

?>