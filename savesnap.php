<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

$userid = $_SESSION['UserId'];

$sql = 'SELECT id, picture, tnpicture FROM ! WHERE userid = ? AND picno = ?';

$row = $db->getRow( $sql, array( USER_SNAP_TABLE, $userid, $_POST['txtpicno'] ) );

$userinfo = $db->getRow('select * from ! where id = ?', array( USER_TABLE, $userid) );

$err = 0;

if ($config['snaps_require_approval'] == 'Y') {

	$act = 'N';

} else {

	$act = 'Y';

}

$curr_imgfile = $curr_tnimgfile = '';

if ($config['images_in_db'] == 'N') {

	if (substr_count($row['picture'], 'file:' )>0 ) {
		$curr_imgfile = ltrim(rtrim(str_replace('file:','',$row['picture'] ) ) );
	}
	if (substr_count($row['tnpicture'],'file:' )>0 ) {
		$curr_tnimgfile = ltrim(rtrim(str_replace('file:','',$row['tnpicture'] ) ) );
	}
}

$allwdsize = $config['upload_snap_maxsize'];

if ($_POST['album_name'] != '') {

/* Add new album first and then process the image */

	$album_id = $db->getOne('select id from ! where name = ? and username = ?', array(USERALBUMS_TABLE, $_POST['album_name'], $userinfo['username'] )  );

	if ($album_id > 0 ) {
		null;
	} else {
		$db->query('insert into ! (username, name, passwd) values (?, ?, ?)', array( USERALBUMS_TABLE, $userinfo['username'], $_POST['album_name'], md5($_POST['album_passwd'])) );

		$album_id = $db->getOne('select id from ! where name = ? and username = ? ', array(USERALBUMS_TABLE, $_POST['album_name'], $userinfo['username']  )  );
	}

} else {

	$album_id = $_POST['album_id'];

}

if (isset($_POST['changealbum']) ) {
/* Change album name  */

	$sql = "update ! set album_id = ? where userid = ? and picno = ?";
	$db->query($sql, array(USER_SNAP_TABLE, $album_id, $userid, $_POST['txtpicno']) );
	header( 'location: uploadsnaps.php?msg='.ALBUM_CHANGED );
	exit;

}

if( is_uploaded_file( $_FILES['txtimage']['tmp_name'] ) ) {

	$img_file = $_FILES['txtimage']['tmp_name'];

	$ext = split( '/', $_FILES['txtimage']['type'] );

	$picext = strtolower($ext[1]);

	if( $picext == 'pjpeg' || $picext == 'jpeg'){

		$picext = 'jpg';
	}

	if( $picext == 'x-png' ) {
		$picext= 'png';
	}
	//echo "$picext<br>";

	$ext_ok = '0';

	foreach (explode(',',$config['upload_snap_ext']) as $ex) {


		if ( $ex == $picext ) $ext_ok++;

	}

/* bmp is removed as valid source time being */
	if ( $ext_ok <= '0' or $picext == 'bmp') {

		header( 'location: uploadsnaps.php?msg=' .WRONG_TYPE  );
		exit;

	}

	clearstatcache();

	$fstats= stat($img_file);

	$picsize = $fstats[7];

	$handle = fopen ($img_file, 'rb');

	/* Get current picture size and allowed size. If pic size is more than the allowed size, flag error.. */


	if ($picsize > $allwdsize) {

		header( 'location: uploadsnaps.php?msg='.BIG_PIC_SIZE );
		exit;

	}

	$orgimg = fread($handle, $picsize);

	fclose ($handle);

	if ($_POST['txtpicno'] == '' or !isset($_POST['txtpicno']) ) {
		$_POST['txtpicno'] = $db->getOne('select max(picno) from ! where userid = ?',array(USER_SNAP_TABLE,$userid) )+1;
	}

	if ( $picext != 'jpg' ) {
	/* convert the picture to jpg. This is to enable picture editing  */


		//$jpgfile = createThumb($orgimg, 'N');
		$img_tmp=createImg($picext,$img_file);
		$jpgfile = createJpeg($img_tmp, 'N');
		$newimg = file_get_contents($jpgfile);

	} else {

		$newimg = $orgimg;
	}

	$img_tmp=createImg($picext,$img_file);

	$tnimg_file = createJpeg($img_tmp,'Y');

	$tnimg = file_get_contents($tnimg_file);

	$tnext = 'jpg';

	$picext = 'jpg';

	if ($config['images_in_db'] == 'N') {

		$imgfile = writeImageToFile($newimg, $userid, '1'.$_POST['txtpicno'],$curr_imgfile);

		$newimg = 'file:'.$imgfile;
		sleep(5);

		$tnimgfile = writeImageToFile($tnimg, $userid, '2'.$_POST['txtpicno'],$curr_tnimgfile);

		$tnimg = 'file:'.$tnimgfile;

	} else {

		$newimg = base64_encode($newimg);

		$tnimg = base64_encode($tnimg);
	}

	if ( $row ) {

		$sql = 'update ! set picture = ?, ins_time = ?, active=?, picext=?, tnpicture = ?, tnext = ?, album_id = ?  where userid = ? and picno = ? and id = ?';

		$db->query( $sql, array( USER_SNAP_TABLE, $newimg, $time, $act,	$picext, $tnimg, $tnext, $album_id, $userid, $_POST['txtpicno'], $row['id'] ) );

	} else {

		$sql = 'insert into ! (  userid, picno, picture, ins_time, active, picext, tnpicture, tnext, album_id ) values (  ?, ?, ?, ?, ?, ?, ?, ?, ? )';

		$db->query( $sql, array( USER_SNAP_TABLE, $userid, $_POST['txtpicno'], $newimg, $time, $act, $picext, $tnimg, $tnext, $album_id ) );

	}
	updateLoadedPicturesCnt($userid);

	if ($config['newpic_admin_info'] == 'Y') {
		sendAdminEmail();
	}

	Header("Cache-Control: must-revalidate");

	$ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() -30) . " GMT";
	Header($ExpStr);

	header( 'location: uploadsnaps.php?msg='.PICTURE_LOADED );
	exit;

}

if ( is_uploaded_file( $_FILES['tnimage']['tmp_name'] ) ) {

	$tnimg_file = $_FILES['tnimage']['tmp_name'];

	$ext = split( '/', $_FILES['tnimage']['type'] );

	$tnext = strtolower($ext[1]);

	$tnsize = $config['upload_snap_tnsize'];

	if( $tnext == 'pjpeg' || $tnext == 'jpeg'){

		$tnext = 'jpg';

	}

	if( $tnext == 'x-png' ) {
		$tnext= 'png';
	}

	$ext_ok = 0;

	foreach (explode(',',$config['upload_snap_ext']) as $ex) {

		if ( $ex == $tnext ) $ext_ok++;

	}

	if ( $ext_ok <= 0 ) {

		header( 'location: uploadsnaps.php?msg=' .WRONG_TYPE  );
		exit;

	}

	clearstatcache();

	$fstats= stat($tnimg_file);

	$picsize = $fstats[7];

	if ($picsize > $allwdsize) {

		header( 'location: uploadsnaps.php?msg='.BIG_PIC_SIZE );
		exit;

	}

	list($tnwidth, $tnheight, $tntype, $tnattr) = getimagesize($tnimg_file);


	if ($tnwidth > $tnsize or $tnheight > $tnsize) {

			header( 'location: uploadsnaps.php?msg='.BIGTHUMBNAIL );
			exit;
	}

	$handle = fopen ($tnimg_file, 'rb');

	/* Get current picture size and allowed size. If pic size is more than the allowed size, flag error.. */

	$tnimg = fread($handle, filesize ($tnimg_file));

	fclose ($handle);

	if ( $tnext != 'jpg' ) {

	/* convert the picture to jpg. This is to enable picture editing  */

		//$jpgfile = createThumb($tnimg, 'N');
		$img_tmp=createImg($tnext,$tnimg_file);
		$jpgfile = createJpeg($img_tmp, 'N');

		$newtnimg = file_get_contents($jpgfile);

		unlink($jpgfile);

		$tnext = 'jpg';

	} else {

		$newtnimg = $tnimg;
	}


	$tnimg = base64_encode( $newtnimg );

	if ($config['images_in_db'] == 'N') {

		$tnimgfile = writeImageToFile($tnimg, $userid, $_POST['txtpicno'], $curr_tnimgfile);

		$tnimg = 'file:'.tnimgfile;
	}

	if ($row) {

		$sql = 'update ! set tnpicture = ?, ins_time = ?, active=?, tnext=?, album_id = ? where id = ?';

		$db->query( $sql, array( USER_SNAP_TABLE, $tnimg, $time, $act, 	$tnext, $album_id, $row['id'] ) );

	} else {

		$sql = 'insert into ! (  userid, picno, tnpicture, ins_time, active, tnext, album_id ) values (  ?, ?, ?, ?, ?, ?, ? )';

		$db->query( $sql, array( USER_SNAP_TABLE,  $userid, $_POST['txtpicno'], $tnimg, $time, $act, $tnext, $album_id ) );

	}

	updateLoadedPicturesCnt($userid);

	if ($config['newpic_admin_info'] == 'Y') {
		sendAdminEmail();
	}

	header( 'location: uploadsnaps.php?msg='.PICTURE_LOADED );
	exit;

}

header( 'location: uploadsnaps.php?msg='.FAILED_UPLOAD );
exit;

function createImg($type,$file) {
	$type=strtolower($type);
	if($type == 'bmp') $img=imagecreatefromwbmp($file);
	else if($type == 'png') $img=imagecreatefrompng($file);
	else if($type == 'gif') $img=imagecreatefromgif($file);
	else if($type == 'jpg') $img=imagecreatefromjpeg($file);
	return $img;
}



function createJpeg( $img , $reduce='Y') {

	global $config;
	global $userid;
	global $ext;

	$tnsize = $config['upload_snap_tnsize'];

	//$img = imagecreatefrompng($org);

	$w = imagesx( $img );

	$h = imagesy( $img );

	if ($reduce == 'Y' && ($w > $tnsize || $h > $tnsize)) {
		if( $w > $h ) {
			$ratio = $w / $h;
			$nw = $tnsize;
			$nh = $nw / $ratio;
		} else {
			$ratio = $h / $w;
			$nh = $tnsize;
			$nw = $nh /$ratio;
		}
	} else {

		$nh = $h;
		$nw = $w;
	}

	$img2 = imagecreatetruecolor( $nw, $nh );

	imagecopyresampled ( $img2, $img, 0, 0, 0 , 0, $nw, $nh, $w, $h );

	$fimg = 'img_' . time().$userid . '.jpg';

	$real_tpath = realpath ("temp");

	if(	$HTTP_ENV_VARS['OS'] == 'Windows_NT'){

		$real_tpath= str_replace( "\\", "\\\\", $real_tpath);

		$file = $real_tpath . "\\" . $fimg;

	}else{

		$file = $real_tpath . "/" . $fimg;

	}

	imagejpeg( $img2, $file );

	imagedestroy($img2);

	imagedestroy($img);

	return $file;
}



function writeImageToFile($img, $userid, $picno, $file="") {
/* This routine will create an image file */
	if ($file == '') {
		$filename= time().$userid.$picno.'.jpg';
	} else {
		$filename = $file;
	}

	$img = imagecreatefromstring( $img );
	imagejpeg($img, USER_IMAGE_DIR.$filename);

	return ($filename);
}

function sendAdminEmail () {
/* Send email to admin */
	global $db, $userid, $config, $t;

	$siteurl = HTTP_METHOD. $_SERVER['SERVER_NAME'] . DOC_ROOT;

	$body = get_lang('newpic', MAIL_FORMAT);

	$Subject = get_lang('newpic_sub');

	$From = $To = $email = $config['admin_email'];

	$username = $db->getOne('select username from ! where id=?', array(USER_TABLE, $userid) );

	$t->assign("userid", $_SESSION['UserId']);
	$t->assign('picno', $_POST['txtpicno']);


	$body = str_replace( '#UserName#',  $username , $body );

	$body = str_replace( '#PicNo#',  $_POST['txtpicno'] , $body );

	$body = str_replace( '#smallPic#',  $t->fetch('smallPic.tpl') , $body );

	mailSender($From, $To, $email, $Subject, $body);

}

?>