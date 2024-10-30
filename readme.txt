=== BruteBank - WP Security & Firewall ===
Contributors: brutebank
Tags: security, disable xmlrpc, wp security, firewall, bruteforce, secure, ban, login protection, hacker protection, security plugin
Requires at least: 4.0
Tested up to: 6.1
Stable tag: 1.10
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 5.4

Blocking website attacks from your phone, designed for WordPress.

== Description ==

= Blocking website attacks from your phone, designed for WordPress users. =
BruteBank is an interactive firewall plugin that allows WordPress owners and server administrators to receive real time threat notifications via a mobile app. This app then allows for immediate threat mitigation by blocking attacking IP addresses.

= Login Monitoring =

The BruteBank Wordpress plugin monitors invalid login attempts to username and password logins as well as password protected pages. It then reports those attacks to the cloud for processing and fingerprinting.

= App Alerts = 

Using the app you and your team are able to review attacking IP addresses organized by country and user. Blocking specific addresses or entire country and user targeted attacks with a swipe of your finger.

= Instant Firewall Blocks =

The threats you block in the app are imported by the BruteBank Wordpress plugin blocking attackers instantly. Any further attempts by the attacker will result in a 403 forbidden message. 

= XML-RPC Blocking =

XML-RPC is a WordPress API that allows developers to login and manage your website content. Unless you’re sure your website is using this feature, you should disable it. With BruteBank you can disable the XML-RPC API to prevent attackers from brute forcing your login credentials with a flip of a switch. 

= Installation =

Setting up the Wordpress plugin is as easy as a few clicks. 

= Installing the Wordpress Plugin =

1. Login to your Wordpress WP-Admin area as an Administrator.
2. Click on “Plugins -> Add New” in the left hand menu.
3. Search for “Brutebank” in the keyword search. 
4. Click “Install Now” next to the BruteBank plugin. 

= Configuring the Wordpress Plugin =

1. Navigate to the “Plugins -> Installed Plugins” section in the left hand menu. 
2. Click “Activate” next to the BruteBank plugin. 
3. Click “Settings” next to the BruteBank plugin.
4. Copy and paste the public key and secret key you created in the “Setting up a Server key pair” section under “Getting Started”. 
5. Click the “Update” button.

Your Wordpress plugin is now configured and reporting attacks to your mobile app! 

**Learn more and signup at [BruteBank.io](https://www.brutebank.io)**

== Screenshots ==

1. Configuration of plugin

== Frequently Asked Questions ==
= How much does BruteBank cost? =
BruteBank for Wordpress is $4.95 per month. That’s only $0.16 per day!

= Can I invite other users to manage threats? =
Yes, you can invite an unlimited number of users to your team. 

= What are team rule sets?  =
Team rule sets are blocks identified by other servers on your team. You can configure the rule import URL to include or exclude team rules easily.

= Where can I get support? =
You can contact our support team via our website at: https://www.brutebank.io/support

= Does BruteBank disable XMLRPC? =
Yes! Simply turn on the "Disable XMLRPC" feature within the BruteBank settings. You can also add the below code to your .htaccess file if you'd like to remove it completely from public access, which is recommended.

`<FilesMatch "^xmlrpc\.php$">`
`  Require all denied`
`</FilesMatch>`

= Does this plugin offer 2FA ( Two Factor Authentication )? =
Yes! Simple turn on the "Enable 2FA ( Two Factor Authentication ) feature win the BruteBank settings. 

== Changelog ==

= 1.10 =
*Release Date - February 15, 2023*
* WP-Login - added cached IP check before individual IP check of user.

= 1.9 =
*Release Date - December 14, 2022*
* Admin form updates

= 1.8 =
*Release Date - June 1, 2022*
* Server key validation and a warning when protection is not enabled.

= 1.7 =
*Release Date - February 22, 2022*
* 2FA updates

= 1.6 =
*Release Date - December 2, 2021*
* Performance improvements & server key management

= 1.5 =
*Release Date - November 29, 2021*
* Caching of top attacking IP addresses locally for greater speed

= 1.4.2 =
*Release Date - August 31, 2021*
* Extended the 2FA expiration to 30 seconds

= 1.4.1 =
*Release Date - August 31, 2021*
* Addition of <?php tags for servers that dont support PHP short tags

= 1.4 =
*Release Date - August 27, 2021*
* Introducing Two Factor Authentication ( 2FA )

= 1.3.2 =
*Release Date - January 20, 2021*
* Plugin name update to: BruteBank - WP Security & Firewall

= 1.3.1 =
*Release Date - January 13, 2021*
* Removed database upgrade IF NOT EXISTS for MySQL support.

= 1.3 =
*Release Date - January 12, 2021*
* Added support for 3rd party plugin "Password Protected" by Ben Husan.

= 1.2 =
*Release Date - December 17, 2020*
* Feature update: Added the ability to disable XMLRPC a well known bruteforce hacking interface in Wordpress.

= 1.1 =
*Release Date - July 22, 2020*
* WP Admin menu icon update
* Additional IP address checks to ensure the correct IP is being logged.

= 1.0 =
*Release Date - May 29, 2020*
* Initial release. 