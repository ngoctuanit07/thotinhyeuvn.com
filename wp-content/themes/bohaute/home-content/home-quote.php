<article class="item">
	<?php 
		$thumb_id = get_post_thumbnail_id();
		$thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full', true);
		$thumb_url = $thumb_url_array[0]; 
	?>
	<div id="quote-item" <?php if( has_post_thumbnail() ) { ?> class="has-bg" style="background-image:url('<?php echo esc_url( $thumb_url ); ?>')" <?php } ?> >
		<div class="quote-item-content">
			<span class="fa fa-quote-left"></span>
			<?php the_content(); ?>
			<span class="fa fa-quote-right"></span>
		</div>
		<span class="quote-overlay"></span>
	</div>
</article>