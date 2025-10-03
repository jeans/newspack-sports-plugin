<?php
/**
 * Sport Tabs Template Part
 *
 * @package Newspack_Sports
 */

$tabs = $args['tabs'] ?? [];
$current_tab = $args['current_tab'] ?? 'news';
?>

<nav class="sports-tabs-navigation" role="navigation" aria-label="<?php esc_attr_e( 'Sports sections', 'newspack-sports' ); ?>">
	<ul class="sports-tabs-list">
		<?php foreach ( $tabs as $tab_key => $tab ) : ?>
			<li class="sports-tab-item <?php echo Newspack_Sports\Core\Template_Handler::is_active_tab( $tab_key ) ? 'active' : ''; ?>">
				<a href="<?php echo esc_url( $tab['url'] ); ?>" class="sports-tab-link">
					<?php echo esc_html( $tab['label'] ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>