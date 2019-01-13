=== GuardianKey ===
Contributors: gbernardes,reddhatt
Tags: security, authentication,block hackers,login security
Requires at least: 4.4
Tested up to: 5.0.2
Stable tag: 1.3
Requires PHP: 5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

GuardianKey is a service to protect systems in real-time against authentication attacks. Through an advanced Machine Learning approach, it can detect and block malicious accesses to the system, notify the legitimate user and the system administrator about such access attempts.

== Description ==

GuardianKey is a service to protect systems in real-time against authentication attacks. Through an advanced Machine Learning approach, it can detect and block malicious accesses to the system, notify the legitimate user and the system administrator about such access attempts.

Beyond the security, the GuardianKey solution provides a good user experience, because the user is not required to provide extra information or to execute tasks during the login.

GuardianKey's approach provides a risk assessment in real-time. The events and risks can be explored in the GuardianKey's administration panel.

== How GuardianKey works ==

The GuardianKey detection engine analyzes the events sent by your online system to the GuardianKey servers. 

The detection engine uses Machine Learning and our secret mathematical risk formula to combine the following three analysis approaches: Threat Intelligence, Behavioral Profiling, and Psychometric Profiling. Using these three pillars, our engine computes a risk for each event sent by the protected systems. In real time, the online attempt can be blocked, an extra requirement can be requested to the user, or notifications can be triggered.

All data sent to GuardianKey servers are doubly encrypted, and NOT send passwords or sensitive data.

**More information at https://guardiankey.io/**

== Plugin Installation ==

1. Install GuardianKey from Wordpress plugin directory, and activate plugin
2. Access Administration-\>Tools-\>GuardianKey
3. If you want notify users, change "Notify Users" option to "yes"


== Using GuardianKey ==

Access https://panel.guardiankey.io and login using the credentials sent to your e-mail address during the registration. You can recover the pass if you forgot it.

There is a documentation for the panel available at https://guardiankey.io/panel-documentation/

If you have troubles, join the community to get help, at https://groups.google.com/forum/#!forum/guardiankey

== Screenshots ==

1. Admin Panel
2. E-mail of notification

== Changelog ==

= 1.3 =
* Update sendevent function for use REST interface instead UDP

= 1.2 =
* Fix erros 

= 1.1 =
* Fix bugs in webhook function
* Update readme.txt
 
= 1.0 =
* Intial release