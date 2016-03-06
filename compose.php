<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$returnto = 'compose.php';
include ( 'sessioninc.php' );

// $_POST['txtmessage'] = str_replace(chr(13),'<br>',$_POST['txtmessage']);

if( isset( $_POST['frm'] ) ) {
	if (strtolower($_SESSION['spam_code']) != strtolower($_POST['spam_code'])) {

		$t->assign('errormsg', get_lang('errormsgs',121));

	} else {
		if ( $_POST['frm'] == 'frmTemplate' ) {
			// templated message
			// fetch the template message
			$msgdata = $db->getRow('SELECT subject, text FROM ! WHERE id = ?', array(USERTEMPLATE_TABLE, $_POST['templateid']) );
			$_POST['txtmessage'] = $msgdata['text'];
			$_POST['txtsubject'] = $msgdata['subject'];

			// make appropriate substitutions

			$sql = 'select username, firstname, email, country, state_province, county, city, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age from ! where id = ?';

			$row = $db->getRow( $sql, array( USER_TABLE, $_POST['txtrecipient'] ) );

			// current template variables:
			// [username], [firstname], [city], [state], [country], [age]
			// you can add more template variables by simply adding to this array

			$countryname = $db->getOne('select name from ! where code = ?', array(COUNTRIES_TABLE, $row['country'] ) );

			$statename = $db->getOne('select name from ! where code = ? and countrycode = ?', array(STATES_TABLE, $row['state_province'], $row['country'] ) );

			$row['countryname'] = $countryname;

			$row['statename'] = ($statename != '') ? $statename : $row['state_province'];

			$row['city'] = getCityName($row['country'], $row['state_province'], $row['city'], $row['county']);

			$sub = array(
				'[username]' 	=> $row['username'],
				'[firstname]' 	=> $row['firstname'],
				'[city]'		=> $row['city'],
				'[state]'		=> $row['statename'],
				'[country]'		=> $row['countryname'],
				'[age]'			=> $row['age'],
			);

			foreach( $sub as $key => $val ) {
				$_POST['txtmessage'] = str_replace( $key, $val, $_POST['txtmessage'] );
				$_POST['txtsubject'] = str_replace( $key, $val, $_POST['txtsubject'] );
			}
		}

		$_POST['txtmessage'] = strip_tags($_POST['txtmessage']);

	// this is frm = frmCompose
		$msgs_for_today = 0;

		/* Check the count of messages sent for today... */
		$msgs_for_today = $db->getOne('select act_cnt from ! where userid = ? and act_type = ? and act_date = ?',array(USER_ACTIONS, $_SESSION['UserId'], 'M'
		, date('Ymd')));

		$allowed_count = ($_SESSION['security']['message_keep_cnt'] > 0)? $_SESSION['security']['message_keep_cnt'] : $config['message_count'];

		$total_msgs_count = $db->getOne('select count(*) from ! where owner = ?', array(MAILBOX_TABLE, $_SESSION['UserId']));

		if ($msgs_for_today >= $_SESSION['security']['messages_per_day'] && !isset($_REQUEST['reply'])) {

			$t->assign('errormsg',  get_lang('errormsgs',122));

		} elseif ($allowed_count <= $total_msgs_count) {

			$t->assign('errormsg', get_lang('errormsgs', 131));

		} else {

			if ($msgs_for_today > 0) {
				$db->query('update ! set act_cnt=act_cnt+1 where userid=? and act_type=? and act_date = ?', array(USER_ACTIONS,$_SESSION['UserId'], 'M', date('Ymd')));
			} else {
				$db->query('insert into ! (userid, act_type, act_date, act_cnt) values (?,?,?,?)', array(USER_ACTIONS, $_SESSION['UserId'], 'M', date('Ymd'), 1));
			}
			if ( isset( $_SESSION['UserId'] ) && $_SESSION['UserId'] != '' ) {

				// check if profile should be included //

				if ($_POST["chkinclude"] == "1") {

					// get information //

					$sqlSections = 'SELECT * FROM ! WHERE enabled = ? ORDER BY displayorder';

					$dataSections = $db->getAll( $sqlSections, array( SECTIONS_TABLE, 'Y'  ) );

					$found = false;

					foreach( $dataSections as $section ){

						$prefs = array();

						$sqlpref = 'SELECT DISTINCT q.id, q.question, q.extsearchhead, q.control_type as type FROM ! pref INNER JOIN ! q ON pref.questionid = q.id WHERE pref.userid = ? AND q.section = ? ORDER BY q.id ';

						$rsPref = $db->getAll( $sqlpref,array( USER_PREFERENCE_TABLE, QUESTIONS_TABLE, $_SESSION["UserId"], $section['id'] ) );

						foreach( $rsPref as $row ){

							if ($row['type'] != 'textarea') {

								$sqlopt = 'SELECT pref.answer as answer, opt.answer as anstxt from ! pref left join ! opt on pref.questionid = opt.questionid and opt.id = pref.answer where pref.userid = ? and opt.questionid = ? order by opt.questionid, opt.displayorder';

								$rsOptions = $db->getAll( $sqlopt, array( USER_PREFERENCE_TABLE, OPTIONS_TABLE, $_SESSION["UserId"], $row['id'] ) );

							} else {

								$sqlopt = 'select pref.answer as answer, pref.answer as anstxt from ! pref where pref.userid = ? and pref.questionid = ?';

								$rsOptions = $db->getAll( $sqlopt, array( USER_PREFERENCE_TABLE, $_SESSION["UserId"], $row['id'] ) );
							}

							$opts = array();

							foreach( $rsOptions as $key=>$opt ){
								$opts[] = $opt['anstxt'];
							}

							if (count($opts)>0) {
								$optsPhr = implode( ', ', $opts);
							} else {
								$optsPhr = "";
							}

							$row['options'] = $optsPhr;

							$prefs[] = $row;

							$found = true;
						}

						if( count($prefs) > 0 ){

							$pref[] = array( 'SectionName' => $section['section'], 'preferences' => $prefs );
						}

					}

					// add to message //

					if ( is_array( $pref ) ) {
						foreach ($pref as $item) {

							$_POST['txtmessage'] .= "<br />" . "<br />" . stripslashes( $item['SectionName'] ) . "<br />";
							$_POST['txtmessage'] .= "-----------------";

							foreach ($item['preferences'] as $item2) {

								if (strlen($item2['options']) > 0) {

									$_POST['txtmessage'] .= "<br />" . "<br />" . stripslashes( $item2['extsearchhead'] ). "<br />";
									$_POST['txtmessage'] .= "- " . stripslashes( $item2['options'] );
								}
							}
						}
					}

				}

				$time001 = time();
				$sql = 'INSERT INTO ! (owner, senderid, recipientid, subject, message, sendtime, folder, notifysender) values(?, ?, ?, ?, ?, ?, ?, ?)';

				$db->query( $sql, array( MAILBOX_TABLE, $_POST['txtrecipient'], $_SESSION['UserId'], $_POST['txtrecipient'], stripEmails(strip_tags($_POST['txtsubject'])), stripEmails($_POST['txtmessage']), $time001, 'inbox', ($_POST["chknotify"] - 0) ) );

				/* MOD END */

				$sql = 'INSERT INTO ! (owner, senderid, recipientid, subject, message, sendtime, folder) values(?, ?, ?, ?, ?, ?, ?)';

				$db->query( $sql, array( MAILBOX_TABLE, $_SESSION['UserId'], $_SESSION['UserId'], $_POST['txtrecipient'], stripEmails(strip_tags($_POST['txtsubject'])), stripEmails($_POST['txtmessage']), $time001, 'sent' ) );

				$recipient_choice = $db->getOne('select choice_value from ! where userid=? and choice_name=?', array(USER_CHOICES_TABLE, $_POST['txtrecipient'], 'email_message_received') );

				if ($recipient_choice == '1' or $recipient_choice == '' or !isset($recipient_choice) ) {

					if ($config['letter_messagereceived'] == 'Y' && ($config['nomail_for_onlineuser'] != 'Y' or ($config['nomail_for_onlineuser'] == 'Y' && !getOnlineStats($_POST['txtrecipient']) )) ) {

					/* Send email about the received message to the receiver */

						$sql = 'select *, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age from ! where id = ?';

						$row = $db->getRow( $sql, array( USER_TABLE, $_POST['txtrecipient'] ) );

						$sendername = $db->getOne('select username from ! where id = ?', array(USER_TABLE, $_SESSION['UserId']) );

						$Subject = get_lang('message_received_sub');

						$From= $config['admin_email'];

						$To = $row['email'];

						$t->assign('item', $db->getRow('select *, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age from ! where id = ?', array( USER_TABLE, $_SESSION['UserId']) ) );

						$message = get_lang('message_received', MAIL_FORMAT);

						$message = str_replace('#From#', get_lang('FROM1'), $message);

						$message = str_replace('#TO#', get_lang('To1'), $message);

						$message = str_replace('#FirstName#', $row['firstname'] ,$message);

						$message = str_replace('#SenderName#', $sendername, $message);

						$message = str_replace('#UserName#', $row['username'], $message);

						$message = str_replace('#Date#', get_lang('col_head_date'), $message);

						$message = str_replace('#MESSAGE_DATE#', date(get_lang('DISPLAY_DATETIME_FORMAT'),time()), $message);

						$message = str_replace('#Subject#', get_lang('col_head_subject'), $message);

						$message = str_replace('#MSG_SUBJECT#', stripEmails(strip_tags($_POST['txtsubject'])), $message);

						if (MAIL_FORMAT == 'html') {

							$message = str_replace('#smallProfile#',  $t->fetch('profile_for_html_mail.tpl'), $message);

						}

						mailSender($From, $To, $row['email'], $Subject, $message);

					}
				}

				if ($_REQUEST['reply'] == '2') {

					/* update replied flag */

					$db->query('update ! set replied=? where id=?', array(MAILBOX_TABLE, 1, $_REQUEST['msgid']) );

					header("location: mailmessages.php?folder=".$_REQUEST['folder']."&amp;selflag=".$_REQUEST['selflag']."&amp;sort=".$_REQUEST['sort']."&amp;type=".$_REQUEST['type'],"&amp;replied=1");
					exit;
				}

			}
			$t->assign( 'msg_sent', true );
		}
	}
}

$sql = 'SELECT id, text FROM ! WHERE userid = ?';

$row = $db->getAll( $sql, array( USERTEMPLATE_TABLE, $_SESSION['UserId'] ) );

$t->assign( 'templates', $row );

$sql = 'SELECT username, firstname, lastname FROM ! WHERE id = ?';

$row = $db->getRow( $sql, array( USER_TABLE, $_REQUEST['recipient'] ) );

$t->assign( 'user', $row );



if ($_REQUEST['reply'] == '1') {
	/* Reply for a message */

	$msg = $db->getRow('select * from ! where id = ?', array(MAILBOX_TABLE, $_REQUEST['msgid'] ) );

	if (substr($msg['subject'],0,3) != 'Re:') {
		$msg['subject'] = 'Re: '.$msg['subject'];
	}

	if (strpos($msg['message'],'-- Original Message ---') >= 0 ) {
		$msg['message'] = str_replace('-- Original Message ---','-----------------',$msg['message']);
	}

	$msg['message'] = chr(13).chr(13).chr(13).'-- Original Message ---'.chr(13).str_replace('<br>',chr(13),$msg['message']).chr(13).'-- End Original Message ---'.chr(13);


	$t->assign('msg', $msg);

} elseif ($_REQUEST['reply'] == '11') {
	/* Reply  "No Thanks" */

	$msg['subject'] = get_lang('no_thanks_subject');

	$message =  get_lang('no_thanks_message', MAIL_FORMAT);

	$message = str_replace('#site_name#', $config['site_name'], $message);

	$message = str_replace('#recipient_username#', $_REQUEST['refuname'], $message);

	$message = str_replace('#sender_username#', $_SESSION['UserName'], $message);

	$msg['message'] = str_replace('<br>',chr(10),$message);

	$t->assign('msg', $msg);

	$_REQUEST['level'] = '1';

}

$t->assign('lang',$lang);

if ( $config['use_profilepopups'] == 'Y' ) {

	$t->display('compose.tpl');
} else {
	$t->assign('rendered_page', $t->fetch('compose.tpl') );

	$t->display ( 'index.tpl' );
}
?>