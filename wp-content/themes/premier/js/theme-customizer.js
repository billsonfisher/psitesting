/**

 * Theme Customizer enhancements for a better user experience.

 *

 * Contains handlers to make Theme Customizer preview reload changes asynchronously.

 */



( function( $ ) {

	wp.customize( 'blogname', function( value ) {

		value.bind( function( to ) {

			$( '.site-title a' ).html( to );

		} );

	} );

	wp.customize( 'blogdescription', function( value ) {

		value.bind( function( to ) {

			$( '.site-description' ).html( to );

		} );

	} );


	wp.customize( 'blogdescription', function( value ) {

		value.bind( function( to ) {

			$( '.site-description' ).html( to );

		} );

	} );


	wp.customize( 'telnumber_textbox_header_one', function( value ) {

		value.bind( function( to ) {

			$( '.contact.telnumber' ).html( to );

		} );

	} );

	wp.customize( 'mobile_textbox_header_one', function( value ) {

		value.bind( function( to ) {

			$( '.contact.mobile' ).html( to );

		} );

	} );

	wp.customize( 'email_textbox_header_one', function( value ) {

		value.bind( function( to ) {

			$( '.contact.email' ).html( to );

		} );

	} );

	wp.customize( 'address_textbox_header_one', function( value ) {

		value.bind( function( to ) {

			$( '.contact.address' ).html( to );

		} );

	} );

	wp.customize( 'copyright_text', function( value ) {

		value.bind( function( to ) {

			$( '.site-info p' ).html( to );

		} );

	} );

	wp.customize( 'featured_textbox', function( value ) {

		value.bind( function( to ) {

			$( '.featuretext_left h2' ).html( to );

		} );

	} );

	wp.customize( 'featured_button_txt', function( value ) {

		value.bind( function( to ) {

			$( '.featuretext_right a' ).html( to );

		} );

	} );

	wp.customize( 'homepage_service_title', function( value ) {

		value.bind( function( to ) {

			$( '.featuretext_middle h3' ).html( to );

		} );

	} );

	wp.customize( 'featured_textbox_header_one', function( value ) {

		value.bind( function( to ) {

			$( '.box-1 .featuretext h4 a' ).html( to );

		} );

	} );

	wp.customize( 'featured_textbox_header_two', function( value ) {

		value.bind( function( to ) {

			$( '.box-2 .featuretext h4 a' ).html( to );

		} );

	} );

	wp.customize( 'featured_textbox_header_three', function( value ) {

		value.bind( function( to ) {

			$( '.box-3 .featuretext h4 a' ).html( to );

		} );

	} );

	wp.customize( 'featured_textbox_text_one', function( value ) {

		value.bind( function( to ) {

			$( '.box-1 .featuretext p' ).html( to );

		} );

	} );

	wp.customize( 'featured_textbox_text_two', function( value ) {

		value.bind( function( to ) {

			$( '.box-2 .featuretext p' ).html( to );

		} );

	} );

	wp.customize( 'featured_textbox_text_three', function( value ) {

		value.bind( function( to ) {

			$( '.box-3 .featuretext p' ).html( to );

		} );

	} );

	wp.customize( 'homepage_recent_title', function( value ) {

		value.bind( function( to ) {

			$( '.section_thumbnails h3' ).html( to );

		} );

	} );

	wp.customize( 'homepage_partners_title', function( value ) {

		value.bind( function( to ) {

			$( '.client h3' ).html( to );

		} );

	} );

	wp.customize( 'premier_custom_main_color', function( value ) {

		value.bind( function( to ) {

			$( 
				'a.more-link,.main-navigation li.current_page_item,#main-navigation li.current-menu-parent,.main-navigation li.current_page_item:hover a,#main-navigation li.current_page_item:hover,.main-navigation li.current-menu-parent:hover > a,.main-navigation li:hover a,.main-navigation > li > a,.main-navigation li.current_page_ancestor a,.main-navigation ul ul li a:hover,#main-navigation ul ul li a:hover i,.main-navigation li.current-menu-parent ul.sub-menu li.current_page_item a,.page-header,.entry-title,.flex-more-link a,#smoothup:hover,.thumbs-more-link a:hover,.unity-separator,.grid-more-link,.tagcloud a,#featuretext_container,.featuretext,.recent_title' 
			).css( 'background-color', to );

			$( 
				'.topbar_content_left .contact i,.testimonial-client-name a,.testimonial-client-name,.testimonial_home .testimonial .quote,.genericon:hover,.social-media li:hover,.main-small-navigation .menu li a,.main-small-navigation li.current_page_item a,.main-small-navigation li.current-menu-item a,.topbar_content_left:before,.socialIcons a,.socialIcons a:visited,.entry-content a,.entry-content a:visited,.entry-summary a,.entry-summary a:visited,.entry-content a:hover,.entry-summary a:hover,.grid-more-link a:hover,.nav-social-btn:hover i,.nav-social-btn:focus i,#main-navigation ul ul li a:hover i, .main-navigation li.current-menu-parent ul.sub-menu li.current_page_item a,.nav-social-btn i,.nav-social-btn:hover i' 
			).css('color', to);

			$( 
				'.main-navigation,.tagcloud a,.social-media li:hover,.social-media li:focus' 
			).css( 'border-color', to);

			$( 
				'.main-small-navigation li:hover > a,.main-small-navigation ul ul a:hover,.main-navigation ul li a:hover,.main-navigation ul li a:focus' 
			).css('color', '#fff');
		} );

	} );



} )( jQuery );