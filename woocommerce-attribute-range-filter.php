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
  public $maxamps;
  public $productquery;

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

    // filter the woocommerce main query
    add_action( 'woocommerce_product_query', [ $this, 'filter_by_current_range' ] );

    // hide empty categories according to the filter
    add_filter('woocommerce_product_subcategories_args', [ $this, 'filter_subcategories' ]);

    // load textdomain for translations
    add_action( 'plugins_loaded', [ $this, 'load_our_textdomain' ] );

    $this->maxamps = 0;
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

  /**
   * Filter the products
   */
  public function filter_by_current_range( $query ) {
    $this->productquery = $query;
    $query->set('tax_query', $this->get_tax_query());
  }

  public function get_tax_query() {
    $tax_query = [];

    if( isset( $_GET['maxamps'] ) && isset( $_GET['minamps'] ) ) {

      $terms = get_terms([
        'taxonomy' => 'pa_stromstyrke',
      ]);

      $amps = [];
      foreach( $terms as $term ) {
        if( (int) $term->slug <= (int) $_GET['maxamps'] && (int) $term->slug >= (int) $_GET['minamps'] ) {
          $amps[] = $term->term_id;
        }
      }

      $tax_query = [
        'relation' => 'AND',
        [
          'taxonomy' => 'pa_stromstyrke',
          'terms' => $amps,
          'operator' => 'IN',
        ],
/*        [
          'taxonomy' => 'pa_stromstyrke',
          'operator' => 'NOT EXISTS',
        ],*/
      ];
    }
    if( isset( $this->productquery->tax_query ) ) {
      $tax_query = array_merge( $this->productquery->tax_query->queries, $tax_query );
    }
    return $tax_query;
  }

  public function filter_subcategories( $cat_args ){
    $products = new WP_Query([
      'post_type' => 'product',
      'posts_per_page' => -1,
      'meta_query' => [
        [
          'key' => '_visibility',
          'value' => [
            'visible',
            'catalog',
          ],
          'compare' => 'IN',
        ],
      ],
      'tax_query' => $this->get_tax_query(),
    ]);
    $cat_ids = [];
    foreach( $products->posts as $product ) {
      $cats = get_the_terms( $product->ID, 'product_cat' );
      foreach( $cats as $cat ) {
        $cat_ids[] = $cat->term_id;
      }
      $amps = get_the_terms( $product->ID, 'pa_stromstyrke' );
      foreach( $amps as $amp ) {
        if( (int) $amp->slug > $this->maxamps ) {
          $this->maxamps = (int) $amp->slug;
        }
      }
    }
    $cat_ids = array_unique( $cat_ids );
    $cat_args['include'] = $cat_ids;
    if( isset( $_GET['maxamps'] ) && 0 === $cat_args['parent'] ) {
      unset($cat_args['parent']);
      $parents = get_terms( [
        'taxonomy' => 'product_cat',
        'parent' => 0,
        'hierarchical' => 1,
      ] );

      foreach( $parents as $parent ) {
        $cat_args['exclude'][] = $parent->term_id;
        $parentid = array_search( $parent->term_id, $cat_args['include'] );
        unset( $cat_args['include'][$parentid] );
      }
    }
    /*print_r( $cat_args );
    wp_die();*/
    return $cat_args;
  }
}

endif;

// init the plugin
$woocommerce_attribute_range_filter = WooCommerce_Attribute_Range_filter::init();
