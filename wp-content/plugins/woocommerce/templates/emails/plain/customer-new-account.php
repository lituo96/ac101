<?php
/**
 * Customer new account email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/plain/customer-new-account.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails/Plain
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

echo "= " . $email_heading . " =\n\n";

echo sprintf( __( '感谢您创建一个帐户 %1$s. 您的用户名是 %2$s', 'woocommerce' ), $blogname, '<strong>' . $user_login . '</strong>' ) . "\n\n";

if ( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && $password_generated ) {
	echo sprintf( __( '您的密码是 %s.', 'woocommerce' ), '<strong>' . $user_pass . '</strong>' ) . "\n\n";
}

echo sprintf( __( '您可以访问您的帐户区域查看您的订单和更改您的密码在这里: %s.', 'woocommerce' ), wc_get_page_permalink( 'myaccount' ) ) . "\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
