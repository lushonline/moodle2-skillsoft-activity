
# Introduction #
The following lists some simple checks you can perform if you have issue using any of the Skillsoft content.

## Does your browser meet the minimum requirements? ##
The Skillsoft players are supported in a wide range of browsers but do require that a supported Java Runtime Environment (JRE) is installed.

An online check of your browser is available at http://browser.skillport.com

The Skillsoft technical requirements are available here http://documentation.Skillsoft.com - select "Content and Players Client System Requirements", found under "Quick Links".

## Have you allowed the Signed Java Applet to run? ##
As your Moodle instance and the Skillsoft OLSA servers are on different domains the Skillsoft Player Java Applet needs to be a signed applet to be able to communicate with Moodle.

This is a security limitation imposed on unsigned Java Applets.

When you launch the course the first time you should be prompted to "Accept" the SkillSoft Corporation certificate, if you do not accept the certificate the Applet cannot communicate with Moodle.

## Is your Moodle instance using HTTPS? ##
If your Moodle is using HTTPS is it using a "self-signed" SSL certificate.

By default the Oracle JRE does not trust "self-signed" SSL certificates and so the Skillsoft Player Applet will be prevented from communicating with Moodle.

You may be prompted by the JRE with a message "The web site's certificate cannot be verified. Do you want to continue?"

You will either need to ensure your users "Trust" your unsigned certificate or switch to a commercial SSL certificate.

Another option is to install your "self-signed" certificate into the JRE certificate store on all machines.


