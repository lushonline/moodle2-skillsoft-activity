<?php
/*
 * @package		mod-skillsoft
 * @author		$Author$
 * @version		SVN: $Header$
 * @copyright	2009-2014 Martin Holden
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot.'/mod/skillsoft/locallib.php');

$settings->add(new admin_setting_configtext('skillsoft_olsaendpoint',
					get_string('skillsoft_olsaendpoint', 'skillsoft'),
					get_string('skillsoft_olsaendpointdesc', 'skillsoft'),
					''));

$settings->add(new admin_setting_configtext('skillsoft_olsacustomerid',
					get_string('skillsoft_olsacustomerid', 'skillsoft'),
					get_string('skillsoft_olsacustomeriddesc', 'skillsoft'),
					''));

$settings->add(new admin_setting_configtext('skillsoft_olsasharedsecret',
					get_string('skillsoft_olsasharedsecret', 'skillsoft'),
					get_string('skillsoft_olsasharedsecretdesc', 'skillsoft'),
					''));

$settings->add(new admin_setting_configtext('skillsoft_sessionpurge',
					get_string('skillsoft_sessionpurge', 'skillsoft'),
					get_string('skillsoft_sessionpurgedesc', 'skillsoft'),
                   	8,
                   	PARAM_INT));

$settings->add(new admin_setting_configselect('skillsoft_trackingmode',
			   get_string('skillsoft_trackingmode', 'skillsoft'),
			   get_string('skillsoft_trackingmodedesc', 'skillsoft'),
			   TRACK_TO_LMS,
			   skillsoft_get_tracking_method_array()));

$settings->add(new admin_setting_configselect('skillsoft_useridentifier',
			   get_string('skillsoft_useridentifier', 'skillsoft'),
			   get_string('skillsoft_useridentifierdesc', 'skillsoft'),
			   IDENTIFIER_USERID,
			   skillsoft_get_user_identifier_array()));

$settings->add(new admin_setting_configtext('skillsoft_defaultssogroup',
					get_string('skillsoft_defaultssogroup', 'skillsoft'),
					get_string('skillsoft_defaultssogroupdesc', 'skillsoft'),
					'SkillSoft'));

$settings->add(new admin_setting_configtext('skillsoft_accountprefix',
			   get_string('skillsoft_accountprefix', 'skillsoft'),
			   get_string('skillsoft_accountprefixdesc', 'skillsoft'),
			   ''));

$settings->add(new admin_setting_configcheckbox('skillsoft_usesso',
				 get_string('skillsoft_usesso', 'skillsoft'),
				 get_string('skillsoft_usessodesc', 'skillsoft'),
				 0));			   
			   
$settings->add(new admin_setting_configtext('skillsoft_ssourl',
			   get_string('skillsoft_ssourl', 'skillsoft'),
			   get_string('skillsoft_ssourldesc', 'skillsoft'),
			   $CFG->wwwroot.'/mod/skillsoft/ssopreloader.php?a=%s'));
			   
$settings->add(new admin_setting_configselect('skillsoft_sso_actiontype',
			   get_string('skillsoft_sso_actiontype', 'skillsoft'),
			   get_string('skillsoft_sso_actiontypedesc', 'skillsoft'),
			   SSO_ASSET_ACTIONTYPE_SUMMARY,
			   skillsoft_get_sso_asset_actiontype_array()));

$settings->add(new admin_setting_configtext('skillsoft_reportstartdate',
			   get_string('skillsoft_reportstartdate', 'skillsoft'),
			   get_string('skillsoft_reportstartdatedesc', 'skillsoft'),
			   '01-JAN-2000'));			   

$settings->add(new admin_setting_configcheckbox('skillsoft_reportincludetoday',
			   get_string('skillsoft_reportincludetoday', 'skillsoft'),
			   get_string('skillsoft_reportincludetodaydesc', 'skillsoft'),
			   0));			   
			   
$settings->add(new admin_setting_configcheckbox('skillsoft_clearwsdlcache',
		   get_string('skillsoft_clearwsdlcache', 'skillsoft'),
		   get_string('skillsoft_clearwsdlcachedesc', 'skillsoft'),
		   0));

$settings->add(new admin_setting_configcheckbox('skillsoft_disableusagedatacrontask',
		get_string('skillsoft_disableusagedatacrontask', 'skillsoft'),
		get_string('skillsoft_disableusagedatacrontaskdesc', 'skillsoft'),
		0));

$settings->add(new admin_setting_configcheckbox('skillsoft_resetcustomreportcrontask',
		get_string('skillsoft_resetcustomreportcrontask', 'skillsoft'),
		get_string('skillsoft_resetcustomreportcrontaskdesc', 'skillsoft'),
		0));
		
//May-2013 (2013041400)
$settings->add(new admin_setting_configcheckbox('skillsoft_strictaiccstudentid',
		get_string('skillsoft_strictaiccstudentid', 'skillsoft'),
		get_string('skillsoft_strictaiccstudentiddesc', 'skillsoft'),
		1));		
		
//May-2013
$settings->add(new admin_setting_configtext('skillsoft_aiccwindowsettings',
			   get_string('skillsoft_aiccwindowsettings', 'skillsoft'),
			   get_string('skillsoft_aiccwindowsettingsdesc', 'skillsoft'),
			   'width=800,height=600'));	