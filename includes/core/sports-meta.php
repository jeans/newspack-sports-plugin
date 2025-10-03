<?php
namespace Newspack_Sports\Core;

class Sports_Meta {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'register_term_meta' ] );
		add_action( 'sport_add_form_fields', [ $this, 'add_sport_fields' ] );
		add_action( 'sport_edit_form_fields', [ $this, 'edit_sport_fields' ] );
		add_action( 'created_sport', [ $this, 'save_sport_fields' ] );
		add_action( 'edited_sport', [ $this, 'save_sport_fields' ] );
	}

	public function register_term_meta() {
		// Display type (archive vs page)
		register_meta( 'term', 'sports_display_type', [
			'type'         => 'string',
			'show_in_rest' => true,
			'single'       => true,
			'default'      => 'archive',
		] );

		// Associated page for custom display
		register_meta( 'term', 'sports_display_page', [
			'type'         => 'integer',
			'show_in_rest' => true,
			'single'       => true,
		] );

		// Remote data source
		register_meta( 'term', 'sports_data_source', [
			'type'         => 'string',
			'show_in_rest' => true,
			'single'       => true,
			'default'      => 'sportsradar',
		] );

		// API competition ID
		register_meta( 'term', 'sports_api_id', [
			'type'         => 'string',
			'show_in_rest' => true,
			'single'       => true,
		] );

		// Sport icon
		register_meta( 'term', 'sports_icon', [
			'type'         => 'string',
			'show_in_rest' => true,
			'single'       => true,
		] );
	}

	public function add_sport_fields( $taxonomy ) {
		?>
		<div class="form-field">
			<label for="sports_display_type"><?php _e( 'Display Type', 'newspack-sports' ); ?></label>
			<select name="sports_display_type" id="sports_display_type">
				<option value="archive"><?php _e( 'Archive Page', 'newspack-sports' ); ?></option>
				<option value="page"><?php _e( 'Custom Page', 'newspack-sports' ); ?></option>
			</select>
			<p><?php _e( 'Choose how this sport should be displayed.', 'newspack-sports' ); ?></p>
		</div>

		<div class="form-field">
			<label for="sports_display_page"><?php _e( 'Display Page', 'newspack-sports' ); ?></label>
			<?php
			wp_dropdown_pages( [
				'name'             => 'sports_display_page',
				'show_option_none' => __( 'Select a page', 'newspack-sports' ),
				'post_status'      => 'publish',
			] );
			?>
			<p><?php _e( 'Select a page to display for this sport (if using custom page display).', 'newspack-sports' ); ?></p>
		</div>

		<div class="form-field">
			<label for="sports_data_source"><?php _e( 'Data Source', 'newspack-sports' ); ?></label>
			<select name="sports_data_source" id="sports_data_source">
				<option value="sportsradar">Sportsradar</option>
				<option value="statsperform">Statsperform</option>
				<option value="heimspiel">Heimspiel</option>
			</select>
			<p><?php _e( 'Select the data provider for this sport.', 'newspack-sports' ); ?></p>
		</div>

		<div class="form-field">
			<label for="sports_api_id"><?php _e( 'API Competition ID', 'newspack-sports' ); ?></label>
			<input type="text" name="sports_api_id" id="sports_api_id" value="" />
			<p><?php _e( 'Enter the competition ID from the data provider.', 'newspack-sports' ); ?></p>
		</div>
		<?php
	}

	public function edit_sport_fields( $term ) {
		$display_type = get_term_meta( $term->term_id, 'sports_display_type', true );
		$display_page = get_term_meta( $term->term_id, 'sports_display_page', true );
		$data_source  = get_term_meta( $term->term_id, 'sports_data_source', true );
		$api_id       = get_term_meta( $term->term_id, 'sports_api_id', true );
		?>
		<tr class="form-field">
			<th scope="row">
				<label for="sports_display_type"><?php _e( 'Display Type', 'newspack-sports' ); ?></label>
			</th>
			<td>
				<select name="sports_display_type" id="sports_display_type">
					<option value="archive" <?php selected( $display_type, 'archive' ); ?>><?php _e( 'Archive Page', 'newspack-sports' ); ?></option>
					<option value="page" <?php selected( $display_type, 'page' ); ?>><?php _e( 'Custom Page', 'newspack-sports' ); ?></option>
				</select>
				<p class="description"><?php _e( 'Choose how this sport should be displayed.', 'newspack-sports' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row">
				<label for="sports_display_page"><?php _e( 'Display Page', 'newspack-sports' ); ?></label>
			</th>
			<td>
				<?php
				wp_dropdown_pages( [
					'name'             => 'sports_display_page',
					'selected'         => $display_page,
					'show_option_none' => __( 'Select a page', 'newspack-sports' ),
					'post_status'      => 'publish',
				] );
				?>
				<p class="description"><?php _e( 'Select a page to display for this sport (if using custom page display).', 'newspack-sports' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row">
				<label for="sports_data_source"><?php _e( 'Data Source', 'newspack-sports' ); ?></label>
			</th>
			<td>
				<select name="sports_data_source" id="sports_data_source">
					<option value="sportsradar" <?php selected( $data_source, 'sportsradar' ); ?>>Sportsradar</option>
					<option value="statsperform" <?php selected( $data_source, 'statsperform' ); ?>>Statsperform</option>
					<option value="heimspiel" <?php selected( $data_source, 'heimspiel' ); ?>>Heimspiel</option>
				</select>
				<p class="description"><?php _e( 'Select the data provider for this sport.', 'newspack-sports' ); ?></p>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row">
				<label for="sports_api_id"><?php _e( 'API Competition ID', 'newspack-sports' ); ?></label>
			</th>
			<td>
				<input type="text" name="sports_api_id" id="sports_api_id" value="<?php echo esc_attr( $api_id ); ?>" />
				<p class="description"><?php _e( 'Enter the competition ID from the data provider.', 'newspack-sports' ); ?></p>
			</td>
		</tr>
		<?php
	}

	public function save_sport_fields( $term_id ) {
		if ( isset( $_POST['sports_display_type'] ) ) {
			update_term_meta( $term_id, 'sports_display_type', sanitize_text_field( $_POST['sports_display_type'] ) );
		}
		if ( isset( $_POST['sports_display_page'] ) ) {
			update_term_meta( $term_id, 'sports_display_page', intval( $_POST['sports_display_page'] ) );
		}
		if ( isset( $_POST['sports_data_source'] ) ) {
			update_term_meta( $term_id, 'sports_data_source', sanitize_text_field( $_POST['sports_data_source'] ) );
		}
		if ( isset( $_POST['sports_api_id'] ) ) {
			update_term_meta( $term_id, 'sports_api_id', sanitize_text_field( $_POST['sports_api_id'] ) );
		}
	}
}