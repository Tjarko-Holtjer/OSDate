<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

// Get user's data (timezone)
$user=$db->getRow("select * from ! where id=?",array(USER_TABLE, $_SESSION["UserId"]));
$t->assign("user",$user);
$lang['tz'] = get_lang_values('tz');

if ( $_GET['edit'] ) {
	$sqledit = "SELECT id, ".
			   "       userid, ".
			   "       event, ".
			   "       description, ".
			   "       calendarid, ".
			   "       enabled, ".
			   "       timezone, ".
			   "       DATE_ADD(datetime_from, INTERVAL ! HOUR) as datetime_from, ".
			   "       DATE_ADD(datetime_to, INTERVAL ! HOUR) as datetime_to, ".
			   "       recurring, ".
			   "       recuroption, ".
			   "       private_to ".
			   "from ! Where id = ?";
	$data = $db->getRow( $sqledit, array( $user["timezone"], $user["timezone"], EVENTS_TABLE, $_GET['edit'] ) );
	$t->assign( 'lang', $lang );
	$t->assign( 'error', get_lang('admin_error_msgs', $_GET['errid'] ) );
	$t->assign( 'data', $data );
	$t->assign('rendered_page', $t->fetch('eventedit.tpl'));
	$t->display( 'index.tpl' );
	exit;
}

if ( $_GET['insert'] ) {
	$t->assign( 'lang', $lang );
	if (isset($_GET['timestamp'])) {
		$t->assign('timestamp', date('Y-m-d',$_GET['timestamp']));
	} else {
		$t->assign('timestamp', date('Y-m-d',time()));
	}
	$t->assign( 'error', get_lang('admin_error_msgs', $_GET['errid'] ) );
	$t->assign('rendered_page', $t->fetch('eventins.tpl'));
	$t->display( 'index.tpl' );
	exit;
}

if ( $_GET['delete'] ){
	$id = $_GET['delete'];
	// Deleting watches for event
	$sqldel = 'DELETE FROM ! WHERE eventid = ?';
	$result = $db->query( $sqldel, array( WATCHES_TABLE, $id ) );
	// Deleting event
	$sqldel = 'DELETE FROM ! WHERE id = ? ';
	$result = $db->query( $sqldel, array( EVENTS_TABLE, $id) );
}

// Get event data
$query="select id, userid, event, description, ".
	   "       date_add(datetime_from, interval ! hour) as datetime_from, ".
	   "       date_add(datetime_to, interval ! hour) as datetime_to, ".
	   "       calendarid, timezone, private_to, ".
	   "       enabled, ".
	   "       recurring, ".
	   "       recuroption ".
	   "from ! ".
	   "where id=? ";
$event=$db->getRow($query,array($user["timezone"], $user["timezone"], EVENTS_TABLE,$_REQUEST["event_id"]));

if(!$event)
	$t->assign("error",1);
else
{	$sql="select count(*) from ! where userid=? and eventid=? ";
	$event["watched"]=$db->getOne($sql,array(WATCHES_TABLE, $_SESSION["UserId"], $event["id"]));
	$event["username"]=$db->getOne("select username from ! where id=?",array(USER_TABLE, $event["userid"]));
	$event['datetime_from'] = strtotime($event['datetime_from']);
	$event['datetime_to'] = strtotime($event['datetime_to']);

	if ($event['username'] == '') {
		/* Admin User */
		$event['username'] = $db->getOne("select username from ! where id=?",array(ADMIN_TABLE, $event["userid"]));
	}
}

$t->assign('lang',$lang);

$t->assign("event",$event);
$t->assign('rendered_page', $t->fetch('eventview.tpl') );
$t->display ( 'index.tpl' );

exit;
?>