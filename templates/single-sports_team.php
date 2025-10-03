<?php
/**
 * Single Sports Team Template
 *
 * @package Newspack_Sports
 */

get_header();

while ( have_posts() ) :
	the_post();

	$team_players = get_posts( [
		'post_type' => 'sports_person',
		'posts_per_page' => -1,
		'tax_query' => [
			[
				'taxonomy' => 'sports_team_tax',
				'field' => 'slug',
				'terms' => get_post_field( 'post_name', get_the_ID() )
			]
		],
		'orderby' => 'meta_value_num',
		'meta_key' => 'person_number',
		'order' => 'ASC'
	] );
	?>

	<main id="primary" class="site-main single-sports-team">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header team-header">
				<?php
				// Breadcrumb
				$sports = wp_get_post_terms( get_the_ID(), 'sport' );
				$competitions = wp_get_post_terms( get_the_ID(), 'competition' );
				?>
				
				<nav class="sports-breadcrumb">
					<?php if ( ! empty( $sports ) ) : ?>
						<a href="<?php echo get_term_link( $sports[0] ); ?>"><?php echo esc_html( $sports[0]->name ); ?></a>
						<?php if ( ! empty( $competitions ) ) : ?>
							<span class="breadcrumb-separator">/</span>
							<a href="<?php echo get_term_link( $competitions[0] ); ?>"><?php echo esc_html( $competitions[0]->name ); ?></a>
						<?php endif; ?>
						<span class="breadcrumb-separator">/</span>
					<?php endif; ?>
					<a href="<?php echo Newspack_Sports\Core\Template_Handler::get_tab_url( 'teams' ); ?>"><?php _e( 'Teams', 'newspack-sports' ); ?></a>
					<span class="breadcrumb-separator">/</span>
					<span><?php the_title(); ?></span>
				</nav>

				<div class="team-header-content">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="team-logo">
							<?php the_post_thumbnail( 'large' ); ?>
						</div>
					<?php endif; ?>

					<div class="team-info">
						<h1 class="entry-title team-name"><?php the_title(); ?></h1>
						
						<?php
						$team_meta = get_post_meta( get_the_ID() );
						if ( ! empty( $team_meta['team_founded'][0] ) ) :
							?>
							<div class="team-founded">
								<strong><?php _e( 'Founded:', 'newspack-sports' ); ?></strong>
								<?php echo esc_html( $team_meta['team_founded'][0] ); ?>
							</div>
							<?php
						endif;

						if ( ! empty( $team_meta['team_stadium'][0] ) ) :
							?>
							<div class="team-stadium">
								<strong><?php _e( 'Stadium:', 'newspack-sports' ); ?></strong>
								<?php echo esc_html( $team_meta['team_stadium'][0] ); ?>
							</div>
							<?php
						endif;
						?>
					</div>
				</div>
			</header>

			<div class="entry-content team-content">
				<?php the_content(); ?>
			</div>

			<?php if ( ! empty( $team_players ) ) : ?>
				<section class="team-players">
					<h2><?php _e( 'Team Players', 'newspack-sports' ); ?></h2>
					
					<div class="players-grid">
						<?php foreach ( $team_players as $player ) : 
							$player_meta = get_post_meta( $player->ID );
							?>
							<article class="player-card">
								<?php if ( has_post_thumbnail( $player->ID ) ) : ?>
									<div class="player-photo">
										<a href="<?php echo get_permalink( $player->ID ); ?>">
											<?php echo get_the_post_thumbnail( $player->ID, 'thumbnail' ); ?>
										</a>
									</div>
								<?php endif; ?>
								
								<div class="player-info">
									<h3 class="player-name">
										<a href="<?php echo get_permalink( $player->ID ); ?>">
											<?php echo esc_html( $player->post_title ); ?>
										</a>
									</h3>
									
									<div class="player-meta">
										<?php if ( ! empty( $player_meta['person_number'][0] ) ) : ?>
											<span class="player-number">#<?php echo esc_html( $player_meta['person_number'][0] ); ?></span>
										<?php endif; ?>
										
										<?php if ( ! empty( $player_meta['person_position'][0] ) ) : ?>
											<span class="player-position"><?php echo esc_html( $player_meta['person_position'][0] ); ?></span>
										<?php endif; ?>
									</div>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php
			// Display upcoming matches for this team
			$upcoming_matches = get_posts( [
				'post_type' => 'sports_match',
				'posts_per_page' => 5,
				'meta_query' => [
					'relation' => 'OR',
					[
						'key' => 'home_team',
						'value' => get_the_ID()
					],
					[
						'key' => 'away_team',
						'value' => get_the_ID()
					]
				],
				'meta_key' => 'match_date',
				'orderby' => 'meta_value',
				'order' => 'ASC',
				'meta_type' => 'DATE'
			] );

			if ( ! empty( $upcoming_matches ) ) :
				?>
				<section class="team-upcoming-matches">
					<h2><?php _e( 'Upcoming Matches', 'newspack-sports' ); ?></h2>
					
					<div class="matches-list">
						<?php foreach ( $upcoming_matches as $match ) : 
							$match_date = get_post_meta( $match->ID, 'match_date', true );
							$match_time = get_post_meta( $match->ID, 'match_time', true );
							$home_team_id = get_post_meta( $match->ID, 'home_team', true );
							$away_team_id = get_post_meta( $match->ID, 'away_team', true );
							$home_team = $home_team_id ? get_post( $home_team_id ) : null;
							$away_team = $away_team_id ? get_post( $away_team_id ) : null;
							?>
							<article class="match-item">
								<div class="match-date">
									<?php echo esc_html( date_i18n( 'M j', strtotime( $match_date ) ) ); ?>
									<?php if ( $match_time ) : ?>
										<br><small><?php echo esc_html( $match_time ); ?></small>
									<?php endif; ?>
								</div>
								
								<div class="match-teams">
									<?php if ( $home_team && $away_team ) : ?>
										<span class="home-team <?php echo $home_team->ID === get_the_ID() ? 'current-team' : ''; ?>">
											<?php echo esc_html( $home_team->post_title ); ?>
										</span>
										<span class="vs"> vs </span>
										<span class="away-team <?php echo $away_team->ID === get_the_ID() ? 'current-team' : ''; ?>">
											<?php echo esc_html( $away_team->post_title ); ?>
										</span>
									<?php else : ?>
										<span><?php echo esc_html( $match->post_title ); ?></span>
									<?php endif; ?>
								</div>
								
								<div class="match-link">
									<a href="<?php echo get_permalink( $match->ID ); ?>"><?php _e( 'View', 'newspack-sports' ); ?></a>
								</div>
							</article>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>
		</article>
	</main>

	<?php
endwhile;

get_footer();