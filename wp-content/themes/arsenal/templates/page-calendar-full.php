<?php
/**
 * Template Name: Full Calendar
 * Description: Displays full calendar with all rounds and matches for the season
 * 
 * Полный календарь матчей сезона с разбивкой по турам
 *
 * Назначение:
 * - Отображение полного календаря всех матчей сезона
 * - Группировка матчей по турам (раундам)
 * - Показ результатов завершённых матчей и времени предстоящих
 * - Отображение эмблем и названий команд
 * - Информация о площадке проведения матча
 *
 * Использование:
 * - Присвоить этот шаблон странице "Календарь" в админ-панели WordPress
 * - Шаблон автоматически загружается при доступе к странице
 *
 * Отображаемые элементы:
 * - Заголовок "Календарь матчей сезона YYYY"
 * - Раунды, сгруппированные по турам
 * - Для каждого матча:
 *   * Дата и время (или результат)
 *   * Статус матча (Победа/Ничья/Поражение/Предстоит)
 *   * Логотип и название домашней команды
 *   * Счёт или время матча
 *   * Логотип и название гостевой команды
 *   * Место проведения (если указано)
 *
 * Стили:
 * - CSS класс: .calendar-section
 * - Контейнер: .calendar-container
 * - Группа тура: .round-group
 * - Заголовок тура: .round-header, .round-title
 * - Матчи в туре: .round-matches
 * - Карточка матча: .match-card, .match-upcoming (для не сыгранных)
 * - Команда: .team-block (.home-team, .away-team)
 * - Счёт: .match-score, .score
 * - Бейдж статуса: .match-badge (.badge-win, .badge-draw, .badge-loss, .badge-scheduled)
 *
 * Зависимости:
 * - глобальная переменная $wpdb для запросов в БД
 * - arsenal_icon() функция для иконок (локация)
 * - get_header() / get_footer() для оборачивания
 *
 * История версий:
 * - v1.0 (04.01.2026) - Переношение из components в templates
 * - Обновлены пути и структура для работы как полного шаблона страницы
 * - Добавлены заголовки и более подробная документация
 *
 * @package Arsenal
 * @since 1.0.0
 * @link https://www.figma.com/design/3PDLTXxabQeweijDRr6x3E/Arsenal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Enqueue calendar styles if they exist
wp_enqueue_style( 'arsenal-calendar', get_template_directory_uri() . '/assets/css/page-calendar-full.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );

global $wpdb;

// Получаем активный год сезона из настроек плагина (wp_options)
$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );

// team_id команды Арсенал (используем team_id, а не id)
$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
if ( ! $arsenal_team_id ) {
	$arsenal_team_id = '915703'; // Fallback ID
}

// Получаем все матчи Арсенала на активный год сезона, сгруппированные по турам
$matches = $wpdb->get_results( $wpdb->prepare( "
	SELECT 
		m.*,
		ht.name as home_team,
		ht.logo_url as home_logo,
		at.name as away_team,
		at.logo_url as away_logo,
		st.name as venue
	FROM wp_arsenal_matches m
	LEFT JOIN wp_arsenal_teams ht ON m.home_team_id = ht.team_id
	LEFT JOIN wp_arsenal_teams at ON m.away_team_id = at.team_id
	LEFT JOIN wp_arsenal_stadiums st ON m.stadium_id = st.stadium_id
	WHERE (m.home_team_id = %s OR m.away_team_id = %s)
		AND YEAR(m.match_date) = %d
	ORDER BY m.tour ASC, m.match_date ASC
", $arsenal_team_id, $arsenal_team_id, $active_season_year ) );

// Функция определения результата
function arsenal_match_result_calendar( $match, $arsenal_team_id ) {
	if ( $match->home_team_id == $arsenal_team_id ) {
		// Арсенал дома
		if ( $match->home_score > $match->away_score ) return 'win';
		if ( $match->home_score < $match->away_score ) return 'loss';
		return 'draw';
	} else {
		// Арсенал в гостях
		if ( $match->away_score > $match->home_score ) return 'win';
		if ( $match->away_score < $match->home_score ) return 'loss';
		return 'draw';
	}
}

// Функция получения логотипа команды
function arsenal_get_logo_url_calendar( $url ) {
	if ( ! empty( $url ) ) {
		return $url;
	}
	return get_template_directory_uri() . '/assets/images/placeholder-logo.png';
}

// Группируем матчи по турам
$matches_by_round = array();
foreach ( $matches as $match ) {
	$round = ! empty( $match->tour ) ? intval( $match->tour ) : 0;
	if ( ! isset( $matches_by_round[ $round ] ) ) {
		$matches_by_round[ $round ] = array();
	}
	$matches_by_round[ $round ][] = $match;
}

// Сортируем по номеру тура (по возрастанию)
ksort( $matches_by_round );

// Определяем, есть ли матчи вообще
$has_matches = ! empty( $matches_by_round );
?>

<main class="calendar-page">
	<section class="calendar-section">
		<div class="container">
			<div class="calendar-header">
			<h1 class="calendar-title">Календарь матчей сезона <?php echo esc_html( $active_season_year ); ?></h1>

			<?php if ( $has_matches ) : ?>
				<div class="calendar-container">
					<?php foreach ( $matches_by_round as $round => $round_matches ) : ?>
						<div class="calendar-round-group">
							<div class="calendar-round-matches">
								<?php foreach ( $round_matches as $match ) :
									// Статус матча (используем home_score и away_score)
									$has_result = ! is_null( $match->home_score ) && ! is_null( $match->away_score );
									$result = $has_result ? arsenal_match_result_calendar( $match, $arsenal_team_id ) : null;
									$badge_class = $has_result ? 'calendar-badge-' . $result : 'calendar-badge-scheduled';
									
									// Дополнительные классы для цветового выделения логотипов
									$match_card_class = 'calendar-match-card';
									if ( $has_result ) {
										// Определяем результат для каждой команды
										if ( $match->home_team_id === $arsenal_team_id ) {
											// Логика для домашней команды
											$match_card_class .= ' calendar-match-' . $result;
										} else {
											// Логика для гостевой команды
											$match_card_class .= ' calendar-match-away-' . $result;
										}
									}

									// Текст бейджа
									if ( $has_result ) {
										if ( $result === 'win' ) {
											$badge_text = 'Победа';
										} elseif ( $result === 'draw' ) {
											$badge_text = 'Ничья';
										} else {
											$badge_text = 'Поражение';
										}
									} else {
										$badge_text = 'Предстоит';
									}

									// Форматирование даты
									$formatted_date = wp_date( 'j M', strtotime( $match->match_date ) );
									$formatted_time = wp_date( 'H:i', strtotime( $match->match_date ) );
								?>
									<article class="<?php echo esc_attr( $match_card_class ); ?><?php echo $has_result ? '' : ' calendar-match-upcoming'; ?>">
										<!-- Шапка: бейджи слева, дата справа -->
										<div class="calendar-match-header">
											<div class="calendar-match-badges">
												<span class="calendar-round-badge">Тур <?php echo esc_html( $round ); ?></span>
												<span class="calendar-match-badge <?php echo esc_attr( $badge_class ); ?>">
													<?php echo esc_html( $badge_text ); ?>
												</span>
											</div>
											<div class="calendar-match-datetime">
												<?php echo esc_html( $formatted_date ); ?> • <?php echo esc_html( $formatted_time ); ?>
											</div>
										</div>

										<!-- Основная часть: команды вертикально -->
										<div class="calendar-match-body">
											<!-- Названия команд -->
											<div class="calendar-teams-names">
												<span class="calendar-home-team-name"><?php echo esc_html( $match->home_team ); ?></span>
												<span class="calendar-vs-text">vs</span>
												<span class="calendar-away-team-name"><?php echo esc_html( $match->away_team ); ?></span>
											</div>

											<!-- Логотипы и счёт -->
											<div class="calendar-match-center">
												<div class="calendar-team-logo calendar-home-logo">
													<img 
														src="<?php echo esc_url( arsenal_get_logo_url_calendar( $match->home_logo ) ); ?>" 
														alt="<?php echo esc_attr( $match->home_team ); ?>"
														loading="lazy"
													>
												</div>

												<div class="calendar-match-score">
													<?php if ( $has_result ) : ?>
														<span class="calendar-score-value"><?php echo esc_html( $match->home_score ); ?> : <?php echo esc_html( $match->away_score ); ?></span>
													<?php else : ?>
														<span class="calendar-score-dash">—</span>
													<?php endif; ?>
												</div>

												<div class="calendar-team-logo calendar-away-logo">
													<img 
														src="<?php echo esc_url( arsenal_get_logo_url_calendar( $match->away_logo ) ); ?>" 
														alt="<?php echo esc_attr( $match->away_team ); ?>"
														loading="lazy"
													>
												</div>
											</div>
										</div>

										<!-- Подвал: стадион -->
										<?php if ( ! empty( $match->venue ) ) : ?>
										<div class="calendar-match-footer">
											<span class="calendar-match-stadium"><?php echo esc_html( $match->venue ); ?></span>
										</div>
										<?php endif; ?>
									</article>
								<?php endforeach; ?>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="calendar-empty">
					<p>Матчи на сезон <?php echo esc_html( $active_season_year ); ?> не найдены</p>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
