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
/*
$titleEditor = new FCKeditor('title') ;
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
$blog->loadSettings($_SESSION['UserId']);

// Load template if load template button pushed
//
if ( $_POST['action'] == 'add_blog' && $_POST['load_template'] ) {

     $blog->loadTemplate();
}
// Add Blog if save button pressed
//
elseif ( $_POST['action'] == 'add_blog' ) {

      $blog->addBlog($_SESSION['UserId']);

      if ( $blog->getErrorMessage() ) {

          $t->assign ( 'error_message', $blog->getErrorMessage() );
      }
      else {

          header( 'location: bloglist.php' );
          exit;
      }
}


// If user turned off the gui editor, display the normal text box
//
$t->assign('gui_editor', $blog->settings['gui_editor']);

/*if ( $blog->settings['gui_editor'] == 0 ) {

  $titleEditor->useGui = false;
  $storyEditor->useGui = false;

  $nogui_spellchecker_js =  '<script language="javascript" type="text/javascript" src="' . DOC_ROOT . 'javascript/spellerpages/spellChecker.js"></script>';
   $titlejs = '<input type="button" class="formbutton" value="' .  get_lang('spell_check') . '" onclick="var speller = new spellChecker(document.frmEditPref.title);speller.openChecker();" />';
   $storyjs = '<input type="button" class="formbutton" value="' .  get_lang('spell_check') . '" onclick="var speller = new spellChecker(document.frmEditPref.story);speller.openChecker();" />';

   $t->assign( 'title_spellchecker_link', $titlejs);
   $t->assign( 'story_spellchecker_link', $storyjs);
}
else {

   $nogui_spellchecker_js = '';
   $t->assign( 'title_spellchecker_link', '');
   $t->assign( 'body_spellchecker_link', '');
}
*/
// Set the values to show on the page
//
$data = $blog->getData();

// If there's a saved template, give the oportunity to use it
//
/*
if ( $blog->settings['title_template'] ) {

      $loadtemp = '<input type="submit" name="load_template" value="' . get_lang('blog_load_template') . '">';
      $t->assign( 'load_template_html',  $loadtemp);
}
else {

      $t->assign( 'load_template_html',  '');
}
$savetemp = '<table border=0 cellspacing=0 cellpadding=0><tr><td valign="middle">'.get_lang('blog_save_template') . '</td><td valign="middle"><input type="checkbox" name="save_template" value="1" /></td></tr></table>';

$t->assign( 'save_template_html',  $savetemp);
*/
$t->assign( 'loadtemp',  $loadtemp);


$t->assign( 'data',  $data);

/*
$titleEditor->Value      = $data['title'];
$t->assign( 'blog_title_form', $titleEditor->CreateHtml() ) ;

$storyEditor->Value      = $data['story'];
$t->assign( 'blog_story_form', $storyEditor->CreateHtml() ) ;
*/

$t->assign( 'date_posted', $data['date_posted'] ) ;


// If we already have the max stories, display an error
//
if ( $blog->getStoryCount($_SESSION['UserId']) >= $config['max_blog_stories'] ) {

    $t->assign ( 'error_message', get_lang('blog_errors', 'max_stories_warning') );

    $t->assign('rendered_page', $t->fetch('addbloglimit.tpl') );
}
else {

    $t->assign('rendered_page', $t->fetch('addblog.tpl') );

}


// Put the javascript and ccs into the head of the document
//
$js = '<script type="text/javascript" src="' . DOC_ROOT . 'javascript/calendar/epoch_classes.js"></script>';
$js .= $nogui_spellchecker_js;

$css = '<link rel="stylesheet" type="text/css" href="' . DOC_ROOT . 'javascript/calendar/epoch_styles.css" />';
$t->assign('lang',$lang);
$t->assign('addtional_javascript', $js);
$t->assign('addtional_css', $css);

// Make the page
//

$t->display( 'index.tpl' );

exit;

?>
