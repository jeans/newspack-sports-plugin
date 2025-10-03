<?php
namespace Newspack_Sports\Admin;

class Admin {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_menu' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		add_filter( 'manage_edit-sport_columns', [ $this, 'add_sport_columns' ] );
		add_filter( 'manage_sport_custom_column', [ $this, 'manage_sport_columns' ], 10, 3 );
		add_filter( 'manage_edit-competition_columns', [ $this, 'add_competition_columns' ] );
		add_filter( 'manage_competition_custom_column', [ $this, 'manage_competition_columns' ], 10, 3 );
	}

	/**
	 * Add admin menu pages
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'Sports Settings', 'newspack-sports' ),
			__( 'Sports', 'newspack-sports' ),
			'manage_options',
			'newspack-sports',
			[ $this, 'admin_settings_page' ],
			'dashicons-awards',
			30
		);

		add_submenu_page(
			'newspack-sports',
			__( 'Sports Settings', 'newspack-sports' ),
			__( 'Settings', 'newspack-sports' ),
			'manage_options',
			'newspack-sports',
			[ $this, 'admin_settings_page' ]
		);

		add_submenu_page(
			'newspack-sports',
			__( 'Sports Data', 'newspack-sports' ),
			__( 'Data Import', 'newspack-sports' ),
			'manage_options',
			'newspack-sports-data',
			[ $this, 'data_import_page' ]
		);
	}

	/**
	 * Register plugin settings
	 */
	public function register_settings() {
		// API Keys section
		register_setting( 'newspack_sports_settings', 'newspack_sports_sportsradar_api_key' );
		register_setting( 'newspack_sports_settings', 'newspack_sports_statsperform_api_key' );
		register_setting( 'newspack_sports_settings', 'newspack_sports_heimspiel_api_key' );

		// General settings
		register_setting( 'newspack_sports_settings', 'newspack_sports_default_sport' );
		register_setting( 'newspack_sports_settings', 'newspack_sports_enable_cache' );
		register_setting( 'newspack_sports_settings', 'newspack_sports_cache_duration' );

		add_settings_section(
			'newspack_sports_api_section',
			__( 'API Settings', 'newspack-sports' ),
			[ $this, 'api_section_callback' ],
			'newspack_sports_settings'
		);

		add_settings_field(
			'newspack_sports_sportsradar_api_key',
			__( 'Sportsradar API Key', 'newspack-sports' ),
			[ $this, 'sportsradar_api_key_callback' ],
			'newspack_sports_settings',
			'newspack_sports_api_section'
		);

		add_settings_field(
			'newspack_sports_statsperform_api_key',
			__( 'Statsperform API Key', 'newspack-sports' ),
			[ $this, 'statsperform_api_key_callback' ],
			'newspack_sports_settings',
			'newspack_sports_api_section'
		);

		add_settings_field(
			'newspack_sports_heimspiel_api_key',
			__( 'Heimspiel API Key', 'newspack-sports' ),
			[ $this, 'heimspiel_api_key_callback' ],
			'newspack_sports_settings',
			'newspack_sports_api_section'
		);

		add_settings_section(
			'newspack_sports_general_section',
			__( 'General Settings', 'newspack-sports' ),
			[ $this, 'general_section_callback' ],
			'newspack_sports_settings'
		);

		add_settings_field(
			'newspack_sports_default_sport',
			__( 'Default Sport', 'newspack-sports' ),
			[ $this, 'default_sport_callback' ],
			'newspack_sports_settings',
			'newspack_sports_general_section'
		);

		add_settings_field(
			'newspack_sports_enable_cache',
			__( 'Enable Caching', 'newspack-sports' ),
			[ $this, 'enable_cache_callback' ],
			'newspack_sports_settings',
			'newspack_sports_general_section'
		);

		add_settings_field(
			'newspack_sports_cache_duration',
			__( 'Cache Duration (minutes)', 'newspack-sports' ),
			[ $this, 'cache_duration_callback' ],
			'newspack_sports_settings',
			'newspack_sports_general_section'
		);
	}

	/**
	 * API section callback
	 */
	public function api_section_callback() {
		echo '<p>' . __( 'Configure API keys for sports data providers.', 'newspack-sports' ) . '</p>';
	}

	/**
	 * General section callback
	 */
	public function general_section_callback() {
		echo '<p>' . __( 'General sports plugin settings.', 'newspack-sports' ) . '</p>';
	}

	/**
	 * Sportsradar API key field
	 */
	public function sportsradar_api_key_callback() {
		$value = get_option( 'newspack_sports_sportsradar_api_key', '' );
		echo '<input type="password" name="newspack_sports_sportsradar_api_key" value="' . esc_attr( $value ) . '" class="regular-text" />';
		echo '<p class="description">' . __( 'Get your API key from <a href="https://developer.sportradar.com/" target="_blank">Sportsradar Developer Portal</a>.', 'newspack-sports' ) . '</p>';
	}

	/**
	 * Statsperform API key field
	 */
	public function statsperform_api_key_callback() {
		$value = get_option( 'newspack_sports_statsperform_api_key', '' );
		echo '<input type="password" name="newspack_sports_statsperform_api_key" value="' . esc_attr( $value ) . '" class="regular-text" />';
		echo '<p class="description">' . __( 'Get your API key from Statsperform.', 'newspack-sports' ) . '</p>';
	}

	/**
	 * Heimspiel API key field
	 */
	public function heimspiel_api_key_callback() {
		$value = get_option( 'newspack_sports_heimspiel_api_key', '' );
		echo '<input type="password" name="newspack_sports_heimspiel_api_key" value="' . esc_attr( $value ) . '" class="regular-text" />';
		echo '<p class="description">' . __( 'Get your API key from Heimspiel.', 'newspack-sports' ) . '</p>';
	}

	/**
	 * Default sport field
	 */
	public function default_sport_callback() {
		$value = get_option( 'newspack_sports_default_sport', '' );
		$sports = get_terms( [
			'taxonomy' => 'sport',
			'hide_empty' => false,
		] );

		echo '<select name="newspack_sports_default_sport">';
		echo '<option value="">' . __( 'Select a sport', 'newspack-sports' ) . '</option>';
		foreach ( $sports as $sport ) {
			echo '<option value="' . esc_attr( $sport->slug ) . '" ' . selected( $value, $sport->slug, false ) . '>' . esc_html( $sport->name ) . '</option>';
		}
		echo '</select>';
		echo '<p class="description">' . __( 'Default sport for the homepage or when no sport is specified.', 'newspack-sports' ) . '</p>';
	}

	/**
	 * Enable cache field
	 */
	public function enable_cache_callback() {
		$value = get_option( 'newspack_sports_enable_cache', '1' );
		echo '<input type="checkbox" name="newspack_sports_enable_cache" value="1" ' . checked( $value, '1', false ) . ' />';
		echo '<p class="description">' . __( 'Enable caching for sports data to improve performance.', 'newspack-sports' ) . '</p>';
	}

	/**
	 * Cache duration field
	 */
	public function cache_duration_callback() {
		$value = get_option( 'newspack_sports_cache_duration', '30' );
		echo '<input type="number" name="newspack_sports_cache_duration" value="' . esc_attr( $value ) . '" min="1" max="1440" />';
		echo '<p class="description">' . __( 'How long to cache sports data in minutes.', 'newspack-sports' ) . '</p>';
	}

	/**
	 * Main settings page
	 */
	public function admin_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<div class="newspack-sports-admin-header">
				<div class="newspack-sports-admin-stats">
					<div class="stat-box">
						<h3><?php echo wp_count_terms( 'sport' ); ?></h3>
						<p><?php _e( 'Sports', 'newspack-sports' ); ?></p>
					</div>
					<div class="stat-box">
						<h3><?php echo wp_count_terms( 'competition' ); ?></h3>
						<p><?php _e( 'Competitions', 'newspack-sports' ); ?></p>
					</div>
					<div class="stat-box">
						<h3><?php echo wp_count_posts( 'sports_match' )->publish; ?></h3>
						<p><?php _e( 'Matches', 'newspack-sports' ); ?></p>
					</div>
					<div class="stat-box">
						<h3><?php echo wp_count_posts( 'sports_team' )->publish; ?></h3>
						<p><?php _e( 'Teams', 'newspack-sports' ); ?></p>
					</div>
				</div>
			</div>

			<form action="options.php" method="post">
				<?php
				settings_fields( 'newspack_sports_settings' );
				do_settings_sections( 'newspack_sports_settings' );
				submit_button( __( 'Save Settings', 'newspack-sports' ) );
				?>
			</form>

			<div class="newspack-sports-quick-links">
				<h2><?php _e( 'Quick Links', 'newspack-sports' ); ?></h2>
				<div class="quick-links-grid">
					<a href="<?php echo admin_url( 'edit-tags.php?taxonomy=sport' ); ?>" class="quick-link">
						<span class="dashicons dashicons-admin-generic"></span>
						<span><?php _e( 'Manage Sports', 'newspack-sports' ); ?></span>
					</a>
					<a href="<?php echo admin_url( 'edit-tags.php?taxonomy=competition' ); ?>" class="quick-link">
						<span class="dashicons dashicons-tag"></span>
						<span><?php _e( 'Manage Competitions', 'newspack-sports' ); ?></span>
					</a>
					<a href="<?php echo admin_url( 'edit.php?post_type=sports_match' ); ?>" class="quick-link">
						<span class="dashicons dashicons-calendar"></span>
						<span><?php _e( 'Manage Matches', 'newspack-sports' ); ?></span>
					</a>
					<a href="<?php echo admin_url( 'edit.php?post_type=sports_team' ); ?>" class="quick-link">
						<span class="dashicons dashicons-groups"></span>
						<span><?php _e( 'Manage Teams', 'newspack-sports' ); ?></span>
					</a>
					<a href="<?php echo admin_url( 'edit.php?post_type=sports_person' ); ?>" class="quick-link">
						<span class="dashicons dashicons-id"></span>
						<span><?php _e( 'Manage Persons', 'newspack-sports' ); ?></span>
					</a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Data import page
	 */
	public function data_import_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php _e( 'Sports Data Import', 'newspack-sports' ); ?></h1>
			
			<div class="newspack-sports-import-sections">
				<div class="import-section">
					<h2><?php _e( 'API Data Sync', 'newspack-sports' ); ?></h2>
					<p><?php _e( 'Sync live data from your configured API providers.', 'newspack-sports' ); ?></p>
					
					<div class="api-status">
						<h3><?php _e( 'API Status', 'newspack-sports' ); ?></h3>
						<ul>
							<li>
								<strong>Sportsradar:</strong> 
								<span class="status-indicator <?php echo get_option( 'newspack_sports_sportsradar_api_key' ) ? 'status-active' : 'status-inactive'; ?>">
									<?php echo get_option( 'newspack_sports_sportsradar_api_key' ) ? __( 'Configured', 'newspack-sports' ) : __( 'Not Configured', 'newspack-sports' ); ?>
								</span>
							</li>
							<li>
								<strong>Statsperform:</strong> 
								<span class="status-indicator <?php echo get_option( 'newspack_sports_statsperform_api_key' ) ? 'status-active' : 'status-inactive'; ?>">
									<?php echo get_option( 'newspack_sports_statsperform_api_key' ) ? __( 'Configured', 'newspack-sports' ) : __( 'Not Configured', 'newspack-sports' ); ?>
								</span>
							</li>
							<li>
								<strong>Heimspiel:</strong> 
								<span class="status-indicator <?php echo get_option( 'newspack_sports_heimspiel_api_key' ) ? 'status-active' : 'status-inactive'; ?>">
									<?php echo get_option( 'newspack_sports_heimspiel_api_key' ) ? __( 'Configured', 'newspack-sports' ) : __( 'Not Configured', 'newspack-sports' ); ?>
								</span>
							</li>
						</ul>
					</div>

					<div class="sync-actions">
						<h3><?php _e( 'Manual Sync', 'newspack-sports' ); ?></h3>
						<button type="button" class="button button-primary" id="sync-standings">
							<?php _e( 'Sync Standings', 'newspack-sports' ); ?>
						</button>
						<button type="button" class="button button-primary" id="sync-schedule">
							<?php _e( 'Sync Schedule', 'newspack-sports' ); ?></button>
						<button type="button" class="button button-primary" id="sync-results">
							<?php _e( 'Sync Results', 'newspack-sports' ); ?>
						</button>
					</div>
				</div>

				<div class="import-section">
					<h2><?php _e( 'Bulk Import', 'newspack-sports' ); ?></h2>
					<p><?php _e( 'Import sports data from CSV files.', 'newspack-sports' ); ?></p>
					
					<form method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'newspack_sports_import', 'newspack_sports_import_nonce' ); ?>
						<table class="form-table">
							<tr>
								<th scope="row">
									<label for="import_type"><?php _e( 'Import Type', 'newspack-sports' ); ?></label>
								</th>
								<td>
									<select name="import_type" id="import_type">
										<option value="teams"><?php _e( 'Teams', 'newspack-sports' ); ?></option>
										<option value="matches"><?php _e( 'Matches', 'newspack-sports' ); ?></option>
										<option value="players"><?php _e( 'Players', 'newspack-sports' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="csv_file"><?php _e( 'CSV File', 'newspack-sports' ); ?></label>
								</th>
								<td>
									<input type="file" name="csv_file" id="csv_file" accept=".csv" />
									<p class="description"><?php _e( 'Upload a CSV file with the appropriate format.', 'newspack-sports' ); ?></p>
								</td>
							</tr>
						</table>
						<?php submit_button( __( 'Import CSV', 'newspack-sports' ) ); ?>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue admin scripts and styles
	 */
	public function enqueue_admin_scripts( $hook ) {
		if ( strpos( $hook, 'newspack-sports' ) === false ) {
			return;
		}

		wp_enqueue_style(
			'newspack-sports-admin',
			NEWSPACK_SPORTS_PLUGIN_URL . 'assets/css/admin.css',
			[],
			NEWSPACK_SPORTS_VERSION
		);

		wp_enqueue_script(
			'newspack-sports-admin',
			NEWSPACK_SPORTS_PLUGIN_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			NEWSPACK_SPORTS_VERSION,
			true
		);

		wp_localize_script(
			'newspack-sports-admin',
			'newspack_sports_admin',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'newspack_sports_admin' ),
				'strings' => [
					'syncing' => __( 'Syncing...', 'newspack-sports' ),
					'synced' => __( 'Sync complete!', 'newspack-sports' ),
					'error' => __( 'Error during sync.', 'newspack-sports' ),
				]
			]
		);
	}

	/**
	 * Add custom columns to sport taxonomy
	 */
	public function add_sport_columns( $columns ) {
		$new_columns = [];
		
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( $key === 'name' ) {
				$new_columns['sports_data_source'] = __( 'Data Source', 'newspack-sports' );
				$new_columns['sports_api_id'] = __( 'API ID', 'newspack-sports' );
				$new_columns['sports_display_type'] = __( 'Display Type', 'newspack-sports' );
			}
		}
		
		return $new_columns;
	}

	/**
	 * Manage sport taxonomy columns content
	 */
	public function manage_sport_columns( $content, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'sports_data_source':
				$data_source = get_term_meta( $term_id, 'sports_data_source', true );
				return $data_source ?: '—';
			
			case 'sports_api_id':
				$api_id = get_term_meta( $term_id, 'sports_api_id', true );
				return $api_id ?: '—';
			
			case 'sports_display_type':
				$display_type = get_term_meta( $term_id, 'sports_display_type', true );
				return $display_type ? ucfirst( $display_type ) : 'Archive';
			
			default:
				return $content;
		}
	}

	/**
	 * Add custom columns to competition taxonomy
	 */
	public function add_competition_columns( $columns ) {
		$new_columns = [];
		
		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;
			if ( $key === 'name' ) {
				$new_columns['sports_sport'] = __( 'Sport', 'newspack-sports' );
				$new_columns['sports_api_id'] = __( 'API ID', 'newspack-sports' );
			}
		}
		
		return $new_columns;
	}

	/**
	 * Manage competition taxonomy columns content
	 */
	public function manage_competition_columns( $content, $column_name, $term_id ) {
		switch ( $column_name ) {
			case 'sports_sport':
				$sport = get_term_meta( $term_id, 'sport', true );
				if ( $sport ) {
					$sport_term = get_term( $sport, 'sport' );
					return $sport_term ? $sport_term->name : '—';
				}
				return '—';
			
			case 'sports_api_id':
				$api_id = get_term_meta( $term_id, 'sports_api_id', true );
				return $api_id ?: '—';
			
			default:
				return $content;
		}
	}
}