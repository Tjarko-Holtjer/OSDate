<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

if ($_POST['act'] == 'send') {

	$email=strip_tags($_POST['txtemail']);

	if ($email == '') {

		$error = get_lang('errormsgs','19');

	} else {

		$sql = 'SELECT id, username, firstname, lastname, actkey, status, active FROM ! WHERE email = ? limit 0,1';

		$row = $db->getRow( $sql, array( USER_TABLE, $email ) );

		if ($row['status'] == 'active' && $row['active'] == '1') {

			$error = get_lang('resend_conflink_err1');

		} elseif ($row['id'] > 0 ) {
			/* resend the confirmation link email */

			/* Generate new password */
			$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';

			$pwd = '';

			for( $i = 0; $i < 8; $i++ ) {

				$rand = rand( 0, strlen( $chars ) );
				$pwd .= $chars{$rand};
			}

			$db->query('update ! set password=? where id=?', array(USER_TABLE, md5($pwd), $row['id']) );

			$Subject = get_lang('profile_confirmation_email_sub');

			$From = $config['admin_email'];

			$To = $firstname.' '.$lastname.'<'.$email.'>';

			$body = get_lang('profile_confirmation_email', MAIL_FORMAT);

			$body = str_replace( '#FirstName#',  $row['firstname'] , $body );

			$body = str_replace( '#ConfCode#',  $row['actkey'] , $body );

			$body = str_replace('#Welcome#', get_lang('welcome'), $body);

			$body = str_replace( '#ConfirmationLink#',  HTTP_METHOD . $_SERVER['SERVER_NAME'] . DOC_ROOT . 'completereg.php?confcode' , $body );

			$body = str_replace( '#StrID#',  $row['username'] , $body );

			$body = str_replace( '#Email#',  $email , $body );

			$body = str_replace( '#Password#',  $pwd , $body );

			$body = str_replace( '#Upgrade#',  get_lang('upgrade_membership') , $body );

			mailSender($From, $To, $email, $Subject, $body);

			$error = get_lang('resend_conflink_msg');

		} else {

			$error = get_lang('letter_errormsgs','5');
		}
	}
}

$t->assign('error',$error);

$t->assign('lang',$lang);

$t->assign('rendered_page', $t->fetch('resend_conflink.tpl') );

$t->display( 'index.tpl' );
?>