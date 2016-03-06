<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );

include_once(LIB_DIR . 'blog_class.php');

$blog = new Blog();


if ( ! $blog->settingsExist($_SESSION['UserId']) ) {

      header( 'location: blogsettings.php?error_name=nosetup' );
      exit;
}

if ($blog->getStoryCount($_SESSION['UserId']) <= 0){
      header( 'location: addblog.php' );
      exit;

}

// If user clicked the remove button and confirmed the delete, delete it
//
if ( $_GET['action'] == 'delete' && $_GET['delete'] == 'Y' ) {

   $blog->deleteStory($_GET['id']);
}
elseif ( $_POST['action'] == 'multiple_delete'  ) {

  $blog->multipleDeleteStory($_POST['delete']);

}

// Make the sort links
//
$blog->sort_page = 'bloglist.php';
$t->assign('sort_blog_views',   $blog->SortLink(get_lang('blog_views_hdr'),'views') );
$t->assign('sort_blog_ratings', $blog->SortLink(get_lang('blog_rating_list_hdr'),'votes') );
$t->assign('sort_blog_title',   $blog->SortLink(get_lang('blog_title_hdr'),'title') );
$t->assign('sort_date_posted',  $blog->SortLink(get_lang('blog_date_posted_hdr'),'date_posted') );

$t->assign('list', $blog->getAllStories($_SESSION['UserId']) );
$t->assign( 'lang', $lang );


$js = '<script type="text/javascript" src="'. DOC_ROOT . 'javascript/functions.js"></script>';
$t->assign('addtional_javascript', $js);

// Make the page
//
$t->assign('rendered_page', $t->fetch('bloglist.tpl') );

$t->display( 'index.tpl' );

exit;

?>
