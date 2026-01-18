<?php
/**
 * Форма редактирования/добавления члена руководства
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Обработка формы должна быть в отдельном хуке ПЕРЕД выводом
// Здесь только вывод формы

global $wpdb;

$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$error_msg = '';
$management = null;
$is_edit = false;

if ( $id ) {
    $management = $wpdb->get_row( $wpdb->prepare( 
        "SELECT * FROM {$wpdb->prefix}arsenal_management WHERE id = %d", 
        $id 
    ) );
    $is_edit = true;
    if ( ! $management ) {
        wp_die( 'Запись не найдена' );
    }
}

?>
<div class="wrap">
    <h1><?php echo $is_edit ? '✏️ Редактирование' : '➕ Добавление члена руководства'; ?></h1>

    <?php if ( ! empty( $error_msg ) ): ?>
        <div class="notice notice-error"><p><?php echo esc_html( $error_msg ); ?></p></div>
    <?php endif; ?>

    <form method="post" class="management-form-container">
        <?php wp_nonce_field( 'arsenal_management_nonce' ); ?>
        <input type="hidden" name="save_management" value="1">
        <?php if ( $is_edit ): ?>
            <input type="hidden" name="id" value="<?php echo esc_attr( $id ); ?>">
        <?php endif; ?>

        <div class="management-form-wrapper">
            <!-- Левая колонка: основная информация -->
            <div class="management-form-left">
                <!-- Основная информация -->
                <div class="management-form-section">
                    <h3>Основная информация</h3>

                    <div class="form-group">
                        <label for="name">
                            <strong>ФИО <span class="required">*</span></strong>
                        </label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo $management ? esc_attr( $management->name ) : ''; ?>"
                               placeholder="Иван Петров"
                               class="regular-text" required>
                        <span class="description">Полное имя члена руководства</span>
                    </div>

                    <div class="form-group">
                        <label for="position">
                            <strong>Должность <span class="required">*</span></strong>
                        </label>
                        <input type="text" id="position" name="position" 
                               value="<?php echo $management ? esc_attr( $management->position ) : ''; ?>"
                               placeholder="Директор, Главный тренер, Спортивный директор"
                               class="regular-text" required>
                        <span class="description">Должность в клубе</span>
                    </div>

                    <div class="form-group">
                        <label for="club_type">
                            <strong>Тип клуба</strong>
                        </label>
                        <?php
                        $club_type = 'Основной клуб';
                        if ( $management ) {
                            $club_info = json_decode( $management->club_type, true );
                            if ( is_array( $club_info ) && isset( $club_info['type'] ) ) {
                                $club_type = $club_info['type'];
                            } elseif ( is_string( $management->club_type ) ) {
                                $club_type = $management->club_type;
                            }
                        }
                        ?>
                        <select id="club_type" name="club_type" class="regular-text">
                            <option value="Основной клуб" <?php selected( $club_type, 'Основной клуб' ); ?>>Основной клуб</option>
                            <option value="СДЮШ" <?php selected( $club_type, 'СДЮШ' ); ?>>СДЮШ</option>
                        </select>
                        <span class="description">Выберите тип клуба для члена руководства</span>
                    </div>

                    <div class="form-group">
                        <label for="description">
                            <strong>Описание</strong>
                        </label>
                        <textarea id="description" name="description" rows="5" 
                                  class="large-text" placeholder="Краткая биография, опыт, достижения..."><?php echo $management ? esc_textarea( $management->description ) : ''; ?></textarea>
                        <span class="description">Расширенная информация о члене руководства (опыт, достижения и т.д.)</span>
                    </div>

                    <div class="form-group">
                        <label for="display_order">
                            <strong>Порядок отображения</strong>
                        </label>
                        <input type="number" id="display_order" name="display_order" 
                               value="<?php echo $management ? intval( $management->display_order ) : '0'; ?>"
                               min="0" max="999"
                               class="small-text" style="width: 100px;">
                        <span class="description">Порядок вывода членов руководства (по возрастанию)</span>
                    </div>
                </div>
            </div>

            <!-- Правая колонка: фото -->
            <div class="management-form-right">
                <!-- Фото -->
                <div class="management-form-section">
                    <h3>Фотография</h3>

                    <div class="management-photo-box">
                        <?php if ( $management && $management->photo_url ): ?>
                            <img id="photo-preview" src="<?php echo esc_url( $management->photo_url ); ?>" 
                                 alt="Фото" class="photo-preview">
                            <br><br>
                            <button type="button" class="button upload-photo-btn">Изменить фото</button>
                            <button type="button" class="button button-link-delete remove-photo-btn">Удалить</button>
                        <?php else: ?>
                            <div id="photo-preview-empty">
                                <span class="dashicons dashicons-camera"></span>
                                <p>Фото не загружено</p>
                            </div>
                            <button type="button" class="button upload-photo-btn">Загрузить фото</button>
                        <?php endif; ?>
                    </div>

                    <input type="hidden" id="photo_url" name="photo_url" 
                           value="<?php echo $management ? esc_attr( $management->photo_url ) : ''; ?>">
                </div>
            </div>
        </div>

        <!-- Кнопки -->
        <div class="management-form-buttons">
            <button type="submit" class="button button-primary">
                <?php echo $is_edit ? '💾 Обновить' : '✨ Добавить'; ?>
            </button>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-management' ); ?>" class="button">Отмена</a>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    var mediaUploader = null;

    $('.upload-photo-btn').on('click', function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Выберите фотографию',
            button: { text: 'Выбрать' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#photo_url').val(attachment.url);

            // Обновить превью
            var preview = $('#photo-preview');
            if (preview.length) {
                preview.attr('src', attachment.url);
            } else {
                $('#photo-preview-empty').html(
                    '<img src="' + attachment.url + '" class="photo-preview">'
                );
            }
        });

        mediaUploader.open();
    });

    $('.remove-photo-btn').on('click', function(e) {
        e.preventDefault();
        $('#photo_url').val('');
        $('#photo-preview').remove();
        $('#photo-preview-empty').html(
            '<span class="dashicons dashicons-camera"></span><p>Фото не загружено</p>'
        );
        location.reload();
    });
});
</script>
