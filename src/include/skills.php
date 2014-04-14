<?

define( 'skill_str', 100 );
define( 'skill_dex', 200 );
define( 'skill_int', 300 );
define( 'skill_cha', 400 );
define( 'skill_con', 500 );

function getSkillId( $base, $depth ) {
    return $base + $depth;
}

function getSkillList() {
    $query = "
      SELECT
        s.*
      FROM
        `skills` AS s
      ORDER BY
        id
    ";

    $results = sqlQuery( $query );
    $skills = array();

    while ( $skill = $results->fetch_assoc() ) {
        $skill[ 'full_description' ] = fixStr( $skill[ 'full_description' ] );
        $skills[ $skill[ 'id' ] ] = $skill;
    }

    return $skills;
}

function getSkillSql( $skill_id ) {
    $s_id = esc( $skill_id );

    $query = "
      SELECT
        s.*
      FROM
        `skills` AS s
      WHERE
        id = $s_id
    ";

    $results = sqlQuery( $query );
    if ( ! $results ) { return FALSE; }

    $skill = $results->fetch_assoc();
    $skill[ 'full_description' ] = fixStr( $skill[ 'full_description' ] );
    return $skill;
}

function getSkill( $skill_id ) {
    $s = esc( $skill_id );

    $filename = '/home/swrittenb/ts_util/skills/' . $s . '.inc';
    if ( file_exists( $filename ) ) {
        include $filename;
        return $skill;
    } else {
        return getSkillSql( $s );
    }
}

function hasSkill( $c_obj, $i ) {
    if ( 0 == $i ) { return TRUE; }
    if ( array_key_exists( $i, $c_obj->c[ 'skills' ] ) ) {
        return $c_obj->c[ 'skills' ][ $i ];
    }
    return FALSE;
}

function learnSkill( $c_obj, $s_id, $perm ) {
    return FALSE;
}
function deleteAllSkills( $c ) {
    return FALSE;
}
/*SKILLS
function learnSkill( $c_obj, $s_id, $perm ) {
    $c_id = $c_obj->c[ 'id' ];
    $s_id = intval( $s_id );
    $perm = intval( $perm );

    $query = "
      INSERT INTO
        `char_skills` (char_id, skill_id, perm)
      VALUES
        ('$c_id', '$s_id', '$perm')
    ";

    $results = sqlQuery( $query );

    unset( $_SESSION[ 'skills' ] );
}

function deleteAllSkills( $c ) {
    $query = "
      DELETE FROM
        `char_skills`
      WHERE
        char_id = " . $c[ 'id' ] . " AND perm = 0
    ";
    $results = sqlQuery( $query );

    unset( $_SESSION[ 'skills' ] );

    return TRUE;
}
*/

?>