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
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                    <label for="department_id" style="margin: 0; font-weight: 600; color: #333; font-size: 14px;">Отдел</label>
                    <button type="button" id="btn-add-department" class="button button-small" 
                            style="padding: 4px 12px; font-size: 12px; white-space: nowrap;">
                        ➕ Добавить
                    </button>
                    <button type="button" id="btn-edit-department" class="button button-small" 
                            style="padding: 4px 12px; font-size: 12px; white-space: nowrap;">
                        ✏️ Редактировать
                    </button>
                </div>
                <select id="department_id" name="department_id"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box;">
                    <option value="">— Не указан —</option>
                    <?php foreach ( $departments as $dept ): ?>
                        <option value="<?php echo $dept->id; ?>"
                                <?php selected( $job_title->department_id ?? null, $dept->id ); ?>
                                data-department-id="<?php echo $dept->id; ?>">
                            <?php echo esc_html( $dept->department_name ); ?>
                            <button type="button" class="btn-delete-department" data-dept-id="<?php echo $dept->id; ?>" 
                                    style="display:none;">✕</button>
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

<!-- Модальное окно для добавления отдела -->
<div id="modal-add-department" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; flex-direction: column; justify-content: center; align-items: center;">
    <div style="background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 5px 20px rgba(0,0,0,0.3);">
        <h2 style="margin-top: 0; margin-bottom: 20px;">➕ Добавить новый отдел</h2>
        
        <form id="form-add-department" style="display: flex; flex-direction: column; gap: 15px;">
            <div>
                <label for="new_department_name" style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Название отдела *</label>
                <input type="text" id="new_department_name" name="department_name" required
                       placeholder="Например: Тренерский штаб, Медицина, Администрация"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 14px;">
            </div>

            <div>
                <label for="new_department_description" style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Описание</label>
                <textarea id="new_department_description" name="description" rows="3"
                          placeholder="Опишите функции и ответственность отдела"
                          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-family: inherit; font-size: 14px;"></textarea>
            </div>

            <div id="department-message" style="padding: 10px; border-radius: 4px; display: none;"></div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                <button type="button" id="btn-cancel-department" class="button" 
                        style="padding: 8px 20px;">Отмена</button>
                <button type="submit" class="button button-primary" 
                        style="padding: 8px 20px;">💾 Добавить отдел</button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для редактирования отдела -->
<div id="modal-edit-department" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; flex-direction: column; justify-content: center; align-items: center;">
    <div style="background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto; box-shadow: 0 5px 20px rgba(0,0,0,0.3);">
        <h2 style="margin-top: 0; margin-bottom: 20px;">✏️ Редактировать отдел</h2>
        
        <form id="form-edit-department" style="display: flex; flex-direction: column; gap: 15px;">
            <input type="hidden" id="edit_department_id" name="department_id">
            
            <div>
                <label for="edit_department_name" style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Название отдела *</label>
                <input type="text" id="edit_department_name" name="department_name" required
                       placeholder="Название отдела"
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-size: 14px;">
            </div>

            <div>
                <label for="edit_department_description" style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Описание</label>
                <textarea id="edit_department_description" name="description" rows="3"
                          placeholder="Опишите функции и ответственность отдела"
                          style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; font-family: inherit; font-size: 14px;"></textarea>
            </div>

            <div id="edit-department-message" style="padding: 10px; border-radius: 4px; display: none;"></div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 10px;">
                <button type="button" id="btn-cancel-edit-department" class="button" 
                        style="padding: 8px 20px;">Отмена</button>
                <button type="submit" class="button button-primary" 
                        style="padding: 8px 20px;">💾 Сохранить</button>
            </div>
        </form>
    </div>
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
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
    font-size: 14px;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-sizing: border-box;
    font-size: 14px;
    font-family: inherit;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #0073aa;
    box-shadow: 0 0 0 2px rgba(0, 115, 170, 0.1);
}

.form-group input[type="checkbox"] {
    margin: 0;
    cursor: pointer;
}

.form-group small {
    color: #666;
    display: block;
    margin-top: 5px;
    font-size: 13px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAddDept = document.getElementById('btn-add-department');
    const btnEditDept = document.getElementById('btn-edit-department');
    const modalAdd = document.getElementById('modal-add-department');
    const modalEdit = document.getElementById('modal-edit-department');
    const formAdd = document.getElementById('form-add-department');
    const formEdit = document.getElementById('form-edit-department');
    const btnCancelAdd = document.getElementById('btn-cancel-department');
    const btnCancelEdit = document.getElementById('btn-cancel-edit-department');
    const messageDiv = document.getElementById('department-message');
    const editMessageDiv = document.getElementById('edit-department-message');
    const selectDept = document.getElementById('department_id');

    // ===== ДОБАВЛЕНИЕ ОТДЕЛА =====
    btnAddDept.addEventListener('click', function(e) {
        e.preventDefault();
        modalAdd.style.display = 'flex';
        document.getElementById('new_department_name').focus();
    });

    btnCancelAdd.addEventListener('click', function() {
        modalAdd.style.display = 'none';
        formAdd.reset();
        messageDiv.style.display = 'none';
    });

    modalAdd.addEventListener('click', function(e) {
        if (e.target === modalAdd) {
            modalAdd.style.display = 'none';
            formAdd.reset();
            messageDiv.style.display = 'none';
        }
    });

    formAdd.addEventListener('submit', function(e) {
        e.preventDefault();
        const deptName = document.getElementById('new_department_name').value.trim();
        const deptDesc = document.getElementById('new_department_description').value.trim();

        if (!deptName) {
            showMessage(messageDiv, 'Название отдела обязательно', 'error');
            return;
        }

        fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'arsenal_add_department',
                nonce: '<?php echo wp_create_nonce('arsenal_add_department'); ?>',
                department_name: deptName,
                description: deptDesc
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(messageDiv, '✅ Отдел добавлен успешно!', 'success');
                const newOption = document.createElement('option');
                newOption.value = data.data.department_id;
                newOption.textContent = deptName;
                newOption.selected = true;
                selectDept.appendChild(newOption);
                
                setTimeout(() => {
                    modalAdd.style.display = 'none';
                    formAdd.reset();
                    messageDiv.style.display = 'none';
                }, 1500);
            } else {
                showMessage(messageDiv, '❌ Ошибка: ' + (data.data || 'Не удалось добавить отдел'), 'error');
            }
        })
        .catch(error => {
            showMessage(messageDiv, '❌ Ошибка сети: ' + error.message, 'error');
        });
    });

    // ===== РЕДАКТИРОВАНИЕ ОТДЕЛА =====
    btnEditDept.addEventListener('click', function(e) {
        e.preventDefault();
        const selectedId = selectDept.value;
        
        if (!selectedId) {
            alert('Пожалуйста, выберите отдел для редактирования');
            return;
        }

        // Получаем данные отдела через AJAX
        fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'arsenal_get_department',
                nonce: '<?php echo wp_create_nonce('arsenal_get_department'); ?>',
                department_id: selectedId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_department_id').value = data.data.id;
                document.getElementById('edit_department_name').value = data.data.department_name;
                document.getElementById('edit_department_description').value = data.data.description || '';
                modalEdit.style.display = 'flex';
                document.getElementById('edit_department_name').focus();
            } else {
                alert('❌ Ошибка загрузки данных отдела');
            }
        })
        .catch(error => {
            alert('❌ Ошибка сети: ' + error.message);
        });
    });

    btnCancelEdit.addEventListener('click', function() {
        modalEdit.style.display = 'none';
        formEdit.reset();
        editMessageDiv.style.display = 'none';
    });

    modalEdit.addEventListener('click', function(e) {
        if (e.target === modalEdit) {
            modalEdit.style.display = 'none';
            formEdit.reset();
            editMessageDiv.style.display = 'none';
        }
    });

    formEdit.addEventListener('submit', function(e) {
        e.preventDefault();
        const deptId = document.getElementById('edit_department_id').value;
        const deptName = document.getElementById('edit_department_name').value.trim();
        const deptDesc = document.getElementById('edit_department_description').value.trim();

        if (!deptName) {
            showMessage(editMessageDiv, 'Название отдела обязательно', 'error');
            return;
        }

        fetch(ajaxurl || '/wp-admin/admin-ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'arsenal_update_department',
                nonce: '<?php echo wp_create_nonce('arsenal_update_department'); ?>',
                department_id: deptId,
                department_name: deptName,
                description: deptDesc
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(editMessageDiv, '✅ Отдел обновлен успешно!', 'success');
                
                // Обновляем текст в dropdown
                const option = selectDept.querySelector(`option[value="${deptId}"]`);
                if (option) {
                    option.textContent = deptName;
                }
                
                setTimeout(() => {
                    modalEdit.style.display = 'none';
                    formEdit.reset();
                    editMessageDiv.style.display = 'none';
                }, 1500);
            } else {
                showMessage(editMessageDiv, '❌ Ошибка: ' + (data.data || 'Не удалось обновить отдел'), 'error');
            }
        })
        .catch(error => {
            showMessage(editMessageDiv, '❌ Ошибка сети: ' + error.message, 'error');
        });
    });

    function showMessage(el, text, type) {
        el.textContent = text;
        el.className = 'notice notice-' + type;
        el.style.display = 'block';
    }
});
</script>
