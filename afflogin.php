<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$t->assign('lang',$lang);

$t->assign( 'rendered_page', $t->fetch( 'afflogin.tpl' ) );

$t->display( 'index.tpl' );
?>