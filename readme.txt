=== WooCommerce Delete Expired Coupons ===
Contributors: pinchofcode
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=paypal@pinchofcode.com&item_name=Donation+for+Pinch+Of+Code
Tags: woocommerce, delete, coupons, expired, automatic
Requires at least: 3.8
Tested up to: 3.9.2
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically delete WooCommerce expired coupons.

== Description ==

Automatically delete WooCommerce expired coupons.

= Usage =

Activate the plugin, then go to *WooCommerce > Settings > Integration* and enable the automatic deletion ticking the option *Enable automatic deletion*.
By default, the plugin does not completely delete expired coupons, they are moved in the trash instead. You can force the deletion ticking the option *Force deletion* in the plugin settings.
Also, there you can change the automatic deletion frequency with the options *Frequency* and *Frequency type*.

Save your settings and the first run will be fired.

= About the Keep expired coupons options =

There are two options named *Keep expired coupons for* and *Type of "Keep expired coupons for"*.
These options are used if do not want to delete immediately the expired coupons. You can keep them for a while, and then delete them if your customers do not use them.

= Support =
For any support request, please create a new issue [here](https://github.com/PinchOfCode/woocommerce-delete-expired-coupons/issues).

**Note**: since the free nature of this plugin, the support may be discontinuous, but all the requests are checked and replied. We suggest to write on [GitHub](https://github.com/PinchOfCode/woocommerce-delete-expired-coupons/issues) to get faster support

= License =
Copyright (C) 2014 Pinch Of Code. All rights reserved.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

== Installation ==

= WP Installation =

1. Go to Plugins > Add New > Search
2. Type WooCommerce Delete Expired Coupons in the search box and hit Enter
3. Click on the button Install and then activate the plugin
4. Go to WooCommerce > Settings > Integration and change the plugin settings if needed

= Manual Installation =

1. Upload `woocommerce-delete-expired-coupons` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to WooCommerce > Settings > Integration and change the plugin settings if needed

== Changelog ==

= 1.1.1 =
* Fix: "Keep expired coupons for" option can be setted to 0 to avoid keeping coupons

= 1.1.0 =
* Add: Option to enable/disable the automatic deletion
* Add: Option to choose wheather to delete or simply trash expired coupons
* Add: Option to choose to keep expired coupons for a predefinied time before to trash/delete them
* Add: Option to choose the events frequency
* Fix: Coupons without expiry date are no longer deleted automatically. Thanks to daileycon for reporting

= 1.0.0 =
* First release

== Upgrade Notice ==

= 1.1.0 =
This update adds some settings. You will need to change them in WooCommerce > Settings > Integration to enable the automatic deletion and change the events frequency.