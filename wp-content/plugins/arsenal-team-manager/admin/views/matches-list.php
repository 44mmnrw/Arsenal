<?php
/**
 * Шаблон: Список матчей в админке
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="matches-wrapper">
    <!-- Сообщения об успехе/ошибке -->
    <?php if ( isset( $_GET['success'] ) && $_GET['success'] == 1 ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>✓ Матч успешно сохранён!</strong></p>
        </div>
    <?php endif; ?>
    
    <?php if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>✓ Матч успешно удалён!</strong></p>
        </div>
    <?php endif; ?>
    
    <?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Произошла ошибка при сохранении матча!</strong></p>
        </div>
    <?php endif; ?>
    
    <div class="matches-header">
        <h1>⚽ Матчи ФК Арсенал</h1>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-add' ) ); ?>" class="button button-primary">
            ➕ Добавить матч
        </a>
    </div>

    <div class="matches-stats">
        <div class="stat-box">
            <span class="stat-number"><?php echo intval( $total ); ?></span>
            <span class="stat-label">Всего матчей</span>
        </div>
    </div>
    
    <!-- Фильтры -->
    <div class="matches-filters">
        <form method="GET" action="" id="matches-filter-form">
            <input type="hidden" name="page" value="arsenal-matches">
            
            <div class="filters-grid">
                <div class="filter-group">
                    <label for="filter_team">🏆 Команда:</label>
                    <select name="filter_team" id="filter_team" onchange="document.getElementById('matches-filter-form').submit();">
                        <option value="">Все команды</option>
                        <?php foreach ( $teams as $team_id => $team_name ) : ?>
                            <option value="<?php echo esc_attr( $team_id ); ?>" 
                                <?php selected( ! empty( $_GET['filter_team'] ) && $_GET['filter_team'] === $team_id ); ?>>
                                <?php echo esc_html( $team_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter_status">📊 Статус:</label>
                    <select name="filter_status" id="filter_status" onchange="document.getElementById('matches-filter-form').submit();">
                        <option value="">Все статусы</option>
                        <?php foreach ( $statuses as $status_id => $status_name ) : ?>
                            <option value="<?php echo esc_attr( $status_id ); ?>" 
                                <?php selected( ! empty( $_GET['filter_status'] ) && $_GET['filter_status'] === $status_id ); ?>>
                                <?php echo esc_html( $status_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_tournament">🎯 Турнир:</label>
                    <select name="filter_tournament" id="filter_tournament" onchange="document.getElementById('matches-filter-form').submit();">
                        <option value="">Все турниры</option>
                        <?php foreach ( $tournaments as $tournament_id => $tournament_name ) : ?>
                            <option value="<?php echo esc_attr( $tournament_id ); ?>" 
                                <?php selected( ! empty( $_GET['filter_tournament'] ) && $_GET['filter_tournament'] == $tournament_id ); ?>>
                                <?php echo esc_html( $tournament_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="filter_season">📅 Сезон:</label>
                    <select name="filter_season" id="filter_season" onchange="document.getElementById('matches-filter-form').submit();">
                        <option value="">Все сезоны</option>
                        <?php foreach ( $seasons as $season_id => $season_name ) : ?>
                            <option value="<?php echo esc_attr( $season_id ); ?>" 
                                <?php selected( ! empty( $_GET['filter_season'] ) && $_GET['filter_season'] == $season_id ); ?>>
                                <?php echo esc_html( $season_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-matches' ) ); ?>" class="button">
                        🔄 Сброс
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <?php if ( ! empty( $matches ) ) : ?>
        <div class="matches-table">
            <div class="matches-row matches-header">
                <div class="matches-col-date">📅 Дата</div>
                <div class="matches-col-time">⏰ Время</div>
                <div class="matches-col-home">🏠 Дома</div>
                <div class="matches-col-score">📊 Счёт</div>
                <div class="matches-col-away">✈️ Гости</div>
                <div class="matches-col-status">📈 Статус</div>
                <div class="matches-col-tour">🔢 Тур</div>
                <div class="matches-col-action">✏️</div>
                <div class="matches-col-action">🗑️</div>
            </div>

            <?php foreach ( $matches as $match ) : ?>
                <div class="matches-row">
                    <div class="matches-col-date">
                        <strong><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $match->match_date ) ) ); ?></strong>
                    </div>
                    <div class="matches-col-time">
                        <?php echo ! empty( $match->match_time ) ? esc_html( substr( $match->match_time, 0, 5 ) ) : '—'; ?>
                    </div>
                    <div class="matches-col-home">
                        <?php if ( ! empty( $match->home_logo ) ) : ?>
                            <img src="<?php 
                                $logo_url = $match->home_logo;
                                if ( $logo_url && strpos( $logo_url, 'http' ) === 0 ) {
                                    echo esc_url( $logo_url );
                                } elseif ( $logo_url && strpos( $logo_url, '/' ) === 0 ) {
                                    echo esc_url( home_url() . $logo_url );
                                } else {
                                    echo esc_url( home_url( '/' . $logo_url ) );
                                }
                            ?>" alt="<?php echo esc_attr( $match->home_team_name ); ?>" 
                                 class="match-team-logo">
                        <?php endif; ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match->id ) ); ?>" 
                           class="match-team-link">
                            <?php echo esc_html( $match->home_team_name ); ?>
                        </a>
                    </div>
                    <div class="matches-col-score">
                        <strong><?php echo intval( $match->home_score ); ?> : <?php echo intval( $match->away_score ); ?></strong>
                    </div>
                    <div class="matches-col-away">
                        <?php if ( ! empty( $match->away_logo ) ) : ?>
                            <img src="<?php 
                                $logo_url = $match->away_logo;
                                if ( $logo_url && strpos( $logo_url, 'http' ) === 0 ) {
                                    echo esc_url( $logo_url );
                                } elseif ( $logo_url && strpos( $logo_url, '/' ) === 0 ) {
                                    echo esc_url( home_url() . $logo_url );
                                } else {
                                    echo esc_url( home_url( '/' . $logo_url ) );
                                }
                            ?>" alt="<?php echo esc_attr( $match->away_team_name ); ?>" 
                                 class="match-team-logo">
                        <?php endif; ?>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match->id ) ); ?>" 
                           class="match-team-link">
                            <?php echo esc_html( $match->away_team_name ); ?>
                        </a>
                    </div>
                    <div class="matches-col-status">
                        <?php
                        echo isset( $statuses[ $match->status ] ) 
                            ? esc_html( $statuses[ $match->status ] ) 
                            : esc_html( $match->status );
                        ?>
                    </div>
                    <div class="matches-col-tour">
                        <?php echo ! empty( $match->tour ) ? intval( $match->tour ) : '—'; ?>
                    </div>
                    <div class="matches-col-action">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match->id ) ); ?>" 
                           class="button" title="Редактировать">✏️</a>
                    </div>
                    <div class="matches-col-action">
                        <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?action=arsenal_delete_match&match_id=' . $match->id ), 'delete_match_' . $match->id ) ); ?>" 
                           class="button" 
                           onclick="return confirm('Вы уверены? Это удалит матч и все связанные события и составы.');"
                           title="Удалить">🗑️</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ( $total_pages > 1 ) : ?>
            <div class="matches-pagination">
                <div class="pagination">
                    <?php
                    echo paginate_links( array(
                        'base'      => add_query_arg( 'paged', '%#%' ),
                        'format'    => '',
                        'prev_text' => '← Предыдущая',
                        'next_text' => 'Следующая →',
                        'total'     => $total_pages,
                        'current'   => $paged,
                    ) );
                    ?>
                </div>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <div class="matches-empty-message">
            <p>⚽ Матчи не найдены. <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-add' ) ); ?>">Создайте первый матч</a></p>
        </div>
    <?php endif; ?>
</div>
