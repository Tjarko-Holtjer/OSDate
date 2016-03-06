<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$t->assign('lang',$lang);

include ( 'sessioninc.php' );

if ( isset( $_GET['errid'] ) ) {

	$t->assign ( 'pwd_change_error', get_lang('errormsgs',$_GET['errid']) );
}

$t->assign('rendered_page', $t->fetch('changempass.tpl') );

$t->display ( 'index.tpl' );


?>