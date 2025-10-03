<?php
namespace Newspack_Sports\Core;

class Template_Handler {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_filter( 'query_vars', [ $this, 'add_query_vars' ] );
		add_filter( 'template_include', [ $this, 'handle_sports_templates' ] );
		add_action( 'template_redirect', [ $this, 'handle_tabbed_archives' ] );
		
		// Add body classes for sports templates
		add_filter( 'body_class', [ $this, 'add_body_classes' ] );
	}

	/**
	 * Add custom rewrite rules for sports URLs
	 */
	public function add_rewrite_rules() {
		// Sport + Competition + Tab
		// sport1.de/handball/handball-em/news
		// sport1.de/handball/handball-em/tabelle
		add_rewrite_rule(
			'^sport/([^/]+)/([^/]+)/(news|table|results|schedule|teams|players)/?$',
			'index.php?sport=$matches[1]&competition=$matches[2]&sports_tab=$matches[3]',
			'top'
		);

		// Sport level with tab (no specific competition)
		// sport1.de/handball/news
		// sport1.de/handball/table
		add_rewrite_rule(
			'^sport/([^/]+)/(news|table|results|schedule|teams|players)/?$',
			'index.php?sport=$matches[1]&sports_tab=$matches[2]',
			'top'
		);

		// Competition archive without tab (defaults to news)
		// sport1.de/handball/handball-em
		add_rewrite_rule(
			'^sport/([^/]+)/([^/]+)/?$',
			'index.php?sport=$matches[1]&competition=$matches[2]',
			'top'
		);

		// Sport archive without tab (defaults to news)
		// sport1.de/handball
		add_rewrite_rule(
			'^sport/([^/]+)/?$',
			'index.php?sport=$matches[1]',
			'top'
		);

		// Custom post type archives with sport/competition context
		add_rewrite_rule(
			'^sport/([^/]+)/([^/]+)/matches/([^/]+)/?$',
			'index.php?sport=$matches[1]&competition=$matches[2]&sports_match=$matches[3]',
			'top'
		);

		add_rewrite_rule(
			'^sport/([^/]+)/([^/]+)/teams/([^/]+)/?$',
			'index.php?sport=$matches[1]&competition=$matches[2]&sports_team=$matches[3]',
			'top'
		);

		add_rewrite_rule(
			'^sport/([^/]+)/([^/]+)/players/([^/]+)/?$',
			'index.php?sport=$matches[1]&competition=$matches[2]&sports_person=$matches[3]',
			'top'
		);
	}

	/**
	 * Add custom query variables
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'sports_tab';
		$vars[] = 'competition';
		$vars[] = 'sport';
		return $vars;
	}

	/**
	 * Handle template loading for sports pages
	 */
	public function handle_sports_templates( $template ) {
		$sport = get_query_var( 'sport' );
		$competition = get_query_var( 'competition' );
		$sports_tab = get_query_var( 'sports_tab' );

		// If we're on a sports-related page
		if ( $sport || $competition || $sports_tab ) {
			$new_template = $this->locate_sports_template( $sport, $competition, $sports_tab );
			if ( $new_template ) {
				return $new_template;
			}
		}

		// Handle custom post type single templates with sports context
		if ( is_singular( 'sports_match' ) || is_singular( 'sports_team' ) || is_singular( 'sports_person' ) ) {
			$new_template = $this->locate_single_sports_template();
			if ( $new_template ) {
				return $new_template;
			}
		}

		return $template;
	}

	/**
	 * Locate the appropriate sports template
	 */
	private function locate_sports_template( $sport, $competition, $tab ) {
		$templates = [];

		// Build template hierarchy based on URL structure
		if ( $competition && $tab ) {
			// sport/handball/handball-em/news
			$templates[] = "sport-{$sport}-competition-{$competition}-tab-{$tab}.php";
			$templates[] = "sport-competition-tab-{$tab}.php";
			$templates[] = "sport-{$sport}-tab-{$tab}.php";
			$templates[] = "sport-tab-{$tab}.php";
			$templates[] = "sport-{$sport}-competition.php";
			$templates[] = "sport-competition.php";
		} elseif ( $competition ) {
			// sport/handball/handball-em (default to news tab)
			$templates[] = "sport-{$sport}-competition-{$competition}.php";
			$templates[] = "sport-competition.php";
		} elseif ( $tab ) {
			// sport/handball/news
			$templates[] = "sport-{$sport}-tab-{$tab}.php";
			$templates[] = "sport-tab-{$tab}.php";
			$templates[] = "sport-{$sport}.php";
		} elseif ( $sport ) {
			// sport/handball
			$templates[] = "sport-{$sport}.php";
		}

		// Always fall back to general sport archive
		$templates[] = 'sport-archive.php';
		$templates[] = 'taxonomy-sport.php';
		$templates[] = 'archive.php';

		return $this->locate_template( $templates );
	}

	/**
	 * Locate single sports post type templates
	 */
	private function locate_single_sports_template() {
		$post_type = get_post_type();
		$templates = [];

		switch ( $post_type ) {
			case 'sports_match':
				$templates[] = 'single-sports-match.php';
				$templates[] = 'single-sports_match.php';
				break;
			case 'sports_team':
				$templates[] = 'single-sports-team.php';
				$templates[] = 'single-sports_team.php';
				break;
			case 'sports_person':
				$templates[] = 'single-sports-person.php';
				$templates[] = 'single-sports_person.php';
				break;
		}

		$templates[] = 'single.php';

		return $this->locate_template( $templates );
	}

	/**
	 * Locate template in plugin and theme directories
	 */
	private function locate_template( $templates ) {
		// First check in theme directory
		$located = locate_template( $templates, false, false );

		if ( $located ) {
			return $located;
		}

		// Then check in plugin templates directory
		foreach ( $templates as $template ) {
			$plugin_template = NEWSPACK_SPORTS_PLUGIN_PATH . 'templates/' . $template;
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return false;
	}

	/**
	 * Handle tabbed archive functionality
	 */
	public function handle_tabbed_archives() {
		if ( ! is_tax( 'sport' ) && ! is_tax( 'competition' ) ) {
			return;
		}

		$sport = get_query_var( 'sport' );
		$competition = get_query_var( 'competition' );
		$tab = get_query_var( 'sports_tab' ) ?: 'news';

		// Modify query based on tab
		switch ( $tab ) {
			case 'news':
				// Default archive behavior - show posts
				break;

			case 'table':
			case 'results':
			case 'schedule':
				// These will be handled by custom templates with remote data
				// For now, set 404 if no remote data setup
				if ( ! $this->has_remote_data( $sport, $competition, $tab ) ) {
					// We'll still show the template but with placeholder
				}
				break;

			case 'teams':
				// Show teams archive
				if ( $competition ) {
					$this->set_teams_archive_query( $sport, $competition );
				} else {
					$this->set_teams_archive_query( $sport );
				}
				break;

			case 'players':
				// Show players archive
				if ( $competition ) {
					$this->set_players_archive_query( $sport, $competition );
				} else {
					$this->set_players_archive_query( $sport );
				}
				break;
		}
	}

	/**
	 * Check if remote data is available for this sport/competition
	 */
	private function has_remote_data( $sport, $competition, $tab ) {
		// This will be integrated with Sports_Data_Manager
		$term = $competition ? get_term_by( 'slug', $competition, 'competition' ) : get_term_by( 'slug', $sport, 'sport' );
		
		if ( $term && get_term_meta( $term->term_id, 'sports_api_id', true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Modify query for teams archive
	 */
	private function set_teams_archive_query( $sport, $competition = null ) {
		global $wp_query;

		$tax_query = [
			[
				'taxonomy' => 'sport',
				'field'    => 'slug',
				'terms'    => $sport,
			]
		];

		if ( $competition ) {
			$tax_query[] = [
				'taxonomy' => 'competition',
				'field'    => 'slug',
				'terms'    => $competition,
			];
		}

		$wp_query->set( 'post_type', 'sports_team' );
		$wp_query->set( 'tax_query', $tax_query );
		$wp_query->set( 'posts_per_page', 50 );
	}

	/**
	 * Modify query for players archive
	 */
	private function set_players_archive_query( $sport, $competition = null ) {
		global $wp_query;

		$tax_query = [
			[
				'taxonomy' => 'sport',
				'field'    => 'slug',
				'terms'    => $sport,
			]
		];

		if ( $competition ) {
			$tax_query[] = [
				'taxonomy' => 'competition',
				'field'    => 'slug',
				'terms'    => $competition,
			];
		}

		$wp_query->set( 'post_type', 'sports_person' );
		$wp_query->set( 'tax_query', $tax_query );
		$wp_query->set( 'posts_per_page', 100 );
		$wp_query->set( 'orderby', 'meta_value_num' );
		$wp_query->set( 'meta_key', 'person_number' );
		$wp_query->set( 'order', 'ASC' );
	}

	/**
	 * Add body classes for sports templates
	 */
	public function add_body_classes( $classes ) {
		$sport = get_query_var( 'sport' );
		$competition = get_query_var( 'competition' );
		$tab = get_query_var( 'sports_tab' );

		if ( $sport ) {
			$classes[] = 'sports-page';
			$classes[] = 'sport-' . sanitize_html_class( $sport );
		}

		if ( $competition ) {
			$classes[] = 'competition-page';
			$classes[] = 'competition-' . sanitize_html_class( $competition );
		}

		if ( $tab ) {
			$classes[] = 'sports-tab-' . sanitize_html_class( $tab );
		}

		if ( is_singular( 'sports_match' ) ) {
			$classes[] = 'sports-match-single';
		}

		if ( is_singular( 'sports_team' ) ) {
			$classes[] = 'sports-team-single';
		}

		if ( is_singular( 'sports_person' ) ) {
			$classes[] = 'sports-person-single';
		}

		return $classes;
	}

	/**
	 * Get current sports context for use in templates
	 */
	public static function get_sports_context() {
		$context = [
			'sport' => get_query_var( 'sport' ),
			'competition' => get_query_var( 'competition' ),
			'tab' => get_query_var( 'sports_tab' ) ?: 'news',
		];

		if ( $context['sport'] ) {
			$context['sport_term'] = get_term_by( 'slug', $context['sport'], 'sport' );
		}

		if ( $context['competition'] ) {
			$context['competition_term'] = get_term_by( 'slug', $context['competition'], 'competition' );
		}

		return $context;
	}

	/**
	 * Get available tabs for current sports context
	 */
	public static function get_available_tabs() {
		$context = self::get_sports_context();
		$tabs = [
			'news' => [
				'label' => __( 'News', 'newspack-sports' ),
				'url' => self::get_tab_url( 'news' ),
			],
			'table' => [
				'label' => __( 'Table', 'newspack-sports' ),
				'url' => self::get_tab_url( 'table' ),
			],
			'results' => [
				'label' => __( 'Results', 'newspack-sports' ),
				'url' => self::get_tab_url( 'results' ),
			],
			'schedule' => [
				'label' => __( 'Schedule', 'newspack-sports' ),
				'url' => self::get_tab_url( 'schedule' ),
			],
			'teams' => [
				'label' => __( 'Teams', 'newspack-sports' ),
				'url' => self::get_tab_url( 'teams' ),
			],
			'players' => [
				'label' => __( 'Players', 'newspack-sports' ),
				'url' => self::get_tab_url( 'players' ),
			],
		];

		return apply_filters( 'newspack_sports_available_tabs', $tabs, $context );
	}

	/**
	 * Get URL for a specific tab
	 */
	public static function get_tab_url( $tab ) {
		$context = self::get_sports_context();
		$base_url = '';

		if ( $context['competition'] ) {
			$base_url = home_url( "/sport/{$context['sport']}/{$context['competition']}" );
		} elseif ( $context['sport'] ) {
			$base_url = home_url( "/sport/{$context['sport']}" );
		} else {
			return '';
		}

		if ( $tab === 'news' ) {
			return $base_url . '/';
		}

		return $base_url . '/' . $tab . '/';
	}

	/**
	 * Check if current tab is active
	 */
	public static function is_active_tab( $tab ) {
		$context = self::get_sports_context();
		return $context['tab'] === $tab;
	}
}