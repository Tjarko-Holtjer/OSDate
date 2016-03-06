<?php
if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );
}
include(LIB_DIR . 'poll_class.php');

$poll = new Poll();

$poll->loadPoll($_REQUEST['id']);



 
// Set the values to show on the page
// 
$question = $poll->getQuestion();

$t->assign( 'questionid', $question['id'] ) ;

$t->assign( 'question',    $question);

$answer = $poll->getAnswer();

$t->assign( 'answer',  $answer );


$t->assign( 'rendered_page', $t->fetch( 'pollresults.tpl' ) );

$t->display ( 'index.tpl' );


?>