/**
 * Arsenal Theme Customizer Controls
 *
 * Allows clicking on elements in the preview to edit them in the Customizer
 *
 * @package Arsenal
 * @since 1.0.0
 */

( function( $ ) {
	'use strict';

	// Обработчик клика на элементы с data-customize-setting-link
	$( document ).on( 'click', '[data-customize-setting-link]', function( e ) {
		e.preventDefault();

		var settingId = $( this ).data( 'customize-setting-link' );

		if ( settingId && wp.customize.control( settingId ) ) {
			// Открыть кастомайзер и сфокусировать на нужной настройке
			wp.customize.control( settingId ).focus();
		}
	} );

	// Добавить стиль при наведении на редактируемые элементы
	$( 'body' ).on( 'mouseenter', '[data-customize-setting-link]', function() {
		$( this ).css( {
			'cursor': 'pointer',
			'position': 'relative'
		} );

		// Добавить визуальный индикатор (если это кнопка/ссылка)
		if ( ! $( this ).data( 'has-indicator' ) ) {
			$( this ).append( '<span class="customize-edit-hint" style="display: none;">✎</span>' );
			$( this ).data( 'has-indicator', true );
		}

		$( this ).find( '.customize-edit-hint' ).fadeIn( 100 );
	} );

	// Убрать индикатор при уходе курсора
	$( 'body' ).on( 'mouseleave', '[data-customize-setting-link]', function() {
		$( this ).find( '.customize-edit-hint' ).fadeOut( 100 );
	} );

} )( jQuery );
