<?php
//Include init.php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

$name = strip_tags(trim( $_POST[ 'txtname' ] ) );

$password = strip_tags(trim( $_POST[ 'txtpassword' ] ) );

$conpassword = strip_tags(trim( $_POST[ 'txtconpassword' ] ) );

$email = strip_tags(trim( $_POST[ 'txtemail' ] ) );

if( $name=='' || $password=='' || $email=='' || $conpassword =='' ){

	header( 'location: affindex.php?error=' . get_lang('affiliates_error',0));
	exit;

} elseif( $conpassword != $password ){

	header( 'location: affindex.php?error=' . get_lang('affiliates_error',1));
	exit;

}

$sqlc = 'SELECT count(*) as aacount from ! where email= ? ';

$rowc = $db->getRow( $sqlc, array( AFFILIATE_TABLE, $email ) );

if ( $rowc['aacount'] > 0 ) {

	header( 'location: affindex.php?error=' . get_lang('affiliates_error',2));
	exit;

}

$status = 'approval';

if ($config['aff_default_active_status'] == 'Y') $status = 'active';

$regdate = time();


$sqlins = "INSERT INTO ? (  name, email, password, status, regdate ) VALUES ( ?, ?, ?, ?, ? )";

$result = $db->query ( $sqlins, array( AFFILIATE_TABLE, $name, $email, md5($password), $status, $regdate ) );

$lastid = $db->getOne('select id from ! where name = ? and email = ?', array(AFFILIATE_TABLE, $name, $email));

$t->assign ( 'affid', $lastid );

$t->assign('rendered_page', $t->fetch('affsignupsuccess.tpl') );

$t->display( 'index.tpl' );

exit;
?>

