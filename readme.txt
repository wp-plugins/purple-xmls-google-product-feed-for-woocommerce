=== Google XML Product Feed for WooCommerce ===
Contributors: PurpleTurtlePro 
Donate link: http://www.purpleturtle.pro
Tags: WooCommerce, Google, Merchant, Shopping
Requires at least: 3.0Tested up to: 3.6.1Version: 3.1.1Stable tag: 3.1.1License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Purple XMLs Google Product Feed for WooCommerce creates an XML output by of any category in the WooCommerce installation

== Description ==

The Purple XMLs Google Product Feed for WooCommerce creates an XML output of any category in the WooCommerce installation. It includes all required feed types for Google Merchant Center for non-variable products.

The Purple XMLs Plugin creates a digestible XML feed for Google to import WooCommerce Products into a Google Merchant Account. This Plugin creates an XML output of entire Categories that adheres to Google Feed Specifications.

> [**Upgrade to Pro**](http://www.w3bdesign.ca/woocommerce-google-merchant-feed/) // The Pro version supports unlimited Attributes and Variations
> **AUTOMATIC** // You can forget it! -Added products update the feed instantly!
> **SIMPLE** // Configured in less than 2 minutes, it works automatically!The plugin includes some great options to create your own customized feed.= Upcoming Features =* Ebay Feed Support* Amazon Feed Support

= Help & Support =Suggestions and Feedback should be directed to our [Client Hub ](https://www.purpleturtle.pro/submitticket.php)

#### Functionality
* Creates an XML output that includes properly formatted XML for Google Shopping and Google Merchant


== Installation ==
Upload the plugin to the plugins directory of your Wordpress install and activate the plugin. 
 Navigate to the settings and select your category, then the google category, and finally enter the brand name in the text area. 
Generate the feeb by clicking the button . 

you must find the single general google category that best suits your needs here:
http://support.google.com/merchants/bin/answer.py?hl=en&answer=160081

== Screenshots ==
1. 4 Steps to the feed

== Help ==
Suggestions and Feedback should be directed to our [Client Hub ](https://www.purpleturtle.pro/submitticket.php)

Feed errors? I have a video for that
http://www.youtube.com/watch?v=RueV471NbJ4

== Frequently Asked Questions == 
Question: My Feed has errors<br />
Answer: Make sure all of your titles and descriptions contain proper markup <br />
Titles and descriptions can not contain just & .. needs to be fully qualified HTML ie. "&amp;"
<br />
Question: My Feed has no output<br />
Answer:  Try changing the line in the google-feed.php from:<br />
$product = get_product($prod->ID);<br />
to $product = new WC_Product($prod->ID);<br />

== Changelog == 
= Version 1.2 19/10/2013 =* **Added** Css for styling* **Added** options panel improvements= Version 1.1 17/10/2013 =* First functional release. Enjoy!= Version 1.0 15/10/2013 =* First Release