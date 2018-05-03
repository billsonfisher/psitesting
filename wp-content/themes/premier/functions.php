<?php
/**
 * premier functions and definitions
 *
 * @package premier
 * @since premier 1.0
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * @since premier 1.0
 */
if ( ! isset( $content_width ) )
	$content_width = 654; /* pixels */

if ( ! function_exists( 'premier_setup' ) ):
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which runs
 * before the init hook. The init hook is too late for some features, such as indicating
 * support post thumbnails.
 *
 * @since premier 1.0
 */
function premier_setup() {

	/**
	 * Custom template tags for this theme.
	 */
	require( get_template_directory() . '/inc/template-tags.php' );

	/**
	 * Custom functions that act independently of the theme templates
	 */
	require( get_template_directory() . '/inc/tweaks.php' );

	/**
	 * Make theme available for translation
	 * Translations can be filed in the /languages/ directory
	 * If you're building a theme based on premier, use a find and replace
	 * to change 'premier' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'premier', get_template_directory() . '/languages' );

	/**
	 * Add default posts and comments RSS feed links to head
	 */
	add_theme_support( 'automatic-feed-links' );

	/**
	 * This theme uses wp_nav_menu() in one location.
	 */
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'premier' ),
	) );

	/**
	 * Add support for post thumbnails
	 */
	add_theme_support('post-thumbnails');
	add_image_size( 100, 300, true);
	add_image_size( 'recent', 700, 400, true );

	/**
	 * Add support for the Aside Post Formats
	 */
	add_theme_support( 'post-formats', array( 'aside', ) );

	// Display Title in theme
	add_theme_support( 'title-tag' );

	// Add WooCommerce Support
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// link a custom stylesheet file to the TinyMCE visual editor
    $font_url = str_replace( ',', '%2C', '//fonts.googleapis.com/css?family=Open+Sans' );
	add_editor_style( array('style.css', 'css/editor-style.css', $font_url) );

}
endif; // premier_setup
add_action( 'after_setup_theme', 'premier_setup' );

/**
 * Setup the WordPress core custom background feature.
 *
 * Use add_theme_support to register support for WordPress 3.4+
 * as well as provide backward compatibility for previous versions.
 * Use feature detection of wp_get_theme() which was introduced
 * in WordPress 3.4.
 *
 * Hooks into the after_setup_theme action.
 *
 * @since premier 1.0
 */
function premier_register_custom_background() {

	$args = array(
		'default-color' => 'EEE',
	);

	$args = apply_filters( 'premier_custom_background_args', $args );

	if ( function_exists( 'wp_get_theme' ) ) {
		add_theme_support( 'custom-background', $args );
	} else {
		define( 'BACKGROUND_COLOR', $args['default-color'] );
		define( 'BACKGROUND_IMAGE', $args['default-image'] );
		add_theme_support( 'custom-background', $args );
	}

}
add_action( 'after_setup_theme', 'premier_register_custom_background' );

/**
 * Register widgetized area and update sidebar with default widgets
 *
 * @since premier 1.0
 */
function premier_widgets_init() {
	register_sidebar( array(
		'name' => __( 'Primary Sidebar', 'premier' ),
		'id' => 'sidebar-1',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	) );

	register_sidebar( array(
		'name' => __( 'WooCommerce Sidebar', 'premier' ),
		'id' => 'woocommerce_sidebar',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	) );

	register_sidebar( array(
		'name' => __( 'Secondary Sidebar', 'premier' ),
		'id' => 'sidebar-2',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	) );

	register_sidebar( array(
		'name' => __( 'Left Sidebar', 'premier' ),
		'id' => 'sidebar-3',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget' => '</aside>',
		'before_title' => '<h1 class="widget-title">',
		'after_title' => '</h1>',
	) );

	register_sidebar(array(
			'name' => 'Left Footer Column',
			'id'   => 'left_column',
			'description' => __( 'Widget area for footer left column', 'premier' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4>',
			'after_title'   => '</h4>'
		));
		register_sidebar(array(
			'name' => 'Center  Footer Column',
			'id'   => 'center_column',
			'description' => __( 'Widget area for footer center column', 'premier' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4>',
			'after_title'   => '</h4>'
		));
		register_sidebar(array(
			'name' => 'Right Footer Column',
			'id'   => 'right_column',
			'description' => __( 'Widget area for footer right column', 'premier' ),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4>',
			'after_title'   => '</h4>'
		));

}
add_action( 'widgets_init', 'premier_widgets_init' );

/**
 * Enqueue scripts and styles
 */
function premier_scripts() {

	wp_enqueue_style( 'style', get_stylesheet_uri(), '', '1.4' );

	wp_enqueue_style('font-awesome', get_template_directory_uri() . '/css/font-awesome.min.css', '', '4.3.0');

	wp_enqueue_style('animate', get_template_directory_uri().'/css/animate.min.css', '', '1.4');

    wp_enqueue_style('flexslider', get_template_directory_uri().'/js/flexslider.css', '', '2.0');


	wp_enqueue_script( 'small-menu', get_template_directory_uri() . '/js/small-menu.js', array( 'jquery' ), '1.4', true );

	wp_enqueue_script( 'keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '1.2' );

	wp_enqueue_script( 'smoothup', get_template_directory_uri() . '/js/smoothscroll.js', array( 'jquery' ), '',  true );

	wp_enqueue_script( 'inview', get_template_directory_uri() . '/js/Inview.js', array('jquery'));

	wp_enqueue_script( 'theme-js', get_template_directory_uri() . '/js/animate.js', array('jquery', 'inview'));

	wp_enqueue_script('flexslider', get_template_directory_uri().'/js/jquery.flexslider-min.js', array('jquery'));

    wp_enqueue_script('flexslider-init', get_template_directory_uri().'/js/flexslider-init.js', array('jquery', 'flexslider'));


	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {

		wp_enqueue_script( 'comment-reply' );

	}

	if ( is_singular() && wp_attachment_is_image() ) {

		wp_enqueue_script( 'keyboard-image-navigation', get_template_directory_uri() . '/js/keyboard-image-navigation.js', array( 'jquery' ), '20120202' );

	}

}
add_action( 'wp_enqueue_scripts', 'premier_scripts' );


/**
 * Implement excerpt for homepage slider
 */
function premier_get_slider_excerpt(){
$excerpt = get_the_content();
$excerpt = preg_replace(" (\[.*?\])",'',$excerpt);
$excerpt = strip_shortcodes($excerpt);
$excerpt = strip_tags($excerpt);
$excerpt = substr($excerpt, 0, 120);
$excerpt = trim(preg_replace( '/\s+/', ' ', $excerpt));
return $excerpt;
}


// Theme Options
include('functions/customizer_controller.php');
include('functions/customizer_settings.php');
include('functions/customizer_styles.php');


/**
 * Implement excerpt for homepage thumbnails
 */
function premier_content($limit) {

  $content = explode(' ', get_the_content(), $limit);

  if (count($content)>=$limit) {

    array_pop($content);

    $content = implode(" ",$content).'...';

  } else {

    $content = implode(" ",$content);
  }

  $content = preg_replace('/\[.+\]/','', $content);
  $content = apply_filters('the_content', $content);
  $content = str_replace(']]>', ']]&gt;', $content);

  return $content;

}

/**
 * Custom "more" link format
 */
function premier_new_excerpt_more($more) {
       global $post;
	return '...';
}
add_filter('excerpt_more', 'premier_new_excerpt_more');



/**
 * Implement the Custom Header feature
 */
add_theme_support( 'custom-header' );
function premier_custom_header_setup() {
	$args = array(
		'default-image'          => '',
		'default-text-color'     => '222',
		'width'                  => 1170,
		'height'                 => 400,
		'flex-height'            => true,
		'wp-head-callback'       => 'premier_header_style',
		'admin-head-callback'    => 'premier_admin_header_style',
		'admin-preview-callback' => 'premier_admin_header_image',
	);

	$args = apply_filters( 'premier_custom_header_args', $args );

	if ( function_exists( 'wp_get_theme' ) ) {
		add_theme_support( 'custom-header', $args );
	}
}
add_action( 'after_setup_theme', 'premier_custom_header_setup' );

if ( ! function_exists( 'premier_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog
 *
 * @see premier_custom_header_setup().
 *
 * @since premier 1.0
 */
function premier_header_style() {

	// If no custom options for text are set, let's bail
	// get_header_textcolor() options: HEADER_TEXTCOLOR is default, hide text (returns 'blank') or any hex value
	if ( HEADER_TEXTCOLOR == get_header_textcolor() && '' == get_header_image() )
		return;
	// If we get this far, we have custom styles. Let's do this.
	?>
	<style type="text/css">
	<?php
		// Do we have a custom header image?
		if ( '' != get_header_image() ) :
	?>
	<?php endif;

		// Has the text been hidden?
		if ( 'blank' == get_header_textcolor() ) :
	?>
		.site-title,
		.site-description {
			position: absolute !important;
			clip: rect(1px 1px 1px 1px); /* IE6, IE7 */
			clip: rect(1px, 1px, 1px, 1px);
		}
		.site-header hgroup {
			background: none;
			padding: 0;
		}
	<?php
		// If the user has set a custom color for the text use that
		else :
	?>
		.site-title a,
		.site-description {
			color: #<?php echo get_header_textcolor(); ?> !important;
		}
	<?php endif; ?>
	</style>
	<?php
}
endif; // premier_header_style

if ( ! function_exists( 'premier_admin_header_style' ) ) :
/**
 * Styles the header image displayed on the Appearance > Header admin panel.
 *
 * @see premier_custom_header_setup().
 *
 * @since premier 1.0
 */
function premier_admin_header_style() {
?>
	<style type="text/css">
	.appearance_page_custom-header #headimg {
		background: #FFF;
		border: none;
		min-height: 200px;
	}
	#headimg h1 {
		font-size: 20px;
		font-family: 'open_sansbold', sans-serif;
		font-weight: normal;
		padding: 0.8em 0.5em 0;
	}
	#desc {
		padding: 0 2em 2em;
	}
	#headimg h1 a,
	#desc {
		color: #222;
		text-decoration: none;
	}
	#headimg img {
	}
	</style>
<?php
}
endif; // premier_admin_header_style

if ( ! function_exists( 'premier_admin_header_image' ) ) :
/**
 * Custom header image markup displayed on the Appearance > Header admin panel.
 *
 * @see premier_custom_header_setup().
 *
 * @since premier 1.0
 */
function premier_admin_header_image() { ?>
	<div id="headimg">
		<?php
		if ( 'blank' == get_header_textcolor() || '' == get_header_textcolor() )
			$style = ' style="display:none;"';
		else
			$style = ' style="color:#' . get_header_textcolor() . ';"';
		?>
		<h1><a id="name"<?php echo $style; ?> onclick="return false;" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
		<div id="desc"<?php echo $style; ?>><?php bloginfo( 'description' ); ?></div>
		<?php $header_image = get_header_image();
		if ( ! empty( $header_image ) ) : ?>
			<img src="<?php echo esc_url( $header_image ); ?>" alt="" />
		<?php endif; ?>
	</div>
<?php }
endif; // premier_admin_header_image

/**
 * Include Google Fonts
 */
function premier_google_fonts() {
	// Add Google Font stylesheets
	$array_font_sections = array(
		'font_site_title',
		'font_body',
		'font_headers',
	);
	$fonts = '';
  $enqueueFonts = 0;

	foreach($array_font_sections as $id => $section)
	{
		if(get_theme_mod($array_font_sections[$id].'_display'))
		{
			$family = get_theme_mod($array_font_sections[$id]);

			$fonts = $family;

			$fonts = ltrim($fonts, '|');
      $url = add_query_arg('family', urlencode($fonts), "//fonts.googleapis.com/css" );

			wp_enqueue_style('premier-google-fonts-'.urlencode($fonts), $url);

    	$enqueueFonts++;
		}
	}
    if($enqueueFonts == 0){
        return;
    }

}
add_action('wp_enqueue_scripts', 'premier_google_fonts');

/*  Query WooCommerce activation */
if ( ! function_exists( 'is_woocommerce_activated' ) ) {
	function is_woocommerce_activated() {
		return class_exists( 'woocommerce' ) ? true : false;
	}
}
/*  Get woocommerce custom theme code */
if ( is_woocommerce_activated() ) {
	require get_template_directory() . '/inc/woocommerce/hooks.php';

	function premier_header_add_to_cart_fragment( $fragments ) {
    global $woocommerce;
    ob_start();
    ?>
    <a class="cart-contents" href="<?php echo $woocommerce->cart->get_cart_url(); ?>" title="<?php _e('View your shopping cart', 'premier'); ?>">
      <?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'premier'), $woocommerce->cart->cart_contents_count);?> - <?php echo $woocommerce->cart->get_cart_total(); ?>
      <i class="fa fa-shopping-cart"></i>
    </a>
    <?php

    $fragments['a.cart-contents'] = ob_get_clean();

    return $fragments;

  }
  add_filter('add_to_cart_fragments', 'premier_header_add_to_cart_fragment');

}

/**
 * Change the Shop archive page title.
 * @param  string $title
 * @return string
 */
function premier_custom_shop_archive_title( $title ) {
    if ( is_woocommerce_activated() && is_shop() ) {
        return str_replace( __( 'Products', 'premier' ), 'Shop', $title );
    }

    return $title;
}
add_filter( 'wp_title', 'premier_custom_shop_archive_title' );