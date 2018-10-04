<div class="error notice is-dismissible"><p>Plugin Name requires PHP %s %s.</p></div>

<?php

 if ( version_compare(PHP_VERSION, $this->min, $operator) ) {
            $this->hasRequiredPHP = true;
        } else {
            add_action( "admin_notices", array( $this, "notice" ) );
        }






$php_min_version_check = version_compare( HEALTH_CHECK_PHP_MIN_VERSION, PHP_VERSION, '<=' );


$message = '<p>' . sprintf( __( 'Your server is running PHP version <strong>%1$s</strong> and MySQL version <strong>%2$s</strong>.', 'health-check' ), PHP_VERSION, $wpdb->db_version() ) . '</p>';



$warning .= "<p><strong>" . __( 'Warning:', 'health-check' ) . "</strong> " . sprintf( __( 'For performance and security reasons, we strongly recommend running PHP version %s or higher.', 'health-check' ), HEALTH_CHECK_PHP_REC_VERSION ) . "</p>";




echo sprintf(__('Never miss an important update. Opt-in to our security and feature updates notifications, and non-sensitive diagnostic tracking. <a href="%s">Click here</a>', 'seo-booster'),
			$seobooster_fs->get_reconnect_url()
		);













if (version_compare(PHP_VERSION, '5.6', '<'))  {
    $warning = sprintf(__('Your website is running PHP v. %s. You should upgrade to PHP 7 as soon as possible. There are many security and functionality advantages. ', 'seo-booster'),
			PHP_VERSION
		);
}





        ?>
<div class="notice notice-warning">Your website is running PHP version %s. You should upgrade to %s as soon as possible. There are </div>








