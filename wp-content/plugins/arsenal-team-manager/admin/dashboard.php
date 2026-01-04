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
        echo '<div class="notice notice-success is-dismissible"><p><strong>Активный сезон обновлён:</strong> ' . $new_year . ' (ID: ' . $season_id . ')</p></div>';
    } else {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Активный год обновлён:</strong> ' . $new_year . '</p></div>';
    }
}

// Получаем активный год сезона из настроек
$active_season_year = get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) );

// ID команды Арсенал - ищем по названию и получаем team_id (VARCHAR для матчей)
$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );
if ( ! $arsenal_team_id ) {
    $arsenal_team_id = null;
}

// Статистика - безопасные запросы с обработкой ошибок
// Игроки в составе - только те, у кого есть контракт
$total_players = intval( $wpdb->get_var( 
    "SELECT COUNT(DISTINCT player_id) FROM wp_arsenal_team_contracts"
) );

// Матчи Арсенала (где оба тима есть и забиты оба счёта)
$total_matches = 0;
if ( $arsenal_team_id ) {
    $total_matches = intval( $wpdb->get_var( $wpdb->prepare( 
        "SELECT COUNT(*) FROM wp_arsenal_matches 
        WHERE (home_team_id = %s OR away_team_id = %s)
        AND home_score IS NOT NULL AND away_score IS NOT NULL
        AND YEAR(match_date) = %d
        AND tournament_id = %s",
        $arsenal_team_id, $arsenal_team_id, $active_season_year, '71CFDAA6'
    ) ) );
}

// Голы Арсенала в матчах (сумма забитых по home_score и away_score)
$total_goals = 0;
if ( $arsenal_team_id ) {
    $total_goals = intval( $wpdb->get_var( $wpdb->prepare(
        "SELECT COALESCE(SUM(CASE 
            WHEN home_team_id = %s THEN home_score
            WHEN away_team_id = %s THEN away_score
            ELSE 0 
        END), 0)
        FROM wp_arsenal_matches 
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
$arsenal_team_id = $wpdb->get_var( "SELECT team_id FROM wp_arsenal_teams WHERE name LIKE '%Арсенал%' LIMIT 1" );

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
<div class="wrap">
    <h1 class="wp-heading-inline">
        <span class="dashicons dashicons-admin-users" style="font-size: 32px; width: 32px; height: 32px;"></span>
        Управление ФК Арсенал Дзержинск
    </h1>
    <hr class="wp-header-end">
    
    <!-- Выбор активного года сезона -->
    <div class="postbox" style="margin-top: 20px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <h2 style="margin: 0 0 15px 0;">
            <span class="dashicons dashicons-calendar" style="color: #2271b1;"></span>
            Активный год сезона
        </h2>
        <form method="post" action="">
            <?php wp_nonce_field( 'arsenal_save_season_action', 'arsenal_season_nonce' ); ?>
            <div style="display: flex; align-items: center; gap: 15px;">
                <label for="arsenal_active_season_year" style="font-weight: 600;">
                    Выберите год для отображения статистики:
                </label>
                <select name="arsenal_active_season_year" id="arsenal_active_season_year" style="width: 120px;">
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
                    Сохранить
                </button>
                <span class="description" style="color: #646970;">
                    Этот год используется для статистики игроков на сайте
                </span>
            </div>
        </form>
    </div>
    
    <div style="margin-top: 20px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            
            <!-- Карточка: Игроки -->
            <div class="postbox" style="padding: 20px;">
                <h2 style="margin: 0 0 10px 0;">
                    <span class="dashicons dashicons-groups" style="color: #d9534f;"></span>
                    Игроки в составе
                </h2>
                <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #d9534f;">
                    <?php echo $total_players; ?>
                </p>
                <a href="<?php echo admin_url( 'admin.php?page=arsenal-players' ); ?>" class="button">
                    Управление игроками
                </a>
            </div>
            
            <!-- Карточка: Место в турнире -->
            <div class="postbox" style="padding: 20px;">
                <h2 style="margin: 0 0 10px 0;">
                    <span class="dashicons dashicons-awards" style="color: #f0ad4e;"></span>
                    Место в турнире
                </h2>
                <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #f0ad4e;">
                    <?php echo $standings ? $standings->rank : '—'; ?>
                </p>
                <p style="margin: 5px 0;">
                    Очков: <strong><?php echo $standings ? $standings->points : '—'; ?></strong>
                </p>
            </div>
            
            <!-- Карточка: Матчи -->
            <div class="postbox" style="padding: 20px;">
                <h2 style="margin: 0 0 10px 0;">
                    <span class="dashicons dashicons-calendar-alt" style="color: #5bc0de;"></span>
                    Матчей сыграно
                </h2>
                <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #5bc0de;">
                    <?php echo $total_matches; ?>
                </p>
            </div>
            
            <!-- Карточка: Голы -->
            <div class="postbox" style="padding: 20px;">
                <h2 style="margin: 0 0 10px 0;">
                    <span class="dashicons dashicons-yes" style="color: #5cb85c;"></span>
                    Голов забито
                </h2>
                <p style="font-size: 32px; font-weight: bold; margin: 10px 0; color: #5cb85c;">
                    <?php echo $total_goals ? $total_goals : 0; ?>
                </p>
            </div>
            
        </div>
        
    </div>
</div>
