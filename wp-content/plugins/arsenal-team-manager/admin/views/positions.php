<?php
/**
 * Справочник позиций
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

// Обработка сохранения
if ( isset( $_POST['arsenal_save_position'] ) ) {
    check_admin_referer( 'arsenal_positions' );
    
    $code = sanitize_text_field( $_POST['code'] );
    $name_ru = sanitize_text_field( $_POST['name_ru'] );
    $name_en = sanitize_text_field( $_POST['name_en'] );
    $sort_order = intval( $_POST['sort_order'] );
    
    // Проверяем, существует ли позиция
    $exists = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM wp_arsenal_positions WHERE code = %s", $code ) );
    
    if ( $exists ) {
        // Обновляем
        $wpdb->update(
            'wp_arsenal_positions',
            array(
                'name_ru' => $name_ru,
                'name_en' => $name_en,
                'sort_order' => $sort_order
            ),
            array( 'code' => $code ),
            array( '%s', '%s', '%d' ),
            array( '%s' )
        );
        echo '<div class="notice notice-success"><p>Позиция обновлена.</p></div>';
    } else {
        // Добавляем новую
        $wpdb->insert(
            'wp_arsenal_positions',
            array(
                'code' => $code,
                'name_ru' => $name_ru,
                'name_en' => $name_en,
                'sort_order' => $sort_order
            ),
            array( '%s', '%s', '%s', '%d' )
        );
        echo '<div class="notice notice-success"><p>Позиция добавлена.</p></div>';
    }
}

// Обработка удаления
if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['code'] ) ) {
    check_admin_referer( 'delete_position_' . $_GET['code'] );
    
    $code = sanitize_text_field( $_GET['code'] );
    
    // Проверяем, используется ли позиция
    $used = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM wp_arsenal_players WHERE position = %s", $code ) );
    
    if ( $used > 0 ) {
        echo '<div class="notice notice-error"><p>Нельзя удалить позицию, которая используется игроками (' . $used . ' игр.).</p></div>';
    } else {
        $wpdb->delete( 'wp_arsenal_positions', array( 'code' => $code ), array( '%s' ) );
        echo '<div class="notice notice-success"><p>Позиция удалена.</p></div>';
    }
}

// Получаем все позиции
$positions = $wpdb->get_results( "
    SELECT 
        pos.*,
        COUNT(p.id) as players_count
    FROM wp_arsenal_positions pos
    LEFT JOIN wp_arsenal_players p ON p.position = pos.id
    GROUP BY pos.id
    ORDER BY pos.sort_order
" );

?>
<div class="wrap">
    <h1 class="wp-heading-inline">Справочник позиций</h1>
    <hr class="wp-header-end">
    
    <div style="display: grid; grid-template-columns: 1fr 400px; gap: 20px; margin-top: 20px;">
        
        <!-- Таблица существующих позиций -->
        <div>
            <h2>Существующие позиции</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 60px;">Код</th>
                        <th>Название (русский)</th>
                        <th>Название (English)</th>
                        <th style="width: 80px;">Порядок</th>
                        <th style="width: 80px;">Игроков</th>
                        <th style="width: 100px;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( $positions ): ?>
                        <?php foreach ( $positions as $pos ): ?>
                        <tr>
                            <td><strong><?php echo esc_html( $pos->code ); ?></strong></td>
                            <td><?php echo esc_html( $pos->name_ru ); ?></td>
                            <td><?php echo esc_html( $pos->name_en ); ?></td>
                            <td><?php echo esc_html( $pos->sort_order ); ?></td>
                            <td><?php echo $pos->players_count; ?></td>
                            <td>
                                <button class="button button-small edit-position-btn" 
                                        data-code="<?php echo esc_attr( $pos->code ); ?>"
                                        data-name-ru="<?php echo esc_attr( $pos->name_ru ); ?>"
                                        data-name-en="<?php echo esc_attr( $pos->name_en ); ?>"
                                        data-sort="<?php echo esc_attr( $pos->sort_order ); ?>">
                                    Изменить
                                </button>
                                <?php if ( $pos->players_count == 0 ): ?>
                                <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=arsenal-positions&action=delete&code=' . $pos->code ), 'delete_position_' . $pos->code ); ?>" 
                                   class="button button-small button-link-delete"
                                   onclick="return confirm('Удалить позицию?');">
                                    Удалить
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">Позиции не найдены.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Форма добавления/редактирования -->
        <div class="postbox" style="padding: 20px;">
            <h2 id="form-title">Добавить позицию</h2>
            
            <form method="post" action="">
                <?php wp_nonce_field( 'arsenal_positions' ); ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="code">Код *</label></th>
                        <td>
                            <input type="text" id="code" name="code" 
                                   value="" maxlength="5" required
                                   style="text-transform: uppercase;">
                            <p class="description">G, D, M, F</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="name_ru">Русский *</label></th>
                        <td>
                            <input type="text" id="name_ru" name="name_ru" 
                                   value="" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="name_en">English *</label></th>
                        <td>
                            <input type="text" id="name_en" name="name_en" 
                                   value="" required>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="sort_order">Порядок</label></th>
                        <td>
                            <input type="number" id="sort_order" name="sort_order" 
                                   value="0" min="0">
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="arsenal_save_position" class="button button-primary">
                        Сохранить
                    </button>
                    <button type="button" id="reset-form" class="button">
                        Сброс
                    </button>
                </p>
            </form>
        </div>
        
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Редактирование позиции
    $('.edit-position-btn').on('click', function() {
        var code = $(this).data('code');
        var nameRu = $(this).data('name-ru');
        var nameEn = $(this).data('name-en');
        var sort = $(this).data('sort');
        
        $('#form-title').text('Редактировать позицию');
        $('#code').val(code).prop('readonly', true);
        $('#name_ru').val(nameRu);
        $('#name_en').val(nameEn);
        $('#sort_order').val(sort);
        
        $('html, body').animate({
            scrollTop: $('#form-title').offset().top - 100
        }, 500);
    });
    
    // Сброс формы
    $('#reset-form').on('click', function() {
        $('#form-title').text('Добавить позицию');
        $('#code').val('').prop('readonly', false);
        $('#name_ru').val('');
        $('#name_en').val('');
        $('#sort_order').val('0');
    });
});
</script>
