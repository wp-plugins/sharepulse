=== Share Pulse ===
Contributors: Jack Reichert
Plugin Name: Share Pulse
Plugin URI: http://www.jackreichert.com/plugins/sharepulse/
Donate link: http://www.jackreichert.com/the-human-fund/
Tags: Digg, Facebook, share statistics, Social Media, social widget, Stats, Twitter
Author URI: http://www.jackreichert.com/
Author: Jack Reichert
Requires at least: 2.7
Tested up to: 3.0.1
Stable tag: 1.1.1

SharePulse rank's, in a sidebar widget, your site's most popular articles of the week. Stats are tabulated from the Twitter, Digg and Facebook APIs.

== Description ==

Inspired by the "Most shared this week Powered by TweetMeme", widget found on the single article pages on Mashable.com, this plugin takes the concept one step farther...

Since adding "ReTweet" buttons to my site I noticed a sharp increase in the amount of articles that were tweeted and retweeted. The same holds true with your most popular posts, your readers want to know what your best content is, and what others think they should be reading. If you show them what other readers like it will create a positive reinforcing cycle and make those posts even more popular and the reach of your site will grow.

SharePulse tabulates the stats returned for the Twitter (actually, Topsy), Digg & Facebook APIs of the most shared articles in the past week and displays them in an attractive sidebar widget. Not only that, but it offers Retweet, Digg and fShare buttons for each link right next to the article, along with the stats of how many times your article was shared on each site.

This plugin compliments another plugin I developed, [ShareLinks](http://www.jackreichert.com/plugins/sharelinks/), which offers different themes for social buttons to add to your posts.

== Installation ==

   1. Upload the `SharePulse` folder to the `/wp-content/plugins/` directory
   2. Make sure that the permissions for the "SharePulse" & "cache" folders as well as the file timthumb.php are all set for 777. (Note: This is only applicable if you will be using thumbnails.) 
   3. Activate the plugin through the "Plugins" menu in WordPress
   4. Drag `Share Pulse` widget into the sidebar via admin area "Appearance>Widgets"
   5. All settings are accessible from there.

== Settings ==

Header text: SharePulse allows you to customize the widget title

Create thumbnails? The widget has two standard sizes, with thumbnails (300px) and without thumbnails (190px).

Theme: Currently we offer two themes for Sharepulse: "Bullet Holes" icons designed by Gopal Raju and "Social.me" icons designed by jwloh. (see screenshots below)

Twitter Source: You can also change the Twitter account that is RTed @ when the articles are tweeted.

Awe.sm api key: If you have an awe.sm account you can enter your api key here so that you can track the stats of your shortened urls.

Display stats from the past: You can choose to display the stats from the past month, week or day.

Number of posts to display: You can choose the number of posts to display in the widget. If the APIs do not return enough info to display as many as you like, which can happen if your site is young, then SharePulse will take random posts to fill in the missing posts.

Allow linkback at bottom of widget: We did not ask for payment to develop this widget yet we are sharing it freely, if you like it please show your appreciating by allowing the linkback.

== Frequently Asked Questions ==

= I just Tweeted/Dugg/Shared a post, why isn't it showing up? =

In short, SharePulse relies on the stats from different APIs (Application Programming Interface) they sometimes take a while to process the data.

The full answer: Basically, APIs are ways that different sites/services provide for programmers to access tools that they develop. In this case, I ask Topsy (a service that collects data about articles being shared on twitter), Digg and Facebook for the articles that were shared in the past week from your website, SharePulse then calculates which articles were shared the most and presents that to you. Sometimes it takes time for the APIs to update the data they are providing. In addition, to avoid overloading the APIs SharePulse will only request data no more than every 3 minutes. If it tried more often it would be blocked from the APIs for up to an hour (trust me, I tried).

The bottom line. It's not me, it's them. But be patient, they provide a great service. This too will update.

= I just installed SharePulse and I think it's broken. It's just showing random posts. =

Until you share a minimum number of articles SharePulse displays random posts. Once you have shared more posts than the number of posts you requested to display in the widget settings SharePulse will start tabulating.

= The Thumbnails are not showing up?! Help?!?!?! =

* Are the permissions set properly? (see step 2 in the installation process) the thumbnails rely on a file called "timthumb" which needs to be able to write files to the server, if the permissions are not set correctly then the thumbnails won't work.

* Are the images in your post hosted on *your* site or are you linking to other people's sites? Timthumb does not work if the images are not self hosted. In general it is good practice to upload your art to your own site. If the person whose image you are linking to decides to take it down, or block it, it will no longer show on you site… Do you need that risk? Besides, it's plain courtesy. You're using their art, the least you can do is not make them pay for extra server costs as well.

= I would like to make a suggestion, how do I contact you? =

I have a [contact form](http://www.jackreichert.com/uncategorized/sharepulse-info/) on my site (see bottom of page). 
Praise, suggestions, comments and even bugs found are welcome.

== Screenshots ==

1. The "Bulletholes" theme. `/tags/1.1/SharePulse_Bulletholes.png`
2. The "Bulletholes" theme in "Narrow view". `/tags/1.1/SharePulse_narrow_view_bullets.png`
3. The "Social.me" theme. `/tags/1.1/SharePulse_Social_me.png`
4. The "Social.me" theme in "Narrow view". `/tags/1.1/SharePulse_narrow_view_social.png`
5. Widget "settings". `/tags/1.1/SharePulse_widget_settings.png`

== Upgrade Notice ==

Nothing yet.

== Changelog ==

= 1.0 =
* Nothing really happened here. First version is 1.1 for aesthetic reasons.

== Donations ==

If you like this plugin, [buy me a beer](http://www.jackreichert.com/the-human-fund)!
