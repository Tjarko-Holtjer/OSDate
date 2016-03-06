<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

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
}

$t->assign('sort_order', $sort_order);

$sortme .= $sort_order." ";

$t->assign( 'sort_order', $sort_order );

$t->assign( 'sort_by', $sort_by );

if ( $_POST['searchtype'] == 'simple' ) {

	//In case if search is at zip or city
	$searchatname = $_POST['txtsearch'];

	$srchby = $_POST['searchby'] . ":" ;

	//If Search is at country code or state code
	$sql = 'SELECT name from ';

	if ( $_POST['searchby'] == 'country' ) {

		$sql .= COUNTRIES_TABLE;

		$sql .= " WHERE code='" . $_POST['txtsearch'] . "' ";

		$rs = $db->query ( $sql );

		$row = $rs->fetchRow();

		$searchatname = $row['name'];

		$srchby = get_lang('signup_country');

	} elseif ( $_POST['searchby'] == 'state_province' ) {

		$sql .= STATES_TABLE;

		$sql .= " WHERE code='" . $_POST['txtsearch'] . "'  ";

		$rs = $db->query ( $sql );

		$row = $rs->fetchRow();

		$searchatname = $row['name'];

		$srchby = get_lang('signup_state_province');

	}

	if ( $_POST['txtsearch'] == 'AA' ) {

		$sql = 'SELECT * FROM ' . USER_TABLE;

		$sql .= " WHERE status in ('active','".get_lang('status_enum','active')."') and " . $_POST['searchby'] . "<>'" . $_POST['txtsearch'] . "' AND id > 0 ";

	} else {

		$sql = 'SELECT * FROM ' . USER_TABLE;

		$sql .= " WHERE status in ('active','".get_lang('status_enum','active')."') and " . $_POST['searchby'] . "= '" . $_POST['txtsearch'] . "' AND id > 0 ";
	}

	//Place last query and related values in session with out sort string
	$_SESSION['LastQuery'] = $sql;

	$_SESSION['SearchBy'] = $srchby;

	$_SESSION['SearchAt'] = $searchatname;

	$sql .= $sortme;

	$rs = $db->query ( $sql );

	showPageB ( $rs, $srchby, $searchatname );

} elseif ( $_POST['searchtype'] == 'advance' ) {

	$sql = 'SELECT * FROM ' . USER_TABLE . " WHERE status in ('active','".get_lang('status_enum','active')."') AND id > 0 and ";

	if ( $_POST['txtcountry'] == 'AA' ) {

			$sql .= "country <> 'AA' ";

	} else {

			$sql .= "country ='" . $_POST['txtcountry'] . "' ";
	}

	if ( $_POST['txtcity'] ) {

		if ( $_POST['txtcity'] == '*' ) {

			$sql .= ' AND city IS NOT NULL ';

		}

		else {

			$sql .= " AND city='" . $_POST['txtcity'] . "' ";

		}
	}

	if ( $_POST['txtzip'] ) {

		if ( $_POST['txtzip'] == '*' ) {

			$sql .= ' AND zip IS NOT NULL ';

		}

		else {

			$sql .= " AND zip='" . $_POST['txtzip'] . "' ";

		}
	}


	$sql .= $sortme;

	$rs = $db->query ( $sql );

	showPageA ( $rs );

	exit;

} elseif ( $_REQUEST['sort_by'] ) {

	$sql = $_SESSION['LastQuery'];

	$sql .= $sortme;

	$rs = $db->query ( $sql );

	showPageB ( $rs, $_SESSION['SearchBy'], $_SESSION['SearchAt'] );

	exit;
}

function showPageA ( $rs ) {

	global $lang;
	global $db;
	global $t;

		//No Record found
	if ( $rs->numRows() == 0 ) {

		$t->assign ( 'error', '1' );

		$t->assign ( 'lang', $lang );

		$t->assign('rendered_page', $t->fetch('showapsearch.tpl') );

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

		$t->assign('rendered_page', $t->fetch('showapsearch.tpl') );

		$t->display ( 'index.tpl' );


	}
}


function showPageB ( $rs, $srchby, $srchatname ) {

	global $lang;
	global $db;
	global $t;

	//No Record found
	if ( $rs->numRows() == 0 ) {

		$t->assign ( 'error', "1" );

		$t->assign ( 'searchby', $srchby );

		$t->assign ( 'searchat', $srchatname );

		$t->assign ( 'lang', $lang );
//				$t->display ( 'showspsearch.tpl' );
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

		$t->assign ( 'searchby', $srchby );

		$t->assign ( 'searchat', $srchatname );

		$t->assign ( 'data', $data );

		$t->assign ( 'lang', $lang );

		$t->assign('rendered_page', $t->fetch('showsimpsh.tpl') );

		$t->display ( 'index.tpl' );
	}
}


?>