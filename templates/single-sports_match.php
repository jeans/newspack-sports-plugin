<?php
/**
 * Single Sports Match Template
 *
 * @package Newspack_Sports
 */

get_header();

while ( have_posts() ) :
	the_post();

	$match_date = get_post_meta( get_the_ID(), 'match_date', true );
	$match_time = get_post_meta( get_the_ID(), 'match_time', true );
	$home_team_id = get_post_meta( get_the_ID(), 'home_team', true );
	$away_team_id = get_post_meta( get_the_ID(), 'away_team', true );
	$home_score = get_post_meta( get_the_ID(), 'home_score', true );
	$away_score = get_post_meta( get_the_ID(), 'away_score', true );
	$home_team = $home_team_id ? get_post( $home_team_id ) : null;
	$away_team = $away_team_id ? get_post( $away_team_id ) : null;
	?>

	<main id="primary" class="site-main single-sports-match">
		<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
			<header class="entry-header match-header">
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
					<span><?php _e( 'Match', 'newspack-sports' ); ?></span>
				</nav>

				<h1 class="entry-title match-title">
					<?php if ( $home_team && $away_team ) : ?>
						<span class="home-team"><?php echo esc_html( $home_team->post_title ); ?></span>
						<span class="vs-separator"> vs </span>
						<span class="away-team"><?php echo esc_html( $away_team->post_title ); ?></span>
					<?php else : ?>
						<?php the_title(); ?>
					<?php endif; ?>
				</h1>

				<div class="match-meta">
					<?php if ( $match_date ) : ?>
						<div class="match-date">
							<strong><?php _e( 'Date:', 'newspack-sports' ); ?></strong>
							<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $match_date ) ) ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $match_time ) : ?>
						<div class="match-time">
							<strong><?php _e( 'Time:', 'newspack-sports' ); ?></strong>
							<?php echo esc_html( $match_time ); ?>
						</div>
					<?php endif; ?>

					<?php if ( $home_score !== '' && $away_score !== '' ) : ?>
						<div class="match-score">
							<strong><?php _e( 'Score:', 'newspack-sports' ); ?></strong>
							<span class="home-score"><?php echo esc_html( $home_score ); ?></span>
							<span class="score-separator"> - </span>
							<span class="away-score"><?php echo esc_html( $away_score ); ?></span>
						</div>
					<?php endif; ?>
				</div>
			</header>

			<?php if ( $home_team && $away_team ) : ?>
				<div class="match-teams">
					<div class="team home-team">
						<?php if ( has_post_thumbnail( $home_team_id ) ) : ?>
							<div class="team-logo">
								<?php echo get_the_post_thumbnail( $home_team_id, 'medium' ); ?>
							</div>
						<?php endif; ?>
						<h3 class="team-name"><?php echo esc_html( $home_team->post_title ); ?></h3>
					</div>

					<div class="match-vs">
						<span class="vs-text">VS</span>
						<?php if ( $home_score !== '' && $away_score !== '' ) : ?>
							<div class="match-result">
								<span class="score"><?php echo esc_html( $home_score ); ?> - <?php echo esc_html( $away_score ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="team away-team">
						<?php if ( has_post_thumbnail( $away_team_id ) ) : ?>
							<div class="team-logo">
								<?php echo get_the_post_thumbnail( $away_team_id, 'medium' ); ?>
							</div>
						<?php endif; ?>
						<h3 class="team-name"><?php echo esc_html( $away_team->post_title ); ?></h3>
					</div>
				</div>
			<?php endif; ?>

			<div class="entry-content match-content">
				<?php the_content(); ?>
			</div>

			<footer class="entry-footer match-footer">
				<?php
				// Display match statistics if available
				$match_stats = get_post_meta( get_the_ID(), 'match_statistics', true );
				if ( $match_stats ) :
					?>
					<div class="match-statistics">
						<h3><?php _e( 'Match Statistics', 'newspack-sports' ); ?></h3>
						<div class="stats-grid">
							<?php foreach ( $match_stats as $stat => $value ) : ?>
								<div class="stat-item">
									<strong><?php echo esc_html( $stat ); ?>:</strong>
									<span><?php echo esc_html( $value ); ?></span>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>
			</footer>
		</article>
	</main>

	<?php
endwhile;

get_footer();