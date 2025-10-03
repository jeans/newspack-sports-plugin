<?php
namespace Newspack_Sports\Core;

class Sports_Data_Manager {
	private static $instance = null;

	private $api_providers = [
		'sportsradar' => [
			'name' => 'Sportsradar',
			'base_url' => 'https://api.sportradar.com',
			'sports' => [
				'soccer' => [
					'endpoints' => [
						'standings' => '/soccer/{access_level}{version}/{lang}/tournaments/{tournament_id}/standings.json',
						'schedule' => '/soccer/{access_level}{version}/{lang}/tournaments/{tournament_id}/schedule.json',
						'results' => '/soccer/{access_level}{version}/{lang}/tournaments/{tournament_id}/results.json',
					],
					'access_level' => 'production',
					'version' => 'v4',
				],
				'basketball' => [
					'endpoints' => [
						'standings' => '/basketball/{access_level}{version}/{lang}/tournaments/{tournament_id}/standings.json',
						'schedule' => '/basketball/{access_level}{version}/{lang}/tournaments/{tournament_id}/schedule.json',
					],
					'access_level' => 'production',
					'version' => 'v4',
				]
			]
		],
		'statsperform' => [
			'name' => 'Statsperform',
			'base_url' => 'https://api.statsperform.com',
			'sports' => [
				'soccer' => [
					'endpoints' => [
						'standings' => '/opta/football/tournaments/{tournament_id}/standings',
						'matches' => '/opta/football/tournaments/{tournament_id}/matches',
					]
				]
			]
		],
		'heimspiel' => [
			'name' => 'Heimspiel',
			'base_url' => 'https://api.heimspiel.com',
			'sports' => [
				'olympics' => [
					'endpoints' => [
						'standings' => '/v1/olympics/{sport}/standings',
						'schedule' => '/v1/olympics/{sport}/schedule',
					]
				]
			]
		]
	];

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'register_remote_data_blocks' ] );
		add_filter( 'remotedatablocks_data_sources', [ $this, 'register_data_sources' ] );
		add_filter( 'remotedatablocks_cache_duration', [ $this, 'adjust_cache_duration' ], 10, 2 );
	}

	/**
	 * Register custom Gutenberg blocks for sports data
	 */
	public function register_remote_data_blocks() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Sports Standings Block
		register_block_type( 'newspack-sports/standings', [
			'api_version' => 2,
			'title' => __( 'Sports Standings', 'newspack-sports' ),
			'category' => 'sports',
			'icon' => 'list-view',
			'supports' => [
				'html' => false,
			],
			'attributes' => [
				'sport' => [
					'type' => 'string',
					'default' => '',
				],
				'competition' => [
					'type' => 'string',
					'default' => '',
				],
				'dataSource' => [
					'type' => 'string',
					'default' => 'sportsradar',
				],
			],
			'render_callback' => [ $this, 'render_standings_block' ],
		] );

		// Sports Schedule Block
		register_block_type( 'newspack-sports/schedule', [
			'api_version' => 2,
			'title' => __( 'Sports Schedule', 'newspack-sports' ),
			'category' => 'sports',
			'icon' => 'calendar',
			'supports' => [
				'html' => false,
			],
			'attributes' => [
				'sport' => [
					'type' => 'string',
					'default' => '',
				],
				'competition' => [
					'type' => 'string',
					'default' => '',
				],
				'dataSource' => [
					'type' => 'string',
					'default' => 'sportsradar',
				],
				'days' => [
					'type' => 'number',
					'default' => 7,
				],
			],
			'render_callback' => [ $this, 'render_schedule_block' ],
		] );

		// Sports Results Block
		register_block_type( 'newspack-sports/results', [
			'api_version' => 2,
			'title' => __( 'Sports Results', 'newspack-sports' ),
			'category' => 'sports',
			'icon' => 'awards',
			'supports' => [
				'html' => false,
			],
			'attributes' => [
				'sport' => [
					'type' => 'string',
					'default' => '',
				],
				'competition' => [
					'type' => 'string',
					'default' => '',
				],
				'dataSource' => [
					'type' => 'string',
					'default' => 'sportsradar',
				],
				'days' => [
					'type' => 'number',
					'default' => 7,
				],
			],
			'render_callback' => [ $this, 'render_results_block' ],
		] );
	}

	/**
	 * Register data sources with Remote Data Blocks
	 */
	public function register_data_sources( $sources ) {
		$sources['sportsradar'] = [
			'name' => 'Sportsradar',
			'fetch_callback' => [ $this, 'fetch_sportsradar_data' ],
		];

		$sources['statsperform'] = [
			'name' => 'Statsperform',
			'fetch_callback' => [ $this, 'fetch_statsperform_data' ],
		];

		$sources['heimspiel'] = [
			'name' => 'Heimspiel',
			'fetch_callback' => [ $this, 'fetch_heimspiel_data' ],
		];

		return $sources;
	}

	/**
	 * Adjust cache duration based on data type
	 */
	public function adjust_cache_duration( $duration, $data_type ) {
		switch ( $data_type ) {
			case 'live_scores':
				return 60; // 1 minute for live scores
			case 'schedule':
				return 3600; // 1 hour for schedule
			case 'standings':
				return 1800; // 30 minutes for standings
			case 'results':
				return 3600; // 1 hour for results
			default:
				return $duration;
		}
	}

	/**
	 * Fetch data from Sportsradar API
	 */
	public function fetch_sportsradar_data( $params ) {
		$sport = $params['sport'] ?? 'soccer';
		$competition_id = $params['competition_id'] ?? '';
		$data_type = $params['data_type'] ?? 'standings';
		$api_key = $this->get_api_key( 'sportsradar' );

		if ( ! $api_key || ! $competition_id ) {
			return new \WP_Error( 'missing_parameters', __( 'Missing API key or competition ID', 'newspack-sports' ) );
		}

		$endpoint = $this->build_sportsradar_endpoint( $sport, $data_type, $competition_id );
		$url = $this->api_providers['sportsradar']['base_url'] . $endpoint . '?api_key=' . $api_key;

		$response = wp_remote_get( $url, [
			'timeout' => 30,
			'headers' => [
				'Accept' => 'application/json',
			],
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! $data ) {
			return new \WP_Error( 'invalid_response', __( 'Invalid response from Sportsradar API', 'newspack-sports' ) );
		}

		return $this->format_sportsradar_data( $data, $data_type, $sport );
	}

	/**
	 * Build Sportsradar API endpoint
	 */
	private function build_sportsradar_endpoint( $sport, $data_type, $competition_id ) {
		$sport_config = $this->api_providers['sportsradar']['sports'][ $sport ] ?? $this->api_providers['sportsradar']['sports']['soccer'];
		
		$endpoint_template = $sport_config['endpoints'][ $data_type ] ?? $sport_config['endpoints']['standings'];
		
		$replacements = [
			'{access_level}' => $sport_config['access_level'],
			'{version}' => $sport_config['version'],
			'{lang}' => 'en',
			'{tournament_id}' => $competition_id,
		];

		return str_replace( array_keys( $replacements ), array_values( $replacements ), $endpoint_template );
	}

	/**
	 * Format Sportsradar data for display
	 */
	private function format_sportsradar_data( $data, $data_type, $sport ) {
		switch ( $data_type ) {
			case 'standings':
				return $this->format_standings_data( $data );
			case 'schedule':
				return $this->format_schedule_data( $data );
			case 'results':
				return $this->format_results_data( $data );
			default:
				return $data;
		}
	}

	/**
	 * Format standings data
	 */
	private function format_standings_data( $data ) {
		// Example Sportsradar standings structure
		$standings = [];
		
		if ( isset( $data['standings'] ) ) {
			foreach ( $data['standings'] as $standing ) {
				$standings[] = [
					'position' => $standing['rank'] ?? 0,
					'team' => $standing['team']['name'] ?? '',
					'team_id' => $standing['team']['id'] ?? '',
					'played' => $standing['games_played'] ?? 0,
					'won' => $standing['wins'] ?? 0,
					'drawn' => $standing['draws'] ?? 0,
					'lost' => $standing['losses'] ?? 0,
					'goals_for' => $standing['goals_for'] ?? 0,
					'goals_against' => $standing['goals_against'] ?? 0,
					'goal_difference' => $standing['goal_difference'] ?? 0,
					'points' => $standing['points'] ?? 0,
				];
			}
		}

		return [
			'standings' => $standings,
			'last_updated' => current_time( 'mysql' ),
		];
	}

	/**
	 * Format schedule data
	 */
	private function format_schedule_data( $data ) {
		$matches = [];
		
		if ( isset( $data['sport_events'] ) ) {
			foreach ( $data['sport_events'] as $event ) {
				$matches[] = [
					'id' => $event['id'] ?? '',
					'date' => $event['scheduled'] ?? '',
					'home_team' => $event['competitors'][0]['name'] ?? '',
					'away_team' => $event['competitors'][1]['name'] ?? '',
					'venue' => $event['venue']['name'] ?? '',
					'round' => $event['tournament_round']['number'] ?? '',
				];
			}
		}

		return [
			'schedule' => $matches,
			'last_updated' => current_time( 'mysql' ),
		];
	}

	/**
	 * Format results data
	 */
	private function format_results_data( $data ) {
		$results = [];
		
		if ( isset( $data['results'] ) ) {
			foreach ( $data['results'] as $result ) {
				$results[] = [
					'id' => $result['sport_event']['id'] ?? '',
					'date' => $result['sport_event']['scheduled'] ?? '',
					'home_team' => $result['sport_event']['competitors'][0]['name'] ?? '',
					'away_team' => $result['sport_event']['competitors'][1]['name'] ?? '',
					'home_score' => $result['home_score'] ?? 0,
					'away_score' => $result['away_score'] ?? 0,
					'status' => $result['sport_event_status']['status'] ?? 'completed',
				];
			}
		}

		return [
			'results' => $results,
			'last_updated' => current_time( 'mysql' ),
		];
	}

	/**
	 * Fetch data from Statsperform API
	 */
	public function fetch_statsperform_data( $params ) {
		// Statsperform implementation would go here
		// Similar structure to Sportsradar but with Statsperform API specifics
		return [
			'data' => [],
			'message' => 'Statsperform integration not yet implemented',
			'last_updated' => current_time( 'mysql' ),
		];
	}

	/**
	 * Fetch data from Heimspiel API
	 */
	public function fetch_heimspiel_data( $params ) {
		// Heimspiel implementation would go here
		// Similar structure to Sportsradar but with Heimspiel API specifics
		return [
			'data' => [],
			'message' => 'Heimspiel integration not yet implemented',
			'last_updated' => current_time( 'mysql' ),
		];
	}

	/**
	 * Get API key from settings
	 */
	private function get_api_key( $provider ) {
		return get_option( "newspack_sports_{$provider}_api_key", '' );
	}

	/**
	 * Render standings block
	 */
	public function render_standings_block( $attributes ) {
		$sport = $attributes['sport'] ?? '';
		$competition = $attributes['competition'] ?? '';
		$data_source = $attributes['dataSource'] ?? 'sportsradar';

		$competition_id = $this->get_competition_api_id( $competition, $sport );
		
		if ( ! $competition_id ) {
			return '<div class="sports-data-error">' . __( 'No competition ID configured for this sport.', 'newspack-sports' ) . '</div>';
		}

		ob_start();
		?>
		<div class="wp-block-newspack-sports-standings sports-data-block" 
			data-sport="<?php echo esc_attr( $sport ); ?>"
			data-competition="<?php echo esc_attr( $competition_id ); ?>"
			data-data-source="<?php echo esc_attr( $data_source ); ?>"
			data-data-type="standings">
			
			<!-- wp:remotedatablocks/remote-data -->
			<div class="wp-block-remotedatablocks-remote-data" 
				data-source="<?php echo esc_attr( $data_source ); ?>"
				data-endpoint=""
				data-params='<?php echo wp_json_encode( [
					'sport' => $sport,
					'competition_id' => $competition_id,
					'data_type' => 'standings'
				] ); ?>'>
				
				<div class="sports-data-loading">
					<p><?php _e( 'Loading standings...', 'newspack-sports' ); ?></p>
				</div>
				
				<div class="sports-data-error" style="display: none;">
					<p><?php _e( 'Error loading standings data.', 'newspack-sports' ); ?></p>
				</div>
			</div>
			<!-- /wp:remotedatablocks/remote-data -->
			
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render schedule block
	 */
	public function render_schedule_block( $attributes ) {
		$sport = $attributes['sport'] ?? '';
		$competition = $attributes['competition'] ?? '';
		$data_source = $attributes['dataSource'] ?? 'sportsradar';
		$days = $attributes['days'] ?? 7;

		$competition_id = $this->get_competition_api_id( $competition, $sport );
		
		if ( ! $competition_id ) {
			return '<div class="sports-data-error">' . __( 'No competition ID configured for this sport.', 'newspack-sports' ) . '</div>';
		}

		ob_start();
		?>
		<div class="wp-block-newspack-sports-schedule sports-data-block" 
			data-sport="<?php echo esc_attr( $sport ); ?>"
			data-competition="<?php echo esc_attr( $competition_id ); ?>"
			data-data-source="<?php echo esc_attr( $data_source ); ?>"
			data-data-type="schedule"
			data-days="<?php echo esc_attr( $days ); ?>">
			
			<!-- wp:remotedatablocks/remote-data -->
			<div class="wp-block-remotedatablocks-remote-data" 
				data-source="<?php echo esc_attr( $data_source ); ?>"
				data-endpoint=""
				data-params='<?php echo wp_json_encode( [
					'sport' => $sport,
					'competition_id' => $competition_id,
					'data_type' => 'schedule',
					'days' => $days
				] ); ?>'>
				
				<div class="sports-data-loading">
					<p><?php _e( 'Loading schedule...', 'newspack-sports' ); ?></p>
				</div>
				
				<div class="sports-data-error" style="display: none;">
					<p><?php _e( 'Error loading schedule data.', 'newspack-sports' ); ?></p>
				</div>
			</div>
			<!-- /wp:remotedatablocks/remote-data -->
			
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Render results block
	 */
	public function render_results_block( $attributes ) {
		$sport = $attributes['sport'] ?? '';
		$competition = $attributes['competition'] ?? '';
		$data_source = $attributes['dataSource'] ?? 'sportsradar';
		$days = $attributes['days'] ?? 7;

		$competition_id = $this->get_competition_api_id( $competition, $sport );
		
		if ( ! $competition_id ) {
			return '<div class="sports-data-error">' . __( 'No competition ID configured for this sport.', 'newspack-sports' ) . '</div>';
		}

		ob_start();
		?>
		<div class="wp-block-newspack-sports-results sports-data-block" 
			data-sport="<?php echo esc_attr( $sport ); ?>"
			data-competition="<?php echo esc_attr( $competition_id ); ?>"
			data-data-source="<?php echo esc_attr( $data_source ); ?>"
			data-data-type="results"
			data-days="<?php echo esc_attr( $days ); ?>">
			
			<!-- wp:remotedatablocks/remote-data -->
			<div class="wp-block-remotedatablocks-remote-data" 
				data-source="<?php echo esc_attr( $data_source ); ?>"
				data-endpoint=""
				data-params='<?php echo wp_json_encode( [
					'sport' => $sport,
					'competition_id' => $competition_id,
					'data_type' => 'results',
					'days' => $days
				] ); ?>'>
				
				<div class="sports-data-loading">
					<p><?php _e( 'Loading results...', 'newspack-sports' ); ?></p>
				</div>
				
				<div class="sports-data-error" style="display: none;">
					<p><?php _e( 'Error loading results data.', 'newspack-sports' ); ?></p>
				</div>
			</div>
			<!-- /wp:remotedatablocks/remote-data -->
			
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get competition API ID from term meta
	 */
	private function get_competition_api_id( $competition_slug, $sport_slug ) {
		if ( $competition_slug ) {
			$term = get_term_by( 'slug', $competition_slug, 'competition' );
			if ( $term ) {
				return get_term_meta( $term->term_id, 'sports_api_id', true );
			}
		}
		
		if ( $sport_slug ) {
			$term = get_term_by( 'slug', $sport_slug, 'sport' );
			if ( $term ) {
				return get_term_meta( $term->term_id, 'sports_api_id', true );
			}
		}

		return '';
	}

	/**
	 * Get available data sources for a sport
	 */
	public function get_available_data_sources( $sport ) {
		$sources = [];
		
		foreach ( $this->api_providers as $provider_key => $provider ) {
			if ( isset( $provider['sports'][ $sport ] ) ) {
				$sources[ $provider_key ] = $provider['name'];
			}
		}
		
		return $sources;
	}
}