<?php
/**
 * Функции для страницы статистики игрока
 * Работают с новой структурой БД Arsenal
 */

/**
 * Получить данные игрока по ID
 */
function arsenal_get_player_data( $player_id ) {
	global $wpdb;
	
	// Игрок может быть идентифицирован по id (число) или player_id (строка)
	// Пробуем по player_id сначала, потом по id
	$player = $wpdb->get_row( $wpdb->prepare(
		"SELECT p.id, p.player_id, p.full_name, p.first_name, p.last_name, p.position_id, p.birth_date, p.citizenship, p.height_cm, p.weight_kg, p.photo_url, p.shirt_number, p.biography, pos.name as position_name 
		 FROM {$wpdb->prefix}arsenal_players p 
		 LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
		 WHERE p.player_id = %s LIMIT 1",
		sanitize_text_field( $player_id )
	) );
	
	// Если не найден по player_id, пробуем по числовому id
	if ( ! $player && is_numeric( $player_id ) ) {
		$player = $wpdb->get_row( $wpdb->prepare(
			"SELECT p.id, p.player_id, p.full_name, p.first_name, p.last_name, p.position_id, p.birth_date, p.citizenship, p.height_cm, p.weight_kg, p.photo_url, p.shirt_number, p.biography, pos.name as position_name 
			 FROM {$wpdb->prefix}arsenal_players p 
			 LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
			 WHERE p.id = %d LIMIT 1",
			intval( $player_id )
		) );
	}
	
	return $player;
}

/**
 * Получить список доступных турниров для игрока за ВСЕ годы
 */
function arsenal_get_player_seasons( $player_id ) {
	global $wpdb;
	
	$seasons = $wpdb->get_results( $wpdb->prepare(
		"SELECT DISTINCT t.tournament_id, t.name as tournament_name
		 FROM {$wpdb->prefix}arsenal_tournaments t
		 INNER JOIN {$wpdb->prefix}arsenal_matches m ON m.tournament_id = t.tournament_id
		 INNER JOIN {$wpdb->prefix}arsenal_match_lineups ml ON ml.match_id = m.match_id
		 WHERE ml.player_id = %s
		 GROUP BY t.tournament_id
		 ORDER BY t.id DESC",
		$player_id
	) );
	
	return $seasons;
}

/**
 * Получить статистику игрока по tournament_id + году
 * ШАГ 1: Берем ВСЕ match_id турнира за год
 * ШАГ 2: Ищем события игрока в этих матчах
 */
function arsenal_get_player_stats( $player_id, $tournament_id = null, $year = null ) {
	global $wpdb;
	
	// ШАГ 1: Получаем ВСЕ match_id матчей турнира за текущий год
	$match_ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT match_id FROM {$wpdb->prefix}arsenal_matches 
		 WHERE tournament_id = %s AND YEAR(match_date) = %d",
		$tournament_id,
		$year
	) );
	
	if ( empty( $match_ids ) ) {
		return (object) array(
			'matches_played' => 0,
			'matches_started' => 0,
			'minutes_played' => 0,
			'goals' => 0,
			'assists' => 0,
			'yellow_cards' => 0,
			'red_cards' => 0
		);
	}
	
	// ШАГ 2: Ищем матчи и минуты игрока в этих матчах
	$placeholders = implode( ',', array_fill( 0, count( $match_ids ), '%s' ) );
	$params = array_merge( array( $player_id ), $match_ids );
	
	$lineups = $wpdb->get_row( $wpdb->prepare(
		"SELECT 
			COUNT(*) as matches_played,
			SUM(CASE WHEN is_starting = 1 THEN 1 ELSE 0 END) as matches_started,
			SUM(CASE WHEN is_starting = 1 THEN 90 ELSE 0 END) as minutes_played
		 FROM {$wpdb->prefix}arsenal_match_lineups
		 WHERE player_id = %s AND match_id IN ($placeholders)",
		$params
	) );
	
	// ШАГ 3: Вычисляем реальные минуты на основе подстановок
	// Для каждого матча где был игрок в lineups, ищем события sub_out/sub_in
	$params_events = array_merge( array( $player_id ), $match_ids );
	
	$minutes_data = $wpdb->get_results( $wpdb->prepare(
		"SELECT 
			ml.match_id,
			ml.is_starting,
			MIN(CASE WHEN me.event_type = '8A3000CC' THEN me.minute END) as sub_out,
			MAX(CASE WHEN me.event_type = 'F6804FE9' THEN me.minute END) as sub_in
		 FROM {$wpdb->prefix}arsenal_match_lineups ml
		 LEFT JOIN {$wpdb->prefix}arsenal_match_events me ON ml.match_id = me.match_id AND me.player_id = ml.player_id
		 WHERE ml.player_id = %s AND ml.match_id IN ($placeholders)
		 GROUP BY ml.match_id",
		$params_events
	) );
	
	// Считаем минуты для каждого матча
	$total_minutes = 0;
	foreach ($minutes_data as $m) {
		// Если вышел из игры
		if ($m->sub_out) {
			$total_minutes += $m->sub_out;
		} 
		// Если вошел в игру (играл с этой минуты до конца)
		elseif ($m->sub_in) {
			$total_minutes += (90 - $m->sub_in);
		}
		// Если ни вышел ни вошел
		else {
			// Если был в стартовом составе - играл 90 минут
			if ($m->is_starting) {
				$total_minutes += 90;
			}
			// Иначе не выходил вообще - 0 минут
			else {
				$total_minutes += 0;
			}
		}
	}
	
	// Обновляем minutes_played в lineups
	$lineups->minutes_played = $total_minutes;
	
	$events = $wpdb->get_row( $wpdb->prepare(
		"SELECT 
			SUM(CASE WHEN event_type = 'A3898573' THEN 1 ELSE 0 END) as goals,
			SUM(CASE WHEN event_type = 'B44F03A6' THEN 1 ELSE 0 END) as assists,
			SUM(CASE WHEN event_type = '7B83D3F0' THEN 1 ELSE 0 END) as yellow_cards,
			SUM(CASE WHEN event_type = 'FC171553' THEN 1 ELSE 0 END) as red_cards
		 FROM {$wpdb->prefix}arsenal_match_events
		 WHERE player_id = %s AND match_id IN ($placeholders)",
		$params_events
	) );
	
	return (object) array(
		'matches_played' => $lineups->matches_played ?? 0,
		'matches_started' => $lineups->matches_started ?? 0,
		'minutes_played' => $lineups->minutes_played ?? 0,
		'goals' => $events->goals ?? 0,
		'assists' => $events->assists ?? 0,
		'yellow_cards' => $events->yellow_cards ?? 0,
		'red_cards' => $events->red_cards ?? 0,
		'goals_conceded' => null  // Будет переопределено далее для вратарей
	);
}

/**
 * Получить события игрока в конкретном турнире и году
 * ШАГ 1: Берем ВСЕ завершенные матчи турнира за год
 * ШАГ 2: Ищем участие игрока в этих матчах + считаем минуты с учетом подстановок
 */
function arsenal_get_player_events( $player_id, $tournament_id, $year = null ) {
	global $wpdb;
	
	// ШАГ 1: Получаем ВСЕ завершенные match_id турнира за год
	$match_ids = $wpdb->get_col( $wpdb->prepare(
		"SELECT match_id FROM {$wpdb->prefix}arsenal_matches 
		 WHERE tournament_id = %s AND YEAR(match_date) = %d AND status = '0083CE05'
		 ORDER BY match_date DESC",
		$tournament_id,
		$year
	) );
	
	if ( empty( $match_ids ) ) {
		return array();
	}
	
	// ШАГ 2: Для каждого матча проверяем участие игрока и его события
	$placeholders = implode( ',', array_fill( 0, count( $match_ids ), '%s' ) );
	
	$events = $wpdb->get_results( $wpdb->prepare(
		"SELECT 
			m.match_id,
			m.match_date,
			m.home_score,
			m.away_score,
			m.home_team_id,
			m.away_team_id,
			ht.name as home_team,
			at.name as away_team,
			ml.is_starting,
			(SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_events WHERE match_id = m.match_id AND player_id = %s AND event_type = 'A3898573') as goals,
			(SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_events WHERE match_id = m.match_id AND player_id = %s AND event_type = 'B44F03A6') as assists,
			(SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_events WHERE match_id = m.match_id AND player_id = %s AND event_type = '7B83D3F0') as yellow_cards,
			(SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_match_events WHERE match_id = m.match_id AND player_id = %s AND event_type = 'FC171553') as red_cards,
			(SELECT MIN(minute) FROM {$wpdb->prefix}arsenal_match_events WHERE match_id = m.match_id AND player_id = %s AND event_type = '8A3000CC') as sub_out,
			(SELECT MAX(minute) FROM {$wpdb->prefix}arsenal_match_events WHERE match_id = m.match_id AND player_id = %s AND event_type = 'F6804FE9') as sub_in
		 FROM {$wpdb->prefix}arsenal_matches m
		 INNER JOIN {$wpdb->prefix}arsenal_teams ht ON ht.team_id = m.home_team_id
		 INNER JOIN {$wpdb->prefix}arsenal_teams at ON at.team_id = m.away_team_id
		 INNER JOIN {$wpdb->prefix}arsenal_match_lineups ml ON ml.match_id = m.match_id AND ml.player_id = %s
		 WHERE m.match_id IN ($placeholders)
		 ORDER BY m.match_date DESC",
		array_merge( array( $player_id, $player_id, $player_id, $player_id, $player_id, $player_id, $player_id ), $match_ids )
	) );
	
	// ШАГ 3: Для каждого матча рассчитаем минуты
	foreach ( $events as &$match ) {
		if ( $match->sub_out ) {
			$match->minutes_played = $match->sub_out;
		} elseif ( $match->sub_in ) {
			$match->minutes_played = 90 - $match->sub_in;
		} else {
			// Если ни вышел ни вошел
			if ( $match->is_starting ) {
				// Был в стартовом составе - 90 минут
				$match->minutes_played = 90;
			} else {
				// Не выходил вообще - 0 минут
				$match->minutes_played = 0;
			}
		}
	}
	
	return $events;
}

/**
 * Получить position_id игрока (HEX строка из wp_arsenal_positions)
 */
function arsenal_get_player_position( $position_id ) {
	return $position_id;
}

/**
 * Получить позиции на поле (координаты SVG по HEX position_id из БД)
 * 
 * @param string $position_id HEX position_id из wp_arsenal_positions
 * @return array Массив с x и y координатами в процентах
 */
function arsenal_get_field_positions( $position_id = '62C23862' ) {
	return array(
		'A98B3A74' => array( 'x' => 10, 'y' => 50 ),       // Вратарь - левая сторона у ворот
		'6B9B6564' => array( 'x' => 25, 'y' => 50 ),       // Защитник - ближе к воротам
		'62C23862' => array( 'x' => 35, 'y' => 25 ),       // Полузащитник - центр поля (по умолчанию)
		'04AADD4E' => array( 'x' => 80, 'y' => 50 ),       // Нападающий - ближе к чужим воротам
	);
}

/**
 * Получить все данные игрока в одном запросе
 * Объединяет: базовые данные, турниры, статистику, события в один вызов
 * 
 * @param string $player_id ID игрока (HEX строка)
 * @param string $selected_tournament_id ID турнира
 * @param int    $selected_year Год сезона (null = берется из настроек плагина)
 * @return array|false Массив с полными данными или false
 */
function arsenal_get_player_full_data( $player_id, $selected_tournament_id, $selected_year = null ) {
	if ( ! $selected_year ) {
		// Получаем активный год из настроек плагина (wp_options)
		$selected_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );
	}
	
	$stats = arsenal_get_player_stats( $player_id, $selected_tournament_id, $selected_year );
	
	// Получаем позицию игрока для определения, вратарь ли это
	$player_data = arsenal_get_player_data( $player_id );
	$position_code = isset( $player_data->position_id ) ? $player_data->position_id : null;
	
	// Если вратарь (position_code = 'A98B3A74'), считаем пропущенные голы динамически
	if ( $position_code === 'A98B3A74' ) {
		$stats->goals_conceded = arsenal_get_goalkeeper_goals_conceded( $player_id, $selected_tournament_id, $selected_year );
	} else {
		$stats->goals_conceded = 0;  // Для полевых игроков не считаем пропущенные голы
	}
	
	return array(
		'player'              => $player_data,
		'seasons'             => arsenal_get_player_seasons( $player_id ),
		'stats'               => $stats,
		'events'              => arsenal_get_player_events( $player_id, $selected_tournament_id, $selected_year ),
		'years'               => arsenal_get_tournament_years( $selected_tournament_id ),
		'yearly_stats'        => arsenal_get_tournament_yearly_stats( $player_id, $selected_tournament_id ), // Возвращает ВСЕ годы, не только текущий
	);
}

/**
 * Получить тип команды игрока (основа/резерв)
 */
function arsenal_get_player_squad_type( $player_id ) {
	global $wpdb;
	
	$squad_data = $wpdb->get_row( $wpdb->prepare(
		"SELECT s.squad_name FROM {$wpdb->prefix}arsenal_team_contracts tc
		 LEFT JOIN {$wpdb->prefix}arsenal_squad s ON tc.squad_id = s.squad_id
		 WHERE tc.player_id = %s
		 LIMIT 1",
		$player_id
	) );
	
	return $squad_data && ! empty( $squad_data->squad_name ) ? $squad_data->squad_name : 'Основа';
}

/**
 * Получить доступные годы для турнира
 */
function arsenal_get_tournament_years( $tournament_id ) {
	global $wpdb;
	
	return $wpdb->get_results( $wpdb->prepare(
		"SELECT DISTINCT YEAR(match_date) as year
		 FROM {$wpdb->prefix}arsenal_matches
		 WHERE tournament_id = %s
		 ORDER BY year DESC",
		$tournament_id
	) );
}

/**
 * Получить статистику по годам для турнира
 * Используется в таблице "Статистика по годам"
 */
function arsenal_get_tournament_yearly_stats( $player_id, $tournament_id ) {
	global $wpdb;
	
	// Получаем все матчи выбранного турнира
	$all_matches = $wpdb->get_results( $wpdb->prepare(
		"SELECT match_id, YEAR(match_date) as year, match_date
		 FROM {$wpdb->prefix}arsenal_matches
		 WHERE tournament_id = %s
		 ORDER BY match_date DESC",
		$tournament_id
	) );
	
	$years_stats = array();
	
	if ( ! empty( $all_matches ) ) {
		// Группируем match_id по годам
		$matches_by_year = array();
		foreach ( $all_matches as $match ) {
			if ( ! isset( $matches_by_year[$match->year] ) ) {
				$matches_by_year[$match->year] = array();
			}
			$matches_by_year[$match->year][] = $match->match_id;
		}
		
		// Для каждого года получаем статистику
		foreach ( $matches_by_year as $year => $match_ids ) {
			if ( empty( $match_ids ) ) {
				continue;
			}
			
			$placeholders = implode( ',', array_fill( 0, count( $match_ids ), '%s' ) );
			$params_lineups = array_merge( array( $player_id ), $match_ids );
			
			// Получаем составы
			$lineups = $wpdb->get_row( $wpdb->prepare(
				"SELECT 
					COUNT(*) as matches_played
				 FROM {$wpdb->prefix}arsenal_match_lineups
				 WHERE player_id = %s AND match_id IN ($placeholders)",
				$params_lineups
			) );
			
			// Считаем минуты
			$minutes_data = $wpdb->get_results( $wpdb->prepare(
				"SELECT 
					ml.match_id,
					ml.is_starting,
					MIN(CASE WHEN me.event_type = '8A3000CC' THEN me.minute END) as sub_out,
					MAX(CASE WHEN me.event_type = 'F6804FE9' THEN me.minute END) as sub_in
				 FROM {$wpdb->prefix}arsenal_match_lineups ml
				 LEFT JOIN {$wpdb->prefix}arsenal_match_events me ON ml.match_id = me.match_id AND me.player_id = ml.player_id
				 WHERE ml.player_id = %s AND ml.match_id IN ($placeholders)
				 GROUP BY ml.match_id",
				$params_lineups
			) );
			
			// Считаем минуты для каждого матча
			$total_minutes = 0;
			foreach ( $minutes_data as $m ) {
				if ( $m->sub_out ) {
					$total_minutes += $m->sub_out;
				} elseif ( $m->sub_in ) {
					$total_minutes += (90 - $m->sub_in);
				} else {
					if ( $m->is_starting ) {
						$total_minutes += 90;
					}
				}
			}
			
			// Получаем события
			$events = $wpdb->get_row( $wpdb->prepare(
				"SELECT 
					SUM(CASE WHEN event_type = 'A3898573' THEN 1 ELSE 0 END) as goals,
					SUM(CASE WHEN event_type = 'B44F03A6' THEN 1 ELSE 0 END) as assists,
					SUM(CASE WHEN event_type = '7B83D3F0' THEN 1 ELSE 0 END) as yellow_cards,
					SUM(CASE WHEN event_type = 'FC171553' THEN 1 ELSE 0 END) as red_cards
				 FROM {$wpdb->prefix}arsenal_match_events
				 WHERE player_id = %s AND match_id IN ($placeholders)",
				$params_lineups
			) );
			
			// Получаем позицию игрока для определения, вратарь ли это
			$player_data = arsenal_get_player_data( $player_id );
			$position_code = isset( $player_data->position_id ) ? $player_data->position_id : null;
			
			// Если вратарь, считаем пропущенные голы за год
			$goals_conceded = 0;
			if ( $position_code === 'A98B3A74' ) {
				$goals_conceded = arsenal_get_goalkeeper_goals_conceded( $player_id, $tournament_id, $year );
			}
			
			$years_stats[] = (object) array(
				'year' => $year,
				'matches_played' => $lineups->matches_played ?? 0,
				'minutes_played' => $total_minutes,
				'goals' => $events->goals ?? 0,
				'assists' => $events->assists ?? 0,
				'yellow_cards' => $events->yellow_cards ?? 0,
				'red_cards' => $events->red_cards ?? 0,
				'goals_conceded' => $goals_conceded
			);
		}
	}
	
	// Сортируем по году в обратном порядке (новые годы сверху)
	usort( $years_stats, function( $a, $b ) {
		return $b->year - $a->year;
	} );
	
	return $years_stats;
}

/**
 * Получить количество пропущенных голов вратарём за сезон
 *
 * Подсчитывает пропущенные голы используя финальный счет матча.
 * Считает голы только в матчах, где вратарь ИГРАЛ (был в стартовом составе).
 *
 * @param string $goalkeeper_id ID вратаря (player_id из wp_arsenal_players)
 * @param string $tournament_id  ID турнира
 * @param int    $year          Год (по умолчанию текущий)
 * @return int                  Количество пропущенных голов
 */
function arsenal_get_goalkeeper_goals_conceded( $goalkeeper_id, $tournament_id, $year = null ) {
	global $wpdb;
	
	if ( ! $year ) {
		$year = intval( date( 'Y' ) );
	}
	
	// Считаем голы только в матчах, где вратарь был в СТАРТОВОМ СОСТАВЕ
	$goals_conceded = $wpdb->get_var( $wpdb->prepare(
		"SELECT COALESCE( SUM(
			CASE 
				WHEN ml.team_id = m.home_team_id THEN m.away_score
				WHEN ml.team_id = m.away_team_id THEN m.home_score
				ELSE 0
			END
		), 0 ) as total_conceded
		 FROM {$wpdb->prefix}arsenal_match_lineups ml
		 INNER JOIN {$wpdb->prefix}arsenal_matches m ON ml.match_id = m.match_id
		 WHERE ml.player_id = %s
		 AND m.tournament_id = %s
		 AND YEAR(m.match_date) = %d
		 AND m.status = '0083CE05'
		 AND ml.is_starting = 1",
		$goalkeeper_id,
		$tournament_id,
		$year
	) );
	
	return intval( $goals_conceded );
}

/**
 * Получить команду игрока в определенном году турнира
 */
function arsenal_get_player_team_by_year( $player_id, $tournament_id, $year ) {
	global $wpdb;
	
	// Получаем команды, в которых играл игрок в этом году для выбранного турнира
	$team = $wpdb->get_row( $wpdb->prepare(
		"SELECT DISTINCT t.name as team_name
		 FROM {$wpdb->prefix}arsenal_match_lineups ml
		 INNER JOIN {$wpdb->prefix}arsenal_matches m ON m.match_id = ml.match_id
		 INNER JOIN {$wpdb->prefix}arsenal_teams t ON t.team_id = ml.team_id
		 WHERE ml.player_id = %s 
		 AND m.tournament_id = %s 
		 AND YEAR(m.match_date) = %d
		 LIMIT 1",
		$player_id,
		$tournament_id,
		$year
	) );
	
	return $team ? $team->team_name : '—';
}
