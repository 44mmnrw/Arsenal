<?php
/**
 * Форма редактирования/добавления должности
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

$job_title_id = isset( $_GET['job_title_id'] ) ? intval( $_GET['job_title_id'] ) : 0;
$job_title = null;
$is_edit = false;

if ( $job_title_id ) {
    $job_title = Arsenal_Staff_Manager::get_job_title( $job_title_id );
    $is_edit = true;
    if ( ! $job_title ) {
        wp_die( 'Должность не найдена' );
    }
}

// Обработка формы
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['save_job_title'] ) ) {
    check_admin_referer( 'arsenal_job_title_nonce' );

    $data = array(
        'job_title_name' => sanitize_text_field( $_POST['job_title_name'] ?? '' ),
        'description' => sanitize_textarea_field( $_POST['description'] ?? '' ),
        'department_id' => ! empty( $_POST['department_id'] ) ? intval( $_POST['department_id'] ) : null,
    );

    if ( empty( $data['job_title_name'] ) ) {
        echo '<div class="notice notice-error"><p>Название должности обязательно</p></div>';
    } else {
        if ( $is_edit ) {
            $result = Arsenal_Staff_Manager::update_job_title( $job_title_id, $data );
            $message = 'Должность обновлена';
        } else {
            $result = Arsenal_Staff_Manager::add_job_title( $data['job_title_name'], $data['description'] );
            $message = 'Должность добавлена';
            $job_title_id = $result;
            $is_edit = true;
        }

        if ( $result ) {
            $job_title = Arsenal_Staff_Manager::get_job_title( $job_title_id );
            echo '<div class="notice notice-success"><p>' . esc_html( $message ) . '</p></div>';
        } else {
            error_log( 'Arsenal Staff - update_job_title failed: ' . json_encode( $data ) );
            global $wpdb;
            error_log( 'WP Error: ' . $wpdb->last_error );
            echo '<div class="notice notice-error"><p>Ошибка при сохранении: ' . esc_html( $wpdb->last_error ) . '</p></div>';
        }
    }
}

?>
<div class="wrap">
    <h1><?php echo $is_edit ? '✏️ Редактирование должности' : '➕ Добавление новой должности'; ?></h1>

    <?php
    $departments = Arsenal_Staff_Manager::get_departments( true );
    ?>

    <form method="post" style="max-width: 600px; margin: 20px 0;">
        <?php wp_nonce_field( 'arsenal_job_title_nonce' ); ?>
        <input type="hidden" name="save_job_title" value="1">

        <div class="form-section" style="background: #fff; padding: 20px; border: 1px solid #ccd0d4; border-radius: 4px;">
            
            <div class="form-group">
                <label for="job_title_name">Название должности *</label>
                <input type="text" id="job_title_name" name="job_title_name" required
                       value="<?php echo esc_attr( $job_title->job_title_name ?? '' ); ?>"
                       placeholder="Например: Тренер, Врач команды, Сертификатор">
                <small style="color: #666; display: block; margin-top: 5px;">Введите название должности</small>
            </div>

            <div class="form-group">
                <label for="department_id">Отдел</label>
                <select id="department_id" name="department_id"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">— Не указан —</option>
                    <?php foreach ( $departments as $dept ): ?>
                        <option value="<?php echo $dept->id; ?>"
                                <?php selected( $job_title->department_id ?? null, $dept->id ); ?>>
                            <?php echo esc_html( $dept->department_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: #666; display: block; margin-top: 5px;">Выберите отдел, к которому относится должность</small>
            </div>

            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" rows="4" 
                          style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                          placeholder="Опишите обязанности и компетенции"><?php echo esc_textarea( $job_title->description ?? '' ); ?></textarea>
                <small style="color: #666; display: block; margin-top: 5px;">Опционально</small>
            </div>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px; justify-content: center;">
            <button type="submit" class="button button-primary button-large">
                💾 Сохранить
            </button>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-staff' ); ?>" class="button button-large">
                ← Назад
            </a>
        </div>
    </form>
</div>

<style>
.form-section {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
    font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #0073aa;
    box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.1);
}

.form-group input[type="checkbox"] {
    margin: 0;
    cursor: pointer;
}
</style>
