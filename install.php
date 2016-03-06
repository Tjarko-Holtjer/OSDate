<?php
//osDate Installer
session_start();
include 'install_files/consts.php'; 	// Constants
include 'install_files/funcs.php';		// Needed functions
include 'install_files/header.tpl';		// HTML Header
@set_time_limit(1200);

error_reporting( E_ALL - E_NOTICE );

if ( (!isset( $_GET['step'])) || ($_GET['step'] < 1 || $_GET['step'] > 7) )
	$step = 1;
else
	$step = $_GET['step'];

if ($step > 1)
{
	$typeValues = array( 'mysql', 'pgsql', 'ibase', 'msql', 'mssql', 'oci8', 'odbc', 'sybase', 'ifx', 'fbsql');

	$typeNames  = array(
		'MySQL',
		'PostgreSQL',
		'InterBase',
		'Mini SQL',
		'Microsoft SQL Server',
		'Oracle 7/8/8i',
		'ODBC (Open Database Connectivity)',
		'SyBase',
		'Informix',
		'FrontBase'
		);
	// Can use templates
	$full_path = dirname(__FILE__) . '/';
	$dbtype='mysql'; //default db type
	import_request_variables( 'pgs' );

	define ( 'SMARTY_DIR', $full_path . 'libs/Smarty/' );
	define ( 'TEMPLATES_DIR', $full_path . 'templates/' );
	define ( 'TEMPLATE_DIR', $full_path . 'templates/' );
	define ( 'TEMPLATE_C_DIR', $full_path . 'templates_c/' );
	define ( 'PEAR_DIR', $full_path . 'libs/Pear/' );
	define ( 'CACHE_DIR', $full_path . 'cache/' );
	define ( 'INCLUDE_DIR', $full_path . 'includes/' );
	define ( 'DOC_ROOT', $full_path );
//	define ( 'LANG_DIR', $full_path . 'language/' );

	require_once SMARTY_DIR . 'Config_File.class.php';
	require_once SMARTY_DIR . 'Smarty.class.php';
	require_once INCLUDE_DIR . 'internal/Functions.php';

	//ini_set( 'include_path', PEAR_DIR );

	require_once( PEAR_DIR . 'DB.php' );

	//	PEAR::setErrorHandling( PEAR_ERROR_CALLBACK, 'errhndl' );

	$t = new Smarty;
	$t->template_dir = TEMPLATE_DIR;
	$t->compile_dir = TEMPLATE_C_DIR;
	$t->cache_dir = CACHE_DIR;

	$t->assign('typeValues',$typeValues);
	$t->assign('typeNames',$typeNames);

	$t->assign('dbtype', $dbtype);

	if (!isset($_SESSION['configAvailable'])) $_SESSION['configAvailable'] = 'N';

	if ( !isset($_SESSION['replacearray'])) {

		if (file_exists('myconfigs/config.php') ) {

			include_once('myconfigs/config.php');
			$configdata = file('myconfigs/config.php');

		} elseif (file_exists('config.php')) {

			$configdata = file('config.php');
			include_once('config.php');

		}
		if (count($configdata) > 2) {
			$_SESSION['configAvailable'] = 'Y';
			$_SESSION['osDate_current_version'] = VERSION;
			foreach ($configdata as $line) {
				if (substr_count($line,'define') > 0) {
					eval($line);
				}
			}
		}

		$_SESSION['replacearray'] = array(
		'MAIL_FORMAT'	=> MAIL_FORMAT,
		'MAIL_TYPE'		=> MAIL_TYPE,
		'SMTP_HOST'		=> SMTP_HOST,
		'SMTP_PORT'		=> SMTP_PORT,
		'SMTP_AUTH'		=> SMTP_AUTH,
		'SMTP_USER'		=> SMTP_USER,
		'SMTP_PASS'		=> SMTP_PASS,
		'SM_PATH'		=> SM_PATH,
		'DB_USER'		=> DB_USER,
		'DB_NAME'		=> DB_NAME,
		'DB_HOST'		=> DB_HOST,
		'DB_PASS'		=> DB_PASS,
		'DB_TYPE'		=> DB_TYPE,
		'DB_PREFIX' 	=> DB_PREFIX
		);

	} elseif (isset($_SESSION['replacearray'])){

		define ('MAIL_FORMAT', $_SESSION['replacearray']['MAIL_FORMAT']);
		define ('MAIL_TYPE', $_SESSION['replacearray']['MAIL_TYPE']);
		define ('SMTP_HOST', $_SESSION['replacearray']['SMTP_HOST']);
		define ('SMTP_PORT', $_SESSION['replacearray']['SMTP_PORT']);
		define ('SMTP_AUTH', $_SESSION['replacearray']['SMTP_AUTH']);
		define ('SMTP_USER', $_SESSION['replacearray']['SMTP_USER']);
		define ('SMTP_PASS', $_SESSION['replacearray']['SMTP_PASS']);
		define ('SM_PATH', $_SESSION['replacearray']['SM_PATH']);
		define ('DB_USER', $_SESSION['replacearray']['DB_USER']);
		define ('DB_NAME', $_SESSION['replacearray']['DB_NAME']);
		define ('DB_HOST', $_SESSION['replacearray']['DB_HOST']);
		define ('DB_PASS', $_SESSION['replacearray']['DB_PASS']);
		define ('DB_TYPE', $_SESSION['replacearray']['DB_TYPE']);
		define ('DB_PREFIX', $_SESSION['replacearray']['DB_PREFIX']);
		define ('DOC_ROOT', $_SESSION['replacearray']['DOC_ROOT']);

	}

	define('VERSION',$_SESSION['osDate_current_version']);
}

// First step - general check

if ($step == 1) {
	include 'install_files/step_1.tpl';

	unset($_SESSION['replacearray']);
	unset($_SESSION['configAvailable']);
	$canContinue = 1;

	//check GD librarry
	$good = function_exists( 'gd_info' ) ? 1 : 0;
	$canContinue = $canContinue && $good;
	Message ( 'GD library exists: ', $good );

	//JPEG support
	$good = function_exists( 'imagecreatefromjpeg' ) && function_exists( 'imagejpeg' ) ? 1 : 0;
	$canContinue = $canContinue && $good;
	Message ( 'JPEG support exists for GD: ', $good );
	//---
/*	if ( file_exists('./config.php') ) {
		$canContinue = isWriteable ( $canContinue, 'config.php', 0777, 'config.php file' );
	} else {
		$canContinue = isWriteable ( $canContinue, 'config.inc.php', 0777, 'config.php file' );
	}
*/
	$canContinue = isWriteable ( $canContinue, 'myconfigs/', 0777, 'myconfigs folder');
	if (file_exists('configs/config.php') ) {
		$canContinue = isWriteable ( $canContinue, 'myconfigs/config.php', 0777, 'myconfigs/config.php file');
	}
	$canContinue = isWriteable ( $canContinue, 'temp/', 0777, 'temp folder' );
	$canContinue = isWriteable ( $canContinue, 'templates_c/', 0777, 'templates_c folder' );
	$canContinue = isWriteable ( $canContinue, 'cache/', 0777, 'cache folder' );
	$canContinue = isWriteable ( $canContinue, 'emailimages/', 0777, 'emailimages folder' );
	$canContinue = isWriteable ( $canContinue, 'banners/', 0777, 'banners folder' );
	$canContinue = isWriteable ( $canContinue, 'userimages/', 0777, 'userimages folder' );
	$canContinue = isWriteable ( $canContinue, 'uservideos/', 0777, 'uservideos folder' );
	$canContinue = isWriteable ( $canContinue, 'plugins/', 0777, 'plugins folder' );
	$ftpCan = $canContinue;

	$good = function_exists( 'mysql_connect' ) ? 1 : 0;
	$canContinue = $canContinue && $good;

	Message ( 'MySQL support exists: ', $good );

	$good = phpversion() >= '4.1.2' ? 1 : 0;
	$canContinue = $canContinue && $good;

	Message ( 'PHP version >= 4.1.2: ', $good );

	echo '</table>';

	if ( $canContinue) {
		echo  '<tr><td colspan="2" align="center"><b><font style="font-size:12pt">Congratulations!</font></b><br />You may continue the installation.</td></tr><tr><td colspan=2 align="right"><input type="button" name="continue" value="Continue >>" onclick="javascript:document.location.href=\'?step=2&amp;dispstep=1\'" /></td></tr>';
	} else {
		echo  '<tr><td colspan="2" ><br />The installer has detected some problems with your server environment, which will not allow osDate to operate correctly.<br /><br />Please correct these issues and then refresh the page to re-check your environment.<br /><br />';

		echo '<br /><input type="button" name="continue" value="Continue >>" onclick="javascript:alert(\'Please correct the above problems before continuing.\')" /></td></tr>';
	}
}

// Second step - database login information

if ( $step == 2 )
{
	$t->assign( 'errorConnection', 0 );
	include 'install_files/step_2.php';
}

// Third step - test connection

if ( $step == 3 )
{
	include 'install_files/step_3.php';
}

if ($step == 4) {

	include 'install_files/step_4.php';
}
if ($step == 5) {

	include 'install_files/step_5.php';
}
if ($step == 6)
{
	/* Accept mail settings */

	$sendMailPath = @ini_get( sendmail_path );

	// if sendmail is found, then parse it to remove sendmail options

	if ( $sendMailPath ) {
		$sendMailPathParts = explode( ' ', $sendMailPath );
		$sendMailPath = $sendMailPathParts[0];
	}

	$t->assign( 'dispstep', $dispstep+1);
	$t->assign( 'config_opt', $config_opt);

	$t->assign( 'sendMailPath', $sendMailPath );

    $t->assign( 'formatValues', array( 'text', 'html') );
    $t->assign( 'formatNames', array(  'Text', 'HTML' ) );
    $t->assign( 'typeValues', array( 'sendmail', 'smtp', 'mail' ) );
    $t->assign( 'typeNames', array(  'Sendmail', 'SMTP', 'Standard Mail' ) );

    $t->display( 'install/install_step6.tpl' );
}

// Fifth step - writing mail settings
if ($step == 7)
{
	// clear the cache & template_c before starting osDate

	$t->clear_all_cache();
	$t->clear_compiled_tpl();

	// remove all files from image cache
	include_once( 'includes/internal/class.cacher.php' );
	$c = new Cacher();
	$c->clear();

	define ('CONFIG_FILE', 'config.inc.php');

	if ($_POST['mail_set']!= '' ) {

	    extract ($_POST);

	    if (!isset($smtpAuth))
			$smtpAuth = 0;

		// Replacing config variables

		$replace = array(
			'DB_USER' => $_SESSION['replacearray']['DB_USER'],
			'DB_NAME' => $_SESSION['replacearray']['DB_NAME'],
			'DB_HOST' => $_SESSION['replacearray']['DB_HOST'],
			'DB_PASS' => $_SESSION['replacearray']['DB_PASS'],
			'DB_TYPE' => $_SESSION['replacearray']['DB_TYPE'],
			'DB_PREFIX' => $_SESSION['replacearray']['DB_PREFIX'],
			'DOC_ROOT' => $_SESSION['replacearray']['DOC_ROOT'],
			'MAIL_FORMAT'	=> $mailFormat,
			'MAIL_TYPE'		=> $mailType,
			'SMTP_HOST'		=> $smtpHost,
			'SMTP_PORT'		=> $smtpPort,
			'SMTP_AUTH'		=> $smtpAuth,
			'SMTP_USER'		=> $smtpUser,
			'SMTP_PASS'		=> $smtpPassword,
			'SM_PATH'		=> $smPath );

		$configData = getConfigData( $replace, CONFIG_FILE );

		$configCreated = writeConfig( $configData );

		$t->assign( 'configCreated', $configCreated );
	}
	$t->assign('mail_set', $_POST['mail_set']);
	$t->assign('config_opt', $config_opt);
	$t->assign('dispstep', $dispstep + 1);
	$t->display('install/install_step7.tpl');

}

unset( $db );

include './install_files/footer.tpl'; //HTML Footer.
?>