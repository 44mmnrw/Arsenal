<?php
/**
 * Проверка связей между должностями, отделами и составами
 * 
 * Использование: php service_scripts_ai/check-staff-relationships.php
 */

// Подключаем WordPress
require_once dirname( dirname( __FILE__ ) ) . '/wp-load.php';

if ( ! defined( 'ABSPATH' ) ) {
    die( 'WordPress не загружен' );
}

global $wpdb;

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "📊 ПРОВЕРКА СВЯЗЕЙ МЕЖДУ ДОЛЖНОСТЯМИ, ОТДЕЛАМИ И СОСТАВАМИ\n";
echo "═══════════════════════════════════════════════════════════════\n\n";

// ============================================================
// 1️⃣ СТАТИСТИКА ПО СОСТАВАМ И ОТДЕЛАМ
// ============================================================
echo "1️⃣  СТАТИСТИКА ПО СОСТАВАМ И ОТДЕЛАМ\n";
echo "───────────────────────────────────────────────────────────────\n";

$stats = $wpdb->get_results(
    "SELECT 
        s.id as squad_id,
        s.squad_name,
        s.squad_id as squad_hash,
        COUNT(DISTINCT d.id) as dept_count,
        COUNT(DISTINCT j.id) as job_count
     FROM {$wpdb->prefix}arsenal_squad s
     LEFT JOIN {$wpdb->prefix}arsenal_staff_department d ON s.squad_id = d.squad_id
     LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles j ON d.id = j.department_id
     GROUP BY s.id, s.squad_name, s.squad_id
     ORDER BY s.id ASC"
);

foreach ( $stats as $stat ) {
    echo "  Состав: {$stat->squad_name} (ID={$stat->squad_id})\n";
    echo "    Squad Hash: {$stat->squad_hash}\n";
    echo "    Отделов: {$stat->dept_count}\n";
    echo "    Должностей: {$stat->job_count}\n\n";
}

// ============================================================
// 2️⃣ ПОЛНАЯ ТАБЛИЦА ДОЛЖНОСТЕЙ С ОТДЕЛАМИ И СОСТАВАМИ
// ============================================================
echo "2️⃣  ДОЛЖНОСТИ С ПРИНАДЛЕЖНОСТЬЮ К ОТДЕЛАМ И СОСТАВАМ\n";
echo "───────────────────────────────────────────────────────────────\n";

$jobs = $wpdb->get_results(
    "SELECT 
        j.id,
        j.job_title_name,
        d.id as dept_id,
        d.department_name,
        s.squad_name,
        s.id as squad_id,
        s.squad_id as squad_hash
     FROM {$wpdb->prefix}arsenal_staff_job_titles j
     LEFT JOIN {$wpdb->prefix}arsenal_staff_department d ON j.department_id = d.id
     LEFT JOIN {$wpdb->prefix}arsenal_squad s ON d.squad_id = s.squad_id
     ORDER BY s.squad_name, d.department_name, j.job_title_name"
);

printf("  %-3s | %-30s | %-25s | %-20s\n", "ID", "Должность", "Отдел", "Состав");
echo "  " . str_repeat("─", 82) . "\n";

foreach ( $jobs as $job ) {
    $squad = $job->squad_name ?? 'Не установлен';
    $dept = $job->department_name ?? 'Не установлен';
    printf("  %-3d | %-30s | %-25s | %-20s\n", 
        $job->id,
        substr($job->job_title_name, 0, 29),
        substr($dept, 0, 24),
        substr($squad, 0, 19)
    );
}

echo "\n";

// ============================================================
// 3️⃣ ПРОВЕРКА КОНФЛИКТОВ - СОТРУДНИКИ С НЕПРАВИЛЬНЫМИ СВЯЗЯМИ
// ============================================================
echo "3️⃣  ПРОВЕРКА КОНФЛИКТОВ - СОТРУДНИКИ С НЕПРАВИЛЬНЫМИ СВЯЗЯМИ\n";
echo "───────────────────────────────────────────────────────────────\n";

$conflicts = $wpdb->get_results(
    "SELECT 
        st.id,
        st.first_name,
        st.second_name,
        st.job_title_id,
        st.department_id,
        j.job_title_name,
        j.department_id as job_dept_id,
        d.department_name,
        sq.squad_name
     FROM {$wpdb->prefix}arsenal_staff st
     LEFT JOIN {$wpdb->prefix}arsenal_staff_job_titles j ON st.job_title_id = j.id
     LEFT JOIN {$wpdb->prefix}arsenal_staff_department d ON st.department_id = d.id
     LEFT JOIN {$wpdb->prefix}arsenal_squad sq ON st.squad_id = sq.id
     WHERE st.job_title_id IS NOT NULL 
       AND j.department_id IS NOT NULL
       AND j.department_id != st.department_id"
);

if ( ! empty( $conflicts ) ) {
    echo "  ⚠️  НАЙДЕНЫ КОНФЛИКТЫ:\n\n";
    foreach ( $conflicts as $conflict ) {
        echo "  ❌ Сотрудник: {$conflict->first_name} {$conflict->second_name} (ID={$conflict->id})\n";
        echo "     Состав: {$conflict->squad_name}\n";
        echo "     Должность: {$conflict->job_title_name} → принадлежит отделу '{$conflict->job_title_name}' (ID={$conflict->job_dept_id})\n";
        echo "     Указанный отдел: {$conflict->department_name} (ID={$conflict->department_id})\n";
        echo "     ➜ Должность в отделе {$conflict->job_dept_id}, но сотрудник в отделе {$conflict->department_id}\n\n";
    }
} else {
    echo "  ✅ Конфликтов не найдено. Все связи корректные!\n\n";
}

// ============================================================
// 4️⃣ ПРОВЕРКА СОТРУДНИКОВ С NULL ЗНАЧЕНИЯМИ
// ============================================================
echo "4️⃣  СОТРУДНИКИ С ПУСТЫМИ ПОЛЯМИ\n";
echo "───────────────────────────────────────────────────────────────\n";

$nulls = $wpdb->get_results(
    "SELECT 
        id,
        first_name,
        second_name,
        CASE WHEN job_title_id IS NULL THEN '❌ нет' ELSE '✅ да' END as has_job,
        CASE WHEN department_id IS NULL THEN '❌ нет' ELSE '✅ да' END as has_dept,
        CASE WHEN squad_id IS NULL THEN '❌ нет' ELSE '✅ да' END as has_squad
     FROM {$wpdb->prefix}arsenal_staff
     WHERE job_title_id IS NULL OR department_id IS NULL OR squad_id IS NULL
     ORDER BY id ASC"
);

if ( ! empty( $nulls ) ) {
    echo "  Найдено " . count( $nulls ) . " сотрудников с пустыми полями:\n\n";
    foreach ( $nulls as $null ) {
        echo "  - {$null->first_name} {$null->second_name} (ID={$null->id})\n";
        echo "    Должность: {$null->has_job}, Отдел: {$null->has_dept}, Состав: {$null->has_squad}\n\n";
    }
} else {
    echo "  ✅ Все сотрудники заполнены полностью!\n\n";
}

// ============================================================
// 5️⃣ ИТОГОВАЯ СТАТИСТИКА
// ============================================================
echo "5️⃣  ИТОГОВАЯ СТАТИСТИКА\n";
echo "───────────────────────────────────────────────────────────────\n";

$total_squads = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_squad" );
$total_depts = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff_department" );
$total_jobs = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff_job_titles" );
$total_staff = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}arsenal_staff" );

echo "  Составы: $total_squads\n";
echo "  Отделы: $total_depts\n";
echo "  Должности: $total_jobs\n";
echo "  Сотрудники: $total_staff\n\n";

$conflicts_count = count( $conflicts );
$nulls_count = count( $nulls );

echo "  ═══════════════════════════════════════════════════════════════\n";
if ( $conflicts_count == 0 && $nulls_count == 0 ) {
    echo "  ✅ ВСЕ СВЯЗИ КОРРЕКТНЫЕ - ГОТОВО К ИМПЛЕМЕНТАЦИИ ВАЛИДАЦИИ!\n";
} else {
    echo "  ⚠️  НАЙДЕНЫ ПРОБЛЕМЫ:\n";
    echo "     • Конфликтов: $conflicts_count\n";
    echo "     • Неполных записей: $nulls_count\n";
}
echo "  ═══════════════════════════════════════════════════════════════\n\n";
?>
