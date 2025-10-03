<?php
/**
 * Sport Header Template Part
 *
 * @package Newspack_Sports
 */

$context = $args;
$sport_term = $context['sport_term'] ?? null;
$competition_term = $context['competition_term'] ?? null;
?>

<header class="sports-page-header">
	<div class="sports-breadcrumb">
		<nav aria-label="<?php esc_attr_e( 'Sports breadcrumb', 'newspack-sports' ); ?>">
			<a href="<?php echo home_url( '/sport/' ); ?>"><?php _e( 'Sports', 'newspack-sports' ); ?></a>
			
			<?php if ( $sport_term ) : ?>
				<span class="breadcrumb-separator">/</span>
				<a href="<?php echo get_term_link( $sport_term ); ?>"><?php echo esc_html( $sport_term->name ); ?></a>
			<?php endif; ?>
			
			<?php if ( $competition_term ) : ?>
				<span class="breadcrumb-separator">/</span>
				<a href="<?php echo get_term_link( $competition_term ); ?>"><?php echo esc_html( $competition_term->name ); ?></a>
			<?php endif; ?>
		</nav>
	</div>

	<div class="sports-title-section">
		<?php if ( $sport_term ) : ?>
			<h1 class="sports-title">
				<?php
				if ( $competition_term ) {
					printf( 
						'%s <span class="sports-subtitle">%s</span>',
						esc_html( $sport_term->name ),
						esc_html( $competition_term->name )
					);
				} else {
					echo esc_html( $sport_term->name );
				}
				?>
			</h1>
		<?php endif; ?>

		<?php
		// Display sport icon if available
		if ( $sport_term && $sport_icon = get_term_meta( $sport_term->term_id, 'sports_icon', true ) ) :
			?>
			<div class="sport-icon">
				<img src="<?php echo esc_url( $sport_icon ); ?>" alt="<?php echo esc_attr( $sport_term->name ); ?>" />
			</div>
			<?php
		endif;
		?>
	</div>

	<?php if ( $sport_term && $sport_term->description ) : ?>
		<div class="sport-description">
			<?php echo wp_kses_post( $sport_term->description ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $competition_term && $competition_term->description ) : ?>
		<div class="competition-description">
			<?php echo wp_kses_post( $competition_term->description ); ?>
		</div>
	<?php endif; ?>
</header>