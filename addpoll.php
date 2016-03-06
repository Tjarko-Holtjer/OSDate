<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );

include(LIB_DIR . 'poll_class.php');

$poll = new Poll();



// Edit the preferences if save button pressed 
// 
if ( $_POST['action'] == 'add_poll' ) {

      $poll->addPoll($_SESSION['UserId']);

      if ( $poll->getErrorMessage() ) {
    
          $t->assign ( 'error_message', $poll->getErrorMessage() );
      }
      else {
    
          header( 'location: polllist.php' );
          exit;
      }
}

$t->assign ( 'question', $_POST['question'] );
$t->assign ( 'option', $_POST['option'] );




$t->assign('rendered_page', $t->fetch('addpoll.tpl') );

// Make the page
// 

$t->display( 'index.tpl' );

exit;

?>
