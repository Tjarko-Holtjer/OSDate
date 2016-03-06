<?php
if ( !defined( 'SMARTY_DIR' ) ) {

     include_once( 'init.php' );


}

include( 'sessioninc.php' );

$data = array();

$userid = $_SESSION['UserId'];

$sectionid = $_REQUEST[ 'sectionid' ];

if ($_REQUEST['search_new'] == 1 or (!isset($sectionid) && !isset($_REQUEST['advsearch']) && !isset($_SESSION['advsearch']) ) ) {

    $sectionid = 0;

    $_SESSION['advsearch'] = array();

    $_SESSION['advsearch']['srchlookagestart'] = $config['end_year']*-1;

    $_SESSION['advsearch']['srchlookageend'] = $config['start_year']*-1;

    $_SESSION['advsearch']['srchgender'] = $usergender;
}

$get_search = $_REQUEST['get_search'];

if (isset($_REQUEST['del_search']) && $_REQUEST['del_search'] != '' && $_REQUEST['search_name'] != '') {
    /* delete the saved search */

    $db->query('delete from ! where userid = ? and search_name = ?', array(USER_SEARCH_TABLE, $_SESSION['UserId'], $_REQUEST['search_name']) );

}

if (isset($get_search) && $get_search != '' ){

    /* Get  saved search and populate properly  */
    $_SESSION['search_name'] = $_REQUEST['search_name'];

    $_REQUEST['save_type'] = 'R';

    $search_text = $db->getOne('select query from ! where userid=? and search_name=?', array(USER_SEARCH_TABLE, $_SESSION['UserId'], $_REQUEST['search_name']) );

	$_SESSION['advsearch'] = array();

    $_SESSION['advsearch'] = unserialize($search_text);

    $srchlookcountry = $_SESSION['advsearch']['srchlookcountry'];

    $srchlookcounty = $_SESSION['advsearch']['srchlookcounty'];

    $srchlookstate_province = $_SESSION['advsearch']['srchlookstate_province'];

    $srchlookcity = $_SESSION['advsearch']['srchlookcity'];

    $srchlookzip = $_SESSION['advsearch']['srchlookzip'];

	$with_photo = $_SESSION['advsearch']['with_photo'];

	$with_video = $_SESSION['advsearch']['with_video'];

	$who_is_online = $_SESSION['advsearch']['who_is_online'];

    $sectionid = 0;

    if ($srchlookcountry != '' && $srchlookcountry != 'AA') {

        $lang['lookstates'] = getStates($srchlookcountry,'Y');

        $zipsavailable = $db->getOne('select count(*) from ! where countrycode = ?', array(ZIPCODES_TABLE, $srchlookcountry) );

        $t->assign('zipsavailable', $zipsavailable);

        if (count($lang['lookstates']) == 1) {
            foreach ($lang['lookstates'] as $key => $val) {
                $_SESSION['advsearch']['srchlookstate_province'] = $srchlookstate_province = $key;
            }
        }

        if ($srchlookstate_province != '' ) {

            $lang['lookcounties'] = getCounties($srchlookcountry, $srchlookstate_province, 'Y');

            if (count($lang['lookcounties']) == 1) {
                foreach ($lang['lookcounties'] as $key => $val) {
                    $_SESSION['advsearch']['srchlookcounty'] = $srchlookcounty = $key;
                }
            }

            if ($srchlookcounty != '') {

                $lang['lookcities'] = getCities($srchlookcountry, $srchlookstate_province, $srchlookcounty, 'Y');

                if (count($lang['lookcities']) == 1) {
                    foreach($lang['lookcities'] as $key => $val) {
                        $_SESSION['advsearch']['srchlookcity'] = $srchlookcity = $key;
                    }
                }

                if ($srchlookcity != '') {

                    $lang['lookzipcodes'] = getZipcodes($srchlookcountry, $srchlookstate_province, $srchlookcounty, $srchlookcity, 'Y');
                }
            }
        }
    }

}

if (isset($_REQUEST['save_type'])) {

    $_SESSION['search_save_type'] = $_REQUEST['save_type'];
}

if (isset($_REQUEST['search_name'])) $_SESSION['search_name'] = $_REQUEST['search_name'];

if (isset($_REQUEST['new_name'])) {

    $_SESSION['search_new_name'] = $_REQUEST['new_name'];

} elseif (!isset($_SESSION['search_name']) or $_SESSION['search_name'] == '') {

    $_SESSION['search_new_name'] = '';

    $_SESSION['search_save_type'] = '';

}

$usergender = $db->getOne('select gender from ! where id = ?', array(USER_TABLE, $_SESSION['UserId']) );

/*  Query to reterive records from osdate_questions table
 sorted descending on mandatory -  mandatory fields should be displayed first
*/

if ((!isset($get_search) || $get_search == '') || isset($_REQUEST['advsearch'])) {

    if ($_REQUEST['cursectionid'] == '0' ) {
        /* Save data from section 0 - signup data */

        $_SESSION['advsearch']['srchusername'] = $_REQUEST['srchusername'];


        if (isset($_REQUEST['srchgender']) ) {

            $_SESSION['advsearch']['srchgender'] = $_REQUEST['srchgender'];

        }

        if (isset($_REQUEST['srchlookgender']) ) {

            $_SESSION['advsearch']['srchlookgender'] = $_REQUEST['srchlookgender'];

        } else {
		    unset($_SESSION['advsearch']['srchlookgender']);
		}

        $_SESSION['advsearch']['srchlookagestart'] = $_REQUEST['srchlookagestart'];

        $_SESSION['advsearch']['srchlookageend'] = $_REQUEST['srchlookageend'];

        $_SESSION['advsearch']['srchradius'] = $_REQUEST['srchradius'];

        $_SESSION['advsearch']['radiustype'] = $_REQUEST['radiustype'];

		$_SESSION['advsearch']['with_photo'] = $_REQUEST['with_photo'];

		$_SESSION['advsearch']['with_video'] = $_REQUEST['with_video'];

		$_SESSION['advsearch']['who_is_online'] = $_REQUEST['who_is_online'];

        $_SESSION['advsearch']['srchlookcountry'] = $srchlookcountry = $_REQUEST['srchlookcountry'];

        $_SESSION['advsearch']['srchlookcounty'] = $srchlookcounty = $_REQUEST['srchlookcounty'];

        $_SESSION['advsearch']['srchlookstate_province'] = $srchlookstate_province = $_REQUEST['srchlookstate_province'];

        $_SESSION['advsearch']['srchlookcity'] = $srchlookcity = $_REQUEST['srchlookcity'];

        $_SESSION['advsearch']['srchlookzip'] = $srchlookzip = strtoupper($_REQUEST['srchlookzip']);

    } elseif ($_REQUEST['cursectionid'] > 0 && ( isset($_REQUEST['question']) || isset($_REQUEST['selected_questions']) ) ) {
	/* Check already selected options and if they are unchecked, remove session settings */
		if (isset($_REQUEST['selected_questions'])) {
			foreach ($_REQUEST['selected_questions'] as $k => $q) {
				$_SESSION['advsearch']['question'][$q]=null;
			}
		}

		if (isset($_REQUEST['question']) ) {
	        foreach ($_REQUEST['question'] as $mkey => $val) {
	 			$_SESSION['advsearch']['question'][$mkey] = $val;
			}
        }

    }
}
// edit by Adam
// make dropdowns propogate across sections
// pulled this code out of the above cursectionid==0 if block
// and moved down here so assignments were made and relations were kept outside of that section.

$srchlookcountry = $_SESSION['advsearch']['srchlookcountry'];

$srchlookcounty = $_SESSION['advsearch']['srchlookcounty'];

$srchlookstate_province = $_SESSION['advsearch']['srchlookstate_province'];

$srchlookcity = $_SESSION['advsearch']['srchlookcity'];

$srchlookzip = $_SESSION['advsearch']['srchlookzip'];

$with_photo = $_SESSION['advsearch']['with_photo'];

$with_video = $_SESSION['advsearch']['with_video'];

$who_is_online = $_SESSION['advsearch']['who_is_online'];

if ($srchlookcountry != '' && $srchlookcountry != 'AA') {

	$lang['lookstates'] = getStates($srchlookcountry,'Y');

	$zipsavailable = $db->getOne('select count(*) from ! where countrycode = ?', array(ZIPCODES_TABLE, $srchlookcountry) );

	$t->assign('zipsavailable', $zipsavailable);

	if (count($lang['lookstates']) == 1) {
		foreach ($lang['lookstates'] as $key => $val) {
			$_SESSION['advsearch']['srchlookstate_province'] = $srchlookstate_province = $key;
		}
	}

	if ($srchlookstate_province != '' && $srchlookstate_province != 'AA') {

		$lang['lookcounties'] = getCounties($srchlookcountry, $srchlookstate_province, 'Y');

		if (count($lang['lookcounties']) == 1) {
			foreach ($lang['lookcounties'] as $key => $val) {
				$_SESSION['advsearch']['srchlookcounty'] = $srchlookcounty = $key;
			}
		}

		if ($srchlookcounty != '' && $srchlookcounty != 'AA') {

			$lang['lookcities'] = getCities($srchlookcountry, $srchlookstate_province, $srchlookcounty, 'Y');

			if (count($lang['lookcities']) == 1) {
				foreach($lang['lookcities'] as $key => $val) {
					$_SESSION['advsearch']['srchlookcity'] = $srchlookcity = $key;
				}
			}

			if ($srchlookcity != '' && $srchlookcity != 'AA') {

				$lang['lookzipcodes'] = getZipcodes($srchlookcountry, $srchlookstate_province, $srchlookcounty, $srchlookcity, 'Y');
			}
		}
	}
}

//
// end of adam edits
//

if ( isset( $_REQUEST['results_per_page'] ) && $_REQUEST['results_per_page'] ) {

    $psize = $_REQUEST['results_per_page'];

    $config['search_results_per_page'] = $_REQUEST['results_per_page'] ;

    $_SESSION['ResultsPerPage'] = $_REQUEST['results_per_page'];

} elseif ( $_SESSION['ResultsPerPage'] != '' ) {

    $psize = $_SESSION['ResultsPerPage'];

    $config['search_results_per_page'] = $_SESSION['ResultsPerPage'] ;

} else {

    $psize = $config['search_results_per_page'];

    $_SESSION['ResultsPerPage'] = $config['search_results_per_page'];
}

$t->assign ( 'psize',  $psize );


if (isset($_REQUEST['advsearch'])  ){
/* Search is requested. Now let us select data and display. Output in sqlselect. */

    if (!isset($_REQUEST['sort_by'])) {
    /* First time search actiated.. Prepare query */

 	       /* if not a blog search, do it like this */
 	       $sort_by = ' username ';

 	       $sort_order = ' asc ';

 	       if ($_SESSION['advsearch']['srchradius'] != '') {
 		       /* zipcode proximity search */
 	           /* First get the latitude and longitude of the zip code entered */
 	           $cntrycode=($_SESSION['advsearch']['srchlookcountry']!='AA')?$_SESSION['advsearch']['srchlookcountry']:$config['default_country'];

 	           $srchzip = $_SESSION['advsearch']['srchlookzip'];

 	           if ($cntrycode == 'GB') {
 	               $ukzip = explode(' ',$_SESSION['advsearch']['srchlookzip']);
 	               $srchzip = $ukzip[0];
 	           }

 	           $row = $db->getRow('select * from ! where code=?  and countrycode=?',array(ZIPCODES_TABLE, $srchzip, $cntrycode ) );

 	           $lat = $row['latitude'];
 	           $lng = $row['longitude'];

 	           $zipcodes_in = "";

 	           if ($lng!='' && $lat!='') {

 	               $radius = $_SESSION['advsearch']['srchradius'];
 	               $radiustype = $_SESSION['advsearch']['radiustype'];

 	               if ($radiustype == 'kms') {
                    /* Kilometers calculation */
 	                   $sql = "select DISTINCT code, sqrt(power(69.1*(latitude - $lat),2) +  power( 69.1 * (longitude-$lng) * cos(latitude/57.3),2)) as dist from ! where countrycode=? and sqrt(power(69.1*(latitude - $lat),2)+power(69.1*(longitude-$lng)*cos(latitude/57.3),2)) < " . $radius ;
 	               } else {
                    /* Miles  */
 	                   $sql = "select DISTINCT code, (3958* 3.1415926 * sqrt((latitude - $lat) * (latitude- $lat) + cos(latitude / 57.29578) * cos($lat/57.29578)*(longitude - $lng) * (longitude - $lng))/180) as dist from ! where countrycode=? and (3958* 3.1415926 * sqrt((latitude - $lat) * (latitude- $lat) + cos(latitude / 57.29578) * cos($lat/57.29578)*(longitude - $lng) * (longitude - $lng))/180) < " . $radius ;
 	               }

 	               $zipcodes = $db->getAll($sql, array(ZIPCODES_TABLE, $cntrycode) );

 	               foreach ($zipcodes as $val) {
 	                   if ($zipcodes_in != '') $zipcodes_in.=", ";
 	                   $zipcodes_in.= "'".strtoupper($val['code'])."'";
 	               }

 	               if ($cntrycode == 'GB') {

 	                   $zipcodes_in = " substr(upper(user.zip),1,instr(user.zip,' ')) in (".$zipcodes_in.") ";

 	               } else {

 	                   $zipcodes_in = " upper(user.zip) in (".$zipcodes_in.") ";

 	               }

 	               $_SESSION['advsearch']['zipcodes_in']=$zipcodes_in;

				}
			}

	        $prefsel = "";

	        $questionmatch='';

	        $questionusers = array();

	        $_SESSION['advsearch']['questionusers']=array();
	        $_SESSION['advsearch']['questionmatch']='';
	        if (count($_SESSION['advsearch']['question']) > 0){

				$match_needed = 0;

		        /* Let us make the question query */

	            foreach($_SESSION['advsearch']['question'] as $questionid => $options) {

	                $opts = '';
					if (count($options) <= 0) {
						$_SESSION['advsearch']['question'][$questionid] = '';
					} else {
		                foreach ($options as $k => $val) {

		                    if ($val != '') {

		                        if ($opts != '' ) $opts.=', ';

		                        $opts .= "'".$val."'";
		                    }
		                }
					}
	                if ($opts != '') {
						$match_needed++;

	                    if ($prefsel != '') { $prefsel .= ' or '; }

	                    $prefsel .= " ( pref.questionid = ".$questionid." and pref.answer in ( ".$opts." ) ) ";
	                }
	            }

	            if ($prefsel != '') {

	                $qry= "select distinct userid, count(questionid) as match_cnt from ! as pref where 1 and ".$prefsel." group by  userid";

	                $questionusers=$db->getAll($qry,array(USER_PREFERENCE_TABLE) );

	                if (count($questionusers) > 0) {
						$met_condition = 0;
	                    foreach ($questionusers as $qst) {

							if ($qst['match_cnt'] == $match_needed) {
								$met_condition=1;
		                        if ($questionmatch != '') $questionmatch .= ", ";
		                        $questionmatch.= "'".$qst['userid']."'";
							}
	                    }


						if ($met_condition > 0 ) {
		                    $questionmatch = ' user.id in ('.$questionmatch.') ';
						} else {
							$questionmatch =" user.id in ('9999999999') ";
						}

	                    $_SESSION['advsearch']['questionusers']=$questionusers;
	                    $_SESSION['advsearch']['questionmatch']=$questionmatch;
	                } else {
						$questionmatch =" user.id in ('9999999999') ";
	                    $_SESSION['advsearch']['questionmatch']=$questionmatch;
					}
	            } else {
                    $_SESSION['advsearch']['questionusers']=array();
                    $_SESSION['advsearch']['questionmatch']='';
				}
	        }

	     /* Make a banned users list */
	        $bannedlist = '';
	        $bannedusers = $db->getAll('select usr.id as ref_userid from ! as bdy, ! as usr where bdy.act=? and ((usr.username = bdy.username and bdy.ref_username = ?) or (usr.username = bdy.ref_username and bdy.username = ? ) )', array(BUDDY_BAN_TABLE, USER_TABLE, 'B', $_SESSION['UserName'], $_SESSION['UserName']) );
	        if (count($bannedusers) > 0) {
	            $bannedlist=' and user.id not in (';
	            $bdylst = '';
	            foreach ($bannedusers as $busr) {
	                if ($bdylst != '') $bdylst .= ',';
	                $bdylst .= "'".$busr['ref_userid']."'";
	            }
	            $bannedlist .=$bdylst.') ';
	        }

	        $actflag = get_lang('active');

			if ($with_photo) {
				if ($with_video) {
					if ($who_is_online) {
				        $sqlselect = "SELECT DISTINCT user.id, user.username, user.gender, user.lastvisit, user.country, user.about_me, user.state_province, user.county, user.city, user.lookgender, user.zip, onl.is_online, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! mem, ! user, ! as onl, ".USER_SNAP_TABLE." as ph, ".USER_VIDEOS_TABLE." as vd where user.id <> ? and ph.userid=user.id and vd.userid = user.id and onl.userid=user.id and user.level=mem.roleid and mem.includeinsearch='1' and user.active=1 and user.status in ('active', '".$actflag."')  ";
					} else {
				        $sqlselect = "SELECT DISTINCT user.id, user.username, user.gender, user.lastvisit, user.country, user.state_province,  user.about_me, user.county, user.city, user.lookgender, user.zip, onl.is_online, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! mem,".USER_SNAP_TABLE." as ph, ". USER_VIDEOS_TABLE." as vd, ! user left join ! as onl on onl.userid=user.id where user.id <> ? and ph.userid=user.id and vd.userid = user.id and user.level=mem.roleid and mem.includeinsearch='1' and user.active=1 and user.status in ('active', '".$actflag."')  ";
					}
				} else {
					if ($who_is_online) {
				        $sqlselect = "SELECT DISTINCT user.id, user.username, user.gender, user.lastvisit, user.country, user.state_province,  user.about_me, user.county, user.city, user.lookgender, user.zip, onl.is_online, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! mem, ! user, ! as onl, ".USER_SNAP_TABLE." as ph where user.id <> ? and ph.userid=user.id and onl.userid=user.id and user.level=mem.roleid and mem.includeinsearch='1' and user.active=1 and user.status in ('active', '".$actflag."')  ";
					} else {
				        $sqlselect = "SELECT DISTINCT user.id, user.username, user.gender, user.lastvisit, user.country, user.state_province,  user.about_me, user.county, user.city, user.lookgender, user.zip, onl.is_online, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! mem,".USER_SNAP_TABLE." as ph, ! user left join ! as onl on onl.userid=user.id where user.id <> ? and ph.userid=user.id and user.level=mem.roleid and mem.includeinsearch='1' and user.active=1 and user.status in ('active', '".$actflag."')  ";
					}
				}
			} else {
				if ($with_video) {
					if ($who_is_online) {
						$sqlselect = "SELECT DISTINCT user.id, user.username, user.gender, user.lastvisit, user.country, user.state_province,  user.about_me, user.county, user.city, user.lookgender, user.zip, onl.is_online, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! mem, ! user, ! as onl, ".USER_VIDEOS_TABLE." as vd where user.id <> ? and vd.userid = user.id and onl.userid=user.id and user.level=mem.roleid and mem.includeinsearch='1' and user.active=1 and user.status in ('active', '".$actflag."')  ";
					} else {
						$sqlselect = "SELECT DISTINCT user.id, user.username,  user.about_me, user.gender, user.lastvisit, user.country, user.state_province, user.county, user.city, user.lookgender, user.zip, onl.is_online, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ".USER_VIDEOS_TABLE." as vd, ! mem, ! user left join ! as onl on onl.userid=user.id where user.id <> ? and vd.userid = user.id and user.level=mem.roleid and mem.includeinsearch='1' and user.active=1 and user.status in ('active', '".$actflag."')  ";
					}
				} else {
					if ($who_is_online) {
						$sqlselect = "SELECT DISTINCT user.id, user.username,  user.about_me, user.gender, user.lastvisit, user.country, user.state_province, user.county, user.city, user.lookgender, user.zip, onl.is_online, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! mem, ! user, ! as onl where user.id <> ? and onl.userid=user.id and user.level=mem.roleid and mem.includeinsearch='1' and user.active=1 and user.status in ('active', '".$actflag."')  ";
					} else {
						$sqlselect = "SELECT DISTINCT user.id, user.username,  user.about_me, user.gender, user.lastvisit, user.country, user.state_province, user.county, user.city, user.lookgender, user.zip, onl.is_online, floor((to_days(curdate())-to_days(birth_date))/365.25)  as age FROM ! mem, ! user left join ! as onl on onl.userid=user.id where user.id <> ? and user.level=mem.roleid and mem.includeinsearch='1' and user.active=1 and user.status in ('active', '".$actflag."')  ";
					}
				}
			}

	        if ($_SESSION['advsearch']['questionmatch'] != '') {
	            $sqlselect .= ' and '.$_SESSION['advsearch']['questionmatch'];
	        }

			if ($_SESSION['advsearch']['zipcodes_in']!='') {
				$sqlselect .= ' and '.$_SESSION['advsearch']['zipcodes_in'];
			}

			if ($_SESSION['advsearch']['srchusername'] != '') {
				$sqlselect .= "and upper(user.username) like upper('%".$_SESSION['advsearch']['srchusername']."%') ";
			}

			if (count($_SESSION['advsearch']['srchlookgender']) > 0) {
				$lookgender='';
				foreach($_SESSION['advsearch']['srchlookgender'] as $lg) {
					if ($lookgender != '') $lookgender.=", ";
					$lookgender .= "'".$lg."'";
				}
				$sqlselect .= " and user.gender in (".$lookgender.") ";
			}

			/* Bypass cross matching in search if set in global settings or the lookgender is not accepted */
			if ( ($config['bypass_search_lookgender'] == 'N' or $config['bypass_search_lookgender'] == '0' ) and ( $config['accept_lookgender'] == 'Y' or $config['accept_lookgender'] == '1') ) {

				$txtgender_search = "and (user.lookgender = 'A' or (user.lookgender = 'B' and '".$usergender."' in ('M','F') ) or user.lookgender = '".$usergender."') ";

				$sqlselect .= $txtgender_search;
			}

			$sqlselect .= " and ( floor((to_days(curdate())-to_days(birth_date))/365.25)  between '".$_SESSION['advsearch']['srchlookagestart']."' and '". $_SESSION['advsearch']['srchlookageend']."' )";

	        if ($_SESSION['advsearch']['srchlookcountry']!='' and $_SESSION['advsearch']['srchlookcountry']!= 'AA' and $_SESSION['advsearch']['srchlookcountry']!= '-1') {
            $sqlselect .= " and user.country = '".$_SESSION['advsearch']['srchlookcountry']."' ";
	        }
	        if ($_SESSION['advsearch']['srchlookcounty']!='' and $_SESSION['advsearch']['srchlookcounty']!= 'AA' and $_SESSION['advsearch']['srchlookcounty']!= '-1') {
            $sqlselect .= " and user.county = '".$_SESSION['advsearch']['srchlookcounty']."' ";
	        }
	        if ($_SESSION['advsearch']['srchlookstate_province']!='' and $_SESSION['advsearch']['srchlookstate_province']!= 'AA' and $_SESSION['advsearch']['srchlookstate_province']!= '-1') {
	            $sqlselect .= " and user.state_province = '".$_SESSION['advsearch']['srchlookstate_province']."' ";
	        }
	        if ($_SESSION['advsearch']['srchlookcity']!='' and $_SESSION['advsearch']['srchlookcity']!= 'AA' and $_SESSION['advsearch']['srchlookcity']!= '-1') {
	            $sqlselect .= " and user.city = '".$_SESSION['advsearch']['srchlookcity']."' ";
	        }
	        if (($_SESSION['advsearch']['srchlookzip']!='' and $_SESSION['advsearch']['srchlookzip']!= 'AA' and $_SESSION['advsearch']['srchlookzip']!= '-1') and $_SESSION['advsearch']['srchradius']=='') {
	            $sqlselect .= " and user.zip = '".$_SESSION['advsearch']['srchlookzip']."' ";
	        }

	        $sqlselect .= $bannedlist;

	        $_SESSION['advsearch']['sql'] = $sqlselect;

	        $sqlselect .= ' order by '.$sort_by;
	} elseif ($_REQUEST['sort_by'] != '' or $_REQUEST['page'] != '') {

		if ($_REQUEST['page'] != '' && $_REQUEST['sort_by'] == '') { $_REQUEST['sort_by'] = $_SESSION['sort_by']; }

		$_SESSION['sort_by'] = $_REQUEST['sort_by'];

		if ($_REQUEST['sort_by'] == '') {

			$sort_by='username';

		} else {

			$sort_by=$_REQUEST['sort_by'];
		}

		if ($_REQUEST['sort_order'] == '') {

			$sort_order='asc';

		} else {

			$sort_order=$_REQUEST['sort_order'];

		}

		$sortme = " order by ";

		if ($sort_by == 'username') {

			$sortme .= 'user.username ';

		} elseif ( $sort_by == 'age' ) {

			$sortme .= ' age ';

		} elseif ( $sort_by == 'logintime' ) {

			$sortme .= 'user.lastvisit ';

		} elseif ( $sort_by == 'online' ) {

			$sortme .= ' onl.is_online desc, user.username ';
		}

		$sortme .= $sort_order;

		$sqlselect = $_SESSION['advsearch']['sql']." ".$sortme;
	}

    /* If  not blog search, do it this way.  We already have the results for the blog
      search */
    if ( $_REQUEST['cursectionid'] != 99 ) {

	    $t->assign('sort_by',$sort_by);

	    $t->assign('sort_order',$sort_order);

            /* Actually perform the search query to see what info we will get */
	    $rs = $db->query($sqlselect,array(MEMBERSHIP_TABLE, USER_TABLE, ONLINE_USERS_TABLE, $_SESSION['UserId']));

	    $cpage = $_REQUEST['page'];

	    $lang['sort_types'] = get_lang_values('sort_types');

	    $lang['search_results_per_page'] = get_lang_values('search_results_per_page');

	    if( $cpage == '' ) $cpage = 1;

		$rcount = $rs->numRows();

		if( $rcount > 0 ) {

			$t->assign( 'totalrecs', $rcount );

			$pages = ceil( $rcount / $psize );

			$start = ( $cpage - 1 ) * $psize;

			$t->assign ( 'start', $start );

			if( $pages > 1 ) {

				if ( $cpage > 1 ) {

					$prev = $cpage - 1;

					$t->assign( 'prev', $prev );

				}

				$t->assign ( 'cpage', $cpage );

				$t->assign ( 'pages', $pages );

				if ( $cpage < $pages ) {

					$next = $cpage + 1;

					$t->assign ( 'next', $next );

				}
			}

			$sqlselect .= " limit $start,$psize"    ;
		}

		/* Perform the search query again to get the info */
		$rs = $db->getAll( $sqlselect, array( MEMBERSHIP_TABLE, USER_TABLE, ONLINE_USERS_TABLE, $_SESSION['UserId'] ) );

		$data = array();

		if( $rs) {
			foreach( $rs as $row) {

				$row['countryname'] = $db->getOne('select name from ! where code = ?', array( COUNTRIES_TABLE, $row['country'] ) );

				$row['statename'] = $db->getOne('select name from ! where code = ? and countrycode = ?', array( STATES_TABLE, $row['state_province'], $row['country'] ) );

				$row['statename'] = ($row['statename'] != '') ? $row['statename'] : $row['state_province'];

				if (count($_SESSION['advsearch']['questionusers']) > 0 ) {
					foreach ($_SESSION['advsearch']['questionusers'] as $usr) {
						if ($usr['userid'] == $row['id']) {
							$row['matchcnt'] = $usr['match_cnt'];
						}
					}
				}

						  /* Save the search results into data */
				$data[] = $row;
			}
		} else {
			$t->assign( 'error', 1);
		}



		if (($_SESSION['search_save_type'] == 'N' or $_SESSION['search_save_type'] == 'R') && (!isset($get_search) or $get_search == '')   ) {

			/* Save this search */
			if ($_SESSION['search_save_type'] == 'N') {
				$srch_name = $_SESSION['search_new_name'];
			} else {
				$srch_name = $_SESSION['search_name'];
			}

			$qry_txt = serialize($_SESSION['advsearch']);


			$rec_available = $db->getOne('select 2 from ! where userid=? and search_name=?', array(USER_SEARCH_TABLE, $_SESSION['UserId'], trim($srch_name)) );

			if (isset($rec_available) && $rec_available==2) {
				$db->query('update ! set query=? where userid=? and search_name=?', array(USER_SEARCH_TABLE, $qry_txt, $_SESSION['UserId'], trim($srch_name)) );
			} else {
				$db->query('insert into ! (userid, search_name, query) values (?, ?, ?)', array(USER_SEARCH_TABLE, $_SESSION['UserId'], trim($srch_name), $qry_txt) );
			}

			$_SESSION['search_name'] = $srch_name;

			$_SESSION['search_new_name'] = '';

			$_SESSION['save_type'] = 'R';

		}

	}

    $lang['sort_types'] = get_lang_values('sort_types');

	$t->assign ( 'data', $data );

	$t->assign ( 'lang', $lang );

	$t->assign('rendered_page', $t->fetch('advmatch.tpl') );

    $t->display ( 'index.tpl' );

    exit;

}

if ($sectionid > 0 ) {
	$currdisplayorder = $db->getOne('select displayorder from ! where id=?', array(SECTIONS_TABLE, $sectionid) );

	$nextsectionid = $db->getOne('select id from ! where displayorder > ? and enabled = ? order by displayorder asc limit 1',array(SECTIONS_TABLE, $currdisplayorder, 'Y') );

	if (!isset($nextsectionid)) $nextsectionid = 0;


 //   $newsectionid = $db->getOne("select id from ! where enabled=? and id >= ! order by displayorder limit 1",array(SECTIONS_TABLE, 'Y', $sectionid) );

 //   $sectionid = $newsectionid;
    /* reterive record from osdate_questions and osdate_questionoptions table   */

    $sqlquestion = 'select id, question, mandatory, description, guideline, maxlength, control_type, extsearchhead from ! where enabled = ? and section = ? and question <> ? and extsearchable = ?  order by mandatory desc, displayorder';

    $temp = $db->getAll( $sqlquestion, array( QUESTIONS_TABLE, 'Y', $sectionid , '', 'Y') );

    $data = array();

    foreach( $temp as $index => $row ) {
        if (($config['use_extsearchhead'] == '1' or $config['use_extsearchhead'] == 'Y') && $row['extsearchhead'] != '') {
            $row['question'] = $row['extsearchhead'];
        }

		/* THis is made to adjust for multi-language */
		if ($_SESSION['opt_lang'] != 'english') {
			$lang_question = $_SESSION['profile_questions'][$row['id']]['question'];
			$lang_descr = 	$_SESSION['profile_questions'][$row['id']]['description'];
			$lang_guide = 	$_SESSION['profile_questions'][$row['id']]['guideline'];
			if ($lang_question != '') {
				$row['question'] = $lang_question;
			}
			if ($lang_descr != '') {
				$row['description'] = $lang_descr;
			}
			if ($lang_guide != '') {
				$row['guideline'] = $lang_guide;
			}
		}


        $sql = 'select * from ! where enabled = ? and questionid = ? order by displayorder';

        $options = $db->getAll( $sql, array( OPTIONS_TABLE, 'Y', $row['id'] ) ) ;

		/* THis is made to adjust for multi-language */
		$optsrs = array();
		if ($_SESSION['opt_lang'] != 'english') {
			foreach($options as $kx => $opt) {
				$lang_ansopt = $_SESSION['profile_questions'][$row['id']][$opt['id']];
				if ($lang_ansopt != '') {
					$opt['answer'] = $lang_ansopt;
				}
				$optsrs[] = $opt;
			}
		} else {$optsrs = $options; }


        $row['options'] = makeOptions ( $optsrs);

        $endoptions = makeOptions ($optsrs);

        krsort($endoptions);

        reset($endoptions);

        $row['endoptions'] = $endoptions;

        $data [] = $row;
    }

        /* Get a default date for the blog search */
	if ( $_SESSION['advsearch']['date_posted'] == '' ) {

		$_SESSION['advsearch']['date_posted'] = date('Y-m-d');
	}
} else {

	$nextsectionid = $db->getOne('select id from ! where enabled = ? order by displayorder asc limit 1',array(SECTIONS_TABLE,'Y') );
}


$t->assign('nextsectionid',$nextsectionid);

if ( isset( $_GET['errid'] ) ) {

    $t->assign( 'mandatory_question_error', get_lang('errormsgs',$_GET['errid']) );

}

if (($_SESSION['search_save_type'] == 'N' or $_SESSION['search_save_type'] == 'R') && (!isset($get_search) or $get_search == '')  && $_REQUEST['search_new'] != '1' ) {

    /* Save this search */
    if ($_SESSION['search_save_type'] == 'N') {
        $srch_name = $_SESSION['search_new_name'];
    } else {
        $srch_name = $_SESSION['search_name'];
    }

    $qry_txt = serialize($_SESSION['advsearch']);

    if ($srch_name != '') {

        $rec_available = $db->getOne('select 2 from ! where userid=? and search_name=?', array(USER_SEARCH_TABLE, $_SESSION['UserId'], trim($srch_name)) );

        if (isset($rec_available) && $rec_available==2) {
            $db->query('update ! set query=? where userid=? and search_name=?', array(USER_SEARCH_TABLE, $qry_txt, $_SESSION['UserId'], trim($srch_name)) );
        } else {
            $db->query('insert into ! (userid, search_name, query) values (?, ?, ?)', array(USER_SEARCH_TABLE, $_SESSION['UserId'], trim($srch_name), $qry_txt) );
        }
    }

    $_SESSION['search_name'] = $srch_name;

    $_SESSION['search_new_name'] = '';

    $_SESSION['search_save_type'] = 'R';

}

$t->assign('user_searches', $db->getAll('select search_name from ! where userid = ? order by search_name', array(USER_SEARCH_TABLE, $_SESSION['UserId']) ) );

$lang['lookcountries'] = $allcountries;

$t->assign('lang', $lang);

$t->assign( 'sectionid', $sectionid );

$t->assign('frmname', 'frm' . $sectionid );

$t->assign( 'head', $sections[ $sectionid ] );

$t->assign( 'data', $data );

$t->assign('rendered_page', $t->fetch('advsearch.tpl') );

$t->display('index.tpl');

?>
