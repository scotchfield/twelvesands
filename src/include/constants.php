<?

define( 'sg_verified_none', 0 );
define( 'sg_verified_true', 127 );

define( 'sg_newcombat', 0 );//sg_debug );

define( 'sg_zone_none',                  0 );
define( 'sg_zone_standard',              1 );
define( 'sg_zone_store',                 2 );
define( 'sg_zone_encounter',             3 );
define( 'sg_zone_travel',                4 );
define( 'sg_zone_fishing',               5 );
define( 'sg_zone_mining',                6 );
define( 'sg_zone_itemstore',             7 );
define( 'sg_zone_dungeon',               8 );
define( 'sg_zone_infirmary',             100 );
define( 'sg_zone_hallofrecords',         101 );
define( 'sg_zone_grandacademy',          102 );
define( 'sg_zone_capitalcasino',         103 );
define( 'sg_zone_auctionhouse',          104 );
define( 'sg_zone_badges',                105 );
define( 'sg_zone_pathfinder',            106 );
define( 'sg_zone_bank',                  107 );
define( 'sg_zone_warfaregame',           108 );
define( 'sg_zone_sandstorm',             109 );
define( 'sg_zone_scarshield_stairs',     110 );
define( 'sg_zone_trading_company',       111 );
define( 'sg_zone_lottery',               112 );
define( 'sg_zone_pravokan_revelry',      113 );
define( 'sg_zone_plotlist',              114 );

define( 'sg_artifact_none',           0 );
define( 'sg_artifact_weapon',         1 );
define( 'sg_artifact_usable',         2 );
define( 'sg_artifact_edible',         3 );
define( 'sg_artifact_combat_usable',  4 );
define( 'sg_artifact_readable',       5 );
define( 'sg_artifact_quest',          6 );
define( 'sg_artifact_enchanting',     7 );
define( 'sg_artifact_rune',           8 );
define( 'sg_artifact_armour_head',    100 );
define( 'sg_artifact_armour_chest',   101 );
define( 'sg_artifact_armour_legs',    102 );
define( 'sg_artifact_armour_neck',    103 );
define( 'sg_artifact_armour_trinket', 104 );
define( 'sg_artifact_armour_hands',   105 );
define( 'sg_artifact_armour_wrists',  106 );
define( 'sg_artifact_armour_belt',    107 );
define( 'sg_artifact_armour_boots',   108 );
define( 'sg_artifact_armour_ring',    109 );
define( 'sg_artifact_mount',          150 );
define( 'sg_artifact_puzzle_1',       200 );
define( 'sg_artifact_warfare_1',      201 );

define( 'sg_artifact_rarity_poor', 0 );
define( 'sg_artifact_rarity_common', 1 );
define( 'sg_artifact_rarity_uncommon', 2 );
define( 'sg_artifact_rarity_rare', 3 );
define( 'sg_artifact_rarity_epic', 4 );

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
    sg_artifact_armour_ring );

define( 'sg_encounter_foe',      'foe' );
define( 'sg_encounter_treasure', 'treasure' );
define( 'sg_encounter_choice',   'choice' );

define( 'sg_max_fatigue', 100000);

define( 'sg_fatigue_treasure', 1000 );
define( 'sg_fatigue_combat',   2500 );
define( 'sg_fatigue_defeat',   7500 );
define( 'sg_fatigue_flee',     2000 );
define( 'sg_fatigue_fishing',   750 );
define( 'sg_fatigue_mining',   1200 );

define( 'sg_skills_bonus_str',    1 );
define( 'sg_skills_bonus_dex',    2 );
define( 'sg_skills_bonus_int',    3 );
define( 'sg_skills_bonus_cha',    5 );
define( 'sg_skills_bonus_con',    6 );
define( 'sg_skills_bonus_level',  7 );
define( 'sg_skills_bonus_max_health', 8 );
define( 'sg_skills_fatigue_reduction', 9 );
define( 'sg_skills_gold_drop_boost', 10 );
define( 'sg_skills_bonus_melee_damage', 11 );
define( 'sg_skills_bonus_defend_damage', 12 );
define( 'sg_skills_bonus_xp_award_percent', 13 );
define( 'sg_skills_bonus_rep_award_percent', 14 );
define( 'sg_skills_skill_award', 15 );
define( 'sg_skills_bonus_fishing', 22 );
define( 'sg_skills_bonus_mining', 23 );
define( 'sg_skills_item_drop_boost', 24 );
define( 'sg_skills_bonus_buff_duration', 25 );
define( 'sg_skills_bonus_food_reduction', 26 );
define( 'sg_skills_resist_fire', 27 );
define( 'sg_skills_resist_water', 28 );
define( 'sg_skills_resist_earth', 29 );
define( 'sg_skills_resist_air', 30 );
define( 'sg_skills_resist_arcane', 31 );
define( 'sg_skills_resist_electric', 32 );
define( 'sg_skills_resist_necro', 33 );
define( 'sg_skills_bonus_armour', 34 );
define( 'sg_skills_bonus_dodge', 35 );
define( 'sg_skills_bonus_initiative', 36 );
define( 'sg_skills_bonus_hunger', 37 );
define( 'sg_skills_bonus_crafting_xp', 38 );
define( 'sg_skills_noncombat_freq_boost', 39 );
define( 'sg_skills_bonus_crit', 40 );
define( 'sg_skills_bonus_to_hit', 41 );
define( 'sg_skills_bonus_mana_percent', 42 );
define( 'sg_skills_bonus_armour_percent', 43 );
define( 'sg_skills_bonus_health_percent', 44 );
define( 'sg_skills_mana_regen', 45 );
define( 'sg_skills_bonus_melee_damage_percent', 46 );
define( 'sg_skills_bonus_all', 47 );
define( 'sg_skills_bonus_spell_damage', 48 );
define( 'sg_skills_hp_regen', 49 );
define( 'sg_skills_rested_eating_bonus', 50 );
define( 'sg_skills_resist_magical', 51 );
define( 'sg_skills_track_goodies', 52 );
define( 'sg_skills_xp_combat_bonus', 53 );
define( 'sg_skills_pravokan', 54 );
define( 'sg_skills_fishing_fatigue_percent', 55 );
define( 'sg_skills_bonus_magic_dmg_percent', 56 );
define( 'sg_skills_resist_physical', 57 );
define( 'sg_skills_convert_magic_to_dmg_percent', 58 );
define( 'sg_skills_reduce_cook_craft_fatigue_percent', 59 );
define( 'sg_skills_dunnich', 60 );

define( 'sg_log_engage_foe',        1 );
define( 'sg_log_defeat_foe',        2 );
define( 'sg_log_killed_by_foe',     3 );
define( 'sg_log_run_from_foe',      4 );
define( 'sg_log_login_success',     5 );
define( 'sg_log_login_failed',      6 );
define( 'sg_log_use_item',          7 );
define( 'sg_log_fate_wheel_win',    8 );
define( 'sg_log_fate_wheel_loss',   9 );
define( 'sg_log_auction_list',      10 );
define( 'sg_log_auction_buy',       11 );
define( 'sg_log_donate_view',       12 );
define( 'sg_log_donate_success',    13 );
define( 'sg_log_casino_card_win',   14 );
define( 'sg_log_casino_card_loss',  15 );
define( 'sg_log_buy_item',          16 );
define( 'sg_log_sell_item',         17 );
define( 'sg_log_casino_dice_win',   18 );
define( 'sg_log_sandstorm_wisdom_win',   19 );
define( 'sg_log_sandstorm_wisdom_loss',  20 );
define( 'sg_log_zone_marker',       21 );
define( 'sg_log_auction_revoke',    22 );
define( 'sg_log_fishing',           23 );
define( 'sg_log_mining',            24 );
define( 'sg_log_spell_cast',        25 );
define( 'sg_log_combat_state',      26 );
define( 'sg_log_duel_win',          27 );
define( 'sg_log_duel_timeout',      28 );
define( 'sg_log_bank_deposit',      29 );
define( 'sg_log_bank_withdraw',     30 );
define( 'sg_log_dungeon_run_start', 31 );
define( 'sg_log_dungeon_run_end',   32 );
define( 'sg_log_gold_deposit',      33 );
define( 'sg_log_gold_withdraw',     34 );
define( 'sg_log_enchant',           35 );
define( 'sg_log_disenchant',        36 );
define( 'sg_log_outfit',            37 );
define( 'sg_log_trading_bid',       38 );
define( 'sg_log_trading_insufficient',    39 );
define( 'sg_log_trading_reserve',    40 );
define( 'sg_log_lottery_purchase',  41 );

define( 'sg_foetype_none',          0 );
define( 'sg_foetype_humanoid',      1 );
define( 'sg_foetype_beast',         2 );
define( 'sg_foetype_undead',        3 );
define( 'sg_foetype_elemental',     4 );
define( 'sg_foetype_demon',         5 );
define( 'sg_foetype_dragon',        6 );
define( 'sg_foetype_gaseous',       7 );
define( 'sg_foetype_mechanical',    8 );
define( 'sg_foetype_ooze',          9 );
define( 'sg_foetype_insect',       10 );
define( 'sg_foetype_spectral',     11 );

// Encounter State flag 1: (1 << const_val)
define( 'sg_es1_initiative_check',    0 );
define( 'sg_es1_statreduce_2_points', 1 );
define( 'sg_es1_chararmour_500',      2 );
define( 'sg_es1_double_gold_drop',    3 );
define( 'sg_es1_stun_1',              4 );
define( 'sg_es1_stun_2',              5 );
define( 'sg_es1_stun_3',              6 );
define( 'sg_es1_str_loss_3',          7 );
define( 'sg_es1_str_loss_5',          8 );
define( 'sg_es1_dex_loss_3',          9 );
define( 'sg_es1_dex_loss_5',         10 );
define( 'sg_es1_int_loss_3',         11 );
define( 'sg_es1_int_loss_5',         12 );
define( 'sg_es1_char_extra_attack',  13 );
define( 'sg_es1_stun_0',             14 );
define( 'sg_es1_bleed_0',            15 );
define( 'sg_es1_bleed_1',            16 );
define( 'sg_es1_bleed_2',            17 );
define( 'sg_es1_bleed_3',            18 );
define( 'sg_es1_expose_1',           19 );
define( 'sg_es1_expose_2',           20 );
define( 'sg_es1_expose_3',           21 );
define( 'sg_es1_expose_4',           22 );
define( 'sg_es1_expose_5',           23 );
define( 'sg_es1_shatter_1',          24 );
define( 'sg_es1_shatter_2',          25 );
define( 'sg_es1_shatter_3',          26 );
define( 'sg_es1_chilling_1',         27 );
define( 'sg_es1_chilling_2',         28 );

define( 'sg_es2_enraged_1',          0 );
define( 'sg_es2_enraged_2',          1 );
define( 'sg_es2_enraged_3',          2 );
define( 'sg_es2_enraged_4',          3 );
define( 'sg_es2_enraged_5',          4 );
define( 'sg_es2_enraged_6',          5 );
define( 'sg_es2_enraged_7',          6 );
define( 'sg_es2_enraged_8',          7 );
define( 'sg_es2_enraged_9',          8 );
define( 'sg_es2_enraged_10',         9 );
define( 'sg_es2_healthsiphon_1',    10 );
define( 'sg_es2_healthsiphon_2',    11 );
define( 'sg_es2_healthsiphon_3',    12 );
define( 'sg_es2_healthsiphon_4',    13 );
define( 'sg_es2_healthsiphon_5',    14 );

define( 'sg_combatspell_fireball_1', 1 );
define( 'sg_combatspell_lightning_1', 2 );
define( 'sg_combatspell_chilling_1', 3 );
define( 'sg_combatspell_searing_1', 4 );
define( 'sg_combatspell_healing_1', 5 );
define( 'sg_combatspell_blindinglight_1', 6 );
define( 'sg_combatspell_healthsiphon_1', 7 );
define( 'sg_combatspell_tragicwail_1', 8 );

define( 'sg_spell_heal_10',            1 );
define( 'sg_spell_str_2',              2 );
define( 'sg_spell_dex_2',              3 );
define( 'sg_spell_int_2',              4 );
define( 'sg_spell_cha_2',              5 );
define( 'sg_spell_con_2',              6 );
define( 'sg_spell_item_drops_10',      7 );
define( 'sg_spell_fatigue_2',          8 );
define( 'sg_spell_charm',              9 );
define( 'sg_spell_obscuring_mist',    10 );
define( 'sg_spell_conjure_water',     11 );
define( 'sg_spell_avoid_foes',        12 );
define( 'sg_spell_mage_armour',       13 );
define( 'sg_spell_fiery_hands',       14 );
define( 'sg_spell_inspiring_song',    15 );
define( 'sg_spell_sweets_1',          16 );
define( 'sg_spell_sweets_2',          17 );
define( 'sg_spell_heal_50',           18 );

define( 'sg_crafttype_weapon', 0 );
define( 'sg_crafttype_armour', 1 );
define( 'sg_crafttype_metal',  2 );
define( 'sg_crafttype_usable', 3 );

define( 'sg_cooktype_meat', 0 );
define( 'sg_cooktype_fish', 1 );
define( 'sg_cooktype_baked', 2 );
define( 'sg_cooktype_mushroom', 3 );

define( 'sg_attacktype_physical', 0 ); // (1 << 0)
define( 'sg_attacktype_crush', 1 );
define( 'sg_attacktype_stab', 2 );
define( 'sg_attacktype_slam', 3 );
define( 'sg_attacktype_acidic', 4 );
define( 'sg_attacktype_slashing', 5 );
define( 'sg_attacktype_poison', 6 );
define( 'sg_attacktype_leech', 7 );
define( 'sg_attacktype_zone', 8 );
define( 'sg_attacktype_magical', 15 );
define( 'sg_attacktype_fire', 16 );
define( 'sg_attacktype_water', 17 );
define( 'sg_attacktype_earth', 18 );
define( 'sg_attacktype_air', 19 );
define( 'sg_attacktype_arcane', 20 );
define( 'sg_attacktype_electric', 21 );
define( 'sg_attacktype_necromancy', 22 );
define( 'sg_attacktype_spectral', 23 );

define( 'sg_attackspecial_bleed', 0 ); // (1 << 0)
define( 'sg_attackspecial_stun', 1 );

define( 'sg_recipetype_cooking', 1 );
define( 'sg_recipetype_crafting', 2 );
define( 'sg_recipetype_enchanting', 3 );

define( 'sg_flag_sandstorm_wisdom_1', 1 );
define( 'sg_flag_sandstorm_wisdom_2', 2 );
define( 'sg_flag_css_background_color', 3 );
define( 'sg_flag_navbar_toggles', 4 );
define( 'sg_flag_onetimeencounter', 5 );
define( 'sg_flag_scalingfoe', 6 );
define( 'sg_flag_es1', 7 );
define( 'sg_flag_es2', 8 );
define( 'sg_flag_es3', 9 );
define( 'sg_flag_es4', 10 );
define( 'sg_flag_es5', 11 );
define( 'sg_flag_store_ui', 12 );
define( 'sg_flag_store_artifact', 13 );
define( 'sg_flag_store_count', 14 );
define( 'sg_flag_ui', 15 );
define( 'sg_flag_unequip', 16 );
define( 'sg_flag_combat_flag_id_set', 17 );
define( 'sg_flag_combat_flag_bit_set', 18 );
define( 'sg_flag_equip', 19 );
define( 'sg_flag_almok_chapel', 20 );
define( 'sg_flag_cb1_start', 21 );
define( 'sg_flag_cb1_end', 30 );
define( 'sg_flag_last_zone', 31 );
define( 'sg_flag_last_combat_zone', 32 );
define( 'sg_flag_emerald_caves', 33 );
define( 'sg_flag_daily', 34 );
define( 'sg_flag_almok_vaults', 35 );
define( 'sg_flag_almok_stables', 36 );
define( 'sg_flag_top_melee_damage', 37 );
define( 'sg_flag_top_spell_damage', 38 );
define( 'sg_flag_top_damage_taken', 39 );
define( 'sg_flag_top_block_amount', 40 );
define( 'sg_flag_top_heal_amount', 41 );
define( 'sg_flag_goldstone_depths', 42 );
define( 'sg_flag_bank_withdrawals', 43 );
define( 'sg_flag_sandstorm_combats', 44 );
define( 'sg_flag_game_flag_increase', 45 );
define( 'sg_flag_game_flag_decrease', 46 );
define( 'sg_flag_unequip_enc', 47 );
define( 'sg_flag_equip_enc', 48 );
define( 'sg_flag_store_enc', 49 );
define( 'sg_flag_enchanting', 50 );
define( 'sg_flag_last_combat_artifact', 51 );
define( 'sg_flag_scalingfoe_max', 52 );
define( 'sg_flag_combat_round', 53 );
define( 'sg_flag_achieve_equip', 54 );
define( 'sg_flag_lost_treasury', 55 );
define( 'sg_flag_account_bit_options', 56 );
define( 'sg_flag_recipes', 57 );
define( 'sg_flag_pravokan_reveler_srand', 58 );
define( 'sg_flag_pravokan_reveler_count', 59 );
define( 'sg_flag_great_labyrinth', 60 );
define( 'sg_flag_combat_force_encounter_id', 61 );
define( 'sg_flag_combat_force_encounter_type', 62 );
define( 'sg_flag_top_rune_damage', 63 );
define( 'sg_flag_plot_used', 64 );

define( 'sg_plotflag_installed', 1 );
define( 'sg_plotflag_installed_2', 2 );


define( 'sg_css_version', 7 );

define( 'sg_css_bgflag_beige', 1 );

define( 'sg_auction_sell', 1 );
define( 'sg_auction_request', 2 );

define( 'sg_auctionsort_time', 1 );
define( 'sg_auctionsort_cost', 2 );
define( 'sg_auctionsort_charname', 3 );
define( 'sg_auctionsort_artifactname', 4 );

define( 'sg_scalingfoe', 141 );

define( 'sg_store_ui_bought', 0 );
define( 'sg_store_ui_sold', 1 );
define( 'sg_store_ui_not_sold_here', 2 );
define( 'sg_store_ui_invalid_amount', 3 );
define( 'sg_store_ui_no_money', 4 );
define( 'sg_store_ui_no_rep', 5 );
define( 'sg_store_ui_no_quantity', 6 );
define( 'sg_store_ui_cant_sell', 7 );

define( 'sg_flagui_show_tip', 0 );

define( 'sg_quest_in_progress', 0 );
define( 'sg_quest_done', 1 );

define( 'sg_dailyflag_sweet_1', 0 );
define( 'sg_dailyflag_sweet_2', 1 );

define( 'sg_encountertype_foe', 1 );
define( 'sg_encountertype_duel', 2 );
define( 'sg_encountertype_choice', 3 );

define( 'sg_duel_enabled', 1 );

define( 'sg_duel_challenge_recv', 1 );
define( 'sg_duel_challenge_sent', 2 );

define( 'sg_artifact_flag_nosell', 0 );
define( 'sg_artifact_flag_notrade', 1 );
define( 'sg_artifact_flag_disenchantable', 2 );

define( 'sg_track_foe', 1 );
define( 'sg_track_use', 2 );
define( 'sg_track_loot', 3 );

define( 'sg_tracking_enabled', 1 );
define( 'sg_achievements_enabled', 1 );
define( 'sg_allies_enabled', 1 );


?>