<?php
//Creates an omnibar widget
class IDX_Omnibar_Widget extends WP_Widget
{
  function IDX_Omnibar_Widget()
  {
    $widget_ops = array('classname' => 'IDX_Omnibar_Widget', 'description' => 'An Omnibar Search Widget for use with IDX WordPress Sites');
    $this->WP_Widget('IDX_Omnibar_Widget', 'IDX Omnibar Search Widget', $widget_ops);
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

    global $wpdb;
    $urlList = $wpdb->get_results("SELECT idx_url FROM ".$wpdb->prefix."idx_omnibar", ARRAY_A);
    foreach($urlList as $item){
      foreach($item as $url){
          $idxUrl = $url;
      }
    }

      //grab url from database set from get-locations.php
    // Widget HTML:
    echo <<<"EOD"
    <form class="idx-omnibar-form idx-omnibar-original-form">
      <input class="idx-omnibar-input" type="text" placeholder="City, County, Zipcode, Address or Listing ID" onblur="if (this.value == '') {this.value = 'City, County, Zipcode, Address or Listing ID';}" onfocus="if (this.value == 'City, County, Zipcode, Address or Listing ID') {this.value = '';}"><input type="submit" value="Search">
      <div class="idx-omnibar-extra idx-omnibar-price-container"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bed-container"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0"></div>
    </form>
    <link rel="stylesheet" href="$plugin_dir/idx-omnibar/assets/css/awesomplete.css" media="screen" title="no title" charset="utf-8">
    <link rel="stylesheet" href="$plugin_dir/idx-omnibar/assets/css/idx-omnibar.css" media="screen" title="no title" charset="utf-8">
    <script>var idxUrl = '$idxUrl';</script>
    <script src="$plugin_dir/idx-omnibar/assets/js/awesomplete.min.js"></script>
    <script src="$plugin_dir/idx-omnibar/assets/js/idx-omnibar.js"></script>
    <script src="$plugin_dir/idx-omnibar/assets/js/locationlist.json" defer></script>
EOD;
    echo $after_widget;
  }
}
//second widget with extra fields:
class IDX_Omnibar_Widget_Extra extends WP_Widget {
  function IDX_Omnibar_Widget_Extra()
  {
    $widget_ops = array('classname' => 'IDX_Omnibar_Widget_Extra', 'description' => 'An Omnibar Search Widget with extra fields for use with IDX WordPress Sites');
    $this->WP_Widget('IDX_Omnibar_Widget_Extra', 'IDX Omnibar With Extra Fields', $widget_ops);
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

    global $wpdb;
    $urlList = $wpdb->get_results("SELECT idx_url FROM ".$wpdb->prefix."idx_omnibar", ARRAY_A);
    foreach($urlList as $item){
      foreach($item as $url){
          $idxUrl = $url;
      }
    }

      //grab url from database set from get-locations.php
    // Widget HTML:
    echo <<<"EOD"
    <form class="idx-omnibar-form idx-omnibar-extra-form">
      <input class="idx-omnibar-input" type="text" placeholder="City, County, Zipcode, Address, or Listing ID" onblur="if (this.value == '') {this.value = 'City, County, Zipcode, Address, or Listing ID';}" onfocus="if (this.value == 'City, County, Zipcode, Address, or Listing ID') {this.value = '';}">
      <div class="idx-omnibar-extra idx-omnibar-price-container"><label>Price Max</label><input class="idx-omnibar-price" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bed-container"><label>Beds</label><input class="idx-omnibar-bed" type="number" min="0"></div><div class="idx-omnibar-extra idx-omnibar-bath-container"><label>Baths</label><input class="idx-omnibar-bath" type="number" min="0"></div>
      <input type="submit" value="Search">
    </form>
    <link rel="stylesheet" href="$plugin_dir/idx-omnibar/assets/css/awesomplete.css" media="screen" title="no title" charset="utf-8">
    <link rel="stylesheet" href="$plugin_dir/idx-omnibar/assets/css/idx-omnibar.css" media="screen" title="no title" charset="utf-8">
    <script>var idxUrl = '$idxUrl';</script>
    <script src="$plugin_dir/idx-omnibar/assets/js/awesomplete.min.js"></script>
    <script src="$plugin_dir/idx-omnibar/assets/js/idx-omnibar.js"></script>
    <script src="$plugin_dir/idx-omnibar/assets/js/locationlist.json" defer></script>
EOD;
    echo $after_widget;
  }
}

//Initialize Instances of Widget Classes
add_action( 'widgets_init', create_function('', 'return register_widget("IDX_Omnibar_Widget");') );
add_action( 'widgets_init', create_function('', 'return register_widget("IDX_Omnibar_Widget_Extra");') );
