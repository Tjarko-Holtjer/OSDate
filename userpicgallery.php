<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include('sessioninc.php');

$userid = $_REQUEST['id'];

$username = $db->getOne('select username from ! where id = ?',array( USER_TABLE, $userid) );

$useralbums = $db->getAll('select id, name from ! where username = ?', array(USERALBUMS_TABLE, $username) );

$album_passwd = $_REQUEST['album_passwd'];

$album_id = $_REQUEST['album_id'];

if ($album_id != '') {

	/* First check if the user opted to allow membrs in the buddy list to view */

	$buddy_view = $db->getOne('select choice_value from ! where userid=? and choice_name=?', array(USER_CHOICES_TABLE, $userid, 'allow_buddy_view_album') );
	$hotlist_view = $db->getOne('select choice_value from ! where userid=? and choice_name=?', array(USER_CHOICES_TABLE, $userid, 'allow_hotlist_view_album') );

	if ($buddy_view == '1' or $buddy_view == '' or !isset($buddy_view) ) {

		$in_buddy_list = $db->getOne('select id from ! where username = ? and ref_username = ? and act = ?', array(BUDDY_BAN_TABLE, $username, $_SESSION['UserName'], 'F') );

	}

	if ($hotlist_view == '1' or $hotlist_view == '' or !isset($hotlist_view) ) {

		$in_hot_list = $db->getOne('select id from ! where username = ? and ref_username = ? and act = ?', array(BUDDY_BAN_TABLE, $username, $_SESSION['UserName'], 'H') );

	}

	if ( ($in_buddy_list == '' or !isset($in_buddy_list) or $in_buddy_list < 0 or $buddy_view == '0' or !isset($buddy_view)) and ($in_hot_list == '' or !isset($in_hot_list) or $in_hot_list < 0 or $hotlist_view == '0' or !isset($hotlist_view)) ) {

			$pwd = $db->getOne('select passwd from ! where username = ? and  id = ?', array(USERALBUMS_TABLE, $username, $album_id) );

			if ($pwd != md5($album_passwd) && $userid != $_SESSION['UserId']) {

				$err = INVALID_PASSWORD;

				$album_id = '';
			}
	}

}

if ($album_id != '') {
	$pics = $db->getAll('select picno from ! where userid = ? and album_id =?',array( USER_SNAP_TABLE, $userid, $album_id) );
} else {
	$pics = $db->getAll('select picno from ! where userid = ? and (album_id is NUll or album_id = ?)',array( USER_SNAP_TABLE, $userid, 0) );
}

$t->assign('useralbums', $useralbums);

$t->assign('username',$username);

$t->assign('pics',$pics);

$t->assign('userid',$userid);

$t->assign('err', $err);

$t->assign('album_id', $album_id);

$t->assign('lang',$lang);

if ( $config['use_profilepopups'] == 'Y' ) {

	$t->display( 'userpicgallery.tpl' );

}
else {

	$t->assign( 'rendered_page', $t->fetch( 'userpicgallery.tpl' ) );

	$t->display ( 'index.tpl' );
}


?>