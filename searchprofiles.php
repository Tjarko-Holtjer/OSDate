<?php

// rewrite this page

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

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
$t->assign ('sort_order',  $_REQUEST['sort_order'] );
$searchtype = $_REQUEST['searchtype'];
$searchby = $_REQUEST['searchby'];
$searchtxt = $_REQUEST['txtsearch'];

$sortme = ' order by user.username';


if ($_SESSION['sort_by'] != '' and $_REQUEST['sort_by'] == '') {

	$_REQUEST['sort_by'] = $_SESSION['sort_by'];

}

if ($_REQUEST['sort_by'] != '') {

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
}

$t->assign('sort_by', $sort_by);

$t->assign('sort_order', $sort_order);

if ( $searchtype == 'simple' ) {

	if ( $searchby == 'username' ) {

			$sql = 'SELECT DISTINCT user.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ' . USER_TABLE .
			" as user, ".MEMBERSHIP_TABLE." as mem WHERE mem.roleid=user.level and mem.includeinsearch=1 and status in ('active','".get_lang('status_enum','active')."') AND user.id > 0 AND user.id<>'" . $_SESSION['UserId'] .
			"' AND upper(user." . $searchby . ") like upper('%" . $searchtxt . "%') ";

			$querystring = array('searchtype'=>'simple','searchby'=>$searchby,'txtsearch'=>$searchtxt);

	} elseif ( ( $searchby == 'country' ) || ( $searchby == 'state_province' ) ) {

		if ( $searchtxt == 'AA' ) {

			$sql = 'SELECT DISTINCT user.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ' . USER_TABLE .
			" as user, ".MEMBERSHIP_TABLE." as mem WHERE mem.roleid=user.level and mem.includeinsearch=1 and status in ('active','".get_lang('status_enum','active')."') AND user.id > 0 AND user.id<>'" . $_SESSION['UserId'] .
			"' AND user." . $searchby . " <> '" . $searchtxt . "' ";

		} else {

			$sql = 'SELECT DISTINCT user.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ' . USER_TABLE .
			" as user, ".MEMBERSHIP_TABLE." as mem WHERE mem.roleid=user.level and mem.includeinsearch=1 and status in ('active','".get_lang('status_enum','active')."') AND user.id > 0 AND user.id<>'" . $_SESSION['UserId'] .
			"' AND user." . $searchby . "= '" . $searchtxt . "'" ;

		}
		$querystring = array('searchtype'=>'simple','searchby'=>$searchby,'txtsearch'=>$searchtxt);

	} elseif ( ( $searchby == 'zip' ) || ( $searchby == 'city' ) ) {

			$sql = 'SELECT DISTINCT user.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ' . USER_TABLE .
			" as user , ".MEMBERSHIP_TABLE." as mem WHERE mem.roleid=user.level and mem.includeinsearch=1 and status in ('active','".get_lang('status_enum','active')."') AND user.id > 0 AND user.id<>'" . $_SESSION['UserId'] .
			"' AND user." . $searchby . " like '%" . $searchtxt . "%'";

		$querystring = array('searchtype'=>'simple','searchby'=>$searchby,'txtsearch'=>$searchtxt);

	} elseif ( (int)$searchby )  {

			$sql = 'SELECT  user.*,  floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ' . USER_TABLE .
			" as user, " . USER_PREFERENCE_TABLE . " as pref , ".MEMBERSHIP_TABLE." as mem WHERE mem.roleid=user.level and mem.includeinsearch=1 and user.id=pref.userid  AND status in ('active','".get_lang('status_enum','active')."') AND user.id > 0 AND user.id<>'" . $_SESSION['UserId'] ."'";
			$keyval = '';

			//get the names of checkboxes of search page
			// and add them to the 'question in' portion of the query
			$flag = false;

			foreach ( $_REQUEST as $key => $item ){

				if ( is_array($item) && (int)$key != 0){

					if( $flag == false){

						$sql .= ' AND pref.questionid in(  ';

						$flag = true;

					}

					$sql .= " " . $key . ", ";

					$keyval = $key;
				}
			}

			if ( $flag==true ){

				$sql = substr( $sql, 0, strlen($sql) -2 );

				$sql .= " )";
			}

			//get the values of checkboxes of search page
			// and add them to the 'answer in' portion of the query

			$flag = false;

			foreach ( $_REQUEST as $key => $item ){

				if ( is_array($item) && (int)$key != 0){

					foreach( $item as $key1 => $item1 ){

						if( $flag == false ){

							$sql .= ' AND pref.answer in(  ';

							$flag = true;
						}

						$sql .= " " . $item1 . ", ";

						$querystring[$keyval] = $item1;
// . '%5B%5D=' .
					}
				}
			}

			if ( $flag == true ){

				$sql = substr( $sql, 0, strlen($sql) -2 );

				$sql .= " )";

			}

		$querystring['searchtype']='simple';
		$querystring['searchby']=$searchby;
	}

	$sql .=  $sortme;

	$cpage = $_REQUEST['page'];

	if( $cpage == '' ) $cpage = 1;

	$rs = $db->query( $sql );

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

	$data = $db->getAll( $sql );

	if ( count($data) <= 0 ) {

		$t->assign ( 'error', "1" );

		$t->assign ( 'backlink', 'search.php' );

		$t->assign ( 'lang', $lang );

		$t->assign('rendered_page', $t->fetch('showsimpsh.tpl') );

		$t->display ( 'index.tpl' );

		exit;

	} else {

		if ( $_REQUEST['savesearch'] == 'on' ) {

			$sqlins = 'INSERT INTO ! ( userid,	query)	VALUES 	( ?,? )';

			$rsins = $db->query( $sqlins, array( USER_SEARCH_TABLE, $_SESSION['UserId'], addslashes( $sql ) ));
		}

		hasRight('');

		$t->assign  ( 'querystring', $querystring);

		$t->assign( 'sort_type', checkSortType( $_REQUEST['type'] ) );

		$t->assign ( 'backlink', 'search.php' );

		$users_data = array();

		foreach($data as $row) {

			$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

			$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

			$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

			$users_data[] = $row;

		}

		$t->assign ( 'data', $users_data );

		$t->assign ( 'lang', $lang );

		$t->assign('rendered_page', $t->fetch('showsimpsh.tpl') );

		$t->display ( 'index.tpl' );

		exit;
	}


} elseif ( $_REQUEST['searchtype'] == 'advance' ) {

	$sql = 'SELECT DISTINCT user.*, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! as user, ! as mem WHERE mem.roleid=user.level and mem.includeinsearch=1 and user.id > 0 AND ';

	if ( $_REQUEST['txtcountry'] == 'AA' ){

		$sql .= "country <> 'AA' ";
	} else {
		$sql .= "country = '" . ltrim(rtrim($_REQUEST['txtcountry'])) . "' ";
	}

	if ( $_REQUEST['txtstates'] ) {

		if ( $_REQUEST['txtstates'] == '*' or $_REQUEST['txtstates'] == 'AA' ) {

			$sql .= ' AND state_province IS NOT NULL ';

		} else {

			$sql .= " AND upper(state_province) like upper('%" . $_REQUEST['txtstates'] . "%') ";
		}
	}

	if ( $_REQUEST['txtcity'] ) {

		if ( $_REQUEST['txtcity'] == '*' ) {

			$sql .= ' AND city IS NOT NULL ';

		} else {

			$sql .= " AND city='" . $_REQUEST['txtcity'] . "' ";
		}
	}

	if ( $_REQUEST['txtzip'] ) {

		if ( $_REQUEST['txtzip'] == '*' ) {

			$sql .= ' AND zip IS NOT NULL ';

		} else {
			$sql .= " AND zip='" . $_REQUEST['txtzip'] . "' ";
		}
	}

	$sql .= $sortme;

	$rs = $db->query( $sql, array( USER_TABLE, MEMBERSHIP_TABLE ) );

	//code for paging
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

		$sql .= " limit $start, $psize"	;
	}

	$rs = $db->query ( $sql, array( USER_TABLE, MEMBERSHIP_TABLE ) );

	$querystring = array('txtcountry'=> $_REQUEST['txtcountry'],
		'txtstate'=>$_REQUEST['txtstate'],
		'txtcity'=> $_REQUEST['txtcity'],
		'txtzip'=> $_REQUEST['txtzip'],
		'searchtype'=>$_REQUEST['searchtype']
		);

	$t->assign  ( 'querystring', $querystring);

	showPageA ( $rs );

	exit;

}


function showPageA ( $rs ) {

	global $lang;
	global $t;
	global $db;

	//No Record found
	if ( $rs->numRows() == 0 ) {

		$t->assign ( 'error', '1' );

		$t->assign ( 'lang', $lang );

		$t->assign ( 'backlink', 'search.php' );

		$t->assign('rendered_page', $t->fetch('showsimpsh.tpl') );

		$t->display ( 'index.tpl' );

	}else {
		//If records exists
		while ( $row = $rs->fetchRow() ) {

			$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

			$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

			$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

			$data[] = $row;

		}

		$t->assign ( 'data', $data );

		$t->assign ( 'lang', $lang );

		$t->assign ( 'backlink', 'search.php' );

		$t->assign('rendered_page', $t->fetch('showsimpsh.tpl') );

		$t->display ( 'index.tpl' );

	}
}


function showPageB ( $rs, $srchby, $srchatname ) {

	global $lang;
	global $db;
	global $t;

	//No Record found
	if ( $rs->numRows() == 0 ) {

		$t->assign ( 'error', '1' );

		$t->assign ( 'lang', $lang );

		$t->assign('rendered_page', $t->fetch('showsimpsh.tpl') );

		$t->display ( 'index.tpl' );

	} else {
		//If records exists
		while ( $row = $rs->fetchRow() ) {

			$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

			$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

			$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

			$data[] = $row;
		}

		$t->assign ( 'data', $data );

		$t->assign ( 'lang', $lang );

		$t->assign('rendered_page', $t->fetch('showsimpsh.tpl') );

		$t->display ( 'index.tpl' );
	}
}

?>