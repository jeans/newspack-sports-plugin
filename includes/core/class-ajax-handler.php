<?php
namespace Newspack_Sports\Core;

class Ajax_Handler {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_newspack_sports_sync_data', [ $this, 'sync_data' ] );
		add_action( 'wp_ajax_nopriv_newspack_sports_find_team', [ $this, 'find_team' ] );
		add_action( 'wp_ajax_newspack_sports_find_team', [ $this, 'find_team' ] );
		add_action( 'wp_ajax_newspack_sports_get_standings', [ $this, 'get_standings' ] );
		add_action( 'wp_ajax_nopriv_newspack_sports_get_standings', [ $this, 'get_standings' ] );
	}

	public function sync_data() {
		check_ajax_referer( 'newspack_sports_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( -1, 403 );
		}

		$type = sanitize_text_field( $_POST['type'] ?? '' );

		switch ( $type ) {
			case 'standings':
				$result = $this->sync_standings();
				break;
			case 'schedule':
				$result = $this->sync_schedule();
				break;
			case 'results':
				$result = $this->sync_results();
				break;
			default:
				$result = new \WP_Error( 'invalid_type', __( 'Invalid sync type.', 'newspack-sports' ) );
		}

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( $result->get_error_message() );
		} else {
			wp_send_json_success( [
				'message' => __( 'Data synced successfully.', 'newspack-sports' ),
				'data' => $result
			] );
		}
	}

	private function sync_standings() {
		// This would integrate with Sports_Data_Manager to sync standings
		$sports = get_terms( [
			'taxonomy' => 'sport',
			'hide_empty' => false,
			'meta_query' => [
				[
					'key' => 'sports_api_id',
					'compare' => 'EXISTS'
				]
			]
		] );

		$results = [];
		foreach ( $sports as $sport ) {
			// Sync logic would go here
			$results[ $sport->slug ] = 'Synced';
		}

		return $results;
	}

	private function sync_schedule() {
		// Schedule sync logic
		return [ 'message' => 'Schedule sync completed' ];
	}

	private function sync_results() {
		// Results sync logic
		return [ 'message' => 'Results sync completed' ];
	}

	public function find_team() {
		check_ajax_referer( 'newspack_sports_frontend', 'nonce' );

		$team_name = sanitize_text_field( $_POST['team_name'] ?? '' );

		if ( empty( $team_name ) ) {
			wp_send_json_error( 'Team name required' );
		}

		$teams = get_posts( [
			'post_type' => 'sports_team',
			'posts_per_page' => 1,
			's' => $team_name,
			'fields' => 'ids'
		] );

		if ( empty( $teams ) ) {
			wp_send_json_success( null );
		}

		$team_id = $teams[0];
		$team_data = [
			'name' => get_the_title( $team_id ),
			'url' => get_permalink( $team_id ),
			'logo' => get_the_post_thumbnail_url( $team_id, 'thumbnail' )
		];

		wp_send_json_success( $team_data );
	}

	public function get_standings() {
		$sport = sanitize_text_field( $_GET['sport'] ?? '' );
		$competition = sanitize_text_field( $_GET['competition'] ?? '' );

		if ( empty( $sport ) ) {
			wp_send_json_error( 'Sport parameter required' );
		}

		$data_manager = Sports_Data_Manager::instance();
		$standings = $data_manager->fetch_sportsradar_data( [
			'sport' => $sport,
			'competition_id' => $competition,
			'data_type' => 'standings'
		] );

		if ( is_wp_error( $standings ) ) {
			wp_send_json_error( $standings->get_error_message() );
		}

		wp_send_json_success( $standings );
	}
}