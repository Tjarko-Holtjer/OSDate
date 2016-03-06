<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$sort = ' username ';
if ( isset( $_REQUEST['results_per_page'] ) && $_REQUEST['results_per_page'] ) {
	$psize = $_REQUEST['results_per_page'];
	$config['search_results_per_page'] = $_REQUEST['results_per_page'] ;
	$_SESSION['ResultsPerPage'] = $_REQUEST['results_per_page'];
} elseif ( $_SESSION['ResultsPerPage'] != '' ) {
	$psize = $_SESSION['ResultsPerPage'];
	$config['search_results_per_page'] = $_SESSION['ResultsPerPage'] ;
} else {
	$psize = $config['search_results_per_page'];
	$_SESSION['ResultsPerPage'] = $config['search_results_per_page'];
}

$t->assign ( 'psize',  $psize );
$x=hasRight('viewpicture');

if ( $_REQUEST['frm'] == 'frmExtSearch' && !isset($_REQUEST['sort']) ){
	$txtgender_search = '';
	if ($_REQUEST['txtgender']) {
		$txtgender_search = ' user.lookgender in  ( ';
		foreach ($_REQUEST['txtgender'] as $key => $val) {
			if ($txtgender_search != ' user.lookgender in  ( ') {
				$txtgender_search .= ', ';
			}
			if ($val == 'A') {
				$txtgender_search = " ( user.lookgender <> 'A'";
			} elseif ($val == 'B') {
				$txtgender_search .= "'M','F'";
			} else {
				$txtgender_search .= "'".$val."'";
			}
		}
		$txtgender_search .= ' ) ';
	}

	$sqlselect = 'SELECT DISTINCT user.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! user INNER JOIN ! pref ON user.id=pref.userid ';
//	$sqlwhere = ' WHERE user.lookgender in '. $txtgender_search;
	$sqlwhere = " WHERE user.id > 0 AND status in ('active','".get_lang('status_enum','active')."') ";
	if ($txtgender_search != '') {
		$sqlwhere .= ' AND '. $txtgender_search;
	}

	if (strlen($_REQUEST['txtlookgender']) > 0 ) {

		$lookgender_search = ' user.gender in  ( ';

		foreach ($_REQUEST['txtlookgender'] as $key => $val) {
			if ($lookgender_search != ' user.gender in  ( ') {
				$lookgender_search .= ', ';
			}
			if ($val == 'A' ) {
				$lookgender_search = "( user.gender <> 'A' ";
			} elseif ($val == 'B' ) {
				$lookgender_search .= "'M','F'";
			} else {
				$lookgender_search .= "'".$val."'";
			}
		}
		$lookgender_search .= ' ) ';
		$sqlwhere .= ' AND '.$lookgender_search;
	}

	if ($_REQUEST['txtlookagestart'] != '' && $_REQUEST['txtlookageend'] != '') {
		$ageselect = " ( floor(period_diff(extract(year_month from NOW()),extract(year_month from birth_date))/12) between '". $_REQUEST['txtlookagestart'] ."' and '". $_REQUEST['txtlookageend']. "' ) ";
		$sqlwhere .= ' AND '.$ageselect;
	}

	if ($_REQUEST['txtfrom'] != 'AA' and $_REQUEST['txtfrom'] != '') {
		$countryselect = " user.country = '".$_REQUEST['txtfrom']."' ";
		$sqlwhere .= ' AND '.$countryselect;
	}

	$prefs = '';
	foreach ( $_REQUEST as $key => $item ) {
		if ( is_array($item) && (int)$key != 0 && (int)$key != 5){
			$control_type = $db->getOne('select control_type from ! where id = ?', array( QUESTIONS_TABLE, (int)$key ) );
			if ($control_type == 'textarea' and count($item) > 0 and $item[0] != '' ) {
				$prefs .= " and (pref.questionid = '".(int)$key."' and upper(pref.answer) like upper('%".$item[0]."%') ) ";
			} elseif ($control_type != 'textarea')  {
				$prefs .= " INNER JOIN ".USER_PREFERENCE_TABLE." pref".(int)$key." ON user.id=pref".(int)$key.".userid  and ( pref".(int)$key.".questionid =  '".(int)$key."' and pref".(int)$key.".answer in (";
				$ans='';
				foreach ($item as $val) {
					if ($ans != '') {
						$ans .= ", ";
					}
					$ans .= "'".$val."'";
				}
				$prefs .= $ans." ) ) ";
			}
			$querystring[$key][] = $val;
		} elseif ( (int)$key == 5 && $item[0] != '' && $item[1] != '')  {
			$prefs .= " and ( pref.questionid = '".(int)$key."' and pref.answer between '".$item[0]."' and '".$item[1]."' ) ";
		}
	}

	$qry = $sqlselect;
	if ($prefs != '') {
		$qry .= $prefs;
	}
	$qry .= $sqlwhere;
	$_SESSION['LastQuery'] = $qry;
	$qry .= ' order by '.$sort;
} elseif ($_REQUEST['sort_by'] != '' or $_REQUEST['page'] != '') 	{
	if ($_REQUEST['page'] != '' && $_REQUEST['sort_by'] == '') { $_REQUEST['sort_by'] = $_SESSION['sort_by']; }
	$_SESSION['sort_by'] = $_REQUEST['sort_by'];
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
	$sortme = " order by ";
	if ($sort_by == 'username') {
		$sortme .= 'user.username ';
	} elseif ( $sort_by == 'age' ) {
		$sortme .= ' age ';
	} elseif ( $sort_by == 'logintime' ) {
		$sortme .= 'user.lastvisit ';
	}
	$sortme .= $sort_order;
	$qry = $_SESSION['LastQuery'].$sortme;
	$t->assign('sort_by',$_REQUEST['sort_by']);
}


$rs = $db->query ( $qry, array(  USER_TABLE, USER_PREFERENCE_TABLE ) );
$cpage = $_REQUEST['page'];
if( $cpage == '' ) $cpage = 1;
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
	$qry .= " limit $start, $psize"	;
}

$rs = $db->getAll( $qry, array( USER_TABLE, USER_PREFERENCE_TABLE ) );
$data = array();
if( $rs) {
	foreach( $rs as $row) {
		$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );
		$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );
		$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];
		$data[] = $row;
	}
} else {
	$t->assign( 'error', 1);
}

$t->assign( 'sort_order', $sort_order );
$t->assign ( 'backlink', 'extsearch.php' );
$t->assign ( 'data', $data );
$t->assign ( 'lang', $lang );
//$t->assign('rendered_page', $t->fetch('showsimpsh_popup.tpl') );
$t->display ( 'showsimpsh_popup.tpl' );

?>