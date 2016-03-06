<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

$userid = $_REQUEST['userid'] ;

if ($userid == '') $userid = $_SESSION['UserId'];

$videono = $_REQUEST['videono'];

if ($videono == '' or $videono < 1 or !isset($videono) or $videono == null) $videono= $db->getOne('select max(videono)+1 from ! where userid = ?',array(USER_VIDEOS_TABLE,$userid) );

if ($videono == '') $videono = 1;
$userinfo = $db->getRow('select * from ! where id = ?', array( USER_TABLE, $userid) );

$err = 0;

if ($config['snaps_require_approval'] == 'Y') {

	$act = 'N';

} else {

	$act = 'Y';

}

$curr_file =  '';

if ($_POST['album_name'] != '') {

/* Add new album first and then process the image */

	$album_id = $db->getOne('select id from ! where name = ? and username = ?', array(USERALBUMS_TABLE, $_POST['album_name'], $userinfo['username'] )  );

	if ($album_id > 0 ) {
		null;
	} else {
		$db->query('insert into ! (username, name, passwd) values (?, ?, ?)', array( USERALBUMS_TABLE, $userinfo['username'], $_POST['album_name'], md5($_POST['album_passwd'])) );

		$album_id = $db->getOne('select id from ! where name = ? and username = ?', array(USERALBUMS_TABLE, $_POST['album_name'],  $userinfo['username'] )  );
	}

} else {

	$album_id = $_POST['album_id'];

}

if (isset($_POST['changealbum']) ) {
/* Change album name  */

	$sql = "update ! set album_id = ? where userid = ? and videono = ?";
	$db->query($sql, array(USER_VIDEOS_TABLE, $album_id, $userid, $videono) );
	header( 'location: uploadvideos.php?userid='.$userid.'&amp;msg='.ALBUM_CHANGED );
	exit;
}

if( is_uploaded_file( $_FILES['txtimage']['tmp_name'] ) ) {

	$img_file = $_FILES['txtimage']['tmp_name'];
	$fname = $_FILES['txtimage']['name'];

/*
	$ext = split( '/', $_FILES['txtimage']['type'] );

	$picext = strtolower($ext[1]);

	//echo "$picext<br>";

	$ext_ok = '0';

	foreach (explode(',',$config['upload_videos_ext']) as $ex) {


		if ($ex == $picext ) $ext_ok++;

	}

	if ( $ext_ok <= '0' ) {

		header( 'location: uploadvideos.php?msg=' .WRONG_TYPE .'&amp;userid='.$userid );
		exit;

	}
*/
	clearstatcache();

	/* Create video file name */
	if (substr_count($fname,'.flv') > 0 || substr_count($fname,'.swf') > 0 ) {

		$orgimg = file_get_contents($img_file);
		$video_filename = $userinfo['username'].'_V'.$videono.'_'.$fname;


	/* Now write the video into file */
		$fout = @fopen(USER_VIDEO_DIR.$video_filename,'wb');
		fwrite($fout, $orgimg);
		fclose($fout);
		$rtn = true;
	} else {

		$video_filename = $userinfo['username'].'_V'.$videono.'_'.time().'.flv';
		include ('mpeg2flv/mpeg2flv.php');
		$rtn = convert2flv($img_file, USER_VIDEO_DIR.$video_filename);

	}
	if ($rtn==true || $rtn != '') {
		/* Now add this into the table */
		$sql = 'insert into ! (userid, videono, filename, album_id, active) values (?,?,?,?,?)';

		$db->query($sql, array(USER_VIDEOS_TABLE, $userid, $videono, $video_filename, $album_id, $act) );

		updateLoadedVideosCnt($userid);

		if ($config['newvideo_admin_info'] == 'Y') {
			sendAdminEmail();
		}

		Header("Cache-Control: must-revalidate");

		$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() -30) . " GMT";
		Header($ExpStr);

		header( 'location: uploadvideos.php?msg='.VIDEO_LOADED.$rtn.'&userid='.$userid );

		exit;
	} else {
		Header("Cache-Control: must-revalidate");

		$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() -30) . " GMT";
		Header($ExpStr);

		header( 'location: uploadvideos.php?msg=130&userid='.$userid );

		exit;

	}
}


header( 'location: uploadvideos.php?msg=125&userid='.$userid );
exit;


function sendAdminEmail () {
/* Send email to admin */
	global $db, $userid, $config, $userinfo;

	$body = get_lang('newvideo', MAIL_FORMAT);

	$Subject = get_lang('newvideo_sub'). ' - ' . $config['site_name'];

	$From = $To = $email = $config['admin_email'];

	$body = str_replace( '#SiteName#',  SITENAME , $body );

	$body = str_replace( '#AdminName#',  $config['admin_name'] , $body );

	$body = str_replace( '#UserName#',  $userinfo['username'] , $body );

	$body = str_replace( '#PicNo#',  $videono , $body );

	mailSender($From, $To, $email, $Subject, $body);

}

?>