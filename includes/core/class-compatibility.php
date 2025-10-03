<?php
namespace Newspack_Sports\Core;

class Compatibility {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'check_compatibility' ] );
		add_filter( 'newspack_sports_compatibility_issues', [ $this, 'check_required_plugins' ] );
	}

	public function check_compatibility() {
		$issues = apply_filters( 'newspack_sports_compatibility_issues', [] );

		if ( ! empty( $issues ) && current_user_can( 'manage_options' ) ) {
			add_action( 'admin_notices', [ $this, 'display_compatibility_notices' ] );
		}
	}

	public function check_required_plugins( $issues ) {
		// Check for Remote Data Blocks plugin
		if ( ! class_exists( 'RemoteDataBlocks\\Plugin' ) ) {
			$issues[] = [
				'type' => 'warning',
				'message' => __( 'Remote Data Blocks plugin is recommended for live sports data integration.', 'newspack-sports' ),
				'action' => '<a href="' . admin_url( 'plugin-install.php?s=Remote+Data+Blocks&tab=search&type=term' ) . '">' . __( 'Install Plugin', 'newspack-sports' ) . '</a>'
			];
		}

		// Check for Newspack Multibranded Site plugin
		if ( ! class_exists( 'Newspack_Multibranded_Site\\Plugin' ) ) {
			$issues[] = [
				'type' => 'info',
				'message' => __( 'Newspack Multibranded Site plugin can be used alongside this plugin for multi-sport branding.', 'newspack-sports' ),
				'action' => '<a href="' . admin_url( 'plugin-install.php?s=Newspack+Multibranded+Site&tab=search&type=term' ) . '">' . __( 'Learn More', 'newspack-sports' ) . '</a>'
			];
		}

		// Check permalink structure
		if ( ! get_option( 'permalink_structure' ) ) {
			$issues[] = [
				'type' => 'error',
				'message' => __( 'Pretty permalinks are required for sports URLs to work properly.', 'newspack-sports' ),
				'action' => '<a href="' . admin_url( 'options-permalink.php' ) . '">' . __( 'Update Permalinks', 'newspack-sports' ) . '</a>'
			];
		}

		return $issues;
	}

	public function display_compatibility_notices() {
		$issues = apply_filters( 'newspack_sports_compatibility_issues', [] );

		foreach ( $issues as $issue ) {
			$class = 'notice-' . $issue['type'];
			?>
			<div class="notice <?php echo esc_attr( $class ); ?> is-dismissible">
				<p>
					<strong><?php _e( 'Newspack Sports:', 'newspack-sports' ); ?></strong>
					<?php echo esc_html( $issue['message'] ); ?>
					<?php if ( ! empty( $issue['action'] ) ) : ?>
						&nbsp;<?php echo $issue['action']; ?>
					<?php endif; ?>
				</p>
			</div>
			<?php
		}
	}

	public static function is_plugin_active( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', [] ), true ) || self::is_plugin_active_for_network( $plugin );
	}

	public static function is_plugin_active_for_network( $plugin ) {
		if ( ! is_multisite() ) {
			return false;
		}

		$plugins = get_site_option( 'active_sitewide_plugins' );
		return isset( $plugins[ $plugin ] );
	}
}