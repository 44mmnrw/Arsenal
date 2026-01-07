<?php
/**
 * Template part: Players Grid
 *
 * Сетка карточек игроков с сортировкой по позициям
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

// Запрос: все игроки Арсенала с активными контрактами
$sql = "SELECT p.*, pos.name as position_name, pos.id as position_id_numeric
	FROM {$wpdb->prefix}arsenal_players p
	LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
	INNER JOIN {$wpdb->prefix}arsenal_team_contracts tc ON p.player_id = tc.player_id
	WHERE tc.squad_id = %s
	ORDER BY pos.id ASC, p.shirt_number ASC";

$players = $wpdb->get_results( $wpdb->prepare( $sql, '21F3D7B3' ) );

// Группируем игроков по позициям
$players_by_position = array();
foreach ( $players as $player ) {
	$position = ! empty( $player->position_name ) ? $player->position_name : 'Неизвестная позиция';
	if ( ! isset( $players_by_position[ $position ] ) ) {
		$players_by_position[ $position ] = array();
	}
	$players_by_position[ $position ][] = $player;
}

?>

<section class="teams-section">
	<div class="container">
		<div class="teams-header">
			<h1 class="teams-title">Команды</h1>
		</div>

		<?php if ( ! empty( $players ) ) : ?>
			<div class="teams-content">
				<?php
				foreach ( $players_by_position as $position_name => $position_players ) :
				?>
					<div class="position-group">
						<h2 class="position-heading"><?php echo esc_html( $position_name ); ?></h2>
						
						<div class="players-grid">
							<?php foreach ( $position_players as $player ) : 
								// Получаем данные игрока
								$photo_url = ! empty( $player->photo_url ) ? $player->photo_url : get_template_directory_uri() . '/assets/images/player-placeholder.png';
								$position = ! empty( $player->position_name ) ? $player->position_name : 'Не указана';
								$shirt_number = ! empty( $player->shirt_number ) ? intval( $player->shirt_number ) : null;
								$name_display = ! empty( $player->full_name ) ? $player->full_name : 'Неизвестно';
								
								// Вычисляем возраст
								$age = '';
								if ( ! empty( $player->birth_date ) ) {
									try {
										$birth_date = new DateTime( $player->birth_date );
										$today = new DateTime();
										$age = $today->diff( $birth_date )->y;
									} catch ( Exception $e ) {
										$age = '';
									}
								}
								
								// Получаем статистику игрока текущего сезона
								$current_year = intval( get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) ) );
								
								// Получаем первый доступный турнир текущего года
								$first_tournament = $wpdb->get_var( $wpdb->prepare(
									"SELECT tournament_id FROM {$wpdb->prefix}arsenal_matches 
									 WHERE YEAR(match_date) = %d
									 LIMIT 1",
									$current_year
								) );
								
								if ( ! $first_tournament ) {
									$first_tournament = '71CFDAA6'; // По умолчанию
								}
								
								// Используем функцию arsenal_get_player_stats из inc/player-functions.php
								if ( function_exists( 'arsenal_get_player_stats' ) && ! empty( $player->player_id ) ) {
									$player_stats = arsenal_get_player_stats( $player->player_id, $first_tournament, $current_year );
									
									$matches_count = ( $player_stats && ! empty( $player_stats->matches_played ) ) ? intval( $player_stats->matches_played ) : 0;
									$goals_count = ( $player_stats && ! empty( $player_stats->goals ) ) ? intval( $player_stats->goals ) : 0;
									$assists_count = ( $player_stats && ! empty( $player_stats->assists ) ) ? intval( $player_stats->assists ) : 0;
								} else {
									// Fallback если функция не определена или player_id пустой
									$matches_count = 0;
									$goals_count = 0;
									$assists_count = 0;
								}
								
								// Параметры для ссылки
								$player_url = function_exists( 'arsenal_get_player_url' ) ? arsenal_get_player_url( $player->id ) : '#';
							?>
								<a href="<?php echo esc_url( $player_url ); ?>" class="player-card" title="<?php echo esc_attr( $name_display ); ?>">
									<!-- Левая колонка 50%: Фото + Номер -->
									<div class="player-card__photo">
										<img 
											src="<?php echo esc_url( $photo_url ); ?>" 
											alt="<?php echo esc_attr( $name_display ); ?>"
											class="player-card__image"
											loading="lazy"
										>
										<?php if ( $shirt_number ) : ?>
											<div class="player-card__number">
												<?php echo esc_html( $shirt_number ); ?>
											</div>
										<?php endif; ?>
									</div>

									<!-- Правая колонка 50%: Информация -->
									<div class="player-card__info">
										<h3 class="player-card__name"><?php echo esc_html( $name_display ); ?></h3>
										<p class="player-card__position"><?php echo esc_html( $position ); ?></p>
										<?php if ( $age ) : ?>
										<p class="player-card__meta"><?php echo esc_html( arsenal_pluralize_years( $age ) ); ?> • Беларусь</p>
										<?php endif; ?>
									</div>

									<!-- Нижняя строка 100%: Статистика -->
									<div class="player-card__stats">
										<div class="player-stat">
											<span class="player-stat__label">Матчи</span>
											<span class="player-stat__value"><?php echo esc_html( $matches_count ); ?></span>
										</div>
										<div class="player-stat">
											<span class="player-stat__label">Голы</span>
											<span class="player-stat__value"><?php echo esc_html( $goals_count ); ?></span>
										</div>
										<div class="player-stat">
											<span class="player-stat__label">Пасы</span>
											<span class="player-stat__value"><?php echo esc_html( $assists_count ); ?></span>
										</div>
									</div>
								</a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php
				endforeach;
				?>
			</div>
		<?php else : ?>
			<p class="no-players-message">Игроки основного состава не найдены</p>
		<?php endif; ?>
	</div>
</section>
