<?php

// LOAD Bohaute CORE (if you remove this, the theme will break)
require_once( 'library/bohaute.php' );

function bohaute_theme_setup() {

  // let's get language support going, if you need it
  load_theme_textdomain( 'bohaute', get_template_directory() . '/library/translation' );

  // enqueue base scripts and styles
  add_action( 'wp_enqueue_scripts', 'bohaute_scripts_and_styles', 999 );
  // ie conditional wrapper

  // launching this stuff after theme setup
  bohaute_theme_support();
 

  // adding sidebars to Wordpress (these are created in functions.php)
  add_action( 'widgets_init', 'bohaute_register_sidebars' );

  // cleaning up excerpt
  add_filter( 'excerpt_more', 'bohaute_excerpt_more' );

} /* end bohaute ahoy */

// let's get this party started
add_action( 'after_setup_theme', 'bohaute_theme_setup' );


/************* OEMBED SIZE OPTIONS *************/

if ( ! isset( $content_width ) ) {
  $content_width = 640;
}

/************* THUMBNAIL SIZE OPTIONS *************/

// Thumbnail sizes

add_image_size( '300x300', 300, 300, true );
add_image_size( '600x600', 600, 600, true );


add_filter( 'image_size_names_choose', 'bohaute_custom_image_sizes' );
function bohaute_custom_image_sizes( $sizes ) {
    return array_merge( $sizes, array(
        '300x300' => __('300px by 300px','bohaute')
    ) );
}

/************* ACTIVE SIDEBARS ********************/

// Sidebars & Widgetizes Areas
function bohaute_register_sidebars() {
  register_sidebar(array(
    'id' => 'sidebar1',
    'name' => __( 'Posts Sidebar', 'bohaute' ),
    'description' => __( 'The Posts sidebar.', 'bohaute' ),
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h4 class="widgettitle">',
    'after_title' => '</h4>',
  ));

  register_sidebar(array(
    'id' => 'sidebar2',
    'name' => __( 'Page Sidebar', 'bohaute' ),
    'description' => __( 'The Page sidebar.', 'bohaute' ),
    'before_widget' => '<div id="%1$s" class="widget %2$s">',
    'after_widget' => '</div>',
    'before_title' => '<h4 class="widgettitle">',
    'after_title' => '</h4>',
  ));


} // don't remove this bracket!


/************* COMMENT LAYOUT *********************/

// Comment Layout
function bohaute_comments( $comment, $args, $depth ) {
   $GLOBALS['comment'] = $comment; ?>
  <div id="comment-<?php comment_ID(); ?>" <?php comment_class('cf'); ?>>
    <article  class="cf">
      <header class="comment-author vcard">
        <?php
          echo get_avatar( $comment, 60 );
        ?>
        <?php // end custom gravatar call ?>
      </header>
      <?php if ($comment->comment_approved == '0') : ?>
        <div class="alert alert-info">
          <p><?php _e( 'Your comment is awaiting moderation.', 'bohaute' ) ?></p>
        </div>
      <?php endif; ?>
      <section class="comment_content cf">
        <?php printf(__( '<cite class="fn">%1$s</cite> %2$s', 'bohaute' ), get_comment_author_link(), edit_comment_link(__( '(Edit)', 'bohaute' ),'  ','') ) ?>
        <time datetime="<?php echo comment_time('Y-m-j'); ?>"><?php comment_time(__( 'F jS, Y', 'bohaute' )); ?></time>
        <?php comment_text() ?>
      </section>
      <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ?>
    </article>
  <?php // </li> is added by WordPress automatically ?>
<?php
} // don't remove this bracket!

add_filter( 'comment_form_defaults', 'bohaute_remove_comment_form_allowed_tags' );
function bohaute_remove_comment_form_allowed_tags( $defaults ) {

  $defaults['comment_notes_after'] = '';
  return $defaults;

}


/*******************************************************************
* These are settings for the Theme Customizer in the admin panel. 
*******************************************************************/
if ( ! function_exists( 'bohaute_theme_customizer' ) ) :
  function bohaute_theme_customizer( $wp_customize ) {
  
    /* color scheme option */
    $wp_customize->add_setting( 'bohaute_color_settings', array (
      'default' => '#c9a96e',
      'sanitize_callback' => 'sanitize_hex_color',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'bohaute_color_settings', array(
      'label'    => __( 'Theme Color Scheme', 'bohaute' ),
      'section'  => 'colors',
      'settings' => 'bohaute_color_settings',
    ) ) );

    if ( !function_exists( 'the_custom_logo' )) {
      /* logo fallback option */
      $wp_customize->add_section( 'bohaute_logo_section' , array(
        'title'       => __( 'Site Logo', 'bohaute' ),
        'priority'    => 1,
        'description' => __( 'Upload a logo to replace the default site name in the header', 'bohaute' ),
      ) );
      
      $wp_customize->add_setting( 'bohaute_logo', array(
        'sanitize_callback' => 'esc_url_raw',
      ) );
      
      $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'bohaute_logo', array(
        'label'    => __( 'Choose your logo (ideal width is 100-350px and ideal height is 35-40)', 'bohaute' ),
        'section'  => 'bohaute_logo_section',
        'settings' => 'bohaute_logo',
      ) ) );
    }
    
    /* social media option */
    $wp_customize->add_section( 'bohaute_social_section' , array(
      'title'       => __( 'Social Media Icons', 'bohaute' ),
      'priority'    => 32,
      'description' => __( 'Optional media icons in the header', 'bohaute' ),
    ) );
    
    $wp_customize->add_setting( 'bohaute_facebook', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );

    /* author bio in posts option */
    $wp_customize->add_section( 'bohaute_posts_section' , array(
      'title'       => __( 'Post Settings', 'bohaute' ),
      'priority'    => 35,
      'description' => __( '', 'bohaute' ),
    ) );

    $wp_customize->add_setting( 'bohaute_related_posts', array (
      'sanitize_callback' => 'bohaute_sanitize_checkbox',
    ) );
    
    $wp_customize->add_control('related_posts', array(
      'settings' => 'bohaute_related_posts',
      'label' => __('Disable the Related Posts?', 'bohaute'),
      'section' => 'bohaute_posts_section',
      'type' => 'checkbox',
    ));

    $wp_customize->add_setting( 'bohaute_author_area', array (
      'sanitize_callback' => 'bohaute_sanitize_checkbox',
    ) );
    
    $wp_customize->add_control('author_info', array(
      'settings' => 'bohaute_author_area',
      'label' => __('Disable the Author Information?', 'bohaute'),
      'section' => 'bohaute_posts_section',
      'type' => 'checkbox',
    ));
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_facebook', array(
      'label'    => __( 'Enter your Facebook url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_facebook',
      'priority'    => 101,
    ) ) );
  
    $wp_customize->add_setting( 'bohaute_twitter', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_twitter', array(
      'label'    => __( 'Enter your Twitter url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_twitter',
      'priority'    => 102,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_google', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_google', array(
      'label'    => __( 'Enter your Google+ url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_google',
      'priority'    => 103,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_pinterest', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_pinterest', array(
      'label'    => __( 'Enter your Pinterest url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_pinterest',
      'priority'    => 104,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_linkedin', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_linkedin', array(
      'label'    => __( 'Enter your Linkedin url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_linkedin',
      'priority'    => 105,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_youtube', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_youtube', array(
      'label'    => __( 'Enter your Youtube url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_youtube',
      'priority'    => 106,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_tumblr', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_tumblr', array(
      'label'    => __( 'Enter your Tumblr url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_tumblr',
      'priority'    => 107,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_instagram', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_instagram', array(
      'label'    => __( 'Enter your Instagram url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_instagram',
      'priority'    => 108,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_flickr', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_flickr', array(
      'label'    => __( 'Enter your Flickr url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_flickr',
      'priority'    => 109,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_vimeo', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_vimeo', array(
      'label'    => __( 'Enter your Vimeo url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_vimeo',
      'priority'    => 110,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_rss', array (
      'sanitize_callback' => 'esc_url_raw',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_rss', array(
      'label'    => __( 'Enter your RSS url', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_rss',
      'priority'    => 112,
    ) ) );
    
    $wp_customize->add_setting( 'bohaute_email', array (
      'sanitize_callback' => 'sanitize_email',
    ) );
    
    $wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'bohaute_email', array(
      'label'    => __( 'Enter your email address', 'bohaute' ),
      'section'  => 'bohaute_social_section',
      'settings' => 'bohaute_email',
      'priority'    => 113,
    ) ) );
    
  
  }
endif;
add_action('customize_register', 'bohaute_theme_customizer');

/**
 * Sanitize checkbox
 */
if ( ! function_exists( 'bohaute_sanitize_checkbox' ) ) :
  function bohaute_sanitize_checkbox( $input ) {
    if ( $input == 1 ) {
      return 1;
    } else {
      return '';
    }
  }
endif;

/**
* Apply Color Scheme
*/

  function bohaute_apply_color() {

  if( get_theme_mod('bohaute_color_settings') ){
    $color_scheme = esc_html( get_theme_mod('bohaute_color_settings') );
  } else{
    $color_scheme = '#c9a96e';
  }

  $custom_css = "
        a,
        #logo a, 
        a:hover,
        .nav li a:hover, 
        .nav li a:focus,
        nav[role='navigation'] > .nav > li.current_page_item > a,
        .blog-list .item .meta-cat a, article.post .meta-cat a,
        .blog-list .item a.excerpt-read-more,
        .ias-trigger a,
        .blue-btn, .comment-reply-link, #submit,
        a:visited,
        h1.archive-title,
        .scrollToTop span,
        #responsive-nav .nav-icon {
          color: {$color_scheme};
        }
        #top-area,
        footer.footer[role='contentinfo'],
        nav[role='navigation'] .nav li ul li a,
        #submit:hover,.blue-btn:active,.blue-btn:focus,.blue-btn:hover,.comment-reply-link:active,.comment-reply-link:focus,.comment-reply-link:hover,
        button:hover,
        html input[type='button']:hover,
        input[type='reset']:hover,
        input[type='submit']:hover{
          background: {$color_scheme};
        }
        .blog-list .item a.excerpt-read-more,
        .ias-trigger a,
        .blue-btn, .comment-reply-link, #submit,
        .scrollToTop{
          border: 1px solid {$color_scheme};
        }
        @media screen and (max-width: 1039px) {
          #main-navigation{
            background: {$color_scheme};
          }
        }
        ";

        wp_enqueue_style( 'bohaute-stylesheet', get_template_directory_uri() . '/library/css/style.min.css', array(), '', 'all' );
  
        wp_enqueue_style( 'bohaute-main-stylesheet', get_stylesheet_uri(), array(), '', 'all' );
       
        wp_add_inline_style( 'bohaute-main-stylesheet', $custom_css );
    
  }

add_action( 'wp_enqueue_scripts', 'bohaute_apply_color', 999 );

/*-----------------------------------------------------------------------------------*/
/* custom functions below */
/*-----------------------------------------------------------------------------------*/
define('bohaute_THEMEURL', get_template_directory_uri());
define('bohaute_IMAGES', bohaute_THEMEURL.'/images'); 
define('bohaute_JS', bohaute_THEMEURL.'/js');
define('bohaute_CSS', bohaute_THEMEURL.'/css');
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

function bohaute_oembed() {

    global $post;

    if ( $post && $post->post_content ) {

        global $shortcode_tags;
        // Make a copy of global shortcode tags - we'll temporarily overwrite it.
        $theme_shortcode_tags = $shortcode_tags;

        // The shortcodes we're interested in.
        $shortcode_tags = array(
            'video' => $theme_shortcode_tags['video'],
            'audio' => $theme_shortcode_tags['audio'],
            'embed' => $theme_shortcode_tags['embed']
        );
        // Get the absurd shortcode regexp.
        $video_regex = '#' . get_shortcode_regex() . '#i';

        // Restore global shortcode tags.
        $shortcode_tags = $theme_shortcode_tags;

        $pattern_array = array( $video_regex );

        // Get the patterns from the embed object.
        if ( ! function_exists( '_wp_oembed_get_object' ) ) {
            include ABSPATH . WPINC . '/class-oembed.php';
        }
        $oembed = _wp_oembed_get_object();
        $pattern_array = array_merge( $pattern_array, array_keys( $oembed->providers ) );

        // Or all the patterns together.
        $pattern = '#(' . array_reduce( $pattern_array, function ( $carry, $item ) {
            if ( strpos( $item, '#' ) === 0 ) {
                // Assuming '#...#i' regexps.
                $item = substr( $item, 1, -2 );
            } else {
                // Assuming glob patterns.
                $item = str_replace( '*', '(.+)', $item );
            }
            return $carry ? $carry . ')|('  . $item : $item;
        } ) . ')#is';

        // Simplistic parse of content line by line.
        $lines = explode( "\n", $post->post_content );

        foreach ( $lines as $line ) {
            $line = trim( $line );

            if ( preg_match( $pattern, $line, $matches ) ) {
                if ( strpos( $matches[0], '[' ) === 0 ) {
                    $ret = do_shortcode( $matches[0] );
                    $audio = explode('"', $matches[0]);
                    if( array_key_exists(1, $audio)){
                      return $audio[1];
                    }
                } else {
                    $ret = wp_oembed_get( $matches[0] );
                }
                return $ret;
            }
        }
    }
}

function bohaute_author_excerpt() {
      $text_limit = '50'; //Words to show in author bio excerpt
      $read_more  = __("Read more",'bohaute'); //Read more text
      $end_of_txt = "...";
      $name_of_author = get_the_author();
      $url_of_author  = get_author_posts_url(get_the_author_meta('ID'));
      $short_desc_author = wp_trim_words(strip_tags(
                          get_the_author_meta('description')), $text_limit, 
                          $end_of_txt.'<br/>
                        <a href="'.$url_of_author.'" style="font-weight:bold;">'.$read_more .'</a>');

      return $short_desc_author;
}

 function bohaute_catch_that_image() {
  global $post;
  $pattern = '|<img.*?class="([^"]+)".*?/>|';
  $transformed_content = apply_filters('the_content',$post->post_content);
  preg_match($pattern,$transformed_content,$matches);
  if (!empty($matches[1])) {
    $classes = explode(' ',$matches[1]);
    $id = preg_grep('|^wp-image-.*|',$classes);
    if (!empty($id)) {
      $id = str_replace('wp-image-','',$id);
      if (!empty($id)) {
        $id = reset($id);
        $transformed_content = wp_get_attachment_image($id,'full');  
        return $transformed_content;
      }
    }
  }
  
}

function bohaute_catch_that_image_thumb() {
  global $post;
  $pattern = '|<img.*?class="([^"]+)".*?/>|';
  $transformed_content = apply_filters('the_content',$post->post_content);
  preg_match($pattern,$transformed_content,$matches);
  if (!empty($matches[1])) {
    $classes = explode(' ',$matches[1]);
    $id = preg_grep('|^wp-image-.*|',$classes);
    if (!empty($id)) {
      $id = str_replace('wp-image-','',$id);
      if (!empty($id)) {
        $id = reset($id);
        $transformed_content = wp_get_attachment_image($id,'thumbnail');  
         return $transformed_content;
      }
    }
  }
 
}

function bohaute_catch_gallery_image_full()  { 
    global $post;
    $gallery = get_post_gallery( $post, false );
    if ( !empty($gallery['ids']) ) {
      $ids = explode( ",", $gallery['ids'] );
      $total_images = 0;
      foreach( $ids as $id ) {
        
        $title = get_post_field('post_title', $id);
        $meta = get_post_field('post_excerpt', $id);
        $link = wp_get_attachment_url( $id );
        $image  = wp_get_attachment_image( $id, 'full');
        $total_images++;
        
        if ($total_images == 1) {
          $first_img = $image;
          return $first_img;
        }
      }
    } 
}

function bohaute_catch_gallery_image_thumb()  { 
    global $post;
    $gallery = get_post_gallery( $post, false );
    if ( !empty($gallery['ids']) ) {
      $ids = explode( ",", $gallery['ids'] );
      $total_images = 0;
      foreach( $ids as $id ) {
        
        $title = get_post_field('post_title', $id);
        $meta = get_post_field('post_excerpt', $id);
        $link = wp_get_attachment_url( $id );
        $image  = wp_get_attachment_image( $id, 'thumbnail');
        $total_images++;
        
        if ($total_images == 1) {
          $first_img = $image;
          return $first_img;
        }
      }
    } 
}

/* social icons*/
function bohaute_social_icons()  { 

  $social_networks = array( array( 'name' => __('Facebook','bohaute'), 'theme_mode' => 'bohaute_facebook','icon' => 'fa-facebook' ),
                            array( 'name' => __('Twitter','bohaute'), 'theme_mode' => 'bohaute_twitter','icon' => 'fa-twitter' ),
                            array( 'name' => __('Google+','bohaute'), 'theme_mode' => 'bohaute_google','icon' => 'fa-google-plus' ),
                            array( 'name' => __('Pinterest','bohaute'), 'theme_mode' => 'bohaute_pinterest','icon' => 'fa-pinterest' ),
                            array( 'name' => __('Linkedin','bohaute'), 'theme_mode' => 'bohaute_linkedin','icon' => 'fa-linkedin' ),
                            array( 'name' => __('Youtube','bohaute'), 'theme_mode' => 'bohaute_youtube','icon' => 'fa-youtube' ),
                            array( 'name' => __('Tumblr','bohaute'), 'theme_mode' => 'bohaute_tumblr','icon' => 'fa-tumblr' ),
                            array( 'name' => __('Instagram','bohaute'), 'theme_mode' => 'bohaute_instagram','icon' => 'fa-instagram' ),
                            array( 'name' => __('Flickr','bohaute'), 'theme_mode' => 'bohaute_flickr','icon' => 'fa-flickr' ),
                            array( 'name' => __('Vimeo','bohaute'), 'theme_mode' => 'bohaute_vimeo','icon' => 'fa-vimeo-square' ),
                            array( 'name' => __('RSS','bohaute'), 'theme_mode' => 'bohaute_rss','icon' => 'fa-rss' )
                      );


  for ($row = 0; $row < 11; $row++)
  {
     
      if (get_theme_mod( $social_networks[$row]["theme_mode"])): ?>
         <a href="<?php echo esc_url( get_theme_mod($social_networks[$row]['theme_mode']) ); ?>" class="social-tw" title="<?php echo esc_url( get_theme_mod( $social_networks[$row]['theme_mode'] ) ); ?>" target="_blank">
          <i class="fa <?php echo $social_networks[$row]['icon']; ?>"></i> 
        </a>
      <?php endif;
  }

  if(get_theme_mod('bohaute_email')): ?>
        <a href="mailto:<?php echo esc_attr(get_theme_mod('bohaute_email')); ?>" class="social-tw" title="<?php echo esc_attr( get_theme_mod('bohaute_email')); ?>" target="_blank">
          <i class="fa fa-envelope"></i> 
        </a>
  <?php endif;                        
}

/**
 * Include the TGM_Plugin_Activation class.
 */
require_once get_template_directory() . '/library/class/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'bohaute_register_required_plugins' );
/**
 * Register the required plugins for this theme.
 *
 * In this example, we register two plugins - one included with the TGMPA library
 * and one from the .org repo.
 *
 * The variable passed to bohaute_register_plugins() should be an array of plugin
 * arrays.
 *
 * This function is hooked into bohaute_init, which is fired within the
 * TGM_Plugin_Activation class constructor.
 */
function bohaute_register_required_plugins() {
 
    /**
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = array(
 
 
        // This is an example of how to include a plugin from the WordPress Plugin Repository.
        array(
            'name'      => __('Recent Posts Widget With Thumbnails','bohaute'),
            'slug'      => 'recent-posts-widget-with-thumbnails',
            'required'  => false,
        ),
 
    );
 
    /**
     * Array of configuration settings. Amend each line as needed.
     * If you want the default strings to be available under your own theme domain,
     * leave the strings uncommented.
     * Some of the strings are added into a sprintf, so see the comments at the
     * end of each line for what each argument will be.
     */
    $config = array(
        'default_path' => '',                      // Default absolute path to pre-packaged plugins.
        'menu'         => 'bohaute-install-plugins', // Menu slug.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.
        'strings'      => array(
            'page_title'                      => __( 'Install Required Plugins', 'bohaute' ),
            'menu_title'                      => __( 'Install Plugins', 'bohaute' ),
            'installing'                      => __( 'Installing Plugin: %s', 'bohaute' ), // %s = plugin name.
            'oops'                            => __( 'Something went wrong with the plugin API.', 'bohaute' ),
            'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.' , 'bohaute'), // %1$s = plugin name(s).
            'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.' , 'bohaute'), // %1$s = plugin name(s).
            'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.' , 'bohaute'), // %1$s = plugin name(s).
            'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.' , 'bohaute'), // %1$s = plugin name(s).
            'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.' , 'bohaute'), // %1$s = plugin name(s).
            'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.' , 'bohaute'), // %1$s = plugin name(s).
            'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.' , 'bohaute'), // %1$s = plugin name(s).
            'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.' , 'bohaute'), // %1$s = plugin name(s).
            'install_link'                    => _n_noop( 'Begin installing plugin', 'Begin installing plugins' , 'bohaute'),
            'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins' , 'bohaute'),
            'return'                          => __( 'Return to Required Plugins Installer', 'bohaute' ),
            'plugin_activated'                => __( 'Plugin activated successfully.', 'bohaute' ),
            'complete'                        => __( 'All plugins installed and activated successfully. %s', 'bohaute' ), // %s = dashboard link.
            'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
        )
    );
 
    tgmpa( $plugins, $config );
 
}
/* DON'T DELETE THIS CLOSING TAG */ ?>