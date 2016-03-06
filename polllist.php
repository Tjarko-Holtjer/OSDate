<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );

include(LIB_DIR . 'poll_class.php');

$poll = new Poll();



// If user clicked the remove button and confirmed the delete, delete it
// 
if ( $_GET['action'] == 'delete' && $_GET['delete'] == 'Y' ) {
 
   $poll->deletePoll($_GET['id']);
}
elseif ( $_POST['action'] == 'multiple_delete'  ) {

  $poll->multipleDeletePoll($_POST['delete']);

}

$t->assign('list', $poll->getAllPolls($_SESSION['UserId']) );

$js = '<script type="text/javascript" src="' . DOC_ROOT . 'javascript/functions.js"></script>';
$t->assign('addtional_javascript', $js);

// Make the page
// 
$t->assign('rendered_page', $t->fetch('polllist.tpl') );

$t->display( 'index.tpl' );

exit;

?>