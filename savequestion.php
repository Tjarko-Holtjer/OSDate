<?php

if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include('sessioninc.php');

$userid = $_SESSION['UserId'];

//Get the next section id

$sql = 'SELECT displayorder FROM ! WHERE enabled = ? and id = ?';

$row = $db->getRow( $sql, array( SECTIONS_TABLE, 'Y', $_POST['sectionid'] ) );

$sql = 'SELECT id FROM ! WHERE enabled = ? and displayorder = ?';

$nextsection = $db->getRow( $sql, array( SECTIONS_TABLE, 'Y', $row['displayorder'] + 1 ) );


foreach ( $_POST as $questionid => $options ) {
	$j = 0;

	//If request parameter is sectionid, then check for next section and if it is last section then endofsection = 1

	if ( $questionid == 'sectionid' ) {

	}
	//If request variable contains variable=value. This is the case when user option has only one answer.
	elseif ( !is_array( $options ) ) {

		$userpref[ $j++ ] = $userid;

		if ( substr( $questionid, -1 ) == 'Y' ) {

			if ( $options == NULL ) {
				header ( 'location: questions.php?sectionid='. $_POST['sectionid'] . '&errid=20' );
				exit;

			}
		}

		$questionid = substr( $questionid, 0, strlen( $questionid) -1  );

		$userpref[ $j ] = $questionid;

		$j++;

		$userpref[ $j ] = $options;

		//Check that user already has answered question
		$sqlsel = 'SELECT id FROM ! WHERE userid = ? AND questionid = ?';

		$row = $db->getRow( $sqlsel, array( USER_PREFERENCE_TABLE, $userpref[0], $userpref[1] ) );

		if ( $row ) {

			$sql = 'UPDATE ! SET userid	= ?, questionid	= ?, answer = ?		WHERE id = ?';

			$db->query ( $sql, array(USER_PREFERENCE_TABLE, $userpref[0], $userpref[1], strip_tags($userpref[2]), $row['id']) );
		} else {

			$sqlins = 'INSERT INTO ! ( userid, questionid, answer ) VALUES ( ?, ?, ? )';
			$db->query( $sqlins , array( USER_PREFERENCE_TABLE, $userpref[0], $userpref[1], strip_tags($userpref[2]) ) );
		}

	}
	//If request variable contains variable=Array. This is the case when user option have many options.
	else {
		foreach( $options as $option ) {

			$j = 0;

			$userpref[ $j ] = $userid;

			$j++;

			if ( substr( $questionid, -1 ) == 'Y' ) {

				if ( $options == NULL ) {

					header ( 'location: questions.php?sectionid='. $_POST['sectionid'] . '&errid=20' );
					exit;

				}
			}

			$qid = substr( $questionid, 0, strlen( $questionid) -1 );

			$userpref[ $j ] = $qid;

			$j++;

			$userpref[ $j ] = $option;

			//Check that user already has answered question
			$sqlsel = 'SELECT id FROM ! WHERE userid=? AND questionid=? AND answer=?';

			$row = $db->getRow ( $sqlsel, array(USER_PREFERENCE_TABLE, $userpref[0], $userpref[1], $userpref[2] ) );

			//$row = $result->fetchRow();
			if ( $row ) {

				$sql = 'UPDATE ! SET userid	= ?, questionid	= ?, answer = ?		WHERE id = ?';

				$db->query ( $sql, array(USER_PREFERENCE_TABLE, $userpref[0], $userpref[1], strip_tags($userpref[2]), $row['id']) );
			} else {

				$sqlins = 'INSERT INTO ! ( userid, questionid, answer ) VALUES ( ?, ?, ? )';

				$db->query( $sqlins , array( USER_PREFERENCE_TABLE, $userpref[0], $userpref[1], strip_tags($userpref[2]) ) );

			}

		} //foreach

	} //else

} //foreach

if( $nextsection['id'] == "" ) {

	header ( 'location: signupsuccess.php');
} else {
	header ( 'location: questions.php?sectionid='. $nextsection['id'] );
}

?>