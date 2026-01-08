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
    <h1 class="wp-heading-inline">Стадионы</h1>
    <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-stadium-add' ) ); ?>" class="page-title-action">
        + Добавить стадион
    </a>
    <hr class="wp-header-end">
    
    <!-- Сообщения об успехе/ошибке -->
    <?php if ( isset( $_GET['success'] ) && $_GET['success'] == 1 ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Стадион успешно сохранён!</strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Закрыть уведомление</span></button>
        </div>
    <?php endif; ?>
    
    <?php if ( isset( $_GET['deleted'] ) && $_GET['deleted'] == 1 ) : ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>Стадион успешно удалён!</strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Закрыть уведомление</span></button>
        </div>
    <?php endif; ?>
    
    <?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Произошла ошибка при обработке стадиона!</strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Закрыть уведомление</span></button>
        </div>
    <?php endif; ?>
    
    <!-- Таблица стадионов -->
    <table class="wp-list-table widefat striped">
        <thead>
            <tr>
                <th style="width: 80px;">Фото</th>
                <th>Название</th>
                <th style="width: 150px;">Город</th>
                <th style="width: 120px;">Вместимость</th>
                <th style="width: 150px;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! empty( $stadiums ) ) : ?>
                <?php foreach ( $stadiums as $stadium ) : ?>
                    <tr>
                        <td>
                            <?php if ( ! empty( $stadium->photo_url ) ) : ?>
                                <?php
                                // Преобразуем относительный путь в полный URL если нужно
                                $photo_url = $stadium->photo_url;
                                if ( ! str_starts_with( $photo_url, 'http://' ) && ! str_starts_with( $photo_url, 'https://' ) ) {
                                    $photo_url = home_url( $photo_url );
                                }
                                ?>
                                <img src="<?php echo esc_url( $photo_url ); ?>" 
                                     alt="<?php echo esc_attr( $stadium->name ); ?>" 
                                     style="max-width: 60px; max-height: 60px; object-fit: cover; border-radius: 4px;">
                            <?php else : ?>
                                <span style="color: #999;">Нет фото</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $stadium->name ); ?></strong>
                        </td>
                        <td>
                            <?php echo ! empty( $stadium->city ) ? esc_html( $stadium->city ) : '—'; ?>
                        </td>
                        <td>
                            <?php echo ! empty( $stadium->capacity ) ? number_format( intval( $stadium->capacity ), 0, ',', ' ' ) : '—'; ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-stadium-edit&stadium_id=' . $stadium->id ) ); ?>" 
                               class="button button-small">
                                Редактировать
                            </a>
                            <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_stadium&stadium_id=' . $stadium->id ), 'arsenal_delete_stadium' ) ); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('Вы уверены, что хотите удалить этот стадион?');">
                                Удалить
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">
                        <em>Стадионы не найдены</em>
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
