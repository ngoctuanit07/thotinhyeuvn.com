<?php
// Creates warning if PHP version is less than 5.6
if (version_compare(PHP_VERSION, '7', '<'))  {

   echo '<div class="notice notice-error">';
   echo '<p>'.sprintf(__('Your website is running PHP v. %s. You should upgrade to PHP 7 as soon as possible. There are many security and functionality benefits in upgrading. You should verify your website is compatible, contact your webmaster or your hosting company.', 'seo-booster'),
			PHP_VERSION
		).'</p>';
   echo '</div>';
}

?>