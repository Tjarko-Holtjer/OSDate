Release 2.0 Notes
=================

------------------------------------------------------------------------------------
Author: Vijay Nair                                            Date: Mar 25, 2007
------------------------------------------------------------------------------------

This document describes additions and modifications effected in 2.0 release. 

If you are upgrading from earlier version to this, please take a FULL BACKUP (including
files and Database) and take special precaution about the Php and TPL files
you may have changed or modified. If you are loading osDate 2.0 to a new directory than 
the existing one (I prefer this method), then copy your existing config.php to the 
new root directory before starting installation program.

Any user upgrading from 1.1.8 patch 03 or earlier, MUST DO FOLLOWING STEP after upgrade
to 2.0.

a)	After loading to DB, they should run /admin/modifyuserstatus.php to rectify the 
	Active Flag values.

	
In 2.0, interface programs are provided to use PHPBB or VBulletin or MyBB or Phorum as your 
Forum. IN 2.0, YOU MUST INSTALL THE FORUM OF YOUR CHOICE SEPARATELY AND SET THE INSTALLATION 
DETAILS USING ADMIN GLOBAL SETTINGS OPTION. (You can install PHPBB, VBulletin, MyBB or Phorum 
under the DOC_ROOT directory of your site. e.g. osdate/phpBB2 or osdate/forum/phpBB2 etc.). 
Once you install any one, using Admin. Global Settings, define the forum you are using in the 
global settings. Then you can start using forum integrated with osDate. 
(PHPBB is NO MORE BUNDLED WITH osDate.)


This release include a mechanism to develop plugins and interface is given to manage 
developed plugins. About 10 plugins are included now. Please read the full documentation 
about the methods and other necessary details, and see some plugins before you start
development work of new plugin. 
(url: libs/modOsDate/doc/modOsDate/modPlugin.html)


Upgrades:
---------

You must change the DEFAULT_LANGUAGE in config.php to 'english' if you are using any 
other language. The installer will not load any language and you will find many definitions 
are missing in the pages. Upgrades should be done using ENGLISH as base language.
Once all upgrades are over, you can load the language of your choice and then change
DEFAULT_LANGUAGE in config.php.


Important Note:
You must define the profile question sections in the language of your choice if you
want to enable multi-language option for profile questions. Please see example given.


New Additions
=============
This section lists programs and/or functions which are added in this release.

1.  New installer is included. This is totally different from earlier version.
	
2.	New program to load States into database.
	This program will help to load states into database for selected country. Also,
	it will allow to delete already loaded states for selected country from Database.
	
3.	New program to load Cities into database.
	This program will help to load cities into database for selected country. Also,
	it will allow to delete already loaded cities for selected country from Database.
	
4.	New program to load Counties into database.
	This program will help to load counties into database for selected country. Also,
	it will allow to delete already loaded counties for selected country from Database.
	
5.	Blog is introduced. Admin and user can set up their own blogs. Admin Blogs are viewable 
	to general public. A voting system and comment acceptance is provided.
	
6.	Users can manage their own Watched Profiles list.
	A new program is provided to manage user watched profiles. When the user is looking
	the profile of another member, they can add that profile to watched list, if it is not
	already in the list. Using the link 'Watched Profiles' from the menu, they can manage
	the list. 
	(effected programs: showprofile.php and /templates/default/nickpage_navi.tpl and 
	/templates/default/panelmenu.tpl )
	
7.	Admin. can now remove user pictures. The link is given in the profile management page
	in admin. section. The pictures count is the link to display pictures for the user.
	There is a delete button to delete the picture. The main picture and thumbnail picture
	will be removed from system. The number of thumbnails displayed is controlled by the
	Global Settings of Number of thumbnails to be displayed in picture gallery in one row.
	(effected programs: profile.php, profile.tpl, showpics.php, showpics.tpl, language file)

8.	Spam control code entry is introduced in various programs given below. THis will force 
	the user to enter the code shown as image to accept inputs.
	(effected programs: language files, new program spam_image.php and following programs)
	- signup.tpl and savesignup.php
	- tellafriend.tpl and sendinvite.php
	- feedback.tpl and feedback.php
	- compose.tpl and compose.php
	- changempass.tpl and modifympass.php )
	
9.	Some of the fields in the user table made optional to accept.
	Following fields from User Table are made non mandatory and if needed, 
	can be suppressed in the forms. These fields are set in Global Configuration table.

		lookgender
		lookagestart
		lookageend
		allow_viewonline             
		country                      
		timezone
		state_province              
		county                      
		city                        
		zip                    
		address_line1
		address_line2
		lookcountry
		lookstate_province
		lookcounty
		lookcity
		lookzip
	
	These fields can defined as not acceptable, meaning these fields will be suppressed
	from various forms and the user will not see them. Some fields can be made acceptable
	but non mandatory.
	(Programs effected: glblsettings.php and glblsettings.tpl, signup.tpl, savesignup.php.
	 edituser.tpl, modifyuser.php, admin/modifyprofile.php, admin/profileedit.tpl, 
	 nickpage_basic.tpl,userresultview.tpl, admin/userresultview.tpl, 
	 admin/nickpage_basic.tpl, advsearch.tpl, advsearch.php, admin/advsearch.php, 
	 admin/advmatch.tpl
	 )
	 
10.	Membership level controls for messages to be sent per day by a member.
	Now you can define the number of messages allowed to be sent by a member for the given
	membership level. This is done in Membership table using Admin. Manage Membership
	interface.
	(Programs effected: admin/membership.php, admin/membership.tpl, admin/addmship.tpl,
	 admin/savemship.php, language file, table structure osdate_membership, new table 
	 osdate_user_actions, mshipcompare.tpl, compose.php)

11.	Membership level controls for winks to be sent per day by a member.
	Now you can define the number of winks allowed to be sent by a member for the given
	membership level. This is done in Membership table using Admin. Manage Membership
	interface.
	(Programs effected: admin/membership.php, admin/membership.tpl, admin/addmship.tpl,
	 admin/savemship.php, language file, table structure osdate_membership, new table 
	 osdate_user_actions, mshipcompare.tpl, sendwinks.php)
	 
12.	Profile questions and answers are made multi-language capable.
	Using the program /admin/convert_profile_questions.php, you must make the file
	profile_questions.php. This file is made in the DOC_ROOT/temp directory. You must copy
	this file to respective language directory and make the translations. THe default one 
	is English and must be in the table as it is. If you are making any changes and/or
	adding new ones, it should be done in normal way and create the file and keep language specific 
	definitions in the language directory.
	(Programs effected: /admin/convert_profile_questions.php, init.php, showprofile.php, 
	 editquestions.php, /admin/editprofilequestions.php, admin/showprofile.php )

13. Snaps watermarking with picture is introduced. THis is in place of Text watermarking
	which is already available.
	This is done using the global parameter watermark_image. There is already a configuration
	parameter for watermark_snaps which is TEXT watermarking. This new parameter will 
	provide for watermarking with images. The watermark images should be either jpg or png.
	If watermark_snaps is defined, it will override the watermark_image parameter.
	(Programs effected: global parameter settings, getsnap.php)

14.	Now the user menu can display all available options, even if there is no privilege to use it.
	This is set in global settings (display_all_menu_items). If this option is set to Y, menu will
	list all options. However, the clicking of the option will show a message to upgrade membership
	level. (Programs effected: language file, init.php, global settings, panelmenu.tpl)
	
15.	AJAX based shoutbox is implemented. This must be enabled in global settings. ALso, you can setup
	the number of messages to be displayed and the maximum number of messages to be kept in the system. 
	Small number of smilies are implemented. 
	(Programs effected: shoutbox.php, shoutbox.tpl, shoutbox.js, init.php, index.php, leftcolumn.tpl,
	 global settings table and form)
	 
16.	A new interface is provided in the Admin. Section to manage pictures of a member. YOu can load,
	and/or delete pictures of a user as well as change teh album id. From the profile listing window, 
	click on the pictures count. This will bring up user pictures display page. (Just like userpicgallery).
	Click on the Manage Pictures pictures on the right hand top cornet. THis will start the 
	manage pictures page.
	(Programs effected: userpics.tpl, userpics.php, saveuserpics.php, showpics.tpl, showpics.php)
	
17.	Video profiles can now be loaded.
	This is controlled using the membership level settings. The directory /uservideos should be
	made writeable (777). 
	(programs effected: uploadvideos.php, admin/uploadvideos.php, uservideos.tpl, savevideo.php, 
	 admin/savevideo.php,  showvideo.tpl, /videos directory with all files in it, nickpage_basic.tpl,
	 uservideogallery.php, uservideogallery.tpl, language file, admin/profile.php, profile.tpl, 
	 panelmenu.tpl)
	
	 The user menu has option to manage videos. Using this the user can load videos and assign it
	 to different albums, if albums are allowed for the user level. In the profile display window,
	 Number of videos loaded are mentioned. If it is more than 0, you can view the videos. 
	 
	 In admin section, profile listing will now show number of videos loaded. Clicking on this
	 count, the video management interface will be presented.
	 
18.	Improved mailing system. New HTML mails are added. The link in the mail is clickable and user
	will be taken to the login screen and forwarded to the appropriate screens upon successful
	login.
	
	Winks sending has such an HTML mailing system.
	(programs effected: sendwinks.php, login.tpl, index.php, compose.php, language file, midlogin.php )
	
19.	Email receiving preferences of the user can be set now. (My Settings in panel menu). This is based
	on the membership level. Membership level controls are available to set this privilege.

	Following emails receiving options can be set by the user.
	Message Received, Wink Received, Message Read, Added to Buddy/Ban/Hot list, Membership expiry
	letter from admin. , Receiving Blog Comment
	(programs effected: mysettings.php, mysettings.tpl, sendwinks.php, admin/mship_expiry_letter.php,
	 compose.php, mailmessages.php, buddybanlist.php, language file, blog class file)

20.	In Mysettings, the user can set if members in their buddy list can view their private albums.
	When the user clicks for private gallery, password is bypassed if the user is in buddy list and
	this is set to allow.
	
21.	Provision to resend confirmation link to the user is made. The user can request the confirmation
	link again. System will create a new password and send confirmation link, if the profile is not
	already confirmed.
	(programs effected: language file, resend_conflink.php, resend_conflink.tpl)
	
22.	Now couple/group profiles can be identified. The method is as follows:
	a) First, individual profiles shold be entered and activated.
	b) Then create a couple profile where these individual profile's usernames can be 
		referenced. 
	c) In the profile display window, the usernames of individual profiles will be displayed
		and can be clicked to view details of that profile.
		
	(Programs effected: signup.php, savesignup.php, signup.tpl, edituser.php, modifyuser.php,
	 edituser.tpl, showprofile.php, nick_basic.tpl, admin/showprofile.php, admin/nick_basic.tpl,
	 admin/modifyprofile.php, admin/profileedit.tpl)
	 
23.	Program to transfer pictures from DB to FileSystem and vice versa is introduced.
	After loggin into Admin. section, the admin should start the program as follows:
	
	a) To transfer pictures from FileSystem to DB
		/admin/transferpics.php?action=FS2DB
		
	b) To transfer pictures from DB to FileSystem	
		/admin/transferpics.php?actin=DB2FS
		
	(Programs effected: admin/transferpics.php)
	
24.	Mymatches email system is introduced. This system is available only in HTML method.
	This can be defined as a cronjob. It can be	run independantly also. 
	( /cronjobs/mymatches_email.php) It will send emails to all users who have not set 
	their choice to receive mymatches emails or to those who have set their choice but 
	last email was sent before the said period.
	(Programs effected: language file, /cronjobs/mymatches_email.php)
	
25.	Membership expiry reminder letters sending program can be run independantly now.
	It can be set to run as a cronjob also. (/cronjobs/mship_expiry_reminders_email.php)
	It should be run as /cronjobs/mship_expiry_reminders_email.php?expiry=x where
	x = 0 for expired letters, x = number of days in which membership will expire.
	(Programs effected: /cronjobs/mship_expiry_reminders_email.php)

26.	php121 instant messenger is integrated. You need to enable this option in configuration
	setting. When this is enabled, corresponding tables will be added. Also, you need to 
	the respective membership level to access this program. e.g. by default, this is disabled 
	in configuration settings and not allowed in any membership level. THIS IS PURELY
	EXPERIMENTAL. DO not change any files in /php121 directory unles you know what you are 
	doing.
	
27.	From now onwards, the config.php file will be in the directory /myconfigs. This is moved
	to its own directory to avoid and/or reduce attacks.
	
28.	Lucky Spin is introduced. This is a plugin which should be installed through 
	Admin Plugins interface. This will display, at random, a user photo and abridged 
	profile in the left column. This will display only those users who has loaded photos 
	in public album and will not display the user which was shown immediately before. 

29.	Following plugins are introduced.
	
		Adult Questionnaire
		Compatability questionnaire
		Featured RSS Feeds
		Google Maps
		Hot or Not ranking
		Advanced Hot or Not
		Language Banners
		My Friends List
		Payment History
		My Profile Backup
		Scrolling Gallery
		Speed Dating

	Please read the documentation of each plugin for more details.

30. A two tier caching mechanism is implimented. First level is at page level. This is
	for certain types of pages only. The files for this are in /includes/internal directory.
	Second is at mysql data extraction level. This is implimented by creating two classes 
	- cashedDB and mysqlc. These files are under /libs/pear directory. 

31.	A cronjob is included to remove all cache files which are older than the time limit 
	set in configuration section. (cronjobs/delete_cache_files.php)


Modifications and/or improvements
=================================

1.  New option is added to remove DB definitions for a language from Database.
	If you want to drop one language definitions from Database, use Admin. Manage Language option
	and select the language and click on 'Remove langauge from DB' button. This will remove all
	entries for selected language from Database.
	(load_language.php and load_language.tpl are effected by this)
	
2.	New option to delete zip codes for a Country. If you want to delete zip codes which are 
	loaded for a country, use the option Admin. Load Zips and select the country and 
	Click the button 'Delete Zip Codes'. This will remove all loaded zip codes for the Country.
	(load_zips.php and load_zips.tpl are effectd by this.)
	
3.	Email message subjects are now being taken from Language File. This was fixed in the 
	program earlier. You need to modify the language file and load to DB to implement this. Please
	see English Language file for sample and details. (various programs which are sending message 
	are effected by this modification)
	
4.	Advance and Quick Profile Search results are now capable of sorting on Online users.
	This can be done once results are shown based on default sort order, i.e. username.
	(effected programs: advsearch.php, searchmatch.php, showsimpsh.tpl, advmatch.tpl)
	
5.	UserName can be used as Profile Display Parameter.you can define usage of username
	by giving the url showprofile.php?username=xx. This is controlled using the option
	in site's global settings. If this is set to 'Y', the url will be with username. Default is N
	which will show the url with id as parameter.
	
	If mode_reqrite is enabled, the username usage will show like http://yoursitename/username. 
	Otherwise it will be like http://yoursitename/id.htm. To use SEO, you must 
	copy /SEF_URL/.htaccess.txt as .htaccess to your osdate root directory and /admin directory.
	
	
	Following programs are modified to effect this improvement. 
	
		From directory /templates/default
			allstories.tpl, buddybanlist.tpl, deletemessages.tpl, fullstory.tpl, 
			home_newuserlist.tpl, listviewswinks.tpl, mailmessages.tpl, newmemberlist.tpl,
			panelmenu.tpl, sentmessages.tpl, showmessage.tpl, stories.tpl
			userresultview.tpl, userresultviewsmall.tpl
		From directory /templates/default/admin
			approve_snaps.tpl, editglblsettings.tpl, featured_profiles.tpl,
			managestories.tpl, profile.tpl, reactivate.tpl, transactions.tpl,
			unapprovedusers.tpl, userresultview.tpl, userresultviewsmall.tpl
			
		/templates/romantic/homepage.tpl
		/templates/silver-red/homepage.tpl
		
6.	The small profile display window and full profile display window will now show the 
	number of pictures loaded by the user. In the small profile window, the count is linked 
	to user picture gallery. (programs effected: userresultviewsmall.tpl, 
	nickpage_basic.tpl, language file, admin/userpicgallery.tpl  and smarty function file  
	function.checkuser.php )
	
7.	When a user's membership expires, now system will display the word 'Expired' rather than
	showing negative number of days. (effected programs: user_home_stats.tpl)
	
8.	The home page is more compartmentalised. Now it has small snippets of tpl files for 
	each activities and each can be placed where ever it is needed. (effected progras:
	homepage.tpl, user_home_stats.tpl, special_offer.tpl, home_newuserlist.tpl, 
	home_featured_profiles.tpl, home_membersincelastlogin.tpl)
	
9.	New profiles which are shown in the home page and newmembers listing can be selected for
	members since last xx days. This number of days are set in Admin. Global Settings.
	Using this, you can list only members registered in last 30 days, 40 days, etc.
	(effected programs: newuserlist.php, index.php)
	
10.	The requirement of registration confirmation by the new users can be bypased. This is
	setup in Admin. Global Settings. If this bypassing is set to 'Y', then new users will be 
	activated immediately, if the default_user_status is set to be active. Otherwise, the
	user rec will be set such that the user has confirmed registration. (effected programs: 
	editglblsettings.tpl, savesignup.php)
	
11.	When a new user register, email will be sent to site admin. This is set in global 
	settings. If this parameter is set to 'Y', then email will be sent to admin.
	(effected programs: language files, savesignup.php, editglblsettings.tpl)

12.	Display of views and winks since xxx days before and to a maximum of xxx numbers.
	In Global Settings, there are two parameters introduced for this purpose;
		no_last_viewswinks - THis is the number of records to be shown. IF date of
							last login procudes more records, then all those entries
							will be listed.
		last_viewswinks_since - This is the number of days before current date from which
								the list to be shown. (since 30 days/45 days)
								
	(effected programs: editglblsettings.tpl, index.php, user_home_stats.tpl, listviewswinks.php,
		listviewswinks.tpl, language files )
		
13.	Zip Codes loading program is modified to display filename and message properly.
	(effected programs: /admin/load_zips.php, admin/load_zips.tpl)
	
14.	At the installation time, the Super Admin. Username is now being accepted. 'admin' is 
	given as default, which can be changed. Present systems will have 'admin' as super user
	name. But this can be changed using phpmyadmin and changing the value for username field 
	in table DBPREFIX_admin_user table.
	
15.	New provision is given to bypass Look Gender cross verification in search programs.
	(effected programs: advsearch.php, searchmatch.php,	admin/editgblblsettings.tpl). 
	The purpose is to avoid current cross verification of User Gender with the result 
	set of searches.
		
16.	New provision is included in search to select only online users and/or users 
	who has a photo. (effected programs: advsearch.php, advsearch.tpl, language file)

17.	Membership expiry date is added as a column in Admin. Profile listing. This column is 
	sortable. (effected programs: profile.php, profile.tpl, language file, init.php)
	
18.	Pictures loaded count and videos loaded count are added in Admin. Profile listing.
	(effected programs: profile.php, profile.tpl, language file)

19.	Admin. Approve Snaps page will now display the album name, if available.
	(effected programs: approve_snaps.php, approve_snaps.tpl)
	
20.	Advance search is modified to select profiles based on username. However, the gender cross 
	validation should be disabled for this to work if you want to display users of same gender also.
	(effected programs: advsearch.php)
	
21.	The picture gallery display page is modified to display x number of thumbnails in a row. This is
	set in site's Global Settings. Using this parameter, admin can limit number of thumbnails 
	displayed per line in the album display page.
	(effected programs: userpicgallery.tpl)
	
22.	Display picture of the user to whom message being sent.
	(effected programs: compose.tpl)
	
23.	The multiple registration confirmation issue is rectified. Now system will update 'Confirmed'
	word in place of confirmation code when the user confirms registration.
	(effected programs: language file, completereg.php, savesignup.php)
	
24.	When a new user signs up, admin. will receive an email. This is controlled in Configuration
	settings.
	(effected programs: language file, savesignup.php, global settings)
	
25.	When a user loads a new photo, admin. will receive email.
	(effected programs: language file, global settings, savesnap.php)
	
26.	New timezone details are being displayed.
	(effected programs: language file)
	
27.	In the profile display window, now system will display all pictures loaded by the user in
	public album. This will display all loaded publically viewable pictures in profile window
	itself. A link is given for gallery. (programs effected: language file, showprofile.php, 
	nickpage.tpl, admin/getsnap.php, admin/nickpage.tpl, admin/showprofile.php)
	
28.	Do not send intimation email of message receipts to users if they are online.
	This is provided as option in configuration settings. 
	(Effected programs: compose.php, editglblsettings.tpl)

29.	Cosmetic display changes are done to the screen where profile questions and answers are
	displayed. (Programs: editquestions.tpl, admin/editprofilequestions.tpl, advsearch.tpl)
	
30.	Banner management screen modifications. The column headings are made sortable.
	(Programs effected: admin/managebabber.php, admin/managebanner.tpl)

31.	The invite-a-friend page is now being made as integral page of the system. This will display
	the form in the same homepage rather than poping up the page. (Programs effected: tellafriend.php, 
	index.tpl, tellafriend.tpl)
	
32.	THe user was able to rate own profile. THis is rectified. 
	(programs effected: mickpage_newrating.tpl)
	
33.	There was a problem with sections questions options management. Saving the edited option was 
	removing the question name from the page. This is now rectified.
	(Programs effected: modifyoptions.php, sectionquestiondetail.tpl, sectionquestiondeail.php)
	
34.	Feedback acceptance form is modified to display the entered text if there is an error condition.
	(Programs effected: feedback.php, feedback.tpl)
	
35.	The way in which the expiry message displayed in the user homepage is modified. 
	(Programs effected: language file, index.tpl, user_home_stats.tpl)
	
36.	Advance search can search profiles with Photos, with videos and who is online.
	(Programs modified: advsearch.php, advsearch.tpl, language file)
	
37.	The User and admin menus are grouped. This effect the way the menus are displayed.
	(Programs effected: panelmenu.tpl, language file)
	
38.	Newmemberlist program is improved. All headings are now made sortable as well as pagination
	is introduced.
	(programs effected: newmemberslist.php, newmemberslist.tpl)
	
39.	Emailing system is improved with new HTML based emails. By default, system is made to send
	html emails. New formats are given for following:
	a) Registration confirmation email.
	b) Profile activation email
	c) Winks received message
	d) Message received mail
	e) Profile membership level changed information
	f) Message read notification
	g) Invite a friend
	h) Buddy/Ban/Hot list added notification
	i) Profile reactivation notification
	j) Feedback email to admin
	k) Forgot password retrieving
	l) Membership expired and expiring messages
	
	Appropriate programs are modified to incorporate these emails sending.
	
40.	Pagination is introduced in allstories and allnews display pages.
	(programs effected: index.php, allstories.tpl, allnews.tpl)
	
41.	A new field is introduced in USER table to accept small description about self. This field can
	be made non enterable or set non displayable in small profile window. This is set n Global 
	settings. 
	(programs effected: language file, global settings program, USER table, signup.php, savesignup.php, 
	edituser.php, signup.tpl, edituser.tpl, admin/profileedit.tpl, admin/modifyuser.php, showprofile.php,
	userresultviewsmall.tpl, nickpage_basic.tpl, admin/showprofile.php)
	
42.	Affiliate login page is modified to incorporate Forgot password option.

43.	/includes/internal/Functions.php is modified to include the HTML injection blocking
	in place. 
	
44.	/admin/sendletter.php is modified to select multiple genders.i.e. the gender 
	selection is made as checkbox.
	(Programs effected: sendletter.php, sendletter.tpl)
	
45.	The default male.jpg, female.jpg and couple.jpg are moved to templates/skinname/images directory.
	This was originally in /images directory. This will make things easier to have 
	template wise default pictures. (ALL EXISTING SYSTEMS USING ANY SKIN OTHR THAN THE DEFAULT ONES
	or SUPPLIED BY TUFAT, MUST ENSURE THIS POINT. i.e. male.jps, female.jps and couple.jpg
	and any other non-default jpg files should be copied to /templates/skin/images/ directory.)

46.	The mailSender() function is improved. Now each program has to send only the data portion and most
	commonly used values (list is given below) will be replaced by this function.
	This function also will add html email wrapper to each email if mail format
	is html.
	
	#AdminName#, #link#, #SiteUrl#, #SkinName#, #siteName#, #AdminEmail#
	

47. Userpicgallery display program is modified. A new showpic_iframe.tpl is displayed
	in the iframe using showpic_iframe.php program. This is to align the picture to center
	and to have background color uniform. A new css class .picgallery is defined.
	
48.	A new interface is added in admin to view calendar of events, just like the one given 
	for users. (Programs effected: calendarevents.php, calendarview.php, event.php)
	
49.	Issue with the age calculation is rectified. This involved changes to many programs
	(29 programs)
	
50.	Flashchat integration issue is rectified.

51.	Issue with Rememberme option in the login screen is rectified.

52.	Issue with HTML Emails not being sent on some machines rectified.


