<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );
// include(JSCRIPT_DIR . "FCKeditor/fckeditor.php") ;
include_once(LIB_DIR . 'blog_class.php');

$blog = new Blog();

// If the preferences are missing, go to the settings page
//
if ( ! $blog->settingsExist($_SESSION['UserId']) ) {

      header( 'location: blogsettings.php?error_name=nosetup' );
      exit;
}

// Initialize FCK Editor
//
/* $titleEditor = new FCKeditor('title') ;
$titleEditor->BasePath   = DOC_ROOT . 'javascript/FCKeditor/' ;
$titleEditor->ToolbarSet = 'Basic';
$titleEditor->Width      = '100%' ;
$titleEditor->Height     = '100' ;


$storyEditor = new FCKeditor('story') ;
$storyEditor->BasePath   = DOC_ROOT . 'javascript/FCKeditor/' ;
$storyEditor->ToolbarSet = 'Default';
$storyEditor->Width      = '100%' ;
$storyEditor->Height     = '500' ;
*/
// Edit the preferences if save button pressed
//
if ( $_POST['action'] == 'edit_blog' ) {

      $blog->editBlog($_POST['id']);

      if ( $blog->getErrorMessage() ) {

          $t->assign ( 'error_message', $blog->getErrorMessage() );
      }
      else {

          header( 'location: bloglist.php' );
          exit;
      }
}
// Get the blog info if just clicked a edit link
else {

    $blog->loadBlog($_REQUEST['id']);
    $blog->prepData();
}


// If user turned off the gui editor, display the normal text box
//

$data = $blog->getData();

if ($data['userid'] > 0) {
	$blog->loadSettings($data['userid']);
} else {
	$blog->loadSettings($data['adminid']);
}

$t->assign('gui_editor', $blog->settings['gui_editor']);
/*
if ( $blog->settings['gui_editor'] == 0 ) {

  $titleEditor->useGui = false;
  $storyEditor->useGui = false;

  $nogui_spellchecker_js =  '<script language="javascript" type="text/javascript" src="' . DOC_ROOT . 'javascript/spellerpages/spellChecker.js">
</script>';
   $titlejs = '<input type="button" class="formbutton" value="' .  get_lang('spell_check') . '" onclick="var speller = new spellChecker(document.frmEditPref.title);speller.openChecker();" />';
   $storyjs = '<input type="button" class="formbutton" value="' .  get_lang('spell_check') . '" onclick="var speller = new spellChecker(document.frmEditPref.story);speller.openChecker();" />';

   $t->assign( 'title_spellchecker_link', $titlejs);
   $t->assign( 'story_spellchecker_link', $storyjs);
}
else {

   $t->assign( 'nogui_spellchecker_js', '');
   $t->assign( 'title_spellchecker_link', '');
   $t->assign( 'body_spellchecker_link', '');
}
*/
// Set the values to show on the page
//
$data = $blog->getData();


$t->assign( 'blog_id', $data['id'] ) ;

$t->assign( 'data',  $data);

/*
$titleEditor->Value      = $data['title'];
$t->assign( 'blog_title_form', $titleEditor->CreateHtml() ) ;

$storyEditor->Value      = $data['story'];
$t->assign( 'blog_story_form', $storyEditor->CreateHtml() ) ;
*/

$t->assign( 'date_posted', date('Y-m-d',$data['date_posted'] )) ;


// Put the javascript and ccs into the head of the document
//


$js = '<script type="text/javascript" src="' . DOC_ROOT . 'javascript/calendar/epoch_classes.js"></script>';

$css = '<link rel="stylesheet" type="text/css" href="' . DOC_ROOT . 'javascript/calendar/epoch_styles.css" />';

$t->assign('addtional_javascript', $js);
$t->assign('addtional_css', $css);



// Make the page
//
$t->assign('rendered_page', $t->fetch('editblog.tpl') );

$t->display( 'index.tpl' );

exit;

?>
