<?php
/**
 * Список сотрудников клуба
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

require_once get_template_directory() . '/inc/classes/class-arsenal-staff-manager.php';

// Получаем все сотрудники с фильтрацией
$job_title_id = isset( $_GET['job_title'] ) ? intval( $_GET['job_title'] ) : null;
$search = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';

$args = array(
    'job_title_id' => $job_title_id,
    'search' => $search,
    'orderby' => 'second_name',
    'order' => 'ASC',
);

$staff = Arsenal_Staff_Manager::get_staff( $args );
$job_titles = Arsenal_Staff_Manager::get_job_titles( true );
$staff_count = Arsenal_Staff_Manager::count_staff( true );

?>
<div class="wrap">
    <div class="staff-list-wrapper">
        <div class="staff-list-header">
            <h1>👔 Персонал клуба</h1>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-staff-add' ); ?>" class="button button-primary">
                ➕ Добавить сотрудника
            </a>
        </div>

        <div class="staff-list-stats">
            <div class="stat-box">
                <span class="stat-number"><?php echo count( $staff ); ?></span>
                <span class="stat-label">Всего сотрудников</span>
            </div>
        </div>

        <!-- Раздел вкладок по составам -->
        <div class="staff-list-section">
            <div class="staff-list-section-header">
                <h2>👥 Сотрудники по составам</h2>
                <a href="<?php echo admin_url( 'admin.php?page=arsenal-squad-add' ); ?>" class="button button-primary">
                    Состав/Тип команды
                </a>
            </div>

            <?php
            // Получаем все составы из wp_arsenal_squad
            $squads = $wpdb->get_results( "SELECT id, squad_name, squad_id FROM {$wpdb->prefix}arsenal_squad ORDER BY id ASC" );
            
            if ( $squads ):
            ?>
                <div class="squad-tabs-wrapper">
                    <!-- Вкладки навигации -->
                    <div class="squad-tabs-nav">
                        <button class="squad-tab-button active" data-squad-id="all">
                            👥 Все сотрудники
                        </button>
                        <?php foreach ( $squads as $index => $squad ): ?>
                            <button class="squad-tab-button" 
                                    data-squad-id="<?php echo $squad->id; ?>">
                                <?php echo esc_html( $squad->squad_name ); ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <!-- Содержимое вкладок -->
                    <div class="squad-tabs-content">
                        <?php foreach ( $squads as $index => $squad ): 
                            // Получаем сотрудников для каждого состава с JOIN на должности
                            $squad_staff = $wpdb->get_results( $wpdb->prepare(
                                "SELECT s.*, j.job_title_name 
                                 FROM {$wpdb->prefix}arsenal_staff s
                                 LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles j ON s.job_title_id = j.id
                                 WHERE s.squad_id = %d 
                                 ORDER BY s.second_name ASC",
                                $squad->id
                            ) );
                        ?>
                            <div class="squad-tab-content <?php echo $index === 0 ? 'active' : ''; ?>" 
                                 data-squad-id="<?php echo $squad->id; ?>">
                                
                                <!-- Раздел отделов -->
                                <div class="squad-departments-section">
                                    <div class="squad-departments-header">
                                        <h3>📋 Отделы</h3>
                                        <button class="button button-small squad-add-department-btn" 
                                                data-squad-id="<?php echo $squad->squad_id; ?>"
                                                data-squad-numeric-id="<?php echo $squad->id; ?>">
                                            ➕ Добавить отдел
                                        </button>
                                    </div>
                                    
                                    <?php 
                                    // Получаем отделы для этого состава
                                    $squad_departments = $wpdb->get_results( $wpdb->prepare(
                                        "SELECT * FROM {$wpdb->prefix}arsenal_staff_department WHERE squad_id = %s ORDER BY sort_order ASC, department_name ASC",
                                        $squad->squad_id
                                    ) );
                                    ?>
                                    
                                    <?php if ( $squad_departments ): ?>
                                        <div class="squad-departments-buttons">
                                            <?php foreach ( $squad_departments as $dept ): ?>
                                            <button class="button squad-dept-button" 
                                                    data-department-id="<?php echo $dept->id; ?>"
                                                    data-department-name="<?php echo esc_attr( $dept->department_name ); ?>"
                                                    data-squad-id="<?php echo $squad->squad_id; ?>">
                                                📁 <?php echo esc_html( $dept->department_name ); ?>
                                            </button>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="squad-no-departments">Отделы не созданы</p>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Раздел сотрудников -->
                                <div class="squad-staff-section">
                                    <h3>👥 Сотрудники</h3>
                                
                                <?php if ( $squad_staff ): ?>
                                    <table class="wp-list-table widefat striped">
                                        <thead>
                                            <tr>
                                                <th class="staff-col-photo">Фото</th>
                                                <th class="staff-col-name">ФИО</th>
                                                <th class="staff-col-job">Должность</th>
                                                <th class="staff-col-dept">Отдел</th>
                                                <th class="staff-col-action">Действие</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ( $squad_staff as $person ): ?>
                                            <tr class="clickable-row" data-staff-id="<?php echo $person->id; ?>" data-edit-url="<?php echo admin_url( 'admin.php?page=arsenal-staff-edit&staff_id=' . $person->id ); ?>">
                                                <td>
                                                    <?php if ( $person->photo_url ): ?>
                                                        <img src="<?php echo esc_url( $person->photo_url ); ?>" 
                                                             alt="<?php echo esc_attr( $person->first_name . ' ' . $person->second_name ); ?>"
                                                             class="staff-thumbnail">
                                                    <?php else: ?>
                                                        <div class="staff-thumbnail-empty">
                                                            <span class="dashicons dashicons-admin-users"></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo esc_html( $person->first_name . ' ' . $person->second_name ); ?></strong>
                                                    <?php if ( $person->email ): ?>
                                                        <small class="staff-email">📧 <?php echo esc_html( $person->email ); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="job-title-badge">
                                                        <?php echo esc_html( $person->job_title_name ?? '—' ); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="department-badge">
                                                        <?php 
                                                        if ( $person->department_id ) {
                                                            $dept = Arsenal_Staff_Manager::get_department( $person->department_id );
                                                            echo esc_html( $dept ? $dept->department_name : '—' );
                                                        } else {
                                                            echo '—';
                                                        }
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?php echo admin_url( 'admin.php?page=arsenal-staff-edit&staff_id=' . $person->id ); ?>" 
                                                       class="button button-small">
                                                        ✏️ Редактировать
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p class="staff-no-data">
                                        В этом составе нет сотрудников
                                    </p>
                                <?php endif; ?>
                                </div>
                                <!-- Конец раздела сотрудников -->

                                <!-- Кнопка удаления состава -->
                                <?php if ( count( $squads ) > 1 ): ?>
                                    <div class="squad-delete-footer-wrapper">
                                        <button class="button button-delete squad-delete-button-footer" 
                                                data-squad-id="<?php echo $squad->id; ?>"
                                                data-squad-name="<?php echo esc_attr( $squad->squad_name ); ?>">
                                            🗑️ Удалить состав
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <!-- Вкладка "Все сотрудники" -->
                        <div class="squad-tab-content" data-squad-id="all">
                            <div class="squad-staff-section">
                                <h3>👥 Все сотрудники</h3>
                            
                                <?php 
                                // Получаем всех сотрудников со всех составов
                                $all_staff = $wpdb->get_results(
                                    "SELECT s.*, 
                                            j.job_title_name, 
                                            d.department_name,
                                            sq.squad_name
                                     FROM {$wpdb->prefix}arsenal_staff s
                                     LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles j ON s.job_title_id = j.id
                                     LEFT JOIN {$wpdb->prefix}arsenal_staff_department d ON s.department_id = d.id
                                     LEFT JOIN {$wpdb->prefix}arsenal_squad sq ON s.squad_id = sq.id
                                     ORDER BY sq.squad_name ASC, s.second_name ASC, s.first_name ASC"
                                );
                                ?>
                                
                                <?php if ( $all_staff ): ?>
                                    <table class="wp-list-table widefat striped">
                                        <thead>
                                            <tr>
                                                <th class="staff-col-photo">Фото</th>
                                                <th class="staff-col-name">ФИО</th>
                                                <th class="staff-col-job">Должность</th>
                                                <th class="staff-col-dept">Отдел</th>
                                                <th class="staff-col-squad">Состав</th>
                                                <th class="staff-col-action">Действие</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ( $all_staff as $person ): ?>
                                            <tr class="clickable-row" data-staff-id="<?php echo $person->id; ?>" data-edit-url="<?php echo admin_url( 'admin.php?page=arsenal-staff-edit&staff_id=' . $person->id ); ?>">
                                                <td>
                                                    <?php if ( $person->photo_url ): ?>
                                                        <img src="<?php echo esc_url( $person->photo_url ); ?>" 
                                                             alt="<?php echo esc_attr( $person->first_name . ' ' . $person->second_name ); ?>"
                                                             class="staff-thumbnail">
                                                    <?php else: ?>
                                                        <div class="staff-thumbnail-empty">
                                                            <span class="dashicons dashicons-admin-users"></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo esc_html( $person->first_name . ' ' . $person->second_name ); ?></strong>
                                                    <?php if ( $person->email ): ?>
                                                        <small class="staff-email">📧 <?php echo esc_html( $person->email ); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="job-title-badge">
                                                        <?php echo esc_html( $person->job_title_name ?? '—' ); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="department-badge">
                                                        <?php echo esc_html( $person->department_name ?? '—' ); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="squad-name-badge">
                                                        <?php echo esc_html( $person->squad_name ?? '—' ); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?php echo admin_url( 'admin.php?page=arsenal-staff-edit&staff_id=' . $person->id ); ?>" 
                                                       class="button button-small">
                                                        ✏️ Редактировать
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p class="staff-no-data">
                                        Нет сотрудников в базе
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Конец вкладки "Все сотрудники" -->
                    </div>
                </div>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const tabButtons = document.querySelectorAll('.squad-tab-button');
                    const tabContents = document.querySelectorAll('.squad-tab-content');
                    const storageKey = 'arsenal_active_squad_tab';

                    // Функция активации вкладки
                    function activateTab(squadId) {
                        tabContents.forEach(c => c.classList.remove('active'));
                        tabButtons.forEach(b => b.classList.remove('active'));
                        
                        const content = document.querySelector('[data-squad-id="' + squadId + '"].squad-tab-content');
                        const button = document.querySelector('[data-squad-id="' + squadId + '"].squad-tab-button');
                        
                        if (content) content.classList.add('active');
                        if (button) button.classList.add('active');
                        
                        localStorage.setItem(storageKey, squadId);
                    }

                    // При клике на вкладку
                    tabButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            activateTab(this.dataset.squadId);
                        });
                    });

                    // При загрузке - восстанавливаем сохраненную вкладку
                    const saved = localStorage.getItem(storageKey);
                    if (saved) {
                        activateTab(saved);
                    } else {
                        // По умолчанию активируем первую вкладку (All Staff)
                        if (tabButtons.length > 0) {
                            activateTab(tabButtons[0].dataset.squadId);
                        }
                    }
                });
                </script>
            <?php else: ?>
                <p class="squad-empty-message">Составы не созданы в wp_arsenal_squad</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nonce = '<?php echo wp_create_nonce( 'arsenal_staff_nonce' ); ?>';

    // Кликабельные строки таблицы
    document.querySelectorAll('tr.clickable-row').forEach(row => {
        row.addEventListener('click', function(e) {
            // Не открываем, если клик по кнопке или ссылке
            if (e.target.tagName === 'BUTTON' || e.target.tagName === 'A' || e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            
            const editUrl = this.dataset.editUrl;
            if (editUrl) {
                window.location.href = editUrl;
            }
        });
    });

    // Удаление сотрудника
    document.querySelectorAll('[data-action="delete-staff"]').forEach(button => {
        button.addEventListener('click', function() {
            const staffId = this.dataset.staffId;
            
            if ( ! confirm('Вы уверены, что хотите удалить этого сотрудника?') ) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'arsenal_delete_staff');
            formData.append('staff_id', staffId);
            formData.append('nonce', nonce);

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if ( data.success ) {
                    alert('Сотрудник удален');
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.data);
                }
            });
        });
    });

    // Удаление должности
    document.querySelectorAll('[data-action="delete-job-title"]').forEach(button => {
        button.addEventListener('click', function() {
            const jobTitleId = this.dataset.jobTitleId;
            
            if ( ! confirm('Вы уверены, что хотите удалить эту должность?') ) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'arsenal_delete_job_title');
            formData.append('job_title_id', jobTitleId);
            formData.append('nonce', nonce);

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if ( data.success ) {
                    alert('Должность удалена');
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.data);
                }
            });
        });
    });

    // Удаление состава
    document.querySelectorAll('[data-action="delete-squad"]').forEach(button => {
        button.addEventListener('click', function() {
            const squadId = this.dataset.squadId;
            const squadName = this.dataset.squadName;
            
            if ( ! confirm('Вы уверены, что хотите удалить состав "' + squadName + '"?') ) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'arsenal_delete_squad');
            formData.append('squad_id', squadId);
            formData.append('nonce', nonce);

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if ( data.success ) {
                    alert('Состав удален');
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.data);
                }
            });
        });
    });

    // Удаление состава через кнопку рядом с вкладкой или внутри вкладки
    document.querySelectorAll('.squad-delete-button, .squad-delete-button-footer').forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            const squadId = this.dataset.squadId;
            const squadName = this.dataset.squadName;
            
            if ( ! confirm('Вы уверены, что хотите удалить состав "' + squadName + '"?') ) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'arsenal_delete_squad');
            formData.append('squad_id', squadId);
            formData.append('nonce', nonce);

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if ( data.success ) {
                    alert('Состав удален');
                    location.reload();
                } else {
                    alert('Ошибка: ' + data.data);
                }
            });
        });
    });

    // Добавление отдела
    let addDepartmentSquadId = null;
    let addDepartmentSquadNumericId = null;
    
    document.querySelectorAll('.squad-add-department-btn').forEach(button => {
        button.addEventListener('click', function() {
            addDepartmentSquadId = this.dataset.squadId;
            addDepartmentSquadNumericId = this.dataset.squadNumericId;
            document.getElementById('add-department-input').value = '';
            document.getElementById('add-department-modal').style.display = 'flex';
        });
    });

    // Закрытие модального окна добавления отдела
    document.querySelectorAll('[data-modal="add-department"]').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('add-department-modal').style.display = 'none';
        });
    });

    // Отправка формы добавления отдела
    document.querySelector('.add-department-submit-btn').addEventListener('click', function() {
        const deptName = document.getElementById('add-department-input').value.trim();
        
        if ( ! deptName ) {
            alert('Введите название отдела');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'arsenal_add_department');
        formData.append('squad_id', addDepartmentSquadId);
        formData.append('department_name', deptName);
        formData.append('nonce', nonce);

        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if ( data.success ) {
                // Сохраняем numeric ID вкладки в localStorage перед перезагрузкой
                localStorage.setItem('arsenal_active_squad_tab', addDepartmentSquadNumericId);
                // Очищаем форму для нового отдела
                document.getElementById('add-department-input').value = '';
                // Перезагружаем страницу чтобы отдел появился в списке
                location.reload();
            } else {
                alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
            }
        });
    });

    // Закрытие модального окна при клике на фон
    document.getElementById('add-department-modal').addEventListener('click', function(e) {
        if ( e.target === this ) {
            this.style.display = 'none';
        }
    });

    // Удаление отдела
    document.querySelectorAll('.squad-delete-department-btn').forEach(button => {
        button.addEventListener('click', function() {
            const squadId = this.dataset.squadId;
            const departmentId = this.dataset.departmentId;
            const departmentName = this.dataset.departmentName;
            
            if ( ! confirm('Вы уверены, что хотите удалить отдел "' + departmentName + '"?') ) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'arsenal_delete_department');
            formData.append('squad_id', squadId);
            formData.append('department_id', departmentId);
            formData.append('nonce', nonce);

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if ( data.success ) {
                    alert('Отдел удален');
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                }
            });
        });
    });

    // Открытие модального окна отдела
    const modal = document.getElementById('department-modal');
    const modalClose = document.querySelector('.department-modal-close');
    let currentDepartmentId = null;
    let currentSquadId = null;

    document.querySelectorAll('.squad-dept-button').forEach(button => {
        button.addEventListener('click', function() {
            const departmentId = this.dataset.departmentId;
            const departmentName = this.dataset.departmentName;
            const squadId = this.dataset.squadId;

            currentDepartmentId = departmentId;
            currentSquadId = squadId;

            // Обновляем заголовок модала
            document.getElementById('modal-department-name').textContent = departmentName;

            // Получаем должности этого отдела
            fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'arsenal_get_department_jobs',
                    department_id: departmentId,
                    nonce: nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                const jobsList = document.getElementById('modal-job-titles-list');
                jobsList.innerHTML = '';

                if ( data.success && data.data.length > 0 ) {
                    data.data.forEach(job => {
                        const li = document.createElement('li');
                        li.className = 'modal-job-title-item';
                        li.innerHTML = '<span>📌 ' + job.job_title_name + '</span><button class="button button-small modal-delete-job-btn" data-job-id="' + job.id + '">🗑️</button>';
                        jobsList.appendChild(li);
                        attachDeleteJobHandler(li.querySelector('.modal-delete-job-btn'));
                    });
                } else {
                    const li = document.createElement('li');
                    li.className = 'modal-no-jobs';
                    li.textContent = 'В этом отделе нет должностей';
                    jobsList.appendChild(li);
                }

                // Очищаем форму добавления
                document.getElementById('new-job-title').value = '';

                // Показываем модал
                modal.style.display = 'flex';
            });
        });
    });

    // Закрытие модального окна отдела на крестик
    document.querySelectorAll('.department-modal-close').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('department-modal').style.display = 'none';
            document.getElementById('add-department-modal').style.display = 'none';
        });
    });

    // Закрытие модального окна при клике на фон
    document.getElementById('department-modal').addEventListener('click', function(e) {
        if ( e.target === this ) {
            this.style.display = 'none';
        }
    });

    // Добавление должности в модальном окне
    document.querySelector('.modal-add-job-btn').addEventListener('click', function() {
        const jobTitle = document.getElementById('new-job-title').value.trim();
        const jobTitlePlural = document.getElementById('new-job-title-plural').value.trim();
        
        if ( ! jobTitle ) {
            alert('Введите название должности (единственное число)');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'arsenal_add_job_title');
        formData.append('department_id', currentDepartmentId);
        formData.append('job_title_name', jobTitle);
        formData.append('job_title_name_plural', jobTitlePlural);
        formData.append('nonce', nonce);

        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if ( data.success ) {
                document.getElementById('new-job-title').value = '';
                document.getElementById('new-job-title-plural').value = '';
                // Обновляем список должностей
                fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                    method: 'POST',
                    body: new URLSearchParams({
                        action: 'arsenal_get_department_jobs',
                        department_id: currentDepartmentId,
                        nonce: nonce
                    })
                })
                .then(response => response.json())
                .then(data => {
                    const jobsList = document.getElementById('modal-job-titles-list');
                    jobsList.innerHTML = '';

                    if ( data.success && data.data.length > 0 ) {
                        data.data.forEach(job => {
                            const li = document.createElement('li');
                            li.className = 'modal-job-title-item';
                            li.innerHTML = '<span>📌 ' + job.job_title_name + '</span><button class="button button-small modal-delete-job-btn" data-job-id="' + job.id + '">🗑️</button>';
                            jobsList.appendChild(li);
                            attachDeleteJobHandler(li.querySelector('.modal-delete-job-btn'));
                        });
                    } else {
                        const li = document.createElement('li');
                        li.className = 'modal-no-jobs';
                        li.textContent = 'В этом отделе нет должностей';
                        jobsList.appendChild(li);
                    }
                });
            } else {
                alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
            }
        });
    });

    // Функция для подключения обработчика удаления должности
    function attachDeleteJobHandler(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const jobId = this.dataset.jobId;
            
            if ( ! confirm('Вы уверены, что хотите удалить эту должность?') ) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'arsenal_delete_job_title');
            formData.append('job_title_id', jobId);
            formData.append('nonce', nonce);

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if ( data.success ) {
                    // Обновляем список должностей
                    fetch('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
                        method: 'POST',
                        body: new URLSearchParams({
                            action: 'arsenal_get_department_jobs',
                            department_id: currentDepartmentId,
                            nonce: nonce
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        const jobsList = document.getElementById('modal-job-titles-list');
                        jobsList.innerHTML = '';

                        if ( data.success && data.data.length > 0 ) {
                            data.data.forEach(job => {
                                const li = document.createElement('li');
                                li.className = 'modal-job-title-item';
                                li.innerHTML = '<span>📌 ' + job.job_title_name + '</span><button class="button button-small modal-delete-job-btn" data-job-id="' + job.id + '">🗑️</button>';
                                jobsList.appendChild(li);
                                attachDeleteJobHandler(li.querySelector('.modal-delete-job-btn'));
                            });
                        } else {
                            const li = document.createElement('li');
                            li.className = 'modal-no-jobs';
                            li.textContent = 'В этом отделе нет должностей';
                            jobsList.appendChild(li);
                        }
                    });
                } else {
                    alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                }
            });
        });
    }

    // Закрытие модального окна
    modalClose.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Редактирование названия отдела
    let editDeptModal = document.getElementById('edit-department-name-modal');
    let editDeptNameBtn = document.getElementById('edit_department_name_btn');
    let editDeptInput = document.getElementById('edit_department_name_input');
    let saveDeptBtn = document.getElementById('save-edit-dept-btn');
    let cancelDeptBtn = document.getElementById('cancel-edit-dept-btn');
    let closeDeptModalBtn = document.getElementById('close-edit-dept-modal');
    
    if ( editDeptNameBtn ) {
        editDeptNameBtn.addEventListener('click', function() {
            const currentName = document.getElementById('modal-department-name').textContent;
            editDeptInput.value = currentName;
            editDeptModal.style.display = 'flex';
        });
    }
    
    if ( closeDeptModalBtn ) {
        closeDeptModalBtn.addEventListener('click', function() {
            editDeptModal.style.display = 'none';
        });
    }
    
    if ( cancelDeptBtn ) {
        cancelDeptBtn.addEventListener('click', function() {
            editDeptModal.style.display = 'none';
        });
    }
    
    if ( saveDeptBtn ) {
        saveDeptBtn.addEventListener('click', function() {
            const newName = editDeptInput.value.trim();
            
            if ( ! newName ) {
                alert('Введите название отдела');
                return;
            }
            
            const formData = new FormData();
            formData.append('action', 'arsenal_update_department_name');
            formData.append('department_id', currentDepartmentId);
            formData.append('department_name', newName);
            formData.append('nonce', nonce);
            
            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if ( data.success ) {
                    document.getElementById('modal-department-name').textContent = newName;
                    editDeptModal.style.display = 'none';
                    alert('Отдел обновлен');
                } else {
                    alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                }
            });
        });
    }
    
    // Закрытие модала при клике вне окна
    if ( editDeptModal ) {
        editDeptModal.addEventListener('click', function(e) {
            if ( e.target === this ) {
                this.style.display = 'none';
            }
        });
    }

    // Удаление отдела из модального окна
    document.querySelector('.modal-delete-department-btn').addEventListener('click', function() {
        if ( ! confirm('Вы уверены, что хотите удалить этот отдел?') ) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'arsenal_delete_department');
        formData.append('squad_id', currentSquadId);
        formData.append('department_id', currentDepartmentId);
        formData.append('nonce', nonce);

        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if ( data.success ) {
                alert('Отдел удален');
                location.reload();
            } else {
                alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
            }
        });
    });

    // Закрытие модала при клике вне окна
    modal.addEventListener('click', function(e) {
        if ( e.target === modal ) {
            modal.style.display = 'none';
        }
    });
});
</script>

<!-- Модальное окно для добавления отдела -->
<div id="add-department-modal" class="department-modal" style="display: none;">
    <div class="department-modal-content">
        <div class="department-modal-header">
            <h3>➕ Добавить новый отдел</h3>
            <button class="department-modal-close" data-modal="add-department">&times;</button>
        </div>
        <div class="department-modal-body">
            <div class="form-group">
                <label for="add-department-input">Название отдела *</label>
                <input 
                    type="text" 
                    id="add-department-input" 
                    class="form-control" 
                    placeholder="Например: Тренерский штаб"
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px;"
                />
            </div>
        </div>
        <div class="department-modal-footer">
            <button class="button button-secondary" data-modal="add-department">Отмена</button>
            <button class="button button-primary add-department-submit-btn">➕ Добавить отдел</button>
        </div>
    </div>
</div>

<!-- Модальное окно для отдела -->
<div id="department-modal" class="department-modal" style="display: none;">
    <div class="department-modal-content">
        <div class="department-modal-header">
            <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                <h3 id="modal-department-name" style="margin: 0;"></h3>
                <button type="button" id="edit_department_name_btn" style="height: 32px; width: 32px; padding: 0; cursor: pointer; border: 1px solid #ccc; border-radius: 4px; background: #f5f5f5; font-size: 16px; display: flex; align-items: center; justify-content: center;">
                    ✏️
                </button>
            </div>
            <button class="department-modal-close">&times;</button>
        </div>
        <div class="department-modal-body">
            <h4 style="margin-top: 0; margin-bottom: 15px;">Добавление должностей</h4>
            <div class="modal-job-form">
                <div style="margin-bottom: 12px;">
                    <input 
                        type="text" 
                        id="new-job-title" 
                        class="modal-job-input" 
                        placeholder="Единственное число (напр. Массажист)"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 8px;"
                    />
                    <input 
                        type="text" 
                        id="new-job-title-plural" 
                        class="modal-job-input" 
                        placeholder="Множественное число (напр. Массажисты)"
                        style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 8px;"
                    />
                    <small style="color: #666; display: block; margin-bottom: 10px;">ℹ️ Множественное число используется на странице персонала состава/команды для группировки сотрудников</small>
                </div>
                <button class="button button-primary modal-add-job-btn">➕ Добавить должность</button>
            </div>
            <ul id="modal-job-titles-list" class="modal-job-titles-list"></ul>
        </div>
        <div class="department-modal-footer">
            <button class="button button-delete modal-delete-department-btn">🗑️ Удалить отдел</button>
        </div>
    </div>
</div>

<!-- Модальное окно для редактирования названия отдела -->
<div id="edit-department-name-modal" class="department-modal" style="display: none;">
    <div class="department-modal-content">
        <div class="department-modal-header">
            <h3>✏️ Редактирование названия отдела</h3>
            <button class="department-modal-close" id="close-edit-dept-modal">&times;</button>
        </div>
        <div class="department-modal-body">
            <div class="form-group">
                <label for="edit_department_name_input">Название отдела *</label>
                <input 
                    type="text" 
                    id="edit_department_name_input" 
                    class="form-control" 
                    placeholder="Например: Тренерский штаб"
                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px;"
                />
            </div>
        </div>
        <div class="department-modal-footer">
            <button class="button button-secondary" id="cancel-edit-dept-btn">Отмена</button>
            <button class="button button-primary" id="save-edit-dept-btn">💾 Сохранить</button>
        </div>
    </div>
</div>
