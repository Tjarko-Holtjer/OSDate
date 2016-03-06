<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

$userid = $_REQUEST['userid'] > 0? $_REQUEST['userid'] : $_SESSION['UserId'];

$videono = $_REQUEST['videono'];

if( $_GET['del'] == 'yes' ){

	$sql = 'SELECT id, filename FROM ! WHERE userid = ? AND videono = ?';

	$row = $db->getRow( $sql, array( USER_VIDEOS_TABLE, $userid, $videono ) );

	@unlink(USER_VIDEO_DIR.$row['filename']);

	$db->query('delete from ! where userid = ? and videono = ?',array( USER_VIDEOS_TABLE, $userid, $videono  ) );

	updateLoadedVideosCnt($userid);

	header('location: ?userid='.$userid);

	exit;
}

$sql = 'select videono, filename, album_id from ! where userid = ? order by videono';

$rows = $db->getAll( $sql, array( USER_VIDEOS_TABLE, $userid ) );

$userdata = $db->getRow('select usr.level, usr.username,  mem.allow_videos, mem.videoscnt, mem.allowalbum from ! as usr, ! as mem where mem.roleid = usr.level and usr.id = ?', array(USER_TABLE, MEMBERSHIP_TABLE, $userid ) );

$data = array();
$data[]="  ";
foreach ($rows as $row) {

	$data[] = $row;
	$nextpic = $row['videono'];
}

$nextpic++;

$useralbums = $db->getAll('select id, name from ! where username = ? order by name', array(USERALBUMS_TABLE, $userdata['username'] ) );

$t->assign('nextvideo',$nextpic);

$t->assign('album_id', $_POST['album_id']);

$t->assign('useralbums',$useralbums);

$t->assign('userdata',$userdata);

if ( function_exists('passthru')) {
	/* system command is allowed */
	$t->assign('system_allowed','Y');
}

$t->assign ( 'lang', $lang );

$t->assign ( 'data', $data );

$t->assign('userid', $userid);

$t->assign('max_picture_cnt', (count($data) < $userdata['videoscnt'])? count($data) : $userdata['videoscnt'] );

$t->assign('rendered_page',$t->fetch('uservideos.tpl'));

$t->display ( 'index.tpl' );

?>