<?php
/**
 * Скрипт проверки валидации типа спонсора
 */

if ( ! file_exists( dirname( __FILE__ ) . '/wp-load.php' ) ) {
	die( "wp-load.php не найден\n" );
}

require_once dirname( __FILE__ ) . '/wp-load.php';
require_once dirname( __FILE__ ) . '/wp-content/themes/arsenal/inc/class-arsenal-sponsors.php';

global $wpdb;

echo "=== Проверка спонсоров в БД ===\n\n";

// Получить всех спонсоров
$all_sponsors = $wpdb->get_results(
	"SELECT id, name, type, is_active FROM {$wpdb->prefix}arsenal_sponsors ORDER BY type DESC, order_index ASC"
);

if ( empty( $all_sponsors ) ) {
	echo "Спонсоров не найдено в БД\n";
	exit;
}

echo "Всего спонсоров: " . count( $all_sponsors ) . "\n\n";

// Разделим по типам
$general = array();
$partners = array();

foreach ( $all_sponsors as $sponsor ) {
	if ( $sponsor->type === 'general_sponsor' ) {
		$general[] = $sponsor;
	} else {
		$partners[] = $sponsor;
	}
}

echo "📊 ГЕНЕРАЛЬНЫЕ СПОНСОРЫ: " . count( $general ) . "\n";
if ( ! empty( $general ) ) {
	foreach ( $general as $g ) {
		echo "  ✓ ID:" . $g->id . " - " . $g->name . " (Активен: " . ( $g->is_active ? 'ДА' : 'НЕТ' ) . ")\n";
	}
} else {
	echo "  - Нет\n";
}

echo "\n🤝 ПАРТНЕРЫ: " . count( $partners ) . "\n";
if ( ! empty( $partners ) ) {
	foreach ( $partners as $p ) {
		echo "  ✓ ID:" . $p->id . " - " . $p->name . " (Активен: " . ( $p->is_active ? 'ДА' : 'НЕТ' ) . ")\n";
	}
} else {
	echo "  - Нет\n";
}

echo "\n=== СТАТУС ВАЛИДАЦИИ ===\n";
if ( count( $general ) > 1 ) {
	echo "⚠️  ПРОБЛЕМА: Генеральных спонсоров больше одного!\n";
	echo "   Необходимо запустить валидацию в админ-панели.\n";
} else if ( count( $general ) === 1 ) {
	echo "✅ OK: Ровно один генеральный спонсор\n";
} else {
	echo "ℹ️  INFO: Генеральный спонсор не установлен\n";
}
