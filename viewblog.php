<?php
if ( !defined( 'SMARTY_DIR' ) ) {

	include_once( 'init.php' );
}
include_once(LIB_DIR . 'blog_class.php');

$blog = new Blog();
$blog->addBlogView($_REQUEST['id'], $_SESSION['UserId']);
$blog->loadBlog($_REQUEST['id']);

if ( $_POST['action'] == 'add_comment' ) {

   $blog->addComment($_REQUEST['id'], $_SESSION['UserId']);


   $blog->prepComment();
   $comments = $blog->getComment();
   $t->assign ( 'comment', $comments['comment'] );

   $t->assign ( 'error_message', $blog->getErrorMessage() );
}
elseif ( $_POST['action'] == 'add_vote' ) {

   $blog->addBlogVote($_REQUEST['id'], $_SESSION['UserId'], $_REQUEST['vote']);
   $blog->loadBlog($_REQUEST['id']); // make sure we have the new vote counted
}
else {

   $t->assign ( 'error_message', '' );
   $t->assign ( 'comment', '' );
}



$blog_data = $blog->getData();

$blog->loadSettings( $blog_data['userid'] || $blog_data['adminid'] );

$t->assign('blog',  $blog_data);
$t->assign('pref',  $blog->getSettings());
$t->assign('numcomments',  $blog->getCommentCount($_REQUEST['id']));
$t->assign('comments',  $blog->getAllComments($_REQUEST['id']));
$t->assign('now',  date('Y-m-d') );

$t->assign('allowcomments',  $blog->allowComments($_REQUEST['id'], $_SESSION['UserId']) );

// For debuging.  Says why the user can't post a comment.  Comment out for production
// print $blog->no_comment;


$arr = array();

for( $i=-5; $i<=5; $i++ ) {
        $arr[$i] = $i;
}


$js = '<script type="text/javascript" src="' . DOC_ROOT . 'javascript/functions.js"></script>';
$t->assign('addtional_javascript', $js);

$t->assign('lang', $lang);


$t->assign ( 'vote_values', $arr );


$t->assign( 'rendered_page', $t->fetch( 'viewblog.tpl' ) );

$t->display ( 'index.tpl' );


?>
