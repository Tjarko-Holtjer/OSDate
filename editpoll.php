<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );
include(JSCRIPT_DIR . "FCKeditor/fckeditor.php") ;
include(LIB_DIR . 'poll_class.php');

$poll = new poll();


// Edit the preferences if save button pressed 
// 
if ( $_POST['action'] == 'edit_poll' ) {

      $poll->editPoll($_POST['id']);

      if ( $poll->getErrorMessage() ) {
    
          $t->assign ( 'error_message', $poll->getErrorMessage() );
      }
      else {
    
          header( 'location: polllist.php' );
          exit;
      }
}
// Get the poll info if just clicked a edit link
else {

    $poll->loadPoll($_REQUEST['id']);
    $poll->prepPoll();
}


// Set the values to show on the page
// 
$question = $poll->getQuestion();

$t->assign( 'questionid', $question['id'] ) ;

$t->assign( 'question',    $question);

$options = $poll->getOption();

$t->assign( 'option',  $options );




// Make the page
// 
$t->assign('rendered_page', $t->fetch('editpoll.tpl') );

$t->display( 'index.tpl' );

exit;

?>
