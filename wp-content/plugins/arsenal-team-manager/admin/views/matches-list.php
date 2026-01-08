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

<div class="wrap">
    <h1 class="wp-heading-inline">Матчи ФК Арсенал</h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-add' ) ); ?>" class="page-title-action">
        + Добавить матч
    </a>
    <hr class="wp-header-end">
    
    <!-- Сообщения об успехе/ошибке -->
    <?php if ( isset( $_GET['success'] ) && $_GET['success'] == 1 ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Матч успешно сохранён!</strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Закрыть уведомление</span></button>
        </div>
    <?php endif; ?>
    
    <?php if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Матч успешно удалён!</strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Закрыть уведомление</span></button>
        </div>
    <?php endif; ?>
    
    <?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Произошла ошибка при сохранении матча!</strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Закрыть уведомление</span></button>
        </div>
    <?php endif; ?>
    
    <!-- Фильтры -->
    <div style="background: #f1f1f1; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
        <form method="GET" action="" id="matches-filter-form">
            <input type="hidden" name="page" value="arsenal-matches">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr auto; gap: 15px; align-items: center;">
                <div>
                    <label for="filter_team" style="display: block; font-weight: 600; margin-bottom: 5px;">Команда:</label>
                    <select name="filter_team" id="filter_team" style="width: 100%; padding: 6px;" onchange="document.getElementById('matches-filter-form').submit();">
                        <option value="">Все команды</option>
                        <?php foreach ( $teams as $team_id => $team_name ) : ?>
                            <option value="<?php echo esc_attr( $team_id ); ?>" 
                                <?php 
                                selected( ! empty( $_GET['filter_team'] ) && $_GET['filter_team'] === $team_id ); 
                                ?>>
                                <?php echo esc_html( $team_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>                <div>
                    <label for="filter_status" style="display: block; font-weight: 600; margin-bottom: 5px;">Статус:</label>
                    <select name="filter_status" id="filter_status" style="width: 100%; padding: 6px;" onchange="document.getElementById('matches-filter-form').submit();">
                        <option value="">Все статусы</option>
                        <?php foreach ( $statuses as $status_id => $status_name ) : ?>
                            <option value="<?php echo esc_attr( $status_id ); ?>" 
                                <?php selected( ! empty( $_GET['filter_status'] ) && $_GET['filter_status'] === $status_id ); ?>>
                                <?php echo esc_html( $status_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="filter_tournament" style="display: block; font-weight: 600; margin-bottom: 5px;">Турнир:</label>
                    <select name="filter_tournament" id="filter_tournament" style="width: 100%; padding: 6px;" onchange="document.getElementById('matches-filter-form').submit();">
                        <option value="">Все турниры</option>
                        <?php foreach ( $tournaments as $tournament_id => $tournament_name ) : ?>
                            <option value="<?php echo esc_attr( $tournament_id ); ?>" 
                                <?php selected( ! empty( $_GET['filter_tournament'] ) && $_GET['filter_tournament'] == $tournament_id ); ?>>
                                <?php echo esc_html( $tournament_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div>
                    <label for="filter_season" style="display: block; font-weight: 600; margin-bottom: 5px;">Сезон:</label>
                    <select name="filter_season" id="filter_season" style="width: 100%; padding: 6px;" onchange="document.getElementById('matches-filter-form').submit();">
                        <option value="">Все сезоны</option>
                        <?php foreach ( $seasons as $season_id => $season_name ) : ?>
                            <option value="<?php echo esc_attr( $season_id ); ?>" 
                                <?php selected( ! empty( $_GET['filter_season'] ) && $_GET['filter_season'] == $season_id ); ?>>
                                <?php echo esc_html( $season_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; align-items: flex-end; height: 100%;">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-matches' ) ); ?>" class="button" style="padding: 6px 15px; white-space: nowrap;">
                        Сброс
                    </a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Таблица матчей -->
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th style="width: 100px;">Дата</th>
                <th>Время</th>
                <th>Домашняя команда</th>
                <th>Счёт</th>
                <th>Гостевая команда</th>
                <th style="width: 80px;">Статус</th>
                <th style="width: 100px;">Тур</th>
                <th style="width: 150px;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $matches ) ) : ?>
                <?php foreach ( $matches as $match ) : ?>
                    <tr>
                        <td>
                            <strong><?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $match->match_date ) ) ); ?></strong>
                        </td>
                        <td>
                            <?php echo ! empty( $match->match_time ) ? esc_html( $match->match_time ) : '—'; ?>
                        </td>
                        <td>
                            <?php if ( ! empty( $match->home_logo ) ) : ?>
                                <img src="<?php 
                                    // Если URL начинается с http, то это полный URL, иначе добавляем домен
                                    $logo_url = $match->home_logo;
                                    if ( strpos( $logo_url, 'http' ) === 0 ) {
                                        echo esc_url( $logo_url );
                                    } elseif ( strpos( $logo_url, '/' ) === 0 ) {
                                        echo esc_url( home_url() . $logo_url );
                                    } else {
                                        echo esc_url( home_url( '/' . $logo_url ) );
                                    }
                                ?>" alt="<?php echo esc_attr( $match->home_team_name ); ?>" 
                                     style="width: 32px; height: 32px; object-fit: contain; margin-right: 8px; vertical-align: middle;">
                            <?php endif; ?>
                            <strong><?php echo esc_html( $match->home_team_name ); ?></strong>
                        </td>
                        <td style="text-align: center; font-weight: 600;">
                            <?php echo intval( $match->home_score ); ?> : <?php echo intval( $match->away_score ); ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $match->away_team_name ); ?></strong>
                            <?php if ( ! empty( $match->away_logo ) ) : ?>
                                <img src="<?php 
                                    // Если URL начинается с http, то это полный URL, иначе добавляем домен
                                    $logo_url = $match->away_logo;
                                    if ( strpos( $logo_url, 'http' ) === 0 ) {
                                        echo esc_url( $logo_url );
                                    } elseif ( strpos( $logo_url, '/' ) === 0 ) {
                                        echo esc_url( home_url() . $logo_url );
                                    } else {
                                        echo esc_url( home_url( '/' . $logo_url ) );
                                    }
                                ?>" alt="<?php echo esc_attr( $match->away_team_name ); ?>" 
                                     style="width: 32px; height: 32px; object-fit: contain; margin-left: 8px; vertical-align: middle;">
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            echo isset( $statuses[ $match->status ] ) 
                                ? esc_html( $statuses[ $match->status ] ) 
                                : esc_html( $match->status );
                            ?>
                        </td>
                        <td style="text-align: center;">
                            <?php echo ! empty( $match->tour ) ? intval( $match->tour ) : '—'; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match->id ) ); ?>" 
                               class="button button-small" style="margin-right: 5px;">
                                Редактировать
                            </a>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?action=arsenal_delete_match&match_id=' . $match->id ), 'delete_match_' . $match->id ) ); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('Вы уверены? Это удалит матч и все связанные события и составы.');">
                                Удалить
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px;">
                        <p style="color: #999;">Матчи не найдены</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Пагинация -->
    <?php if ( $total_pages > 1 ) : ?>
        <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 30px; padding: 20px;">
            <!-- Кнопка "Назад" -->
            <?php if ( $paged > 1 ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'paged', $paged - 1 ) ); ?>" 
                   class="button">
                    ← Назад
                </a>
            <?php else : ?>
                <button class="button" disabled style="opacity: 0.5; cursor: not-allowed;">
                    ← Назад
                </button>
            <?php endif; ?>
            
            <!-- Ссылки на страницы -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <?php
                $pages_to_show = array();
                // Добавляем первые 3 страницы
                for ( $p = 1; $p <= min( 3, $total_pages ); $p++ ) {
                    $pages_to_show[] = $p;
                }
                // Добавляем последнюю страницу если её ещё нет
                if ( $total_pages > 3 && ! in_array( $total_pages, $pages_to_show ) ) {
                    $pages_to_show[] = $total_pages;
                }
                
                $prev_page = 0;
                foreach ( $pages_to_show as $p ) {
                    // Добавляем многоточие, если есть разрыв между страницами
                    if ( $p > $prev_page + 1 ) {
                        echo "<span style=\"padding: 6px 12px; color: #999;\">...</span>";
                    }
                    
                    if ( $p === $paged ) {
                        echo "<span style=\"padding: 6px 12px; background: #2271b1; color: white; border-radius: 4px; font-weight: bold; min-width: 36px; text-align: center;\">" . intval( $p ) . "</span>";
                    } else {
                        echo "<a href=\"" . esc_url( add_query_arg( 'paged', $p ) ) . "\" 
                             style=\"padding: 6px 12px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; text-decoration: none; color: #0073aa; min-width: 36px; text-align: center; transition: background 0.2s;\"
                             onmouseover=\"this.style.background='#e8e8e8'\"
                             onmouseout=\"this.style.background='#f5f5f5'\">$p</a>";
                    }
                    $prev_page = $p;
                }
                ?>
            </div>
            
            <!-- Кнопка "Вперед" -->
            <?php if ( $paged < $total_pages ) : ?>
                <a href="<?php echo esc_url( add_query_arg( 'paged', $paged + 1 ) ); ?>" 
                   class="button">
                    Вперед →
                </a>
            <?php else : ?>
                <button class="button" disabled style="opacity: 0.5; cursor: not-allowed;">
                    Вперед →
                </button>
            <?php endif; ?>
            
            <!-- Информация о странице -->
            <span style="margin-left: 20px; color: #666; font-size: 13px;">
                Страница <?php echo intval( $paged ); ?> из <?php echo intval( $total_pages ); ?> (всего: <?php echo intval( $total ); ?> матчей)
            </span>
        </div>
    <?php endif; ?>
</div>
