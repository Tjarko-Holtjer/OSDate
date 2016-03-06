<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$sql = 'DELETE FROM ! WHERE userid = ?';

$db->query( $sql, array( ONLINE_USERS_TABLE, $_SESSION['UserId'] ) );

$db->disconnect();

session_destroy();
setcookie($config['cookie_prefix']."osdate_info[username]", 'unwanted', strtotime("-1day"), "/" );

unset( $_COOKIE[$config['cookie_prefix'].'osdate_info'] );
unset( $cookie );
unset( $_SESSION );

//header('location: index.php');

header('location: index.php');
exit;
?>