<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );

$userid = $_SESSION['UserId'];

$sectionid = $_GET[ 'sectionid' ];

if ( $sectionid == '' ) {
	$sectionid = 1;
}

// Query to reterive records from osdate_questions table
// sorted descending on mandatory: that is mandatory fields should be displayed first

$sqlquestion = 'select id, question, mandatory, description, guideline, maxlength, control_type from ! where enabled = ? and section = ? and question <> ? order by mandatory desc, displayorder';

$temp = $db->getAll( $sqlquestion, array( QUESTIONS_TABLE, 'Y', $sectionid , '') );

$data = array();

foreach( $temp as $index => $row ) {

	if ($_SESSION['opt_lang'] != 'english') {
	/* THis is made to adjust for multi-language */
		$lang_question = $_SESSION['profile_questions'][$row['id']]['question'];
		$lang_descr = 	$_SESSION['profile_questions'][$row['id']]['description'];
		$lang_guide = 	$_SESSION['profile_questions'][$row['id']]['guideline'];
		if ($lang_question != '') {
			$row['question'] = $lang_question;
		}
		if ($lang_descr != '') {
			$row['description'] = $lang_descr;
		}
		if ($lang_guide != '') {
			$row['guideline'] = $lang_guide;
		}
	}

	// reterive record from osdate_questionoptions table

	$sql = 'select * from ! where enabled = ? and questionid = ? order by displayorder';

	$options = $db->getAll( $sql, array( OPTIONS_TABLE, 'Y', $row['id'] ) ) ;

	$optsrs = array();
	if ($_SESSION['opt_lang'] != 'english') {
	/* THis is made to adjust for multi-language */
		foreach($options as $kx => $opt) {
			$lang_ansopt = $_SESSION['profile_questions'][$row['id']][$opt['id']];
			if ($lang_ansopt != '') {$opt['answer'] = $lang_ansopt;
			}
			$optsrs[] = $opt;
		}
	} else {$optsrs = $options; }

	$row['options'] = makeOptions ( $optsrs );

	$sql = 'select questionid, answer from ! where userid = ? and questionid = ?';

	$userprefrs = $db->getAll( $sql, array( USER_PREFERENCE_TABLE, $userid, $row['id'] ) ) ;

	$row['userpref'] = makeAnswers ( $userprefrs );

	$data [] = $row;
}



if ( isset( $_GET['errid'] ) ) {

	$t->assign( 'mandatory_question_error', get_lang('errormsgs',$_GET['errid']) );

}

$t->assign( 'sectionid', $_GET['sectionid'] );

$t->assign('frmname', 'frm' . $sectionid );

$t->assign( 'head', $sections[ $sectionid ] );

$t->assign('lang', $lang);

$t->assign( 'data', $data );

$t->assign('rendered_page', $t->fetch('editquestions.tpl') );

$t->display('index.tpl');

?>