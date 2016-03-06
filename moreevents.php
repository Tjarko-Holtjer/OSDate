<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include ( 'sessioninc.php' );

// Get user's data (timezone)
$user=$db->getRow("select * from ! where id=?",array(USER_TABLE, $_SESSION["UserId"]));
$t->assign("user",$user);

if(empty($_REQUEST["calendarid"]))
{	// Finding first calendar
	$query="select id from ! order by displayorder limit 1";
	$calendarid=$db->getOne($query,array(CALENDARS_TABLE));
} else {
	$calendarid=$_REQUEST["calendarid"];
}

$t->assign("calendarid",$calendarid);

	$date_timestamp=$_REQUEST["timestamp"];
	$date_array=getdate_safe($date_timestamp);
	$date=$date_array["year"]."-".$date_array["mon"]."-".$date_array["mday"];

	$item=array();
	$item["timestamp"]=$date_timestamp;
	$item["date"]=$date_array;
	$item["cur_date"]=$date;
	$item["events"]=array();

	// selecting all events for that date
	$query="select id, userid, event, description, ".
		   "       date_add(datetime_from, interval ! hour) as datetime_from, ".
		   "       date_add(datetime_to, interval ! hour) as datetime_to, ".
		   "       calendarid, timezone, private_to ".
	       "from !  ".
		   "where 1 ".
		   "  and to_days(date_add(datetime_from,interval ! hour))<=to_days(?) ".
		   "  and to_days(date_add(datetime_to,interval ! hour))>=to_days(?) ".
		   "  and enabled='Y' ".
		   "  and calendarid=? ".
		   "order by datetime_from ";
	$rs=$db->query($query,array($user["timezone"], $user["timezone"], EVENTS_TABLE,$user["timezone"],$date,$user["timezone"],$date,$calendarid));
	while(($event=$rs->fetchRow()))
	{	// Check for private event here
		$add_event=true;
		$event['username'] = $db->getOne('select username from ! where id = ?', array(USER_TABLE, $event['userid']) );
		if($event["private_to"]!="")
		{	$add_event=false;
			$private_to=explode(",",$event["private_to"]);
			$private_to=array_map("trim",$private_to);
			if(in_array($user["username"],$private_to))
			{	$add_event=true;
			}
		}
		if($add_event)
		{	// Checking for watch events
			$sql="select count(*) from ! where userid=? and eventid=? ";
			$event["watched"]=$db->getOne($sql,array(WATCHES_TABLE, $_SESSION["UserId"], $event["id"]));
			$item["events"][]=$event;
		}

	}
if (count($item["events"]) <= 0) {
	$t->assign('error','1');
}
$t->assign("date",$date);
$t->assign("events",$item["events"]);
$t->assign('rendered_page', $t->fetch('moreevents.tpl') );
$t->display ( 'index.tpl' );

exit;
?>