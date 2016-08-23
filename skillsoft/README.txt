SkillSoft Asset Module
Author: Martin Holden, SkillSoft http://www.skillsoft.com
Updated: August 2016
Module Version: 2016082300

================================================================

Moodle Compatibility
--------------------
This plugin will work with Moodle 2.0. It is developed as a Moodle plugin activity module.

It has been tested with:
2.6.1+ (Build: 20140131)
2.7.1+ (Build: 20140821)
2.8.1+ (Build: 20141128)
2.9.7 (Build: 20160711)
3.0.5+ (Build: 20160817)

Known Issues/Limitations
------------------------
* Backup and Restore have been partially implemented in this release, supporting only the Skillsoft Asset - not any tracking data.
* Support for Multiple Completions when using "Track to OLSA" is only supported using the "Custom Report" mode.

PHP Requirement in additional to Moodle 2.x
-------------------------------------------
PHP SOAP Client enabled in PHP.INI
cURL enabled in PHP.INI

Install
-------
To install this plugin just extract the contents into MOODLE_HOME/mod/.
See the Moodle docs for help installing plugins/blocks::
http://docs.moodle.org/en/Installing_contributed_modules_or_plugins

The plugin also requires that the Moodle CRON Job is configured, and
scheduled to run every 5 minutes.
See the Moodle docs for help configuring CRON::
http://docs.moodle.org/en/Cron


Configuration
-------------
The configuration of the block is handled in the typical Moodle way.

TRACK TO OLSA MODE
------------------
The SkillSoft OLSA Site you will be using will need no special
configuration changes, from standard setup

You can choose between using the Moodle internal unique
student id or the Username, as the value that is used
as the username in OLSA.

As it is possible to change the Username, where as the internal
student id is controlled by Moodle an remains the same for the account
we recommend using the internal student id.

You may consider using the Username if you are integrating other
systems with the same OLSA server, this way so long as the Username in
OLSA is consistent with the Moodle Username only a single user record
will exist in OLSA.

Download Support
----------------
If you choose to us the internal unique student id from Moodle,
which the users will not know it is important to ensure that
the SCM Full SSO configuration is used.

Seamless Login to SkillPort Home Page
-------------------------------------
When using Track to OLSA there is a new special assetid 'SSO'
this assetid when used will create a new activity that allows
the user to be seamlessly logged into the SkillPort platform.

The seamless login will create a user in SkillPort if they
do not exist and set the SkillPort username based on setting
above to be either internal Moodle unique id or the Moodle 
username.

The SkillPort user account for, new user and existing SkillPort
users will be updated to with the Moodle users first name, last
name and email.

For new users the SkillPort group membership is controlled by
the skillsoft_defaultssogroup setting in Moodle. Any users that
need to be created in SkillPort will automatically be members of
the groups defined here.

Existing users group membership will be unchanged.

Seamless Login Launch Instead of AICC
-------------------------------------
When using Track to OLSA mode there is now an option to use
OLSA Seamless Login functionality instead of using AICC launching.

With this mode the user is redirected to the URL specified in
skillsoft_ssourl, which defaults to /mod/skillsoft/ssopreloader.php

This page then call OLSA Web Services and the user is taking to the
"Course Summary" page in the SkillPort UI or the asset is "Launched"
based on the setting of skillsoft_sso_actiontype.

This feature is especially useful if your company has already implemented
a SSO process to SkillPort. The skillsoft_ssourl value can be set to point
at this page.

It will be important to ensure that the username the existing SSO page
sends and the value Moodle would have sent match.

The Moodle SkillSoft username is based on either the Moodle UserID or
UserName, and the "prefix" specified in skillsoft_accountprefix setting.

These must match so that Moodle can import the usage data from SkillPort
and link it to the Moodle user account.  

Support for data retrieval using "Custom Report"
------------------------------------------------
When using TRACK TO OLSA mode the solution has been
enhanced to allow the user to choose between using
the OnDemand Communications model where data can be
polled from SkillPort more frequently, every 5-10 minutes,
or using the custom report method. Where data is retrieved
once a day for the previous 24 hour period.

Support for Multiple Completions - "Custom Report" Mode only
------------------------------------------------------------
When the OLSA/SkillPort site is configured to allow users to
obtain multiple completions for assets, Moodle will now record
the multiple attempts.

The GRADEBOOK entry in Moodle will always be for the latest attempt
this means that a user may have registered in GRADEBOOK as completed
and then on a subsequent launch and "restart" of the course in SkillPort
the users GRADEBOOK entry will revert to incomplete.

* Note regarding usage data synchronisation *
When using Track to OLSA there is no distiction between asset
launches from different Moodle Courses. This means that if two
Moodle courses have the same SkillSoft Asset then access from
either course will result in update of the usage data in both.

TRACK TO LMS MODE
-----------------
The SkillSoft OLSA site you will be using will need to have the
following OLSA Player Configurations set:

Player RO Configuration
-----------------------
Standard AICC Configuration plus ensure OBJECTIVES data not used:
	AICC_CORE_LESSON_FOR_RESULTS=true
	AICC_CORE_VENDOR_FOR_DATE=true
	E3_AICC_OBJECTIVES_STATUS_FOR_RESULTS=false

SkillSim RO Configuration
-------------------------
Standard AICC Configuration plus ensure OBJECTIVES data not used:
	AICC_CORE_LESSON_FOR_RESULTS=true 

* Note regarding usage data *
When using Track to LMS mode usage data is returned immediately
to Moodle and Moodle stores this data. In this mode there is
a distiction between asset launches from different Moodle Courses.
This means that if two Moodle courses have the same SkillSoft
Asset then access from each course is tracked seperately.

Support for Multiple Completions
--------------------------------
Once the user has achieved a lesson status of completed on the next
launch they will have the option to continue or start a new attempt.

The GRADEBOOK entry in Moodle will always be for the latest attempt
this means that a user may have registered in GRADEBOOK as completed
and then on a subsequent launch and "restart" of the course
the users GRADEBOOK entry will revert to incomplete.

================================================================

