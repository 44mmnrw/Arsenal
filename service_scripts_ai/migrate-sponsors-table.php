<?php
/**
 * Миграция таблицы wp_arsenal_sponsors на удаленный сервер
 *
 * Скрипт экспортирует текущую структуру и данные таблицы wp_arsenal_sponsors
 * и генерирует SQL файл для импорта на удаленный сервер.
 *
 * Использование:
 *   php migrate-sponsors-table.php [--export] [--check] [--info]
 *
 * Опции:
 *   --export  Экспортировать таблицу в SQL файл (по умолчанию)
 *   --check   Проверить существование таблицы и вывести информацию
 *   --info    Вывести информацию о таблице (строки, поля)
 *
 * @package Arsenal
 * @since 1.0.0
 */

// === НАСТРОЙКИ ===
define( 'EXPORT_DIR', __DIR__ . '/exports' );
define( 'FILENAME', 'wp_arsenal_sponsors_' . date( 'Y-m-d_H-i-s' ) . '.sql' );

// === ПОДКЛЮЧЕНИЕ К WORDPRESS ===
// Определяем путь к корню WordPress (service_scripts_ai находится в корне arsenal)
$wp_root = dirname( __DIR__ );
if ( ! file_exists( $wp_root . '/wp-load.php' ) ) {
    echo "❌ Ошибка: не найден wp-load.php в $wp_root\n";
    exit( 1 );
}

// Подключаем WordPress
require_once $wp_root . '/wp-load.php';

// === ФУНКЦИИ ===

/**
 * Проверить существование таблицы
 */
function check_table_exists() {
    global $wpdb;
    $table = $wpdb->prefix . 'arsenal_sponsors';
    
    $result = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
            DB_NAME,
            $table
        )
    );
    
    return $result > 0;
}

/**
 * Получить информацию о таблице
 */
function get_table_info() {
    global $wpdb;
    $table = $wpdb->prefix . 'arsenal_sponsors';
    
    // Структура таблицы
    $columns = $wpdb->get_results( "DESCRIBE $table" );
    
    // Количество строк
    $row_count = $wpdb->get_var( "SELECT COUNT(*) FROM $table" );
    
    // Размер таблицы
    $size = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT 
                ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
            DB_NAME,
            $table
        )
    );
    
    return array(
        'table'      => $table,
        'columns'    => $columns,
        'row_count'  => $row_count,
        'size_mb'    => $size->size_mb ?? 0,
    );
}

/**
 * Экспортировать структуру таблицы
 */
function export_table_structure() {
    global $wpdb;
    $table = $wpdb->prefix . 'arsenal_sponsors';
    
    $create_table = $wpdb->get_row( "SHOW CREATE TABLE $table", ARRAY_N );
    
    if ( ! $create_table ) {
        return false;
    }
    
    // Добавляем DROP TABLE для безопасности
    $sql = "-- Экспорт таблицы: $table\n";
    $sql .= "-- Дата: " . date( 'Y-m-d H:i:s' ) . "\n\n";
    $sql .= "DROP TABLE IF EXISTS `$table`;\n\n";
    $sql .= $create_table[1] . ";\n\n";
    
    return $sql;
}

/**
 * Экспортировать данные таблицы
 */
function export_table_data() {
    global $wpdb;
    $table = $wpdb->prefix . 'arsenal_sponsors';
    
    $rows = $wpdb->get_results( "SELECT * FROM $table", ARRAY_A );
    
    if ( empty( $rows ) ) {
        return "-- Таблица пуста\n";
    }
    
    $sql = "-- Экспорт данных таблицы: $table\n\n";
    
    foreach ( $rows as $row ) {
        $columns = array_keys( $row );
        $values  = array_values( $row );
        
        // Экранируем значения
        $values = array_map( function( $val ) use ( $wpdb ) {
            if ( $val === null ) {
                return 'NULL';
            }
            return "'" . esc_sql( $val ) . "'";
        }, $values );
        
        $col_list = implode( ', ', array_map( function( $col ) {
            return "`$col`";
        }, $columns ) );
        
        $val_list = implode( ', ', $values );
        
        $sql .= "INSERT INTO `$table` ($col_list) VALUES ($val_list);\n";
    }
    
    $sql .= "\n";
    
    return $sql;
}

/**
 * Сохранить SQL в файл
 */
function save_sql_file( $sql_content ) {
    // Создаем папку если её нет
    if ( ! is_dir( EXPORT_DIR ) ) {
        if ( ! mkdir( EXPORT_DIR, 0755, true ) ) {
            echo "❌ Ошибка: не удалось создать папку " . EXPORT_DIR . "\n";
            return false;
        }
    }
    
    $filepath = EXPORT_DIR . '/' . FILENAME;
    
    if ( file_put_contents( $filepath, $sql_content ) === false ) {
        echo "❌ Ошибка: не удалось сохранить файл $filepath\n";
        return false;
    }
    
    return $filepath;
}

/**
 * Вывести информацию о таблице
 */
function display_table_info() {
    if ( ! check_table_exists() ) {
        echo "❌ Таблица wp_arsenal_sponsors не существует!\n";
        return false;
    }
    
    $info = get_table_info();
    
    echo "\n📊 ИНФОРМАЦИЯ О ТАБЛИЦЕ\n";
    echo "═══════════════════════════════════════════════════════════\n";
    echo "Таблица:       " . $info['table'] . "\n";
    echo "Строк:         " . $info['row_count'] . "\n";
    echo "Размер:        " . $info['size_mb'] . " MB\n";
    echo "Полей:         " . count( $info['columns'] ) . "\n";
    echo "\n📋 СТРУКТУРА:\n";
    echo "───────────────────────────────────────────────────────────\n";
    
    foreach ( $info['columns'] as $column ) {
        $type  = $column->Type;
        $null  = $column->Null === 'YES' ? 'NULL' : 'NOT NULL';
        $key   = ! empty( $column->Key ) ? " [KEY: {$column->Key}]" : '';
        
        echo sprintf( "  %-20s %-20s %s%s\n", $column->Field, $type, $null, $key );
    }
    
    echo "\n";
    return true;
}

// === MAIN ===

if ( php_sapi_name() !== 'cli' ) {
    echo "❌ Этот скрипт должен быть запущен через CLI\n";
    exit( 1 );
}

$command = $argv[1] ?? '--export';

echo "\n🔄 МИГРАЦИЯ ТАБЛИЦЫ wp_arsenal_sponsors\n";
echo "═══════════════════════════════════════════════════════════\n\n";

// Проверяем таблицу
if ( ! check_table_exists() ) {
    echo "❌ ОШИБКА: Таблица wp_arsenal_sponsors не существует!\n";
    echo "   Создайте таблицу перед миграцией:\n";
    echo "   php install-database.php\n";
    exit( 1 );
}

echo "✅ Таблица wp_arsenal_sponsors найдена\n\n";

switch ( $command ) {
    case '--info':
        display_table_info();
        break;
    
    case '--check':
        $info = get_table_info();
        echo "✅ Таблица существует\n";
        echo "   Строк: " . $info['row_count'] . "\n";
        echo "   Размер: " . $info['size_mb'] . " MB\n";
        echo "   Полей: " . count( $info['columns'] . "\n" );
        break;
    
    case '--export':
    default:
        echo "📝 Экспортирую структуру таблицы...\n";
        $sql = export_table_structure();
        
        if ( ! $sql ) {
            echo "❌ Ошибка при экспорте структуры\n";
            exit( 1 );
        }
        
        echo "✅ Структура экспортирована\n";
        
        echo "📝 Экспортирую данные таблицы...\n";
        $data_sql = export_table_data();
        $sql .= $data_sql;
        
        echo "✅ Данные экспортированы\n";
        
        echo "💾 Сохраняю в файл...\n";
        $filepath = save_sql_file( $sql );
        
        if ( ! $filepath ) {
            exit( 1 );
        }
        
        echo "✅ Файл сохранен: " . $filepath . "\n\n";
        
        // Информация о файле
        $filesize = filesize( $filepath );
        echo "📊 РЕЗУЛЬТАТЫ:\n";
        echo "───────────────────────────────────────────────────────────\n";
        echo "Файл:          " . FILENAME . "\n";
        echo "Путь:          " . $filepath . "\n";
        echo "Размер:        " . round( $filesize / 1024, 2 ) . " KB\n";
        echo "\n";
        echo "📋 ИНСТРУКЦИЯ ПО ЗАГРУЗКЕ НА СЕРВЕР:\n";
        echo "───────────────────────────────────────────────────────────\n";
        echo "1. SCP (рекомендуется):\n";
        echo "   scp " . $filepath . " site_user@212.113.120.197:/tmp/\n\n";
        echo "2. SSH и mysql:\n";
        echo "   ssh site_user@212.113.120.197\n";
        echo "   mysql -u arsenal_user -p arsenal < /tmp/" . FILENAME . "\n\n";
        echo "3. Или через phpMyAdmin на сервере:\n";
        echo "   Скопируйте содержимое файла и импортируйте в phpMyAdmin\n";
        echo "\n";
        break;
}

echo "✅ Завершено\n\n";
