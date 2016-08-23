<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
$string['pluginadministration'] = 'Skillsoft Asset';
$string['pluginname'] = 'Skillsoft Asset';
$string['skillsoft:addinstance'] = 'Add a new Skillsoft activity';

$string['modulename'] = 'Skillsoft Asset';
$string['modulename_help'] = '<p>This module provides a way to simply create a new activity for a Skillsoft OLSA Hosted asset that will be tracked by Moodle</p>
<p>All the information to launch the asset can be entered manually, or just the Asset ID can be entered and then the remaining information automatically retrieved from the OLSA Server.</p>
<p>Any asset that provides a score generates GradeBook entries</p>

<strong>Skillsoft Open Learning Services Architecture (OLSA)</strong>
<p>The Skillsoft Open Learning Services Architecture (OLSA) is an enhancements to the Skillsoft Skillport LMS architecture.</p>
<p>It is made up of two components:</p> 
<ul>
<li>LMS Interface - This provides a means for Moodle to launch Skillsoft assets using the industry standard AICC HACP method.</li>
<li>Web Services - This provides a means for another system to interact with Skillport using standards based Web Services. These provide the functionality in this module to support retrieving of course metadata and synchronising progress between the systems</li>
</ul>
<p>OLSA Supports two tracking modes, which determines where the users progress data is stored</p>
<ul>
<li>Track to LMS - All usage data is returned immediately to Moodle</li>
<li>Track to OLSA - All usage data is returned to OLSA Server, and synchronised back to Moodle via a cron task</li>
</ul>
<p>Skillsoft recommend the &quot;Track to OLSA&quot; mode as this enhances the functionality available to the user alllowing use of:</p>
<ul>
<li>Download Capability - Using the Skillsoft Course Manager (SCM) a customer can allow users to download courses to use off line and all tracking and synchronisation of usage data is automatically handled by OLSA</li>
<li>KnowledgeCenters - A Skillsoft KnowledgeCenter is a pre-packaged, user-friendly learning portal that allows learners instant access to trusted, targeted content. This content may include assets loaded for direct access in Moodle. By using &quot;Track to OLSA&quot; only a single training record of usage will exist.</li>
<li>Search and Learn Connect - Using this interface a user can perform on-demand, integrated searches across the full library of Skillsoft resources they have access to and be directed to the specific book page, course topic or other tools they need for instant and relevant answers.</li>
</ul>';
$string['modulenameplural'] = 'Skillsoft Assets';
$string['noassets'] = 'No Skillsoft Assets';
$string['skillsoft_na'] = 'N/A';

$string['skillsoft_waitingalt'] = 'Please wait...';

//Capabilities
$string['skillsoft:viewreport'] = 'View Report for all users';

//Settings.php
$string['skillsoft_olsaendpoint'] = 'Skillsoft OLSA EndPoint';
$string['skillsoft_olsaendpointdesc'] = 'The URI for the Skillsoft OLSA Web Services EndPoint, for example http://test.skillwsa.com/olsa/services/Olsa. The URI is case sensitive';

$string['skillsoft_olsacustomerid'] = 'Skillsoft OLSA Customer ID';
$string['skillsoft_olsacustomeriddesc'] = 'The Customer ID used with OLSA for authentication';

$string['skillsoft_olsasharedsecret'] = 'Skillsoft OLSA Shared Secret';
$string['skillsoft_olsasharedsecretdesc'] = 'The Shared Secret used with OLSA for authentication';

$string['skillsoft_sessionpurge'] = 'Number of hours to keep sessionid';
$string['skillsoft_sessionpurgedesc'] = 'The number of hours that sessionids are kept before purging during CRON run.';

$string['skillsoft_trackingmode'] = 'Skillsoft Tracking Mode';
$string['skillsoft_trackingmodedesc'] = 'The mode the OLSA site is configured for, if Track to LMS results are returned to LMS using AICC. If Track to OLSA the results are stored in OLSA Server and need to be retrieved, options for this are On Demand Communications or via a custom report for previous 24-hrs data.';

$string['skillsoft_useridentifier'] = 'Moodle/Skillsoft User Identifier';
$string['skillsoft_useridentifierdesc'] = 'The user data field to use as common identifier between Moodle and OLSA. We recommend the Moodle user ID as this is a system generated value and will not change in Moodle even if the users Username is modified.';
$string['skillsoft_userid_identifier'] = 'ID';
$string['skillsoft_username_identifier'] = 'Username';

$string['skillsoft_tracktolms'] = 'Track to LMS';
$string['skillsoft_tracktoolsa'] = 'Track to OLSA (On Demand Communications)';
$string['skillsoft_tracktoolsacustomreport'] = 'Track to OLSA (Custom Report)';

$string['skillsoft_ssourl'] = 'Single SignOn URL';
$string['skillsoft_ssourldesc'] = 'Enter the URL for the single signon use %s to indicate the activity id location. i.e. http://myserver/signon.aspx?coursename=%s&action=launch. Leave blank to use AICC.';

$string['skillsoft_sso_actiontype'] = 'Select the OLSA Action Type';
$string['skillsoft_sso_actiontypedesc'] = 'Select the actiontype for launching assets using SSO mode';
$string['skillsoft_sso_actiontype_launch'] = 'Launch Asset without showing Skillport UI (launch)';
$string['skillsoft_sso_actiontype_summary'] = 'Launch Asset Summary Page in Skillport UI (summary)';

$string['skillsoft_defaultssogroup'] = 'Skillsoft Default Group List';
$string['skillsoft_defaultssogroupdesc'] = 'A comma seperated list of the default groups to send for new users during SSO to Skillport. Existing users group membership in Skillport is not altered.';

$string['skillsoft_settingsmissing'] = 'Can not retrieve Skillsoft OLSA Settings: please check the configuration settings.';

$string['skillsoft_accountprefix'] = 'Account Prefix';
$string['skillsoft_accountprefixdesc'] = 'Enter a prefix which will be added in front of the username sent to Skillport.';

$string['skillsoft_reportstartdate'] = 'Custom Report Start Date';
$string['skillsoft_reportstartdatedesc'] = 'Enter the start date for the custom report to retrieve data. This field is automatically updated every time the report successfully runs.';

$string['skillsoft_reportincludetoday'] = 'Custom Report Include Todays Data';
$string['skillsoft_reportincludetodaydesc'] = 'The report defaults to including data upto and including the previous day, this override makes the report include todays data.';

$string['skillsoft_clearwsdlcache'] = 'Clear the cached WSDL files';
$string['skillsoft_clearwsdlcachedesc'] = 'The WSDL files are downloaded and cached to improve SOAP client startup time, if selected this will force them to be downloaded again and recached.';

$string['skillsoft_disableusagedatacrontask'] = 'Disable the usage data task';
$string['skillsoft_disableusagedatacrontaskdesc'] = 'When using "Track to OLSA" this allows you to disable the normal task that runs during CRON to synchronise the usage data from Skillport';

$string['skillsoft_resetcustomreportcrontask'] = 'Reset the custom report cycle';
$string['skillsoft_resetcustomreportcrontaskdesc'] = 'When using "Track to OLSA (Custom Report)" there are a number of steps: RUN - Request the report be generated, POLL - Check the report is ready, DOWNLOAD - Retrieve the report, IMPORT - Import the report data into Moodle database, PROCESS - Add the usage data to the users and activities. This allows you to reset the cycle to the RUN step on next CRON run';

$string['skillsoft_usesso'] = 'Use OLSA SSO';
$string['skillsoft_usessodesc'] = 'Use the OLSA Web Services SSO function, this requires one of the Track to OLSA modes. If unchecked all launches uses the AICC launch process';

//May 2013
$string['skillsoft_strictaiccstudentid'] = 'Enforce Strict AICC student_id format';
$string['skillsoft_strictaiccstudentiddesc'] = 'When enabled the AICC handler enforces that the student_id meets the AICC 2.2 requirements which only allows [A-Za-z0-9\-_:]. If the Moodle/Skillsoft User Identifier is set to use the Username and this is an email address then the AICC code will throw an exception.';

//May 2014
$string['skillsoft_aiccwindowsettings'] = 'Default window settings for AICC launches';
$string['skillsoft_aiccwindowsettingsdesc'] = 'The settings to use for the popup window for AICC launches for example width=800,height=600';


//mod_form.php
$string['skillsoft_assetid'] = 'Asset ID';
$string['skillsoft_assetid_help'] = '<p>This is the Skillsoft unique id that is assigned to all eLearning assets</p>

<strong>Locating Catalog Asset IDs</strong>
<p>Catalog Assets are defined as those that appear within the OLSA platforms catalog view.</p>
<p>In the catalog view all Skillsoft elearning courses, KnowledgeCenters and Learning Programmes are listed.</p>
<p>What is not listed are Books24x7 assets or supplementary material such as Skillsoft JobAids or SkillBriefs.</p>
<p>To retrieve a full list of IDs for all the avilable assets on your OLSA site. Login to the OLSA site as an administrator, your Skillsoft Account Team will provide the user id and password to use, and run a Course Listing Report.</p>
<p>The value to enter as the &quot;Asset ID&quot; is the &quot;Course Number&quot;</p>
<strong>Locating Book Asset IDs</strong>
<p>Login to the Books24x7 site as an administrator, your Skillsoft Account Team will provide the user id and password to use, and run:</p> 
<ul>
<li><p>Titles in Collection - This report provides a list of all titles available in the selected collection listed alphabetically. It also includes coming soon titles. Specific data included:</p>
<p>Publisher/Producer, title, B24 bookid, ISBN, copyright, date added to site. Speaker and expire date are also include for video based collections</p>
</li>
<li><p>Titles in Collection by Topic - This report provides a list of all titles available in the selected collection listed by topic and subtopic areas. Specific data included:</p> 
<p>Topic, subtopic, title, publisher/producer, ISBN, one-line summary, B24 bookid, copyright date. Speaker is also included for video based collections</p>
</li>
</ul>
<p>The value to enter as the &quot;Asset ID&quot; is the &quot;Bookid&quot;</p>
<strong>Special Asset IDs</strong>
<p>There are a number of asset ids not listed in the reports above that perform specific functions:</p>
<ul>
<li><p>SSO</p>
<p>This id if the site is in &quot;Track to OLSA&quot; mode enables a seamless login from Moodle to the Skillport Platform</p>
</li>
<li><p>_addon_books_001</p>
<p>This id if Books24x7 is available will log the user into the Books24x7 home page. This can be used as well as the direct links to individual Books</p>
</li>
</ul>';

$string['skillsoft_retrievemetadata'] = 'Retrieve Metadata';
$string['skillsoft_retrievemetadata_help'] = '<p>When you click this button the Skillsoft OLSA server you are using will be queried, for details on the Asset you specify.</p>
<p>The query will update the Activity with this information if available:</p>
<ul>
    <li>Course Description/Overview</li>
    <li>Target Audience</li>
    <li>Prerequisites</li>
    <li>Expected Duration</li>
</ul>';
$string['skillsoft_updatemetadata'] = 'Update Metadata';
$string['skillsoft_updatemetadata_help'] = '<p>When you click this button the Skillsoft OLSA server you are using will be queried, for details on the Asset you specify.</p>
<p>The query will update the Activity with this information if available:</p>
<ul>
    <li>Course Description/Overview</li>
    <li>Target Audience</li>
    <li>Prerequisites</li>
    <li>Expected Duration</li>
</ul>';
$string['skillsoft_name'] = 'Title';
$string['skillsoft_name_help'] = '<p>Enter the Title for the Skillsoft Asset, optionally you can use the "Retrieve Metadata" button to automatically complete this information from the OLSA server.</p>';
$string['skillsoft_summary'] = 'Overview/Description';
$string['skillsoft_summary_help'] = '<p>Enter the Title for the Skillsoft Asset, optionally you can use the "Retrieve Metadata" button to automatically complete this information from the OLSA server.</p>';
$string['skillsoft_audience'] = 'Target Audience';
$string['skillsoft_audience_help'] = '<p>Enter the Target Audience for the Skillsoft Asset, optionally you can use the "Retrieve Metadata" button to automatically complete this information from the OLSA server.</p>';
$string['skillsoft_prereq'] = 'Prerequisites';
$string['skillsoft_prereq_help'] = '<p>Enter the Prerequisites for the Skillsoft Asset, optionally you can use the "Retrieve Metadata" button to automatically complete this information from the OLSA server.</p>';
$string['skillsoft_launch'] = 'Launch URL';
$string['skillsoft_launch_help'] = '<p>Enter the the fully qualified URL to the Skillsoft Asset or use the "Retrieve Metadata" button to automatically complete this information from the Skillsoft "OLSA" server.</p>
<p>Once you have saved the Skillsoft Asset it is not possible to edit this value or the value of the "Asset ID"</p>';
$string['skillsoft_mastery'] = 'Mastery Score';
$string['skillsoft_mastery_help'] = '<p>When using &quot;Track to LMS&quot; mode, this value is sent to the Skillsoft course on launch and controls when the course will return a lesson status of completed.</p>
<p>If &quot;No Mastery Score&quot; is selected the course will rely on the value configured in Skillsoft OLSA</p>
<p>When using &quot;Track to OLSA&quot; mode, this value is ignored, the judging of completion is performed by the Skillsoft OLSA server</p>';
$string['skillsoft_duration'] = 'Duration (minutes)';
$string['skillsoft_duration_help'] = '<p>Enter the duration for the Skillsoft Asset, in minutes, optionally you can use the "Retrieve Metadata" button to automatically complete this information from the OLSA server.</p>';
$string['skillsoft_assettype'] = 'Asset Type';

$string['skillsoft_aiccwindowsettingsform'] = 'AICC Window Settings';
$string['skillsoft_aiccwindowsettingsform_help'] = '<p>Specify the options for the AICC popup window</p>';


//view.php
$string['skillsoft_enter'] = 'Launch';
$string['skillsoft_viewreport'] = 'View My Report';
$string['skillsoft_viewallreport'] = 'View Report';
$string['skillsoft_newattempt'] = 'You have already completed this course. Tick here to start a new attempt?';

//loadau.php
$string['skillsoft_loading'] = "You will be automatically redirected to the activity in";  // used in conjunction with numseconds
$string['skillsoft_pleasewait'] = "Activity loading, please wait ....";

$string['skillsoft_olsassoapauthentication'] = 'The OLSA Credentials are incorrect: please check the module configuration settings.';
$string['skillsoft_olsassoapinvalidassetid'] = 'The Asset ID specified does not exist. Asset ID={$a}';
$string['skillsoft_olsassoapfault'] = 'SOAP Fault During OLSA Call. Faultstring={$a}';

$string['skillsoft_olsassoapreportnotready'] = 'The report is not yet ready.';
$string['skillsoft_olsassoapreportnotvalid'] = 'The report handle specified does not exist. Handle={$a}';


//preloader.php
$string['skillsoft_metadatatitle'] = "Updating";
$string['skillsoft_metadataloading'] = "Please wait while we retrieve the asset metadata from the OLSA Server";
$string['skillsoft_metadatasetting'] = "Please wait while we configure the activity";
$string['skillsoft_metadataerror'] = "An error has occurred while trying to retrieve the metadata. Details:";

//report.php
$string['skillsoft_firstaccess'] = "First Access";
$string['skillsoft_lastaccess'] = "Last Access";
$string['skillsoft_completed'] = "Completed";
$string['skillsoft_lessonstatus'] = "Status";
$string['skillsoft_totaltime'] = "Total Time";
$string['skillsoft_firstscore'] = "First Score";
$string['skillsoft_currentscore'] = "Current Score";
$string['skillsoft_bestscore'] = "Best Score";
$string['skillsoft_accesscount'] = "Access Count";

$string['skillsoft_noncompletable'] = 'Non Completable Asset';
$string['skillsoft_noncompletable_help'] = '<p>Certain assets that Skillsoft OLSA makes available to load do not provide any scoring or completion status.</p>
<p>The following are some of those asset types:</p>
<ul>
<li>Books24x7 Books</li>
<li>Leadership Development Channel Videos</li>
<li>Business Impact</li>
<li>Challenge Series</li>
<li>Learning Sparks</li>
<li>KnowledgeCenters</li>
<li>Mentoring</li>
<li>Learning Programs</li>
<li>Live Learning Courses</li>
<li>Express Guides</li>
<li>"Add-on" Referral Objects (Search &amp; Learn, Books)</li>
</ul>';

$string['skillsoft_report'] = 'Report';

//cron.php
$string['skillsoft_purgemessage'] = 'Purging skillsoft session ids from database created before {$a}';
$string['skillsoft_odcinit'] = 'Initialising Skillsoft On-Demand Communications Cycle';
$string['skillsoft_odciniterror'] = 'Error Recieved while initialising On-Demand Communications. Error={$a}';
$string['skillsoft_odcgetdatastart'] = 'Start Retrieving Skillsoft TDRs for handle={$a}';
$string['skillsoft_odcgetdataend'] = 'End Retrieving Skillsoft TDRs for handle={$a}';
$string['skillsoft_odcgetdataerror'] = 'Error while retrieving TDRs. Error={$a}';
$string['skillsoft_odcgetdataprocess'] = 'Processing TDR. ID={$a}';
$string['skillsoft_odcnoresultsavailable'] = 'No Results Available';
$string['skillsoft_odcackdata'] = 'Acknowledging handle={$a}';
$string['skillsoft_odcackdataerror'] = 'Error while acknowledging handle. Error={$a}';
$string['skillsoft_odcprocessinginit'] = 'Start Processing retrieved TDRs';
$string['skillsoft_odcprocessretrievedtdr'] = 'Processing TDR. ID={$a->tdrid}   SkillsoftID={$a->skillsoftid}   UserID={$a->userid}';
$string['skillsoft_odcprocessingend'] = 'End Processing retrieved TDRs';

$string['skillsoft_customreport_init'] = 'Initialising Skillsoft Custom Report Cycle';
$string['skillsoft_customreport_end'] = 'End Skillsoft Custom Report Cycle';

$string['skillsoft_customreport_run_start'] = 'Start Submit Custom Report';
$string['skillsoft_customreport_run_initerror'] = 'Error Received while initialising Custom Report Download Cycle. Error={$a}';
$string['skillsoft_customreport_run_alreadyrun'] = 'Report for startdate and endate are the same indicating report already processed.';
$string['skillsoft_customreport_run_startdate'] = 'Report Start Date = {$a}';
$string['skillsoft_customreport_run_enddate'] = 'Report End Date = {$a}';
$string['skillsoft_customreport_run_response'] = 'Report Submitted. Handle = {$a}';
$string['skillsoft_customreport_run_end'] = 'End Submit Custom Report';

$string['skillsoft_customreport_poll_start'] = 'Start Poll for Custom Report';
$string['skillsoft_customreport_poll_polling'] = 'Polling for Report. Handle = {$a}';
$string['skillsoft_customreport_poll_ready'] = 'Report Ready';
$string['skillsoft_customreport_poll_notready'] = 'Report Not Ready.';
$string['skillsoft_customreport_poll_doesnotexist'] = 'Report Does Not Exist.';
$string['skillsoft_customreport_poll_end'] = 'End Poll for Custom Report';

$string['skillsoft_customreport_download_start'] = 'Start Download of Report';
$string['skillsoft_customreport_download_url'] = 'Report URL. URL={$a}';
$string['skillsoft_customreport_download_curlnotavailable'] = 'curl extension not available.';
$string['skillsoft_customreport_download_createdirectoryfailed'] = 'Unable to create download folder. Folder={$a}';
$string['skillsoft_customreport_download_socksproxyerror'] = 'SOCKS5 proxy is not supported in PHP4';
$string['skillsoft_customreport_download_result'] = 'Downloaded {$a->bytes} bytes in {$a->total_time} seconds. Saved to {$a->filepath}';
$string['skillsoft_customreport_download_error'] = 'Download Failed. Error={$a}';
$string['skillsoft_customreport_download_end'] = 'End Download of Report';

$string['skillsoft_customreport_import_start'] = 'Start Importing Downloaded Report';
$string['skillsoft_customreport_import_rowcount'] = 'Rows Processed = {$a}';
$string['skillsoft_customreport_import_totalrow'] = 'Total Rows Processed = {$a}';
$string['skillsoft_customreport_import_errorrow'] = 'Import Failed on row = {$a}';
$string['skillsoft_customreport_import_end'] = 'End Importing Downloaded Report';


$string['skillsoft_customreport_process_start'] = 'Start Processing retrieved Report Results';
$string['skillsoft_customreport_process_totalrecords'] = 'Total records to process = {$a}';
$string['skillsoft_customreport_process_batch'] = 'Processing batch of records. Start Record Position = {$a}';
$string['skillsoft_customreport_process_retrievedresults'] = 'Processing Report Results. ID={$a->id}   SkillsoftID={$a->skillsoftid}   UserID={$a->userid}';
$string['skillsoft_customreport_process_end'] = 'End Processing retrieved Report Results';


//summary
$string['skillsoft_summarymessage'] = 'Attempt: {$a->attempt}<br/>Access Count: {$a->accesscount}<br/>Total Time: {$a->duration}<br />Best Score: {$a->bestscore}<br />';

//backuplib.php
$string['skillsoft_trackedelement'] = 'AICC Datamodel Elements';

//ssopreloader.php
$string['skillsoft_ssotitle'] = 'Logging in to Skillport';
$string['skillsoft_ssoloading'] = 'Please wait while we log you into Skillport';
$string['skillsoft_ssoerror'] = 'An error has occurred while trying to perform Skillport login. Details:';
$string['skillsoft_ssomodeerror'] = 'Skillport seamless login is only available in Track to OLSA mode.';

$string['skillsoft_ssopopupopened'] = 'This window will automatically close in 5 seconds.<br/>';
$string['skillsoft_ssopopupdetected'] = 'A popup blocker prevented the completion of this launch.<br/>Please disable your popup blocker and try again.<br/>';


//getolsadata.php - SSO
$string['skillsoft_ssoassettitle'] = 'Login to Skillport';
$string['skillsoft_ssoassetsummary'] = 'Login to Skillport seamlessly';

//Attempts
$string['skillsoft_attempt'] = 'Attempt';
$string['skillsoft_lastattempt'] = 'Last Attempt';
$string['skillsoft_allattempt'] = 'All Attempts';

//tabs.php
$string['skillsoft_info'] = 'Info';
$string['skillsoft_results'] = 'Results';
$string['skillsoft_allresults'] = 'All Users Results';