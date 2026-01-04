<?php
/**
 * Компонент: Таблица событий игрока
 * 
 * Используемые переменные (передаются через get_template_part):
 * - $player_events (массив) - события игрока
 * - $selected_tournament_name (строка) - название турнира
 * - $position_code (строка) - код позиции (G, D, M, F)
 * 
 * @package Arsenal
 */

// Убеждаемся что переменные доступны
if ( ! isset( $args ) ) {
	$args = array();
}

$player_events = isset( $args['player_events'] ) ? $args['player_events'] : ( isset( $player_events ) ? $player_events : array() );
$selected_tournament_name = isset( $args['selected_tournament_name'] ) ? $args['selected_tournament_name'] : ( isset( $selected_tournament_name ) ? $selected_tournament_name : '' );
$position_code = isset( $args['position_code'] ) ? $args['position_code'] : ( isset( $position_code ) ? $position_code : 'M' );

// Убеждаемся что $player_events это массив
if ( ! is_array( $player_events ) ) {
	$player_events = array();
}
?>

<div class="recent-matches-section" data-player-events-section>
	<h2 class="section-title">
		<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
		События турнира <?php echo esc_html( $selected_tournament_name ); ?>
	</h2>
	<?php if ( ! empty( $player_events ) ) : ?>
		<div class="matches-wrapper" data-matches-table>
			<table class="matches-table">
				<thead>
					<tr>
						<th>Дата</th>
						<th>Матч</th>
						<th>Счёт</th>
						<th>Минут</th>
						<th>Голы</th>
						<th>Ассисты</th>
						<th>ЖК</th>
						<th>КК</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $player_events as $match ) : ?>
						<tr>
							<td data-label="Дата"><?php echo esc_html( date( 'd.m.Y', strtotime( $match->match_date ) ) ); ?></td>
							<td data-label="Матч">
								<a href="<?php echo esc_url( home_url( '/match/' . $match->home_team_id . '/' . date( 'Y-m-d', strtotime( $match->match_date ) ) . '/' ) ); ?>" class="match-link">
									<?php echo esc_html( $match->home_team . ' - ' . $match->away_team ); ?>
								</a>
							</td>
							<td data-label="Счёт" class="match-score">
								<?php 
								if ( $match->home_score !== null && $match->away_score !== null ) {
									echo esc_html( $match->home_score . ':' . $match->away_score );
								} else {
									echo '—';
								}
								?>
							</td>
							<td data-label="Минут"><?php echo esc_html( $match->minutes_played ); ?></td>
							<td data-label="Голы"><?php echo $match->goals ? '⚽ ' . esc_html( $match->goals ) : '—'; ?></td>
							<td data-label="Ассисты"><?php echo $match->assists ? '👟 ' . esc_html( $match->assists ) : '—'; ?></td>
							<td data-label="ЖК"><?php echo $match->yellow_cards ? '🟨 ' . esc_html( $match->yellow_cards ) : '—'; ?></td>
							<td data-label="КК"><?php echo $match->red_cards ? '🟥 ' . esc_html( $match->red_cards ) : '—'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<button class="toggle-matches-btn" data-toggle-matches>
			<span class="toggle-text">Показать все матчи</span>
			<svg class="toggle-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
				<path d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
			</svg>
		</button>
	<?php else : ?>
		<p class="no-stats-message">📊 Нет данных о матчах игрока в этом турнире.</p>
	<?php endif; ?>
</div>
