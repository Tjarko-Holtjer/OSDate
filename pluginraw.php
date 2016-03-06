<?php

require_once( dirname(__FILE__).'/init.php' );

/*
if ( ! OSDATE_INSTALLED ) {
	die ( '<font face=Arial size=2>osDate is not installed, or a previous installation was not successfully completed.<br /><br />Please run <a href=install.php>install.php</a> to use osDate. You will need your database login parameters, and the ability to set the permissions on various files and folders on your server.</font>' );
}
*/
/*
require_once SMARTY_DIR . 'Smarty.class.php';
require_once dirname(__FILE__). '/libs/Smarty/osDate_Smarty.class.php';
require_once PEAR_DIR . 'cachedDB.php';
require_once PEAR_DIR . 'Mail.php';
require_once dirname(__FILE__).'/includes/internal/Functions.php' ;

$t = new osDate_Smarty;
*/
/************************/
// SECURITY CHECK
/************************/
/*
if ( $_SERVER['HTTP_HOST'] != 'localhost' && ( file_exists( FULL_PATH.'/install.php' ) || is_dir( FULL_PATH.'/install_files' ) ) ) {

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
*/
/**********************************/
// PEAR SETUP
/**********************************/
/*if ( !isset( $db ) ) {
	if (DB_TYPE == 'mysql') {
		$dsn = 'mysqlc' . '://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME;
	} else {
		$dsn = DB_TYPE . '://' . DB_USER . ':' . DB_PASS . '@' . DB_HOST . '/' . DB_NAME;
	}
	$db_options = array(
		'persistent' => TRUE );

	$db = @cachedDB::connect( $dsn, $db_options );


	function errhndl ( $err ) {
		echo '<pre>' . $err->message;
		print_r( $err );
		die();
	}

	PEAR::setErrorHandling( PEAR_ERROR_CALLBACK, 'errhndl' );
}

$params = array();// for mail sending with Pear's Mail class

if ( MAIL_TYPE == 'smtp' ) {
	$params['host'] = SMTP_HOST;
	$params['port'] = SMTP_PORT;
	$params['auth'] = (int)SMTP_AUTH;
	$params['username'] = SMTP_USER;
	$params['password'] = SMTP_PASS;
}
*/
/**********************************/
// STARTUP CONFIGURATION DATA
/**********************************/
/*
$configs = $db->getAll( 'SELECT * from !',array( CONFIG_TABLE ) );
$config = array();

foreach( $configs as $index => $row ) {
	$config[ $row[config_variable] ] = $row[config_value];
}

$skin_name = $config['skin_name'];
$lang['site_name'] = $config['site_name'];
define ('SITENAME', $config['site_name']);

if ($_REQUEST['lang']!= '') {$opt_lang=$_REQUEST['lang'];}
elseif ($_SESSION['opt_lang'] != '') {$opt_lang=$_SESSION['opt_lang'];}
elseif ($_COOKIE[$config['cookie_prefix'].'opt_lang'] != '') {$opt_lang=$_COOKIE[$config['cookie_prefix'].'opt_lang'];}
else {$opt_lang=DEFAULT_LANG; }

// hack - fix later
if ( strlen( $opt_lang ) <= 3 ) {
	$opt_lang = DEFAULT_LANG;
}

// $langfile = LANG_DIR.$language_files[$opt_lang];

$_SESSION['opt_lang'] = ($opt_lang=='')?'english':$opt_lang;
setcookie($config['cookie_prefix'].'opt_lang',$opt_lang,time()+60*60*24*365);

$t->template_dir = TEMPLATE_DIR . $skin_name.'/';
$t->compile_dir = TEMPLATE_C_DIR;
$t->cache_dir = CACHE_DIR;
// set the default handler and other values for caching and faster loading
// $t->default_template_handler_func = 'getTplFile';
$t->caching = false;
$t->force_compile = false;
$t->register_prefilter("prefilter_getlang");
$t->compile_id=$_SESSION['opt_lang'];

$t->assign( 'docroot', DOC_ROOT );
$t->assign( 'banner_dir', DOC_ROOT.'banners/' );
$t->assign( 'zodiac_dir', DOC_ROOT.'templates/'.$skin_name. '/zodiacsigns/' );
$t->assign( 'image_dir', DOC_ROOT.'templates/'.$skin_name.'/images/' );
$t->assign( 'css_path', DOC_ROOT.'templates/'.$skin_name.'/' );


require_once MODOSDATE_DIR . 'modOsDate.php';

$mod = new modOsDate();
*/


// If a plugin is provided, time to process and display it's panel
//
if ( isset($_REQUEST['plugin']) ) {

    $param['plugin'] = $_REQUEST['plugin'];

    print $mod->modDisplayPluginContent($param);

}

/*
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
*/
exit;

?>