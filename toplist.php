<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$cpage = $_REQUEST['page'];

if( $cpage == '' ) {
	$cpage = 1;
}

$psize = 10;
$minimum_votes = 3;

$sql = 'SELECT u.id, u.username, floor(datediff(NOW(),from_unixtime(u.birth_date))/365)  as age, u.gender, u.lookgender, count(  *  ) vote_count , round(avg( r.rating ),1) avg_rating FROM ! u INNER JOIN ! r ON u.id=r.profileid GROUP BY 1,2,3,4,5 HAVING COUNT(*)>=' . $minimum_votes . ' ORDER BY 7 DESC LIMIT 10';

$rs = $db->query( $sql, array( USER_TABLE, USER_RATING_TABLE ) );

$rcount = $rs->numRows();

if( $rcount > 0 ) {

	$t->assign( 'totalrecs', $rcount );

	$pages = ceil( $rcount / $psize );

	$start = ( $cpage - 1 ) * $psize;

	$t->assign ( 'start', $start );

	if( $pages > 1 ) {

		if ( $cpage > 1 ) {

			$prev = $cpage - 1;

			$t->assign( 'prev', $prev );

		}
		$t->assign ( 'cpage', $cpage );

		$t->assign ( 'pages', $pages );

		if ( $cpage < $pages ) {

			$next = $cpage + 1;

			$t->assign ( 'next', $next );
		}
	}
	$sql .= " limit $start, $psize"	;
}

$data = array();
$number = 1;

while ( $row = $rs->fetchRow() ) {

	$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

	$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

	$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

	$row['username'] = $number . '. ' . $row['username'] . ' (' . $row['avg_rating'] . ' / ' . $row['vote_count'] . ' votes)';

	$data[] = $row;

	$number+=1;
}

hasRight('');

$t->assign ( 'querystring', $querystring );
$t->assign ( 'psize', $psize );

$t->assign ( 'data', $data );
$t->assign ( 'lang', $lang );
$t->assign ( 'rendered_page', $t->fetch('showtoplist.tpl') );

$t->display ( 'index.tpl' );

exit;
?>
