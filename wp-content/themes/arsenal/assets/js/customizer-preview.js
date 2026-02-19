/**
 * Arsenal Theme Customizer Preview
 *
 * Handles live preview updates and click-to-edit functionality in the Customizer
 *
 * @package Arsenal
 * @since 1.0.0
 */

( function( $ ) {
	'use strict';

	// Получить nonce из глобального объекта (если доступен)
	const nonce = typeof arsenalCustomizerNonce !== 'undefined' ? arsenalCustomizerNonce : '';

	// Ждём, когда Customizer будет готов
	if ( typeof wp !== 'undefined' && wp.customize ) {
		wp.customize.bind( 'preview-ready', function() {
			console.log( 'Customizer Preview Ready' );

			// === CLICK TO EDIT: Красивый popup для редактирования ===
			$( document ).on( 'click', '[data-customize-setting-link]', function( e ) {
				e.preventDefault();
				e.stopPropagation();

				const settingId = $( this ).data( 'customize-setting-link' );
				const $element = $( this );
				const currentText = $element.text();
				const offset = $element.offset();

				console.log( 'Clicked on editable element:', settingId, 'Text:', currentText );

				// Создаём popup HTML
				const popupHTML = '<div class="arsenal-edit-popup" style="position: fixed; z-index: 9999; box-shadow: 0 4px 12px rgba(0,0,0,0.15); border-radius: 8px; background: white; padding: 20px; min-width: 300px;">' +
					'<div class="popup-header" style="margin-bottom: 15px; font-weight: 600; font-size: 14px; color: #333;">Редактировать текст</div>' +
					'<input type="text" class="popup-input" value="' + esc_attr( currentText ) + '" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; box-sizing: border-box; margin-bottom: 15px;">' +
					'<div class="popup-actions" style="display: flex; gap: 10px; justify-content: flex-end;">' +
					'<button class="popup-cancel" style="padding: 8px 16px; border: 1px solid #ddd; background: #f5f5f5; border-radius: 4px; cursor: pointer; font-size: 13px;">Отмена</button>' +
					'<button class="popup-save" style="padding: 8px 16px; border: none; background: #0073aa; color: white; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 600;">Сохранить</button>' +
					'</div>' +
					'</div>';

				// Удаляем старый popup если есть
				$( '.arsenal-edit-popup' ).remove();

				// Добавляем новый popup
				const $popup = $( popupHTML );
				$( 'body' ).append( $popup );

				// Позиционируем popup возле элемента
				const popupLeft = offset.left;
				const popupTop = offset.top + $element.outerHeight() + 10;

				$popup.css( {
					'left': popupLeft + 'px',
					'top': popupTop + 'px'
				} );

				// Фокус на input
				const $input = $popup.find( '.popup-input' );
				$input.focus();
				$input.select();

				// === СОХРАНИТЬ ===
				$popup.find( '.popup-save' ).on( 'click', function() {
					const newText = $input.val().trim();

					if ( newText && newText !== currentText ) {
						console.log( 'Saving new text:', newText, 'to setting:', settingId );
						
						// Обновляем preview
						wp.customize( settingId ).set( newText );

						// Отправляем на сервер через AJAX
						$.ajax( {
							url: wp.ajax.settings.url,
							type: 'POST',
							data: {
								action: 'arsenal_save_customizer_setting',
								setting_id: settingId,
								setting_value: newText,
								nonce: nonce
							},
							success: function( response ) {
								console.log( 'Setting saved to database:', response );
							},
							error: function( xhr, status, error ) {
								console.error( 'Error saving setting:', error );
							}
						} );

						// Закрываем popup
						setTimeout( function() {
							$popup.fadeOut( 200, function() {
								$popup.remove();
								
								// Переинициализируем иконку после сохранения
								if ( ! $element.find( '.customize-edit-hint' ).length ) {
									$element.append( '<span class="customize-edit-hint dashicons dashicons-edit" style="position: absolute; top: 2px; right: 5px; width: 24px; height: 24px; line-height: 24px; background: #0073aa; border: 2px solid white; border-radius: 50%; color: white; font-size: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); pointer-events: none;"></span>' );
								}
							} );
						}, 300 );
					} else {
						$popup.fadeOut( 200, function() {
							$popup.remove();
						} );
					}
				} );

				// === ОТМЕНА ===
				$popup.find( '.popup-cancel' ).on( 'click', function() {
					$popup.fadeOut( 200, function() {
						$popup.remove();
					} );
				} );

				// === ENTER для сохранения, ESC для отмены ===
				$input.on( 'keydown', function( e ) {
					if ( e.key === 'Enter' ) {
						$popup.find( '.popup-save' ).click();
					} else if ( e.key === 'Escape' ) {
						$popup.find( '.popup-cancel' ).click();
					}
				} );

				// === ЗАКРЫТЬ при клике вне popup ===
				$( document ).on( 'click.popup', function( e ) {
					if ( ! $( e.target ).closest( '.arsenal-edit-popup' ).length ) {
						$popup.fadeOut( 200, function() {
							$popup.remove();
						} );
						$( document ).off( 'click.popup' );
					}
				} );
			} );

			// === LIVE PREVIEW: Обновляем текст при редактировании в Customizer ===

			// Текст кнопки "Подать заявку"
			wp.customize( 'arsenal_academy_button_apply_text', function( value ) {
				value.bind( function( to ) {
					$( '.academy-button-apply' ).text( to );
					console.log( 'Updated button apply text:', to );
				} );
			} );

			// Текст кнопки "Контакты"
			wp.customize( 'arsenal_academy_button_contacts_text', function( value ) {
				value.bind( function( to ) {
					$( '.academy-button-contacts' ).text( to );
					console.log( 'Updated button contacts text:', to );
				} );
			} );

			// === ИНИЦИАЛИЗАЦИЯ: Добавляем иконку редактирования ко всем элементам ===
			$( '[data-customize-setting-link]' ).each( function() {
				if ( ! $( this ).find( '.customize-edit-hint' ).length ) {
					$( this ).css( 'position', 'relative' );
					$( this ).append( '<span class="customize-edit-hint dashicons dashicons-edit" style="position: absolute; top: 2px; right: 5px; width: 24px; height: 24px; line-height: 24px; background: #0073aa; border: 2px solid white; border-radius: 50%; color: white; font-size: 14px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 4px rgba(0,0,0,0.2); pointer-events: none;"></span>' );
				}
			} );

			// === HOVER EFFECTS: Изменить курсор при наведении ===
			$( document ).on( 'mouseenter', '[data-customize-setting-link]', function() {
				$( this ).css( {
					'cursor': 'pointer',
					'opacity': '0.8',
					'transition': 'opacity 0.2s ease'
				} );
			} );

			$( document ).on( 'mouseleave', '[data-customize-setting-link]', function() {
				$( this ).css( {
					'opacity': '1'
				} );
			} );
		} );
	}

	/**
	 * Вспомогательная функция для экранирования HTML атрибутов
	 */
	function esc_attr( text ) {
		const div = document.createElement( 'div' );
		div.textContent = text;
		return div.innerHTML;
	}

} )( jQuery );
