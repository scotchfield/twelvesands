<?

require_once 'include/core.php';
require_once sg_base_path . 'include/validate.php';

require_once sg_base_path . 'include/bank.php';
require_once sg_base_path . 'include/char.php';
require_once sg_base_path . 'include/chat.php';
require_once sg_base_path . 'include/combats.php';
require_once sg_base_path . 'include/common.php';
require_once sg_base_path . 'include/constants.php';
require_once sg_base_path . 'include/guilds.php';
require_once sg_base_path . 'include/log.php';
require_once sg_base_path . 'include/user.php';

$action = getGetStr( 'a', '0' );
$id_action = getGetStr( 'i', '0' );
$z = getGetStr( 'z', '1' );

$log_obj = new Logger();
$char_obj = new Char( $_SESSION[ 'c' ] );
$c = $char_obj->c;

if ( 'cb' != $action ) {
    forceCombatCheck( $char_obj );
}

if ( 'zxcp' == $action ) {


} elseif ( 'pc' == $action ) {

    $pass = setUserPassword( $_SESSION[ 'u' ], $id_action );
    if ( FALSE == $pass ) {
        header( 'Location: account.php?s=p0' );
    } else {
        header( 'Location: account.php?s=p1' );
    }

} elseif ( 'p' == $action ) {

    if ( ! isset( $_POST[ 'n' ] ) ) { $n = '0'; } else { $n = $_POST[ 'n' ]; }
    if ( ! isset( $_POST[ 's' ] ) ) { $s = '0'; } else { $s = $_POST[ 's' ]; }
    if ( ! isset( $_POST[ 't' ] ) ) { $t = '0'; } else { $t = $_POST[ 't' ]; }

    if ( ( '0' == $n ) || ( '' == $n ) ) {
        header( 'Location: mail.php?s=100' );
        exit;
    } elseif ( ( '0' == $t ) || ( '' == $t ) ) {
        header( 'Location: mail.php?s=101' );
    } else {
        $char_send_obj = getCharIdAndUserIdForCharName( $n );
        $char_id = $char_send_obj[ 'id' ];
        $user_id = $char_send_obj[ 'user_id' ];
        if ( FALSE == $char_id ) {
            header( 'Location: mail.php?s=102' );
        } else {
            $send = TRUE;

            $artifact_id = 0;
            $artifact_quantity = getPostInt( 'aq', 0 );
            if ( $artifact_quantity > 0 ) {
                $artifact_id = getPostInt( 'i', 0 );
                $artifact_enc = getPostInt( 'ae', 0 );

                if ( ( $artifact_id == 0 ) &&
                     ( $char_obj->c[ 'gold' ] < $artifact_quantity ) ) {
                    $send = FALSE;
                    header( 'Location: mail.php?s=103' );
                } elseif ( ( $artifact_id != 0 ) &&
                           ( getArtifactQuantity(
                                 $char_obj, $artifact_id, $artifact_enc ) <
                                     $artifact_quantity ) ) {
                    $send = FALSE;
                    header( 'Location: mail.php?s=104' );
                }
            }

            $a_obj = getArtifact( $artifact_id );
            if ( getBit( $a_obj[ 'flags' ], sg_artifact_flag_notrade ) ) {
                $send = FALSE;
                header( 'Location: mail.php?s=107' );
            }
            if ( ( $artifact_quantity > 0 ) && ( $user_id == $char_obj->c[ 'user_id' ] ) ) {
                $allowed_artifact_ids = array( 681, 682, 726, 727, 728 );
                if ( ( $a_obj[ 'rarity' ] < sg_artifact_rarity_epic ) &&
                     ( ! in_array( $a_obj[ 'id' ], $allowed_artifact_ids ) ) ) {
                    $send = FALSE;
                    header( 'Location: mail.php?s=108' );
                }
            }

            if ( $send == TRUE ) {
                $time = time();

                if ( $artifact_quantity > 0 ) {
                    if ( $artifact_id == 0 ) {
                        $char_obj->setGold( $char_obj->c[ 'gold' ] - $artifact_quantity );
                    } else {
                        removeArtifact(
                            $char_obj, $artifact_id, $artifact_quantity, $artifact_enc );
                        $time += 3600;
                    }
                }

                sendMail( $char_id, $c[ 'id' ], $c[ 'name' ], $s, $t,
                    $artifact_id, $artifact_quantity, $artifact_enc, $time );

                header( 'Location: mail.php?s=1' );
            }
        }
    }

} elseif ( 'ba' == $action ) {

    $n = getGetInt( 'n', 1 );
    $artifact = FALSE;

    if ( ( $n < 1 ) || ( $n > 1000 ) ) {
        $char_obj->addFlag( sg_flag_store_ui, ( 1 << sg_store_ui_invalid_amount ) );
    } else {
        $zone_artifacts = getZoneArtifacts( $z );
        if ( ! array_key_exists( $id_action, $zone_artifacts ) ) {
            $zone_artifacts = getStoreArtifacts( $z );
            if ( ! array_key_exists( $id_action, $zone_artifacts ) ) {
                $char_obj->addFlag( sg_flag_store_ui,
                                    ( 1 << sg_store_ui_not_sold_here ) );
            }
        }

        if ( array_key_exists( $id_action, $zone_artifacts ) ) {
            $artifact = $zone_artifacts[ $id_action ];
        }
    }

    if ( $artifact != FALSE ) {
        if ( ! canAffordStore( $char_obj, $artifact, $n ) ) {
            $char_obj->addFlag( sg_flag_store_ui, ( 1 << sg_store_ui_no_money ) );
        } elseif ( ( $artifact[ 'reputation_id' ] > 0) &&
                   ( $c[ 'reputations'][ $artifact[ 'reputation_id' ] ][ 'value' ] <
                         $artifact[ 'reputation_required' ] ) ) {
            $char_obj->addFlag( sg_flag_store_ui, ( 1 << sg_store_ui_no_rep ) );
        } else {
            $gold_required = 0;

            if ( $artifact[ 'gold_cost' ] > 0 ) {
                $gold_required = $gold_required + ( $artifact[ 'gold_cost' ] * $n );
            } else {
                $gold_required = $gold_required + ( $artifact[ 'buy_price' ] * $n );
            }

            if ( $c[ 'gold' ] < $gold_required ) {
                $char_obj->addFlag( sg_flag_store_ui, ( 1 << sg_store_ui_no_money ) );
            } else {
                $log_obj->addLog( $c, sg_log_buy_item,
                                  $artifact[ 'id' ], $n, $gold_required, 0 );

                $char_obj->setGold( $c[ 'gold' ] - $gold_required );
                awardArtifactString( $char_obj, $artifact, $n, 0 );
                if ( $artifact[ 'artifact_cost_1' ] > 0 ) {
                    removeArtifact( $char_obj, $artifact[ 'artifact_cost_1' ],
                                    $artifact[ 'artifact_quantity_1' ] * $n );
                }
                if ( $artifact[ 'artifact_cost_2' ] > 0 ) {
                    removeArtifact( $char_obj, $artifact[ 'artifact_cost_2' ],
                                    $artifact[ 'artifact_quantity_2' ] * $n );
                }
                if ( $artifact[ 'artifact_cost_3' ] > 0 ) {
                    removeArtifact( $char_obj, $artifact[ 'artifact_cost_3' ],
                                    $artifact[ 'artifact_quantity_3' ] * $n );
                }

                $char_obj->addFlag( sg_flag_store_ui, ( 1 << sg_store_ui_bought ) );
                $char_obj->addFlag( sg_flag_store_artifact, $artifact[ 'id' ] );
                $char_obj->addFlag( sg_flag_store_count, $n );
                $char_obj->addFlag( sg_flag_store_enc, 0 );
            }
        }
    }

    header( "Location: main.php?z=$z" );

} elseif ( 'sa' == $action ) {

    header( "Location: main.php?z=$z" );

} elseif ( 'cb' == $action ) {

    $i = getGetInt( 'i', -1 );

    if ( $i >= 0 ) {

        if ( array_key_exists( $i, $combat_bar_valid_bases ) ) {

            $combat_bar_array = getAllCombatBarOptions( $char_obj );
            $base = $combat_bar_valid_bases[ $i ];

            $v0 = getGetInt( 'v0', 0 );
            $v1 = getGetInt( 'v1', 0 );
            $v2 = getGetInt( 'v2', 0 );
            $v3 = getGetInt( 'v3', 0 );
            $v4 = getGetInt( 'v4', 0 );
            $v5 = getGetInt( 'v5', 0 );
            $v6 = getGetInt( 'v6', 0 );
            $v7 = getGetInt( 'v7', 0 );
            $v8 = getGetInt( 'v8', 0 );
            $v9 = getGetInt( 'v9', 0 );

            if ( ! array_key_exists( $v0, $combat_bar_array ) ) { $v0 = 0; }
            if ( ! array_key_exists( $v1, $combat_bar_array ) ) { $v1 = 0; }
            if ( ! array_key_exists( $v2, $combat_bar_array ) ) { $v2 = 0; }
            if ( ! array_key_exists( $v3, $combat_bar_array ) ) { $v3 = 0; }
            if ( ! array_key_exists( $v4, $combat_bar_array ) ) { $v4 = 0; }
            if ( ! array_key_exists( $v5, $combat_bar_array ) ) { $v5 = 0; }
            if ( ! array_key_exists( $v6, $combat_bar_array ) ) { $v6 = 0; }
            if ( ! array_key_exists( $v7, $combat_bar_array ) ) { $v7 = 0; }
            if ( ! array_key_exists( $v8, $combat_bar_array ) ) { $v8 = 0; }
            if ( ! array_key_exists( $v9, $combat_bar_array ) ) { $v9 = 0; }

            $char_obj->addFlag( $base, $v0 );
            $char_obj->addFlag( $base + 1, $v1 );
            $char_obj->addFlag( $base + 2, $v2 );
            $char_obj->addFlag( $base + 3, $v3 );
            $char_obj->addFlag( $base + 4, $v4 );
            $char_obj->addFlag( $base + 5, $v5 );
            $char_obj->addFlag( $base + 6, $v6 );
            $char_obj->addFlag( $base + 7, $v7 );
            $char_obj->addFlag( $base + 8, $v8 );
            $char_obj->addFlag( $base + 9, $v9 );

        }

    }

    header( "Location: account.php?a=cb&c=1" );

} elseif ( 'qh' == $action ) {

    $i = getGetInt( 'i', 0 );

    if ( ( $i > 0 ) && ( array_key_exists( $i, $char_obj->c[ 'quests' ] ) ) ) {
        toggleQuestHidden( $char_obj, $i );
    }

    header( "Location: char.php?a=ql" );

} elseif ( 'gr' == $action ) {

    $guild = getGuildById( $char_obj->c[ 'guild_id' ] );
    $r1 = getGetStr( 'r1', '' );
    $r2 = getGetStr( 'r2', '' );
    $r3 = getGetStr( 'r3', '' );
    $r4 = getGetStr( 'r4', '' );
    $r5 = getGetStr( 'r5', '' );

    if ( ( FALSE != $guild ) && ( $guild[ 'leader_id' ] == $char_obj->c[ 'id' ] ) &&
         ( strlen( $r1 ) >= 3 ) && ( strlen( $r1 ) <= 32 ) &&
         ( strlen( $r2 ) >= 3 ) && ( strlen( $r2 ) <= 32 ) &&
         ( strlen( $r3 ) >= 3 ) && ( strlen( $r3 ) <= 32 ) &&
         ( strlen( $r4 ) >= 3 ) && ( strlen( $r4 ) <= 32 ) &&
         ( strlen( $r5 ) >= 3 ) && ( strlen( $r5 ) <= 32 ) ) {
        updateGuildRanks( $char_obj->c[ 'guild_id' ],
            $r1, $r2, $r3, $r4, $r5 );
    }

    header( "Location: guild.php" );

} elseif ( 'gm' == $action ) {

    $guild = getGuildById( $char_obj->c[ 'guild_id' ] );
    if ( ( FALSE != $guild ) && ( $guild[ 'leader_id' ] == $char_obj->c[ 'id' ] ) ) {
        $motto = getPostStr( 'motto', '' );
        $url = getPostStr( 'url', '' );
        $message = getPostStr( 'message', '' );
        updateGuildMessages( $guild[ 'id' ], $motto, $url, $message );
    }

    header( "Location: guild.php" );

} elseif ( 'du' == $action ) {

    if ( count( $char_obj->c[ 'duel_requests' ] ) == 0 ) {
        $i = getGetInt( 'i', 0 );
        $session = getSessionByCharId( $i );
        if ( $i == $char_obj->c[ 'id' ] ) {
            $_SESSION[ 'alert_text' ] = 'You can\'t duel yourself!';
        } elseif ( FALSE != $session ) {
            sendDuelChallenge( $char_obj->c[ 'id' ], $i );
            addChat( $i, 0, 0, 'System', 0,
                     'You have received a duel request from ' .
                     $char_obj->c[ 'name' ] . '!' );
        }
    }

    header( "Location: char.php?a=du" );

} elseif ( 'dua' == $action ) {

    $i = getGetInt( 'i', 0 );
    $challenge = getDuelChallenge( $char_obj, $i );
    if ( FALSE == $challenge ) {
        header( "Location: char.php?a=du" );
    } else {
        $oppo_obj = new Char( $challenge[ 'target_id' ] );

        if ( $oppo_obj->c[ 'encounter_id' ] != 0 ) {

            $_SESSION[ 'alert_text' ] = 'That character is already in combat!  ' .
                'Please wait for them to finish.';
            header( "Location: char.php?a=du" );

        } elseif ( $oppo_obj->c[ 'user_id' ] == $char_obj->c[ 'user_id' ] ) {

            $_SESSION[ 'alert_text' ] = 'You can\'t duel yourself!';
            header( "Location: char.php?a=du" );

        } else {

            $state_id = createDuelState(
                $oppo_obj->c[ 'id' ], $oppo_obj->c[ 'duel_rank' ],
                $char_obj->c[ 'id' ], $char_obj->c[ 'duel_rank' ] );

            $char_obj->addFlag( sg_flag_es1, 0 );
            $char_obj->addFlag( sg_flag_es2, 0 );
            $char_obj->addFlag( sg_flag_es3, 0 );
            $char_obj->addFlag( sg_flag_es4, 0 );
            $char_obj->addFlag( sg_flag_es5, 0 );
            $char_obj->addFlag( sg_flag_combat_round, 0 );

            $oppo_obj->addFlag( sg_flag_es1, 0 );
            $oppo_obj->addFlag( sg_flag_es2, 0 );
            $oppo_obj->addFlag( sg_flag_es3, 0 );
            $oppo_obj->addFlag( sg_flag_es4, 0 );
            $oppo_obj->addFlag( sg_flag_es5, 0 );
            $oppo_obj->addFlag( sg_flag_combat_round, 0 );

            $char_obj->setEncounterId( $state_id );
            $char_obj->setEncounterType( sg_encountertype_duel );
            $oppo_obj->setEncounterId( $state_id );
            $oppo_obj->setEncounterType( sg_encountertype_duel );
            $oppo_obj->save();

            deleteAllDuelChallenges( $char_obj->c[ 'id' ] );
            deleteAllDuelChallenges( $oppo_obj->c[ 'id' ] );

            header( "Location: duel.php" );

        }
    }

} elseif ( 'dur' == $action ) {

    $i = getGetInt( 'i', 0 );
    $challenge = getRawDuelChallenge( $i );
    if ( FALSE != $challenge ) {
        if ( ( $challenge[ 'char_id' ] == $char_obj->c[ 'id' ] ) ||
             ( $challenge[ 'target_id' ] == $char_obj->c[ 'id' ] ) ) {
            deleteDuelChallenge( $i );
            deleteDuelChallengeByValues(
                $challenge[ 'target_id' ], $challenge[ 'char_id' ],
                $challenge[ 'created' ] );
            unset( $_SESSION[ 'duel_time_check' ] );
        }
    }

    header( "Location: char.php?a=du" );

} elseif ( 'md' == $action ) {

    if ( isset( $_POST[ 'ids' ] ) ) {
        deleteMailArray( $char_obj, $_POST[ 'ids' ] );
        header( "Location: mail.php?s=106" );
    } else {
        header( "Location: mail.php?s=105" );
    }

} elseif ( 'zd' == $action ) {

    $v_type = getPostInt( 'v', 0 );
    $n = getPostInt( 'n', 0 );

    if ( isset( $_POST[ 'ids' ] ) ) {
        $a_obj = getCharArtifactsArray( $char_obj->c[ 'id' ], $_POST[ 'ids' ] );

        $new_award_obj = array();
        $bank_obj = array();
        if ( $v_type == 1 ) {
            updateBankDepositObjs( $char_obj, 1, $a_obj,
                                   $bank_obj, $new_award_obj, $log_obj );
        } elseif ( $v_type == 2 ) {
            updateBankDepositObjs( $char_obj, 100000, $a_obj,
                                   $bank_obj, $new_award_obj, $log_obj );
        } elseif ( $v_type == 3 ) {
            updateBankDepositObjs( $char_obj, $n, $a_obj,
                                   $bank_obj, $new_award_obj, $log_obj );
        }

        if ( count( $bank_obj ) > 0 ) {
            addBankArtifacts( $char_obj, $bank_obj );
            setCharArtifacts( $char_obj, $new_award_obj );
        }
    }

    header( 'Location: bank.php' );

} elseif ( 'zw' == $action ) {

    $v_type = getPostInt( 'v', 0 );
    $n = getPostInt( 'n', 0 );

    $max_wd = -1;
    if ( $char_obj->c[ 'd_run' ] > 0 ) {
        $max_wd = 5 + floor( $char_obj->c[ 'total_fatigue' ] / 50000 ) -
            getFlagValue( $char_obj, sg_flag_bank_withdrawals );
    }

    if ( isset( $_POST[ 'ids' ] ) ) {
        $a_obj = getBankArtifactsArray( $char_obj->c[ 'id' ], $_POST[ 'ids' ] );

        $new_bank_obj = array();
        $award_obj = array();
        if ( $v_type == 1 ) {
            updateBankAwardObjs( $char_obj, 1, $a_obj, $award_obj, $new_bank_obj,
                                 $max_wd, $log_obj );
        } elseif ( $v_type == 2 ) {
            updateBankAwardObjs( $char_obj, 100000, $a_obj, $award_obj, $new_bank_obj,
                                 $max_wd, $log_obj );
        } elseif ( $v_type == 3 ) {
            updateBankAwardObjs( $char_obj, $n, $a_obj, $award_obj, $new_bank_obj,
                                 $max_wd, $log_obj );
        }

        if ( count( $award_obj ) > 0 ) {
            setBankArtifacts( $char_obj, $new_bank_obj );
            foreach ( $award_obj as $k => $a ) {
                foreach ( $a as $m => $v ) {
                    $char_obj->awardArtifact( $k, $v, $m );
                }
            }
        }
    }

    header( 'Location: bank.php' );

} elseif ( 'zgd' == $action ) {

    $gold = getPostInt( 'g', 0 );
    if ( $gold < 0 ) { $gold = 0; }

    if ( $char_obj->c[ 'd_run' ] > 0 ) {
        header( 'Location: bank.php?s=3' );
    } else {
        $gold = min( $char_obj->c[ 'gold' ], $gold );
        $char_obj->setGold( $char_obj->c[ 'gold' ] - $gold );
        $char_obj->setGoldBank( $char_obj->c[ 'gold_bank' ] + $gold );
        $log_obj->addLog( $c, sg_log_gold_deposit, $gold, 0, 0, 0 );
        header( 'Location: bank.php?s=1' );
    }

} elseif ( 'zgw' == $action ) {

    $gold = getPostInt( 'g', 0 );
    if ( $gold < 0 ) { $gold = 0; }

    if ( $char_obj->c[ 'd_run' ] > 0 ) {
        header( 'Location: bank.php?s=3' );
    } else {
        $gold = min( $char_obj->c[ 'gold_bank' ], $gold );
        $char_obj->setGold( $char_obj->c[ 'gold' ] + $gold );
        $char_obj->setGoldBank( $char_obj->c[ 'gold_bank' ] - $gold );
        $log_obj->addLog( $c, sg_log_gold_withdraw, $gold, 0, 0, 0 );
        header( 'Location: bank.php?s=2' );
    }

} elseif ( 'cwa' == $action ) {

    $gold = getPostInt( 'g', 0 );
    $a1 = getPostInt( 'a1', 0 );
    $a2 = getPostInt( 'a2', 0 );
    $a3 = getPostInt( 'a3', 0 );
    $a4 = getPostInt( 'a4', 0 );
    $a5 = getPostInt( 'a5', 0 );

    if ( ( $gold >= 1000 ) && ( $char_obj->c[ 'gold' ] >= $gold ) &&
         ( $a1 > 0 ) && ( $a2 > 0 ) && ( $a3 > 0 ) && ( $a4 > 0 ) && ( $a5 > 0 ) ) {

        $artifacts = getCharWarfareArtifacts( $char_obj->c[ 'id' ] );

        if ( ( array_key_exists( $a1, $artifacts ) ) &&
             ( array_key_exists( $a2, $artifacts ) ) &&
             ( array_key_exists( $a3, $artifacts ) ) &&
             ( array_key_exists( $a4, $artifacts ) ) &&
             ( array_key_exists( $a5, $artifacts ) ) &&
             ( $char_obj->c[ 'gold' ] >= $gold ) ) {

            $power = $artifacts[ $a1 ][ 'base_damage' ] +
                     $artifacts[ $a2 ][ 'base_damage' ] +
                     $artifacts[ $a3 ][ 'base_damage' ] +
                     $artifacts[ $a4 ][ 'base_damage' ] +
                     $artifacts[ $a5 ][ 'base_damage' ];

            if ( $power <= 20 ) {
                createWarfareGame( $char_obj->c[ 'id' ], $char_obj->c[ 'name' ],
                                   $gold, $a1, $a2, $a3, $a4, $a5 );
                $char_obj->setGold( $char_obj->c[ 'gold' ] - $gold );
            }

        }

    }

    header( 'Location: main.php?z=108&a=1' );

} elseif ( 'cwp' == $action ) {

    $a1 = getPostInt( 'a1', 0 );
    $a2 = getPostInt( 'a2', 0 );
    $a3 = getPostInt( 'a3', 0 );
    $a4 = getPostInt( 'a4', 0 );
    $a5 = getPostInt( 'a5', 0 );
    $id = getPostInt( 'id', -1 );

    $game = getWarfareGame( $id );

    if ( ( $game == FALSE ) || ( $game[ 'status' ] != 0 ) ||
         ( $game[ 'char_id_1' ] == $char_obj->c[ 'id' ] ) ||
         ( $game[ 'wager' ] > $char_obj->c[ 'gold' ] ) ) {

        header( 'Location: main.php?z=108&y=1' );

    } elseif ( ( $a1 > 0 ) && ( $a2 > 0 ) && ( $a3 > 0 ) && ( $a4 > 0 ) && ( $a5 > 0 ) ) {

        $artifacts = getCharWarfareArtifacts( $char_obj->c[ 'id' ] );
        if ( ( array_key_exists( $a1, $artifacts ) ) &&
             ( array_key_exists( $a2, $artifacts ) ) &&
             ( array_key_exists( $a3, $artifacts ) ) &&
             ( array_key_exists( $a4, $artifacts ) ) &&
             ( array_key_exists( $a5, $artifacts ) ) &&
             ( $char_obj->c[ 'gold' ] >= $gold ) ) {

            $power = $artifacts[ $a1 ][ 'base_damage' ] +
                     $artifacts[ $a2 ][ 'base_damage' ] +
                     $artifacts[ $a3 ][ 'base_damage' ] +
                     $artifacts[ $a4 ][ 'base_damage' ] +
                     $artifacts[ $a5 ][ 'base_damage' ];

            if ( $power <= 20 ) {
                $a_obj = getArtifactArray( array( $game[ 'a1' ], $game[ 'a2' ], $game[ 'a3' ],
                                                  $game[ 'a4' ], $game[ 'a5' ] ) );

                $s1 = getWarfareWinner( $a_obj[ $game[ 'a1' ] ][ 'base_damage' ],
                                        $artifacts[ $a1 ][ 'base_damage' ] );
                $s2 = getWarfareWinner( $a_obj[ $game[ 'a2' ] ][ 'base_damage' ],
                                        $artifacts[ $a2 ][ 'base_damage' ] );
                $s3 = getWarfareWinner( $a_obj[ $game[ 'a3' ] ][ 'base_damage' ],
                                        $artifacts[ $a3 ][ 'base_damage' ] );
                $s4 = getWarfareWinner( $a_obj[ $game[ 'a4' ] ][ 'base_damage' ],
                                        $artifacts[ $a4 ][ 'base_damage' ] );
                $s5 = getWarfareWinner( $a_obj[ $game[ 'a5' ] ][ 'base_damage' ],
                                        $artifacts[ $a5 ][ 'base_damage' ] );

                $winner = $s1 + $s2 + $s3 + $s4 + $s5;
                if ( $winner < 0 ) { $winner = 1; } else { $winner = 2; }

                updateWarfareGame( $game[ 'id' ],
                    $game[ 'char_id_1' ], $game[ 'char_name_1' ],
                    $char_obj->c[ 'id' ], $char_obj->c[ 'name' ],
                    $winner, $game[ 'wager' ],
                    $game[ 'a1' ], $game[ 'a2' ], $game[ 'a3' ], $game[ 'a4' ], $game[ 'a5' ],
                    $a1, $a2, $a3, $a4, $a5,
                    $s1, $s2, $s3, $s4, $s5 );

                $char_obj->setGold( $char_obj->c[ 'gold' ] - $game[ 'wager' ] );

                if ( $winner == 1 ) {
                    $win_id = $game[ 'char_id_1' ];
                    $win_name = $game[ 'char_name_1' ];
                    $lose_id = $char_obj->c[ 'id' ];
                    $lose_name = $char_obj->c[ 'name' ];
                } else {
                    $win_id = $char_obj->c[ 'id' ];
                    $win_name = $char_obj->c[ 'name' ];
                    $lose_id = $game[ 'char_id_1' ];
                    $lose_name = $game[ 'char_name_1' ];
                }

                $prize = floor( $game[ 'wager' ] * 0.99 ) * 2;

                sendMail( $win_id, 0, 'Warfare Referee', 'Warfare Victory!',
                    'Looks like you won your match against ' . $lose_name .
                    '.  Congratulations!  Here\'s your share of the wager, minus ' .
                    'the house fee.  Keep up the great work!',
                    0, $prize, 0, time() );
                sendMail( $lose_id, 0, 'Warfare Referee', 'Warfare Loss!',
                    'Sorry, looks like you were defeated in your match against ' .
                    $win_name . '.  Better luck next time!', 0, 0, 0, time() );
            }

        }

        header( 'Location: main.php?z=108&a=4&i=' . $game[ 'id' ] );

    } else {
        header( 'Location: main.php?z=108' );
    }

} elseif ( 'aaq' == $action ) {

    $quest_todo = getAvailableQuests( $char_obj );
    $q_obj = array();
    foreach ( $quest_todo as $q ) {
        if ( ( $q[ 'repeatable' ] == 0 ) ||
             ( ( $q[ 'repeatable' ] == 1 ) &&
               ( ! isset( $char_obj->c[ 'quests' ][ $q[ 'id' ] ] ) ) ) ) {
            $q_obj[] = $q[ 'id' ];
        }
    }

    if ( count( $q_obj ) > 0 ) {
        addQuestListSeen( $char_obj, $q_obj );
        header( 'Location: char.php?a=ql&ss=1' );
    } else {
        header( 'Location: char.php?a=ql&ss=2' );
    }

} elseif ( 'zzz' == $action ) {

    if ( sg_debug ) {
        clearSelect( FALSE );
        unset( $_SESSION[ 'zone_cache' ] );
    }
    header( 'Location: main.php' );

} else {

    header( 'Location: main.php' );

}

$save = $char_obj->save();
$log_save = $log_obj->save();

?>