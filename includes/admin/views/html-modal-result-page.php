<?php
defined('ABSPATH') or die();

include('html-start.php');

/**
 * @var $request
 */
if ($request === WCMP_Export::EXPORT_RETURN) {
    printf('<h3>%s</h3>', __('Return email successfully sent to customer', 'woocommerce-myparcel'));
}

include('html-end.php');
