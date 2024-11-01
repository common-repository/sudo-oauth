=== Sudo Oauth ===
Contributors: caotu
Tags: sudo,oauth
Donate link: http://nguyencaotu.com/donate
Requires at least: 4.5
Tested up to: 5.3
Stable tag: trunk

Sudo Oauth Plugin support to connect to id.sudo.vn system. This plugin only user in VietNam.

== Description ==
Plugin support to connect to ID Sudo system - a management account system.

== Installation ==
This section describes how to install the plugin and get it working.
1. Unzip the plugin contents to the `/wp-content/plugins/sudo-oauth/` directory
2. Activate the plugin through the \'Plugins\' menu in WordPress.
3. Go to Menu Sudo Oauth Settings, Input sitenam and key do connect.
4. Register account id.sudo.vn
5. Contact admin tucao@sudo.vn

Please contact tucao@sudo.vn do provide API do Connect with ID.Sudo.vn

== Frequently Asked Questions ==
What is id.sudo.vn ?
id.sudo.vn is a system manage user. You can only a account can login any webstie on system install id.sudo.vn.

How to create API?
Please contact tucao@sudo.vn. Only me can create API.


== Screenshots ==

1. Setting plugin for rules user

== Changelog ==

= 2.0.5 =
Big update:
    - Create new function open/close plugin
    - Update random link follow - nofollow using admin setting
    - Update Restrict backlink per post using admin setting
    - Update Accept upload media option
    - Update Show user info in bottom of post
    - Update limit post per day / user login
    - Update new style

= 2.0.4 =
Remove UTF-8-BOM Encoding from response data

= 2.0.3 =
Fix bug can't set auth cookie in some website installed plugin cache

= 2.0.2 =
Fix cannot modify header information - headers already sent by in pluggable

= 2.0.1 =
Big update:
    - Random link follow - nofollow (30%)
    - Restrict backlink per post (<= 5 backlink)
    - Accept upload media option
    - Show user info in bottom of post
	
= 1.0.6 =
Update datetime class for php version lower 5.3

= 1.0.5 =
Update set restrict multi category and add limit posts per day with user connect from id system

= 1.0.4 =
Fixed bug can't set restrict category

= 1.0.3 =
Update Set category for Author

= 1.0.2 =
Fixed bug hosting not install curl extension.

= 1.0.1 =
Fixed account only systeam login can not change passworrd