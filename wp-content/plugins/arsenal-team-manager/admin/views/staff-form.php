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

$job_titles = Arsenal_Staff_Manager::get_job_titles( true );
$departments = Arsenal_Staff_Manager::get_departments( true );

?>
<div class="wrap">
    <h1><?php echo $is_edit ? '✏️ Редактирование сотрудника' : '➕ Добавление нового сотрудника'; ?></h1>

    <form method="post" class="staff-form-wrapper">
        <?php wp_nonce_field( 'arsenal_staff_nonce' ); ?>
        <input type="hidden" name="save_staff" value="1">
        <?php if ( $is_edit ): ?>
            <input type="hidden" name="staff_id" value="<?php echo intval( $staff_id ); ?>">
        <?php endif; ?>

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

            <div class="form-row">
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

                <div class="form-group">
                    <label for="department_id">Отдел</label>
                    <select id="department_id" name="department_id">
                        <option value="">— Не указан —</option>
                        <?php foreach ( $departments as $dept ): ?>
                            <option value="<?php echo $dept->id; ?>" 
                                    <?php selected( $staff->department_id ?? null, $dept->id ); ?>>
                                <?php echo esc_html( $dept->department_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="club_type">Тип клуба</label>
                    <select id="club_type" name="club_type">
                        <option value="Основной клуб" <?php selected( $staff->club_type ?? 'Основной клуб', 'Основной клуб' ); ?>>Основной клуб</option>
                        <option value="СДЮШ" <?php selected( $staff->club_type ?? 'Основной клуб', 'СДЮШ' ); ?>>СДЮШ</option>
                    </select>
                </div>
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

            <div class="form-group">
                <label for="experience">Опыт работы (лет)</label>
                <input type="number" id="experience" name="experience" min="0" max="100"
                       value="<?php echo esc_attr( $staff->experience ?? '' ); ?>">
            </div>

            <div class="form-group">
                <label for="citizenship">Гражданство</label>
                <input type="text" id="citizenship" name="citizenship" placeholder="Например: Беларусь"
                       value="<?php echo esc_attr( $staff->citizenship ?? '' ); ?>">
            </div>

            <div class="form-group">
                <label for="interesting_fact">Интересный факт</label>
                <textarea id="interesting_fact" name="interesting_fact" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"><?php echo esc_textarea( $staff->interesting_fact ?? '' ); ?></textarea>
            </div>

            <div class="form-group">
                <label for="achievements">Достижения <span style="color: #999; font-size: 12px;">(по одному на строку)</span></label>
                <textarea id="achievements" name="achievements" rows="5" placeholder="Чемпион Беларуси&#10;Лучший тренер сезона&#10;3 Кубка Беларуси"
                          style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"><?php 
                    $achievements_array = json_decode( $staff->achievements ?? 'null', true );
                    echo esc_textarea( is_array( $achievements_array ) ? implode( "\n", $achievements_array ) : '' ); 
                ?></textarea>
            </div>

            <div class="form-group">
                <label for="career_positions_text">История должностей <span style="color: #999; font-size: 12px;">(Должность | Организация | Опыт/Период)</span></label>
                <textarea id="career_positions_text" name="career_positions_text" rows="5" placeholder="Главный тренер | ФК Арсенал Дзержинск | 2020-2024&#10;Главный тренер | ФК Динамо | 2015-2020"
                          style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"><?php 
                    $career_positions = array();
                    if ( ! empty( $staff->career_positions ) ) {
                        $career_json = json_decode( $staff->career_positions, true );
                        $career_positions = is_array( $career_json ) ? $career_json : array();
                    }
                    $career_lines = array();
                    foreach ( $career_positions as $position ) {
                        $career_lines[] = implode( ' | ', array(
                            $position['title'] ?? '',
                            $position['organization'] ?? '',
                            $position['experience'] ?? '',
                        ));
                    }
                    echo esc_textarea( implode( "\n", $career_lines ) );
                ?></textarea>
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
                    <img id="photo-preview" src="<?php echo esc_url( home_url( $staff->photo_url ) . '?v=' . time() ); ?>" alt="Фото сотрудника">
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

            // Добавляем timestamp для избежания кэширования
            const urlWithVersion = fullUrl + '?v=' + Date.now();

            if (photoPreview.tagName === 'IMG') {
                photoPreview.src = urlWithVersion;
            } else {
                const img = document.createElement('img');
                img.src = urlWithVersion;
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
        
        if (photoPreview.tagName === 'IMG') {
            photoPreview.style.display = 'none';
        } else {
            photoPreview.style.display = 'none';
        }
        
        // Показать текст "Фото не загружено"
        const emptyText = document.createElement('p');
        emptyText.textContent = 'Фото не загружено';
        emptyText.style.color = '#999';
        emptyText.id = 'photo-empty-text';
        
        if (!document.getElementById('photo-empty-text')) {
            photoPreview.parentNode.insertBefore(emptyText, photoPreview.nextSibling);
        }
        
        // Скрыть кнопку удаления
        if (removeButton) {
            removeButton.style.display = 'none';
        }
    }

    if (removeButton) {
        removeButton.addEventListener('click', removePhoto);
    }
});
</script>

<script>
(function($) {
    'use strict';
    
    var originalDepartmentValue = $('#department_id').val();
    
    $(document).ready(function() {
        var $jobTitleSelect = $('#job_title_id');
        var $departmentSelect = $('#department_id');
        
        // Сохранить оригинальное значение при загрузке
        originalDepartmentValue = $departmentSelect.val();
        
        // При изменении должности - автоматически подставить её отдел
        $jobTitleSelect.on('change', function() {
            var jobTitleId = $(this).val();
            
            if ( ! jobTitleId ) {
                // Если должность не выбрана - очистить отдел и разблокировать
                $departmentSelect.val( '' );
                $departmentSelect.prop('disabled', false);
                $departmentSelect.next('.dept-hint').remove();
                return;
            }
            
            // AJAX запрос для получения department_id должности
            $.ajax({
                url: '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>',
                type: 'POST',
                data: {
                    action: 'arsenal_get_job_title_department',
                    nonce: '<?php echo wp_create_nonce( 'arsenal_nonce' ); ?>',
                    job_title_id: jobTitleId
                },
                success: function( response ) {
                    if ( response.success && response.data.department_id ) {
                        // Если у должности есть отдел - подставить и заблокировать
                        $departmentSelect.val( response.data.department_id );
                        $departmentSelect.prop('disabled', true);
                        
                        // Показать подсказку
                        var $hint = $departmentSelect.next('.dept-hint');
                        if ( ! $hint.length ) {
                            $departmentSelect.after(
                                '<small class="dept-hint" style="color: #666; display: block; margin-top: 5px;">' +
                                'Отдел автоматически подставлен на основе должности' +
                                '</small>'
                            );
                        }
                    } else {
                        // Если у должности нет отдела - разблокировать выбор
                        $departmentSelect.prop('disabled', false);
                        $departmentSelect.next('.dept-hint').remove();
                    }
                },
                error: function() {
                    console.log('Ошибка при получении отдела должности');
                    $departmentSelect.prop('disabled', false);
                }
            });
        });
        
        // Выполнить при загрузке страницы ТОЛЬКО если должность уже выбрана
        if ( $jobTitleSelect.val() ) {
            $jobTitleSelect.trigger('change');
        } else {
            // Если должность не выбрана - убедиться что отдел разблокирован
            $departmentSelect.prop('disabled', false);
            $departmentSelect.next('.dept-hint').remove();
        }
    });
})(jQuery);
</script>
