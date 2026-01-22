<?php
/**
 * Класс управления составами (типами команды)
 *
 * @package Arsenal_Team_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Squad_Admin {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        // Управление составами скрыто от меню (может быть управляемо программно)
        // add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'wp_ajax_arsenal_delete_squad', array( $this, 'handle_delete_squad' ) );
        add_action( 'wp_ajax_arsenal_add_squad', array( $this, 'handle_add_squad' ) );
    }

    /**
     * Регистрация меню
     */
    public function register_menu() {
        add_submenu_page(
            'arsenal-team',
            'Добавить состав',
            'Состав',
            'manage_options',
            'arsenal-squad-add',
            array( $this, 'render_squad_form' )
        );
    }

    /**
     * Рендер формы добавления состава
     */
    public function render_squad_form() {
        global $wpdb;

        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['nonce'] ) ) {
            error_log( '=== Squad Form POST ===' );
            error_log( 'Nonce: ' . $_POST['nonce'] );
            
            if ( ! wp_verify_nonce( $_POST['nonce'], 'arsenal_staff_nonce' ) ) {
                error_log( 'Nonce verification failed' );
                wp_die( 'Ошибка безопасности' );
            }

            $squad_name = sanitize_text_field( $_POST['squad_name'] ?? '' );
            error_log( 'Squad name: ' . $squad_name );

            if ( empty( $squad_name ) ) {
                echo '<div class="notice notice-error"><p>Название состава не может быть пустым</p></div>';
            } else {
                // Генерируем MD5 хеш из названия (первые 8 символов, UPPERCASE)
                $squad_id = strtoupper( substr( md5( $squad_name ), 0, 8 ) );

                // Проверяем есть ли уже такой состав
                $existing = $wpdb->get_row( $wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}arsenal_squad WHERE squad_id = %s",
                    $squad_id
                ) );

                if ( $existing ) {
                    echo '<div class="notice notice-warning"><p>Состав с таким названием уже существует</p></div>';
                } else {
                    // Вставляем новый состав
                    error_log( 'Inserting squad: ' . $squad_name . ' (MD5: ' . $squad_id . ')' );
                    
                    $result = $wpdb->insert(
                        $wpdb->prefix . 'arsenal_squad',
                        array(
                            'squad_id' => $squad_id,
                            'squad_name' => $squad_name,
                        ),
                        array( '%s', '%s' )
                    );
                    
                    error_log( 'Insert result: ' . ( $result ? 'SUCCESS' : 'FAILED' ) );
                    if ( ! $result ) {
                        error_log( 'DB Error: ' . $wpdb->last_error );
                    }

                    if ( $result ) {
                        echo '<div class="notice notice-success"><p>Состав успешно добавлен</p></div>';
                    } else {
                        echo '<div class="notice notice-error"><p>Ошибка при добавлении состава: ' . esc_html( $wpdb->last_error ) . '</p></div>';
                    }
                }
            }
        }

        // Получаем все составы
        $squads = $wpdb->get_results( "SELECT id, squad_name FROM {$wpdb->prefix}arsenal_squad ORDER BY id ASC" );
        ?>
        <div class="wrap">
            <h1>Управление составами (типами команды)</h1>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <!-- Форма добавления -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
                    <h2>Добавить новый состав</h2>
                    <form method="post" style="max-width: 100%;">
                        <?php wp_nonce_field( 'arsenal_staff_nonce', 'nonce' ); ?>

                        <div class="form-group" style="margin-bottom: 15px;">
                            <label for="squad_name" style="display: block; margin-bottom: 5px; font-weight: bold;">
                                Название состава:
                            </label>
                            <input type="text" 
                                   id="squad_name" 
                                   name="squad_name" 
                                   placeholder="например: Основной состав" 
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <small style="color: #666; display: block; margin-top: 5px;">
                                MD5 хеш будет сгенерирован автоматически из названия
                            </small>
                        </div>

                        <button type="submit" class="button button-primary">
                            ➕ Добавить состав
                        </button>
                    </form>
                </div>

                <!-- Список существующих составов -->
                <div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 5px;">
                    <h2>Существующие составы</h2>

                    <?php if ( $squads ): ?>
                        <table class="widefat striped" style="margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">ID</th>
                                    <th style="width: 50%;">Название</th>
                                    <th style="width: 20%;">Действие</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $squads as $squad ): ?>
                                <tr>
                                    <td><code><?php echo $squad->id; ?></code></td>
                                    <td><?php echo esc_html( $squad->squad_name ); ?></td>
                                    <td>
                                        <?php if ( count( $squads ) > 1 ): ?>
                                            <button class="button button-small button-delete" 
                                                    data-squad-id="<?php echo $squad->id; ?>"
                                                    data-squad-name="<?php echo esc_attr( $squad->squad_name ); ?>"
                                                    onclick="deleteSquad(<?php echo $squad->id; ?>, '<?php echo esc_attr( $squad->squad_name ); ?>')">
                                                🗑️ Удалить
                                            </button>
                                        <?php else: ?>
                                            <span style="color: #999;">нельзя удалить</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="color: #999;">Составы не найдены</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
        function deleteSquad(squadId, squadName) {
            if ( ! confirm('Вы уверены, что хотите удалить состав "' + squadName + '"?') ) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'arsenal_delete_squad');
            formData.append('squad_id', squadId);
            formData.append('_ajax_nonce', '<?php echo wp_create_nonce( "arsenal_staff_nonce" ); ?>');

            fetch('<?php echo admin_url( "admin-ajax.php" ); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                if ( data.success ) {
                    alert('Состав удален');
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                alert('Ошибка сети: ' + error.message);
            });
        }
        </script>
        <?php
    }

    /**
     * Обработчик удаления состава через AJAX
     */
    public function handle_delete_squad() {
        error_log( '=== handle_delete_squad() ===' );
        
        check_ajax_referer( 'arsenal_staff_nonce', '_ajax_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            error_log( 'No permissions' );
            wp_send_json_error( 'Недостаточно прав' );
        }

        global $wpdb;

        $squad_id = intval( $_POST['squad_id'] ?? 0 );
        error_log( 'Squad ID to delete: ' . $squad_id );

        if ( ! $squad_id ) {
            error_log( 'Squad ID is empty' );
            wp_send_json_error( 'ID состава не указан' );
        }

        // Проверяем количество составов
        $squad_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_squad" );
        error_log( 'Total squads: ' . $squad_count );
        
        if ( $squad_count <= 1 ) {
            error_log( 'Cannot delete last squad' );
            wp_send_json_error( 'Нельзя удалить последний состав' );
        }

        // Проверяем есть ли сотрудники в этом составе
        $staff_count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff WHERE squad_id = %d",
            $squad_id
        ) );
        error_log( 'Staff in squad: ' . $staff_count );

        if ( $staff_count > 0 ) {
            error_log( 'Squad has staff' );
            wp_send_json_error( 'В составе есть сотрудники. Сначала переместите их в другой состав.' );
        }

        // Удаляем состав
        error_log( 'Deleting squad ' . $squad_id );
        $result = $wpdb->delete(
            $wpdb->prefix . 'arsenal_squad',
            array( 'id' => $squad_id ),
            array( '%d' )
        );
        error_log( 'Delete result: ' . ( $result ? 'SUCCESS' : 'FAILED' ) );
        
        if ( ! $result ) {
            error_log( 'DB Error: ' . $wpdb->last_error );
        }

        if ( $result ) {
            wp_send_json_success( 'Состав удален' );
        } else {
            wp_send_json_error( 'Ошибка при удалении состава: ' . $wpdb->last_error );
        }
    }

    /**
     * Обработчик добавления состава через AJAX
     */
    public function handle_add_squad() {
        check_ajax_referer( 'arsenal_staff_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Недостаточно прав' );
        }

        global $wpdb;

        $squad_name = sanitize_text_field( $_POST['squad_name'] ?? '' );

        if ( empty( $squad_name ) ) {
            wp_send_json_error( 'Название состава не может быть пустым' );
        }

        // Генерируем MD5 хеш (первые 8 символов, UPPERCASE)
        $squad_id = strtoupper( substr( md5( $squad_name ), 0, 8 ) );

        // Проверяем есть ли уже такой состав
        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}arsenal_squad WHERE squad_id = %s",
            $squad_id
        ) );

        if ( $existing ) {
            wp_send_json_error( 'Состав с таким названием уже существует' );
        }

        // Вставляем новый состав
        $result = $wpdb->insert(
            $wpdb->prefix . 'arsenal_squad',
            array(
                'squad_id' => $squad_id,
                'squad_name' => $squad_name,
            ),
            array( '%s', '%s' )
        );

        if ( $result ) {
            wp_send_json_success( 'Состав успешно добавлен' );
        } else {
            wp_send_json_error( 'Ошибка при добавлении состава' );
        }
    }
}

// Инициализация класса
Arsenal_Squad_Admin::get_instance();
