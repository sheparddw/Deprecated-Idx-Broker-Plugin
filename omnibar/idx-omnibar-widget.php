<?php

function idx_omnibar_basic ($plugin_dir, $idxUrl){
  //css and js have been minified and combined to help performance
  wp_enqueue_style('idx-omnibar', plugins_url('/css/idx-omnibar.min.css', dirname(__FILE__)));
  wp_register_script('idx-omnibar-js', plugins_url('/js/idx-omnibar.min.js', dirname(__FILE__)));
  //inserts inline variable for the results page url
  wp_localize_script('idx-omnibar-js', 'idxUrl', $idxUrl);
  wp_enqueue_script('idx-omnibar-js');
  wp_enqueue_script('idx-location-list', plugins_url('/js/locationlist.json', dirname(__FILE__))); 

  return <<<EOD
    <form class="idx-omnibar-form idx-omnibar-original-form">
      <input class="idx-omnibar-input" type="text" placeholder="City, Postal Code, Address, or Listing ID" onblur="if (this.value == '') {this.value = 'City, Postal Code, Address, or Listing ID';}" onfocus="if (this.value == 'City, Postal Code, Address, or Listing ID') {this.value = '';}"><button type="submit" value="Search"><i class="fa fa-search"></i><span>Search</span></button>
      <div class="idx-omnibar-extra idx-omnibar-price-container" style="display: none;"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bed-container" style="display: none;"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container" style="display: none;"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0" step="0.01"></div>
    </form>
EOD;
} 

function idx_omnibar_extra ($plugin_dir, $idxUrl){
   //css and js have been minified and combined to help performance
  wp_enqueue_style('idx-omnibar', plugins_url('/css/idx-omnibar.min.css', dirname(__FILE__)));
  wp_register_script('idx-omnibar-js', plugins_url('/js/idx-omnibar.min.js', dirname(__FILE__)));
  //inserts inline variable for the results page url
  wp_localize_script('idx-omnibar-js', 'idxUrl', $idxUrl);
  wp_enqueue_script('idx-omnibar-js');
  wp_enqueue_script('idx-location-list', plugins_url('/js/locationlist.json', dirname(__FILE__))); 

  return <<<EOD
    <form class="idx-omnibar-form idx-omnibar-extra-form">
      <input class="idx-omnibar-input" type="text" placeholder="City, Postal Code, Address, or Listing ID" onblur="if (this.value == '') {this.value = 'City, Postal Code, Address, or Listing ID';}" onfocus="if (this.value == 'City, Postal Code, Address, or Listing ID') {this.value = '';}">
      <div class="idx-omnibar-extra idx-omnibar-price-container"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0" title="No commas or dollar signs are allowed."></div><div class="idx-omnibar-extra idx-omnibar-bed-container"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0" step="0.01" title="Only numbers and decimals are allowed"></div>
      <button type="submit" value="Search"><i class="fa fa-search"></i><span>Search</span></button>
    </form>
EOD;
} 

//Creates an omnibar widget
class IDX_Omnibar_Widget extends WP_Widget
{
  function __construct()
  {
    $widget_ops = array('classname' => 'IDX_Omnibar_Widget', 'description' => 'An Omnibar Search Widget for use with IDX WordPress Sites');
    parent::__construct('IDX_Omnibar_Widget', 'IDX Omnibar Search Widget', $widget_ops);
  }

  function form($instance)
  {
    $instance = wp_parse_args((array) $instance, array( 'title' => '' ));
    $title = $instance['title'];
?>
  <p><label for="<?php echo esc_attr($title); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
  }

  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }

  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);

    echo $before_widget;
    $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);

    if (!empty($title))
      echo $before_title . $title . $after_title;
    $plugin_dir = plugins_url();

    $idxUrl = get_option('idx-results-url');

      //grab url from database set from get-locations.php
    // Widget HTML:
    echo idx_omnibar_basic($plugin_dir, $idxUrl);
    echo $after_widget;
  }
}
//second widget with extra fields:
class IDX_Omnibar_Widget_Extra extends WP_Widget {
  function __construct()
  {
    $widget_ops = array('classname' => 'IDX_Omnibar_Widget_Extra', 'description' => 'An Omnibar Search Widget with extra fields for use with IDX WordPress Sites');
    parent::__construct('IDX_Omnibar_Widget_Extra', 'IDX Omnibar With Extra Fields', $widget_ops);
  }
  function form($instance)
  {
    $instance = wp_parse_args((array) $instance, array( 'title' => '' ));
    $title = $instance['title'];
?>
  <p><label for="<?php echo esc_attr($title); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
  }

  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);

    echo $before_widget;
    $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);

    if (!empty($title))
      echo $before_title . $title . $after_title;
    $plugin_dir = plugins_url();

      //grab url from database set from get-locations.php
    $idxUrl = get_option('idx-results-url');

    // Widget HTML:
    echo idx_omnibar_extra($plugin_dir, $idxUrl);
    echo $after_widget;
  }
}

function add_omnibar_shortcode(){
      $idxUrl = get_option('idx-results-url');
      $plugin_dir = plugins_url();
      
      return idx_omnibar_basic($plugin_dir, $idxUrl);
}
function add_omnibar_extra_shortcode(){
      $idxUrl = get_option('idx-results-url');
      $plugin_dir = plugins_url();
      
      return idx_omnibar_extra($plugin_dir, $idxUrl);
}


function show_omnibar_shortcodes($type, $name){
  $widget_shortcode = '['.$type.']';
            $available_shortcodes = '<div class="each_shortcode_row">';
            $available_shortcodes .= '<input type="hidden" id=\''.$type.'\' value=\''.$widget_shortcode.'\'>';
            $available_shortcodes .= '<span>'.$name.' &nbsp;<a name="'.$type.'" href="javascript:ButtonDialog.insert(ButtonDialog.local_ed,\''.$type.'\')">insert</a></span>';
            $available_shortcodes .= '</div>';

  echo $available_shortcodes;
}

//Initialize Instances of Widget Classes
add_action( 'widgets_init', create_function('', 'return register_widget("IDX_Omnibar_Widget");') );
add_action( 'widgets_init', create_function('', 'return register_widget("IDX_Omnibar_Widget_Extra");') );
add_shortcode('idx-omnibar', 'add_omnibar_shortcode');
add_shortcode('idx-omnibar-extra', 'add_omnibar_extra_shortcode');






