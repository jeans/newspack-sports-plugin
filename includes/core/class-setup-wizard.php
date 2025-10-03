<?php
namespace Newspack_Sports\Core;

class Setup_Wizard {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_setup_page' ] );
		add_action( 'admin_init', [ $this, 'handle_setup_steps' ] );
	}

	public function add_setup_page() {
		add_submenu_page(
			'newspack-sports',
			__( 'Sports Setup Wizard', 'newspack-sports' ),
			__( 'Setup Wizard', 'newspack-sports' ),
			'manage_options',
			'newspack-sports-setup',
			[ $this, 'setup_wizard_page' ]
		);
	}

	public function setup_wizard_page() {
		$step = sanitize_text_field( $_GET['step'] ?? 'welcome' );
		?>
		<div class="wrap newspack-sports-setup-wizard">
			<div class="setup-wizard-header">
				<h1><?php _e( 'Newspack Sports Setup Wizard', 'newspack-sports' ); ?></h1>
				<div class="progress-steps">
					<?php $this->display_progress_steps( $step ); ?>
				</div>
			</div>

			<div class="setup-wizard-content">
				<?php
				switch ( $step ) {
					case 'welcome':
						$this->welcome_step();
						break;
					case 'sports':
						$this->sports_step();
						break;
					case 'apis':
						$this->apis_step();
						break;
					case 'complete':
						$this->complete_step();
						break;
				}
				?>
			</div>
		</div>
		<?php
	}

	private function display_progress_steps( $current_step ) {
		$steps = [
			'welcome' => __( 'Welcome', 'newspack-sports' ),
			'sports' => __( 'Sports Setup', 'newspack-sports' ),
			'apis' => __( 'API Configuration', 'newspack-sports' ),
			'complete' => __( 'Complete', 'newspack-sports' ),
		];
		?>
		<ul class="progress-steps-list">
			<?php foreach ( $steps as $step => $label ) : ?>
				<li class="progress-step <?php echo $step === $current_step ? 'active' : ''; ?>">
					<span class="step-number"><?php echo array_search( $step, array_keys( $steps ) ) + 1; ?></span>
					<span class="step-label"><?php echo esc_html( $label ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}

	private function welcome_step() {
		?>
		<div class="welcome-step">
			<h2><?php _e( 'Welcome to Newspack Sports', 'newspack-sports' ); ?></h2>
			<p><?php _e( 'This wizard will help you set up your sports website with the necessary structure and configurations.', 'newspack-sports' ); ?></p>
			
			<div class="welcome-features">
				<h3><?php _e( 'What we\'ll set up:', 'newspack-sports' ); ?></h3>
				<ul>
					<li><?php _e( 'Sports and competitions hierarchy', 'newspack-sports' ); ?></li>
					<li><?php _e( 'Teams, matches, and players structure', 'newspack-sports' ); ?></li>
					<li><?php _e( 'API connections for live data', 'newspack-sports' ); ?></li>
					<li><?php _e( 'Template system for sports pages', 'newspack-sports' ); ?></li>
				</ul>
			</div>

			<div class="wizard-actions">
				<a href="<?php echo admin_url( 'admin.php?page=newspack-sports-setup&step=sports' ); ?>" class="button button-primary button-large">
					<?php _e( 'Get Started', 'newspack-sports' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	private function sports_step() {
		// Sports setup step implementation
		?>
		<div class="sports-step">
			<h2><?php _e( 'Sports Setup', 'newspack-sports' ); ?></h2>
			<form method="post">
				<?php wp_nonce_field( 'newspack_sports_setup', 'setup_nonce' ); ?>
				<input type="hidden" name="step" value="sports">
				
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="sports_list"><?php _e( 'Initial Sports', 'newspack-sports' ); ?></label>
						</th>
						<td>
							<textarea name="sports_list" id="sports_list" rows="5" cols="50" placeholder="Soccer&#10;Basketball&#10;Tennis&#10;Olympics"></textarea>
							<p class="description"><?php _e( 'Enter one sport per line. These will be created as top-level sports categories.', 'newspack-sports' ); ?></p>
						</td>
					</tr>
				</table>

				<div class="wizard-actions">
					<a href="<?php echo admin_url( 'admin.php?page=newspack-sports-setup&step=welcome' ); ?>" class="button">
						<?php _e( 'Back', 'newspack-sports' ); ?>
					</a>
					<button type="submit" name="submit_sports" class="button button-primary">
						<?php _e( 'Continue', 'newspack-sports' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}

	private function apis_step() {
		// API configuration step
		?>
		<div class="apis-step">
			<h2><?php _e( 'API Configuration', 'newspack-sports' ); ?></h2>
			<p><?php _e( 'Configure API keys for live sports data integration.', 'newspack-sports' ); ?></p>
			
			<form method="post">
				<?php wp_nonce_field( 'newspack_sports_setup', 'setup_nonce' ); ?>
				<input type="hidden" name="step" value="apis">
				
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="sportsradar_key"><?php _e( 'Sportsradar API Key', 'newspack-sports' ); ?></label>
						</th>
						<td>
							<input type="password" name="sportsradar_key" id="sportsradar_key" class="regular-text">
							<p class="description"><?php _e( 'Get your key from the Sportsradar developer portal.', 'newspack-sports' ); ?></p>
						</td>
					</tr>
				</table>

				<div class="wizard-actions">
					<a href="<?php echo admin_url( 'admin.php?page=newspack-sports-setup&step=sports' ); ?>" class="button">
						<?php _e( 'Back', 'newspack-sports' ); ?>
					</a>
					<button type="submit" name="submit_apis" class="button button-primary">
						<?php _e( 'Continue', 'newspack-sports' ); ?>
					</button>
					<button type="submit" name="skip_apis" class="button">
						<?php _e( 'Skip API Setup', 'newspack-sports' ); ?>
					</button>
				</div>
			</form>
		</div>
		<?php
	}

	private function complete_step() {
		?>
		<div class="complete-step">
			<h2><?php _e( 'Setup Complete!', 'newspack-sports' ); ?></h2>
			<div class="success-message">
				<p><?php _e( 'Your sports website is now ready. Here are some next steps:', 'newspack-sports' ); ?></p>
				
				<div class="next-steps">
					<div class="next-step">
						<h3><?php _e( 'Add Sports Content', 'newspack-sports' ); ?></h3>
						<p><?php _e( 'Start adding teams, matches, and players to your sports.', 'newspack-sports' ); ?></p>
						<a href="<?php echo admin_url( 'edit.php?post_type=sports_team' ); ?>" class="button">
							<?php _e( 'Manage Teams', 'newspack-sports' ); ?>
						</a>
					</div>
					
					<div class="next-step">
						<h3><?php _e( 'Configure Sports', 'newspack-sports' ); ?></h3>
						<p><?php _e( 'Set up your sports hierarchy and competition structure.', 'newspack-sports' ); ?></p>
						<a href="<?php echo admin_url( 'edit-tags.php?taxonomy=sport' ); ?>" class="button">
							<?php _e( 'Manage Sports', 'newspack-sports' ); ?>
						</a>
					</div>
					
					<div class="next-step">
						<h3><?php _e( 'View Sports Pages', 'newspack-sports' ); ?></h3>
						<p><?php _e( 'See how your sports content looks on the frontend.', 'newspack-sports' ); ?></p>
						<a href="<?php echo home_url( '/sport/' ); ?>" class="button">
							<?php _e( 'View Sports', 'newspack-sports' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function handle_setup_steps() {
		if ( ! isset( $_POST['setup_nonce'] ) || ! wp_verify_nonce( $_POST['setup_nonce'], 'newspack_sports_setup' ) ) {
			return;
		}

		$step = sanitize_text_field( $_POST['step'] ?? '' );

		switch ( $step ) {
			case 'sports':
				$this->process_sports_step();
				break;
			case 'apis':
				$this->process_apis_step();
				break;
		}
	}

	private function process_sports_step() {
		if ( isset( $_POST['sports_list'] ) ) {
			$sports = array_filter( array_map( 'trim', explode( "\n", $_POST['sports_list'] ) ) );
			
			foreach ( $sports as $sport_name ) {
				if ( ! term_exists( $sport_name, 'sport' ) ) {
					wp_insert_term( $sport_name, 'sport' );
				}
			}
		}

		wp_redirect( admin_url( 'admin.php?page=newspack-sports-setup&step=apis' ) );
		exit;
	}

	private function process_apis_step() {
		if ( isset( $_POST['sportsradar_key'] ) ) {
			update_option( 'newspack_sports_sportsradar_api_key', sanitize_text_field( $_POST['sportsradar_key'] ) );
		}

		wp_redirect( admin_url( 'admin.php?page=newspack-sports-setup&step=complete' ) );
		exit;
	}
}