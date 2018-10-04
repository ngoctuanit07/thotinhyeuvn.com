<!doctype html>

<!--[if lt IE 7]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8 lt-ie7"><![endif]-->
<!--[if (IE 7)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9 lt-ie8"><![endif]-->
<!--[if (IE 8)&!(IEMobile)]><html <?php language_attributes(); ?> class="no-js lt-ie9"><![endif]-->
<!--[if gt IE 8]><!--> <html <?php language_attributes(); ?> class="no-js"><!--<![endif]-->

	<head>
		<meta charset="utf-8">

		<?php // force Internet Explorer to use the latest rendering engine available ?>
		<meta http-equiv="X-UA-Compatible" content="IE=edge">

		<?php // mobile meta (hooray!) ?>
		<meta name="HandheldFriendly" content="True">
		<meta name="MobileOptimized" content="320">
		<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

		<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

		<?php // wordpress head functions ?>
		<?php wp_head(); ?>
		<?php // end of wordpress head ?>

	</head>

	<body <?php body_class(); ?>>

		<div id="container">

			<header class="header" role="banner">
				<div id="top-area">
					<div class="wrap">
						<div class="left-area">
							<?php 
					           	if(function_exists('bohaute_social_icons')) :
					           		echo bohaute_social_icons(); 
					           	endif;
					        ?>
						</div>
						<div class="right-area">
							<form role="search" method="get" id="searchform" class="top-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
								<div>
									<input type="text" value="<?php echo get_search_query(); ?>" name="s" id="s" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'bohaute' ); ?>" />
									<button type="submit" id="searchsubmit"><span class="fa fa-search"></span></button>
								</div>
							</form>
						</div>
						<span class="clear"></span>
					</div>
				</div>

				<div id="inner-header" class="wrap cf">
					<?php if ( function_exists( 'the_custom_logo' ) && has_custom_logo() )  : ?>
					<p id="logo" class="h1"><a href="<?php echo esc_url( home_url() ); ?>"><?php the_custom_logo(); ?></a></p>
					<?php elseif ( !function_exists( 'the_custom_logo' ) && get_theme_mod( 'bohaute_logo' ) ) : ?>
					<p id="logo" class="h1"><a href="<?php echo esc_url( home_url() ); ?>"><img src="<?php echo esc_url( get_theme_mod( 'bohaute_logo' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" /></a></p>
					 <?php else : ?>
					<p id="logo" class="h1"><a href="<?php echo esc_url( home_url() ); ?>"><?php bloginfo('name'); ?><span><?php bloginfo('description'); ?></span></a></p>
					<?php endif; ?>
					<div id="responsive-nav"><span class="fa fa-bars nav-icon"></span></div>
       					 <div class="clear no-display"></div>
       					 <nav role="navigation" id="main-navigation">
       					 	<?php if ( has_nav_menu('main-nav') ) { ?>
								<?php wp_nav_menu(array(
		    					'container' => false,                           // remove nav container
		    					'container_class' => 'menu cf',                 // class of container (should you choose to use it)
		    					'menu_class' => 'nav top-nav cf',               // adding custom nav class
		    					'theme_location' => 'main-nav',                 // where it's located in the theme
		    					'before' => '',                                 // before the menu
			        			'after' => '',                                  // after the menu
			        			'link_before' => '',                            // before each link
			        			'link_after' => '',                             // after each link
			        			'depth' => 0,                                   // limit the depth of the nav
		    					'fallback_cb' => ''                             // fallback function (if there is one)
								)); ?>
							<?php } else { ?>
								<ul class="nav top-nav cf">
  								<?php wp_list_pages('sort_column=menu_order&title_li='); ?>
								</ul>
							<?php } ?>
						</nav>

				</div>

			</header>