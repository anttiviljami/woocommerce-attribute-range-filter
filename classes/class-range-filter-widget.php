<?php

class Range_Filter_Widget extends WP_Widget {

  /**
   * Sets up the widgets name etc
   */
  public function __construct() {
    $opts = [
      'classname' => 'wc-range-filter-widget',
      'description' => __('Filter a range of numeric product attributes with a range slider.', 'wc-attr-range-filter'),
    ];
    parent::__construct( 'wc-range-filter-widget', 'WooCommerce Attribute Range Filter', $opts );
  }

/**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  public function widget( $args, $instance ) {
    $maxamps = WooCommerce_Attribute_Range_filter::$instance->maxamps;
    if( $maxamps ) {
      echo $args['before_widget'];
      if ( ! empty( $instance['title'] ) ) {
        echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
      }
?>
<p><input type="text" id="amount" readonly></p>

<div id="slider-range"></div>
<script>
(function($) {
  $(function() {
    $( "#slider-range" ).slider({
      range: true,
      min: 0,
      max: <?php echo $maxamps; ?>,
      step: 10,
      values: [ 0, <?php echo $maxamps; ?> ],
      slide: function( event, ui ) {
        $( "#amount" ).val( ui.values[ 0 ] + " A - " + ui.values[ 1 ] + " A" );
      },
      stop: function( event, ui ) {
        var newlocation = $.extend({}, window.location);
        newlocation.search = "?minamps=" + ui.values[ 0 ] + "&maxamps=" + ui.values[ 1 ];
        console.log(newlocation);
        $('.products').addClass('loading');
        $('#main').load( newlocation.pathname + newlocation.search + ' #main', function(e) {
          //$('.products').removeClass('loading');
        } );
      }
    });
    $( "#amount" ).val( $( "#slider-range" ).slider( "values", 0 ) +
      " A - " + $( "#slider-range" ).slider( "values", 1 ) + " A" );
  });
})(jQuery);
</script>
<style>
.products { position: relative; }
.products.loading::after {
  display: block;
  content: " ";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255,255,255,.5);
}
</style>
<?php
      echo $args['after_widget'];
    }
  }

  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form( $instance ) {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'New title', 'text_domain' );
    ?>
    <p>
    <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
    </p>
    <?php
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance ) {
    $instance = array();
    $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

    return $instance;
  }
}
