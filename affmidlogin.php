<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

// to do: change error codes to PHP constants

if ($_POST['txtusername'] == '' or $_POST['txtpassword'] == '') {

	$err = MANDATORY_FIELDS;

} else {

	$sql = 'select id, name, status from ! where email = ? and password = ?';

	$row = $db->getRow( $sql, array( AFFILIATE_TABLE, $_POST['txtusername'],  md5( $_POST['txtpassword'] ) ) );

	if( $row ){

		if( $row['status'] == 'active' || $row['status'] == get_lang('status_enum','active') ) {

			$_SESSION['AffId'] = $row['id'];

			$_SESSION['AffName'] = $row['name'];

			header('location: affpanel.php');
			exit();

		} elseif( $row['status'] == 'approval' || $row['status'] == get_lang('status_enum','approval')) {

			$err = NOT_YET_APPROVED;

		} elseif( $row['status'] == 'rejected' || $row['status'] == get_lang('status_enum','rejected')) {

			$err = SUBMISSION_DECLINED;

		} elseif( $row['status'] == 'suspended' || $row['status'] == get_lang('status_enum','suspended')) {

			$err = ACCOUNT_SUSPENDED;
		}

	} else {

		$err = INVALID_LOGIN;

	}
}

header( 'location: afflogin.php?errid=' . $err );
exit();

?>