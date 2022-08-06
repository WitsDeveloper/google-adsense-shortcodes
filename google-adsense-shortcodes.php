<?php

/**
 * Plugin Name: Google AdSense Shortcodes
 * Description: Create Google AdSense shortcodes for easiler implementation into posts and pages.
 * Version: 1.0
 * Requires at least: 5.6
 * Requires PHP: 7.3
 * Author: Sammy Waweru
 * Author URI: http://www.witstechnologies.co.ke
 * License: GPLv3 or later
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: google-adsense-shortcodes
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class GoogleAdsenseShortcodes {
 /* Holds the values to be used in the fields callbacks */
 private $options;

 /* Start up */
 public function __construct() {
  add_action('admin_menu', array($this, 'add_plugin_page'));
  add_action('admin_init', array($this, 'page_init'));
  add_action('admin_notices', array($this, 'gass_admin_notices'));
 }

 /* Add options page */
 public function add_plugin_page() {
  // Page will be under "Settings"
  $gass_admin_page = add_options_page(
   'Google AdSense Shortcodes Admin',
   'Google AdSense Shortcodes',
   'edit_pages',
   'gass-settings-admin',
   array($this, 'create_admin_page')
  );
  add_action('load-' .  $gass_admin_page, 'gass_help_tab');
 }

 /* Options page callback */
 public function create_admin_page() {
  // Set class property
  $this->options = get_option('gass_settings');
  $pubID = isset($this->options['gass_pubid']) ? esc_attr($this->options['gass_pubid']) : '';
?>
  <div class="wrap">
   <h2>Google AdSense Shortcodes Settings</h2>
   <p>* See "Help" tab at top right of page for usage tips.</p>
   <form method="post" action="options.php">
    <?php
    // This prints out all hidden setting fields
    settings_fields('gass_settings');
    do_settings_sections('gass-settings-admin');

    // Allow user to create short here
    if (!empty($pubID)) {
    ?>
     <script>
      jQuery(document).ready(function($) {
       $('#gass_type, #gass_slot').on('change', function(e) {
        var gass_type = $("#gass_type option:selected").text();
        var gass_slot = $('#gass_slot').val();
        $('#gass_code_snippet').html('[google_adsense_shortcode type="' + gass_type + '" slot="' + gass_slot + '"]');
       });
      });
     </script>
     <h3>Select the type and enter slot ID to generate your shortcode.</h3>
     <label for="type">Type</label>
     <select id="gass_type" name="type">
      <option value="In-article">In-article</option>
      <option value="Display-square">Display-square</option>
      <option value="Display-vertical">Display-vertical</option>
      <option value="Display-horizontal">Display-horizontal</option>
      <option value="Multiplex">Multiplex</option>
     </select>
     <label for="slot">Slot</label>
     <input type="text" id="gass_slot" name="slot" value="1234567890">
     <p>Copy the generated shortcode below:</p>
     <code id="gass_code_snippet" class="gass-code-snippet">[google_adsense_shortcode type="In-article" slot="1234567890"]</code>
    <?php
    }

    // Print the submit button
    submit_button();
    ?>
   </form>
  </div>
<?php
 }

 /* Register and add settings */
 public function page_init() {
  register_setting(
   'gass_settings', // Option group
   'gass_settings', // Option name
   array($this, 'gass_sanitize') // Sanitize
  );

  add_settings_section(
   "gass_section", // ID
   'Required Settings', // Title
   array($this, 'gass_print_section_info'), // Callback
   'gass-settings-admin' // Page
  );

  add_settings_field(
   "gass-pubid", // ID
   "AdSense Publication ID:", // Title
   array($this, 'gass_callback'), // Callback
   'gass-settings-admin', // Page
   "gass_section", // Section
   array('id' => 'gass-pubid', 'option_name' => 'gass_pubid') // Args
  );
 }

 /* Sanitize settings fields */
 public function gass_sanitize($args) {
  if (!isset($args['gass_pubid'])) {
   $args['gass_pubid'] = '';
   add_settings_error('gass_settings', 'gass_invalid_pubid', 'Please enter a valid publication ID.', $type = 'error');
  } else {
   $args['gass_pubid'] = sanitize_text_field($args['gass_pubid']);
  }
  return $args;
 }

 /* Print the fields */
 public function gass_callback($val) {
  $id = $val['id'];
  $option_name = $val['option_name'];
  $option_value = isset($this->options['gass_pubid']) ? esc_attr($this->options['gass_pubid']) : '';

  printf(
   '<input type="text" id="%s" name="gass_settings[%s]" value="%s" placeholder="pub-0000000000000000" />',
   esc_attr($id),
   esc_attr($option_name),
   esc_attr($option_value)
  );
 }

 /* Print the Section text */
 public function gass_print_section_info() {
  $guide = '<p>Enter your Google AdSense publication ID below and click "Save Changes".</p>';
  print($guide);
 }

 /* Display the validation errors and update messages */
 public function gass_admin_notices() {
  settings_errors();
 }
}

if (is_admin())
 $gass_settings_page = new GoogleAdsenseShortcodes();

function gass_help_tab() {
 $screen = get_current_screen();
 $screen->add_help_tab(array(
  'id' => 'gass_help',
  'title' => __('Help'),
  'content' => '<ul>
			<li>You must first add your Google AdSense publication ID. In the format pub-0000000000000000</li>
   <li>To get publication ID from your Google AdSense account, go to <a href="https://www.google.com/adsense/start/" target="_blank">Google AdSense</a></li>
   <li>This plugin supports adding ads by units as created on Google AdSense</li>
			<li>Insert [google_adsense_shortcode type="In-article" slot="1234567890"] shortcode where you\'d like your ad to appear</li>
   <li>After you insert the code, make sure to change the type and slot as guided below.</li>
   <li>Supported types: Multiplex, In-article, Display-square, Display-vertical, Display-horizontal</li>
   <li>Slot: Get that from Google AdSense code</li>
		</ul>',
 ));
}

function add_google_adsense($pubID) {
 $GoogleAdsense = '';
 if (!empty($pubID)) {
  $GoogleAdsense .= '<!--google adsense--><script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-' . $pubID . '" crossorigin="anonymous"></script><!--google adsense -->';
 }

 return $GoogleAdsense;
}

// Display an ad unit
function add_google_adsense_unit($atts) {
 extract(shortcode_atts(array(
  'type' => 'In-article',
  'slot' => '',
 ), $atts));
 // Get the Google AdSense Publication ID
 $options = get_option('gass_settings');
 $pubID = isset($options['gass_pubid']) ? esc_attr($options['gass_pubid']) : '';
 $GoogleAdsense = '';
 if (!empty($pubID) && !empty($type) && !empty($slot)) {
  $GoogleAdsense .= add_google_adsense($pubID);
  switch ($type) {
   case "In-article":
    $GoogleAdsense .= '<div class="google_ad_block_' . strtolower($type) . '">
    <ins class="adsbygoogle"
     style="display:block; text-align:center;"
     data-ad-client="ca-' . $pubID . '"
     data-ad-slot="' . $slot . '"
     data-ad-format="fluid"
     data-ad-layout="in-article">
    </ins>
    <script>
     (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
    </div>';
    break;
   case "Multiplex":
    $GoogleAdsense .= '<div class="google_ad_block_' . strtolower($type) . '">
    <ins class="adsbygoogle"
     style="display:block;"
     data-ad-client="ca-' . $pubID . '"
     data-ad-slot="' . $slot . '"
     data-ad-format="autorelaxed">
    </ins>
    <script>
     (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
    </div>';
    break;
   case "Display":
   case "Display-square":
   case "Display-vertical":
   case "Display-horizontal":
    $GoogleAdsense .= '<div class="google_ad_block_' . strtolower($type) . '">
    <ins class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-' . $pubID . '"
     data-ad-slot="' . $slot . '"
     data-ad-format="auto"
     data-full-width-responsive="true">
    </ins>
    <script>
     (adsbygoogle = window.adsbygoogle || []).push({});
    </script>
    </div>';
    break;
  }
 }

 return $GoogleAdsense;
}

add_shortcode('google_adsense_shortcode', 'add_google_adsense_unit');
