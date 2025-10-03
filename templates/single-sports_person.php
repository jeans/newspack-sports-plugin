<?php
/**
 * Single Sports Person Template
 *
 * @package Newspack_Sports
 */

get_header();

while ( have_posts() ) :
	the_post();

	$person_meta = get_post_meta( get_the_ID() );
	$person_teams = wp_get_post_terms( get_the_ID(), 'sports_team_tax' );
	?>

	<main id="primary" class="site-main single-sports-person">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header person-header">
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
					<a href="<?php echo Newspack_Sports\Core\Template_Handler::get_tab_url( 'players' ); ?>"><?php _e( 'Players', 'newspack-sports' ); ?></a>
					<span class="breadcrumb-separator">/</span>
					<span><?php the_title(); ?></span>
				</nav>

				<div class="person-header-content">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="person-photo">
							<?php the_post_thumbnail( 'large' ); ?>
						</div>
					<?php endif; ?>

					<div class="person-info">
						<h1 class="entry-title person-name"><?php the_title(); ?></h1>
						
						<div class="person-meta-grid">
							<?php if ( ! empty( $person_meta['person_number'][0] ) ) : ?>
								<div class="person-meta-item">
									<strong><?php _e( 'Number:', 'newspack-sports' ); ?></strong>
									<span class="person-number">#<?php echo esc_html( $person_meta['person_number'][0] ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $person_meta['person_position'][0] ) ) : ?>
								<div class="person-meta-item">
									<strong><?php _e( 'Position:', 'newspack-sports' ); ?></strong>
									<span class="person-position"><?php echo esc_html( $person_meta['person_position'][0] ); ?></span>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $person_meta['person_birthdate'][0] ) ) : ?>
								<div class="person-meta-item">
									<strong><?php _e( 'Birth Date:', 'newspack-sports' ); ?></strong>
									<span class="person-birthdate">
										<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $person_meta['person_birthdate'][0] ) ) ); ?>
									</span>
								</div>
							<?php endif; ?>

							<?php if ( ! empty( $person_teams ) ) : ?>
								<div class="person-meta-item">
									<strong><?php _e( 'Team:', 'newspack-sports' ); ?></strong>
									<span class="person-team">
										<?php
										$team_links = [];
										foreach ( $person_teams as $team ) {
											$team_links[] = '<a href="' . get_term_link( $team ) . '">' . esc_html( $team->name ) . '</a>';
										}
										echo implode( ', ', $team_links );
										?>
									</span>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</header>

			<div class="entry-content person-content">
				<?php the_content(); ?>
			</div>

			<?php
			// Display player statistics if available
			$player_stats = get_post_meta( get_the_ID(), 'player_statistics', true );
			if ( $player_stats && is_array( $player_stats ) ) :
				?>
				<section class="player-statistics">
					<h2><?php _e( 'Player Statistics', 'newspack-sports' ); ?></h2>
					
					<div class="stats-grid">
						<?php foreach ( $player_stats as $stat => $value ) : ?>
							<div class="stat-item">
								<strong><?php echo esc_html( $stat ); ?>:</strong>
								<span><?php echo esc_html( $value ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endif; ?>

			<?php
			// Display recent matches for this player
			$recent_matches = get_posts( [
				'post_type' => 'sports_match',
				'posts_per_page' => 10,
				'meta_query' => [
					[
						'key' => 'match_players',
						'value' => '"' . get_the_ID() . '"',
						'compare' => 'LIKE'
					]
				],
				'meta_key' => 'match_date',
				'orderby' => 'meta_value',
				'order' => 'DESC',
				'meta_type' => 'DATE'
			] );

			if ( ! empty( $recent_matches ) ) :
				?>
				<section class="player-recent-matches">
					<h2><?php _e( 'Recent Matches', 'newspack-sports' ); ?></h2>
					
					<div class="matches-list">
						<?php foreach ( $recent_matches as $match ) : 
							$match_date = get_post_meta( $match->ID, 'match_date', true );
							$home_team_id = get_post_meta( $match->ID, 'home_team', true );
							$away_team_id = get_post_meta( $match->ID, 'away_team', true );
							$home_score = get_post_meta( $match->ID, 'home_score', true );
							$away_score = get_post_meta( $match->ID, 'away_score', true );
							$home_team = $home_team_id ? get_post( $home_team_id ) : null;
							$away_team = $away_team_id ? get_post( $away_team_id ) : null;
							?>
							<article class="match-item">
								<div class="match-date">
									<?php echo esc_html( date_i18n( 'M j, Y', strtotime( $match_date ) ) ); ?>
								</div>
								
								<div class="match-teams">
									<?php if ( $home_team && $away_team ) : ?>
										<span class="home-team"><?php echo esc_html( $home_team->post_title ); ?></span>
										<span class="score">
											<?php echo esc_html( $home_score ); ?> - <?php echo esc_html( $away_score ); ?>
										</span>
										<span class="away-team"><?php echo esc_html( $away_team->post_title ); ?></span>
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