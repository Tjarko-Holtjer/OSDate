<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}
$t->assign('lang',$lang);

$t->assign('conf', $_GET['conf']);

$t->assign('rendered_page', $t->fetch('confirmreg.tpl') );

$t->display( 'index.tpl' );
?>