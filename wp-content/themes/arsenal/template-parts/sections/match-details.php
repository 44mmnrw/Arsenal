<?php
/**
 * Template part: Match Page
 * 
 * Динамическая страница матча с:
 * - Основной информацией (команды, счёт, дата, время, стадион)
 * - Событиями матча (голы, карточки, замены)
 * - Составами команд (основной и запасные игроки)
 * - Информацией о судьях и функционерах
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ===== ПОЛУЧАЕМ ПАРАМЕТРЫ ИЗ URL =====
// Параметры: ?team_id=813F75&date=2025-03-13
// ИЛИ: ?team_name=Арсенал&date=2025-03-13

$team_id = isset( $_GET['team_id'] ) ? sanitize_text_field( $_GET['team_id'] ) : '';
$team_name = isset( $_GET['team_name'] ) ? sanitize_text_field( $_GET['team_name'] ) : '';
$match_date_param = isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : '';

// Если передано имя команды вместо ID - получаем ID
if ( ! $team_id && $team_name ) {
    global $wpdb;
    $team_obj = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT team_id FROM {$wpdb->prefix}arsenal_teams WHERE name = %s LIMIT 1",
            $team_name
        )
    );
    if ( $team_obj ) {
        $team_id = $team_obj->team_id;
    }
}

if ( ! $team_id || ! $match_date_param ) {
    echo '<p class="match-error">Не указаны параметры: team_id (или team_name) и date в формате YYYY-MM-DD</p>';
    return;
}

// ===== ПОЛУЧАЕМ МАТЧ ПО КОМАНДЕ И ДАТЕ (используя функцию) =====
require_once get_template_directory() . '/inc/functions/match-functions.php';

$match = arsenal_get_match_by_date_and_team( $match_date_param, $team_id );

if ( ! $match ) {
    echo '<p class="match-error">Матч не найден для команды в дату ' . esc_html( $match_date_param ) . '</p>';
    return;
}

// ===== ПОЛУЧАЕМ СОБЫТИЯ МАТЧА (используя функцию) =====
$events = arsenal_get_match_events( $match->match_id );

// ===== ПОЛУЧАЕМ СОСТАВЫ КОМАНД (используя функцию) =====
$lineups_all = arsenal_get_match_lineups( $match->match_id );
$lineups_organized = arsenal_organize_lineups( $lineups_all, $match->home_team_id );

$home_starting = $lineups_organized['home_starting'];
$home_subs = $lineups_organized['home_subs'];
$away_starting = $lineups_organized['away_starting'];
$away_subs = $lineups_organized['away_subs'];

// Форматируем логотипы
$home_logo_url = ! empty( $match->home_logo ) ? home_url() . $match->home_logo : '';
$away_logo_url = ! empty( $match->away_logo ) ? home_url() . $match->away_logo : '';

// Форматируем дату и время
$match_date_formatted = date_i18n( 'd.m.Y', strtotime( $match->match_date ) );
$match_time = date_i18n( 'H:i', strtotime( $match->match_time ) );

// Определяем статус матча
$is_finished = $match->status === 'FT';
$home_score = intval( $match->home_score );
$away_score = intval( $match->away_score );
?>

<article class="match-page">
    <div class="container">
        
        <!-- ===== ОСНОВНОЙ БЛОК МАТЧА ===== -->
        <section class="match-header">
            <div class="match-info-header">
                <div class="match-meta">
                    <span class="match-tour">ТУР <?php echo intval( $match->tour ); ?></span>
                    <span class="match-date"><?php echo esc_html( $match_date_formatted ); ?> <?php echo esc_html( $match_time ); ?></span>
                    <span class="match-status <?php echo $match->status === 'FT' ? 'finished' : 'live'; ?>">
                        <?php echo $match->status === 'FT' ? 'ЗАВЕРШЕНО' : $match->status; ?>
                    </span>
                </div>
            </div>

            <!-- СЧЁТ МАТЧА -->
            <div class="match-score">
                <!-- ДОМАШНЯЯ КОМАНДА -->
                <div class="team home-team">
                    <?php if ( ! empty( $home_logo_url ) ) : ?>
                        <img src="<?php echo esc_url( $home_logo_url ); ?>" alt="<?php echo esc_attr( $match->home_team_name ); ?>" class="team-logo" loading="lazy">
                    <?php endif; ?>
                    <h2 class="team-name"><?php echo esc_html( $match->home_team_name ); ?></h2>
                </div>

                <!-- СЧЁТ -->
                <div class="score-block">
                    <div class="score-display">
                        <span class="score-home"><?php echo intval( $match->home_score ); ?></span>
                        <span class="score-separator">:</span>
                        <span class="score-away"><?php echo intval( $match->away_score ); ?></span>
                    </div>
                </div>

                <!-- ГОСТЕВАЯ КОМАНДА -->
                <div class="team away-team">
                    <h2 class="team-name"><?php echo esc_html( $match->away_team_name ); ?></h2>
                    <?php if ( ! empty( $away_logo_url ) ) : ?>
                        <img src="<?php echo esc_url( $away_logo_url ); ?>" alt="<?php echo esc_attr( $match->away_team_name ); ?>" class="team-logo" loading="lazy">
                    <?php endif; ?>
                </div>
            </div>

            <!-- ДОПОЛНИТЕЛЬНАЯ ИНФОРМАЦИЯ -->
            <div class="match-details">
                <?php if ( ! empty( $match->attendance ) ) : ?>
                    <div class="detail-item">
                        <span class="detail-icon">👥</span>
                        <span class="detail-label">Зрители:</span>
                        <span class="detail-value"><?php echo number_format_i18n( intval( $match->attendance ) ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $match->main_referee ) ) : ?>
                    <div class="detail-item">
                        <span class="detail-icon">⚽</span>
                        <span class="detail-label">Главный судья:</span>
                        <span class="detail-value"><?php echo esc_html( $match->main_referee ); ?></span>
                    </div>
                <?php endif; ?>

                <?php if ( ! empty( $match->stadium_id ) ) : ?>
                    <div class="detail-item">
                        <span class="detail-icon">🏟️</span>
                        <span class="detail-label">Стадион:</span>
                        <span class="detail-value"><?php echo esc_html( $match->stadium_id ); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- ===== СОБЫТИЯ МАТЧА ===== -->
        <?php if ( ! empty( $events ) ) : ?>
            <section class="match-events">
                <h3 class="section-title">История матча</h3>
                <div class="events-timeline">
                    <?php foreach ( $events as $event ) : 
                        $is_home_event = $event->team_id === $match->home_team_id;
                        $event_class = 'event-' . strtolower( $event->event_type_name ? str_replace( '_', '-', $event->event_type_name ) : 'unknown' );
                    ?>
                        <div class="event-item <?php echo $is_home_event ? 'home' : 'away'; ?> <?php echo esc_attr( $event_class ); ?>">
                            <div class="event-minute"><?php echo intval( $event->minute ); ?>'</div>
                            <div class="event-content">
                                <span class="event-type"><?php echo esc_html( $event->event_type_name ); ?></span>
                                <?php if ( ! empty( $event->player_name ) ) : ?>
                                    <span class="event-player"><?php echo esc_html( $event->player_name ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- ===== СОСТАВЫ КОМАНД ===== -->
        <section class="match-lineups">
            <h3 class="section-title">Составы команд</h3>
            
            <div class="lineups-container">
                <!-- ДОМАШНЯЯ КОМАНДА -->
                <div class="team-lineup home">
                    <h4 class="lineup-team-name"><?php echo esc_html( $match->home_team_name ); ?></h4>
                    
                    <?php if ( ! empty( $home_starting ) ) : ?>
                        <div class="lineup-section">
                            <h5 class="lineup-subtitle">Основной состав</h5>
                            <ul class="players-list">
                                <?php foreach ( $home_starting as $player ) : ?>
                                    <li class="player-item">
                                        <span class="player-number"><?php echo intval( $player->shirt_number ); ?></span>
                                        <span class="player-name"><?php echo esc_html( $player->full_name ); ?></span>
                                        <?php if ( ! empty( $player->position_name ) ) : ?>
                                            <span class="player-position"><?php echo esc_html( $player->position_name ); ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $home_subs ) ) : ?>
                        <div class="lineup-section">
                            <h5 class="lineup-subtitle">Запасные</h5>
                            <ul class="players-list">
                                <?php foreach ( $home_subs as $player ) : ?>
                                    <li class="player-item">
                                        <span class="player-number"><?php echo intval( $player->shirt_number ); ?></span>
                                        <span class="player-name"><?php echo esc_html( $player->full_name ); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ГОСТЕВАЯ КОМАНДА -->
                <div class="team-lineup away">
                    <h4 class="lineup-team-name"><?php echo esc_html( $match->away_team_name ); ?></h4>
                    
                    <?php if ( ! empty( $away_starting ) ) : ?>
                        <div class="lineup-section">
                            <h5 class="lineup-subtitle">Основной состав</h5>
                            <ul class="players-list">
                                <?php foreach ( $away_starting as $player ) : ?>
                                    <li class="player-item">
                                        <span class="player-number"><?php echo intval( $player->shirt_number ); ?></span>
                                        <span class="player-name"><?php echo esc_html( $player->full_name ); ?></span>
                                        <?php if ( ! empty( $player->position_name ) ) : ?>
                                            <span class="player-position"><?php echo esc_html( $player->position_name ); ?></span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ( ! empty( $away_subs ) ) : ?>
                        <div class="lineup-section">
                            <h5 class="lineup-subtitle">Запасные</h5>
                            <ul class="players-list">
                                <?php foreach ( $away_subs as $player ) : ?>
                                    <li class="player-item">
                                        <span class="player-number"><?php echo intval( $player->shirt_number ); ?></span>
                                        <span class="player-name"><?php echo esc_html( $player->full_name ); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

    </div>
</article>

<style>
.match-page {
    padding: 40px 0;
    background: #f9f9f9;
}

.match-header {
    background: white;
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.match-info-header {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.match-meta {
    display: flex;
    gap: 15px;
    align-items: center;
    font-size: 14px;
    color: #666;
}

.match-tour,
.match-date,
.match-status {
    padding: 4px 12px;
    background: #f0f0f0;
    border-radius: 4px;
}

.match-status.finished {
    background: #e8f5e9;
    color: #2e7d32;
    font-weight: 600;
}

.match-score {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 20px;
    align-items: center;
    margin-bottom: 30px;
}

.team {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.team-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
}

.team-name {
    font-size: 20px;
    font-weight: 600;
    text-align: center;
    margin: 0;
    color: #1a1a1a;
}

.score-block {
    display: flex;
    justify-content: center;
}

.score-display {
    font-size: 56px;
    font-weight: 700;
    color: #1a1a1a;
    line-height: 1;
}

.score-separator {
    margin: 0 10px;
}

.match-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.detail-icon {
    font-size: 18px;
}

.detail-label {
    color: #666;
    font-weight: 500;
}

.detail-value {
    color: #1a1a1a;
    font-weight: 600;
}

/* СОБЫТИЯ */
.match-events {
    background: white;
    border-radius: 8px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.section-title {
    font-size: 22px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 20px 0;
}

.events-timeline {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.event-item {
    display: flex;
    gap: 15px;
    padding: 12px;
    background: #f9f9f9;
    border-radius: 6px;
    border-left: 4px solid #ddd;
}

.event-item.home {
    border-left-color: #4285f4;
    flex-direction: row;
}

.event-item.away {
    border-left-color: #ea4335;
    flex-direction: row-reverse;
}

.event-minute {
    min-width: 40px;
    font-weight: 700;
    color: #666;
    text-align: center;
}

.event-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.event-type {
    font-weight: 600;
    color: #1a1a1a;
    text-transform: capitalize;
}

.event-player {
    font-size: 13px;
    color: #666;
}

.event-goal .event-type::before {
    content: '⚽ ';
}

.event-warning .event-type::before {
    content: '🟨 ';
}

.event-red-card .event-type::before {
    content: '🟥 ';
}

.event-substitution .event-type::before {
    content: '🔄 ';
}

/* СОСТАВЫ */
.match-lineups {
    background: white;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.lineups-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-top: 20px;
}

@media (max-width: 768px) {
    .lineups-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .match-score {
        grid-template-columns: 1fr;
        gap: 15px;
    }
    
    .score-display {
        font-size: 40px;
    }
}

.team-lineup {
    padding: 20px;
    background: #f9f9f9;
    border-radius: 6px;
}

.lineup-team-name {
    font-size: 18px;
    font-weight: 700;
    color: #1a1a1a;
    margin: 0 0 20px 0;
}

.lineup-section {
    margin-bottom: 25px;
}

.lineup-section:last-child {
    margin-bottom: 0;
}

.lineup-subtitle {
    font-size: 13px;
    font-weight: 600;
    color: #666;
    text-transform: uppercase;
    margin: 0 0 12px 0;
    letter-spacing: 0.5px;
}

.players-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.player-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px;
    background: white;
    border-radius: 4px;
    font-size: 14px;
}

.player-number {
    min-width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0;
    border-radius: 4px;
    font-weight: 600;
    color: #666;
}

.player-name {
    flex: 1;
    color: #1a1a1a;
    font-weight: 500;
}

.player-position {
    font-size: 12px;
    color: #999;
}

.match-error {
    padding: 30px;
    text-align: center;
    color: #999;
    background: white;
    border-radius: 8px;
}
</style>
