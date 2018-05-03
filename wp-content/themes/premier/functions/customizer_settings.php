<?php
/**
 * Customizer - Add Custom Styling
 */
function premier_customizer_style()
{
	wp_enqueue_style('premier-customizer', get_template_directory_uri() . '/functions/css/customizer.css');
}
add_action('customize_controls_print_styles', 'premier_customizer_style');

/**
 * Customizer - Live Preview
 */
function premier_customizer_live_preview() {
	wp_enqueue_script(
		'premier-theme-customizer',
		get_template_directory_uri() . '/js/theme-customizer.js',
		array( 'customize-preview' ),
		rand(),
		true
	);
}
add_action( 'customize_preview_init', 'premier_customizer_live_preview' );

/**
 * Customizer - Panels, Sections, Settings & Controls
 */
function premier_register_theme_customizer( $wp_customize ) {

	//  List Arrays
	$list_social_channels = array( // 1
		'twitter'			=> __( 'Twitter url', 'premier' ),
		'facebook'			=> __( 'Facebook url', 'premier' ),
		'googleplus'		=> __( 'Google + url', 'premier' ),
		'linkedin'			=> __( 'LinkedIn url', 'premier' ),
		'flickr'			=> __( 'Flickr url', 'premier' ),
		'pinterest'			=> __( 'Pinterest url', 'premier' ),
		'youtube'			=> __( 'YouTube url', 'premier' ),
		'vimeo'				=> __( 'Vimeo url', 'premier' ),
		'tumblr'			=> __( 'Tumblr url', 'premier' ),
		'dribble'			=> __( 'Dribbble url', 'premier' ),
		'github'			=> __( 'Github url', 'premier' ),
		'instagram'			=> __( 'Instagram url', 'premier' ),
		'xing'				=> __( 'Xing url', 'premier'),
	);

	$list_contact_options = array(
		'telnumber'			=> __( 'Telephone Number', 'premier'),
		'mobile'			=> __( 'Mobile Number', 'premier'),
		'email'				=> __( 'Email Address', 'premier'),
		'address'			=> __( 'Address', 'premier'),
	);

	$list_featured_text_boxes = array(
		array('one', __('One', 'premier'), __('Service Box One', 'premier')),
		array('two', __('Two', 'premier'), __('Service Box Two', 'premier')),
		array('three', __('Three', 'premier'), __('Service Box Three', 'premier')),
	);

	$list_partners_text_boxes = array(
		array('one', __('One', 'premier'), __('Partners logo 1', 'premier')),
		array('two', __('Two', 'premier'), __('Partners logo 2', 'premier')),
		array('three', __('Three', 'premier'), __('Partners logo 3', 'premier')),
		array('four', __('Four', 'premier'), __('Partners logo 4', 'premier')),
	);


	// ======== Google Font Setup

	$list_fonts        		= array(); // 1
	$list_font_weights 		= array(); // 2
	$webfonts_array    		= file(get_template_directory() . '/inc/fonts.json');
	$webfonts          		= implode( '', $webfonts_array );
	$list_fonts_decode 		= json_decode( $webfonts, true );

	// echo '<pre>';
	// var_dump($list_fonts_decode);
	// echo '</pre>';

	$defaultFont			= 'Open Sans';
	foreach ( $list_fonts_decode['items'] as $key => $value ) {

		$item_family                     = $list_fonts_decode['items'][$key]['family'];
		$list_fonts[$item_family]        = $item_family;
		$list_font_weights[$item_family] = $list_fonts_decode['items'][$key]['variants'];
	}

	$list_all_font_weights = array( // 3
		'100'       => __( 'Thin', 'premier' ),
		'300'       => __( 'Light', 'premier' ),
		'400'  			=> __( 'Regular', 'premier' ),
		'600'       => __( 'Semi-Bold', 'premier' ),
		'700'       => __( 'Bold', 'premier' ),
		'800'       => __( 'Extra Bold', 'premier' ),
	);
	$list_font_sizes = array( // 3
		'12'    => __( 'Small', 'premier' ),
		'16' 		=> __( 'Medium', 'premier' ),
		'28'    => __( 'Large', 'premier' ),
		'48' 		=> __( 'Extra Large', 'premier' ),
		'70'		=> __('XX Large', 'premier'),
    '120'   => __('Massive', 'premier'),
	);
	$array_font_sections = array(
		array('font_site_title', __('Site Title', 'premier'), 'Open Sans', '400', '48', false),
		array('font_body', __('Body Text', 'premier'), 'Open Sans', '400', '16', false),
		array('font_headers', __('Headers', 'premier'), 'Open Sans', '400', '12', false),
	);

	$priority = 0;



	/*
	* //////////////////// Panels ////////////////////////////
	*/

	$priority = 10;

	if(method_exists('WP_Customize_Manager', 'add_panel')){

		$wp_customize->add_panel('premier_header_panel', array(
			'title'		=> __('Site Settings', 'premier'),
			'priority'	=> $priority++,
		));

		$wp_customize->add_panel('premier_homepage_panel', array(
			'title'		=> __('Homepage Settings', 'premier'),
			'priority'	=> $priority++,
		));

		$wp_customize->add_panel('premier_fonts_panel', array(
			'title'		=> __('Typography', 'premier'),
			'priority'	=> $priority++,
		));

	}


	/*
	* //////////////////// Sections ////////////////////////////
	*/

	$priority = 2;

	$wp_customize->add_section( 'premier_logo_section' , array(
		'title'       		=> __( 'Site Logo', 'premier' ),
		'description' 		=> __( 'Upload a logo to replace the default site name and description in the header', 'premier' ),
		'panel'				=> 'premier_header_panel',
		'priority'			=> $priority++,
	) );

	$wp_customize->add_section( 'premier_favicons' , array(
		'title'     		=> __('Favicon & App Icons', 'premier'),
		'panel'		 		=> 'premier_header_panel',
		'priority'			=> $priority++,
	) );

	$wp_customize->add_section( 'telnumber_section_one', array(
		'title'       		=> __( 'Header Contact Details', 'premier' ),
		'description' 		=> __( 'Add contact details that will appear in the header of your site.', 'premier' ),
		'panel'				=> 'premier_header_panel',
		'priority'			=> $priority++,
    ));

	$wp_customize->add_section( 'premier_social_section', array(
		'title'				=> __('Social Media Accounts', 'premier'),
		'description' 		=> __( 'Setup your social media accounts here.', 'premier' ),
		'panel'				=> 'premier_header_panel',
		'priority'			=> $priority++,
	));

	$wp_customize->add_section( 'footer_settings', array(
	    'title'       		=> __( 'Footer Settings', 'premier' ),
	    'description' 		=> __( "Change/hide content in the footer.", 'premier' ),
	    'panel'				=> 'premier_header_panel',
	    'priority'			=> $priority++,
    ));

	$wp_customize->add_section( 'premier_color_scheme_settings', array(
		'title'       		=> __( 'Color Scheme', 'premier' ),
		'description' 		=> __( 'Click on a color for a preview of a color scheme', 'premier' ),
		'panel'				=> 'premier_header_panel',
		'priority'			=> $priority++,
	) );

	$wp_customize->add_section( 'homepage_slider', array(
	    'title'       		=> __( 'Homepage Slider', 'premier' ),
	    'description' 		=> __( 'Control the homepage slider', 'premier' ),
	    'panel'				=> 'premier_homepage_panel',
	    'priority'			=> $priority++,
    ));

	$wp_customize->add_section( 'featured_section_top', array(
	    'title'       		=> __( 'Featured Promo Bar', 'premier' ),
	    'description' 		=> __( 'Create a eye catching "call to action"', 'premier' ),
	    'panel'				=> 'premier_homepage_panel',
	    'priority'			=> $priority++,
    ));

	$wp_customize->add_section( 'service_section_settings', array(
	    'title'       		=> __( 'Service Section Settings', 'premier' ),
	    'description' 		=> __( 'Change the title and control the display.', 'premier' ),
	    'panel'				=> 'premier_homepage_panel',
	    'priority'			=> $priority++,
    ));

	$arraycount = count($list_featured_text_boxes);
	for ($row = 0; $row <  $arraycount; $row++) {
		$wp_customize->add_section( 'featured_section_' . $list_featured_text_boxes[$row][0], array(
			    'title' 			=> $list_featured_text_boxes[$row][2],
			    'description' 		=> __( 'This is a settings section to change the custom homepage services box.', 'premier' ),
			    'panel'				=> 'premier_homepage_panel',
			    'priority' 			=> $priority++,
	        )
	    );
	}

	$wp_customize->add_section( 'recent_posts_section_settings', array(
	    'title'       		=> __( 'Recent Posts Section Settings', 'premier' ),
	    'description' 		=> __( 'Change the title and control the display.', 'premier' ),
	    'panel'				=> 'premier_homepage_panel',
	    'priority'			=> $priority++,
    ));

    $wp_customize->add_section( 'partners_section_settings', array(
	    'title'       		=> __( 'Partners Section Settings', 'premier' ),
	    'description' 		=> __( 'Change the title and control the display.', 'premier' ),
	    'panel'				=> 'premier_homepage_panel',
	    'priority'			=> $priority++,
    ));

	$arraycount = count($list_partners_text_boxes);
	for ($row = 0; $row <  $arraycount; $row++) {
	 	$wp_customize->add_section( 'logo_section_' . $list_partners_text_boxes[$row][0], array(
		    'title' 			=> $list_partners_text_boxes[$row][2],
		    'description' 		=> __( 'This is a settings section to change the Custom Homepage Partners logo 1.', 'premier' ),
		    'panel'				=> 'premier_homepage_panel',
		    'priority'		 	=> $priority++,
	        )
	    );
	}

	$arraycount = count($array_font_sections);
		for ($row = 0; $row <  $arraycount; $row++) {
			$wp_customize->add_section($array_font_sections[$row][0], array(
				'title'		=> $array_font_sections[$row][1],
				'description' => __('Select the font that suits your style', 'premier'),
				'panel'		=> 'premier_fonts_panel',
				'priority'	=> $priority++,
			));
		}


	/*
	* //////////////////// Settings ////////////////////////////
	*/

	$wp_customize->add_setting('premier_global_favicon', array(
		'sanitize_callback'	=> 'premier_sanitize_url',
	));

	$wp_customize->add_setting('premier_global_apple_icon', array(
		'sanitize_callback'	=> 'premier_sanitize_url',
	));

	foreach ($list_contact_options as $key => $value){

		$wp_customize->add_setting( $key . '_textbox_header_one', array(
				'sanitize_callback' => 'premier_sanitize_text',
				'transport'			=> 'postMessage',
			)
		);

	}

	foreach ($list_social_channels as $key => $value) {

		$wp_customize->add_setting( $key, array(
			'sanitize_callback' => 'premier_sanitize_url',
		));

	}

	$wp_customize->add_setting( 'premier_logo', array(
			'sanitize_callback' => 'premier_sanitize_url',
		)
	);

	$wp_customize->add_setting('premier_retina_logo', array(
			'sanitize_callback' => 'premier_sanitize_url',
	));

	$wp_customize->add_setting( 'premier_color_scheme', array(
			'default'        	=> 'red',
			'sanitize_callback' => 'premier_sanitize_text',
		)
	);

	$wp_customize->add_setting( 'homepage_slider_show', array(
			'default' 			=> false,
			'sanitize_callback' => 'premier_sanitize_checkbox',
		)
	);

	$wp_customize->add_setting( 'homepage_slider_cat', array(
			'default'			=> 'featured',
			'sanitize_callback'	=> 'premier_sanitize_text',
		)
	);

	$wp_customize->add_setting( 'homepage_slider_slide_no', array(
	        'default'     		=> '3',
			'sanitize_callback'	=> 'premier_sanitize_integer',
    	)
	);

	$wp_customize->add_setting( 'homepage_promotional_bool', array(
			'default' 			=> false,
			'sanitize_callback' => 'premier_sanitize_checkbox',
		)
	);

	$wp_customize->add_setting( 'featured_textbox', array(
			'default' 			=> __( 'Default Featured Text', 'premier' ),
			'transport'			=> 'postMessage',
			'sanitize_callback' => 'premier_sanitize_text',
		)
	);

	$wp_customize->add_setting( 'featured_button_txt', array(
			'sanitize_callback' => 'premier_sanitize_text',
			'transport'			=> 'postMessage',
			'default'			=> 'Find Out More',
		)
	);

	$wp_customize->add_setting( 'featured_button_url', array(
			'sanitize_callback' => 'premier_sanitize_url',
		)
	);

	$wp_customize->add_setting('homepage_service_bool', array(
			'default' 			=> false,
			'sanitize_callback'	=> 'premier_sanitize_checkbox',
	));

	$wp_customize->add_setting( 'homepage_service_title', array(
			'default'			=> __( 'Services', 'premier' ),
			'transport'			=> 'postMessage',
			'sanitize_callback' => 'premier_sanitize_text',
		)
	);

	$arraycount = count($list_featured_text_boxes);
	for ($row = 0; $row <  $arraycount; $row++) {

		$wp_customize->add_setting( 'header-' . $list_featured_text_boxes[$row][0] . '-file-upload', array(
				'sanitize_callback' => 'premier_sanitize_url',
			)
		);

		$wp_customize->add_setting( 'featured_textbox_header_' . $list_featured_text_boxes[$row][0], array(
	        	'default' 			=> __( 'Default featured text Header', 'premier' ),
	        	'transport'			=> 'postMessage',
				'sanitize_callback' => 'premier_sanitize_text',
	    	)
		);

		$wp_customize->add_setting( 'header_' . $list_featured_text_boxes[$row][0] . '_url', array(
	        	'default' 			=> __( 'Header Link', 'premier' ),
				'sanitize_callback' => 'premier_sanitize_url',
	    	)
		);

		$wp_customize->add_setting( 'featured_textbox_text_' . $list_featured_text_boxes[$row][0], array(
				'default' 			=> __( 'Default featured text', 'premier' ),
				'transport'			=> 'postMessage',
				'sanitize_callback' => 'premier_sanitize_text',
			)
		);

	}

	$wp_customize->add_setting('homepage_recent_bool', array(
			'default' 			=> false,
			'sanitize_callback'	=> 'premier_sanitize_checkbox',
	));

	$wp_customize->add_setting( 'homepage_recent_title', array(
			'default'			=> __( 'Recent Posts', 'premier' ),
			'transport'			=> 'postMessage',
			'sanitize_callback' => 'premier_sanitize_text',
		)
	);

	$wp_customize->add_setting('homepage_partners_bool', array(
			'default' 			=> false,
			'sanitize_callback'	=> 'premier_sanitize_checkbox',
	));

	$wp_customize->add_setting( 'homepage_partners_title', array(
			'default'			=> __( 'Partners', 'premier' ),
			'transport'			=> 'postMessage',
			'sanitize_callback' => 'premier_sanitize_text',
		)
	);

	$arraycount = count($list_partners_text_boxes);
	for ($row = 0; $row <  $arraycount; $row++) {

		$wp_customize->add_setting( 'logo-' . $list_partners_text_boxes[$row][0] . '-file-upload', array(
			'sanitize_callback' => 'premier_sanitize_url',
		));

		$wp_customize->add_setting( 'logo_' . $list_partners_text_boxes[$row][0] . '_url', array(
	        'default' 			=> __( 'Link', 'premier' ),
			'sanitize_callback' => 'premier_sanitize_url',
	    ));

	}

	$wp_customize->add_setting( 'copyright_text', array(
			'default'			=> __( 'Copyright Text', 'premier' ),
			'transport'			=> 'postMessage',
			'sanitize_callback' => 'premier_sanitize_text',
		)
	);

	$wp_customize->add_setting('hide_copyright', array(
			'default' 			=> false,
			'sanitize_callback'	=> 'premier_sanitize_checkbox',
	));

	$wp_customize->add_setting('hide_footer_widgets', array(
			'default' 			=> false,
			'sanitize_callback'	=> 'premier_sanitize_checkbox',
	));

	$wp_customize->add_setting( 'authors_ids', array(
			'sanitize_callback' => 'premier_sanitize_text',
		)
	);

	$wp_customize->add_setting('authors_hide_content', array(
			'default' 			=> false,
			'sanitize_callback'	=> 'premier_sanitize_checkbox',
	));

	$wp_customize->add_setting('authors_content_below', array(
			'default' 			=> false,
			'sanitize_callback'	=> 'premier_sanitize_checkbox',
	));

	// Font Settings
	$arraycount = count($array_font_sections);
	for ($row = 0; $row <  $arraycount; $row++) {
		$wp_customize->add_setting(
			$array_font_sections[$row][0],
			array(
				'default'     => $array_font_sections[$row][2],
				'sanitize_callback'	=> 'premier_sanitize_text',
			)
		);
		$wp_customize->add_setting(
			$array_font_sections[$row][0].'_display',
			array(
				'default'     => $array_font_sections[$row][5],
				'sanitize_callback'	=> 'premier_sanitize_checkbox',
			)
		);
	}

	/*
	* //////////////////// Controls ////////////////////////////
	*/

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'premier_global_favicon',
			array(
				'label'      	=> __('Upload Favicon', 'premier'),
				'section'    	=> 'premier_favicons',
				'settings'   	=> 'premier_global_favicon',
				'priority'	 	=> $priority++,
			)
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Image_Control(
			$wp_customize,
			'premier_global_apple_icon',
			array(
				'label'      	=> __('Upload Apple App Icon', 'premier'),
				'section'    	=> 'premier_favicons',
				'settings'   	=> 'premier_global_apple_icon',
				'priority'	 	=> $priority++,
			)
		)
	);

	foreach ($list_contact_options as $key => $value){

		$wp_customize->add_control( $key . '_textbox_header_one', array(
				'label'			=> $value,
				'section' 		=> 'telnumber_section_one',
			)
		);

	}

	foreach ($list_social_channels as $key => $value) {

		$wp_customize->add_control( $key, array(
			'label'   => $value,
			'section' => 'premier_social_section',
			'type'    => 'url',
		) );

	}

	$wp_customize->add_control( new WP_Customize_Image_Control(
		$wp_customize, 'premier_logo', array(
				'label'    => __( 'Logo', 'premier' ),
				'section'  => 'premier_logo_section',
				'settings' => 'premier_logo',
			)
		)
	);

	$wp_customize->add_control( new WP_Customize_Image_Control(
		$wp_customize, 'premier_retina_logo', array(
				'label'    => __( 'Retina Logo', 'premier' ),
				'section'  => 'premier_logo_section',
				'settings' => 'premier_logo',
			)
		)
	);

	$wp_customize->add_control( 'premier_color_scheme', array(
		'label'   		=> __( 'Choose a color scheme', 'premier' ),
		'section' 		=> 'premier_color_scheme_settings',
		'type'       	=> 'radio',
		'choices'    	=> array(
			__( 'Black', 'premier' ) 		=> 'black',
			__( 'Red', 'premier' ) 		=> 'red',
			__( 'Blue', 'premier' ) 		=> 'blue',
			__( 'Green', 'premier' ) 		=> 'green',
			__( 'Orange', 'premier' ) 		=> 'orange',
			__( 'Pink', 'premier' ) 		=> 'pink',
			__( 'Turquoise', 'premier' ) 	=> 'turquoise',
			__( 'Purple', 'premier' ) 		=> 'purple',
		),
	));


	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'homepage_slider_hide_control',
			array(
				'label'      => __('Show Homepage Slider', 'premier'),
				'section'    => 'homepage_slider',
				'settings'   => 'homepage_slider_show',
				'type'		 => 'checkbox',
				'priority'	 => $priority++,
			)
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Category_Control(
			$wp_customize,
			'homepage_slider_cat_control',
			array(
				'label'    		=> __('Select Featured Category', 'premier'),
				'section'  		=> 'homepage_slider',
				'settings'		=> 'homepage_slider_cat',
				'priority'	 	=> $priority++,
			)
		)
	);

	$wp_customize->add_control(
		new Customize_Number_Control(
			$wp_customize,
			'homepage_slider_slide_no_control',
			array(
				'label'      => __('Amount of slides to display', 'premier'),
				'section'    => 'homepage_slider',
				'settings'   => 'homepage_slider_slide_no',
				'type'		 => 'number',
				'priority'	 => $priority++,
			)
		)
	);



	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'featured_hide_bar',
			array(
				'label'      	=> __('Hide Promotional Bar', 'premier'),
				'section'    	=> 'featured_section_top',
				'settings'   	=> 'homepage_promotional_bool',
				'type'		 	=> 'checkbox',
				'priority'	 	=> $priority++,
			)
		)
	);

	$wp_customize->add_control( 'featured_textbox', array(
		    'label'    		=> __( 'Featured Text Header', 'premier' ),
		    'section' 		=> 'featured_section_top',
		    'settings'  	=> 'featured_textbox',
		    'priority'	 	=> $priority++,
	    )
	);

	$wp_customize->add_control( 'featured_button_txt_control', array(
		    'label'    	=> __( 'Change Button Text', 'premier' ),
		    'section' 	=> 'featured_section_top',
		    'settings'	=> 'featured_button_txt',
		    'priority'	 	=> $priority++,
	    )
	);

	$wp_customize->add_control( 'featured_button_url', array(
			'label'    	=> __( 'Featured Button url', 'premier' ),
			'section' 	=> 'featured_section_top',
			'priority'	 	=> $priority++,
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'display_services_section_control',
			array(
				'label'      	=> __('Hide Services Section', 'premier'),
				'section'    	=> 'service_section_settings',
				'settings'   	=> 'homepage_service_bool',
				'type'		 	=> 'checkbox',
				'priority'	 	=> $priority++,
			)
		)
	);

	$wp_customize->add_control( 'service_section_title_control', array(
        'label'   		=> __('Change Title', 'premier'),
        'section' 		=> 'service_section_settings',
        'settings'   	=> 'homepage_service_title',
        'type'   		=> 'text',
        'priority'	 	=> $priority++,
    ) );

	$arraycount = count($list_featured_text_boxes);
	for ($row = 0; $row <  $arraycount; $row++) {

		$wp_customize->add_control(
			new WP_Customize_Upload_Control(
		        $wp_customize,
		        'header-' . $list_featured_text_boxes[$row][0] . '-file-upload',
		        array(
		            'label' 	=> __( 'Header Image File Upload', 'premier' ),
		            'section' 	=> 'featured_section_' . $list_featured_text_boxes[$row][0],
		            'settings' 	=> 'header-' . $list_featured_text_boxes[$row][0] . '-file-upload'
		        )
		    )
		);

		$wp_customize->add_control( 'featured_textbox_header_' . $list_featured_text_boxes[$row][0], array(
				'label' 	=> __( 'Featured Header Text', 'premier' ),
				'section' 	=> 'featured_section_' . $list_featured_text_boxes[$row][0],
				'settings'	=> 'featured_textbox_header_' . $list_featured_text_boxes[$row][0],
			)
		);

		$wp_customize->add_control( 'header_' . $list_featured_text_boxes[$row][0] . '_url', array(
				'label'    	=> __( 'Header url', 'premier' ),
				'section' 	=> 'featured_section_' . $list_featured_text_boxes[$row][0],
				'settings'	=> 'header_' . $list_featured_text_boxes[$row][0] . '_url',
			)
		);

		$wp_customize->add_control( 'featured_textbox_text_' . $list_featured_text_boxes[$row][0], array(
				'label' 	=> __( 'Featured text', 'premier' ),
				'section' 	=> 'featured_section_' . $list_featured_text_boxes[$row][0],
				'settings' 	=> 'featured_textbox_text_' . $list_featured_text_boxes[$row][0]
			)
		);

	}

	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'display_recent_section_control',
			array(
				'label'      	=> __('Hide Recent Posts Section', 'premier'),
				'section'    	=> 'recent_posts_section_settings',
				'settings'   	=> 'homepage_recent_bool',
				'type'		 	=> 'checkbox',
				'priority'	 	=> $priority++,
			)
		)
	);

	$wp_customize->add_control( 'recent_section_title_control', array(
        'label'   		=> __('Change Title', 'premier'),
        'section' 		=> 'recent_posts_section_settings',
        'settings'   	=> 'homepage_recent_title',
        'type'   		=> 'text',
        'priority'	 	=> $priority++,
    ) );

    $wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'display_partners_section_control',
			array(
				'label'      	=> __('Hide Partners Section', 'premier'),
				'section'    	=> 'partners_section_settings',
				'settings'   	=> 'homepage_partners_bool',
				'type'		 	=> 'checkbox',
				'priority'	 	=> $priority++,
			)
		)
	);

	$wp_customize->add_control( 'partners_section_title_control', array(
        'label'   		=> __('Change Title', 'premier'),
        'section' 		=> 'partners_section_settings',
        'settings'   	=> 'homepage_partners_title',
        'type'   		=> 'text',
        'priority'	 	=> $priority++,
    ) );


    $arraycount = count($list_partners_text_boxes);
	for ($row = 0; $row <  $arraycount; $row++) {

		$wp_customize->add_control(
		    new WP_Customize_Upload_Control(
		        $wp_customize,
		        'logo-' . $list_partners_text_boxes[$row][0] . '-file-upload',
		        array(
		            'label' 		=> __( 'Client logo File Upload', 'premier' ),
		            'section' 		=> 'logo_section_' . $list_partners_text_boxes[$row][0],
		            'settings' 		=> 'logo-' . $list_partners_text_boxes[$row][0] . '-file-upload',
		            'priority'	 	=> $priority++,
		        )
		    )
		);

		$wp_customize->add_control( 'logo_' . $list_partners_text_boxes[$row][0] . '_url', array(
				'label'    		=> __( 'Client logo url', 'premier' ),
				'section' 		=> 'logo_section_' . $list_partners_text_boxes[$row][0],
				'priority'	 	=> $priority++,
		));

	}

	$wp_customize->add_control( 'copyright_text_control', array(
        'label'   		=> __( "Change Copyright Text", 'premier'),
        'section' 		=> 'footer_settings',
        'settings'   	=> 'copyright_text',
        'type'   		=> 'text',
        'priority'	 	=> $priority++,
    ) );

    $wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'hide_copyright_control',
			array(
				'label'      	=> __('Hide Copyright Bar', 'premier'),
				'section'    	=> 'footer_settings',
				'settings'   	=> 'hide_copyright',
				'type'		 	=> 'checkbox',
				'priority'	 	=> $priority++,
			)
		)
	);

    $wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'hide_footer_widgets_control',
			array(
				'label'      	=> __('Hide Footer Widgets', 'premier'),
				'section'    	=> 'footer_settings',
				'settings'   	=> 'hide_footer_widgets',
				'type'		 	=> 'checkbox',
				'priority'	 	=> $priority++,
			)
		)
	);

	$wp_customize->add_control( 'authors_ids_control', array(
        'label'   		=> __( "Add User Id's", 'premier'),
        'section' 		=> 'authors_template_settings',
        'settings'   	=> 'authors_ids',
        'type'   		=> 'text',
        'priority'	 	=> $priority++,
    ) );

    $wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'authors_hide_content_control',
			array(
				'label'      	=> __('Hide Page Content', 'premier'),
				'section'    	=> 'authors_template_settings',
				'settings'   	=> 'authors_hide_content',
				'type'		 	=> 'checkbox',
				'priority'	 	=> $priority++,
			)
		)
	);

	$wp_customize->add_control(
		new WP_Customize_Control(
			$wp_customize,
			'authors_content_below_control',
			array(
				'label'      	=> __('Display Content below User Profiles', 'premier'),
				'section'    	=> 'authors_template_settings',
				'settings'   	=> 'authors_content_below',
				'type'		 	=> 'checkbox',
				'priority'	 	=> $priority++,
			)
		)
	);

	// Font Controls
	$arraycount = count($array_font_sections);
	for ($row = 0; $row <  $arraycount; $row++) {
		$wp_customize->add_control(
			new WP_Customize_Control(
				$wp_customize,
				$array_font_sections[$row][0].'_display',
				array(
					'label'      	=> __('Enable these settings', 'premier'),
					'section'    	=> $array_font_sections[$row][0],
					'settings'   	=> $array_font_sections[$row][0].'_display',
					'type'		 	=> 'checkbox',
					'priority'		=> $priority++,
				)
			)
		);
		$wp_customize->add_control( $array_font_sections[$row][0], array(
			'type'     => 'select',
			'label'    => __( 'Font Family', 'premier' ),
			'section'  => $array_font_sections[$row][0],
			'priority' =>$priority++,
			'choices'  => $list_fonts
		));
	}

	/*
	* //////////////////// Overrides ////////////////////////////
	*/

	$wp_customize->get_section( 'title_tagline' )->panel 			= 'premier_header_panel';
	$wp_customize->get_section( 'title_tagline' )->priority 		= 1;
	$wp_customize->get_section( 'header_image' )->panel 			= 'premier_header_panel';
	$wp_customize->get_section( 'header_image' )->priority 			= 4;
	$wp_customize->get_section( 'colors' )->panel 					= 'premier_header_panel';
	$wp_customize->get_section( 'colors' )->priority 				= 99;
	$wp_customize->get_section('background_image')->panel 			= 'premier_header_panel';
	$wp_customize->get_section( 'background_image' )->priority 		= 4;
	$wp_customize->remove_control( 'font_site_title_size' );

	// Show instant changes for site title and description in the Theme Customizer.
	$wp_customize->get_setting( 'blogname' )->transport        		= 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport 		= 'postMessage';

}
add_action( 'customize_register', 'premier_register_theme_customizer' );


/**
 *  ////////////// SANITIZATION //////////////////////
 */

// Sanitize Text
function premier_sanitize_text( $input ) {
    return wp_kses_post( force_balance_tags( $input ) );
}
// Sanitize Textarea
function premier_sanitize_textarea($input) {
	global $allowedposttags;
	$output = wp_kses( $input, $allowedposttags);
	return $output;
}
// Sanitize Checkbox
function premier_sanitize_checkbox( $input ) {
	if( $input ):
		$output = '1';
	else:
		$output = false;
	endif;
	return $output;
}
// Sanitize Numbers
function premier_sanitize_integer( $input ) {
	$value = (int) $input; // Force the value into integer type.
    return ( 0 < $input ) ? $input : null;
}
function premier_sanitize_float( $input ) {
	return floatval( $input );
}

// Sanitize Uploads
function premier_sanitize_url($input){
	return esc_url_raw($input);
}

// Sanitize Colors
function premier_sanitize_color($input){
	return maybe_hash_hex_color($input);
}
