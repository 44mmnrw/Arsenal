<?php
/**
 * Класс: Админ интерфейс для управления стадионами
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Stadium_Admin {
    
    /**
     * Инициализация
     */
    public function __init__() {
        add_action( 'admin_post_arsenal_create_stadium', array( $this, 'handle_create_stadium' ) );
        add_action( 'admin_post_arsenal_update_stadium', array( $this, 'handle_update_stadium' ) );
        add_action( 'admin_post_arsenal_delete_stadium', array( $this, 'handle_delete_stadium' ) );
    }
    
    /**
     * Отображение списка стадионов
     */
    public function render_stadiums_list() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        // Получение параметров пагинации
        $paged = isset( $_GET['paged'] ) ? intval( $_GET['paged'] ) : 1;
        $per_page = 20;
        
        // Получение данных
        $result = Arsenal_Stadium_Manager::get_stadiums( $paged, $per_page );
        $stadiums = $result['stadiums'];
        $total_pages = $result['total_pages'];
        $total = $result['total'];
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/stadiums-list.php';
    }
    
    /**
     * Отображение формы добавления/редактирования стадиона
     */
    public function render_stadium_form() {
        // Проверка прав доступа
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'У вас нет прав для доступа к этому разделу.' );
        }
        
        $stadium = null;
        $is_edit = false;
        
        // Если редактируем стадион
        if ( isset( $_GET['stadium_id'] ) ) {
            $stadium_id = intval( $_GET['stadium_id'] );
            $stadium = Arsenal_Stadium_Manager::get_stadium( $stadium_id );
            
            if ( ! $stadium ) {
                wp_die( 'Стадион не найден.' );
            }
            
            $is_edit = true;
        }
        
        // Включение шаблона
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/stadium-form.php';
    }
    
    /**
     * Обработка создания стадиона
     */
    public function handle_create_stadium() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_POST['arsenal_stadium_nonce'] ) || 
             ! wp_verify_nonce( $_POST['arsenal_stadium_nonce'], 'arsenal_stadium_form' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        // Подготовка данных
        $data = array(
            'name' => sanitize_text_field( $_POST['name'] ?? '' ),
            'city' => sanitize_text_field( $_POST['city'] ?? '' ),
            'capacity' => sanitize_text_field( $_POST['capacity'] ?? '' ),
            'photo_url' => sanitize_text_field( $_POST['photo_url'] ?? '' ),
        );
        
        // Обработка загрузки изображения
        if ( ! empty( $_FILES['photo'] ) ) {
            $photo_url = $this->handle_image_upload();
            if ( $photo_url ) {
                $data['photo_url'] = $photo_url;
            }
        }
        
        // Создание стадиона
        $stadium_id = Arsenal_Stadium_Manager::create_stadium( $data );
        
        if ( $stadium_id ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-stadiums&success=1' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-stadium-add&error=1' ) );
        }
        
        exit;
    }
    
    /**
     * Обработка обновления стадиона
     */
    public function handle_update_stadium() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_POST['arsenal_stadium_nonce'] ) || 
             ! wp_verify_nonce( $_POST['arsenal_stadium_nonce'], 'arsenal_stadium_form' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        $stadium_id = intval( $_POST['stadium_id'] ?? 0 );
        if ( ! $stadium_id ) {
            wp_die( 'ID стадиона не указан.' );
        }
        
        // Подготовка данных
        $data = array(
            'name' => sanitize_text_field( $_POST['name'] ?? '' ),
            'city' => sanitize_text_field( $_POST['city'] ?? '' ),
            'capacity' => sanitize_text_field( $_POST['capacity'] ?? '' ),
        );
        
        // Обработка загрузки изображения
        if ( ! empty( $_FILES['photo'] ) ) {
            $photo_url = $this->handle_image_upload();
            if ( $photo_url ) {
                $data['photo_url'] = $photo_url;
            }
        }
        
        // Обновление стадиона
        $result = Arsenal_Stadium_Manager::update_stadium( $stadium_id, $data );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-stadiums&success=1' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-stadium-edit&stadium_id=' . $stadium_id . '&error=1' ) );
        }
        
        exit;
    }
    
    /**
     * Обработка удаления стадиона
     */
    public function handle_delete_stadium() {
        // Проверка прав
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Доступ запрещен.' );
        }
        
        // Проверка nonce
        if ( ! isset( $_GET['_wpnonce'] ) || 
             ! wp_verify_nonce( $_GET['_wpnonce'], 'arsenal_delete_stadium' ) ) {
            wp_die( 'Проверка безопасности не пройдена.' );
        }
        
        $stadium_id = intval( $_GET['stadium_id'] ?? 0 );
        if ( ! $stadium_id ) {
            wp_die( 'ID стадиона не указан.' );
        }
        
        $result = Arsenal_Stadium_Manager::delete_stadium( $stadium_id );
        
        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=arsenal-stadiums&deleted=1' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=arsenal-stadiums&error=1' ) );
        }
        
        exit;
    }
    
    /**
     * Обработка загрузки изображения
     *
     * @return string|false Относительный путь изображения или false при ошибке
     */
    private function handle_image_upload() {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        
        $uploaded = wp_handle_upload( $_FILES['photo'], array(
            'test_form' => false,
            'mimes' => array(
                'jpg|jpeg|jpe' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
            ),
        ) );
        
        if ( isset( $uploaded['error'] ) ) {
            error_log( 'Arsenal: Ошибка загрузки изображения - ' . $uploaded['error'] );
            return false;
        }
        
        // Получаем полный URL и преобразуем в относительный путь
        $full_url = $uploaded['url'];
        $site_url = home_url();
        
        // Вычисляем относительный путь от корня сайта
        $relative_path = str_replace( $site_url, '', $full_url );
        
        // Убеждаемся, что путь начинается со слэша
        if ( ! str_starts_with( $relative_path, '/' ) ) {
            $relative_path = '/' . $relative_path;
        }
        
        return $relative_path;
    }
}
