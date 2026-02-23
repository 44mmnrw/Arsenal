<?php
/**
 * Админ-класс одноразового импорта wp_arsenal_* из SQL по URL.
 *
 * @package Arsenal_Team_Manager
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Arsenal_Db_Import_Admin {

	/**
	 * Ключ состояния импорта.
	 */
	const STATE_OPTION = 'arsenal_db_import_state';

	/**
	 * Флаг завершенного импорта.
	 */
	const DONE_OPTION = 'arsenal_db_import_completed';

	/**
	 * URL SQL по умолчанию (можно поменять в UI перед запуском).
	 */
	const DEFAULT_SQL_URL = 'https://raw.githubusercontent.com/44mmnrw/Arsenal/dev_main/release/wp_arsenal_only.sql';

	/**
	 * Конструктор.
	 */
	public function __construct() {
		add_action( 'wp_ajax_arsenal_db_import_start', array( $this, 'ajax_start_import' ) );
		add_action( 'wp_ajax_arsenal_db_import_process', array( $this, 'ajax_process_import' ) );
		add_action( 'wp_ajax_arsenal_db_import_status', array( $this, 'ajax_import_status' ) );
	}

	/**
	 * Рендер страницы импорта.
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Недостаточно прав доступа.', 'arsenal-team-manager' ) );
		}

		$import_done = (bool) get_option( self::DONE_OPTION, false );
		$state       = $this->get_state();

		include ARSENAL_TM_PLUGIN_DIR . 'admin/views/db-import.php';
	}

	/**
	 * AJAX: старт импорта (скачивание SQL, инициализация состояния).
	 */
	public function ajax_start_import() {
		$this->guard_ajax_request();

		if ( (bool) get_option( self::DONE_OPTION, false ) ) {
			wp_send_json_error( array(
				'message' => 'Импорт уже был успешно выполнен ранее. Кнопка скрыта намеренно.',
			) );
		}

		$sql_url = isset( $_POST['sql_url'] ) ? esc_url_raw( wp_unslash( $_POST['sql_url'] ) ) : self::DEFAULT_SQL_URL;
		if ( empty( $sql_url ) || ! wp_http_validate_url( $sql_url ) ) {
			wp_send_json_error( array( 'message' => 'Некорректный URL SQL файла.' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';

		$tmp_file = download_url( $sql_url, 300 );
		if ( is_wp_error( $tmp_file ) ) {
			wp_send_json_error( array(
				'message' => 'Не удалось скачать SQL: ' . $tmp_file->get_error_message(),
			) );
		}

		$file_size = @filesize( $tmp_file );
		if ( false === $file_size || $file_size <= 0 ) {
			@unlink( $tmp_file );
			wp_send_json_error( array( 'message' => 'SQL файл пустой или недоступен.' ) );
		}

		$state = array(
			'sql_url'              => $sql_url,
			'file_path'            => $tmp_file,
			'file_size'            => (int) $file_size,
			'offset'               => 0,
			'buffer'               => '',
			'processed_statements' => 0,
			'executed_statements'  => 0,
			'error_count'          => 0,
			'started_at'           => current_time( 'mysql' ),
			'done'                 => false,
		);

		update_option( self::STATE_OPTION, $state, false );

		wp_send_json_success( $this->format_status_response( $state, 'Импорт запущен.' ) );
	}

	/**
	 * AJAX: статус импорта.
	 */
	public function ajax_import_status() {
		$this->guard_ajax_request();

		$state = $this->get_state();
		if ( empty( $state ) ) {
			wp_send_json_success( array(
				'done'              => (bool) get_option( self::DONE_OPTION, false ),
				'progress_percent'  => (bool) get_option( self::DONE_OPTION, false ) ? 100 : 0,
				'processed'         => 0,
				'executed'          => 0,
				'errors'            => 0,
				'message'           => 'Импорт не запущен.',
				'import_completed'  => (bool) get_option( self::DONE_OPTION, false ),
			) );
		}

		wp_send_json_success( $this->format_status_response( $state, 'Статус получен.' ) );
	}

	/**
	 * AJAX: обработка порции SQL-запросов.
	 */
	public function ajax_process_import() {
		$this->guard_ajax_request();

		$state = $this->get_state();
		if ( empty( $state ) || empty( $state['file_path'] ) ) {
			wp_send_json_error( array( 'message' => 'Нет активной сессии импорта. Нажмите «Импортировать данные».' ) );
		}

		if ( ! empty( $state['done'] ) ) {
			wp_send_json_success( $this->format_status_response( $state, 'Импорт уже завершен.' ) );
		}

		$file_path = $state['file_path'];
		if ( ! file_exists( $file_path ) ) {
			delete_option( self::STATE_OPTION );
			wp_send_json_error( array( 'message' => 'Временный SQL-файл не найден. Запустите импорт заново.' ) );
		}

		$handle = fopen( $file_path, 'rb' );
		if ( false === $handle ) {
			wp_send_json_error( array( 'message' => 'Не удалось открыть временный SQL-файл.' ) );
		}

		$offset = isset( $state['offset'] ) ? (int) $state['offset'] : 0;
		if ( $offset > 0 ) {
			fseek( $handle, $offset );
		}

		$max_statements = 220;
		$max_seconds    = 2.2;
		$started        = microtime( true );

		$buffer = isset( $state['buffer'] ) ? (string) $state['buffer'] : '';

		global $wpdb;
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=0' );
		$wpdb->query( 'SET UNIQUE_CHECKS=0' );

		$processed_now = 0;
		while ( ! feof( $handle ) && $processed_now < $max_statements ) {
			if ( ( microtime( true ) - $started ) >= $max_seconds ) {
				break;
			}

			$line = fgets( $handle );
			if ( false === $line ) {
				break;
			}

			$trimmed = trim( $line );
			$state['offset'] = ftell( $handle );

			if ( '' === $trimmed || 0 === strpos( $trimmed, '--' ) ) {
				continue;
			}

			$buffer .= $line;

			if ( ! preg_match( '/;\s*$/', $trimmed ) ) {
				continue;
			}

			$statement = trim( $buffer );
			$buffer    = '';

			$state['processed_statements']++;
			$processed_now++;

			if ( ! $this->is_arsenal_statement( $statement ) ) {
				continue;
			}

			$statement = $this->apply_db_prefix( $statement, $wpdb->prefix );
			$result    = $wpdb->query( $statement );

			if ( false === $result ) {
				$state['error_count']++;
				continue;
			}

			$state['executed_statements']++;
		}

		$state['buffer'] = $buffer;

		$wpdb->query( 'SET UNIQUE_CHECKS=1' );
		$wpdb->query( 'SET FOREIGN_KEY_CHECKS=1' );

		$eof_reached = feof( $handle );
		fclose( $handle );

		if ( $eof_reached && '' === trim( (string) $state['buffer'] ) ) {
			$state['done'] = true;
			$state['offset'] = (int) $state['file_size'];
			update_option( self::DONE_OPTION, 1, false );

			if ( file_exists( $file_path ) ) {
				@unlink( $file_path );
			}
		}

		update_option( self::STATE_OPTION, $state, false );

		$message = ! empty( $state['done'] )
			? 'Импорт завершён. Кнопка будет скрыта.'
			: 'Импорт выполняется...';

		wp_send_json_success( $this->format_status_response( $state, $message ) );
	}

	/**
	 * Базовая защита AJAX-запроса.
	 */
	private function guard_ajax_request() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => 'Недостаточно прав.' ) );
		}

		check_ajax_referer( 'arsenal_db_import_nonce', 'nonce' );
	}

	/**
	 * Прочитать состояние импорта.
	 *
	 * @return array
	 */
	private function get_state() {
		$state = get_option( self::STATE_OPTION, array() );
		return is_array( $state ) ? $state : array();
	}

	/**
	 * Проверяет, относится ли SQL к wp_arsenal_*.
	 *
	 * @param string $statement SQL-запрос.
	 * @return bool
	 */
	private function is_arsenal_statement( $statement ) {
		$pattern = '/\b(?:DROP\s+TABLE\s+IF\s+EXISTS|CREATE\s+TABLE|INSERT\s+INTO|ALTER\s+TABLE|LOCK\s+TABLES)\s+`?wp_arsenal_[a-z0-9_]+`?/i';
		return (bool) preg_match( $pattern, $statement );
	}

	/**
	 * Применяет текущий префикс WP к таблицам arsenal.
	 *
	 * @param string $statement SQL-запрос.
	 * @param string $prefix    Префикс таблиц.
	 * @return string
	 */
	private function apply_db_prefix( $statement, $prefix ) {
		if ( 'wp_' === $prefix ) {
			return $statement;
		}

		return str_replace( 'wp_arsenal_', $prefix . 'arsenal_', $statement );
	}

	/**
	 * Унифицированный ответ статуса.
	 *
	 * @param array  $state   Состояние.
	 * @param string $message Сообщение.
	 * @return array
	 */
	private function format_status_response( $state, $message ) {
		$file_size = isset( $state['file_size'] ) ? max( 1, (int) $state['file_size'] ) : 1;
		$offset    = isset( $state['offset'] ) ? min( (int) $state['offset'], $file_size ) : 0;
		$percent   = (int) round( ( $offset / $file_size ) * 100 );

		if ( ! empty( $state['done'] ) ) {
			$percent = 100;
		}

		return array(
			'done'             => ! empty( $state['done'] ),
			'progress_percent' => $percent,
			'processed'        => (int) ( $state['processed_statements'] ?? 0 ),
			'executed'         => (int) ( $state['executed_statements'] ?? 0 ),
			'errors'           => (int) ( $state['error_count'] ?? 0 ),
			'message'          => $message,
			'import_completed' => (bool) get_option( self::DONE_OPTION, false ),
		);
	}
}
