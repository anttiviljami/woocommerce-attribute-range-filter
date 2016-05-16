<?php
/**
 * Plugin name: WooCommerce Attribute Range Filter
 * Plugin URI: https://github.com/anttiviljami/woocommerce-attribute-range-filter
 * Description: Filter a range of numeric product attributes with a range slider
 * Version: 0.1
 * Author: @anttiviljami
 * Author URI: https://github.com/anttiviljami
 * License: GPLv3
 * Text Domain: wc-attr-range-filter
 */

/** Copyright 2016 Antti Kuosmanen
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 3, as
  published by the Free Software Foundation.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists('WooCommerce_Attribute_Range_filter') ) :

class WooCommerce_Attribute_Range_filter {
  public static $instance;

  public static function init() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new WooCommerce_Attribute_Range_filter();
    }
    return self::$instance;
  }

  private function __construct() {
    // register the widget
    add_action( 'widgets_init', [ $this, 'register_widgets' ] );

    // include jquery-ui and jquery-ui-slider in the theme
    add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

    // load textdomain for translations
    add_action( 'plugins_loaded', [ $this, 'load_our_textdomain' ] );
  }

  public function register_widgets() {
    require_once 'classes/class-range-filter-widget.php';
    register_widget( 'Range_Filter_Widget' );
  }

  public function enqueue_scripts() {
    global $wp_scripts;
    wp_enqueue_script( 'jquery-ui-slider' );

    $ui = $wp_scripts->query('jquery-ui-core');

    $protocol = is_ssl() ? 'https' : 'http';
    $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
    wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
  }

  /**
   * Load our textdomain
   */
  public static function load_our_textdomain() {
    load_plugin_textdomain( 'wc-attr-range-filter', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
  }
}

endif;

// init the plugin
$woocommerce_attribute_range_filter = WooCommerce_Attribute_Range_filter::init();
