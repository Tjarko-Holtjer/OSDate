<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$sname = trim( $_POST['txtsendername'] );
$semail = trim( $_POST['txtsenderemail'] );
$femail = trim( $_POST['txtrcpntemail'] );

if (strtolower($_SESSION['spam_code']) != strtolower($_POST['spam_code'])) {
	$t->assign('msg', get_lang('errormsgs',121));
	$t->assign('txtsendername', $sname);
	$t->assign('txtsenderemail', $semail);
	$t->assign('txtrcpntemail', $femail);
	$t->assign('rendered_page', $t->fetch('tellafriend.tpl') );
	$t->display ( 'index.tpl' );
	exit;
}
// fix this file later
/*
if ( $sname || $semail || $femail ) {

	echo $lang['taf_errormsgs'][1] ;
	exit;
}
*/

$subject = str_replace('#FromName#',$sname, get_lang('invite_a_friend_sub'));

$body = get_lang('invite_a_friend', MAIL_FORMAT);

$body = str_replace( '#FromName#',  $sname , $body );

$From    = $sname . ' <'. $semail . '>';
$To = $femail;

$success = mailSender($From, $To, $femail, $subject, $body);

if( $success ) {
	$t->assign('msg', get_lang('taf_errormsgs',0) );
} else {
	$t->assign('msg', $lang('taf_errormsgs',3) );
}

$t->assign('rendered_page', $t->fetch('tellafriend.tpl') );
$t->display ( 'index.tpl' );
?>