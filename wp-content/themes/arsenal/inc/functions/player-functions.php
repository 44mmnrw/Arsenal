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
		"SELECT p.id, p.player_id, p.full_name, p.first_name, p.last_name, p.position_id, p.birth_date, p.citizenship, p.height_cm, p.weight_kg, p.dominant_foot, p.photo_url, p.shirt_number, p.biography, pos.name as position_name 
		 FROM {$wpdb->prefix}arsenal_players p 
		 LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
		 WHERE p.player_id = %s LIMIT 1",
		sanitize_text_field( $player_id )
	) );
	
	// Если не найден по player_id, пробуем по числовому id
	if ( ! $player && is_numeric( $player_id ) ) {
		$player = $wpdb->get_row( $wpdb->prepare(
			"SELECT p.id, p.player_id, p.full_name, p.first_name, p.last_name, p.position_id, p.birth_date, p.citizenship, p.height_cm, p.weight_kg, p.dominant_foot, p.photo_url, p.shirt_number, p.biography, pos.name as position_name 
			 FROM {$wpdb->prefix}arsenal_players p 
			 LEFT JOIN {$wpdb->prefix}arsenal_positions pos ON p.position_id = pos.position_id
			 WHERE p.id = %d LIMIT 1",
			intval( $player_id )
		) );
	}
	
	return $player;
}

/**
 * Подсчитать количество сыгранных минут в матче для игрока.
 *
 * @param int        $is_starting   Признак выхода в стартовом составе (1/0).
 * @param int|string $sub_in_minute Минута выхода на замену (если была), может быть null.
 * @param int|string $sub_out_minute Минута ухода с поля (если была), может быть null.
 * @return int Количество сыгранных минут.
 */
function arsenal_calculate_match_minutes( $is_starting, $sub_in_minute, $sub_out_minute ) {
	$is_starting    = intval( $is_starting );
	$sub_in_minute  = is_numeric( $sub_in_minute ) ? intval( $sub_in_minute ) : null;
	$sub_out_minute = is_numeric( $sub_out_minute ) ? intval( $sub_out_minute ) : null;

	if ( $is_starting ) {
		if ( null !== $sub_out_minute ) {
			return max( 0, $sub_out_minute );
		}

		return 90;
	}

	if ( null !== $sub_in_minute ) {
		if ( null !== $sub_out_minute ) {
			return max( 0, $sub_out_minute - $sub_in_minute );
		}

		return max( 0, 90 - $sub_in_minute );
	}

	return 0;
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
	
	// ШАГ 3: Вычисляем реальные минуты на основе подстановок
	// Для каждого матча где был игрок в lineups, ищем события sub_out/sub_in
	$params_events = array_merge( array( $player_id ), $match_ids );
	
	$minutes_data = $wpdb->get_results( 
		$wpdb->prepare(
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
		) 
	);
	
	$matches_played_count  = 0;
	$matches_started_count = 0;
	$total_minutes         = 0;
	
	foreach ( $minutes_data as $match_record ) {
		$minutes_for_match = arsenal_calculate_match_minutes( $match_record->is_starting, $match_record->sub_in, $match_record->sub_out );
		
		if ( intval( $match_record->is_starting ) === 1 ) {
			$matches_started_count++;
		}
		
		if ( $minutes_for_match > 0 || intval( $match_record->is_starting ) === 1 ) {
			$matches_played_count++;
		}
		
		$total_minutes += $minutes_for_match;
	}
	
	$events = $wpdb->get_row( 
		$wpdb->prepare(
			"SELECT 
				SUM(CASE WHEN event_type = 'A3898573' THEN 1 ELSE 0 END) as goals,
				SUM(CASE WHEN event_type = 'B44F03A6' THEN 1 ELSE 0 END) as assists,
				SUM(CASE WHEN event_type = '7B83D3F0' THEN 1 ELSE 0 END) as yellow_cards,
				SUM(CASE WHEN event_type = 'FC171553' THEN 1 ELSE 0 END) as red_cards
			 FROM {$wpdb->prefix}arsenal_match_events
			 WHERE player_id = %s AND match_id IN ($placeholders)",
			$params_events
		) 
	);
	
	return (object) array(
		'matches_played' => $matches_played_count,
		'matches_started' => $matches_started_count,
		'minutes_played' => $total_minutes,
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
	$filtered_events = array();

	foreach ( $events as &$match ) {
		$match->minutes_played = arsenal_calculate_match_minutes( $match->is_starting, $match->sub_in, $match->sub_out );

		if ( $match->minutes_played > 0 || intval( $match->is_starting ) === 1 ) {
			$filtered_events[] = $match;
		}
	}

	return $filtered_events;
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
		'selected_year'       => $selected_year, // Текущий выбранный год для применения коррекций
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
			
			// Считаем минуты и количество сыгранных матчей
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
			
			$matches_played_count  = 0;
			$total_minutes         = 0;
	
			foreach ( $minutes_data as $match_record ) {
				$minutes_for_match = arsenal_calculate_match_minutes( $match_record->is_starting, $match_record->sub_in, $match_record->sub_out );
		
				if ( $minutes_for_match > 0 || intval( $match_record->is_starting ) === 1 ) {
					$matches_played_count++;
				}
		
				$total_minutes += $minutes_for_match;
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
				'matches_played' => $matches_played_count,
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

/**
 * Получить примененные коррекции статистики игрока для турнира
 *
 * @param string $player_id ID игрока
 * @param string $tournament_id ID турнира
 * @return array Массив примененных коррекций
 */
function arsenal_get_player_corrections( $player_id, $tournament_id ) {
	// Используем класс из плагина, если он загружен
	if ( class_exists( 'Arsenal_Player_Stats_Corrections' ) ) {
		$corrections_manager = Arsenal_Player_Stats_Corrections::get_instance();
		// Получаем ТОЛЬКО примененные коррекции
		return $corrections_manager->get_corrections( $player_id, $tournament_id, true );
	}
	return array();
}

/**
 * Применить коррекции к статистике игрока
 *
 * Если есть примененные коррекции для конкретного года, добавляет их дельты к исходной статистике.
 * Использует тот же подход фильтрации по году, что и arsenal_apply_player_corrections_to_yearly_stats().
 *
 * @param object $stats Объект статистики игрока (из arsenal_get_player_stats)
 * @param string $player_id ID игрока
 * @param string $tournament_id ID турнира
 * @param int $year Год, для которого применяются коррекции
 * @return object Исправленный объект статистики
 */
function arsenal_apply_player_corrections( $stats, $player_id, $tournament_id, $year = null ) {
	global $wpdb;
	
	if ( ! $stats ) {
		return $stats;
	}
	
	// Если год не передан, используем текущий год
	if ( $year === null ) {
		$year = intval( date( 'Y' ) );
	} else {
		// ВАЖНО: конвертируем год в int для правильного сравнения!
		$year = intval( $year );
	}
	
	// DEBUG
	$debug = isset( $_GET['debug_corrections'] ) && $_GET['debug_corrections'] === '1';
	if ( $debug ) {
		error_log( "=== arsenal_apply_player_corrections DEBUG ===" );
		error_log( "Player ID: $player_id, Tournament: $tournament_id, Year: $year" );
	}
	
	// Получаем примененные коррекции с годом из таблицы seasons
	$corrections_with_years = $wpdb->get_results( $wpdb->prepare(
		"SELECT 
			c.*,
			YEAR(COALESCE(s.start_date, '2000-01-01')) as correction_year
		 FROM {$wpdb->prefix}arsenal_player_stats_corrections c
			LEFT JOIN {$wpdb->prefix}arsenal_seasons s ON c.season_id = s.season_id
			WHERE c.player_id = %s 
			AND c.tournament_id = %s 
			AND c.is_applied = 1
			ORDER BY c.created_at DESC",
		$player_id,
		$tournament_id
	) );
	
	if ( $debug ) {
		error_log( "Found corrections: " . count( $corrections_with_years ?? array() ) );
	}
	
	if ( empty( $corrections_with_years ) ) {
		return $stats;
	}
	
	// Преобразуем в массив для проще работы с stdClass
	$corrected_stats = clone $stats;
	
	// Суммируем коррекции только для нужного года
	$corrections_for_year = array(
		'minutes_played_delta' => 0,
		'matches_played_delta' => 0,
		'goals_delta' => 0,
		'goals_conceded_delta' => 0,
		'assists_delta' => 0,
		'yellow_cards_delta' => 0,
		'red_cards_delta' => 0,
	);
	
	// Применяем коррекцию если:
	// 1. Она указана для конкретного года И год совпадает, ИЛИ
	// 2. Она не указана для конкретного года (season_id IS NULL)
	foreach ( $corrections_with_years as $correction ) {
		$correction_year = intval( $correction->correction_year );
		
		$should_apply = false;
		
		if ( ! empty( $correction->season_id ) ) {
			// Коррекция для конкретного сезона - применяем только если года совпадают
			$should_apply = ( $correction_year === $year );
		} else {
			// Коррекция без season_id - применяем для всех лет (можно включить/выключить по необходимости)
			$should_apply = true;
		}
		
		if ( $debug ) {
			error_log( "Correction {$correction->correction_id}: year=$correction_year, target_year=$year, should_apply=" . ( $should_apply ? 'YES' : 'NO' ) );
		}
		
		if ( $should_apply ) {
			if ( isset( $correction->minutes_played_delta ) ) {
				$corrections_for_year['minutes_played_delta'] += intval( $correction->minutes_played_delta );
			}
			if ( isset( $correction->matches_played_delta ) ) {
				$corrections_for_year['matches_played_delta'] += intval( $correction->matches_played_delta );
			}
			if ( isset( $correction->goals_delta ) ) {
				$corrections_for_year['goals_delta'] += intval( $correction->goals_delta );
			}
			if ( isset( $correction->goals_conceded_delta ) ) {
				$corrections_for_year['goals_conceded_delta'] += intval( $correction->goals_conceded_delta );
			}
			if ( isset( $correction->assists_delta ) ) {
				$corrections_for_year['assists_delta'] += intval( $correction->assists_delta );
			}
			if ( isset( $correction->yellow_cards_delta ) ) {
				$corrections_for_year['yellow_cards_delta'] += intval( $correction->yellow_cards_delta );
			}
			if ( isset( $correction->red_cards_delta ) ) {
				$corrections_for_year['red_cards_delta'] += intval( $correction->red_cards_delta );
			}
		}
	}
	
	if ( $debug ) {
		error_log( "Accumulated corrections: " . json_encode( $corrections_for_year ) );
	}
	
	// Применяем коррекции к статистике
	$corrected_stats->minutes_played = intval( $corrected_stats->minutes_played ) + $corrections_for_year['minutes_played_delta'];
	$corrected_stats->matches_played = intval( $corrected_stats->matches_played ) + $corrections_for_year['matches_played_delta'];
	$corrected_stats->goals = intval( $corrected_stats->goals ) + $corrections_for_year['goals_delta'];
	$corrected_stats->goals_conceded = intval( $corrected_stats->goals_conceded ?? 0 ) + $corrections_for_year['goals_conceded_delta'];
	$corrected_stats->assists = intval( $corrected_stats->assists ) + $corrections_for_year['assists_delta'];
	$corrected_stats->yellow_cards = intval( $corrected_stats->yellow_cards ) + $corrections_for_year['yellow_cards_delta'];
	$corrected_stats->red_cards = intval( $corrected_stats->red_cards ) + $corrections_for_year['red_cards_delta'];
	
	if ( $debug ) {
		error_log( "Final stats: minutes=" . $corrected_stats->minutes_played . ", goals=" . $corrected_stats->goals . ", assists=" . $corrected_stats->assists );
	}
	
	return $corrected_stats;
}

/**
 * Применить коррекции к статистике по годам
 *
 * Применяет коррекции только к тому году, который указан в season_id коррекции
 *
 * @param array $yearly_stats Массив статистики по годам
 * @param string $player_id ID игрока
 * @param string $tournament_id ID турнира
 * @return array Исправленный массив статистики по годам
 */
function arsenal_apply_player_corrections_to_yearly_stats( $yearly_stats, $player_id, $tournament_id ) {
	global $wpdb;
	
	if ( empty( $yearly_stats ) ) {
		return $yearly_stats;
	}
	
	// Получаем примененные коррекции с годом из таблицы seasons
	// Используем LEFT JOIN чтобы обработать коррекции БЕЗ season_id (которые применяются ко всем годам)
	$corrections_with_years = $wpdb->get_results( $wpdb->prepare(
		"SELECT 
			c.*,
			YEAR(COALESCE(s.start_date, '2000-01-01')) as correction_year
		FROM {$wpdb->prefix}arsenal_player_stats_corrections c
		LEFT JOIN {$wpdb->prefix}arsenal_seasons s ON c.season_id = s.season_id
		WHERE c.player_id = %s 
		AND c.tournament_id = %s 
		AND c.is_applied = 1
		ORDER BY c.created_at DESC",
		$player_id,
		$tournament_id
	) );
	
	if ( empty( $corrections_with_years ) ) {
		return $yearly_stats;
	}
	
	// Применяем коррекции к каждому году
	$corrected_stats = array();
	
	foreach ( $yearly_stats as $stat ) {
		$corrected_stat = clone $stat;
		$year = intval( $stat->year );
		
		// Суммируем коррекции только для этого года
		$year_corrections = array(
			'minutes_played_delta' => 0,
			'matches_played_delta' => 0,
			'goals_delta' => 0,
			'goals_conceded_delta' => 0,
			'assists_delta' => 0,
			'yellow_cards_delta' => 0,
			'red_cards_delta' => 0,
		);
		
		// Проходим по коррекциям и суммируем только те, что подходят для этого года
		foreach ( $corrections_with_years as $correction ) {
			$correction_year = intval( $correction->correction_year );
			
			// Применяем коррекцию если:
			// 1. Она указана для конкретного года И год совпадает, ИЛИ
			// 2. Она не указана для конкретного года (season_id IS NULL)
			$should_apply = false;
			
			if ( ! empty( $correction->season_id ) ) {
				// Коррекция для конкретного сезона - применяем только если года совпадают
				$should_apply = ( $correction_year === $year );
			} else {
				// Коррекция без season_id - применяем для всех лет (можно включить/выключить по необходимости)
				$should_apply = true;
			}
			
			if ( $should_apply ) {
				if ( isset( $correction->minutes_played_delta ) ) {
					$year_corrections['minutes_played_delta'] += intval( $correction->minutes_played_delta );
				}
				if ( isset( $correction->matches_played_delta ) ) {
					$year_corrections['matches_played_delta'] += intval( $correction->matches_played_delta );
				}
				if ( isset( $correction->goals_delta ) ) {
					$year_corrections['goals_delta'] += intval( $correction->goals_delta );
				}
				if ( isset( $correction->goals_conceded_delta ) ) {
					$year_corrections['goals_conceded_delta'] += intval( $correction->goals_conceded_delta );
				}
				if ( isset( $correction->assists_delta ) ) {
					$year_corrections['assists_delta'] += intval( $correction->assists_delta );
				}
				if ( isset( $correction->yellow_cards_delta ) ) {
					$year_corrections['yellow_cards_delta'] += intval( $correction->yellow_cards_delta );
				}
				if ( isset( $correction->red_cards_delta ) ) {
					$year_corrections['red_cards_delta'] += intval( $correction->red_cards_delta );
				}
			}
		}
		
		// Применяем коррекции к данному году
		$corrected_stat->minutes_played = intval( $corrected_stat->minutes_played ) + $year_corrections['minutes_played_delta'];
		$corrected_stat->matches_played = intval( $corrected_stat->matches_played ) + $year_corrections['matches_played_delta'];
		$corrected_stat->goals = intval( $corrected_stat->goals ) + $year_corrections['goals_delta'];
		$corrected_stat->goals_conceded = intval( $corrected_stat->goals_conceded ?? 0 ) + $year_corrections['goals_conceded_delta'];
		$corrected_stat->assists = intval( $corrected_stat->assists ) + $year_corrections['assists_delta'];
		$corrected_stat->yellow_cards = intval( $corrected_stat->yellow_cards ) + $year_corrections['yellow_cards_delta'];
		$corrected_stat->red_cards = intval( $corrected_stat->red_cards ) + $year_corrections['red_cards_delta'];
		
		$corrected_stats[] = $corrected_stat;
	}
	
	return $corrected_stats;
}
