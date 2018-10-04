<article class="item">
	<a href="<?php the_permalink(); ?>">	 
		<?php $image = bohaute_catch_that_image(); if ( has_post_thumbnail()): the_post_thumbnail( 'full'); elseif(!empty($image)): echo wp_kses_post( $image ); endif; ?>
	</a>
	<?php 
		if ( has_post_format( 'audio' ) && bohaute_oembed() ) {
		?>
			<div class="audio-wrapper">
			<audio src="<?php echo esc_url( bohaute_oembed(),'bohaute' ); ?>" preload="auto"></audio>
			</div>
		<?php
		}
	?> 
	<p class="meta-cat">
		<?php
			$category_list = get_the_category_list( __( ', ', 'bohaute' ) );
			printf( __('%s', 'bohaute'),
			$category_list
			);
		?>
	</p>
	<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<div class="excerpt"><?php the_excerpt( '<span class="read-more">' . __( 'Read More &raquo;', 'bohaute' ) . '</span>' ); ?></div>
	
</article>