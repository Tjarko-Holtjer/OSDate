<?PHP

include_once('init.php');

$lastactivitytime = time() - ($config['session_timeout'] * 60);

$sql = 'SELECT * FROM ! where lastactivitytime < ?';

$temp = $db->getAll( $sql, array( ONLINE_USERS_TABLE, $lastactivitytime ) );

session_destroy();

if ( sizeof( $temp ) > 0 ) {

	foreach( $temp as $index => $row ) {

		if ( ( time() - $row['lastactivitytime'] ) > (int)( $config['session_timeout'] * 60 ) && $row['session_id'] != '' ) {

			/* First destroy session */
			session_id($row['session_id']);
			session_start();
			session_destroy();

			$db->query( 'DELETE FROM ! WHERE userid = ?', array( ONLINE_USERS_TABLE, $row['userid'] ) );
		}
	}
}

?>