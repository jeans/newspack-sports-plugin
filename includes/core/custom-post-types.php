<?php
namespace Newspack_Sports\Core;

class Custom_Post_Types {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'init', [ $this, 'register_post_types' ] );
		add_action( 'init', [ $this, 'register_meta_fields' ] );
	}

	public function register_post_types() {
		// Sports Match CPT
		register_post_type( 'sports_match', [
			'labels' => [
				'name'                  => __( 'Matches', 'newspack-sports' ),
				'singular_name'         => __( 'Match', 'newspack-sports' ),
				'add_new'               => __( 'Add New Match', 'newspack-sports' ),
				'add_new_item'          => __( 'Add New Match', 'newspack-sports' ),
				'edit_item'             => __( 'Edit Match', 'newspack-sports' ),
				'new_item'              => __( 'New Match', 'newspack-sports' ),
				'view_item'             => __( 'View Match', 'newspack-sports' ),
				'view_items'            => __( 'View Matches', 'newspack-sports' ),
				'search_items'          => __( 'Search Matches', 'newspack-sports' ),
				'not_found'             => __( 'No matches found', 'newspack-sports' ),
				'not_found_in_trash'    => __( 'No matches found in Trash', 'newspack-sports' ),
				'all_items'             => __( 'All Matches', 'newspack-sports' ),
				'archives'              => __( 'Match Archives', 'newspack-sports' ),
				'attributes'            => __( 'Match Attributes', 'newspack-sports' ),
				'insert_into_item'      => __( 'Insert into match', 'newspack-sports' ),
				'uploaded_to_this_item' => __( 'Uploaded to this match', 'newspack-sports' ),
				'filter_items_list'     => __( 'Filter matches list', 'newspack-sports' ),
				'items_list_navigation' => __( 'Matches list navigation', 'newspack-sports' ),
				'items_list'            => __( 'Matches list', 'newspack-sports' ),
			],
			'public'              => true,
			'has_archive'         => true,
			'show_in_rest'        => true,
			'menu_position'       => 5,
			'menu_icon'           => 'dashicons-awards',
			'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'rewrite'             => [ 'slug' => 'matches' ],
			'taxonomies'          => [ 'sport', 'competition' ],
		] );

		// Sports Team CPT
		register_post_type( 'sports_team', [
			'labels' => [
				'name'                  => __( 'Teams', 'newspack-sports' ),
				'singular_name'         => __( 'Team', 'newspack-sports' ),
				'add_new'               => __( 'Add New Team', 'newspack-sports' ),
				'add_new_item'          => __( 'Add New Team', 'newspack-sports' ),
				'edit_item'             => __( 'Edit Team', 'newspack-sports' ),
				'new_item'              => __( 'New Team', 'newspack-sports' ),
				'view_item'             => __( 'View Team', 'newspack-sports' ),
				'view_items'            => __( 'View Teams', 'newspack-sports' ),
				'search_items'          => __( 'Search Teams', 'newspack-sports' ),
				'not_found'             => __( 'No teams found', 'newspack-sports' ),
				'not_found_in_trash'    => __( 'No teams found in Trash', 'newspack-sports' ),
				'all_items'             => __( 'All Teams', 'newspack-sports' ),
				'archives'              => __( 'Team Archives', 'newspack-sports' ),
				'attributes'            => __( 'Team Attributes', 'newspack-sports' ),
				'insert_into_item'      => __( 'Insert into team', 'newspack-sports' ),
				'uploaded_to_this_item' => __( 'Uploaded to this team', 'newspack-sports' ),
				'filter_items_list'     => __( 'Filter teams list', 'newspack-sports' ),
				'items_list_navigation' => __( 'Teams list navigation', 'newspack-sports' ),
				'items_list'            => __( 'Teams list', 'newspack-sports' ),
			],
			'public'              => true,
			'has_archive'         => true,
			'show_in_rest'        => true,
			'menu_position'       => 6,
			'menu_icon'           => 'dashicons-groups',
			'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'rewrite'             => [ 'slug' => 'teams' ],
			'taxonomies'          => [ 'sport', 'competition' ],
		] );

		// Sports Person CPT
		register_post_type( 'sports_person', [
			'labels' => [
				'name'                  => __( 'Persons', 'newspack-sports' ),
				'singular_name'         => __( 'Person', 'newspack-sports' ),
				'add_new'               => __( 'Add New Person', 'newspack-sports' ),
				'add_new_item'          => __( 'Add New Person', 'newspack-sports' ),
				'edit_item'             => __( 'Edit Person', 'newspack-sports' ),
				'new_item'              => __( 'New Person', 'newspack-sports' ),
				'view_item'             => __( 'View Person', 'newspack-sports' ),
				'view_items'            => __( 'View Persons', 'newspack-sports' ),
				'search_items'          => __( 'Search Persons', 'newspack-sports' ),
				'not_found'             => __( 'No persons found', 'newspack-sports' ),
				'not_found_in_trash'    => __( 'No persons found in Trash', 'newspack-sports' ),
				'all_items'             => __( 'All Persons', 'newspack-sports' ),
				'archives'              => __( 'Person Archives', 'newspack-sports' ),
				'attributes'            => __( 'Person Attributes', 'newspack-sports' ),
				'insert_into_item'      => __( 'Insert into person', 'newspack-sports' ),
				'uploaded_to_this_item' => __( 'Uploaded to this person', 'newspack-sports' ),
				'filter_items_list'     => __( 'Filter persons list', 'newspack-sports' ),
				'items_list_navigation' => __( 'Persons list navigation', 'newspack-sports' ),
				'items_list'            => __( 'Persons list', 'newspack-sports' ),
			],
			'public'              => true,
			'has_archive'         => true,
			'show_in_rest'        => true,
			'menu_position'       => 7,
			'menu_icon'           => 'dashicons-id',
			'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'rewrite'             => [ 'slug' => 'persons' ],
			'taxonomies'          => [ 'sport', 'competition', 'sports_team_tax' ],
		] );
	}

	public function register_meta_fields() {
		// Match meta fields
		register_post_meta( 'sports_match', 'match_date', [
			'type'         => 'string',
			'show_in_rest' => true,
			'single'       => true,
		] );

		register_post_meta( 'sports_match', 'match_time', [
			'type'         => 'string',
			'show_in_rest' => true,
			'single'       => true,
		] );

		register_post_meta( 'sports_match', 'home_team', [
			'type'         => 'integer',
			'show_in_rest' => true,
			'single'       => true,
		] );

		register_post_meta( 'sports_match', 'away_team', [
			'type'         => 'integer',
			'show_in_rest' => true,
			'single'       => true,
		] );

		register_post_meta( 'sports_match', 'home_score', [
			'type'         => 'integer',
			'show_in_rest' => true,
			'single'       => true,
		] );

		register_post_meta( 'sports_match', 'away_score', [
			'type'         => 'integer',
			'show_in_rest' => true,
			'single'       => true,
		] );

		// Person meta fields
		register_post_meta( 'sports_person', 'person_position', [
			'type'         => 'string',
			'show_in_rest' => true,
			'single'       => true,
		] );

		register_post_meta( 'sports_person', 'person_number', [
			'type'         => 'integer',
			'show_in_rest' => true,
			'single'       => true,
		] );

		register_post_meta( 'sports_person', 'person_birthdate', [
			'type'         => 'string',
			'show_in_rest' => true,
			'single'       => true,
		] );
	}
}