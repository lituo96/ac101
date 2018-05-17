<?php
/**
 * The template part for displaying a message that posts cannot be found.
 *
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package storefront
 */

?>

<div class="no-results not-found">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Nothing Found', 'storefront' ); ?></h1>
	</header><!-- .page-header -->

	<div class="page-content">
		<?php if ( is_home() && current_user_can( 'publish_posts' ) ) : ?>

			<p><?php printf( wp_kses( __( '准备发表你的第一篇文章了吗？Ready to publish your first post? <a href="%1$s">Get started here</a>.', 'storefront' ), array( 'a' => array( 'href' => array() ) ) ), esc_url( admin_url( 'post-new.php' ) ) ); ?></p>

		<?php elseif ( is_search() ) : ?>

			<p><?php esc_html_e( '对不起，但没有匹配您的搜索条件。请用一些不同的关键字再试一次。Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'storefront' ); ?></p>
			<?php get_search_form(); ?>

		<?php else : ?>

			<p><?php esc_html_e( '看来我们找不到你要找的东西了。也许搜索会有所帮助。It seems we can&rsquo;t find what you&rsquo;re looking for. Perhaps searching can help.', 'storefront' ); ?></p>
			<?php get_search_form(); ?>

		<?php endif; ?>
	</div><!-- .page-content -->
</div><!-- .no-results -->
