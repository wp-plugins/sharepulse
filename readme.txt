=== Share Pulse ===
Contributors: Jack Reichert
Plugin Name: Share Pulse
Plugin URI: http://www.jackreichert.com/plugins/sharepulse/
Donate link: http://www.jackreichert.com/the-human-fund/
Tags: twitter, comments, statistics, facebook, share, social media, social widget, stats, most popular
Author URI: http://www.jackreichert.com/
Author: Jack Reichert
Requires at least: 2.9
Tested up to: 3.2
Stable tag: trunk

SharePulse ranks, in a sidebar widget, your site's articles which had the greatest social impact. Stats are tabulated from most commented posts as well as the Twitter and Facebook APIs.

== Description ==

SharePulse finds and ranks your site's posts that have the greatest social impact. The stats are gathered from Twitter (Using the Topsy API), Facebook as well as your own site's most commented posts. You can then display these posts in your site's sidebar showing off your posts which have had the greatest social impact. Each post is displayed with the total number of tweets, shares and comments along with a sum of all three.

== Installation ==

   1. Upload the `SharePulse` folder to the `/wp-content/plugins/` directory
   2. Activate the plugin through the "Plugins" menu in WordPress
   3. Drag `Share Pulse` widget into the sidebar via admin area "Appearance>Widgets"
   4. All settings are accessible from widget.

== Settings ==

Header text: SharePulse allows you to customize the widget title

Twitter Source: You can change the Twitter account that is RTed @ when the articles are tweeted.

Awe.sm api key: If you have an awe.sm account you can enter your api key here so that you can track the stats of your shortened urls.

Display stats from the past: You can choose to display the stats from the past month, week, day or all time.

Number of posts to display: You can choose the number of posts to display in the widget. If the APIs do not return enough info to display as many as you like, which can happen if your site is young, then SharePulse will take random posts to fill in the missing posts.

Allow linkback at bottom of widget: We did not ask for payment to develop this widget yet we are sharing it freely, if you like it please show your appreciating by allowing the linkback.

== Frequently Asked Questions ==

= I just Tweeted/Shared a post, why isn't it showing up? =

The short answer: SharePulse relies on the stats from different APIs (Application Programming Interface) they sometimes take a while to process the data.

The full answer: An API is a way for different sites/services to provide programmers access tools that they offer. In this case, SharePulse asks Topsy (a service that collects data about articles being shared on twitter) and Facebook for the articles that were shared the most in the past day, week, month and of all time from your website, SharePulse then calculates which articles have had the greatest social impact and presents that to you. Sometimes it takes time for the APIs to update the data they are providing. In addition, to avoid overloading the APIs, many APIs limit the number of calls allowed. SharePulse only refreshes its data every 15 minutes. If it tried more often it would be blocked from the APIs for up to an hour leaving you with an empty widget. If you don't mind hacking things, SharePulse comes with cron cycles for to refresh the data in shorter periods of time. Is you do this, though, you run the risk of maxing out your API allowance.

The bottom line. It's not SharePulse, it's the wonderful services they use. But be patient, they provide a great service. This too will update.

= I just installed SharePulse and I think it's broken. It's just showing random posts. =

Until you share a minimum number of articles SharePulse displays random posts. Once you have shared more posts than the number of posts you requested to display in the widget settings SharePulse will start tabulating.

= The Thumbnails are not showing up?! Help?!?!?! =

In this version SharePulse has disabled the thumbnail feature. If you want them back contact me.

= Why was Digg removed? =
Due to the nature of the Digg system I didn't think including it would accurately portray a post's true social impact. So I removed it.

= I would like to make a suggestion, how do I contact you? =

Feel free to [contact me](http://www.jackreichert.com/contact/). 
Praise, suggestions, comments and even bugs found are welcome.


== Upgrade Notice ==

Version 1.1 is clunky and will slow down your page load. Upgrade to 2.0 for a better, more efficient plugnin.

== Changelog ==

= 2.0.1 =
* New clean sleek design
* Plugin rebuilt bottom up to increase efficiency.
* Caching added.
* API calls done asynchronously.
* Digg stats removed. Will consider returning them with popular demand.
* Tumbnails removed. 

= 1.1 =
* First release

= 1.0 =
* Nothing really happened here. First version is 1.1 for aesthetic reasons.

== Donations ==

If you like this plugin, [buy me a beer](http://www.jackreichert.com/the-human-fund)!
