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
        
        // Загрузка логотипа команды - ТОЧНО КАК В WORDPRESS
        $('.upload-logo-btn').on('click', function(e) {
            e.preventDefault();
            
            var teamId = $(this).data('team-id');
            var button = $(this);
            
            // Сохраняем team_id для обработчика
            window.arsenalTeamId = teamId;
            window.arsenalButton = button;
            
            // Переопределяем обработчик перед открытием (как в Posts)
            wp.media.editor.send.attachment = function(props, attachment) {
                var teamId = window.arsenalTeamId;
                var button = window.arsenalButton;
                var fullUrl = attachment.url;
                
                // Конвертируем в относительный URL
                var relativeUrl = fullUrl;
                var homeUrl = '<?php echo home_url(); ?>';
                if (relativeUrl.indexOf(homeUrl) === 0) {
                    relativeUrl = relativeUrl.substring(homeUrl.length);
                    if (relativeUrl.charAt(0) !== '/') {
                        relativeUrl = '/' + relativeUrl;
                    }
                }
                
                // Обновляем превью
                $('#team-logo-preview-' + teamId).parent().html(
                    '<img id="team-logo-preview-' + teamId + '" src="' + fullUrl + '" alt="Team Logo">'
                );
                
                // Показываем кнопку удаления
                button.closest('.teams-card').find('.remove-logo-btn[data-team-id="' + teamId + '"]').show();
                
                // AJAX сохранение
                $.post(arsenal_ajax.ajax_url, {
                    action: 'arsenal_save_team_logo',
                    team_id: teamId,
                    logo_url: relativeUrl,
                    nonce: arsenal_ajax.save_team_logo_nonce
                });
            };
            
            // Открываем галерею как в записях (кнопка не станет серой!)
            wp.media.editor.open(button);
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
