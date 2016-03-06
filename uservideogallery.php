<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}
include('sessioninc.php');

$userid = $_REQUEST['userid'];

$username = $db->getOne('select username from ! where id = ?',array( USER_TABLE, $userid) );

$useralbums = $db->getAll('select id, name from ! where username = ?', array(USERALBUMS_TABLE, $username) );

$album_passwd = $_REQUEST['album_passwd'];

$album_id = $_REQUEST['album_id'];

if ($album_id != '' && $_SESSION['AdminId'] == '' ) {

	$pwd = $db->getOne('select passwd from ! where username = ? and  id = ?', array(USERALBUMS_TABLE, $username, $album_id) );

	if ($pwd != md5($album_passwd) && $userid != $_SESSION['UserId']) {

		$err = INVALID_PASSWORD;

		$album_id = '';
	}
}

if ($album_id != '') {
	$pics = $db->getAll('select videono from ! where userid = ? and album_id =? and active = ?',array( USER_VIDEOS_TABLE, $userid, $album_id, 'Y') );
} else {
	$pics = $db->getAll('select videono from ! where userid = ? and (album_id is NUll or album_id = ?) and active = ?',array( USER_VIDEOS_TABLE, $userid, 0, 'Y') );
}

$t->assign('useralbums', $useralbums);

$t->assign('username',$username);

$t->assign('pics',$pics);

$t->assign('userid',$userid);

$t->assign('err', $err);

$t->assign('album_id', $album_id);

if ( $config['use_profilepopups'] == 'Y' ) {

	$t->display( 'uservideogallery.tpl' );

}
else {

	$t->assign( 'rendered_page', $t->fetch( 'uservideogallery.tpl' ) );

	$t->display ( 'index.tpl' );
}


?>