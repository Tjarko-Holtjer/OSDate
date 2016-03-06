<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$usertbl = USER_TABLE;
$onlinetbl = ONLINE_USERS_TABLE;

// get active users from past 60 seconds

$data = $db->getAll( "SELECT
	id,
	username
		FROM $usertbl
			INNER JOIN $onlinetbl ON $usertbl.id = $onlinetbl.userid
				WHERE 	$usertbl.allow_viewonline = ?  AND
						( $usertbl.status = ? or $usertbl.status = ? ) AND
						$usertbl.id <> ? AND
						unix_timestamp() - $onlinetbl.lastactivitytime < 60 ", array( '1', 'active', get_lang('status_enum','active'), $_SESSION['UserId'] ) );

$xml = '<?xml version="1.0"?>';

$xml .= "<users>";

if( strlen($data) > 0 ){

	foreach ( $data as $user ) {
		$xml .= '<user userid="'.$user['id'].'" username="'.$user['username'].'" />';
	}
}

$xml .= "</users>";

print( $xml );

?>