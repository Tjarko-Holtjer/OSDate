<?php
if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );

}

if ($_POST['groupaction'] == get_lang('delete_selected') ) {

	$checked = $_POST['txtcheck'];

	foreach ($checked as $val) {

		$sql = 'DELETE from ! where id = ? ';

		$db->query($sql, array( VIEWS_WINKS_TABLE, $val ) );

	}

	$t->assign('errid',($_REQUEST['act']=='V'?'70':'71') );

}

if ($_REQUEST['id'] != '' && $_REQUEST['remove'] == '1' ) {

	$sql = 'delete from ! where id = ?';

	$db->query($sql, array( VIEWS_WINKS_TABLE, $_REQUEST['id'] ) );

	$t->assign('errid',($_REQUEST['act']=='V'?'70':'71') );
}

$viewswinks_since_days = ($config['last_viewswinks_since']=='')?0:$config['last_viewswinks_since'];


$viewswinks_since = strtotime("-$viewswinks_since_days day",time());

if ($viewswinks_since > $_SESSION['lastvisit']) $viewswinks_since = $_SESSION['lastvisit'];

if ($viewswinks_since < $_SESSION['regdate']) $viewswinks_since = $_SESSION['regdate'];

$viewswinks_cnt = $config['no_last_viewswinks'];

$cnt = $db->getOne('select count(*) from ! where userid = ? and act_time >= ? and act = ?', array(VIEWS_WINKS_TABLE,   $_SESSION['UserId'], $viewswinks_since, $_REQUEST['act']));

if ($cnt < $viewswinks_cnt) {

	$sql = 'select distinct lis.id, lis.userid, lis.ref_userid, lis.act_time, usr.username from ! as lis, ! as usr where lis.userid = ? and lis.ref_userid = usr.id and act = ? order by act_time desc, usr.username asc limit 0,'.$viewswinks_cnt;

	$list = $db->getAll($sql, array( VIEWS_WINKS_TABLE, USER_TABLE, $_SESSION['UserId'], $_REQUEST['act'] ) );


} else {

	$sql = 'select distinct lis.id, lis.userid, lis.ref_userid, lis.act_time, usr.username from ! as lis, ! as usr where lis.userid = ? and lis.ref_userid = usr.id and act = ? and lis.act_time >= ? order by act_time desc, usr.username asc ';

	$list = $db->getAll($sql, array( VIEWS_WINKS_TABLE, USER_TABLE, $_SESSION['UserId'], $_REQUEST['act'],  $viewswinks_since ) );


}

$t->assign('lang', $lang);

$t->assign('viewswinks_since', strftime($lang['DATE_FORMAT'],$viewswinks_since));

$t->assign('list', $list);

$t->assign('act', $_REQUEST['act']);

if ($_REQUEST['act'] == 'V'){
	$t->assign('listname', get_lang('listofviews'));
} else if ($_REQUEST['act'] == 'W') {
	$t->assign('listname', get_lang('listofwinks'));
}

$t->assign('rendered_page', $t->fetch('listviewswinks.tpl') );

$t->display('index.tpl');


?>