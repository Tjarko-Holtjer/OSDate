<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$email = trim( $_POST['txtemail'] );

if ( $email == '' ) {

	header( 'location: forgotpass.php?errid=1' );
	exit;
}

$sql = 'SELECT id, username, firstname, lastname, password FROM ! WHERE email = ? limit 0,1';

$row = $db->getRow( $sql, array( USER_TABLE, $email ) );

if ( $row ) {

	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';

	$pwd = '';

	for( $i = 0; $i < 8; $i++ ) {

		$rand = rand( 0, strlen( $chars ) );
		$pwd .= $chars{$rand};
	}

	$p = md5( $pwd );

	$sql = 'UPDATE ! SET password = ? WHERE id=?';

	$db->query( $sql, array( USER_TABLE, $p, $row['id'] ) );

	if ($config['forum_installed'] != '' && $config['forum_installed'] != 'None') {

		include_once($config['forum_installed'] . '_forum.php');
		forum_modifympass($conpwd);

	}

	$subject = get_lang('forgot_password_sub');

	$body = get_lang('forgot_password', MAIL_FORMAT);

	$name = $row['firstname'] ;

	$body = str_replace( '#Name#', $name , $body );

	$body = str_replace( '#ID#',  $row['username'] , $body );

	$body = str_replace( '#Password#', $pwd, $body );

	$body = str_replace( '#LoginLink#',  HTTP_METHOD . $_SERVER['SERVER_NAME']  . DOC_ROOT.'login.php' , $body );

	$body = str_replace( '#SiteTitle#',  $config['site_name'] , $body );

	$From    = $config['admin_email'] ;
	$To     = $name . ' <' . $email . '>';


	$success=mailSender($From, $To, $email, $subject, $body);

	if( $success ) {
		header( 'location: forgotpass.php?errid='.PASSWORD_MAIL_SENT );
		exit;
	}
	else {
		header( 'location: forgotpass.php?errid='.MAIL_ERROR );
		exit;
	}
} else {
		header( 'location: forgotpass.php?errid='.NOT_REGISTERED );
		exit;
}
?>