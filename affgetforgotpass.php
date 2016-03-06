<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$email = trim( $_POST['forgot_pass_email'] );

if ( $email == '' ) {
	header( 'location: afflogin.php?errormsg='.urlencode(get_lang('letter_errormsgs','1')) );
	exit;

}

$row = $db->getRow( 'SELECT name, email, password FROM ! WHERE email = ? limit 0,1' ,array( AFFILIATE_TABLE, $email ));

if ( $row ) {

	//Generate Password
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890abcdefghijklmnopqrstuvwxyz';

	$pwd = '';

	for( $i=0; $i<8; $i++ ) {

		$rand = rand(0,strlen($chars));

		$pwd .= $chars{$rand};
	}

	$p = md5( $pwd );

	$sql = 'UPDATE ! SET password = ? WHERE email = ?';

	$db->query( $sql, array(AFFILIATE_TABLE, $p, $email ));

	$subject = get_lang('aff_newpwd_sub');

	$body = get_lang('aff_newpwd',MAIL_FORMAT);

	$name = $row['name'];

	$body = str_replace( '#Name#', $name , $body );

	$body = str_replace( '#Password#', $pwd, $body );

	mailSender($config['admin_email'], $email, $email, $subject, $body);

	header( 'location: afflogin.php?errormsg='.urlencode(get_lang('letter_errormsgs',ALL_OK)) );

	exit;

} else {

	header( 'location: afflogin.php?errormsg='.urlencode(get_lang('letter_errormsgs',NOT_REGISTERED )) );
	exit;

}
?>