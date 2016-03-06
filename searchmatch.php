<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

if ( $_REQUEST['results_per_page'] ) {

	$psize = $_REQUEST['results_per_page'];

	$config['search_results_per_page'] = $_REQUEST['results_per_page'] ;

	$_SESSION['ResultsPerPage'] = $_REQUEST['results_per_page'];

} elseif( $_SESSION['ResultsPerPage'] != '' ) {

	$psize = $_SESSION['ResultsPerPage'];

	$config['search_results_per_page'] = $_SESSION['ResultsPerPage'] ;

} else {

	$psize = $config['search_results_per_page'];

	$_SESSION['ResultsPerPage'] = $config['search_results_per_page'];
}

$t->assign ( 'psize',  $psize );

$with_photo = $_REQUEST['with_photo'];

$cpage = $_REQUEST['page'];

if( $cpage == '' ) {
	$cpage = 1;
}

$lookgender_search="";

/* Bypass cross matching in search if set in global settings */
if ($config['bypass_search_lookgender'] == 'N' or $config['bypass_search_lookgender'] == '0' ) {
	$lookgender_search = " AND usr.lookgender in ('A' ";
	if ($_REQUEST['txtgender'] == 'M' || $_REQUEST['txtgender'] == 'F') {
		$lookgender_search .= ",'B'";
	}
	$lookgender_search .= ",'".$_REQUEST['txtgender']."') ";
}

$gender_search = " AND usr.gender in ( ";

if ($_REQUEST['txtlookgender'] == 'A') {
	$gender_search .= "'M','F','C'";
} elseif ( $_REQUEST['txtlookgender'] == 'B') {
	$gender_search .= "'M','F'";
} else {
	$gender_search .= "'".$_REQUEST['txtlookgender']."'";
}
$gender_search .= ") ";

if ($_REQUEST['sort_by'] == '') {
	$sort_by='username';
} else {
	$sort_by=$_REQUEST['sort_by'];
}

if ($_REQUEST['sort_order'] == '') {
	$sort_order='asc';
} else {
	$sort_order=$_REQUEST['sort_order'];
}

$t->assign('sort_by', $sort_by);

$sortme = " order by ";

if ($sort_by == 'username') {

	$sortme .= 'usr.username ';

} elseif ( $sort_by == 'age' ) {

	$sortme .= ' age ';

} elseif ( $sort_by == 'logintime' ) {

	$sortme .= 'usr.lastvisit ';
} elseif ( $sort_by == 'online' ) {

	$sortme .= ' onl.is_online desc, usr.username ';
}

$t->assign('sort_order', $sort_order);

$sortme .= $sort_order." ";

$bannedlist = '';
if ($_SESSION['UserId'] != '') {
	$bannedusers = $db->getAll('select usr.id as ref_userid from ! as bdy, ! as usr where bdy.act=? and((usr.username = bdy.username and bdy.ref_username = ?) or (usr.username = bdy.ref_username and bdy.username = ? ))', array(BUDDY_BAN_TABLE, USER_TABLE, 'B', $_SESSION['UserName'], $_SESSION['UserName']) );
	if (count($bannedusers) > 0) {
		$bannedlist=' and usr.id not in (';
		$bdylst = '';
		foreach ($bannedusers as $busr) {
			if ($bdylst != '') $bdylst .= ',';
			$bdylst .= "'".$busr['ref_userid']."'";
		}
		$bannedlist .=$bdylst.') ';
	}
}

if ( $with_photo == '1' ) {

	$sql = 'SELECT distinct usr.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age, onl.is_online  FROM  ! as pics, ! as mem, ! usr left join ! as onl on onl.userid=usr.id WHERE pics.userid = usr.id and mem.roleid=usr.level and mem.includeinsearch=1 AND usr.id > 0 and lower(usr.status) in (?, ?) AND floor(period_diff(extract(year_month from NOW()),extract(year_month from usr.birth_date))/12) BETWEEN ? AND ? ! ! ! ! ';

	$rs = $db->query( $sql, array( USER_SNAP_TABLE, MEMBERSHIP_TABLE, USER_TABLE, ONLINE_USERS_TABLE, 'active', get_lang('status_enum','active'), $_REQUEST['txtlookagestart'], $_REQUEST['txtlookageend'], $bannedlist, $gender_search, $lookgender_search, $sortme ) );

} else {
	$sql = 'SELECT usr.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age, onl.is_online  FROM ! as mem, ! usr left join ! as onl on onl.userid=usr.id WHERE lower(usr.status) in(?, ?) AND mem.roleid = usr.level and mem.includeinsearch=1 AND usr.id > 0 AND floor(period_diff(extract(year_month from NOW()),extract(year_month from usr.birth_date))/12) BETWEEN ? AND ? ! ! ! ! ';

	$rs = $db->query( $sql, array(MEMBERSHIP_TABLE, USER_TABLE, ONLINE_USERS_TABLE,  'active', get_lang('status_enum','active'), $_REQUEST['txtlookagestart'], $_REQUEST['txtlookageend'], $bannedlist, $gender_search, $lookgender_search, $sortme ) );
}

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

if ( $with_photo == '1' ) {

	$rs = $db->query( $sql, array(USER_SNAP_TABLE, MEMBERSHIP_TABLE, USER_TABLE,  ONLINE_USERS_TABLE,  'active', get_lang('status_enum','active'), $_REQUEST['txtlookagestart'], $_REQUEST['txtlookageend'], $bannedlist, $gender_search, $lookgender_search, $sortme ) );

} else {

	$rs = $db->query( $sql, array(MEMBERSHIP_TABLE, USER_TABLE, ONLINE_USERS_TABLE, 'active', get_lang('status_enum','active'), $_REQUEST['txtlookagestart'], $_REQUEST['txtlookageend'], $bannedlist, $gender_search, $lookgender_search, $sortme ) );

}

setcookie($config['cookie_prefix']."osdate_info[search_ages]", $_REQUEST['txtlookagestart'].':'.$_REQUEST['txtlookageend'], strtotime("+30day"), "/" );

$querystring = array(
			'txtgender'			=> $_REQUEST['txtgender'],
			'txtlookgender'		=> $_REQUEST['txtlookgender'],
			'txtlookagestart' 	=> $_REQUEST['txtlookagestart'],
			'txtlookageend'		=> $_REQUEST['txtlookageend'],
			'with_photo'		=> $_REQUEST['with_photo']
			) ;

$_SESSION['txtgender'] = $_REQUEST['txtgender'];
$_SESSION['txtlookgender']= $_REQUEST['txtlookgender'];
$_SESSION['lookageend'] = $_REQUEST['txtlookageend'];
$_SESSION['lookagestart'] = $_REQUEST['txtlookagestart'];
$_SESSION['with_photo'] = $_REQUEST['with_photo'];


if ( $rs->numRows() == 0 ) {

	$t->assign ( 'error', "1" );

	$t->assign('querystring', $querystring);

	$t->assign ( 'backlink', 'searchprofile.php' );

	$t->assign ( 'lang', $lang );

	$t->assign('rendered_page', $t->fetch('showsimpsh.tpl') );

	$t->display ( 'index.tpl' );

	exit;

} else {

	if ( $_REQUEST['savesearch'] == 'on' && isset( $_SESSION['UserId'] ) ) {

		$sqlins = 'INSERT INTO ! ( userid, query) VALUES(? , ?)';

		$rsins = $db->query( $sqlins, array(USER_SEARCH_TABLE, $_SESSION['UserId'], $sql ) );
	}

	$data = array();

	while ( $row = $rs->fetchRow() ) {

		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

		$data[] = $row;
	}

	hasRight('');

	$lang['sort_types'] = get_lang_values('sort_types');

	$t->assign  ( 'querystring', $querystring) ;

	$t->assign ( 'data', $data );

	$t->assign ( 'lang', $lang );

	$t->assign('rendered_page', $t->fetch('showsimpsh.tpl') );

	$t->display ( 'index.tpl' );

	exit;
}

?>