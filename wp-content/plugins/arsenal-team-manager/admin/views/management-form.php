<?php
/**
 * Форма редактирования/добавления члена руководства
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$id = isset( $_GET['id'] ) ? intval( $_GET['id'] ) : 0;
$show_success = false;
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

// Обработка формы
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['save_management'] ) ) {
    check_admin_referer( 'arsenal_management_nonce' );

    $data = array(
        'name' => sanitize_text_field( $_POST['name'] ?? '' ),
        'position' => sanitize_text_field( $_POST['position'] ?? '' ),
        'description' => sanitize_textarea_field( $_POST['description'] ?? '' ),
        'photo_url' => ! empty( $_POST['photo_url'] ) ? esc_url_raw( $_POST['photo_url'] ) : '',
    );

    // Валидация
    if ( empty( $data['name'] ) ) {
        echo '<div class="notice notice-error"><p>Пожалуйста, заполните ФИО</p></div>';
    } elseif ( empty( $data['position'] ) ) {
        echo '<div class="notice notice-error"><p>Пожалуйста, заполните должность</p></div>';
    } else {
        if ( $is_edit ) {
            $result = $wpdb->update(
                $wpdb->prefix . 'arsenal_management',
                $data,
                array( 'id' => $id ),
                array( '%s', '%s', '%s', '%s' ),
                array( '%d' )
            );
            $message = 'Запись обновлена';
        } else {
            $result = $wpdb->insert(
                $wpdb->prefix . 'arsenal_management',
                $data,
                array( '%s', '%s', '%s', '%s' )
            );
            $message = 'Запись добавлена';
            if ( $result ) {
                $id = $wpdb->insert_id;
                $is_edit = true;
                $management = $wpdb->get_row( $wpdb->prepare( 
                    "SELECT * FROM {$wpdb->prefix}arsenal_management WHERE id = %d", 
                    $id 
                ) );
            }
        }

        if ( $result !== false ) {
            $show_success = true;
        } else {
            $error_msg = $wpdb->last_error ?: 'Неизвестная ошибка';
            echo '<div class="notice notice-error"><p>Ошибка при сохранении: ' . esc_html( $error_msg ) . '</p></div>';
        }
    }
}

?>
<div class="wrap">
    <h1><?php echo $is_edit ? '✏️ Редактирование' : '➕ Добавление члена руководства'; ?></h1>

    <?php if ( $show_success ): ?>
        <div class="notice notice-success"><p>
            ✓ <?php echo esc_html( $message ); ?>
        </p></div>
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
                        <label for="description">
                            <strong>Описание</strong>
                        </label>
                        <textarea id="description" name="description" rows="5" 
                                  class="large-text" placeholder="Краткая биография, опыт, достижения..."><?php echo $management ? esc_textarea( $management->description ) : ''; ?></textarea>
                        <span class="description">Расширенная информация о члене руководства (опыт, достижения и т.д.)</span>
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
