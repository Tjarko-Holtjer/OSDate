<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

if ($_SESSION['AdminId'] > 0) {

	header('location: admin/index.php');
	exit;
}


if ($_SESSION['UserId'] <= 0 && ($_GET['page'] == 'login' || !$_GET) &&  isset($_COOKIE[$config['cookie_prefix'].'osdate_info']) ) {

	$cookie = $_COOKIE[$config['cookie_prefix'].'osdate_info'];

	$_SESSION['txtusername'] = $cookie['username'];

	$_SESSION['txtpassword'] = $cookie['dir'] ;

	$_SESSION['rememberme'] = true;

	list($_SESSION['lookagestart'], $_SESSION['lookageend'])= split(':',$cookie['search_ages']);

	if ($cookie['username'] != "") {

		if ( !$_GET['errid'] ) {
			header("location: midlogin.php");
			exit;
		}
	}
}

if ( isset( $_GET['affid'] ) ) {

	$_SESSION['ReferalId'] = $_GET['affid'];

	if ( getenv( 'HTTP_CLIENT_IP' ) ){
		$userip = getenv( 'HTTP_CLIENT_IP' );
	}
	else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) )	{
		$userip = getenv( 'HTTP_X_FORWARDED_FOR' );
	}
	else {
		$userip = getenv( 'REMOTE_ADDR' );
	}

	$sql = "select count(*) FROM ! where ip = ? and ip <> '' and affid = ?";

	$count = $db->getOne( $sql, array( AFFILIATE_REFERALS_TABLE, $userip, $_SESSION['ReferalId'] ) );

	if ( $count == 0 ) {
		$sql = "INSERT INTO ! ( affid, userid, ip ) VALUES ( ?, '0', ? )";
		$db->query( $sql, array( AFFILIATE_REFERALS_TABLE, $_SESSION['ReferalId'], $userip ) );
	}

}

if ($_GET['page'] == 'login' and $_GET['errid'] != '') {

	$t->assign ( 'login_error', get_lang('errormsgs',$_GET['errid']) );
}

$_SESSION['lookagestart'] = ($_SESSION['lookagestart']!= '')?$_SESSION['lookagestart']:$config['default_start_agerange'];
$_SESSION['lookageend'] = ($_SESSION['lookageend']!= '')?$_SESSION['lookageend']:$config['default_end_agerange'];


if( isset( $_GET['page'] ) ) {

	$siteurl = HTTP_METHOD . $_SERVER['SERVER_NAME'] . DOC_ROOT ;

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

	$t->assign ( 'psize',  $psize );

	$pageno = (int)$_REQUEST['pageno'];

	if( $pageno == 0 ) $pageno = 1;

	$upr = ($pageno * $psize )- $psize;

	$cpage = $pageno;


	$data = array();

	if ( $_GET['page'] == 'stories' ) {

		$sql = 'SELECT * FROM ! order by `date` desc';
		$reccnt = $db->getOne('select count(*) from !', array(STORIES_TABLE) );
		$pages = ceil( $reccnt / $psize );
		if( $pages > 1 ) {
			$sql .= ' limit '.$upr.','.$psize;
			if ( $cpage > 1 ) {

				$prev = $cpage - 1;

				$t->assign( 'prev', $prev );

			}

			if ( $cpage < $pages ) {

				$next = $cpage + 1;

				$t->assign ( 'next', $next );

			}
		}

		$t->assign ( 'cpage', $cpage );

		$t->assign ( 'pages',  $pages );

		$t->assign ( 'reccount',  $reccount );

		$temp = $db->getAll( $sql, array( STORIES_TABLE ) );

		foreach( $temp as $index => $row ) {

			$sql = 'SELECT username FROM ! where id = ?';
			$row['username'] = $db->getOne( $sql, array( USER_TABLE, $row[sender] ) );
			$row['text'] = stripslashes($row['text']);
			$arrtext = explode( ' ', $row[text], $config['length_story'] + 1 );
			$arrtext[ $config['length_story'] ] = '';
			$row['text'] = trim( implode( ' ', $arrtext ) ) . '...';
			$row['date'] = date( get_lang('DISPLAY_DATE_FORMAT'), $row[date] );

			$data []= $row;
		}
		$t->assign( 'lang', $lang );
		$t->assign ( 'data', $data );
		$t->assign('rendered_page', $t->fetch('allstories.tpl') );

	} elseif ( $_GET['page'] == 'allnews' ) {

		$sql = 'SELECT * FROM ! order by `date` desc';
		$reccnt = $db->getOne('select count(*) from !', array(NEWS_TABLE) );
		$pages = ceil( $reccnt / $psize );
		if( $pages > 1 ) {
			$sql .= ' limit '.$upr.','.$psize;
			if ( $cpage > 1 ) {

				$prev = $cpage - 1;

				$t->assign( 'prev', $prev );

			}

			if ( $cpage < $pages ) {

				$next = $cpage + 1;

				$t->assign ( 'next', $next );

			}
		}

		$t->assign ( 'cpage', $cpage );

		$t->assign ( 'pages',  $pages );

		$t->assign ( 'reccount',  $reccount );


		$temp = $db->getAll( $sql, array( NEWS_TABLE ) );

		foreach( $temp as $index => $row ) {
			$row['date'] = date( get_lang('DISPLAY_DATE_FORMAT'), $row[date] );
			$arrtext = explode( ' ', stripslashes($row['text']), $config['length_story'] + 1);
			$arrtext[ $config['length_story'] ] = '';
			$row['text'] = trim(implode( ' ', $arrtext)) . '...';

			$data []= $row;
		}
		$t->assign( 'lang', $lang );

		$t->assign ( 'data', $data );
		$t->assign('rendered_page', $t->fetch('allnews.tpl') );

	} elseif ( $_GET['page'] == 'articles' ) {

		$sql = 'SELECT * FROM ! order by dat desc';
		$reccnt = $db->getOne('select count(*) from !', array(ARTICLES_TABLE) );
		$pages = ceil( $reccnt / $psize );
		if( $pages > 1 ) {
			$sql .= ' limit '.$upr.','.$psize;
			if ( $cpage > 1 ) {

				$prev = $cpage - 1;

				$t->assign( 'prev', $prev );

			}

			if ( $cpage < $pages ) {

				$next = $cpage + 1;

				$t->assign ( 'next', $next );

			}
		}

		$t->assign ( 'cpage', $cpage );

		$t->assign ( 'pages',  $pages );

		$t->assign ( 'reccount',  $reccount );


		$temp = $db->getAll( $sql, array( ARTICLES_TABLE ) );

		foreach( $temp as $index => $row ) {

			$row['dat'] = date( get_lang('DISPLAY_DATE_FORMAT'), $row['dat'] );
			$arrtext = explode( ' ', stripslashes($row['text']), $config['length_story'] + 1 );
			$arrtext[$config['length_story']] = '';
			$row['text'] = trim(implode( ' ', $arrtext)) . '...';

			$data []= $row;
		}
		$t->assign( 'lang', $lang );

		$t->assign ( 'data', $data );
		$t->assign('rendered_page', $t->fetch('allarticles.tpl') );

	} elseif ( $_GET['page'] == 'showstory' ) {

		$sql = 'SELECT * FROM ! where storyid = ?';
		$temp = $db->getAll( $sql, array( STORIES_TABLE, $_GET['storyid'] ) );

		foreach( $temp as $index => $row ) {

			$sql = 'SELECT username FROM ! where id = ?';
			$row['username'] = $db->getOne( $sql, array( USER_TABLE, $row[sender] ) );

			$row['date'] = date( get_lang('DISPLAY_DATE_FORMAT'), $row[date] );
			$row['text'] = stripslashes($row['text']);

			$data []= $row;
		}
		$t->assign( 'lang', $lang );

		$t->assign ( 'data', $data );
		$t->assign('rendered_page', $t->fetch('fullstory.tpl') );

	} elseif ( $_GET['page'] == 'shownews' ) {

		$sql = 'SELECT * FROM ! where newsid = ?';
		$temp = $db->getAll( $sql, array( NEWS_TABLE, $_GET['newsid'] ) );

		foreach( $temp as $index => $row ) {
			$row['date'] = date(get_lang('DISPLAY_DATE_FORMAT'), $row[date] );
			$row['text'] = stripslashes($row['text']);
			$data []= $row;
		}
		$t->assign( 'lang', $lang );

		$t->assign ( 'data', $data );
		$t->assign('rendered_page', $t->fetch('fullnews.tpl') );

	} elseif ( $_GET['page'] == 'showarticle' ) {

		$sql = 'SELECT * FROM ! where articleid = ?';
		$temp = $db->getAll( $sql, array( ARTICLES_TABLE, $_GET['articleid'] ) );

		foreach( $temp as $index => $row ) {
			$row['dat'] = date( get_lang('DISPLAY_DATE_FORMAT'), $row[dat] );
			$row['text'] = stripslashes($row['text']);
			$data []= $row;
		}
		$t->assign( 'lang', $lang );

		$t->assign ( 'data', $data );
		$t->assign('rendered_page', $t->fetch('fullarticle.tpl') );

	} elseif ( $_GET['page'] == 'login' ) {

		$t->assign('rendered_page', $t->fetch('login.tpl') );

	} elseif ( $_GET['page'] != '' ) {

		$sql = 'SELECT * FROM ! where pagekey = ?';
		$row = $db->getRow( $sql, array( PAGES_TABLE, $_GET['page'] ) );

		if ( $row ) {
			$row['pagetext'] = str_replace('[Your Company]', $config['site_title'],stripslashes(stripslashes($row['pagetext'])));
			$index++;
		}
		$row['pagetext'] = str_replace("#CONTACTUS#",$sireurl.'feedback.php',$row['pagetext']);

		$row['pagetext'] = str_replace("#CANCEL#",$sireurl.'cancel.php',$row['pagetext']);
		$t->assign( 'lang', $lang );

		$t->assign ( 'data', $row );
		$t->assign('rendered_page', $t->fetch('page.tpl') );

	}
}

$bannedlist = '';
if ($_SESSION['UserId'] != '') {
	$bannedusers = $db->getAll('select usr.id as ref_userid from ! as bdy, ! as usr where bdy.act=? and ((usr.username = bdy.username and bdy.ref_username = ?) or (usr.username = bdy.ref_username and bdy.username = ? ))', array(BUDDY_BAN_TABLE, USER_TABLE, 'B', $_SESSION['UserName'], $_SESSION['UserName']) );
	if (count($bannedusers) > 0) {
		$bannedlist=' and id not in (';
		$bdylst = '';
		foreach ($bannedusers as $busr) {
			if ($bdylst != '') $bdylst .= ',';
			$bdylst .= "'".$busr['ref_userid']."'";
		}
		$bannedlist .=$bdylst.') ';
	}

}

if ( strlen( $_SERVER['QUERY_STRING'] ) <= 0 or $_SERVER['QUERY_STRING'] == 'affid='.$_GET['affid'] or(( $_GET['errid'] == NOT_YET_APPROVED or $_GET['errid'] == NOT_ACTIVE ) && $_SESSION['UserId'] > 0 ) ){

	$last_users = $config['no_last_new_users'];

	$list_newmembers_since_days = $config['list_newmembers_since_days'];

	if ($list_newmembers_since_days == '') $list_newmembers_since_days=0;

	$list_newmembers_since = strtotime("-$list_newmembers_since_days day",time());

	if ($list_newmembers_since < $_SESSION['regdate']) $list_newmembers_since = $_SESSION['regdate'];

	/* Modify the newest profile condition to be from last visit time if user is logged in */

	if ( $last_users > 0 ) {

			$sqlNewUsers = "SELECT *, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age  FROM ! WHERE status in (?, ?)  and regdate >= ? ORDER BY regdate DESC LIMIT 0, $last_users";

			$newUsers = $db->getAll( $sqlNewUsers, array( USER_TABLE , get_lang('status_enum','active'), 'active', $list_newmembers_since) );

		$list = array();

		foreach ($newUsers as $row) {

			/* Get countryname and statename */
			$countryname = $db->getOne('select name from ! where code = ?', array(COUNTRIES_TABLE, $row['country'] ) );

			$statename = $db->getOne('select name from ! where code = ? and countrycode = ?', array(STATES_TABLE, $row['state_province'], $row['country'] ) );

			$row['countryname'] = $countryname;

			$row['statename'] = ($statename != '') ? $statename : $row['state_province'];

			$list[] = $row;

		}

	}

	$t->assign( 'users', $list );

	if ($config['list_newmembers'] > 0) {
		/* Get list of latest 10 userid */

			$sqlNew = "SELECT id, username, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age, (case gender when 'M' then 'Male' when 'F' then 'Female' else 'Couple' end ) as gender, allow_viewonline, city, country, state_province  FROM ! WHERE status in (?, ?)  and regdate >= ? ORDER BY regdate DESC LIMIT 0,".$config['list_newmembers'];

			$newUsersList = $db->getAll( $sqlNew, array( USER_TABLE, get_lang('status_enum','active'), 'active', $list_newmembers_since ));

		$list = array();

		foreach ($newUsersList as $row) {

			/* Get countryname and statename */
			$countryname = $db->getOne('select name from ! where code = ?', array(COUNTRIES_TABLE, $row['country'] ) );

			$statename = $db->getOne('select name from ! where code = ? and countrycode = ?', array(STATES_TABLE, $row['state_province'], $row['country'] ) );

			$row['countryname'] = $countryname;

			$row['statename'] = ($statename != '') ? $statename : $row['state_province'];

			$list[] = $row;

		}

		$t->assign('newUsersList',$list);

	}

	if ($config['show_featured_profiles'] > 0 ) {

		$xid = ($_SESSION['UserId'] > 0)?$_SESSION['UserId']:'0';

		$sql = 'select id, userid from ! where ? between start_date and end_date and req_exposures > exposures  and userid <> ? order by rand() limit 0, ! ';

		$list = $db->getAll($sql, array( FEATURED_PROFILES_TABLE, time(), $xid,  $config['show_featured_profiles'] ) );

		$featured_profiles = array();

		foreach ($list as $usr) {

			$sql = 'select *, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age from ! where id = ?';

			$row = $db->getRow($sql, array( USER_TABLE, $usr['userid'] ) );

			/* Get countryname and statename */
			$countryname = $db->getOne('select name from ! where code = ?', array(COUNTRIES_TABLE, $row['country'] ) );

			$statename = $db->getOne('select name from ! where code = ? and countrycode = ?', array(STATES_TABLE, $row['state_province'], $row['country'] ) );

			$row['countryname'] = $countryname;

			$row['statename'] = ($statename != '') ? $statename : $row['state_province'];

			$featured_profiles[] = $row;

			$sql = 'update ! set exposures = exposures + 1 where id = ?';

			$db->query($sql, array( FEATURED_PROFILES_TABLE, $usr['id'] ) );
		}

		$t->assign('featured_profiles', $featured_profiles);

	}

	if ($_SESSION['UserId'] > 0 ) {

		/* Get some stats */

		$viewswinks_since_days = ($config['last_viewswinks_since']=='')?0:$config['last_viewswinks_since'];

		$viewswinks_since = strtotime("-$viewswinks_since_days day",time());

		if ($viewswinks_since > $_SESSION['lastvisit']) $viewswinks_since = $_SESSION['lastvisit'];

		if ($viewswinks_since < $_SESSION['regdate']) $viewswinks_since=$_SESSION['regdate'];

		$sql = 'select count(*) from ! where userid = ? and act_time >= ? and act = ?';

		$t->assign('profile_views', $db->getOne($sql, array( VIEWS_WINKS_TABLE, $_SESSION['UserId'], $viewswinks_since, 'V' ) ) );

		$t->assign('winks', $db->getOne($sql, array( VIEWS_WINKS_TABLE, $_SESSION['UserId'], $viewswinks_since, 'W' ) ) );

		$sql = 'select count(*) from ! where owner=? and recipientid = ? and flagread = 0 and folder = ?';

		$t->assign('new_messages', $db->getOne($sql, array( MAILBOX_TABLE, $_SESSION['UserId'], $_SESSION['UserId'], 'inbox' ) ) );

		$usr = $db->getRow('select usr.levelend, mem.name from ! usr, ! mem  where usr.id = ? and mem.roleid = usr.level', array(USER_TABLE, MEMBERSHIP_TABLE, $_SESSION['UserId']) );

		$levelend = $usr['levelend'];

		$end_date = strftime($lang['DATE_FORMAT'],$levelend);

		$t->assign('curlevel', $usr['name']);

		$diff=$levelend - (time()+0);

		$bal_days = round($diff/86400,0);

		if ($bal_days == -0) $bal_days=0;

		$t->assign('bal_days', $bal_days );

		$t->assign('end_date', $end_date );

		$t->assign('viewswinks_since', strftime($lang['DATE_FORMAT'],$viewswinks_since));

	}

	$t->assign('rendered_page', $t->fetch('homepage.tpl') );
}
$lang['DATE_FORMAT'] = get_lang('DATE_FORMAT');

$t->assign('lang', $lang);


if ($_SESSION['UserId'] == '' || !isset($_SESSION['UserId'])) {
	/* Cache checking enabled only for general public i.e. the user is not logged in */
	$cached_data = $t->fetch( 'index.tpl' );

	require_once FULL_PATH.'includes/internal/osdate_save_cache.php';

	echo($cached_data);

} else {

	$t->display( 'index.tpl' );
}

exit();
?>
