/**
 * Arsenal Team Manager - Admin JavaScript
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Подтверждение удаления
        $('.button-link-delete').on('click', function(e) {
            if (!confirm('Вы уверены?')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Автозаполнение английского имени транслитерацией
        $('#full_name_ru').on('blur', function() {
            var nameRu = $(this).val();
            if (nameRu && !$('#full_name').val()) {
                var translit = transliterate(nameRu);
                $('#full_name').val(translit);
            }
        });
        
        // Загрузка логотипа команды
        $('.upload-logo-btn').on('click', function(e) {
            e.preventDefault();
            
            // Проверяем наличие wp.media
            if (typeof wp === 'undefined' || typeof wp.media === 'undefined') {
                alert('Медиа библиотека WordPress не загружена.');
                return false;
            }
            
            var teamId = $(this).data('team-id');
            var fileFrame = wp.media({
                title: 'Выберите логотип команды',
                button: {
                    text: 'Выбрать логотип',
                },
                library: {
                    type: 'image'
                },
                multiple: false
            });
            
            // При выборе изображения
            fileFrame.on('select', function() {
                var attachment = fileFrame.state().get('selection').first().toJSON();
                var logoUrl = attachment.url || attachment.guid;
                
                // Сохраняем URL в поле логотипа
                $('#team-logo-url-' + teamId).val(logoUrl);
                
                // Обновляем превью
                var previewHtml = '<img src="' + logoUrl + '" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain; display: block;">';
                $('#team-logo-preview-' + teamId).html(previewHtml);
                
                // Логируем для отладки
                console.log('Логотип выбран для команды ' + teamId + ':', logoUrl);
            });
            
            // Открываем окно выбора файлов
            fileFrame.open();
        });
        
    });
    
    /**
     * Простая транслитерация
     */
    function transliterate(text) {
        var converter = {
            'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd',
            'е': 'e', 'ё': 'e', 'ж': 'zh', 'з': 'z', 'и': 'i',
            'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n',
            'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't',
            'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch',
            'ш': 'sh', 'щ': 'sch', 'ь': '', 'ы': 'y', 'ъ': '',
            'э': 'e', 'ю': 'yu', 'я': 'ya',
            
            'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D',
            'Е': 'E', 'Ё': 'E', 'Ж': 'Zh', 'З': 'Z', 'И': 'I',
            'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M', 'Н': 'N',
            'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T',
            'У': 'U', 'Ф': 'F', 'Х': 'H', 'Ц': 'Ts', 'Ч': 'Ch',
            'Ш': 'Sh', 'Щ': 'Sch', 'Ь': '', 'Ы': 'Y', 'Ъ': '',
            'Э': 'E', 'Ю': 'Yu', 'Я': 'Ya'
        };
        
        var result = '';
        for (var i = 0; i < text.length; i++) {
            if (converter[text[i]] !== undefined) {
                result += converter[text[i]];
            } else {
                result += text[i];
            }
        }
        return result;
    }
    
})(jQuery);
