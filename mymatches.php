<?php
if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );
}

include( 'sessioninc.php');

if ( $_GET['results_per_page'] ) {

	$psize = $_GET['results_per_page'];

	$config['search_results_per_page'] = $_GET['results_per_page'] ;

	$_SESSION['ResultsPerPage'] = $_GET['results_per_page'];

} elseif( $_SESSION['ResultsPerPage'] != '' ) {

	$psize = $_SESSION['ResultsPerPage'];

	$config['search_results_per_page'] = $_SESSION['ResultsPerPage'] ;

} else {

	$psize = $config['search_results_per_page'];

	$_SESSION['ResultsPerPage'] = $config['search_results_per_page'];

}

$t->assign ( 'psize',  $psize );

$user = $db->getRow( 'SELECT * FROM ! WHERE id = ?',array( USER_TABLE,  $_SESSION['UserId']) );

$radius = $user['lookradius'];

$radiustype = $user['radiustype'];

$zipcodes_in = "";

/* Check for zip code proximity search */
if ($user['lookzip'] != '' && isset($radius) && $radius > 0) {

	$ziprow = $db->getRow('select * from ! where code=?  and countrycode=?',array(ZIPCODES_TABLE, $user['lookzip'], $user['lookcountry'] ) );

	$lat = $ziprow['latitude'];
	$lng = $ziprow['longitude'];

	if ($lng!='' && $lat!='') {

		if ($radiustype == 'kms') {
			/* Kilometers calculation */
			$sql = "select DISTINCT code, sqrt(power(69.1*(latitude - $lat),2) +  power( 69.1 * (longitude-$lng) * cos(latitude/57.3),2)) as dist from ! where countrycode=? and sqrt(power(69.1*(latitude - $lat),2)+power(69.1*(longitude-$lng)*cos(latitude/57.3),2)) <= " . $radius ;
		} else {
			/* Miles  */
			$sql = "select DISTINCT code, (3958* 3.1415926 * sqrt((latitude - $lat) * (latitude- $lat) + cos(latitude / 57.29578) * cos($lat/57.29578)*(longitude - $lng) * (longitude - $lng))/180) as dist from ! where countrycode=? and (3958* 3.1415926 * sqrt((latitude - $lat) * (latitude- $lat) + cos(latitude / 57.29578) * cos($lat/57.29578)*(longitude - $lng) * (longitude - $lng))/180) <= " . $radius ;
		}

		$zipcodes = $db->getAll($sql, array(ZIPCODES_TABLE, $user['lookcountry'] ) );

		foreach ($zipcodes as $val) {
			if ($zipcodes_in != '') $zipcodes_in.=", ";
			$zipcodes_in.= "'".$val['code']."'";
		}

		if ($zipcodes_in != '') {
			$zipcodes_in = " and user.zip in (".$zipcodes_in.") ";
		} else {
			$zipcodes_in = " and user.zip is null ";
		}
	}
}

$bannedlist = '';
$bannedusers = $db->getAll('select usr.id as ref_userid from ! as bdy, ! as usr where bdy.act=? and ((usr.username = bdy.username and bdy.ref_username = ?) or (usr.username = bdy.ref_username and bdy.username = ? ) )', array(BUDDY_BAN_TABLE, USER_TABLE, 'B', $_SESSION['UserName'], $_SESSION['UserName']) );
if (count($bannedusers) > 0) {
	$bannedlist=' and user.id not in (';
	$bdylst = '';
	foreach ($bannedusers as $busr) {
		if ($bdylst != '') $bdylst .= ',';
		$bdylst .= "'".$busr['ref_userid']."'";
	}
	$bannedlist .=$bdylst.') ';
}


$sqlSelect = 'SELECT user.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age';

$sqlFrom = ' FROM ! user, ! mem ';

$sqlWhere = ' WHERE user.id > 0  AND mem.roleid=user.level and mem.includeinsearch=1 ';

$sqlWhere .= $bannedlist;

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

if( $user['lookcity'] ){

	if( $user['lookcity'] == 'AA' ) {

		$sqlWhere .= ' AND city LIKE \'%\' ';

	}else{

	$sqlWhere .= ' AND city = \'' . $user['lookcity'] ."' ";
	}
}

if( $user['lookzip'] ) {
	if (!isset($radius) ){

		if( $user['lookzip'] == 'AA' ) {

			$sqlWhere .= ' AND zip LIKE \'%\' ';

		}else{

			$sqlWhere .= ' AND zip = \'' . $user['lookzip'] ."' ";
		}
	} else {

		$sqlWhere .= $zipcodes_in;
	}
}


$sqlWhere .= ' AND floor(period_diff(extract(year_month from NOW()),extract(year_month from birth_date))/12) BETWEEN '
	. $user['lookagestart'] . ' AND ' . $user['lookageend'] ;

$sql = $sqlSelect . $sqlFrom . $sqlWhere;

$cpage = $_GET['page'];

if( $cpage == '' ) $cpage = 1;

$rs = $db->query( $sql, array( USER_TABLE, MEMBERSHIP_TABLE ) );

$rcount = $rs->numRows();

if( $rcount > 0 ) {

	$t->assign( 'totalrecs', $rcount );

	$pages = ceil( $rcount / $psize );

	$start = ( $cpage - 1 ) * $psize;

	$t->assign ( 'start', $start );

	if( $pages > 1 ) {

		if ( $cpage > 1 ) {

			$prev = $cpage - 1;

			$t->assign( 'prev', $prev );

		}

		$t->assign ( 'cpage', $cpage );

		$t->assign ( 'pages', $pages );

		if ( $cpage < $pages ) {

			$next = $cpage + 1;

			$t->assign ( 'next', $next );

		}
	}

	$sql .= " limit $start, $psize"	;
}

$data = $db->getAll ( $sql, array( USER_TABLE, MEMBERSHIP_TABLE ) );

$t->assign( 'user', $user );

if ( count($data) <= 0 ) {

	$t->assign ( 'error', "1" );

	$t->assign ( 'backlink', 'index.php' );

	$t->assign ( 'lang', $lang );

	$t->assign('rendered_page', $t->fetch('mymatches.tpl') );

	$t->display ( 'index.tpl' );

	exit;

} else {

	$t->assign ( 'backlink', 'index.php' );

	$t->assign ( 'message_right', hasRight('message') );

	$t->assign ( 'view_pic_profile_right', hasRight('seepictureprofile') );

	$users = array();

	foreach ($data as $row) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$row['statename'] = ($row['statename'] == 'AA') ? get_lang('any_where'): $row['statename'];

		$users[] = $row;
	}


	$t->assign ( 'data', $users );

	$t->assign ( 'lang', $lang );

	$t->assign('rendered_page', $t->fetch('mymatches.tpl') );

	$t->display ( 'index.tpl' );

	exit;
}
?>