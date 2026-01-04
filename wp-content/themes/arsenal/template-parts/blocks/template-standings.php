<?php
/**
 * Template part: Tournament Standings Table
 * 
  * Динамический расчет турнирной таблицы с учетом правил лиги:
 * - Основная сортировка по очкам
 * - При равных очках: личные встречи (очки в них)
 * - Разница мячей в личных встречах
 * - Забитые мячи в личных встречах
 * - Жёлтые карточки (меньше = выше)
 * - Общая разница мячей
 * - Общее количество забитых мячей
 * - Количество побед
 * - ID команды (для стабильной сортировки)
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Перенаправляем на новый компонент
get_template_part( 'template-parts/components/standings-table' );


global $wpdb;

$current_year = 2025;
$current_season_id = '5B2ABC0C'; // Правильный сезон

// ===== СОБИРАЕМ ТАБЛИЦУ ИЗ МАТЧЕЙ СЕЗОНА =====

// Получаем все команды которые участвовали в матчах сезона 2025
$query = "SELECT DISTINCT t.id, t.name, t.logo_url, t.team_id
         FROM {$wpdb->prefix}arsenal_teams t
         INNER JOIN {$wpdb->prefix}arsenal_match_lineups ml ON ml.team_id = t.team_id
         INNER JOIN {$wpdb->prefix}arsenal_matches m ON ml.match_id = m.match_id
         WHERE m.season_id = %s
         ORDER BY t.name";

$query = $wpdb->prepare( $query, $current_season_id );
$teams = $wpdb->get_results( $query );

if ( empty( $teams ) ) {
    echo '<p class="standings-error">Команды не найдены для сезона 2025</p>';
    return;
}

// Инициализируем статистику по ID команды (используем team_id из матчей)
$standings = [];
foreach ( $teams as $team ) {
    $standings[ $team->team_id ] = [
        'id'              => $team->id,
        'team_id'         => $team->team_id,
        'name'            => $team->name,
        'logo_url'        => $team->logo_url,
        'played'          => 0,
        'wins'            => 0,
        'draws'           => 0,
        'losses'          => 0,
        'goals_for'       => 0,
        'goals_against'   => 0,
        'points'          => 0,
        'yellow_cards'    => 0,
    ];
}

// ===== ПОЛУЧАЕМ ЖЁЛТЫЕ КАРТОЧКИ ДЛЯ КАЖДОЙ КОМАНДЫ (сезон 2025) =====
$yellow_cards_data = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT me.team_id, COUNT(*) as count
         FROM {$wpdb->prefix}arsenal_match_events me
         INNER JOIN {$wpdb->prefix}arsenal_matches m ON me.match_id = m.match_id
         WHERE m.season_id = %s 
         AND me.event_type = 'yellow_card' 
         AND me.team_id IS NOT NULL
         GROUP BY me.team_id",
        $current_season_id
    )
);

foreach ( $yellow_cards_data as $yc ) {
    if ( isset( $standings[ $yc->team_id ] ) ) {
        $standings[ $yc->team_id ]['yellow_cards'] = intval( $yc->count );
    }
}

// Получаем все ЗАВЕРШЁННЫЕ матчи сезона 2025 (status = '0083CE05' = Завершено)
$matches = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT m.home_team_id, m.away_team_id, m.home_score, m.away_score
         FROM {$wpdb->prefix}arsenal_matches m
         WHERE m.season_id = %s 
         AND m.status = '0083CE05' 
         AND m.home_score IS NOT NULL 
         AND m.away_score IS NOT NULL
         ORDER BY m.match_date ASC",
        $current_season_id
    )
);

foreach ( $matches as $match ) {
    $home_id = $match->home_team_id;
    $away_id = $match->away_team_id;
    $home_score = intval( $match->home_score );
    $away_score = intval( $match->away_score );
    
    // Пропускаем если команд нет в БД
    if ( ! isset( $standings[ $home_id ] ) || ! isset( $standings[ $away_id ] ) ) {
        continue;
    }
    
    // Сыгранные матчи
    $standings[ $home_id ]['played']++;
    $standings[ $away_id ]['played']++;
    
    // Голы
    $standings[ $home_id ]['goals_for'] += $home_score;
    $standings[ $home_id ]['goals_against'] += $away_score;
    $standings[ $away_id ]['goals_for'] += $away_score;
    $standings[ $away_id ]['goals_against'] += $home_score;
    
    // Результаты и очки
    if ( $home_score > $away_score ) {
        $standings[ $home_id ]['wins']++;
        $standings[ $home_id ]['points'] += 3;
        $standings[ $away_id ]['losses']++;
    } elseif ( $home_score < $away_score ) {
        $standings[ $away_id ]['wins']++;
        $standings[ $away_id ]['points'] += 3;
        $standings[ $home_id ]['losses']++;
    } else {
        $standings[ $home_id ]['draws']++;
        $standings[ $away_id ]['draws']++;
        $standings[ $home_id ]['points']++;
        $standings[ $away_id ]['points']++;
    }
}

// ===== ФУНКЦИЯ РАСЧЁТА СТАТИСТИКИ ЛИЧНЫХ ВСТРЕЧ (HEAD-TO-HEAD) =====
$get_h2h_stats = function( $team1_id, $team2_id ) use ( $wpdb, $current_season_id ) {
    $h2h = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT m.home_team_id, m.away_team_id, m.home_score, m.away_score
             FROM {$wpdb->prefix}arsenal_matches m
             WHERE m.season_id = %s
             AND m.status = '0083CE05'
             AND m.home_score IS NOT NULL AND m.away_score IS NOT NULL
             AND (
                (m.home_team_id = %s AND m.away_team_id = %s) OR
                (m.home_team_id = %s AND m.away_team_id = %s)
             )",
            $current_season_id,
            $team1_id, $team2_id, $team2_id, $team1_id
        )
    );
    
    $stats = [
        'points'      => 0,
        'goals_for'   => 0,
        'goals_against' => 0,
    ];
    
    foreach ( $h2h as $match ) {
        if ( (string) $match->home_team_id === (string) $team1_id ) {
            $team1_score = intval( $match->home_score );
            $team2_score = intval( $match->away_score );
        } else {
            $team1_score = intval( $match->away_score );
            $team2_score = intval( $match->home_score );
        }
        
        $stats['goals_for'] += $team1_score;
        $stats['goals_against'] += $team2_score;
        
        if ( $team1_score > $team2_score ) {
            $stats['points'] += 3;
        } elseif ( $team1_score === $team2_score ) {
            $stats['points'] += 1;
        }
    }
    
    return $stats;
};

// ===== СОРТИРУЕМ ПО ПРАВИЛАМ ЛИГИ =====
usort( $standings, function( $a, $b ) use ( $get_h2h_stats ) {
    // 1. Основная сортировка по очкам
    if ( $a['points'] !== $b['points'] ) {
        return $b['points'] - $a['points'];
    }
    
    // 2. При равных очках: ЛИЧНЫЕ ВСТРЕЧИ
    $h2h_a = $get_h2h_stats( $a['team_id'], $b['team_id'] );
    $h2h_b = $get_h2h_stats( $b['team_id'], $a['team_id'] );
    
    if ( $h2h_a['points'] !== $h2h_b['points'] ) {
        return $h2h_b['points'] - $h2h_a['points'];
    }
    
    // 3. Разница мячей в личных встречах
    $h2h_diff_a = $h2h_a['goals_for'] - $h2h_a['goals_against'];
    $h2h_diff_b = $h2h_b['goals_for'] - $h2h_b['goals_against'];
    
    if ( $h2h_diff_a !== $h2h_diff_b ) {
        return $h2h_diff_b - $h2h_diff_a;
    }
    
    // 4. Забитые мячи в личных встречах
    if ( $h2h_a['goals_for'] !== $h2h_b['goals_for'] ) {
        return $h2h_b['goals_for'] - $h2h_a['goals_for'];
    }
    
    // 5. Жёлтые карточки (меньше = лучше)
    if ( $a['yellow_cards'] !== $b['yellow_cards'] ) {
        return $a['yellow_cards'] - $b['yellow_cards'];
    }
    
    // 6. Общая разница мячей
    $diff_a = $a['goals_for'] - $a['goals_against'];
    $diff_b = $b['goals_for'] - $b['goals_against'];
    
    if ( $diff_a !== $diff_b ) {
        return $diff_b - $diff_a;
    }
    
    // 7. Забитые голы (общие)
    if ( $a['goals_for'] !== $b['goals_for'] ) {
        return $b['goals_for'] - $a['goals_for'];
    }
    
    // 8. Победы
    if ( $a['wins'] !== $b['wins'] ) {
        return $b['wins'] - $a['wins'];
    }
    
    // 9. ID (для стабильной сортировки)
    return $a['id'] - $b['id'];
} );

// ===== ЭТАП 7: Вывод таблицы =====
?>

<section class="tournament-standings-section">
    <div class="container">
        <div class="standings-header">
            <h2 class="standings-title">Турнирная таблица</h2>
            <p class="standings-season">Сезон <?php echo esc_html( $current_year ); ?></p>
        </div>

        <div class="standings-table-wrapper">
            <?php if ( ! empty( $standings ) ) : ?>
                <table class="standings-table">
                    <thead>
                        <tr>
                            <th class="col-position">№</th>
                            <th class="col-team">Клуб</th>
                            <th class="col-games">И</th>
                            <th class="col-wins">В</th>
                            <th class="col-draws">Н</th>
                            <th class="col-losses">П</th>
                            <th class="col-diff">ЗМ:ПМ</th>
                            <th class="col-points">Очки</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $position = 1;
                        foreach ( $standings as $team ) :
                            $goal_diff_str = $team['goals_for'] . ':' . $team['goals_against'];
                            $is_arsenal = stripos( $team['name'], 'Арсенал' ) !== false;
                            
                            // Формируем полный URL логотипа
                            $logo_url = ! empty( $team['logo_url'] ) ? home_url() . $team['logo_url'] : '';
                            ?>
                            <tr<?php echo $is_arsenal ? ' class="arsenal-row"' : ''; ?>>
                                <td class="col-position"><?php echo intval( $position ); ?></td>
                                <td class="col-team">
                                    <?php if ( ! empty( $logo_url ) ) : ?>
                                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( $team['name'] ); ?>" class="team-badge" width="24" height="24" loading="lazy">
                                    <?php endif; ?>
                                    <span class="team-name"><?php echo esc_html( $team['name'] ); ?></span>
                                </td>
                                <td class="col-games text-center"><?php echo intval( $team['played'] ); ?></td>
                                <td class="col-wins text-center"><?php echo intval( $team['wins'] ); ?></td>
                                <td class="col-draws text-center"><?php echo intval( $team['draws'] ); ?></td>
                                <td class="col-losses text-center"><?php echo intval( $team['losses'] ); ?></td>
                                <td class="col-diff text-center"><?php echo esc_html( $goal_diff_str ); ?></td>
                                <td class="col-points text-center font-bold"><?php echo intval( $team['points'] ); ?></td>
                            </tr>
                            <?php
                            $position++;
                        endforeach;
                        ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="standings-error">Данные турнирной таблицы недоступны</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
.tournament-standings-section {
    padding: 40px 0;
    background: #f9f9f9;
}

.standings-header {
    margin-bottom: 30px;
    text-align: center;
}

.standings-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 10px 0;
}

.standings-season {
    font-size: 14px;
    color: #666;
    margin: 0;
}

.standings-table-wrapper {
    background: white;
    border-radius: 8px;
    overflow-x: auto;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.standings-table {
    width: 100%;
    border-collapse: collapse;
}

.standings-table thead {
    background: #f5f5f5;
    border-bottom: 2px solid #ddd;
}

.standings-table thead th {
    padding: 12px 8px;
    text-align: left;
    font-size: 13px;
    font-weight: 600;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.standings-table tbody tr {
    border-bottom: 1px solid #eee;
    transition: background-color 0.2s;
}

.standings-table tbody tr:hover {
    background-color: #fafafa;
}

.standings-table tbody tr.arsenal-row {
    background-color: #fff5f5;
    font-weight: 500;
}

.standings-table tbody td {
    padding: 12px 8px;
    font-size: 14px;
    color: #333;
}

.standings-table .col-position {
    width: 40px;
    text-align: center;
    font-weight: 600;
    color: #666;
}

.standings-table .col-team {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 200px;
    max-width: 250px;
}

.standings-table .team-badge {
    display: block;
    width: 24px;
    height: 24px;
    object-fit: contain;
    flex-shrink: 0;
}

.standings-table .team-name {
    word-break: break-word;
    overflow-wrap: break-word;
    flex: 1;
    min-width: 0;
}

.standings-table .col-games,
.standings-table .col-wins,
.standings-table .col-draws,
.standings-table .col-losses,
.standings-table .col-diff,
.standings-table .col-points {
    text-align: center;
    font-variant-numeric: tabular-nums;
}

.standings-table .col-points {
    font-weight: 600;
    color: #1a1a1a;
    width: 70px;
}

.standings-table .text-center {
    text-align: center;
}

.standings-table .font-bold {
    font-weight: 700;
}

.standings-error {
    padding: 20px;
    text-align: center;
    color: #999;
}
</style>
