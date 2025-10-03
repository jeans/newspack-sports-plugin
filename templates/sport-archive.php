<?php
/**
 * Sport Archive Template
 *
 * @package Newspack_Sports
 */

get_header();

$sports_context = Newspack_Sports\Core\Template_Handler::get_sports_context();
$available_tabs = Newspack_Sports\Core\Template_Handler::get_available_tabs();
$current_tab = $sports_context['tab'];
?>

<main id="primary" class="site-main sports-archive sport-<?php echo esc_attr( $sports_context['sport'] ); ?>">
	
	<?php get_template_part( 'templates/parts/sport-header', null, $sports_context ); ?>
	
	<?php get_template_part( 'templates/parts/sport-tabs', null, [ 
		'tabs' => $available_tabs, 
		'current_tab' => $current_tab 
	] ); ?>

	<div class="sports-content sports-tab-<?php echo esc_attr( $current_tab ); ?>">
		<?php
		switch ( $current_tab ) {
			case 'news':
				$this->display_news_tab( $sports_context );
				break;
				
			case 'table':
				$this->display_table_tab( $sports_context );
				break;
				
			case 'results':
				$this->display_results_tab( $sports_context );
				break;
				
			case 'schedule':
				$this->display_schedule_tab( $sports_context );
				break;
				
			case 'teams':
				$this->display_teams_tab( $sports_context );
				break;
				
			case 'players':
				$this->display_players_tab( $sports_context );
				break;
				
			default:
				$this->display_news_tab( $sports_context );
				break;
		}
		?>
	</div>

</main>

<?php
get_footer();