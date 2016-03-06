<?php

session_start();
error_reporting( E_ERROR );

define ('FULL_PATH', dirname(__FILE__).'/');

if (!is_readable(FULL_PATH.'myconfigs/config.php') ) {
	if (is_readable('install.php')){
		header("location: install.php");
	} else {
		header("location: ../install.php");
	}
}

if (file_exists(FULL_PATH.'myconfigs/config.php')) {
	require_once( FULL_PATH.'myconfigs/config.php' );
} else {
	echo (FULL_PATH.'myconfigs/config.php is missing..<br />');
	exit;
}

if(!isset($_SERVER)) $_SERVER=$GLOBALS['_SERVER'];

include("osdate_init.php");

define('MAIL_FORMAT',strtolower(MAIL_FORMAT));

if ( !OSDATE_INSTALLED ) {
	die ( '<font face=Arial size=2>osDate is not installed, or a previous installation was not successfully completed.<br /><br />Please run <a href=install.php>install.php</a> to use osDate. You will need your database login parameters, and the ability to set the permissions on various files and folders on your server.</font>' );
}

if ( (isset($_SESSION['UserId']) && $_SESSION['UserId'] == '') || !isset($_SESSION['UserId']) || ($_SERVER['SCRIPT_NAME'] == DOC_ROOT.'showprofile.php' && $config['use_profilepopups'] == 'Y') ) {
	/* Cache checking enabled only for general public i.e. the user is not logged in */
	require_once FULL_PATH.'includes/internal/osdate_check_cache.php';
	/* Check for page caching now */
	/* if cached page was available, it would have closed the session there. */
}

require_once SMARTY_DIR . 'Smarty.class.php';
require_once FULL_PATH. 'libs/Smarty/osDate_Smarty.class.php';
require_once PEAR_DIR . 'Mail.php';
require_once PEAR_DIR . 'cachedDB.php';
require_once FULL_PATH.'includes/internal/Functions.php' ;
require_once PEAR_DIR . 'Compat.php';

PHP_Compat::loadFunction('file_get_contents');


$lang = array();

$_SESSION['browser'] = getUserBrowser();

$t = new osDate_Smarty;


/************************/
// SECURITY CHECK
/************************/

if ( $_SERVER['HTTP_HOST'] != 'localhost' && ( file_exists( FULL_PATH.'install.php' ) || is_dir( FULL_PATH.'install_files' ) ) ) {

	echo '
	<br /><br /><br /><center>
	<table border=0 width=500 cellpadding=2 cellspacing=0>
		<tr>
			<td align=center>
				<font color=red face=Arial size=2><B>SECURITY ALERT<br /><br />Please remove the following from your server before continuing: install.php file, and the install_files folder. Then, click "Reload osDate" below to continue.<br /><br />

				<a href=index.php>Reload osDate</a></B></font>
			</td>
		</tr>
	</table></center>';

	exit;
}


/**********************************/
// PEAR SETUP
/**********************************/
if ( !isset( $db ) ) {

	$db_options = array(
		'persistent' => TRUE );

	if (DB_TYPE == 'mysql') {

			$dsn = 'mysqlc' . '://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME;

	} else {

		$dsn = DB_TYPE . '://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME;

	}

	$db = @cachedDB::connect( $dsn, $db_options );

	function errhndl ( $err ) {
/*		echo '<pre>' . $err->message;
		print_r( $err ); */
		echo ('<pre>' . 'Database error occured. Check the query and/or DB connection');
		die();
	}

	PEAR::setErrorHandling( PEAR_ERROR_CALLBACK, 'errhndl' );

	if (PEAR::isError($db)) {
	    die($db->getMessage());
	}

	$db->setFetchMode( DB_FETCHMODE_ASSOC );

/*	Set these two parameters in your mysql installation to improve performance

	$db->query('set global query_cache_size = 8388608');
	$db->query('set global query_cache_type = 1');

*/

}

/*
if ( !get_magic_quotes_gpc() ) {
   function addslashes_deep($value) {
       return is_array($value) ? array_map('addslashes_deep', $value) : addslashes($value);
   }

   $_POST 	= array_map('addslashes_deep', $_POST);
   $_GET 	= array_map('addslashes_deep', $_GET);
   $_COOKIE	= array_map('addslashes_deep', $_COOKIE);
}
*/

$params = array();// for mail sending with Pear's Mail class

if ( MAIL_TYPE == 'smtp' ) {
	$params['host'] = SMTP_HOST;
	$params['port'] = SMTP_PORT;
	$params['auth'] = (int)SMTP_AUTH;
	$params['username'] = SMTP_USER;
	$params['password'] = SMTP_PASS;
}

/**********************************/
// STARTUP CONFIGURATION DATA
/**********************************/

$configs = $db->getAll( 'SELECT * from !',array( CONFIG_TABLE ) );
$config = array();

foreach( $configs as $index => $row ) {
	$config[ $row['config_variable'] ] = $row[config_value];
}


$config['use_popups'] = 'Y';

define('DEFAULT_COUNTRY', $config['default_country']);

$t->assign ( 'config', $config );

if (isset( $_COOKIE[$config['cookie_prefix'].'osdate_info'] ) ) {

	$cookie = $_COOKIE[$config['cookie_prefix'].'osdate_info'];

	list($_SESSION['lookagestart'], $_SESSION['lookageend'])= split(':',$cookie['search_ages']);
}

$skin_name = $config['skin_name'];
$lang['site_name'] = $config['site_name'];
define ('SITENAME', $config['site_name']);

if ($_REQUEST['lang']!= '') {$opt_lang=$_REQUEST['lang'];}
elseif ($_SESSION['opt_lang'] != '') {$opt_lang=str_replace("'",'',$_SESSION['opt_lang']);}
elseif ($_COOKIE[$config['cookie_prefix'].'opt_lang'] != '') {$opt_lang=$_COOKIE[$config['cookie_prefix'].'opt_lang'];}
else {$opt_lang=DEFAULT_LANG; }

// hack - fix later
if ( strlen( $opt_lang ) <= 3 ) {
	$opt_lang = DEFAULT_LANG;
}

// $langfile = LANG_DIR.$language_files[$opt_lang];
if ($_SERVER['HTTPS'] == 'on') {
	$HTTP = 'https://';
} else {
	$HTTP = 'http://';
}
define ('HTTP_METHOD', $HTTP);

$_SESSION['spam_code_length'] = $config['spam_code_length'];

$_SESSION['opt_lang'] = ($opt_lang=='')?'english':$opt_lang;
setcookie($config['cookie_prefix'].'opt_lang',$opt_lang,time()+60*60*24*365);

if (isset($_SESSION['profile_questions']) ) unset($_SESSION['profile_questions']);

//if ($opt_lang != 'english') {
include('language/lang_'.$opt_lang.'/profile_questions.php');
$_SESSION['profile_questions'] = $profile_questions;
//}


$t->template_dir = TEMPLATE_DIR . $skin_name.'/';
$t->compile_dir = TEMPLATE_C_DIR;
$t->cache_dir = CACHE_DIR;
// set the default handler and other values for caching and faster loading
$t->default_template_handler_func = 'getTplFile';
$t->caching = false;
$t->force_compile = false;
$t->register_prefilter("prefilter_getlang");
$t->compile_id=$_SESSION['opt_lang'];
$t->assign('DOC_ROOT', DOC_ROOT);

define('SKIN_IMAGES_DIR', TEMPLATE_DIR.$skin_name.'/images/');

$agecounter = array();

for($i=($config['end_year']*-1); $i<=($config['start_year']*-1); $i++ ) {
	$agecounter[] = $i;
}
$lang['start_agerange'] = $agecounter;
$lang['end_agerange'] = $agecounter;

// require_once LANG_DIR.$language_files['english'];


$langs_loaded = $db->getAll('select distinct lang from !',array(LANGUAGE_TABLE) );

$loaded_languages = array();

foreach ($langs_loaded as $lng) {
	$loaded_languages[$lng['lang']] = $language_options[$lng['lang']];
}


//require_once $langfile;

$lang['status_enum'] = get_lang_values('status_enum');

$lang['status_disp'] = get_lang_values('status_disp');

$lang['status_act'] = get_lang_values('status_act');

$lang['error_msg_color'] = 'red';

$lang['useronlinetext'] = get_lang_values('useronlinetext');

$lang['useronlinecolor'] = get_lang_values('useronlinecolor');

$lang['tz'] = get_lang_values('tz');

$t->assign('languages_options', $languages_options);

$t->assign('loaded_languages', $loaded_languages);

$lang['ENCODING'] = get_lang('ENCODING');

$lang['DIRECTION'] = get_lang('DIRECTION');

$lang['DATE_FORMAT'] = get_lang('DATE_FORMAT');

$lang['search_results_per_page'] = get_lang_values('search_results_per_page');

$lang['enabled_values'] = get_lang_values('enabled_values');

$lang['forum_values'] = get_lang_values('forum_values');

$lang['support_currency'] = get_lang_values('support_currency');

$lang['signup_gender_values'] = get_lang_values('signup_gender_values');

$lang['signup_gender_look'] = get_lang_values('signup_gender_look');

$_SESSION['datetime_month'] = get_lang('datetime_month');

$_SESSION['datetime_day'] = get_lang('datetime_day');

/* MOD START */

$lang['mod_lowtohigh'] = get_lang_values('mod_lowtohigh');

if (strtoupper($lang['DIRECTION']) == 'RTL') {
	$t->assign('imgrtl','RTL');
}

$t->assign('lang', $lang);
/* MOD END */


/**********************************/
// GET CALENDARS
/**********************************/
if ($_SESSION['UserId'] != '' || substr_count($_SERVER['SCRIPT_NAME'],'calendar') > 0 ) {

	$sql = 'select id, calendar from ! where enabled = ? order by displayorder asc';
	$temp = $db->getAll( $sql, array( CALENDARS_TABLE, 'Y' ) );
	foreach( $temp as $index => $row ) {
		$calendars[ $row[id] ] = $row[calendar];
	}
	$t->assign( 'calendars', $calendars );

	// fix later....
	$sql = 'select id, displayorder, calendar from !';
	$temp = $db->getAll( $sql, array( CALENDARS_TABLE ) );
	foreach( $temp as $index => $row ) {
		$calendars[ $row[id] ] = $row[calendar];
	}
	$t->assign( 'allcalendars', $calendars );
}


/**********************************/
// GET REGISTRATION SECTIONS
/**********************************/

if ($_SESSION['UserId'] != '' || $_SESSION['AdminId'] != '' ) {

	$sql = 'select id, section from ! where enabled = ?  order by displayorder asc';

	$temp = $db->getAll( $sql, array( SECTIONS_TABLE, 'Y' ) );

	$sections = array();

	foreach( $temp as $index => $row ) {
		if ($lang['sections'][$row[id] ] != '') {
			$sections[ $row[id] ] = $lang['sections'][$row[id] ];
		} else {
			$sections[ $row[id] ] = get_lang('sections', $row[id]);
		}
	}

	$t->assign( 'sections', $sections );

	// fix later....
	$sql = 'select id, displayorder, section from !';
	$temp = $db->getAll( $sql, array( SECTIONS_TABLE ) );

	foreach( $temp as $index => $row ) {
		if ($lang['sections'][$row[id] ] != '') {
			$sections[ $row[id] ] = $lang['sections'][$row[id] ];
		} else {
			$sections[ $row[id] ] = get_lang('sections', $row[id]);
		}
	}

	$t->assign( 'allsections', $sections );
}


/***********************************************/
// COUNTRIES & STATES - MOVE LATER & COLSOLIDATE
/***********************************************/

/*
if ($_SESSION['UserId'] != ''  || strtolower($_SERVER['SCRIPT_NAME']) == strtolower(DOC_ROOT.'signup.php') || substr_count($_SERVER['SCRIPT_NAME'], 'advsearch.php' ) > 0 || $_SESSION['AdminId'] != '' || strtolower($_SERVER['SCRIPT_NAME']) == strtolower(DOC_ROOT.'feedback.php') || strtolower($_SERVER['SCRIPT_NAME']) == strtolower(DOC_ROOT.'newmemberslist.php')) {

Modified as below.....

if ($_SESSION['UserId'] != ''  || substr_count(strtolower($_SERVER['SCRIPT_NAME']),'signup.php') > 0 || substr_count(strtolower($_SERVER['SCRIPT_NAME']), 'advsearch.php' ) > 0 || $_SESSION['AdminId'] != '' || substr_count(strtolower($_SERVER['SCRIPT_NAME']),'feedback.php') > 0 || substr_count(strtolower($_SERVER['SCRIPT_NAME']),'newmemberslist.php') > 0 ) {

	include_once('countries_list.php');

	$lang['countries'] = $countries;

	$lang['allcountries'] = $allcountries;
}

*/
include_once('countries_list.php');

$lang['countries'] = $countries;

$lang['allcountries'] = $allcountries;


/**********************************/
// GET ONLINE USERS
/**********************************/

$sql = 'SELECT count(ou.userid) as onlineusers FROM ! ou, ! as user where ou.userid <> ifnull(?,-1) and ou.userid = user.id and user.allow_viewonline = ? ';
$usersOnline = $db->getOne( $sql, array( ONLINE_USERS_TABLE, USER_TABLE, $_SESSION['UserId'], '1' ) );
$t->assign( 'online_users_count', $usersOnline );

$t->assign( 'docroot', DOC_ROOT );
$t->assign( 'banner_dir', DOC_ROOT.'banners/' );
// $t->assign( 'zodiac_dir', DOC_ROOT.'templates/'.$skin_name. '/zodiacsigns/' );
$t->assign( 'image_dir', DOC_ROOT.'templates/'.$skin_name.'/images/' );
$t->assign( 'css_path', DOC_ROOT.'templates/'.$skin_name.'/' );

include_once( 'polls.php' );
include_once( 'stories.php' );
include_once( 'news.php' );

include_once(LIB_DIR.'blog_class.php');
$blog = new Blog();
$t->assign( 'adminblog', $blog->getAllAdminStories('Y'));
$t->assign('userblog', $blog->getAllUserStories('Y'));


$time = time();

/**********************************/
// BANNERS
/**********************************/

$banner = array();

$index = 0;

$sql1 = 'SELECT id FROM ! WHERE ( startdate <= ? AND  expdate >= ? ) AND enabled = ? and ( language is null or language = ?)';

$temp = $db->getAll( $sql1, array( BANNER_TABLE, $time, $time, 'Y', $opt_lang ) );


if ( sizeof( $temp ) > 0 ) {

	$j = 1;

	foreach( $temp as $index => $row ) {
		$banner[$j++] = $row[id];
	}

	$my_banner = $banner[ rand( 1, --$j ) ];

	$sql2 = 'SELECT bannerurl FROM ! WHERE id = ?';

	$bannerURL = $db->getOne( $sql2, array( BANNER_TABLE, $my_banner ) );

	$t->assign( 'banner', stripslashes( $bannerURL ) );
}


$lang['recuring_labels'] = get_lang_values('recuring_labels');

$t->assign( 'lang', $lang );


/**********************************/
// UPDATE ONLINE STATUS and COLLECT USER STATS
/**********************************/

if (!$_SESSION['online_deleted']) {
	$curr_session_id = session_id();

	$lastactivitytime = time() - ($config['session_timeout'] * 60);

	$sql = 'SELECT * FROM ! where lastactivitytime < ?';

	$temp = $db->getAll( $sql, array( ONLINE_USERS_TABLE, $lastactivitytime ) );

	if ( sizeof( $temp ) > 0 ) {
		session_write_close();
		foreach( $temp as $index => $row ) {

			if ($row['session_id'] != '') {
				/* First destroy session */
				session_id($row['session_id']);
				session_start();
				session_destroy();
			}
			$db->query( 'DELETE FROM ! WHERE userid = ?', array( ONLINE_USERS_TABLE, $row['userid'] ) );
		}

		session_id($curr_session_id);
		session_start();
	}
	$_SESSION['onine_deleted'] = '1';
}

if ( isset( $_SESSION['UserId'] ) && !isset($_SESSION['AdminId']) ) {

	if ($_SESSION['UserId'] > 0) {
		$sql = 'SELECT count(*) FROM ! WHERE userid = ?';

		$isOnline = $db->getOne( $sql, array( ONLINE_USERS_TABLE, $_SESSION['UserId'] ) );

		if( $isOnline > 0 ) {
			$sql = 'UPDATE ! SET lastactivitytime= ?, session_id = ? WHERE userid = ?';

			$db->query( $sql, array( ONLINE_USERS_TABLE, $time, $curr_session_id, $_SESSION['UserId'] ) );
		}
		else {
			$sql = 'INSERT INTO ! ( userid, lastactivitytime, session_id ) values (?, ?, ? )';

			$db->query( $sql, array( ONLINE_USERS_TABLE, $_SESSION['UserId'], $time, $curr_session_id ) );
		}

		$sql = 'select count(*) from ! where recipientid = ? and flagread = ? and folder = ?';

		$t->assign('new_messages', $db->getOne($sql, array(MAILBOX_TABLE, $_SESSION['UserId'], '0', 'inbox') ) );
	}
}

/* Now delete cache files */
deleteCache();

/***********************************/
/* Collect site statistics         */
/***********************************/

$sql = 'select count(*) from ! where active = ? and regdate > ? and status in ( ?, ?) ';

$weekcnt = $db->getOne( $sql, array( USER_TABLE, '1', strtotime("-7 day"), 'active', get_lang('status_enum','active') ) );

$t->assign( 'weekcnt', $weekcnt );

$sql = 'select sum(if(gender=\'M\',1,0)) as gents, sum(if(gender=\'F\',1,0)) as females, sum(if(gender=\'C\',1,0)) as couples from ! where active = ? and status in (?, ?)';

$row = $db->getRow( $sql, array( USER_TABLE, '1', 'active', get_lang('status_enum','active')  ) );

$t->assign( 'gents', $row['gents'] );

$t->assign( 'females', $row['females'] );

$t->assign( 'couples', $row['couples'] );

$sql = 'select count(*) from ! where ins_time > ? ';

$weeksnaps = $db->getOne( $sql, array( USER_SNAP_TABLE, strtotime("-7 day") ) );

$t->assign( 'weeksnaps', $weeksnaps );

/**********************************/
// TOGGLE CHECK ONLINE STATUS
/**********************************/

if ( !isset( $_SESSION['LastExecTime'] ) ) {
	$_SESSION['LastExecTime'] = time();
}

if ( time() - $_SESSION['LastExecTime'] > 300 ) {

	$_SESSION['LastExecTime'] = time();
}

/**********************************/
// INCLUDE THE FORUM FUNCTIONS
/**********************************/

include_once( FORUM_DIR . 'forum_inc.php');


//Log code by Nathan Adams
$page = $_SERVER['REQUEST_URI'];
$IS_IN_ADMIN = strpos($page, 'admin');
$IS_NOT_SCRIPT = strpos($page, '?');
if ($IS_NOT_SCRIPT === FALSE){
	if ($IS_IN_ADMIN === FALSE){
		$pos = strrpos($page, '/');
		$page_script = substr($page, $pos+1);

		$pos0 = strpos($page_script,'.');

		$sql_page = substr($page_script,0,$pos0+1);

		if ($sql_page != 'getuser' && $sql_page != 'getinstantmsg'){
			if ($sql_page == ''){
				$sql_page = 'index';
			}
			$check_tablesql = 'SELECT * FROM ! WHERE page = ?';
			$check_table = $db->getRow ( $check_tablesql, array ( LOG_TABLE, $sql_page ) );
			$count_array = count($check_table);
			if ($count_array > 0){ //ok it exists
				$update_log = 'UPDATE ! SET visits = visits + 1 WHERE page = ?';
				$query = $db->Query ( $update_log, array ( LOG_TABLE, $sql_page ) );
			} else {
				$create_row = "INSERT INTO ! (page, visits) VALUES (?, '1')";
				$db->Query ( $create_row, array ( LOG_TABLE, $sql_page ) );
			}
		}
	}
}


/**********************************/
// Initialize Mod Osdate
/**********************************/

require_once MODOSDATE_DIR . 'modOsDate.php';

$mod = new modOsDate();
// Build the mod osdate content into predefined Smarty variables

$mod->modSetContent();


function querystring( $arr ) {

	$str = '';

	foreach( $arr as $item ) {

		if( !is_array( $_GET[$item]) ){
			$str .= $item . '=' . urlencode($_GET[$item]) . '&';
		} elseif (is_array( $_GET[$item]) ) {
			foreach( $_GET[$item] as $subitem) {
				$str .= $item . urlencode('[]') . '=' . urlencode($subitem) . '&';
			}
		} elseif( !is_array( $_POST[$item]) ){
			$str .= $item . '=' . urlencode($_POST[$item]) . '&';
		} elseif (is_array( $_POST[$item]) ) {
			foreach( $_POST[$item] as $subitem) {
				$str .= $item . urlencode('[]') . '=' . urlencode($subitem) . '&';
			}
		}
	}

	return $str;
}


function checkOnlineStats( $userid  ) {
	global $db;

	$sql = 'SELECT count(*) as num FROM ! WHERE userid = ?';

	if ( $db->getOne( $sql, array( ONLINE_USERS_TABLE, $userid ) ) ) {
		return 'online';
	}
	else {
		return 'offline';
	}
}


function getLastId() {
	global $db;
	return $db->getOne( 'select LAST_INSERT_ID()' );
}

function hasRight($field){
	global $db, $config;

	if( $_SESSION['security'] == '' ){
		if ($_SESSION['UserId'] == '') {

			$sqlsecurity = 'SELECT * FROM ! where name = ?';

			$row = $db->getRow( $sqlsecurity, array( MEMBERSHIP_TABLE, 'Visitor' ) );

		} elseif( $_SESSION['UserId'] != ''  ){

			// fix later

			$sqlsecurity = 'SELECT * FROM ! where roleid = ?';

			$row = $db->getRow( $sqlsecurity, array( MEMBERSHIP_TABLE, $_SESSION['RoleId'] ) );

		} else {

			$sqlsecurity = 'SELECT * FROM ! WHERE  roleid = ?';

			$row = $db->getRow( $sqlsecurity, array( MEMBERSHIP_TABLE, $config['default_user_level'] ) );

		}

		if( $row ) {
			$_SESSION['security'] = $row;
		}
	}

	return (int)$_SESSION['security'][$field];
}

function checkAdminPermission( $str ) {
	$permit = $_SESSION['Permissions'];
	return $permit[$str] ? 1 : 0;
}

/* Ascertain the sort type */

function checkSortType( $sort_type ) {
	$n_sort_type = '';

	if ( $sort_type == '' ) {

		$n_sort_type = 'asc';

	} elseif ( $sort_type == 'asc' ) {

		$n_sort_type = 'desc' ;

	} elseif( $sort_type == 'desc' ) {

		$n_sort_type = 'asc' ;

	}
	return $n_sort_type;
}

function get_lang ($mainkey, $subkey='') {
	global $db, $config;
	if ($subkey != '') {
	   $y = $db->getOne('select descr from ! where lang=? and mainkey= ? and subkey=?', array(LANGUAGE_TABLE, $_SESSION['opt_lang'], $mainkey, $subkey));
	} else {
	   $y = $db->getOne('select descr from ! where lang=? and mainkey= ? ', array(LANGUAGE_TABLE, $_SESSION['opt_lang'], $mainkey));
	}
	if (!$y) {
		if ($subkey != '') {
		   $y = $db->getOne('select descr from ! where lang=? and mainkey= ? and subkey=?', array(LANGUAGE_TABLE, 'english', $mainkey, $subkey));
		} else {
		   $y = $db->getOne('select descr from ! where lang=? and mainkey= ? ', array(LANGUAGE_TABLE, 'english', $mainkey));
		}
	}

	$y = str_replace('SITENAME', $config['site_name'], $y);
	$y = str_replace('DATE_FORMAT',DATE_FORMAT,$y);
	$y = str_replace('DATE_TIME_FORMAT',DATE_TIME_FORMAT,$y);
	$y = str_replace('DISPLAY_DATE_FORMAT',DISPLAY_DATE_FORMAT,$y);

	return html_entity_decode($y);
}

function prefilter_getlang($source, &$smarty_obj) {
	if (!function_exists('get_my_lang')) {
		function get_my_lang($m){
			$keys=explode(' ',$m[1]);
			list($x,$mkey) = split('=',$keys[0]);
			$skey='';
			if (isset($keys['1']) ){
				list($x1, $skey) = split('=',$keys[1]);
			}
			$mkey=str_replace("'","",$mkey);
			$mkey=str_replace('"','',$mkey);
			$skey=str_replace("'","",$skey);
			$skey=str_replace('"','',$skey);
			return stripslashes(get_lang($mkey,$skey));
		}
	}

	return preg_replace_callback('/{lang (.+?)}/s', 'get_my_lang', $source);
}

function get_lang_values ($mainkey) {
	global $db;
    $y = $db->getAll('select subkey, descr from ! where lang=? and mainkey= ? order by id', array(LANGUAGE_TABLE, 'english', $mainkey));
	$x=array();
	foreach ($y as $ky => $vl) {
		$x[$vl['subkey']] = $vl['descr'];
	}
	$y = $db->getAll('select subkey, descr from ! where lang=? and mainkey= ? order by id', array(LANGUAGE_TABLE, $_SESSION['opt_lang'], $mainkey));
	foreach ($y as $ky => $vl) {
		$x[$vl['subkey']] = $vl['descr'];
	}
	return $x;
}

function makeOptions ( $options ) {

	$result = array();

	foreach( $options as $index => $row ) {

		$result[ $row[id] ] = $row[answer];
	}
	return $result;
}

function makeAnswers ( $options ) {

	$result = array();

	foreach( $options as $index => $row ) {

		$result []= $row[answer];
	}
	return $result;
}

function findSortBy ( $def = 'id' ) {

	global $lang, $_REQUEST;

	if( $_REQUEST['sort'] == '' ) {

		return( $def. ' '. 'asc');

	} else if( $_REQUEST['sort'] == get_lang('col_head_id') ) {

		$sort_by = $def;

	} else if( $_REQUEST['sort'] == get_lang('col_head_username') ) {

		$sort_by = 'username';

	} else if( $_REQUEST['sort'] == get_lang('col_head_name') ) {

		$sort_by = 'name';

	} else if( $_REQUEST['sort'] == get_lang('col_head_firstname') ) {

		$sort_by = 'firstname';

	} else if( $_REQUEST['sort'] == get_lang('col_head_register_at') ) {

		$sort_by = 'regdate';

	} 	else if ( $_REQUEST['sort'] == get_lang('col_head_gender') ) {

		$sort_by = 'gender';

	} else if ( $_REQUEST['sort'] == get_lang('col_head_email') ) {

		$sort_by = 'email';

	} elseif ( $_REQUEST['sort'] == get_lang('col_head_subject') ) {

		$sort_by = 'subject';

	} elseif ( $_REQUEST['sort'] == get_lang('col_head_sendtime') ) {

		$sort_by = 'sendtime';

	} elseif ( $_REQUEST['sort'] == 'picscnt' ) {

		return( 'pictures_cnt '.checkSortType ( $_REQUEST['type'] ) .', firstname '.checkSortType ( $_REQUEST['type'] ) . ', lastname '.checkSortType ( $_REQUEST['type'] ) );


	} elseif ( $_REQUEST['sort'] == 'vdscnt' ) {

		return( 'videos_cnt '.checkSortType ( $_REQUEST['type'] ) .', firstname '.checkSortType ( $_REQUEST['type'] ) . ', lastname '.checkSortType ( $_REQUEST['type'] ) );

	}else if( $_REQUEST['sort'] == get_lang('total_referrals') ) {

		$sort_by = 'totalref';

	}else if ( $_REQUEST['sort'] == get_lang('regis_referals') ) {

		$sort_by = 'regref';

	} else if ( $_REQUEST['sort'] == get_lang('col_head_status') ) {

		$sort_by = 'status';

	} else if ( $_REQUEST['sort'] == get_lang('level_hdr') ) {

		$sort_by = 'level';

	} else if ( $_REQUEST['sort'] == get_lang('date_from') or $_REQUEST['sort'] == get_lang('start_date') or $_REQUEST['sort'] == 'start_date') {

		$sort_by = 'start_date';

	} else if ( $_REQUEST['sort'] == get_lang('date_upto') or $_REQUEST['sort'] == get_lang('end_date') or $_REQUEST['sort'] == 'end_date') {

		$sort_by = 'end_date';
	} else if ( $_REQUEST['sort'] == 'adminname' ) {

		$sort_by = 'fullname ';

	} else if( $_REQUEST['sort'] == get_lang('col_head_fullname') or $_REQUEST['sort'] == 'first_name') {

		return( 'firstname '.checkSortType ( $_REQUEST['type'] ) . ', lastname '.checkSortType ( $_REQUEST['type'] ) );

	} else if( $_REQUEST['sort'] == get_lang('superuser') or $_REQUEST['sort'] == 'superuser') {

		$sort_by = 'super_user';

	} else if( $_REQUEST['sort'] == get_lang('col_head_enabled') ) {

		$sort_by = 'enabled';

	}  else if( $_REQUEST['sort'] == get_lang('article_title') ) {

		$sort_by = 'title';

	} else if( $_REQUEST['sort'] == get_lang('news_header') ) {

		$sort_by = 'header';

	} else if( $_REQUEST['sort'] == get_lang('poll') ) {

		$sort_by = 'question';

	} else if( $_REQUEST['sort'] == get_lang('active') ) {

		$sort_by = 'active';

	} else if( $_REQUEST['sort'] == get_lang('news_header') ) {

		$sort_by = 'header';

	} else if( $_REQUEST['sort'] == get_lang('story_sender') ) {

		$sort_by = 'sender';

	} else if( $_REQUEST['sort'] == get_lang('option') ) {

		$sort_by = 'opt';

	} else if( $_REQUEST['sort'] == get_lang('votes') ) {

		$sort_by = 'result';

	} else if( $_REQUEST['sort'] == 'expire_date' ) {

		$sort_by = 'levelend';

	} else if( $_REQUEST['sort'] == get_lang('col_head_answer') ) {

		$sort_by = 'answer';

	} else if( $_REQUEST['sort'] == get_lang('col_head_question') ) {

		$sort_by = 'question';

	} else if ( $_REQUEST['sort'] == get_lang('state_code') || $_REQUEST['sort'] == get_lang('country_code')  || $_REQUEST['sort'] == get_lang('county_code')|| $_REQUEST['sort'] == get_lang('city_code') || $_REQUEST['sort'] == get_lang('zip_code')  ) {

		$sort_by = 'code';

	} else if ( $_REQUEST['sort'] == get_lang('state_name') || $_REQUEST['sort'] == get_lang('country_name') || $_REQUEST['sort'] == get_lang('county_name') || $_REQUEST['sort'] == get_lang('city_name') ) {

		$sort_by = 'name';

	}

	return ($sort_by . ' ' . checkSortType ( $_REQUEST['type'] ));
}

function make_datetime_from_smarty($prefix)
{	global $_REQUEST;
	$date=$_REQUEST[$prefix."Year"]."-".$_REQUEST[$prefix."Month"]."-".$_REQUEST[$prefix."Day"];
	$time=$_REQUEST[$prefix."Hour"];
	if(isset($_REQUEST[$prefix."Minute"])) $time.=":".$_REQUEST[$prefix."Minute"];
	if(isset($_REQUEST[$prefix."Second"])) $time.=":".$_REQUEST[$prefix."Second"];
	return($date." ".$time);
}

function send_watched_mails($eventid)
{	global $config;
	global $db;
	global $lang;
	global $t;
	global $params;

	$sql="select u.* ".
		 "from ! as u inner join ! as we on u.id=we.userid ".
		 "where we.eventid=? ";
	$users=$db->getAll($sql,array(USER_TABLE,WATCHES_TABLE,$eventid));

	if($users)
	foreach($users as $key=>$user)
	{	$recipients = $user["email"];

		$query="select id, userid, event, description, ".
			   "       date_add(datetime_from, interval ! hour) as datetime_from, ".
			   "       date_add(datetime_to, interval ! hour) as datetime_to, ".
			   "       calendarid, timezone, private_to ".
			   "from ! ".
			   "where id=? ";
		$event=$db->getRow($query,array($user["timezone"], $user["timezone"], EVENTS_TABLE,$eventid));

		$From    = $config['admin_email'];
		$To     = $user["email"];
		$Subject = get_lang('event_notification');

		$t->assign("user",$user);
		$t->assign("event",$event);
		$body = $t->fetch('eventnotificationmail.tpl');

		mailSender($From, $To, $user['email'], $Subject, $body);

	}
}

function getdate_safe($timestamp)
{	$date=array();
	$date["seconds"]=date("s",$timestamp);
	$date["minutes"]=date("i",$timestamp);
	$date["hours"]=date("H",$timestamp);
	$date["mday"]=date("j",$timestamp);
	$date["wday"]=date("w",$timestamp);
	$date["mon"]=date("m",$timestamp);
	$date["year"]=date("Y",$timestamp);
	$date["yday"]=date("z",$timestamp);
	$date["weekday"]=date("D",$timestamp);
	$date["month"]=date("F",$timestamp);
	return($date);
}

function getOnlineStats($userid) {
	global $db;
	$sql = 'SELECT count(*) FROM ! WHERE userid = ?';
	$onl = $db->getOne($sql, array(ONLINE_USERS_TABLE, $userid));
	if ($onl > 0) {return true;}
	return false;
}
?>
