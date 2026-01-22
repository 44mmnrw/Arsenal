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
        <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #ccc;">
            <h2>👥 Сотрудники по составам</h2>

            <?php
            // Получаем все составы из wp_arsenal_squad
            $squads = $wpdb->get_results( "SELECT id, squad_name FROM {$wpdb->prefix}arsenal_squad ORDER BY id ASC" );
            
            if ( $squads ):
            ?>
                <div class="squad-tabs-wrapper">
                    <!-- Вкладки навигации -->
                    <div class="squad-tabs-nav" style="display: flex; border-bottom: 2px solid #ddd; margin-bottom: 20px; gap: 5px;">
                        <?php foreach ( $squads as $index => $squad ): ?>
                            <button class="squad-tab-button" 
                                    data-squad-id="<?php echo $squad->id; ?>"
                                    style="padding: 10px 20px; border: 2px solid #ddd; background: #f5f5f5; cursor: pointer; border-bottom: none; border-radius: 5px 5px 0 0; font-weight: <?php echo $index === 0 ? 'bold' : 'normal'; ?>; background: <?php echo $index === 0 ? '#fff' : '#f5f5f5'; ?>;">
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
                            <div class="squad-tab-content" 
                                 data-squad-id="<?php echo $squad->id; ?>"
                                 style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;">
                                
                                <?php if ( $squad_staff ): ?>
                                    <table class="wp-list-table widefat striped">
                                        <thead>
                                            <tr>
                                                <th style="width: 15%;">Фото</th>
                                                <th style="width: 25%;">ФИО</th>
                                                <th style="width: 20%;">Должность</th>
                                                <th style="width: 20%;">Отдел</th>
                                                <th style="width: 20%;">Действие</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ( $squad_staff as $person ): ?>
                                            <tr>
                                                <td>
                                                    <?php if ( $person->photo_url ): ?>
                                                        <img src="<?php echo esc_url( $person->photo_url ); ?>" 
                                                             alt="<?php echo esc_attr( $person->first_name . ' ' . $person->second_name ); ?>"
                                                             class="staff-thumbnail"
                                                             style="width: 50px; height: 50px; border-radius: 5px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="staff-thumbnail-empty" style="width: 50px; height: 50px; background: #ddd; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                                            <span class="dashicons dashicons-admin-users"></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo esc_html( $person->first_name . ' ' . $person->second_name ); ?></strong>
                                                    <?php if ( $person->email ): ?>
                                                        <br><small style="color: #666;">📧 <?php echo esc_html( $person->email ); ?></small>
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
                                    <p style="padding: 20px; text-align: center; color: #999; background: #f9f9f9; border-radius: 5px;">
                                        В этом составе нет сотрудников
                                    </p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <style>
                    .squad-tabs-nav {
                        flex-wrap: wrap;
                    }
                    
                    .squad-tab-button {
                        transition: all 0.2s ease;
                    }
                    
                    .squad-tab-button:hover {
                        background-color: #e8e8e8 !important;
                    }
                    
                    .squad-tab-button.active {
                        background-color: #fff !important;
                        font-weight: bold;
                        border-bottom: 2px solid #0073aa !important;
                        border-bottom-color: #fff !important;
                    }
                </style>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const tabButtons = document.querySelectorAll('.squad-tab-button');
                    const tabContents = document.querySelectorAll('.squad-tab-content');

                    tabButtons.forEach(button => {
                        button.addEventListener('click', function() {
                            const squadId = this.dataset.squadId;

                            // Скрываем все содержимое
                            tabContents.forEach(content => {
                                content.style.display = 'none';
                            });

                            // Удаляем активный класс у всех кнопок
                            tabButtons.forEach(btn => {
                                btn.classList.remove('active');
                                btn.style.background = '#f5f5f5';
                                btn.style.fontWeight = 'normal';
                                btn.style.borderBottom = '2px solid #ddd';
                            });

                            // Показываем выбранный контент
                            const activeContent = document.querySelector('[data-squad-id="' + squadId + '"].squad-tab-content');
                            if (activeContent) {
                                activeContent.style.display = 'block';
                            }

                            // Отмечаем активную кнопку
                            this.classList.add('active');
                            this.style.background = '#fff';
                            this.style.fontWeight = 'bold';
                            this.style.borderBottom = '2px solid #fff';
                        });
                    });
                });
                </script>
            <?php else: ?>
                <p style="color: #999; padding: 20px;">Составы не созданы в wp_arsenal_squad</p>
            <?php endif; ?>
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
