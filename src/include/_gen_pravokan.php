<?

function getGeneratedFoeName( $srand ) {
    $gen_title = array(
        'Privateer', 'Skipper', 'Lieutenant Commander', 'Ensign',
        'Midshipman', 'Petty Officer', 'Sublieutenant', 'Cadet',
        'Private', 'Sailor', 'Seal', 'Steersman', 'Sea Bear',
        'Seaman', 'Shipboy', 'Handyman', 'Longshoreman', 'Repairman',
        'Trader', 'Guard', 'Swordsmith', 'Small', 'Dreaded', 'Great',
    );

    $gen_adj = array(
        'Barbaric', 'Boorish', 'Brusque', 'Cantankerous',
        'Charming', 'Churlish',
        'Clumsy', 'Crabby', 'Cranky',
        'Cross', 'Crunchy', 'Crusty', 'Curmudgeonly',
        'Disagreeable', 'Discourteous', 'Doctor',
        'Dour', 'Entertaining',
        'Fretful', 'Gentle', 'Gloomy',
        'Glum', 'Gross', 'Grouchy',
        'Gruff', 'Grumpy', 'Ill',
        'Irresistable', 'Irritable', 'Loud', 'Lowbred',
        'Master', 'Morose', 'Nasty', 'Oafish', 'Peevish',
        'Perverse', 'Petulant', 'Pleasant',
        'Polite', 'Rough',
        'Rude', 'Rustic', 'Snappish',
        'Snappy', 'Sophisticated', 'Sour',
        'Sulky', 'Sullen', 'Tasteless', 'Testy',
        'Ugly', 'Ungracious', 'Vulgar',
    );

    $gen_name = array(
        'Trey', 'Norberto', 'Napoleon', 'Jerold', 'Fritz',
        'Rosendo', 'Milford', 'Sang', 'Deon', 'Alfonzo', 'Josiah',
        'Brant', 'Wilton', 'Dewitt', 'Brenton', 'Olin', 'Foster',
        'Faustino', 'Claude', 'Judson', 'Berry', 'Alec', 'Tanner',
        'Jarred', 'Donn', 'Tad', 'Odis', 'Chauncey', 'Tod', 'Augustus',
        'Keven', 'Hilario', 'Orval', 'Olen', 'Alibal', 'Jed',
        'Newton', 'Lenny', 'Tory', 'Delmer', 'Reyes', 'Jonah',
        'Robt', 'Rupert', 'Roland', 'Rolland', 'Kenton', 'Damion',
        'Antone', 'Fredric', 'Bradly', 'Quinn', 'Kip', 'Burl', 'Torm',
        'Walker', 'Willy', 'Noble', 'Mikel', 'Darrick', 'Tobias',
        'DeMarcus', 'Cletus', 'Tyrell', 'Lyndon', 'Keenan', 'Werner',
        'Theo', 'Geraldo', 'Lou', 'Chet', 'Bertram', 'Markus', 'Huey',
        'Hilton', 'Dwain', 'Tyron', 'Omer', 'Fermin', 'Valentine',
        'Garfield', 'Jon', 'Scott', 'Mike', 'Chris', 'Paul', 'Garrett',
        'King', 'Paris', 'Dong', 'Darron', 'Buster', 'Renato', 'Chas',
        'Elroy', 'Arden', 'Clemente', 'Neville', 'Carrol', 'Shayne',
        'Jordon', 'Danilo', 'Claud', 'Val', 'Sherwood', 'Zack', 'Josh',
        'Lino', 'Herb', 'Andreas', 'Walton', 'Palmer', 'Cordell',
        'Warner', 'Modesto', 'Malik', 'Michale', 'Zackary', 'Nicky',
        'Eldridge', 'Antione', 'Korey', 'Colton', 'Lucius', 'Boyce',
        'Man', 'Mario', 'Luigi',
    );

    srand( $srand );
    $gen_full =
        $gen_adj[ rand( 0, count( $gen_adj ) - 1 ) ] . ' ' .
        $gen_name[ rand( 0, count( $gen_name ) - 1 ) ] . ', the ' .
        $gen_title[ rand( 0, count( $gen_title ) - 1 ) ];
    list( $usec, $sec ) = explode( ' ', microtime() );
    $new_srand = ( float ) $sec + ( ( float ) $usec * 100000 );
    srand( $new_srand );
    return $gen_full;
}

?>