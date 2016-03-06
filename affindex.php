<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

if( isset( $_POST['frm'] ) && $_POST['frm'] == 'frmAffSignup' ) {

	$name = strip_tags(trim( $_POST[ 'txtname' ] ) );
	$password = strip_tags(trim( $_POST[ 'txtconpassword' ] ));
	$email = strip_tags(trim( $_POST[ 'txtemail' ] ));
	$t->assign('txtname', $name);
	$t->assign('txtemail',$email);
	// change later

	if( !$name ) {

		header( 'location: affindex.php?error=' . MANDATORY_FIELDS );
		exit;

	} elseif ( !$password ) {

		header( 'location: affindex.php?error=' . MANDATORY_FIELDS );
		exit;

	} elseif( !$email ) {

		header( 'location: affindex.php?error=' . MANDATORY_FIELDS );
		exit;

	} elseif( !$password ) {

		header( 'location: affindex.php?error=' . MANDATORY_FIELDS );
		exit;

	} elseif( trim( $_POST['txtpassword'] ) != $password ){

		header( 'location: affindex.php?error=' . PASS_CONFIRMPASS);
		exit;

	}

	$sql = 'SELECT count(*) as aacount from ! where email = ?';

	$rowc = $db->getRow( $sql, array( AFFILIATE_TABLE, $email ) );

	if ( $rowc['aacount'] > 0 ) {

		header( 'location: affindex.php?error='.EMAIL_EXISTS);
		exit;
	}

	$status = get_lang('status_enum','approval');

	$regdate = time();

	$password = md5($password);

	// the affiliate confirmation code - finish this for osdate 1.1.0 release
	$code = md5( microtime() );

	$sqlins = 'INSERT INTO ! ( name, email, password, status, regdate ) VALUES ( ?, ?, ?, ?, ? )';

	$result = $db->query ( $sqlins, array( AFFILIATE_TABLE, $name, $email, $password, $status, $regdate ) );

	$lastid = $db->getOne('select id from ! where name = ? and email = ?',array(AFFILIATE_TABLE, $name, $email));

	// send the affiliate email...

	$t->assign('lang',$lang);
	$t->assign( 'affid', $lastid );

	$t->assign('rendered_page', $t->fetch('affsignupsuccess.tpl') );

	$t->display( 'index.tpl' );

	exit;
}

if( $_GET['error'] ) {

	$t->assign( 'error', get_lang('affiliates_error',$_GET['error']) );

} else {

	$t->assign( 'error', '' );
}

$t->assign('lang',$lang);

$t->assign('rendered_page', $t->fetch('affindex.tpl') );

$t->display( 'index.tpl' );
?>