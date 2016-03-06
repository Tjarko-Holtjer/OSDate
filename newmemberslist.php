<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$list_newmembers_since_days = $config['list_newmembers_since_days'];

if ($list_newmembers_since_days == '') $list_newmembers_since_days=0;

$list_newmembers_since = strtotime("-$list_newmembers_since_days day",time());

if ( isset( $_GET['results_per_page'] ) && $_GET['results_per_page'] ) {

	$psize = $_GET['results_per_page'];

	$config['search_results_per_page'] = $_GET['results_per_page'] ;

	$_SESSION['ResultsPerPage'] = $_GET['results_per_page'];

} elseif( $_SESSION['ResultsPerPage'] != '' ) {

	$psize = $_SESSION['ResultsPerPage'];

	$config['search_results_per_page'] = $_SESSION['ResultsPerPage'] ;

} else {

	$psize = $config['page_size'];

	$_SESSION['ResultsPerPage'] = $config['page_size'];

}


$sqlNew = "SELECT id, username, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age, gender, city, country, state_province, county, regdate  FROM ! WHERE status in (?, ?)  and regdate >= ? ";

if (!isset($_REQUEST['orderby']) && $_SESSION['orderby'] != '') {

	$orderby = $_SESSION['orderby'];

} else {
	switch ($_REQUEST['orderby']) {
		case 'gender' :
			$orderby = ' gender ';
			break;
		case 'age' :
			$orderby = ' age ';
			break;
		case 'country':
			$orderby = ' country ';
			break;
		case 'city' :
			$orderby = ' country asc, city ';
			break;
		case 'sincedate':
			$orderby = ' regdate ';
			break;
		case 'username':
		default:
		$orderby = ' username ';
	}
	$_SESSION['orderby'] = trim($orderby);

}

if (!isset($_REQUEST['sortorder']) && $_SESSION['sortorder'] != '') {

	$sortorder = $_SESSION['sortorder'];

} else {

	$sortorder = checkSortType($_REQUEST['sortorder']);
}

$_SESSION['sortorder'] = $sortorder;

$sqlNew .=  ' ORDER BY '.$orderby.' '.$sortorder;


$reccount = $db->getOne('select count(*) from ! WHERE status in (?, ?)  and regdate >= ? ',array( USER_TABLE, 'active', get_lang('status_enum','active') , $list_newmembers_since ) );

$t->assign ( 'psize',  $psize );

$page = (int)$_REQUEST['page'];

if( $page == 0 ) $page = 1;

$upr = ($page * $psize )- $psize;

$pages = ceil( $reccount / $psize );

$cpage = $page;

if( $pages > 1 ) {

	$sqlNew .= ' limit '.$upr.','.$psize;

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


$t->assign ( 'pages',  $pages );

$t->assign ( 'reccount',  $reccount );

$t->assign('sortorder', trim($sortorder));

$t->assign('orderby', $_REQUEST['orderby']);

$t->assign( 'lang', $lang );

$newmembers = $db->getAll($sqlNew, array( USER_TABLE, 'active', get_lang('status_enum','active') , $list_newmembers_since ) );

$newmemberslist = array();

foreach ($newmembers as $newmemrec) {
	$cityname = getCityName($newmemrec['country'],$newmemrec['state_province'], $newmemrec['city'], $newmemrec['county'] );
	$newmemrec['city'] = $cityname;
	$newmemberslist[] = $newmemrec;
}

$t->assign('newmemberslist',$newmemberslist);

$t->assign('rendered_page', $t->fetch('newmemberslist.tpl') );

$t->display( 'index.tpl' );

exit;
?>