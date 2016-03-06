<?php
if ( !defined( 'SMARTY_DIR' ) ) {
	include_once( 'init.php' );
}


if( $_GET['username'] != '') {
	$userid = $db->getOne( 'SELECT id FROM ! WHERE username = ? AND status = ? ', array( USER_TABLE, $_GET['username'], 'active' ) );
     $_REQUEST['id'] = $userid;
}


if( $_REQUEST['id'] != '' && (int)$_REQUEST['id'] == -1 ){

	$t->assign( 'err', RESTRICTED_PROFILE );

	$t->display( 'profileview.tpl' );

	exit;

} elseif( $_REQUEST['id'] != '' && (int)$_REQUEST['id'] != 0 ){

	if ($_REQUEST["action"] == "removecomment") {
		$sql = 'UPDATE ! SET reply = ? WHERE id = ?';

		$db->query( $sql, array( USER_RATING_TABLE, '', $_REQUEST["commentid"] ) );

	}

/*	$sql = 'SELECT id, username , level, country , firstname , lastname, gender , lookgender, state_province , lastvisit, about_me, couple_usernames,
		picture , city , floor(period_diff(extract(year_month from NOW()),extract(year_month from birth_date))/12)  as age
		FROM ! WHERE id = ? AND status <> ?';
*/
	$sql = 'SELECT id, username , level, country , firstname , lastname, gender , lookgender, state_province , lastvisit, about_me, couple_usernames,
		picture , city , floor((to_days(curdate())-to_days(birth_date))/365.25)  as age
		FROM ! WHERE id = ? AND status <> ?';
     $user=$db->getRow( $sql ,array( USER_TABLE, $_REQUEST['id'], 'suspend' ));

	/* Get countryname and statename */
	$countryname = $db->getOne('select name from ! where code = ?', array(COUNTRIES_TABLE, $user['country'] ) );

	$statename = $db->getOne('select name from ! where code = ? and countrycode = ?', array(STATES_TABLE, $user['state_province'], $user['country'] ) );

	$is_banned = $db->getOne('select count(*) from ! where act = ? and ((username = ? and ref_username = ?) or (username = ? and ref_username = ?))',array(BUDDY_BAN_TABLE, 'B', $_SESSION['UserName'], $user['username'],$user['username'], $_SESSION['UserName']) );

	$user['countryname'] = $countryname;

	$user['statename'] = ($statename != '') ? $statename : $user['state_province'];

	$user['is_banned'] = $is_banned;

	$user['m_status'] = checkOnlineStats( $user['id']  );

	$user['pub_pics'] = $db->getAll('select picno from ! where userid=? and (album_id is null or album_id = 0) order by picno', array(USER_SNAP_TABLE, $user['id']) );

	$sqlSections = 'SELECT * FROM ! WHERE enabled = ? ORDER BY displayorder';

	$dataSections = $db->getAll( $sqlSections, array( SECTIONS_TABLE, 'Y'  ) );

	$found = false;

	foreach( $dataSections as $section ){
		$prefs = array();

		$sqlpref = 'SELECT DISTINCT q.id, q.question, q.extsearchhead,
			q.control_type as type FROM ! pref INNER JOIN ! q ON pref.questionid = q.id WHERE pref.userid = ? AND q.section = ? ORDER BY q.displayorder ';

          $rsPref = $db->getAll( $sqlpref,array( USER_PREFERENCE_TABLE, QUESTIONS_TABLE, $_REQUEST['id'], $section['id'] ) );

		foreach( $rsPref as $row ){

			if ($_SESSION['opt_lang'] != 'english') {
			/* THis is made to adjust for multi-language */
				$lang_question = $_SESSION['profile_questions'][$row['id']]['question'];
				$lang_extsearchhead = $_SESSION['profile_questions'][$row['id']]['extsearchhead'];
				if ($lang_question != '') {
					$row['question'] = $lang_question;
					$row['extsearchhead'] = $lang_extsearchhead;
				}
			}

			if ($row['type'] != 'textarea') {

				$sqlopt = 'SELECT distinct pref.answer as answer, opt.answer as anstxt from ! pref left join ! opt on pref.questionid = opt.questionid and opt.id = pref.answer where pref.userid = ? and opt.questionid = ? order by opt.questionid, opt.displayorder';

                    $rsOptions = $db->getAll( $sqlopt, array( USER_PREFERENCE_TABLE, OPTIONS_TABLE, $_REQUEST['id'], $row['id'] ) );

			} else {

				$sqlopt = 'select distinct pref.answer as answer, pref.answer as anstxt from ! pref where pref.userid = ? and pref.questionid = ?';

                    $rsOptions = $db->getAll( $sqlopt, array( USER_PREFERENCE_TABLE, $_REQUEST['id'], $row['id'] ) );
			}

			$opts = array();
			foreach( $rsOptions as $key=>$opt ){
				if ($_SESSION['opt_lang'] != 'english') {
				/* THis is made to adjust for multi-language */
					$lang_ansopt = $_SESSION['profile_questions'][$row['id']][$opt['answer']];
					if ($lang_ansopt != '') {$opts[] = $lang_ansopt;
					}else{ $opts[] = $opt['anstxt'];}
				} else {
					$opts[] = $opt['anstxt'];
				}
			}

			if (count($opts)>0) {
				$optsPhr = implode( ', ', $opts);
			} else {
				$optsPhr = "";
			}

			$row['options'] = $optsPhr;

			$prefs[] = $row;

			$found = true;
		}

		if( count($prefs) > 0 ){

			$pref[] = array( 'SectionName' => get_lang('sections',$section['id']), 'preferences' => $prefs );
		}
	}


	/* Get snaps count */
     $snaps_cnt = $db->getOne('select count(*) from ! where userid = ?', array( USER_SNAP_TABLE, $_REQUEST['id'] ) );

	 $t->assign('snaps_cnt', $snaps_cnt);

     $videos_cnt = $db->getOne('select count(*) from ! where userid = ?', array( USER_VIDEOS_TABLE, $_REQUEST['id'] ) );

	 $t->assign('videos_cnt', $videos_cnt);

     $in_savedprofiles = $db->getOne('select count(*) from ! where userid = ? and ref_userid = ?', array( USER_WATCHED_PROFILES, $_SESSION['UserId'], $_REQUEST['id'] ) );

	$t->assign('in_savedprofiles', $in_savedprofiles);

	hasRight('');
	$cplusers = array();

	if ($user['couple_usernames'] != '' && $user['gender'] == 'C') {

		foreach (explode(',',$user['couple_usernames']) as $cpl) {
			$refuid = $db->getOne('select id from ! where username = ?', array(USER_TABLE, trim($cpl)));

			$cplusers[]=array('username' => trim($cpl),
								'uid' => $refuid) ;
		}

		$user['cplusers'] = $cplusers;
	}

	$t->assign( 'user', $user );

	$t->assign('title',str_replace('USERNAME', $user['username'], get_lang('profile_s')) );

	$arr = array();

	for( $i=-5; $i<=5; $i++ ) {
		$arr[$i] = $i;
	}

	$t->assign ( 'rate_values', $arr );

	/* MOD START */

	// remove comment //

	// record user rating //

	$rt = $_REQUEST['txtrating'] + 0;

	if ($_GET["action"] == "rate" and ($rt > 0 && $rt <= 10)) {

		$alreadyrated = $db->getOne('select count(ratingid) from ! where userid = ? and profileid = ? limit 1', array(USER_RATING_TABLE,$_SESSION['UserId'],$_GET['id']) );
		if ($alreadyrated <= 0) {
			$sql = 'INSERT INTO ! ( userid, profileid, rating, rate_time, ratingid, rating_date ) VALUES (  ?, ?, ?, ?, ?, ? )';

			$db->query( $sql, array( USER_RATING_TABLE, $_SESSION['UserId'], $_GET['id'], $rt, time(), $_GET['ratingid'], date("Y/m/d") ) );
		}
	}

	// record comment //

	if ($_GET["action"] == "comment") {

		$commented = $db->getOne('select comment from ! where userid = ? and profileid = ? and ratingid = ?', array(USER_RATING_TABLE,$_SESSION['UserId'], $_GET['id'],$_GET['ratingid']) );
		if (!isset($commented) or $commented == '') {
			$sql = 'INSERT INTO ! ( userid, profileid, rating, rate_time, ratingid, comment, comment_date ) VALUES (  ?, ?, ?, ?, ?, ?, ? )';

			$db->query( $sql, array( USER_RATING_TABLE, $_SESSION['UserId'], $_REQUEST['id'], '0', time(), $_GET['ratingid'], substr(strip_tags($_POST["txtcomment"]),0,250), date("Y/m/d") ) );
		}
	}

	// record reply //

	if ($_GET["action"] == "reply") {

		$sql = 'UPDATE ! SET reply = ? WHERE id = ?';

		$db->query( $sql, array( USER_RATING_TABLE, $_POST["txtcomment"], $_GET["commentid"] ) );

	}

	// get ratings //

     $t->assign( 'profileid', $_REQUEST['id'] );

	$t->assign( 'ratingid', $_GET['ratingid'] );

	$sqlrating = 'SELECT id, rating, displayorder, enabled, description from ! where enabled = ? order by displayorder asc ';;

	$data = $db->getAll( $sqlrating, array(RATINGS_TABLE, 'Y') );

	$total_ratingscnt = 0;

	$newdata = array();

	foreach ($data as $item) {

		// comment count //

		$futuredate1 = date("Y/m/d", mktime(0,0,0,date("m"),(date("d") - $config['mod_rating_rem_com']),date("Y")));

		$sqlrate = 'SELECT count(id) as commentcount FROM ! WHERE profileid = ? and ratingid = ? and comment <> ? and comment_date >= ?';

          $commentcount = $db->getOne($sqlrate, array( USER_RATING_TABLE, $_REQUEST['id'], $item["id"], '', $futuredate1 ) );

		$item["commentcount"] = $commentcount;

		// rating count //

		$futuredate2 = date("Y/m/d", mktime(0,0,0,date("m"),(date("d") - $config['mod_rating_rem_rat']),date("Y")));

		$sqlrate = 'SELECT count(id) as ratingcount FROM ! WHERE profileid = ? and ratingid = ? and rating > ? and rating_date >= ?';

          $ratingcount = $db->getOne($sqlrate, array( USER_RATING_TABLE, $_REQUEST['id'], $item["id"], '0', $futuredate2 ) );

		$item["ratingcount"] = $ratingcount;

		$total_ratingscnt += $ratingcount;

		// rating value //

		$sqlrate = 'SELECT count(rating) as tv , sum(rating) as sm FROM ! WHERE profileid = ? and ratingid = ? and rating > ? and rating_date >= ?';

          $rowrate = $db->getRow($sqlrate, array( USER_RATING_TABLE, $_REQUEST['id'], $item["id"], '0', $futuredate2 ) );

		$tv = $rowrate['tv'];

		$sm = $rowrate['sm'];

		if ( $tv == 0 ) {

			$ratingvalue = 0;

		} else {

			$tv = ($tv == 0) ? 1 : $tv;

			$ratingvalue = round( $sm / $tv );

		}

		$item["ratingvalue"] = $ratingvalue;

		// check user has already rated //

		$has_rated = 1;

		if ( $_SESSION['UserId'] and $_SESSION['UserId'] != $_GET["id"] ) {

			$sqlcrate = 'SELECT count(*) as c  FROM !  WHERE userid = ? AND profileid = ? and ratingid = ? and rating > ?';

               $rowcrate = $db->getRow(  $sqlcrate, array( USER_RATING_TABLE, $_SESSION['UserId'], $_REQUEST['id'], $item["id"], '0' ));

			$c = $rowcrate['c'];

			if ( $c == 0 ) {
				$has_rated = 0;
			}else {
				$has_rated = 1;
			}

		}

		$item["has_rated"] = $has_rated;

		// check if user has already commented //

		$has_commented = 1;

		if ( $_SESSION['UserId'] and $_SESSION['UserId'] != $_GET["id"] ) {

			$sqlcrate = 'SELECT count(*) as c  FROM !  WHERE userid = ? AND profileid = ? and ratingid = ? and comment <> ?';

               $rowcrate = $db->getRow(  $sqlcrate, array( USER_RATING_TABLE, $_SESSION['UserId'], $_REQUEST['id'], $item["id"], '' ));

			$c = $rowcrate['c'];

			if ( $c == 0 ) {
				$has_commented = 0;
			}else {
				$has_commented = 1;
			}

		}

		$item["has_commented"] = $has_commented;

		array_push($newdata, $item);

	}

	$t->assign('total_ratingscnt', $total_ratingscnt);

	$t->assign( 'ratings', $newdata );

	// get options //

	$optionlist = array();
	$optionlist_note = array();

	$div = $config['mod_rating_inc'] - 1;

	for($i=$config['mod_rating_min']; $i<=$config['mod_rating_max']; $i++) {

		$div++;

		if ($i == $config['mod_rating_min']) {

			$thename = "&nbsp;".get_lang('worst1');

		} else if ($i == $config['mod_rating_max']) {

			$thename = "&nbsp;".get_lang('best1');

		} else {

			$thename = "";

		}

		if ($div == $config['mod_rating_inc']) {

		$temparray = array();

		$temparray["name"] = $i . $thename;
		$temparray["value"] = $i;

		array_push($optionlist, $temparray);

		$div = 0;

		}

	}

	if ($config['mod_rating_inc_order'] == "High to Low") {

		$optionlist = array_reverse($optionlist);

	}

	$t->assign( 'ratingoptions', $optionlist );

	// get comments //

	$sqlrate = 'SELECT distinct rat.id, rat.comment, rat.reply, rat.userid, usr.username FROM ! as rat, ! as usr WHERE rat.profileid = ? and rat.ratingid = ? and rat.comment <> ? and rat.comment_date >= ? and usr.id = rat.userid';

     $comments = $db->getAll($sqlrate, array( USER_RATING_TABLE, USER_TABLE, $_REQUEST['id'], $_GET['ratingid'], '', $futuredate1 ) );

	$t->assign( 'comments', $comments );

	/* MOD END */

	if( $found ){

		$t->assign ( 'found', 1);

		$t->assign( 'pref', $pref);

	}

	$sql = 'select count(*) from ! where userid = ? and act = ?';
     $t->assign('profile_views', $db->getOne($sql, array( VIEWS_WINKS_TABLE, $_REQUEST['id'], 'V' ) ) );

	/* Now add this view to profile_views table, if no user logged, make it -1  */

	$sql = 'insert into ! (userid, ref_userid, act_time, act) values (?, ?, ?, ?)';

	$byuser = ($_SESSION['UserId']>0)?$_SESSION['UserId']:-1;

     if ($_REQUEST['id'] != $_SESSION['UserId'] && !isset($_REQUEST['ratingid']) ) {

          $db->query($sql, array( VIEWS_WINKS_TABLE, $_REQUEST['id'], $byuser, time(), 'V' ) );

	}

	$t->assign('errid', $_GET['errid']);

      //     $t->display( 'profileview.tpl' );


      // If there's a blog show this user the blog link
      //
      include_once(LIB_DIR . 'blog_class.php');

      $blog = new Blog();

      if ( $blog->blogExist($_REQUEST['id'])  ) {

         $view_blog = get_lang('view_blog');
         $blog->loadSettings($_REQUEST['id']);

         $t->assign('blogs', $blog->getAllStories($_REQUEST['id']) );
         $t->assign('bpref',  $blog->getSettings() );
         $t->assign('lang',  $lang );
      }
      // Make the blog sort links
      //
      $blog->sort_page_values = array(
                                      'id'   => $_REQUEST['id'],
      );
      $blog->sort_page = 'showprofile.php';
      $t->assign('sort_blog_views',   $blog->SortLink(get_lang('blog_views_hdr'),'views') );
      $t->assign('sort_blog_ratings', $blog->SortLink(get_lang('blog_rating_list_hdr'),'votes') );
      $t->assign('sort_blog_title',   $blog->SortLink(get_lang('blog_title_hdr'),'title') );
      $t->assign('sort_date_posted',  $blog->SortLink(get_lang('blog_date_posted_hdr'),'date_posted') );


      // If there's a poll to show, get it
      //
      include(LIB_DIR . 'poll_class.php');

      $poll = new Poll();

      if ( $_POST['action'] == 'vote_poll' ) {

         $poll->saveVote($_SESSION['UserId']);
      }

      $poll->loadRandPoll($_REQUEST['id']);
      $question = $poll->getQuestion();
      $answer   = $poll->getAnswer();

      $t->assign( 'questionid', $question['id'] ) ;
      $t->assign( 'question',   $question);
      $t->assign( 'answer',     $answer );
      $t->assign( 'profileid',  $_REQUEST['id'] );


		$t->assign('lang',$lang);

	if ( $config['use_profilepopups'] == 'Y' ) {
		$cached_data = $t->fetch( 'nickpage.tpl' );
	}
	else {
		$t->assign('rendered_page', $t->fetch('nickpage.tpl') );
		$cached_data = $t->fetch( 'index.tpl' );
	}
	if ($_SESSION['UserId'] == '' || !isset($_SESSION['UserId'])) {
	/* Cache checking enabled only for general public i.e. the user is not logged in */

		require_once FULL_PATH.'includes/internal/osdate_save_cache.php';

	}
	echo($cached_data);

}
?>
