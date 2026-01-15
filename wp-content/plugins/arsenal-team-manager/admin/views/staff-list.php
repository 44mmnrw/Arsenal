<?php
/**
 * Список сотрудников клуба
 */

if ( ! defined( 'ABSPATH' ) ) exit;

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

        <!-- Фильтры -->
        <div class="staff-filters">
            <form method="get" class="staff-filters-form">
                <input type="hidden" name="page" value="arsenal-staff">
                
                <div class="filter-group">
                    <label for="filter-job-title">Должность:</label>
                    <select name="job_title" id="filter-job-title">
                        <option value="">— Все должности —</option>
                        <?php foreach ( $job_titles as $job ): ?>
                            <option value="<?php echo $job->id; ?>" <?php selected( $job_title_id, $job->id ); ?>>
                                <?php echo esc_html( $job->job_title_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter-search">Поиск:</label>
                    <input type="text" name="s" id="filter-search" value="<?php echo esc_attr( $search ); ?>" 
                           placeholder="Имя, фамилия...">
                </div>

                <button type="submit" class="button">Фильтр</button>
                <a href="<?php echo admin_url( 'admin.php?page=arsenal-staff' ); ?>" class="button">Очистить</a>
            </form>
        </div>

        <!-- Таблица сотрудников -->
        <div class="staff-list-container">
            <div class="staff-table">
                <div class="staff-row staff-header">
                    <div class="staff-col-photo">Фото</div>
                    <div class="staff-col-name">ФИО</div>
                    <div class="staff-col-job">Должность</div>
                    <div class="staff-col-dept">Отдел</div>
                    <div class="staff-col-contract">Контракт</div>
                    <div class="staff-col-action">Действие</div>
                </div>

                <?php if ( $staff ): ?>
                    <?php foreach ( $staff as $person ): ?>
                    <div class="staff-row">
                        <div class="staff-col-photo">
                            <?php if ( $person->photo_url ): ?>
                                <img src="<?php echo esc_url( $person->photo_url ); ?>" 
                                     alt="<?php echo esc_attr( $person->first_name . ' ' . $person->second_name ); ?>"
                                     class="staff-thumbnail">
                            <?php else: ?>
                                <div class="staff-thumbnail-empty">
                                    <span class="dashicons dashicons-admin-users"></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="staff-col-name">
                            <a href="<?php echo admin_url( 'admin.php?page=arsenal-staff-edit&staff_id=' . $person->id ); ?>" 
                               class="staff-name-link">
                                <?php echo esc_html( $person->first_name . ' ' . $person->second_name ); ?>
                            </a>
                            <?php if ( $person->email ): ?>
                                <br><small style="color: #666;">📧 <?php echo esc_html( $person->email ); ?></small>
                            <?php endif; ?>
                        </div>
                        <div class="staff-col-job">
                            <span class="job-title-badge">
                                <?php echo esc_html( $person->job_title_name ?? '—' ); ?>
                            </span>
                        </div>
                        <div class="staff-col-dept">
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
                        </div>
                        <div class="staff-col-contract">
                            <?php if ( $person->contract_start && $person->contract_end ): ?>
                                <small>
                                    📅 <?php echo wp_date( 'd.m.Y', strtotime( $person->contract_start ) ); ?> — 
                                    <?php echo wp_date( 'd.m.Y', strtotime( $person->contract_end ) ); ?>
                                </small>
                            <?php else: ?>
                                <small style="color: #999;">—</small>
                            <?php endif; ?>
                        </div>
                        <div class="staff-col-action">
                            <a href="<?php echo admin_url( 'admin.php?page=arsenal-staff-edit&staff_id=' . $person->id ); ?>" 
                               class="button button-small">
                                ✏️ Редактировать
                            </a>
                            <button class="button button-small button-delete" 
                                    data-staff-id="<?php echo $person->id; ?>"
                                    data-action="delete-staff">
                                🗑️ Удалить
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="staff-row staff-empty">
                        <div class="staff-col-full">
                            <p style="text-align: center; color: #999; padding: 20px;">
                                Сотрудников не найдено
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Раздел должностей -->
        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #ccc;">
            <h2>📋 Должности</h2>
            
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-job-title-add' ); ?>" class="button button-primary">
                ➕ Добавить должность
            </a>

            <div class="job-titles-list" style="margin-top: 20px;">
                <?php 
                $all_job_titles = Arsenal_Staff_Manager::get_job_titles();
                if ( $all_job_titles ):
                ?>
                    <table class="widefat striped">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Название должности</th>
                                <th style="width: 40%;">Описание</th>
                                <th style="width: 20%;">Действие</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $all_job_titles as $job ): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html( $job->job_title_name ); ?></strong>
                                </td>
                                <td>
                                    <?php echo esc_html( $job->description ?: '—' ); ?>
                                </td>
                                <td>
                                    <a href="<?php echo admin_url( 'admin.php?page=arsenal-job-title-edit&job_title_id=' . $job->id ); ?>" 
                                       class="button button-small">
                                        ✏️ Редактировать
                                    </a>
                                    <button class="button button-small button-delete" 
                                            data-job-title-id="<?php echo $job->id; ?>"
                                            data-action="delete-job-title">
                                        🗑️ Удалить
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="color: #999;">Должностей не создано</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nonce = '<?php echo wp_create_nonce( 'arsenal_staff_nonce' ); ?>';

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
});
</script>
