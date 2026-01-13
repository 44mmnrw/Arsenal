<?php
/**
 * Форма редактирования/добавления сотрудника
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

$staff_id = isset( $_GET['staff_id'] ) ? intval( $_GET['staff_id'] ) : 0;
$staff = null;
$is_edit = false;

if ( $staff_id ) {
    $staff = Arsenal_Staff_Manager::get_staff_member( $staff_id );
    $is_edit = true;
    if ( ! $staff ) {
        wp_die( 'Сотрудник не найден' );
    }
}

// Обработка формы
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['save_staff'] ) ) {
    check_admin_referer( 'arsenal_staff_nonce' );

    $data = array(
        'first_name' => sanitize_text_field( $_POST['first_name'] ?? '' ),
        'second_name' => sanitize_text_field( $_POST['second_name'] ?? '' ),
        'job_title_id' => ! empty( $_POST['job_title_id'] ) ? intval( $_POST['job_title_id'] ) : null,
        'birth_date' => ! empty( $_POST['birth_date'] ) ? sanitize_text_field( $_POST['birth_date'] ) : null,
        'contract_start' => ! empty( $_POST['contract_start'] ) ? sanitize_text_field( $_POST['contract_start'] ) : null,
        'contract_end' => ! empty( $_POST['contract_end'] ) ? sanitize_text_field( $_POST['contract_end'] ) : null,
        'phone' => sanitize_text_field( $_POST['phone'] ?? '' ),
        'email' => sanitize_email( $_POST['email'] ?? '' ),
        'photo_url' => ! empty( $_POST['photo_url'] ) ? esc_url_raw( $_POST['photo_url'] ) : '',
        'bio' => sanitize_textarea_field( $_POST['bio'] ?? '' ),
    );

    if ( $is_edit ) {
        $result = Arsenal_Staff_Manager::update_staff( $staff_id, $data );
        $message = 'Сотрудник обновлен';
    } else {
        $result = Arsenal_Staff_Manager::add_staff( $data );
        $message = 'Сотрудник добавлен';
        $staff_id = $result;
        $is_edit = true;
    }

    if ( $result ) {
        $staff = Arsenal_Staff_Manager::get_staff_member( $staff_id );
        echo '<div class="notice notice-success"><p>' . esc_html( $message ) . '</p></div>';
    } else {
        echo '<div class="notice notice-error"><p>Ошибка при сохранении</p></div>';
    }
}

$job_titles = Arsenal_Staff_Manager::get_job_titles( true );

?>
<div class="wrap">
    <h1><?php echo $is_edit ? '✏️ Редактирование сотрудника' : '➕ Добавление нового сотрудника'; ?></h1>

    <form method="post" class="staff-form-wrapper">
        <?php wp_nonce_field( 'arsenal_staff_nonce' ); ?>
        <input type="hidden" name="save_staff" value="1">

        <div class="staff-form-section">
            <h3>Основная информация</h3>

            <div class="form-group">
                <label for="first_name">Имя *</label>
                <input type="text" id="first_name" name="first_name" required
                       value="<?php echo esc_attr( $staff->first_name ?? '' ); ?>">
            </div>

            <div class="form-group">
                <label for="second_name">Фамилия *</label>
                <input type="text" id="second_name" name="second_name" required
                       value="<?php echo esc_attr( $staff->second_name ?? '' ); ?>">
            </div>

            <div class="form-group">
                <label for="job_title_id">Должность</label>
                <select id="job_title_id" name="job_title_id">
                    <option value="">— Не указана —</option>
                    <?php foreach ( $job_titles as $job ): ?>
                        <option value="<?php echo $job->id; ?>" 
                                <?php selected( $staff->job_title_id ?? null, $job->id ); ?>>
                            <?php echo esc_html( $job->job_title_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="birth_date">Дата рождения</label>
                    <input type="date" id="birth_date" name="birth_date"
                           value="<?php echo esc_attr( $staff->birth_date ?? '' ); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                           value="<?php echo esc_attr( $staff->email ?? '' ); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Телефон</label>
                    <input type="tel" id="phone" name="phone"
                           value="<?php echo esc_attr( $staff->phone ?? '' ); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="bio">Биография</label>
                <textarea id="bio" name="bio" rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"><?php echo esc_textarea( $staff->bio ?? '' ); ?></textarea>
            </div>

            <h3>Контракт</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="contract_start">Начало контракта</label>
                    <input type="date" id="contract_start" name="contract_start"
                           value="<?php echo esc_attr( $staff->contract_start ?? '' ); ?>">
                </div>

                <div class="form-group">
                    <label for="contract_end">Конец контракта</label>
                    <input type="date" id="contract_end" name="contract_end"
                           value="<?php echo esc_attr( $staff->contract_end ?? '' ); ?>">
                </div>
            </div>
        </div>

        <div class="staff-form-section">

            <div class="staff-photo-box">
                <?php if ( $staff->photo_url ?? null ): ?>
                    <img id="photo-preview" src="<?php echo esc_url( home_url( $staff->photo_url ) ); ?>" alt="Фото сотрудника">
                <?php else: ?>
                    <div id="photo-preview" style="display: none;"></div>
                    <p style="color: #999;">Фото не загружено</p>
                <?php endif; ?>
                
                <input type="hidden" id="photo_url" name="photo_url" 
                       value="<?php echo esc_attr( $staff->photo_url ?? '' ); ?>">
                
                <button type="button" class="button button-primary" id="upload_photo_button">
                    📤 Загрузить фото
                </button>
                
                <?php if ( $staff->photo_url ?? null ): ?>
                    <button type="button" class="button" id="remove_photo_button">
                        🗑️ Удалить фото
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="staff-form-buttons">
            <button type="submit" class="button button-primary button-large">
                💾 Сохранить
            </button>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-staff' ); ?>" class="button button-large">
                ← Назад
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadButton = document.getElementById('upload_photo_button');
    const photoInput = document.getElementById('photo_url');
    const photoPreview = document.getElementById('photo-preview');
    const removeButton = document.getElementById('remove_photo_button');

    let frame;

    uploadButton.addEventListener('click', function(e) {
        e.preventDefault();

        if (frame) {
            frame.open();
            return;
        }

        frame = wp.media({
            title: 'Выберите фото сотрудника',
            button: {
                text: 'Использовать'
            },
            multiple: false
        });

        frame.on('select', function() {
            const attachment = frame.state().get('selection').first().toJSON();
            // Сохраняем относительный путь вместо полного URL
            const fullUrl = attachment.url;
            const homeUrl = '<?php echo home_url(); ?>';
            const relativePath = fullUrl.replace(homeUrl, '');
            
            photoInput.value = relativePath;

            if (photoPreview.tagName === 'IMG') {
                photoPreview.src = fullUrl;
            } else {
                const img = document.createElement('img');
                img.src = fullUrl;
                img.id = 'photo-preview';
                photoPreview.parentNode.insertBefore(img, photoPreview);
                photoPreview.remove();
            }

            if (!removeButton) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'button';
                btn.id = 'remove_photo_button';
                btn.textContent = '🗑️ Удалить фото';
                btn.addEventListener('click', removePhoto);
                uploadButton.parentNode.insertBefore(btn, uploadButton.nextSibling);
            }
        });

        frame.open();
    });

    function removePhoto(e) {
        e.preventDefault();
        photoInput.value = '';
        photoPreview.src = '';
        photoPreview.style.display = 'none';
        if (removeButton) removeButton.remove();
    }

    if (removeButton) {
        removeButton.addEventListener('click', removePhoto);
    }
});
</script>

<style>
.staff-form-wrapper {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
    margin: 20px 0;
}

.staff-form-section {
    background: #fff;
    padding: 20px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.staff-form-section h3 {
    margin: 0 0 15px 0;
    padding-bottom: 10px;
    border-bottom: 2px solid #0073aa;
    color: #0073aa;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.staff-photo-box {
    background: #f5f5f5;
    padding: 20px;
    text-align: center;
    border-radius: 4px;
    border: 2px dashed #ccc;
    transition: all 0.3s ease;
}

.staff-photo-box:hover {
    border-color: #0073aa;
    background: #f9f9f9;
}

.staff-photo-box img {
    max-width: 100%;
    height: auto;
    max-height: 300px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin: 0 auto 10px auto;
    display: block;
}

.staff-photo-box .button {
    width: 100%;
    box-sizing: border-box;
    margin-bottom: 8px;
}

.staff-photo-box #remove_photo_button {
    background: #fff;
    border-color: #d63638;
    color: #d63638;
}

.staff-photo-box #remove_photo_button:hover {
    background: #d63638;
    color: #fff;
}

.form-group {
    margin-bottom: 15px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #333;
    font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="tel"],
.form-group input[type="date"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #0073aa;
    box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.staff-form-buttons {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding: 20px;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    justify-content: center;
    grid-column: 1 / -1;
}

.staff-form-buttons .button {
    padding: 8px 20px !important;
    font-size: 14px;
    height: auto !important;
    line-height: 1.5 !important;
}
</style>
