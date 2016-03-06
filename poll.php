<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

$txtquestion = $_POST['txtquestion'];

$txtoptions = array();

if (count($_POST['txtoptions']) > 0) {

	foreach ($_POST['txtoptions'] as $key=>$opt) {

		if ($opt != "") {

			$txtoptions[] = $opt;

		}

	}
}

if ( $_POST['action'] == get_lang('savepoll') ) {
	/* Add the question */

	if ($txtquestion == '') {

		$t->assign('error_msg', get_lang('signup_js_errors','question_noblank'));

	} elseif ( count($txtoptions) < 2) {

		$t->assign('error_msg', get_lang('minimum_options') );

	} else {

		$sql = 'insert into ! (question, date, enabled, suggested_by) values ( ?, ?, ?, ? )';

		$db->query($sql, array( POLLS_TABLE, $txtquestion, time(), '0', $_SESSION['UserId'] ) );

		$pollid = $db->getOne('select id from ! where question = ?', array( POLLS_TABLE, $txtquestion) );

		$sql = 'insert into ! (pollid, opt, enabled) values ( ?, ?, ? )';

		foreach ($txtoptions as $key => $opt) {

			$db->query($sql, array( POLLOPTS_TABLE, $pollid, $opt, '0' ));
		}

		$t->assign('saved', 1);

		$t->assign('error_msg', get_lang('pollsuggested'));

	}
}

$t->assign('txtoptions', $txtoptions);

$t->assign('txtquestion', $txtquestion);

$t->assign('rendered_page', $t->fetch('poll.tpl') );

$t->display( 'index.tpl' );

exit;
?>