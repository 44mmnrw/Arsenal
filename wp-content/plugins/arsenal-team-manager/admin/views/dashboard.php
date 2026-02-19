<?php
/**
 * Главная страница плагина (Dashboard)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Обработка сохранения настроек активного сезона
if ( isset( $_POST['arsenal_save_season'] ) && check_admin_referer( 'arsenal_save_season_action', 'arsenal_season_nonce' ) ) {
    $new_year = intval( $_POST['arsenal_active_season_year'] );
    
    // Автоматически находим season_id по году из БД
    $season_id = $wpdb->get_var( $wpdb->prepare(
        "SELECT season_id FROM {$wpdb->prefix}arsenal_matches WHERE YEAR(match_date) = %d LIMIT 1",
        $new_year
    ) );
    
    update_option( 'arsenal_active_season_year', $new_year );
    if ( $season_id ) {
        update_option( 'arsenal_active_season_id', $season_id );
        echo '<div class="notice notice-success is-dismissible"><p><strong>✓ Активный сезон обновлён:</strong> ' . $new_year . ' (ID: ' . $season_id . ')</p></div>';
    } else {
        echo '<div class="notice notice-success is-dismissible"><p><strong>✓ Активный год обновлён:</strong> ' . $new_year . '</p></div>';
    }
}

// Получаем активный год сезона из настроек
$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );

// ID команды Арсенал - ищем по названию и получаем team_id (VARCHAR для матчей)
$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
if ( ! $arsenal_team_id ) {
    $arsenal_team_id = null;
}

// Статистика - безопасные запросы с обработкой ошибок
// Игроки в составе - только те, у кого есть контракт
$total_players = intval( $wpdb->get_var( 
    "SELECT COUNT(DISTINCT player_id) FROM {$wpdb->prefix}arsenal_team_contracts"
) );

// Матчи Арсенала (где оба тима есть и забиты оба счёта)
$total_matches = 0;
if ( $arsenal_team_id ) {
    $total_matches = intval( $wpdb->get_var( $wpdb->prepare( 
        "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_matches 
        WHERE (home_team_id = %s OR away_team_id = %s)
        AND home_score IS NOT NULL AND away_score IS NOT NULL
        AND YEAR(match_date) = %d
        AND tournament_id = %s",
        $arsenal_team_id, $arsenal_team_id, $active_season_year, '71CFDAA6'
    ) ) );
}

// Голы Арсенала в матчах (сумма забитых по home_score и away_score)
$total_goals = 0;
$goals_against = 0;
if ( $arsenal_team_id ) {
    $total_goals = intval( $wpdb->get_var( $wpdb->prepare(
        "SELECT COALESCE(SUM(CASE 
            WHEN home_team_id = %s THEN home_score
            WHEN away_team_id = %s THEN away_score
            ELSE 0 
        END), 0)
        FROM {$wpdb->prefix}arsenal_matches 
        WHERE (home_team_id = %s OR away_team_id = %s)
        AND home_score IS NOT NULL AND away_score IS NOT NULL
        AND YEAR(match_date) = %d
        AND tournament_id = %s",
        $arsenal_team_id, $arsenal_team_id, $arsenal_team_id, $arsenal_team_id, $active_season_year, '71CFDAA6'
    ) ) );
    
    // Голы пропущенные Арсеналом (противоположная логика)
    $goals_against = intval( $wpdb->get_var( $wpdb->prepare(
        "SELECT COALESCE(SUM(CASE 
            WHEN home_team_id = %s THEN away_score
            WHEN away_team_id = %s THEN home_score
            ELSE 0 
        END), 0)
        FROM {$wpdb->prefix}arsenal_matches 
        WHERE (home_team_id = %s OR away_team_id = %s)
        AND home_score IS NOT NULL AND away_score IS NOT NULL
        AND YEAR(match_date) = %d
        AND tournament_id = %s",
        $arsenal_team_id, $arsenal_team_id, $arsenal_team_id, $arsenal_team_id, $active_season_year, '71CFDAA6'
    ) ) );
}

// Получаем турнирную таблицу из функции темы за активный год сезона
if ( function_exists( 'arsenal_calculate_standings' ) ) {
    $all_standings = arsenal_calculate_standings( $active_season_year, '71CFDAA6' );
} else {
    // Fallback если функции нет
    $all_standings = [];
}

// Получаем ID Арсенала по team_id
$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );

// Находим позицию Арсенала в таблице
$position = '—';
$points = '—';
if ( $all_standings && $arsenal_team_id ) {
    $rank_counter = 1;
    foreach ( $all_standings as $team ) {
        if ( $team['team_id'] == $arsenal_team_id ) {
            $position = $rank_counter;
            $points = $team['points'];
            break;
        }
        $rank_counter++;
    }
}

// Создаем объект для совместимости
$standings = (object) array(
    'rank' => $position,
    'points' => $points
);

?>

<div class="dashboard-wrapper">
    <div class="dashboard-header">
        <h1>⚽ Управление ФК Арсенал Дзержинск</h1>
    </div>
    
    <!-- Выбор активного года сезона -->
    <div class="season-selector-card">
        <div class="season-selector-header">
            <h2>📅 Активный год сезона</h2>
        </div>
        <form method="post" action="" class="season-selector-form">
            <?php wp_nonce_field( 'arsenal_save_season_action', 'arsenal_season_nonce' ); ?>
            <div class="season-selector-content">
                <label for="arsenal_active_season_year">
                    Выберите год для отображения статистики:
                </label>
                <div class="season-selector-row">
                    <select name="arsenal_active_season_year" id="arsenal_active_season_year">
                        <?php
                        // Получаем текущий активный год
                        $current_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );
                        
                        // Получаем доступные годы из БД
                        $years = $wpdb->get_col( "SELECT DISTINCT YEAR(match_date) as year 
                                                  FROM {$wpdb->prefix}arsenal_matches 
                                                  ORDER BY year DESC" );
                        
                        if ( ! empty( $years ) ) {
                            foreach ( $years as $year ) {
                                $selected = ( $current_year == $year ) ? 'selected' : '';
                                echo "<option value='{$year}' {$selected}>{$year}</option>";
                            }
                        } else {
                            // Fallback: показываем текущий год
                            echo "<option value='{$current_year}' selected>{$current_year}</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" name="arsenal_save_season" class="button button-primary">
                        ✓ Сохранить
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Статистика карточки -->
    <div class="dashboard-stats-grid">
        
        <!-- Карточка: Игроки -->
        <div class="stat-card">
            <div class="stat-card-header">
                <h3>👥 Игроки в составе</h3>
            </div>
            <div class="stat-card-content">
                <p class="stat-number"><?php echo $total_players; ?></p>
                <p class="stat-description">в активном составе</p>
            </div>
        </div>
        
        <!-- Карточка: Место в турнире -->
        <div class="stat-card">
            <div class="stat-card-header">
                <h3>🏆 Место в турнире</h3>
            </div>
            <div class="stat-card-content">
                <p class="stat-number"><?php echo $standings ? $standings->rank : '—'; ?></p>
                <p class="stat-description">Очков: <strong><?php echo $standings ? $standings->points : '—'; ?></strong></p>
            </div>
        </div>
        
        <!-- Карточка: Матчи -->
        <div class="stat-card">
            <div class="stat-card-header">
                <h3>⚽ Матчей сыграно</h3>
            </div>
            <div class="stat-card-content">
                <p class="stat-number"><?php echo $total_matches; ?></p>
                <p class="stat-description">в текущем сезоне</p>
            </div>
        </div>
        
        <!-- Карточка: Голы -->
        <div class="stat-card">
            <div class="stat-card-header">
                <h3>⚡ Голов забито</h3>
            </div>
            <div class="stat-card-content">
                <p class="stat-number" style="color: #28a745;"><?php echo $total_goals ? $total_goals : 0; ?></p>
                <p class="stat-description">за сезон</p>
            </div>
        </div>
        
        <!-- Карточка: Голы пропущены -->
        <div class="stat-card">
            <div class="stat-card-header">
                <h3>🛡️ Голов пропущено</h3>
            </div>
            <div class="stat-card-content">
                <p class="stat-number" style="color: #dc3545;"><?php echo isset( $goals_against ) ? $goals_against : 0; ?></p>
                <p class="stat-description">за сезон</p>
            </div>
        </div>
        
    </div>
    
    <!-- Раздел быстрой навигации -->
    <div class="dashboard-quick-nav">
        <h2>📌 Быстрая навигация</h2>
        <div class="quick-nav-buttons">
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-matches' ); ?>" class="quick-nav-btn matches-btn">
                <span class="btn-icon">⚽</span>
                <span class="btn-label">Матчи</span>
            </a>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-staff' ); ?>" class="quick-nav-btn staff-btn">
                <span class="btn-icon">👔</span>
                <span class="btn-label">Персонал</span>
            </a>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-players' ); ?>" class="quick-nav-btn players-btn">
                <span class="btn-icon">👥</span>
                <span class="btn-label">Игроки</span>
            </a>
        </div>
    </div>
    
</div>
