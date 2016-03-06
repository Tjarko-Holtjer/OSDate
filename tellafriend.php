<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$t->assign( 'title', get_lang('site_links','invite_a_friend') );
$t->assign('lang',$lang);
$t->assign('rendered_page', $t->fetch('tellafriend.tpl') );
$t->display ( 'index.tpl' );
?>