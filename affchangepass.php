<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'affsessioninc.php' );

if ( isset( $_GET['errid'] ) ) {
	$t->assign ( 'pwd_change_error', get_lang('errormsgs',$_GET['errid']));

}

$t->assign('rendered_page', $t->fetch('affchangepass.tpl') );

$t->display ( 'index.tpl' );

?>