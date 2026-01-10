<?php
/**
 * Arsenal Database Checker
 * 
 * Скрипт для проверки структуры и содержимого БД Arsenal
 * Выводит все таблицы wp_arsenal_*, их поля и количество записей
 * 
 * Использование: php check-database.php
 * 
 * @package Arsenal
 */

// Подключаемся к WordPress для доступа к $wpdb
if ( file_exists( dirname( __DIR__ ) . '/wp-load.php' ) ) {
	require_once dirname( __DIR__ ) . '/wp-load.php';
} else {
	die( "Error: wp-load.php not found. Run this script from the Arsenal root directory.\n" );
}

global $wpdb;

echo "\n";
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║          Arsenal Database Structure & Statistics Check             ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n";
echo "\n";

// 1. Получить все таблицы Arsenal
$tables = $wpdb->get_results(
	"SELECT TABLE_NAME, TABLE_ROWS, DATA_LENGTH, INDEX_LENGTH
	 FROM INFORMATION_SCHEMA.TABLES
	 WHERE TABLE_SCHEMA = '" . DB_NAME . "'
	 AND TABLE_NAME LIKE 'wp_arsenal_%'
	 ORDER BY TABLE_NAME"
);

if ( empty( $tables ) ) {
	echo "❌ No Arsenal tables found in the database!\n\n";
	exit( 1 );
}

echo "✅ Found " . count( $tables ) . " Arsenal tables:\n\n";

// 2. Показать сводку по таблицам
echo str_pad( "Table Name", 40 ) . " | " . str_pad( "Rows", 8 ) . " | " . str_pad( "Size", 10 ) . "\n";
echo str_repeat( "─", 65 ) . "\n";

$total_rows = 0;
foreach ( $tables as $table ) {
	$size_mb = round( ( $table->DATA_LENGTH + $table->INDEX_LENGTH ) / 1024 / 1024, 2 );
	echo str_pad( $table->TABLE_NAME, 40 ) . " | " . str_pad( (int) $table->TABLE_ROWS, 8 ) . " | " . str_pad( $size_mb . " MB", 10 ) . "\n";
	$total_rows += (int) $table->TABLE_ROWS;
}

echo str_repeat( "─", 65 ) . "\n";
echo "TOTAL: " . $total_rows . " records\n\n";

// 3. Детальная структура каждой таблицы
foreach ( $tables as $table ) {
	echo "╔" . str_repeat( "═", 68 ) . "╗\n";
	echo "║ " . str_pad( $table->TABLE_NAME, 66 ) . " ║\n";
	echo "╠" . str_repeat( "═", 68 ) . "╣\n";

	// Получить поля таблицы
	$columns = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY, COLUMN_DEFAULT, EXTRA
			 FROM INFORMATION_SCHEMA.COLUMNS
			 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
			 ORDER BY ORDINAL_POSITION",
			DB_NAME,
			$table->TABLE_NAME
		)
	);

	// Заголовок
	echo "║ " . str_pad( "Column", 25 ) . " | " . str_pad( "Type", 20 ) . " | " . str_pad( "Nullable", 8 ) . " ║\n";
	echo "╟" . str_repeat( "─", 68 ) . "╢\n";

	// Выводить поля
	foreach ( $columns as $col ) {
		$nullable = ( 'YES' === $col->IS_NULLABLE ) ? 'NULL' : 'NOT NULL';
		
		// Добавить информацию о ключах
		$key_info = '';
		if ( 'PRI' === $col->COLUMN_KEY ) {
			$key_info = ' [PK]';
		} elseif ( 'UNI' === $col->COLUMN_KEY ) {
			$key_info = ' [UNI]';
		} elseif ( 'MUL' === $col->COLUMN_KEY ) {
			$key_info = ' [FK]';
		}

		echo "║ " . str_pad( $col->COLUMN_NAME . $key_info, 25 ) . " | " . str_pad( $col->COLUMN_TYPE, 20 ) . " | " . str_pad( $nullable, 8 ) . " ║\n";
	}

	echo "╠" . str_repeat( "═", 68 ) . "╣\n";

	// Получить индексы
	$indexes = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT INDEX_NAME, COLUMN_NAME, SEQ_IN_INDEX, NON_UNIQUE
			 FROM INFORMATION_SCHEMA.STATISTICS
			 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s
			 ORDER BY INDEX_NAME, SEQ_IN_INDEX",
			DB_NAME,
			$table->TABLE_NAME
		)
	);

	if ( ! empty( $indexes ) ) {
		echo "║ Indexes:                                                              ║\n";
		$current_index = '';
		foreach ( $indexes as $idx ) {
			if ( $current_index !== $idx->INDEX_NAME ) {
				$current_index = $idx->INDEX_NAME;
				$is_unique = ( 0 === (int) $idx->NON_UNIQUE ) ? ' (UNIQUE)' : '';
				echo "║  • " . str_pad( $idx->INDEX_NAME . $is_unique, 62 ) . " ║\n";
			}
			echo "║    - " . str_pad( $idx->COLUMN_NAME, 59 ) . " ║\n";
		}
		echo "╠" . str_repeat( "═", 68 ) . "╣\n";
	}

	// Получить внешние ключи (из информационной схемы)
	$fks = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
			 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
			 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND REFERENCED_TABLE_NAME IS NOT NULL
			 ORDER BY CONSTRAINT_NAME",
			DB_NAME,
			$table->TABLE_NAME
		)
	);

	if ( ! empty( $fks ) ) {
		echo "║ Foreign Keys:                                                         ║\n";
		foreach ( $fks as $fk ) {
			$fk_text = "{$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}({$fk->REFERENCED_COLUMN_NAME})";
			echo "║  • " . str_pad( $fk_text, 62 ) . " ║\n";
		}
		echo "╠" . str_repeat( "═", 68 ) . "╣\n";
	}

	// Количество записей
	$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table->TABLE_NAME}" );
	echo "║ " . str_pad( "Records: $count", 66 ) . " ║\n";
	echo "╚" . str_repeat( "═", 68 ) . "╝\n\n";
}

// 4. Проверить целостность FK
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║               Foreign Key Integrity Check                           ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";

$fk_check = $wpdb->get_results(
	"SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
	 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
	 WHERE TABLE_SCHEMA = '" . DB_NAME . "'
	 AND TABLE_NAME LIKE 'wp_arsenal_%'
	 AND REFERENCED_TABLE_NAME IS NOT NULL
	 ORDER BY TABLE_NAME, CONSTRAINT_NAME"
);

if ( empty( $fk_check ) ) {
	echo "⚠️  No foreign keys defined in the database.\n\n";
} else {
	echo "✅ Found " . count( $fk_check ) . " foreign key relationships:\n\n";
	foreach ( $fk_check as $fk ) {
		echo "  • {$fk->TABLE_NAME}.{$fk->COLUMN_NAME} → {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
	}
	echo "\n";
}

// 5. Проверить версию БД
$db_version = $wpdb->get_option( 'arsenal_db_version' );
echo "╔════════════════════════════════════════════════════════════════════╗\n";
echo "║                    Database Version Info                            ║\n";
echo "╚════════════════════════════════════════════════════════════════════╝\n\n";
echo "Arsenal DB Version: " . ( $db_version ? $db_version : 'Not set' ) . "\n";
echo "MySQL Version: " . $wpdb->get_var( 'SELECT VERSION()' ) . "\n\n";

echo "✅ Database check completed successfully!\n\n";
