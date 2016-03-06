<?php
if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );

}

$returnto = 'sendwinks.php';

include('sessioninc.php');

$winks_for_today = 0;

/* Check the count of messages sent for today... */
$winks_for_today = $db->getOne('select act_cnt from ! where userid = ? and act_type = ? and act_date = ?',array(USER_ACTIONS, $_SESSION['UserId'], 'W', date('Ymd')));

if ($winks_for_today >= $_SESSION['security']['winks_per_day'] ) {

	header("location: ".$_GET['rtnurl']."?id=".$_GET['ref_id'].'&errid=123');

	exit;

}

if ($winks_for_today> 0) {
	$db->query('update ! set act_cnt=act_cnt+1 where userid=? and act_type=? and act_date = ?', array(USER_ACTIONS,$_SESSION['UserId'], 'W', date('Ymd')));
} else {
	$db->query('insert into ! (userid, act_type, act_date, act_cnt) values (?,?,?,?)', array(USER_ACTIONS, $_SESSION['UserId'], 'W', date('Ymd'), 1));
}

$sql = 'insert into ! (userid, ref_userid, act, act_time) values (?, ?, ?, ?)';

$db->query($sql, array( VIEWS_WINKS_TABLE, $_GET['ref_id'], $_SESSION['UserId'], 'W', time() ) );

$recipient_choice = $db->getOne('select choice_value from ! where userid=? and choice_name=?', array(USER_CHOICES_TABLE, $_GET['ref_id'], 'email_wink_received') );

if ($recipient_choice == '1' or $recipient_choice == '' or !isset($recipient_choice) ) {

	if ($config['letter_winkreceived'] == 'Y'  ) {
		/* Now intimate the user about this  */

		$usr = $db->getRow('select *, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age from ! where id = ?', array(USER_TABLE, $_GET['ref_id']) );

		$t->assign('item', $db->getRow('select *, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age from ! where id = ?', array(USER_TABLE, $_SESSION['UserId']) ) );

		$message = get_lang('wink_received', MAIL_FORMAT);

		$Subject = str_replace('#ReceiverName#', $usr['username'],str_replace('#SenderName#',$_SESSION['UserName'], get_lang('letter_winkreceived_sub') ) );

		$From = $config['admin_email'];

		$To = $usr['email'];

		$message = str_replace('#FirstName#', $usr['firstname'], $message);

		$message = str_replace('#ReceiverName#', $usr['username'], $message);

		$message = str_replace('#SenderName#', $_SESSION['UserName'], $message);

		$message = str_replace('#UserId#', $_SESSION['UserId'], $message);

		if (MAIL_FORMAT == 'html') {
			$message = str_replace('#smallProfile#', $t->fetch('profile_for_html_mail.tpl'), $message);

		}

		$success = mailSender($From, $To, $To, $Subject, $message);

	}
}
header("location: ".$_GET['rtnurl']."?id=".$_GET['ref_id'].'&errid='.WINKISSENT);
exit;

?>