<?php
if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );

}

include ('sessioninc.php');

$act = $_REQUEST['act'];

/* Select the choices from language file */
$choices = get_lang_values('user_choices');

$email_match_mail_days = strip_tags($_POST['email_match_mail_days']);

if ($email_match_mail_days == '') $email_match_mail_days=0;

if ($_POST) {
	if ($act == 'modify') {

		$sql = 'update ! set choice_value = ? where userid = ? and choice_name=?';

		/* Process each option to update */
		foreach ($choices as $key => $val) {
			if ($key == 'email_match_mail_days') {
				$db->query($sql,array( USER_CHOICES_TABLE, $email_match_mail_days, $_SESSION['UserId'], $key) );
			} else {
				$db->query($sql,array( USER_CHOICES_TABLE, $_POST[$key], $_SESSION['UserId'],$key) );
			}
		}
	} else {
		/* Add new options */
		$sql = 'insert into ! (userid, choice_name, choice_value) values (?, ?, ?)';
		/* Process each option to update */
		foreach ($choices as $key => $val) {
			if ($key == 'email_match_mail_days') {
				$db->query($sql,array( USER_CHOICES_TABLE, $_SESSION['UserId'], $key, $email_match_mail_days) );
			} else {
				$db->query($sql,array( USER_CHOICES_TABLE, $_SESSION['UserId'], $key, $_POST[$key]) );
			}
		}

	}

	$t->assign('error', get_lang('mysettings_updated'));

}

$user_choices = array();

$t->assign('act','add');

$recs = $db->getAll('select * from ! where userid = ?',  array(USER_CHOICES_TABLE, $_SESSION['UserId']));

if (count($recs) > 0) {

	$t->assign('act','modify');

	foreach ($recs as $rec) {

		$user_choices[$rec['choice_name']] = $rec['choice_value'];
	}
}

$_SESSION['mysettings'] = $user_choices;

$t->assign('user_choices', $user_choices);

$lang['user_choices'] = $choices;

$t->assign('lang', $lang);

$t->assign('rendered_page', $t->fetch('mysettings.tpl') );

$t->display('index.tpl');


?>