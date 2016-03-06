<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );
include_once(LIB_DIR . 'blog_class.php');

$blog = new Blog();

// If the preferences are missing, go to the settings page
//
if ( ! $blog->settingsExist($_SESSION['UserId']) ) {

      header( 'location: blogsettings.php?error_name=nosetup' );
      exit;
}


// Edit the preferences if save button pressed
//
if ( $_GET['action'] == 'delete' && $_GET['delete'] == 'Y'  ) {

      $blog->deleteComment($_REQUEST['deleteid'], $_SESSION['UserId']);
}
// Set the values to show on the page
//

$blog->loadBlog($_REQUEST['id']);

$t->assign( 'blog',  $blog->getData() );

$t->assign('now',  date('Y-m-d') );
$t->assign('numcomments',  $blog->getCommentCount($_REQUEST['id']));
$t->assign('comments',  $blog->getAllComments($_REQUEST['id']));


// Make the page
//
$t->assign('lang',$lang);

$t->assign('rendered_page', $t->fetch('viewmyblog.tpl') );

$t->display( 'index.tpl' );

exit;

?>
