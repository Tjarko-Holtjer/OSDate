<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

if ($_GET['errid'] != '') {

	$t->assign ( 'login_error', get_lang('errormsgs',$_GET['errid']) );

}

$t->assign('lang',$lang);
$t->assign('rendered_page', $t->fetch('login.tpl') );

$t->display( 'index.tpl' );
?>