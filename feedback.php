<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$cmd = $_POST['cmd'];

if ( $cmd == 'posted' ){

	$txttitle = strip_tags(trim($_POST['txttitle']));
	$txtname = strip_tags(trim($_POST['txtname']));
	$txtemail = strip_tags(trim($_POST['txtemail']));
	$txtcountry = strip_tags(trim($_POST['txtcountry']));
	$txtcomments = strip_tags(trim($_POST['txtcomments']));

	$t->assign('txttitle', $txttitle);
	$t->assign('txtname', $txtname);
	$t->assign('txtemail', $txtemail);
	$t->assign('txtcountry', $txtcountry);
	$t->assign('txtcomments', $txtcomments);

	if (strtolower($_SESSION['spam_code']) != strtolower($_POST['spam_code'])) {
		$t->assign('msg', '121');
	} else {

		$From    = $config['admin_email'];
		$To      = $config['feedback_email'];
		$Subject = get_lang('email_feedback_subject');

		$message = get_lang('feedback_email_to_admin', MAIL_FORMAT);
		$message = str_replace('#txttitle#',$txttitle,$message);
		$message = str_replace('#txtname#', $txtname,$message);
		$message = str_replace('#txtemail#',$txtemail,$message);
		$message = str_replace('#txtcountry#', $lang['countries'][$txtcountry],$message);
		$message = str_replace('#txtcomments#', $txtcomments, $message);

		$success= mailSender($From, $To, $To, $Subject, $message);

		$t->assign( 'success', $success );
	}
} else if ($_SESSION['UserId'] > 0) {
	$t->assign('txtname', $_SESSION['FullName']);
	$t->assign('txtemail', $_SESSION['email']);
}

$t->assign('lang',$lang);

$t->assign('rendered_page', $t->fetch('feedback.tpl') );

$t->display( 'index.tpl' );
exit;
?>