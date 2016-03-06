<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

// rewrite this page later

if ( isset( $_GET['results_per_page'] ) && $_GET['results_per_page'] ) {

	$psize = $_GET['results_per_page'];

	$config['search_results_per_page'] = $_GET['results_per_page'] ;

	$_SESSION['ResultsPerPage'] = $_GET['results_per_page'];

} elseif ( $_SESSION['ResultsPerPage'] != '' ) {

	$psize = $_SESSION['ResultsPerPage'];

	$config['search_results_per_page'] = $_SESSION['ResultsPerPage'] ;

} else {

	$psize = $config['search_results_per_page'];

	$_SESSION['ResultsPerPage'] = $config['search_results_per_page'];
}

$t->assign ( 'psize',  $psize );

// rewrite later

$sql = 'SELECT u.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! u, ! ou WHERE u.allow_viewonline=? AND u.status in (?,?) AND u.id = ou.userid ';


if( isset( $_SESSION['UserId'] ) && $_SESSION['UserId'] != '' ){

	$sql .= ' and u.id<>' . $_SESSION['UserId'];
}

$cpage = $_GET['page'];

if( $cpage == '' ) {
	$cpage = 1;
}

$rs = $db->query( $sql, array( USER_TABLE, ONLINE_USERS_TABLE, '1', get_lang('status_enum','active'), 'active') );

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

$data = $db->getAll( $sql, array( USER_TABLE, ONLINE_USERS_TABLE, '1', get_lang('status_enum','active'), 'active' ) );

$list = array();

foreach ($data as $row) {

	$countryname = $db->getOne('select name from ! where code = ?', array(COUNTRIES_TABLE, $row['country'] ) );

	$statename = $db->getOne('select name from ! where code = ? and countrycode = ?', array(STATES_TABLE, $row['state_province'], $row['country'] ) );

	$row['countryname'] = $countryname;

	$row['statename'] = ($statename != '') ? $statename : $row['state_province'];

	$list[] = $row;
}

if ( sizeof( $data ) == 0 ) {

	$t->assign ( 'error', "1" );

} else {

	hasRight('');

	$t->assign ( 'data', $list );

}

$t->assign ( 'lang', $lang );

$t->assign('rendered_page', $t->fetch('onlineusers.tpl') );

$t->display ( 'index.tpl' );

exit;

?>