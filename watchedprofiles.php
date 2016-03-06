<?php
if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );

}

if ($_REQUEST['groupaction'] == get_lang('delete_selected') ) {

	$checked = $_POST['txtcheck'];

	if (count($checked) > 0) {
		foreach ($checked as $val) {

			$sql = 'DELETE from ! where id = ?';

			$db->query($sql, array( USER_WATCHED_PROFILES, $val ) );
		}

		$t->assign('errid', REMOVEDFROMLIST);
	}
}

if ($_GET['act'] == 'remove') {
	/* Remove from the list */

	$sql = 'DELETE from ! where id = ? ';

	$db->query($sql, array( USER_WATCHED_PROFILES, $_GET['id'] ) );

	$t->assign('errid', REMOVEDFROMLIST);

}

$t->assign('lang',$lang);

if ($_GET['act'] == 'save' ) {

	/* first get the total number of saved profiles and see if the count exhaused */

	$cnt = $db->getOne('select count(*) from ! where userid = ?', array(USER_WATCHED_PROFILES, $_SESSION['UserId']) );

	if ($_GET['ref_id'] != '' && $cnt < $_SESSION['security']['saveprofilescnt']) {

		$alrdy = $db->getOne('select count(*) from ! where userid = ? and ref_userid = ?', array(USER_WATCHED_PROFILES, $_SESSION['UserId'],$_GET['ref_id'] ) );
		if ($alrdy <= 0) {
			$sql = 'insert into ! (userid, ref_userid) values (?, ?)';

			$db->query($sql, array(USER_WATCHED_PROFILES, $_SESSION['UserId'], $_GET['ref_id']) );

			$errid=202;
		} else {
			$errid = 203;
		}

	} else {

		$errid=201;
	}

	header("location: ".$_GET['rtnurl']."?id=".$_GET['ref_id']."&errid=".$errid);
	exit;

}

/* Show the watched profiles list */
$sql = 'select lis.ref_userid, usr.id as userid, lis.id as lisid, usr.username as ref_username from ! as lis, ! as usr where lis.userid = ? and lis.ref_userid = usr.id order by usr.username ';

$list = $db->getAll($sql, array( USER_WATCHED_PROFILES, USER_TABLE, $_SESSION['UserId'] ) );

$t->assign('list', $list);


$t->assign('rendered_page', $t->fetch('watchedprofiles.tpl') );

$t->display('index.tpl');


?>