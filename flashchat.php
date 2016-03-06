<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$t->assign('rendered_page', $t->fetch('flashchat.tpl') );

if ($_SESSION['AdminId'] > 0) {
	$t->display ( 'admin/index.tpl' );
} else {
	$t->display ( 'index.tpl' );
}
?>
