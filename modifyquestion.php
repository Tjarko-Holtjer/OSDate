<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include('sessioninc.php');

$userid = $_SESSION['UserId'];

$sectionid = $_POST['sectionid'];

$currdisplayorder = $db->getOne('select displayorder from ! where id=?', array(SECTIONS_TABLE, $sectionid) );

$nextsectionid = $db->getOne('select id from ! where displayorder > ? and enabled = ? order by displayorder asc limit 1',array(SECTIONS_TABLE, $currdisplayorder, 'Y') );

if (!isset($nextsectionid)) $nextsectionid = 0;

if ($_POST['reqsectionid']!= '') {
	$nextsectionid = $_POST['reqsectionid'];
}

foreach ( $_POST as $questionid => $options ) {

	$j = 0;
	if ($questionid == 'selected_questions') {
		foreach($options as $kx => $val) {

			$sqldel = 'DELETE FROM ! WHERE userid = ? AND questionid = ?';

			$result = $db->query ( $sqldel, array( USER_PREFERENCE_TABLE, $userid, $val ) );

		}
	}

	if ( $questionid == 'sectionid' || $questionid == 'selected_questions' || $questionid == 'reqsectionid') {

	} elseif ( !is_array( $options ) ) {

		$userpref[ $j ] = $userid;
		$j++;

		if ( substr( $questionid, -1 ) == 'Y' ) {

			if ( $options == NULL ) {

				header ( 'location: editquestions.php?sectionid=' . $_POST['sectionid'] . '&errid='.MANDATORY_FIELDS );

				exit;
			}
		}

		$questionid = substr( $questionid, 0, strlen( $questionid) -1  );

		$userpref[ $j ] = $questionid;

		$j++;

		$userpref[ $j ] = $options;

		$sqlins = 'INSERT INTO ! ( userid, questionid, answer ) VALUES ( ?, ?, ? )';

		$result = $db->query ( $sqlins, array( USER_PREFERENCE_TABLE, $userpref[0], $userpref[1], addslashes(strip_tags($userpref[2] )) ) );

	} else {

		$executeflag = 0;

		foreach( $options as $option ) {

			$j = 0;

			$userpref[ $j ] = $userid;

			$j++;

			if ( substr( $questionid, -1 ) == 'Y' ) {

				if ( $options == NULL ) {

					header ( 'location: editquestions.php?sectionid='. $_POST['sectionid'] . '&errid='.MANDATORY_FIELDS );

					exit;
				}
			}

			$qid = substr( $questionid, 0, strlen( $questionid) -1 );

			$userpref[ $j ] = $qid;

			$j++;

			$userpref[ $j ] = $option;

			$sqlins = 'INSERT INTO ! ( userid, questionid, answer ) VALUES ( ?, ?, ? )';

			if ( !$executeflag ) {

				$executeflag = 1;
			}

			$result = $db->query ( $sqlins, array( USER_PREFERENCE_TABLE, $userpref[0], $userpref[1], addslashes(strip_tags($userpref[2] ) )) );

		} //foreach

	} //else


} //foreach

if( $nextsectionid > 0 ) {
	header ( 'location: editquestions.php?sectionid='. $nextsectionid );
} else {
	header ( 'location: edituser.php' );
}

?>