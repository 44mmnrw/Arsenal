<?php
require 'wp-load.php';

echo "🔍 Проверка отрисовки шаблона upcoming-match...\n\n";

// Получаем шаблон
ob_start();
include( get_template_directory() . '/template-parts/widgets/upcoming-match.php' );
$output = ob_get_clean();

// Ищем lottie-player
if ( strpos( $output, 'lottie-player' ) !== false ) {
	echo "✅ lottie-player найден в выводе\n";
	
	// Ищем путь к clock.json
	if ( strpos( $output, 'clock.json' ) !== false ) {
		echo "✅ clock.json путь найден\n";
	} else {
		echo "❌ clock.json путь НЕ найден\n";
	}
	
	// Показать первые 500 символов с lottie
	$pos = strpos( $output, '<lottie-player' );
	if ( $pos !== false ) {
		echo "\n📋 HTML фрагмент:\n";
		echo substr( $output, $pos, 300 ) . "...\n";
	}
} else {
	echo "❌ lottie-player НЕ найден в выводе\n";
	echo "\n📊 Проверяем, есть ли match-time-wrapper:\n";
	if ( strpos( $output, 'match-time-wrapper' ) !== false ) {
		echo "✅ match-time-wrapper найден\n";
		$pos = strpos( $output, 'match-time-wrapper' );
		echo "Вывод:\n";
		echo substr( $output, $pos, 200 ) . "...\n";
	} else {
		echo "❌ match-time-wrapper НЕ найден - значит ближайшего матча нет в БД\n";
	}
}
?>
