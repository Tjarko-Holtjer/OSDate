<?php
ob_start();
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

//  include ( 'sessioninc.php' );

if( (int)$_GET['id'] <= 0 ) {

	$userid = $_SESSION['UserId'];

} else {

	$userid = $_GET['id'];

}

if (!isset($_GET['picid']) ) {

	$piccnt = $db->getAll('select picno from ! where userid = ? and ( album_id is null or album_id = ?)',array(USER_SNAP_TABLE, $userid,'0' ) );

	$picid = $piccnt[array_rand($piccnt)]['picno'];

	$album_id = '';

} else {

	$picid = $_GET['picid'];

}

$typ = ( $_GET['typ'] ) ? $_GET['typ'] : 'pic' ;

$sql = 'select gender from ! where id = ?';

$gender = $db->getOne( $sql, array( USER_TABLE, $userid ) ) ;

$cond = '';
if ( $config['snaps_require_approval'] == 'Y' && $userid != $_SESSION['UserId'] ) {

	$cond = " and active = 'Y' ";
}


if ($typ == 'tn') {

	$sql = 'select tnpicture as picture, active, tnext as ext, album_id  from ! where userid = ? and picno = ? '.$cond;

} else {

	$sql = 'select picture, active, picext as ext, album_id from ! where userid = ? and picno = ? '.$cond;

}

$row = $db->getRow ( $sql, array( USER_SNAP_TABLE, $userid, $picid ) );

if (substr_count($row['picture'],'file:') > 0) {
	/* The picture is in file system */
	$img = file_get_contents(ltrim(rtrim(str_replace('file:',USER_IMAGE_DIR,$row['picture']) ) ) );

} else {

	$img = base64_decode ( $row['picture']  );

}

if ( $row['picture'] != '' && ( ( hasRight('seepictureprofile') && ( $config['snaps_require_approval'] == 'Y' && $row['active'] == 'Y'  ) ||$config['snaps_require_approval'] == 'N' ) || $userid == $_SESSION['UserId']  ) ) {


	$img = imagecreatefromstring($img);

	$w = imagesx( $img );

	$h = imagesy( $img );

	$wdth = ($_GET['width']!='')?$_GET['width']:$w;

	$hght = ($_GET['height']!='')?$_GET['height']:$h;

	if ($hght > $config['disp_snap_height']) $hght = $config['disp_snap_height'];
	if ($wdth > $config['disp_snap_width']) $wdth = $config['disp_snap_width'];

	if ($typ == 'pic' and ( $wdth < $w or $hght < $h) ) {

		if( $w > $h ) {
			$ratio = $w / $h;
			$nw = $wdth;
			$nh = $nw / $ratio;

		} else {
			$ratio = $h / $w;
			$nh = $hght;
			$nw = $nh /$ratio;
		}

		$img2 = imagecreatetruecolor( $nw, $nh );

		imagecopyresampled ( $img2, $img, 0, 0, 0 , 0, $nw, $nh, $w, $h );
		$image_height=$nh;
		$image_width=$nw;

	} else {

		if ($wdth > $w) $wdth = $w;
		if ($hght > $h) $hght = $h;
		$img2 = imagecreatetruecolor( $wdth, $hght );

		imagecopyresampled ( $img2, $img, 0, 0, 0 , 0, $wdth, $hght, $w, $h );
		$image_height=$hght;
		$image_width=$wdth;
	}

	imagedestroy($img);

	if ( $config['watermark_snaps'] != ''  ){

	/* Watermark the picture  */

		Define('WATERMARK_TEXT_FONT', '1'); // font 1 / 2 / 3 / 4 / 5
		Define('TEXT_SHADOW', $config['watermark_text_shadow']); // 1 - yes / 0 - no
		Define('TEXT_COLOR', $config['watermark_text_color']); // text color
		Define('WATERMARK_ALIGN_H', $config['watermark_position_h']); // left / right / center
		Define('WATERMARK_ALIGN_V', $config['watermark_position_v']); // top / bottom / center
		Define('WATERMARK_MARGIN', $config['watermark_margin']); // margin
		Define('WATERMARK_TEXT', $config['watermark_snaps']); // margin

		$color = eregi_replace("#","", TEXT_COLOR);
		$red = hexdec(substr($color,0,2));
		$green = hexdec(substr($color,2,2));
		$blue = hexdec(substr($color,4,2));

		$text_color = imagecolorallocate ($img2, $red, $green, $blue);

		$text_height=imagefontheight(WATERMARK_TEXT_FONT);
		$text_width=strlen(WATERMARK_TEXT)*imagefontwidth(WATERMARK_TEXT_FONT);
		$wt_y=WATERMARK_MARGIN;
		if (WATERMARK_ALIGN_V=='top') {
			$wt_y=WATERMARK_MARGIN;
		} elseif (WATERMARK_ALIGN_V=='bottom') {
			$wt_y=$image_height-$text_height-WATERMARK_MARGIN;
		} elseif (WATERMARK_ALIGN_V=='center') {
			$wt_y=(int)($image_height/2-$text_height/2);
		}

		$wt_x=WATERMARK_MARGIN;
		if (WATERMARK_ALIGN_H=='left') {
			$wt_x=WATERMARK_MARGIN;
		} elseif (WATERMARK_ALIGN_H=='right') {
			$wt_x=$image_width-$text_width-WATERMARK_MARGIN;
		} elseif (WATERMARK_ALIGN_H=='center') {
			$wt_x=(int)($image_width/2-$text_width/2);
		}

		if (TEXT_SHADOW=='1') {
			imagestring($img2, WATERMARK_TEXT_FONT, $wt_x+1, $wt_y+1, WATERMARK_TEXT, 0);
		}
		imagestring($img2, WATERMARK_TEXT_FONT, $wt_x, $wt_y, WATERMARK_TEXT, $text_color);

	} elseif ($config['watermark_image'] != '') {
		/* Watermarking with image  */
		Define('TEXT_SHADOW', $config['watermark_text_shadow']); // 1 - yes / 0 - no
		Define('TEXT_COLOR', $config['watermark_text_color']); // text color
		Define('WATERMARK_ALIGN_H', $config['watermark_position_h']); // left / right / center
		Define('WATERMARK_ALIGN_V', $config['watermark_position_v']); // top / bottom / center
		Define('WATERMARK_MARGIN', $config['watermark_margin']); // margin

		$wt_file= '.'.$config['watermark_image'];

		$lst2=getimagesize($wt_file);
		$image2_width=$lst2[0];
		$image2_height=$lst2[1];
		$image2_format=$lst2[2];

		if ($image2_format==2) {
		$wt_image=imagecreatefromjpeg($wt_file);
		} elseif ($image2_format==1) {
		$wt_image=imagecreatefromgif($wt_file);
		} elseif ($image2_format==3) {
		$wt_image=imagecreatefrompng($wt_file);
		}

		if ($wt_image) {

			$wt_y=WATERMARK_MARGIN;
			if (WATERMARK_ALIGN_V=='top') {
				$wt_y=WATERMARK_MARGIN;
			} elseif (WATERMARK_ALIGN_V=='bottom') {
				$wt_y=$image_height-$image2_height-WATERMARK_MARGIN;
			} elseif (WATERMARK_ALIGN_V=='center') {
				$wt_y=(int)($image_height/2-$image2_height/2);
			}

			$wt_x=WATERMARK_MARGIN;
			if (WATERMARK_ALIGN_H=='left') {
				$wt_x=WATERMARK_MARGIN;
			} elseif (WATERMARK_ALIGN_H=='right') {
				$wt_x=$image_width-$image2_width-WATERMARK_MARGIN;
			} elseif (WATERMARK_ALIGN_H=='center') {
				$wt_x=(int)($image_width/2-$image2_width/2);
			}

			imagecopymerge($img2, $wt_image, $wt_x, $wt_y, 0, 0, $image2_width, $image2_height, $config['watermark_image_intensity']);
		}

	}

} else {

	if ($gender == 'M') {
		$nopic = SKIN_IMAGES_DIR.'male.jpg';
	} elseif ($gender == 'F') {
		$nopic = SKIN_IMAGES_DIR.'female.jpg';
	} elseif ($gender == 'C') {
		$nopic = SKIN_IMAGES_DIR.'couple.jpg';
	}

	$img2 = imagecreatefromjpeg($nopic);
}
 ob_end_clean();
 header("Pragma: public");
 header("Content-Type: image/jpg");
 header("Content-Transfer-Encoding: binary");
 header("Cache-Control: must-revalidate");

 $ExpStr = "Expires: " . gmdate("D, d M Y H:i:s", time() - 30) . " GMT";

 header($ExpStr);
 header("Content-Disposition: attachment; filename=profile_".$userid."_".$typ.".jpg");

/*
 if ($_SESSION['browser'] != 'MSIE') {

	header("Content-Disposition: inline" );
 }
*/
imagejpeg($img2);
imagedestroy($img2);
?>