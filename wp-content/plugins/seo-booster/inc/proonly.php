<?php
// don't load directly
if ( ! defined( 'ABSPATH' ) ) exit;
global $current_user,$seobooster_fs;
?>
<section class="marketingbox" id="sbpmarketingbox">
	<h3 class="headline"><?php _e('SEO Booster PRO - 30 Day Trial!', 'seo-booster'); ?> <span>&#9829;</span></h3>
	<?php
	//echo "<pre>".print_r(seobooster_fs()->get_trial_plans(),true)."</pre>";
		// echo '<a href="' . seobooster_fs()->get_upgrade_url() . '" class="button button-primary">' .__('See prices and upgrade', 'seo-booster') . '</a>';
	?>
	<div class="innerbox">
		<div class="profeatures">
			<h3><?php _e('Extra features in Premium','seo-booster'); ?></h3>
			<ul>
				<li><span></span><div><?php _e('NEW: Automatic PageSpeed Insights audits (requires free Google api key)','seo-booster'); ?></div></li>
				<li><span></span><div><?php _e('NEW: Cache the keyword to links for more speed.','seo-booster'); ?></div></li>
				<li><span></span><div><?php _e('Keyword injection - Put the most popular keyword in the &lt;title&gt tag.','seo-booster'); ?></div></li>
				<li><span></span><div><?php _e('Popular Keywords - List the most popular keywords in the bottom of each post.','seo-booster'); ?></div></li>
				<li><span></span><div><?php _e('Crawler visits - When and where search engine robots visit.','seo-booster'); ?></div></li>
				<li><span></span><div><?php _e('Get more details about the backlinks to your website.','seo-booster'); ?></div></li>
				<li><span></span><div><?php _e('Backlinks are verified regularly.','seo-booster'); ?></div></li>
				<li><span></span><div><?php _e('Optional - Keyword in title tag.','seo-booster'); ?></div></li>

				<li><span></span><div><?php _e('Export keywords, backlinks and 404 errors to .csv files.','seo-booster'); ?></div></li>
				<li><span></span><div><?php _e('You bribe your way out of seeing this message :-)','seo-booster'); ?></div></li>
				<li><span></span><div><?php _e('Get premium support.','seo-booster'); ?></div></li>
			</ul>
		</div>
		<div class="faq">
			<?php
			echo '<a href="' . seobooster_fs()->get_trial_url() .'" class="button button-primary freetrial">' .
			__('Start 30 Day Trial', 'seo-booster') . '<span>Try the premium features</span></a>';
			//echo '<a href="' . seobooster_fs()->get_upgrade_url() . '" class="button button-primary">' .
			__('See prices', 'seo-booster') . '</a>';
			?>
			<p><center><em>$4.99 per month. Annual price $47.88 for 1 site or $95.88 for unlimited sites.</em></center></p>
			<h3><?php _e('Mini FAQ','seo-booster'); ?></h3>
			<ul>
				<li><?php _e('We ask for your payment information to reduce fraud and provide a seamless subscription experience.','seo-booster'); ?></li>
				<li><?php _e('CANCEL ANYTIME before the trial ends to avoid being charged.','seo-booster'); ?></li>
				<li><?php _e('We will send you an email reminder BEFORE your trial ends.','seo-booster'); ?></li>
				<li><?php _e('We accept Visa, Mastercard, American Express and PayPal.','seo-booster'); ?></li>
				<li><?php _e('Different plans - monthly, yearly or lifetime.','seo-booster'); ?></li>
				<li><?php _e('Upgrade, downgrade or cancel any time.','seo-booster'); ?></li>
			</ul>

		</div>
	</div>
</section>