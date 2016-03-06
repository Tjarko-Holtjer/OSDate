<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}

include( 'sessioninc.php' );
/*include(JSCRIPT_DIR . "FCKeditor/fckeditor.php") ;

// Initialize FCK Editor
//
$oFCKeditor = new FCKeditor('description') ;
$oFCKeditor->BasePath   = DOC_ROOT . 'javascript/FCKeditor/' ;
$oFCKeditor->ToolbarSet = 'Basic';
$oFCKeditor->Width      = '100%' ;
$oFCKeditor->Height     = '100' ;
*/
include_once(LIB_DIR . 'blog_class.php');

$blog = new Blog();

// If we were bounced here with a error message, save it
//
if ( isset($_GET['error_name']) ) {

      $t->assign ( 'error_message', get_lang('blog_errors', $_GET['error_name']) );
}
$t->assign('lang',$lang);

// Edit the preferences if save button pressed
//
if ( $_POST['action'] == 'edit_pref' ) {

      $blog->saveSettings($_SESSION['UserId']);


      if ( $blog->getErrorMessage() ) {

          $t->assign ( 'error_message', $blog->getErrorMessage() );

          $row = $blog->getSettings();
      }
      else {

		if ($blog->getStoryCount($_SESSION['UserId']) <= 0) {
			header( 'location: addblog.php' );
		} else {
         // After saving, go to blog list page
	         header( 'location: bloglist.php' );
		}
         exit;
      }

}
// Display current info
//
else {

   $blog->loadSettings($_SESSION['UserId']);
   $blog->prepSettings();
   // Strip slashes

   $row = $blog->getSettings();
}

// If user turned off the gui editor, display the normal text box
//

/*
if ( $blog->settings['gui_editor'] == 0 ) {

  $oFCKeditor->useGui = false;

  $t->assign( 'nogui_spellchecker_js',  '<script language="javascript" type="text/javascript" src="' . DOC_ROOT . 'javascript/spellerpages/spellChecker.js">
</script>');
   $descriptionjs = '<input type="button" class="formbutton" value="' .  get_lang('spell_check') . '" onclick="var speller = new spellChecker(document.frmEditPref.description);speller.openChecker();">';

   $t->assign( 'description_spellchecker_link', $descriptionjs);
}
else {

   $t->assign( 'nogui_spellchecker_js', '');
   $t->assign( 'description_spellchecker_link', '');
}

$oFCKeditor->Value      = $row['description'];
*/
$t->assign( 'row', $row );
//$t->assign( 'blog_description_form', $oFCKeditor->CreateHtml() ) ;

// Make the page
//
$t->assign('rendered_page', $t->fetch('blogsettings.tpl') );

$t->display( 'index.tpl' );

exit;

?>
