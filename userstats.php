<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );
//copied from mymatches
//why recode it when it's allready there?

//edit w00t w00t it works. Done with the hard part. On to the fun stuff :)

$sqlSelect = 'SELECT *, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age';

$user = $db->getRow( $sqlSelect.' FROM ! WHERE id = ?',array( USER_TABLE,  $_SESSION['UserId']) );

$sqlFrom = ' FROM ! user ';

$sqlWhere = ' WHERE id > 0  ';

$txtgender_search = " AND user.lookgender in  ( 'A', ";

if ($user['gender'] == 'M' or $user['gender'] == 'F') {
	$txtgender_search .= "'B',";
}

$txtgender_search .= "'".$user['gender']."' )";


$txtlookgender_search = '';

if ($user['lookgender'] == 'B') {
	$txtlookender_search = " AND user.gender in ('M','F') ";
} elseif ($user['lookgender'] != 'A') {
	$txtlookgender_search = " AND user.gender = '".$user['lookgender']."' ";
}

$sqlWhere .= ' AND status in (\'Active\',\' '.get_lang('status_enum','active')."') " . $txtlookgender_search . $txtgender_search;

if( $user['lookcountry'] ){

	if( $user['lookcountry'] == 'AA' ) {

		$sqlWhere .= ' AND country LIKE \'%\' ';

	}else{

	$sqlWhere .= ' AND country = \'' . $user['lookcountry'] ."' ";
	}
}

if( $user['lookstate_province'] ){

	if( $user['lookstate_province'] == 'AA' ) {

		$sqlWhere .= ' AND state_province LIKE \'%\' ';

	}else{

	$sqlWhere .= ' AND state_province = \'' . $user['lookstate_province'] ."' ";
	}
}

if( $user['lookcounty'] ){

	if( $user['lookcounty'] == 'AA' ) {

		$sqlWhere .= ' AND county LIKE \'%\' ';

	}else{

	$sqlWhere .= ' AND county = \'' . $user['lookcounty'] ."' ";
	}
}

if( $user['lookcity'] ) {

	if( $user['lookcity'] == 'AA' ) {

		$sqlWhere .= ' AND city LIKE \'%\' ';

	}else{

	$sqlWhere .= ' AND city = \'' . $user['lookcity'] ."' ";
	}

}

if( $user['zip'] ) {

	if( $user['lookcity'] == 'AA' ) {

		$sqlWhere .= ' AND city LIKE \'%\' ';

	}else{

	$sqlWhere .= ' AND city = \'' . $user['lookcity'] ."' ";
	}

}
$sqlWhere .= ' AND floor(period_diff(extract(year_month from NOW()),extract(year_month from birth_date))/12) BETWEEN '
	. $user['lookagestart'] . ' AND ' . $user['lookageend'] ;

$sql = $sqlSelect . $sqlFrom . $sqlWhere;

$CountSelect = 'select count(*) as cnt ';

$same_countrysql = ' FROM ! WHERE country = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samecountrysql = $db->getOne( $CountSelect.$same_countrysql, array ( USER_TABLE, $user['country'], $user['username']  ) );

$same_statesql = ' FROM ! WHERE country = ? and state_province = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samestatesql = $db->getOne( $CountSelect.$same_statesql , array( USER_TABLE, $user['country'], $user['state_province'], $user['username'] ) );

$same_countysql = ' FROM ! WHERE country = ? and state_province = ? and county = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samecountysql = $db->getOne( $CountSelect.$same_countysql , array( USER_TABLE, $user['country'], $user['state_province'], $user['county'], $user['username'] ) );

$same_citysql = ' FROM ! WHERE country = ? and state_province = ? and county = ? and city = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samecitysql = $db->getOne( $CountSelect.$same_citysql, array ( USER_TABLE, $user['country'], $user['state_province'], $user['county'], $user['city'], $user['username']  ) );

$same_zipcodesql = ' FROM ! WHERE country = ? and zip = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samezipcodesql = $db->getOne( $CountSelect.$same_zipcodesql, array ( USER_TABLE, $user['country'],  $user['zip'], $user['username']  ) );

$same_gendersql = ' FROM ! WHERE gender = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samegendersql = $db->getOne( $CountSelect.$same_gendersql, array ( USER_TABLE, $user['gender'], $user['username']  ) );

$same_agesql = ' FROM ! WHERE username <> ? and floor(period_diff(extract(year_month from NOW()),extract(year_month from birth_date))/12) = ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_sameagesql = $db->getOne( $CountSelect.$same_agesql, array ( USER_TABLE, $user['username'], $user['age'] ) );

$same_lookagestartsql = ' FROM ! WHERE username <> ? and floor(period_diff(extract(year_month from NOW()),extract(year_month from birth_date))/12) >= ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samelookagestartsql = $db->getOne( $CountSelect.$same_lookagestartsql, array ( USER_TABLE, $user['username'], $user['lookagestart']  ) );

$same_lookageendsql = ' FROM ! WHERE username <> ? and floor(period_diff(extract(year_month from NOW()),extract(year_month from birth_date))/12) <= ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samelookageendsql = $db->getOne( $CountSelect.$same_lookageendsql, array ( USER_TABLE, $user['username'], $user['lookageend']  ) );

//$same_lookgendersql = 'SELECT count(*) FROM ! WHERE lookgender = ?';
$same_lookgendersql = ' FROM ! WHERE username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
if ($user['lookgender'] == 'B') {
	$same_lookgendersql .= " and gender in ('M','F') ";
} elseif ($user['lookgender'] == 'A') {
} else {
	$same_lookgendersql .= " and gender = '".$user['lookgender']."'";
}
$count_samelookgendersql = $db->getOne( $CountSelect.$same_lookgendersql, array ( USER_TABLE, $user['username']  ) );

//$same_lookcountrysql = 'SELECT count(*) FROM ! WHERE lookcountry = ?';
$same_lookcountrysql = ' FROM ! WHERE country = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samelookcountrysql = $db->getOne( $CountSelect.$same_lookcountrysql, array ( USER_TABLE, $user['lookcountry'], $user['username']  ) );

$same_lookstatesql = ' FROM ! WHERE country = ? and state_province = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active') . '\')' ;
$count_samelookstatesql = $db->getOne( $CountSelect.$same_lookstatesql, array ( USER_TABLE, $user['lookcountry'], $user['lookstate_province'], $user['username']  ) );

$same_lookcountysql = ' FROM ! WHERE country = ? and state_province = ? and county = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active') . '\')' ;
$count_samelookcountysql = $db->getOne( $CountSelect.$same_lookcountysql, array ( USER_TABLE, $user['lookcountry'], $user['lookstate_province'], $user['lookcounty'], $user['username']  ) );

//$same_lookcitysql = 'SELECT count(*) FROM ! WHERE lookcity = ?';
$same_lookcitysql = ' FROM ! WHERE country = ? and state_province = ? and county = ? and  city = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samelookcitysql = $db->getOne( $CountSelect.$same_lookcitysql, array ( USER_TABLE, $user['lookcountry'], $user['lookstate_province'], $user['lookcounty'], $user['lookcity'], $user['username']  ) );

//$same_lookzipsql = 'SELECT count(*) FROM ! WHERE lookzip = ?';
$same_lookzipsql = ' FROM ! WHERE country = ? and state_province = ? and county = ? and  city = ? and zip = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samelookzipsql = $db->getOne( $CountSelect.$same_lookzipsql, array ( USER_TABLE, $user['lookcountry'], $user['lookstate_province'], $user['lookcounty'], $user['lookcity'], $user['lookzip'], $user['username']  ) );

$same_looktimezonesql = ' FROM ! WHERE timezone = ? and username <> ? AND status in (\'Active\',\'' .get_lang('status_enum','active'). '\')' ;
$count_samelooktimezonesql = $db->getOne( $CountSelect.$same_looktimezonesql, array ( USER_TABLE, $user['timezone'], $user['username']  ) );

switch ($_GET['show']){
case 'match':
	$data = $db->getAll ( $sql, array( USER_TABLE ) );

	$users = array();

	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'samecountry':
$data = $db->getAll ( $sqlSelect.$same_countrysql , array( USER_TABLE, $user['country'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'samestate':
	$data = $db->getAll ( $sqlSelect.$same_statesql , array( USER_TABLE, $user['country'], $user['state_province'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'samecounty':
	$data = $db->getAll ( $sqlSelect.$same_countysql , array( USER_TABLE, $user['country'], $user['state_province'], $user['county'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'samecity':
	$data = $db->getAll ( $sqlSelect.$same_citysql , array( USER_TABLE, $user['country'], $user['state_province'], $user['county'], $user['city'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'samezip':
	$data = $db->getAll ( $sqlSelect.$same_zipcodesql , array( USER_TABLE, $user['country'],  $user['zip'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'samegender':
	$data = $db->getAll ( $sqlSelect.$same_gendersql , array( USER_TABLE, $user['gender'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'sameage':
	$data = $db->getAll ( $sqlSelect.$same_agesql , array( USER_TABLE, $user['username'], $user['age'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'lookagestart':
	$data = $db->getAll ( $sqlSelect.$same_lookagestartsql , array( USER_TABLE, $user['username'], $user['lookagestart'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'lookageend':
	$data = $db->getAll ( $sqlSelect.$same_lookagestartsql , array( USER_TABLE, $user['username'], $user['lookagestart'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'lookgender':
	$data = $db->getAll ( $sqlSelect.$same_lookgendersql , array( USER_TABLE, $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'lookcountry':
	$data = $db->getAll ( $sqlSelect.$same_lookcountrysql , array( USER_TABLE, $user['lookcountry'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'lookstate':
	$data = $db->getAll ( $sqlSelect.$same_lookstatesql , array( USER_TABLE, $user['lookcountry'], $user['lookstate_province'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'lookcounty':
	$data = $db->getAll ( $sqlSelect.$same_lookcountysql , array( USER_TABLE, $user['lookcountry'], $user['lookstate_province'], $user['lookcounty'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'lookcity':
	$data = $db->getAll ( $sqlSelect.$same_lookcitysql , array( USER_TABLE, $user['lookcountry'], $user['lookstate_province'], $user['lookcounty'], $user['lookcity'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'lookzip':
	$data = $db->getAll ( $sqlSelect.$same_lookzipsql , array( USER_TABLE, $user['lookcountry'], $user['lookstate_province'], $user['lookcounty'], $user['lookcity'], $user['lookzip'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
case 'looktimezone':
	$data = $db->getAll ( $sqlSelect.$same_looktimezonesql , array( USER_TABLE, $user['timezone'], $user['username'] ) );

	$users = array();
	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$users[] = $row;
	}
	break;
default:
	break;
}

$rs = $db->query( $sql, array( USER_TABLE ) );

$rcount = $rs->numRows();

if (isset($_GET['show'])){

	$t->assign ( 'show', '1' );

	$t->assign ( 'data', $users );

}

//phew all that above me is all for the first stat :(



//wtf I think there is a bug with pear
//whenever i pass same_stat I always get 0 for a stat
//and I've run the sql in phpmyadmin to make sure I was
//doing it right
//phpmyadmin says the query is correct
//hmm....

// $same_stat = 'SELECT count(*) FROM ! WHERE ? = ?';

//$same_state = $db->getAll( $same_statesql, array ( USER_TABLE, $user['state_province'] ) );
//if ($same_state > 0)
//$same_state--;

//$t->assign ( 'same_level', $same_level );

//$t->assign ( 'same_timeregister', $same_timeregister );

//$t->assign ( 'user_posts', $user_posts );

$t->assign ( 'same_timezone', $count_samelooktimezonesql );

$t->assign ( 'same_lookstate', $count_samelookstatesql );

$t->assign ( 'same_lookzip', $count_samelookzipsql );

$t->assign ( 'same_lookcity', $count_samelookcitysql );

$t->assign ( 'same_lookcountry', $count_samelookcountrysql );

$t->assign ( 'same_lookcounty', $count_samelookcountysql );

$t->assign ( 'same_lookgender', $count_samelookgendersql );

$t->assign ( 'same_lookageend', $count_samelookageendsql );

$t->assign ( 'same_lookagestart', $count_samelookagestartsql );

$t->assign ( 'same_age', $count_sameagesql );

$t->assign ( 'same_sex', $count_samegendersql );

$t->assign ( 'same_zip', $count_samezipcodesql );

$t->assign ( 'same_country', $count_samecountrysql );

$t->assign ( 'same_county', $count_samecountysql );

$t->assign ('same_state', $count_samestatesql );

$t->assign ( 'same_city', $count_samecitysql );

$t->assign ( 'number_matches', $rcount );

$t->assign('lang', $lang);

$t->assign('rendered_page', $t->fetch('userstats.tpl') );

$t->display( 'index.tpl' );

exit;

?>