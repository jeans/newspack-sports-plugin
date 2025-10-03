<?php
namespace Newspack_Sports\Core;

class Sports_Taxonomy {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'register_taxonomies' ] );
	}

	public function register_taxonomies() {
		// Main Sport taxonomy
		register_taxonomy( 'sport', [ 'post', 'sports_match', 'sports_team', 'sports_person' ], [
			'labels' => [
				'name'              => __( 'Sports', 'newspack-sports' ),
				'singular_name'     => __( 'Sport', 'newspack-sports' ),
				'search_items'      => __( 'Search Sports', 'newspack-sports' ),
				'all_items'         => __( 'All Sports', 'newspack-sports' ),
				'parent_item'       => __( 'Parent Sport', 'newspack-sports' ),
				'parent_item_colon' => __( 'Parent Sport:', 'newspack-sports' ),
				'edit_item'         => __( 'Edit Sport', 'newspack-sports' ),
				'update_item'       => __( 'Update Sport', 'newspack-sports' ),
				'add_new_item'      => __( 'Add New Sport', 'newspack-sports' ),
				'new_item_name'     => __( 'New Sport Name', 'newspack-sports' ),
				'menu_name'         => __( 'Sports', 'newspack-sports' ),
			],
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [
				'slug'         => 'sport',
				'hierarchical' => true,
				'with_front'   => false,
			],
		] );

		// Competition taxonomy
		register_taxonomy( 'competition', [ 'post', 'sports_match', 'sports_team', 'sports_person' ], [
			'labels' => [
				'name'              => __( 'Competitions', 'newspack-sports' ),
				'singular_name'     => __( 'Competition', 'newspack-sports' ),
				'search_items'      => __( 'Search Competitions', 'newspack-sports' ),
				'all_items'         => __( 'All Competitions', 'newspack-sports' ),
				'parent_item'       => __( 'Parent Competition', 'newspack-sports' ),
				'parent_item_colon' => __( 'Parent Competition:', 'newspack-sports' ),
				'edit_item'         => __( 'Edit Competition', 'newspack-sports' ),
				'update_item'       => __( 'Update Competition', 'newspack-sports' ),
				'add_new_item'      => __( 'Add New Competition', 'newspack-sports' ),
				'new_item_name'     => __( 'New Competition Name', 'newspack-sports' ),
				'menu_name'         => __( 'Competitions', 'newspack-sports' ),
			],
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [
				'slug'         => 'competition',
				'hierarchical' => true,
				'with_front'   => false,
			],
		] );

		// Team taxonomy for persons
		register_taxonomy( 'sports_team_tax', [ 'sports_person' ], [
			'labels' => [
				'name'              => __( 'Teams', 'newspack-sports' ),
				'singular_name'     => __( 'Team', 'newspack-sports' ),
				'search_items'      => __( 'Search Teams', 'newspack-sports' ),
				'all_items'         => __( 'All Teams', 'newspack-sports' ),
				'parent_item'       => __( 'Parent Team', 'newspack-sports' ),
				'parent_item_colon' => __( 'Parent Team:', 'newspack-sports' ),
				'edit_item'         => __( 'Edit Team', 'newspack-sports' ),
				'update_item'       => __( 'Update Team', 'newspack-sports' ),
				'add_new_item'      => __( 'Add New Team', 'newspack-sports' ),
				'new_item_name'     => __( 'New Team Name', 'newspack-sports' ),
				'menu_name'         => __( 'Teams', 'newspack-sports' ),
			],
			'hierarchical'      => true,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => [
				'slug'         => 'team',
				'hierarchical' => true,
				'with_front'   => false,
			],
		] );
	}
}