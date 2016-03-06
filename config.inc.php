<?php

include("../osdate_init.php");

/*
-----------
DB Settings
-----------
*/

define( 'DB_USER', '' );
define( 'DB_NAME', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_PASS', '' );
define( 'DB_TYPE', 'mysql' );
define( 'DB_PREFIX', 'osdate' );


// -------------
// Mail settings
// -------------

	// text|html
define( 'MAIL_FORMAT', 'html' );
		//mail|sendmail|smtp
define( 'MAIL_TYPE', 'mail' );
define( 'SMTP_HOST', 'localhost' );
define( 'SMTP_PORT', '25' );
define( 'SMTP_AUTH', '0' );
define( 'SMTP_USER', '' );
define( 'SMTP_PASS', '' );
define( 'SM_PATH', '/usr/sbin/sendmail' );


/*  Define Language Options */
$language_options = array(
	'chinese'	=> 'Chinese',
	'dutch' 	=> 'Dutch',
	'english' 	=> 'English',
	'french'	=> 'French',
	'german'	=> 'German',
	'greek'		=> 'Greek',
	'hungarian'	=> 'Hungarian',
	'italian'	=> 'Italian',
	'japanese'	=> 'Japanese',
	'norwegian'	=> 'Norway',
	'portuguese'=> 'Portuguese',
	'romanian' 	=> 'Romanian',
	'russian'	=> 'Russian',
	'spanish'	=> 'Spanish',
	'swedish'	=> 'Swedish',
	'turkish'	=> 'Turkish'
	);

$language_files = array(
	'chinese'	=> 'lang_chinese/lang_main.php',
	'dutch'		=> 'lang_dutch/lang_main.php',
	'english' 	=> 'lang_english/lang_main.php',
	'french'	=> 'lang_french/lang_main.php',
	'german'	=> 'lang_german/lang_main.php',
	'greek'		=> 'lang_greek/lang_main.php',
	'hungarian'	=> 'lang_hungarian/lang_main.php',
	'italian'	=> 'lang_italian/lang_main.php',
	'japanese'	=> 'lang_japanese/lang_main.php',
	'norwegian'	=> 'lang_norwegian/lang_main.php',
	'portuguese'=> 'lang_portuguese/lang_main.php',
	'romanian'	=> 'lang_romanian/lang_main.php',
	'russian'	=> 'lang_russian/lang_main.php',
	'spanish'	=> 'lang_spanish/lang_main.php',
	'swedish'	=> 'lang_swedish/lang_main.php',
	'turkish'	=> 'lang_turkish/lang_main.php'
	);

/*  For converting languages to other formats */
$language_conversion = array(
   'chinese'	=> 'ch',
   'dutch'     => 'nl',
   'english'   => 'en',
   'french'    => 'fr',
   'german'    => 'de',
   'greek'     => 'el',
   'hungarian'	=> 'hg',
   'italian'	=> 'it',
   'japanese'	=> 'jp',
   'norwegian'	=> 'nw',
   'portuguese'=> 'pt',
   'romanian'  => 'ro',
   'russian'   => 'ru',
   'spanish'   => 'es',
   'swedish'	=> 'sw',
   'turkish'   => 'tr'
   );



define( 'DEFAULT_LANG', 'english' );

// ---------------
// Default Country
// ---------------
// transferred to config table
// define( 'DEFAULT_COUNTRY', 'US' );

// -----------
// PATH Settings
// -----------
define( 'ROOT_DIR', FULL_PATH );
define( 'ADMIN_DIR', FULL_PATH . 'admin/' );
define( 'BANNER_DIR', FULL_PATH . 'banners' );
define( 'LIB_DIR', FULL_PATH . 'libs/' );
define( 'INC_DIR', FULL_PATH . 'includes/' );
define( 'JSCRIPT_DIR', FULL_PATH . 'javascript/' );
define( 'SMARTY_DIR', FULL_PATH . 'libs/Smarty/' );
define( 'MODOSDATE_DIR', FULL_PATH . 'libs/modOsDate/' );
define( 'AUTH_DIR', FULL_PATH . 'libs/Auth/' );
define( 'TEMPLATE_DIR', FULL_PATH . 'templates/' );
define( 'TEMPLATE_C_DIR', FULL_PATH . 'templates_c/' );
define( 'CACHE_DIR', FULL_PATH . 'cache/' );
define( 'CONFIG_DIR', FULL_PATH . 'configs/' );
define( 'PEAR_DIR', FULL_PATH . 'libs/Pear/' );
define( 'PLUGIN_DIR', FULL_PATH . 'plugins/' );
define( 'LANG_DIR', FULL_PATH . 'language/' );
define( 'MAIL_CLASSES_DIR', FULL_PATH . 'libs/mail/' );
define( 'TEMP_DIR', FULL_PATH.'temp/' );
define( 'USER_IMAGE_DIR', FULL_PATH . 'userimages/');
define( 'FORUM_DIR', FULL_PATH . 'forum/');

define( 'USER_VIDEO_DIR', FULL_PATH . 'uservideos/');


// ---------------------
// GLOBAL PATH Settings
// --------------------
define( 'OSDATE_INSTALLED', '1' );
define( 'DOC_ROOT', '/osDate_v2/' );
define( 'USER_IMAGE_WEB', DOC_ROOT . 'userimages/' );
define( 'LONG_DATE_FORMAT', 'F j, Y' );
define( 'SHORT_DATE_FORMAT', 'm/d/y' );
define( 'DISPLAY_DATE_FORMAT', 'MMM DD, YYYY');
define( 'DATE_TIME_FORMAT', '%b %d, %Y %H:%I:%S');
define( 'DATE_FORMAT', '%b %d, %Y');

define('SHOUTBOX_TIME_FORMAT', 'm/d H:i:s');

// ----------------
// DB TABLE Names
// ---------------

define ( 'ADMIN_EMAILS_TABLE', DB_PREFIX . '_adminemails' );
define ( 'ADMIN_LETTER_TABLE', DB_PREFIX . '_letters' );
define ( 'ADMIN_RIGHTS_TABLE', DB_PREFIX . '_admin_permissions' );
define ( 'ADMIN_TABLE', DB_PREFIX . '_admin' );
define ( 'AFFILIATE_REFERALS_TABLE', DB_PREFIX . '_aff_referals' );
define ( 'AFFILIATE_TABLE', DB_PREFIX . '_affiliates' );
define ( 'ARTICLES_TABLE', DB_PREFIX . '_articles' );
define ( 'BANNER_TABLE', DB_PREFIX . '_banners' );
define ( 'BUDDY_BAN_TABLE', DB_PREFIX . '_buddy_ban_list' );
define ( 'CALENDARS_TABLE', DB_PREFIX . '_calendars ' );
define ( 'CITIES_TABLE', DB_PREFIX . '_cities' );
define ( 'CONFIG_TABLE', DB_PREFIX . '_glblsettings' );
define ( 'COUNTIES_TABLE', DB_PREFIX . '_counties' );
define ( 'COUNTRIES_TABLE', DB_PREFIX . '_countries' );
define ( 'EVENTS_TABLE', DB_PREFIX . '_calendarevents ' );
define ( 'FEATURED_PROFILES_TABLE', DB_PREFIX . '_featured_profiles' );
define ( 'INSTANT_MESSAGE_TABLE', DB_PREFIX . '_instant_message' );
define ( 'IMPORT_QUESTIONS_XREF', DB_PREFIX . '_import_questions_xref' );
define ( 'IMPORTED_USERS', DB_PREFIX . '_imported_users' );
define ( 'LANGUAGE_TABLE', DB_PREFIX . '_languages' );
define ( 'LOG_TABLE', DB_PREFIX . '_log' );
define ( 'MAILBOX_TABLE', DB_PREFIX . '_mailbox' );
define ( 'MEMBERSHIP_TABLE', DB_PREFIX . '_membership' );
define ( 'NEWS_TABLE', DB_PREFIX . '_news' );
define ( 'ONLINE_USERS_TABLE', DB_PREFIX . '_onlineusers' );
define ( 'OPTIONS_TABLE', DB_PREFIX . '_questionoptions' );
define ( 'PAGES_TABLE', DB_PREFIX . '_pages' );
define ( 'PAYMENT_MODULE_TABLE', DB_PREFIX . '_payment_modules' );
define ( 'POLLIPS_TABLE', DB_PREFIX . '_pollips' );
define ( 'POLLOPTS_TABLE', DB_PREFIX . '_polloptions' );
define ( 'POLLS_TABLE', DB_PREFIX . '_polls' );
define ( 'POLL_QUESTION_TABLE', DB_PREFIX . '_poll_question' );
define ( 'POLL_OPTION_TABLE', DB_PREFIX . '_poll_option' );
define ( 'POLL_ANSWER_TABLE', DB_PREFIX . '_poll_answer' );
define ( 'QUESTIONS_TABLE', DB_PREFIX . '_questions' );
define ( 'RATINGS_TABLE', DB_PREFIX . '_ratings');
define ( 'SECTIONS_TABLE', DB_PREFIX . '_sections' );
define ( 'STATES_TABLE', DB_PREFIX . '_states' );
define ( 'STORIES_TABLE', DB_PREFIX . '_stories' );
define ( 'TABLE_CONFIGURATION', DB_PREFIX . '_payment_config' );
define ( 'TRANSACTIONS_TABLE', DB_PREFIX . '_transactions' );
define ( 'USERALBUMS_TABLE', DB_PREFIX . '_useralbums' );
define ( 'USER_PREFERENCE_TABLE', DB_PREFIX . '_userpreference' );
define ( 'USER_RATING_TABLE', DB_PREFIX . '_userrating' );
define ( 'USER_SNAP_TABLE', DB_PREFIX . '_usersnaps' );
define ( 'USER_TABLE', DB_PREFIX . '_user' );
define ( 'USERTEMPLATE_TABLE', DB_PREFIX . '_usertemplates');
define ( 'USER_SEARCH_TABLE', DB_PREFIX . '_usersearches' );
define ( 'VIEWS_WINKS_TABLE', DB_PREFIX . '_views_winks' );
define ( 'WATCHES_TABLE', DB_PREFIX . '_calendarwatchevents ' );
define ( 'ZIPCODES_TABLE', DB_PREFIX . '_zips' );

/* Release 2.0 additions */

define ( 'BLOG_COMMENTS_TABLE', DB_PREFIX . '_blog_comments' );
define ( 'BLOG_PREFERENCES_TABLE', DB_PREFIX . '_blog_preferences' );
define ( 'BLOG_STORY_TABLE', DB_PREFIX . '_blog_story' );
define ( 'BLOG_VOTE_TABLE', DB_PREFIX . '_blog_vote' );
define ( 'PLUGIN_TABLE', DB_PREFIX . '_plugin' );
define ( 'PLUGIN_ACCESS_TABLE', DB_PREFIX . '_plugin_access' );
define ( 'PLUGIN_CONFIG_TABLE', DB_PREFIX . '_plugin_config' );
define ( 'PLUGIN_TABLES_TABLE', DB_PREFIX . '_plugin_tables' );
define ( 'SHOUTBOX_TABLE', DB_PREFIX . '_shoutbox ' );
define ( 'USER_SAVED_PROFILES', DB_PREFIX . '_user_saved_profiles' );
define ( 'USER_WATCHED_PROFILES', DB_PREFIX . '_user_watched_profiles' );
define ( 'USER_ACTIONS', DB_PREFIX . '_user_actions' );
define ( 'USER_VIDEOS_TABLE', DB_PREFIX . '_uservideos ' );
define ( 'USER_CHOICES_TABLE', DB_PREFIX . '_user_choices ' );


// ----------------
// Error Message Codes
// ---------------

define ('USERNAME_BLANK','1');
define ('PASSWORD_BLANK','2');
define ('FIRSTNAME_REQUIRED','4');
define ('LASTNAME_REQUIRED','5');
define ('EMAIL_REQUIRED','6');
define ('CITY_REQUIRED','7');
define ('ZIP_REQUIRED','8');
define ('FIRSTNAME_LENGTH','11');
define ('LASTNAME_LENGTH','12');
define ('EMAIL_LENGTH','13');
define ('CITY_LENGTH','14');
define ('PASS_CONFIRMPASS', '18');
define ('MANDATORY_FIELDS', '20');
define ('INVALID_LOGIN','21');
define ('USERNAME_EXISTS', '22');
define ('WRONG_OLD_PASSWORD','23');
define ('EMAIL_EXISTS','25');
define ('NOT_ACTIVE', '26');
define ('NO_MESSAGE','27');
define ('UNSUPPORTED_FILE_FORMAT','29');
define ('QUESTION_ON_TOP','30');
define ('QUESTION_AT_BOTTOM','31');
define ('NOT_YET_APPROVED','35');
define ('ACCOUNT_SUSPENDED', '36');
define ('SUBMISSION_DECLINED', '37');
define ('INVALID_BIRTHDATE','38');
define ('OLD_NEW_PASSWORD_MUST_DIFFER', '39');
define ('BIGGER_STARTAGE','40');
define ('ERR_STARTDATE_BEFORE_ENDDATE', '51');
define ('ERR_EXISTING', '52');
define ('INVALID_DATE', '53');
define ('INVALID_USERNAME','104');
define ('NOT_LOGGED_IN','55');
define ('BIG_PIC_SIZE','56');
define ('WRONG_TYPE','57');
define ('FAILED_UPLOAD','58');
define ('PROFILEISADDEDTOLIST','59');
define ('BIGTHUMBNAIL','60');
define ('INVALID_ACTIVATION_CODE','61');
define ('REMOVEDFROMLIST','62');
define ('ADDEDTOBUDDYLIST','63');
define ('ADDEDTOBANLIST','64');
define ('ADDEDTOHOTLIST','65');
define ('WINKISSENT','66');
define ('PICTURE_LOADED','67');
define ('PICTURE_APPROVED','68');
define ('PICTURE_REJECTED','69');
define ('USER_REACTIVATED', '72');
define ('COUNTRY_ADDED','73');
define ('COUNTRY_DELETED', '74');
define ('COUNTRYCODE_INUSE','75');
define ('COUNTRY_MODIFIED','76');
define ('STATE_ADDED','77');
define ('STATE_DELETED', '78');
define ('STATECODE_INUSE', '79');
define ('STATE_MODIFIED','76');
define ('STATEPROVINCE_NEEDED','81');
define ('PROFILE_DELETED','83');
define ('PROFILES_DELETED', '84');
define ('PROFILES_ACTIVATED','85');
define ('PROFILES_REJECTED','86');
define ('PROFILES_SUSPENDED','87');

/* Relese 1.0 */

define ('COUNTY_ADDED','88');
define ('COUNTY_DELETED', '89');
define ('COUNTYCODE_INUSE','90');
define ('COUNTY_MODIFIED','91');
define ('CITY_ADDED','92');
define ('CITY_DELETED', '93');
define ('CITYCODE_INUSE','94');
define ('CITY_MODIFIED','95');
define ('ZIP_ADDED','96');
define ('ZIP_DELETED', '97');
define ('ZIPCODE_INUSE','98');
define ('ZIP_MODIFIED','99');
define ('COUNTY_REQUIRED','100');
define ('INVALID_PASSWORD','101');

define ('EVENT_APPROVED','102');
define ('EVENT_REJECTED','103');

define ('REGN_COMPLETED','200');
define ('INVALID_TIMEZONE','301');
define ('ALBUM_CHANGED','302');

/* Folowing are for template oriented messages. */
define ('NO_TEMPLATE', '2');
define ('PASSWORD_MAIL_SENT','0');
define ('MAIL_ERROR','4');
define ('NOT_REGISTERED','5');

/* Story errors */
define ('NO_STORY_HDR','1');
define ('NO_STORY_TEXT','2');
define ('NO_STORY_SENDER','4');

/* Page errors */
define ('NO_PAGE_HDR','1');
define ('NO_PAGE_KEY','2');
define ('NO_PAGE_TEXT','3');
define ('PAGE_EXISTS','4');

/* News and Articles errors */
define ('NO_HDR','1');
define ('NO_TEXT','2');

/* Membership errors */
define ('NO_NAME','1');
define ('NO_PRICE','2');
define ('NO_CURRENCY','3');

/* Banner Messages */
define ('BANNER_BLANK','1');
define ('LINK_BLANK','2');
define ('BANNER_WRONG_TYPE','4');
define ('BANNER_WRONG_SIZE', '5');

/* POll Error */
define ('OPTION_BLANK','3');

/* Admin errors */
define ('FULLNAME_BLANK','3');
define ('OLDPWD_BLANK','4');
define ('NEWPWD_BLANK','5');
define ('CONFPWD_BLANK','6');
define ('DIFF_PASSWORDS','7');
define ('WRONG_PASSWORD','8');

/* Letter errors */
define ('INVALID_EMAIL','2');
define ('ALL_OK','0');
define ('EMAIL_PROBLEM','4');

/* others */
define ('ALREADY_EXISTS','9');

define ('SECTION_BLANK','1');
define ('FIELDS_BLANK','2');
define ('CALENDAR_BLANK','3');

// set_include_path(PEAR_DIR.':'.get_include_path());
@ini_set('include_path',PEAR_DIR.':'.get_include_path());

/* MOD START */

define ('RATING_BLANK','3');

/* Release 2.0 */

define('INVALID_SPAMCODE','121');
define('VIDEO_LOADED', '124');
define('FAILED_VIDEO_UPLOAD', '125');
define('ABOUT_ME_MANDATORY','126');
define('COUPLE_USERNAMES_MISSING','128');
define('RESTRICTED_PROFILE','28');

?>
