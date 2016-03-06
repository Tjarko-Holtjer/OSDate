<?php
/* showpic_iframe.php
	This will display the picture in the iframe defined

	Vijay Nair
*/

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$t->assign('userid',$_GET['id']);
$t->assign('galpicid',$_GET['picid']);
$t->assign('typ', $_GET['typ']);
$t->assign('album_id', $_GET['album_id']);

$t->display("showpic_iframe.tpl");

?>