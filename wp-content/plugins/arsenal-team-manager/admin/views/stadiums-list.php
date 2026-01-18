<?php
/**
 * Шаблон: Список стадионов в админке
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <div class="stadiums-wrapper">
        <div class="stadiums-header">
            <h1>🏟️ Стадионы</h1>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-stadium-add' ) ); ?>" class="button button-primary">
                ➕ Добавить стадион
            </a>
        </div>

        <!-- Сообщения об успехе/ошибке -->
        <?php if ( isset( $_GET['success'] ) && $_GET['success'] == 1 ) : ?>
            <div class="notice notice-success is-dismissible" style="margin: 20px 0;">
                <p><?php esc_html_e( 'Стадион успешно сохранён!', 'arsenal-team-manager' ); ?></p>
            </div>
        <?php endif; ?>

        <?php if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 ) : ?>
            <div class="notice notice-success is-dismissible" style="margin: 20px 0;">
                <p><?php esc_html_e( 'Стадион успешно удалён!', 'arsenal-team-manager' ); ?></p>
            </div>
        <?php endif; ?>

        <?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
            <div class="notice notice-error is-dismissible" style="margin: 20px 0;">
                <p><?php esc_html_e( 'Произошла ошибка при обработке стадиона!', 'arsenal-team-manager' ); ?></p>
            </div>
        <?php endif; ?>

        <div class="stadiums-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo count( $stadiums ); ?></span>
                <span class="stat-label">Всего стадионов</span>
            </div>
        </div>

        <!-- Таблица стадионов в виде сетки -->
        <div class="stadiums-table">
            <div class="stadiums-row stadiums-header">
                <div class="stadiums-col-photo">Фото</div>
                <div class="stadiums-col-name">Название</div>
                <div class="stadiums-col-city">Город</div>
                <div class="stadiums-col-capacity">Вместимость</div>
                <div class="stadiums-col-club-type">Тип клуба</div>
                <div class="stadiums-col-action">Действия</div>
            </div>

            <?php if ( ! empty( $stadiums ) ) : ?>
                <?php foreach ( $stadiums as $stadium ) : ?>
                    <div class="stadiums-row">
                        <div class="stadiums-col-photo">
                            <?php if ( ! empty( $stadium->photo_url ) ) : ?>
                                <?php
                                $photo_url = $stadium->photo_url;
                                if ( ! str_starts_with( $photo_url, 'http://' ) && ! str_starts_with( $photo_url, 'https://' ) ) {
                                    $photo_url = home_url( $photo_url );
                                }
                                ?>
                                <img src="<?php echo esc_url( $photo_url ); ?>" 
                                     alt="<?php echo esc_attr( $stadium->name ); ?>"
                                     class="stadium-thumbnail">
                            <?php else : ?>
                                <div class="stadium-thumbnail-empty">
                                    <span class="dashicons dashicons-format-image"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="stadiums-col-name">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-stadium-edit&stadium_id=' . $stadium->id ) ); ?>" 
                               class="stadium-name-link">
                                <?php echo esc_html( $stadium->name ); ?>
                            </a>
                        </div>
                        <div class="stadiums-col-city">
                            <?php echo ! empty( $stadium->city ) ? esc_html( $stadium->city ) : '—'; ?>
                        </div>
                        <div class="stadiums-col-capacity">
                            <?php echo ! empty( $stadium->capacity ) ? number_format( intval( $stadium->capacity ), 0, ',', ' ' ) : '—'; ?>
                        </div>
                        <div class="stadiums-col-club-type">
                            <span class="club-type-badge">
                                <?php echo ! empty( $stadium->club_type ) ? esc_html( $stadium->club_type ) : 'Основной клуб'; ?>
                            </span>
                        </div>
                        <div class="stadiums-col-action">
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-stadium-edit&stadium_id=' . $stadium->id ) ); ?>" 
                               class="button button-small" title="Редактировать">
                                ✏️
                            </a>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_stadium&stadium_id=' . $stadium->id ), 'arsenal_delete_stadium' ) ); ?>" 
                               class="button button-small" title="Удалить"
                               onclick="return confirm('Удалить стадион?');">
                                🗑️
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="stadiums-row stadiums-empty">
                    <div class="stadiums-empty-message">
                        ℹ️ Стадионы не найдены
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Пагинация -->
        <?php if ( $total_pages > 1 ) : ?>
        <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-top: 30px; padding: 20px;">
            <!-- Кнопка "Назад" -->
            <?php if ( $paged > 1 ) : ?>
                <a href="<?php echo esc_url( admin_url( "admin.php?page=arsenal-stadiums&paged=" . ( $paged - 1 ) ) ); ?>" 
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
                        echo "<a href=\"" . esc_url( admin_url( "admin.php?page=arsenal-stadiums&paged=$p" ) ) . "\" 
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
                <a href="<?php echo esc_url( admin_url( "admin.php?page=arsenal-stadiums&paged=" . ( $paged + 1 ) ) ); ?>" 
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
                Страница <?php echo intval( $paged ); ?> из <?php echo intval( $total_pages ); ?> (всего: <?php echo intval( $total ); ?> стадионов)
            </span>
        </div>
    <?php endif; ?>
    </div>
