<?php
if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );

}

$show=($_GET['show'])?$_GET['show']:0;

$username = $_SESSION['UserName'];

if ($_POST['groupaction'] == get_lang('delete_selected') ) {

	$checked = $_POST['txtcheck'];

	$act = $_POST['act'];

	if (count($checked) > 0) {
		foreach ($checked as $val) {

			$sql = 'DELETE from ! where id = ?';

			$db->query($sql, array( BUDDY_BAN_TABLE, $val ) );
		}

		$t->assign('errid', REMOVEDFROMLIST);
	}
	$show = 1;
}

if ($_GET['remove'] == '1') {
	/* Remove from the list */

	$sql = 'DELETE from ! where id = ? ';

	$db->query($sql, array( BUDDY_BAN_TABLE, $_GET['id'] ) );

	$t->assign('errid', REMOVEDFROMLIST);

	$show = 1;
}

if ($show != "1" ) {

	/* first get both the username and ref_username i.e.  login names */

	$sql = 'SELECT *, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age	FROM ! WHERE id in ( ?, ?) AND status <> ?';

	$user = $db->getAll($sql, array( USER_TABLE, $_SESSION['UserId'], $_GET['ref_id'], get_lang('status_enum','suspended') ) );

	foreach ($user as $key => $usr) {

		if ($usr['id'] != $_GET['ref_id'] ) {

			$item = $usr;

			$username = $usr['username'];

			$userid = $usr['id'];

		} else {

			$ref_username = $usr['username'];

			$ref_userid = $usr['id'];

			$ref_userfirstname = $usr['firstname'];

			$ref_userfullname = $usr['firstname']. ' '.$usr['lastname'];

			$ref_useremail = $usr['email'];
		}
	}

	$errid = '';

	if ($_GET['act'] == 'buddy') {
		$list='F';
		$errid = ADDEDTOBUDDYLIST;
		$listname = get_lang('buddylisthdr');
		$choice_value='email_buddy_list';
	} elseif ( $_GET['act'] == 'hot' ) {
		$list='H';
		$errid = ADDEDTOHOTLIST;
		$listname = get_lang('hotlisthdr');
		$choice_value='email_hot_list';
	} else {
		$list='B';
		$errid = ADDEDTOBANLIST;
		$listname = get_lang('banlisthdr');
		$choice_value='email_ban_list';
	}

	$sql = 'select id from ! where username = ? and ref_username = ? and act = ?';

	/* Check if this user is in banned list of the other user */
	if ($list == 'F' or $list == 'H') {
		$r1 = $db->getOne($sql, array(BUDDY_BAN_TABLE,$ref_username, $username, 'B') );

		if (isset($r1) and $r1 != '') {
			/* In the ban list. Just return back giving error. */
			header("location: ".$_GET['rtnurl']."?id=".$_GET['ref_id']."&errid=105");
			exit;
		}
	}

	/* Check if the ref_username is already in the list or not.. */
	$r=$db->getOne($sql, array( BUDDY_BAN_TABLE, $username, $ref_username, $list ) );
	if ($r > 0) {
	/* Already in the list, just ignore */
	} else {

		$ins_sql = 'insert into ! ( username, act, ref_username, act_date ) values ( ?, ?, ?, ? )';

		$db->query($ins_sql, array( BUDDY_BAN_TABLE, $username, $list, $ref_username, time() ) );

		$recipient_choice = $db->getOne('select choice_value from ! where userid=? and choice_name=?', array(USER_CHOICES_TABLE, $ref_userid, $choice_value) );

		if ($recipient_choice == '1' or $recipient_choice == '' or !isset($recipient_choice) ) {

			if (( $list == 'F' and $config['letter_buddylist'] == 'Y') or ( $list == 'B' and $config['letter_banlist'] == 'Y') or ( $list == 'H' and $config['letter_hotlist'] == 'Y') ) {
			/* Send message to the user who is being added */

				$message = get_lang('added_list',MAIL_FORMAT);

				$Subject = str_replace('#SenderName#',$username,str_replace('#ListName#',$listname,get_lang('added_list_sub')));

				$From = $config['admin_email'];

				$To = $ref_useremail;

				$message = str_replace('#FirstName#', $ref_userfirstname ,$message);

				$message = str_replace('#SenderName#', $username, $message);

				$message = str_replace('#ListName#', $listname, $message);

				if (MAIL_FORMAT == 'html') {

					$t->assign('item', $item);

					$message = str_replace('#smallProfile#',  $t->fetch('profile_for_html_mail.tpl'), $message);

				}

				mailSender($From, $To, $ref_useremail, $Subject, $message);

			}
		}
		/* Now delete if this username is in the opposite list.
			i.e. if we are adding buddy, then we should remove from
			ban list, if available. Otherwise, vice versa  */

		if ($list == 'F' or $list == 'B') {

			if ($list == 'F') { $list = 'B'; }
			elseif ($list == 'B') { $list = 'F'; }

			$sql = 'select id from ! where username = ? and ref_username = ? and act = ?';

			$xr=$db->getOne($sql, array( BUDDY_BAN_TABLE, $username, $ref_username, $list ) );

			if ($xr > 0) {
				/* Remove from the list */

				$db->query('delete from ! where id = ?', array( BUDDY_BAN_TABLE, $xr ) );
			}
		}

	}

	header("location: ".$_GET['rtnurl']."?id=".$_GET['ref_id']."&errid=".$errid);
	exit;

} else {
	/* Show the list  */

	if ($_REQUEST['act'] == 'F') {
		$listname = get_lang('buddylisthdr');
	} elseif ( $_GET['act'] == 'H' ) {
		$listname = get_lang('hotlisthdr');
	} else {
		$listname = get_lang('banlisthdr');
	}


	$sql = 'select lis.ref_username, lis.act_date, usr.id as userid, lis.id as lisid from ! as lis, ! as usr where lis.username = ? and lis.act = ? and lis.ref_username = usr.username order by lis.ref_username ';

	$list = $db->getAll($sql, array( BUDDY_BAN_TABLE, USER_TABLE, $_SESSION['UserName'], $_REQUEST['act'] ) );

	$t->assign('list', $list);

	$t->assign("listcount", count($list));

	$t->assign("listname", $listname);

	$t->assign('act', $_REQUEST['act'] );

	$t->assign('lang', $lang);

	$t->assign('rendered_page', $t->fetch('buddybanlist.tpl') );

	$t->display('index.tpl');

}
?>