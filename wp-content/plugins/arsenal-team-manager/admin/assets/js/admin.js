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
    
    // ========== Модальное окно с тренерами команды ==========
    
    // Открыть модаль с тренерами
    $(document).on('click', '.view-coaches-btn', function(e) {
        e.preventDefault();
        
        var teamId = $(this).data('team-id');
        var teamName = $(this).data('team-name');
        
        // Передаем данные в модаль
        $('#coaches-modal-title').text('Тренеры команды: ' + teamName);
        $('#coaches-modal-team-id').val(teamId);
        
        // Загружаем данные о тренерах - используем AJAX для получения данных
        $.ajax({
            url: arsenal_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'arsenal_get_team_coaches',
                team_id: teamId,
                nonce: arsenal_ajax.nonce
            },
            success: function(response) {
                
                var coachesContent = $('#coaches-modal-content');
                coachesContent.html('');
                
                if (!response.success) {
                    coachesContent.html('<div style="padding: 20px; text-align: center; color: #999;">Ошибка: ' + (response.data ? response.data.message : 'Неизвестная ошибка') + '</div>');
                    return;
                }
                
                // response.data уже содержит массив тренеров
                var coaches = response.data;
                if (!Array.isArray(coaches)) {
                    coaches = [];
                }
                
                if (!coaches || coaches.length === 0) {
                    coachesContent.html('<div style="padding: 20px; text-align: center; color: #999;">Нет данных о тренерах</div>');
                } else {
                    coaches.forEach(function(coach, index) {
                        var coachName = coach.coach_name || 'Неизвестный тренер';
                        var startDate = coach.start_date || '';
                        var endDate = coach.end_date || '';
                        
                        var isActive = endDate === '0000-00-00' || endDate === null || endDate === '';
                        var borderColor = isActive ? '#28a745' : '#6c757d';
                        
                        var startDateFormatted = 'Неизвестно';
                        if (startDate) {
                            var dateObj = new Date(startDate);
                            if (!isNaN(dateObj.getTime())) {
                                startDateFormatted = dateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                            }
                        }
                        
                        var endDateText = '';
                        if (isActive) {
                            endDateText = ' – <span style="color: #28a745; font-weight: 600;">По настоящее время</span>';
                        } else if (endDate && endDate !== '0000-00-00' && endDate !== '') {
                            var endDateObj = new Date(endDate);
                            if (!isNaN(endDateObj.getTime())) {
                                var endDateFormatted = endDateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                                endDateText = ' – По: ' + endDateFormatted;
                            }
                        }
                        
                        // Кнопка удаления только для последней (первой в отсортированном списке - самой новой) записи
                        var deleteButton = (index === 0) ? '<button class="delete-coach-btn" data-team-id="' + teamId + '" style="background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 12px; white-space: nowrap;">✕ Удалить</button>' : '';
                        
                        var coachHtml = '<div class="coach-item" style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px; border-left: 4px solid ' + borderColor + ';">' +
                            '<div style="display: flex; justify-content: space-between; align-items: flex-start;">' +
                            '<div>' +
                            '<div style="font-weight: 600; color: #0a0a0a; font-size: 14px;">' + coachName + '</div>' +
                            '<div style="color: #666; font-size: 12px; margin-top: 4px;">' +
                            'С: ' + startDateFormatted + endDateText +
                            '</div>' +
                            '</div>' +
                            deleteButton +
                            '</div>' +
                            '</div>';
                        
                        coachesContent.append(coachHtml);
                    });
                }
                
                // Показываем модаль
                $('#coaches-modal').addClass('active').show();
                
                // Загружаем список всех тренеров для datalist
                loadCoachesList();
            },
            error: function(xhr, status, error) {
                var coachesContent = $('#coaches-modal-content');
                coachesContent.html('<div style="padding: 20px; text-align: center; color: #d63638;">Ошибка при загрузке данных о тренерах</div>');
                $('#coaches-modal').addClass('active').show();
            }
        });
    });
    
    // Закрыть модаль с тренерами
    $(document).on('click', '#close-coaches-modal, #cancel-coaches-modal', function(e) {
        e.preventDefault();
        $('#coaches-modal').removeClass('active').hide();
    });
    
    // Закрыть при клике вне модали
    $(document).on('click', '#coaches-modal', function(e) {
        if ($(e.target).is('#coaches-modal')) {
            $('#coaches-modal').removeClass('active').hide();
        }
    });
    
    // Удалить последнюю запись о тренере
    $(document).on('click', '.delete-coach-btn', function(e) {
        e.preventDefault();
        
        var teamId = $(this).data('team-id');
        var coachItem = $(this).closest('.coach-item');
        var coachName = coachItem.find('div:first div:first').text().trim();
        
        if (!confirm('Вы уверены, что хотите удалить тренера "' + coachName + '" из БД?\n\nЭто удалит все его данные из системы.')) {
            return;
        }
        
        
        // Нужно получить coach_id. Сначала отправим запрос чтобы получить его или сделаем через другой механизм
        // На самом деле, нужно обновить обработчик на бэкенде. 
        // А пока вызовем перезагрузку истории (как при добавлении)
        $.ajax({
            url: arsenal_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'arsenal_delete_last_coach',
                team_id: teamId,
                nonce: arsenal_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    
                    // Перезагружаем историю тренеров
                    setTimeout(function() {
                        var teamId = $('#coaches-modal-team-id').val();
                        
                        $.ajax({
                            url: arsenal_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'arsenal_get_team_coaches',
                                nonce: arsenal_ajax.nonce,
                                team_id: teamId
                            },
                            success: function(response) {
                                
                                var coaches = response.data;
                                if (!Array.isArray(coaches)) {
                                    coaches = [];
                                }
                                
                                var coachesContent = $('#coaches-modal-content');
                                coachesContent.html('');
                                
                                if (!coaches || coaches.length === 0) {
                                    coachesContent.html('<div style="padding: 20px; text-align: center; color: #999;">Нет данных о тренерах</div>');
                                } else {
                                    coaches.forEach(function(coach, index) {
                                        var coachName = coach.coach_name || 'Неизвестный тренер';
                                        var startDate = coach.start_date || '';
                                        var endDate = coach.end_date || '';
                                        
                                        var isActive = endDate === '0000-00-00' || endDate === null || endDate === '';
                                        var borderColor = isActive ? '#28a745' : '#6c757d';
                                        
                                        var startDateFormatted = 'Неизвестно';
                                        if (startDate) {
                                            var dateObj = new Date(startDate);
                                            if (!isNaN(dateObj.getTime())) {
                                                startDateFormatted = dateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                                            }
                                        }
                                        
                                        var endDateText = '';
                                        if (isActive) {
                                            endDateText = ' – <span style="color: #28a745; font-weight: 600;">По настоящее время</span>';
                                        } else if (endDate && endDate !== '0000-00-00' && endDate !== '') {
                                            var endDateObj = new Date(endDate);
                                            if (!isNaN(endDateObj.getTime())) {
                                                var endDateFormatted = endDateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                                                endDateText = ' – По: ' + endDateFormatted;
                                            }
                                        }
                                        
                                        // Кнопка удаления только для последней (первой в отсортированном списке - самой новой) записи
                                        var deleteButton = (index === 0) ? '<button class="delete-coach-btn" data-team-id="' + teamId + '" style="background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 12px; white-space: nowrap;">✕ Удалить</button>' : '';
                                        
                                        var coachHtml = '<div class="coach-item" style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px; border-left: 4px solid ' + borderColor + ';">' +
                                            '<div style="display: flex; justify-content: space-between; align-items: flex-start;">' +
                                            '<div>' +
                                            '<div style="font-weight: 600; color: #0a0a0a; font-size: 14px;">' + coachName + '</div>' +
                                            '<div style="color: #666; font-size: 12px; margin-top: 4px;">' +
                                            'С: ' + startDateFormatted + endDateText +
                                            '</div>' +
                                            '</div>' +
                                            deleteButton +
                                            '</div>' +
                                            '</div>';
                                        
                                        coachesContent.append(coachHtml);
                                    });
                                }
                                
                            },
                            error: function(xhr, status, error) {
                            }
                        });
                    }, 500);
                } else {
                    alert('Ошибка: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Ошибка при удалении записи о тренере');
            }
        });
    });
    
    // Функция для загрузки списка тренеров в select
    function loadCoachesList() {
        $.ajax({
            url: arsenal_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'arsenal_get_all_coaches',
                nonce: arsenal_ajax.nonce
            },
            success: function(response) {
                
                var coachSelect = $('#coach-name-select');
                
                if (!coachSelect.length) {
                    return;
                }
                
                // Сохраняем заполнитель
                var placeholder = '<option value="">-- Выберите тренера --</option>';
                
                // Полностью перестраиваем select
                var newHtml = placeholder;
                
                if (response.success && response.data && Array.isArray(response.data)) {
                    response.data.forEach(function(coach) {
                        newHtml += '<option value="' + coach.coach_id + '">' + coach.name + '</option>';
                    });
                } else {
                }
                
                // Заменяем содержимое select
                coachSelect.html(newHtml);
            },
            error: function(xhr, status, error) {
            }
        });
    }
    
    // Перезагрузка истории тренеров
    function reloadCoachesHistory() {
        var teamId = $('#coaches-modal-team-id').val();
        if (!teamId) return;
        $.ajax({
            url: arsenal_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'arsenal_get_team_coaches',
                team_id: teamId,
                nonce: arsenal_ajax.nonce
            },
            success: function(response) {
                console.log('Updated coaches response:', response);
                
                var coachesContent = $('#coaches-modal-content');
                coachesContent.html('');
                
                if (!response.success) {
                    coachesContent.html('<div style="padding: 20px; text-align: center; color: #999;">Ошибка: ' + (response.data ? response.data.message : 'Неизвестная ошибка') + '</div>');
                    return;
                }
                
                var coaches = response.data;
                if (!Array.isArray(coaches)) {
                    coaches = [];
                }
                
                if (!coaches || coaches.length === 0) {
                    coachesContent.html('<div style="padding: 20px; text-align: center; color: #999;">Нет данных о тренерах</div>');
                } else {
                    coaches.forEach(function(coach, index) {
                        var coachName = coach.coach_name || 'Неизвестный тренер';
                        var startDate = coach.start_date || '';
                        var endDate = coach.end_date || '';
                        
                        var isActive = endDate === '0000-00-00' || endDate === null || endDate === '';
                        var borderColor = isActive ? '#28a745' : '#6c757d';
                        
                        var startDateFormatted = 'Неизвестно';
                        if (startDate) {
                            var dateObj = new Date(startDate);
                            if (!isNaN(dateObj.getTime())) {
                                startDateFormatted = dateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                            }
                        }
                        
                        var endDateText = '';
                        if (isActive) {
                            endDateText = ' – <span style="color: #28a745; font-weight: 600;">По настоящее время</span>';
                        } else if (endDate && endDate !== '0000-00-00' && endDate !== '') {
                            var endDateObj = new Date(endDate);
                            if (!isNaN(endDateObj.getTime())) {
                                var endDateFormatted = endDateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                                endDateText = ' – По: ' + endDateFormatted;
                            }
                        }
                        
                        var deleteButton = (index === 0) ? '<button class="delete-coach-btn" data-team-id="' + teamId + '" style="background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 12px; white-space: nowrap;">✕ Удалить</button>' : '';
                        
                        var coachHtml = '<div class="coach-item" style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px; border-left: 4px solid ' + borderColor + ';">' +
                            '<div style="display: flex; justify-content: space-between; align-items: flex-start;">' +
                            '<div>' +
                            '<div style="font-weight: 600; color: #0a0a0a; font-size: 14px;">' + coachName + '</div>' +
                            '<div style="color: #666; font-size: 12px; margin-top: 4px;">' +
                            'С: ' + startDateFormatted + endDateText +
                            '</div>' +
                            '</div>' +
                            deleteButton +
                            '</div>' +
                            '</div>';
                        
                        coachesContent.append(coachHtml);
                    });
                }
            },
            error: function(xhr, status, error) {
            }
        });
    }
    
    // Показать/скрыть кнопку удаления при выборе тренера
    $(document).on('change', '#coach-name-select', function(e) {
        var coachId = $(this).val().trim();
        var deleteBtn = $('#delete-coach-btn');
        
        if (coachId) {
            deleteBtn.show();
        } else {
            deleteBtn.hide();
        }
    });
    
    // Удалить выбранного тренера из БД
    $(document).on('click', '#delete-coach-btn', function(e) {
        e.preventDefault();
        
        var coachId = $('#coach-name-select').val().trim();
        var selectedText = $('#coach-name-select option:selected').text();
        
        if (!coachId) {
            alert('Выберите тренера для удаления');
            return;
        }
        
        if (!confirm('Вы уверены, что хотите удалить тренера "' + selectedText + '" из БД?\n\nЭто удалит все его данные из системы.')) {
            return;
        }
        
        $.ajax({
            url: arsenal_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'arsenal_delete_coach_from_db',
                coach_id: coachId,
                nonce: arsenal_ajax.nonce
            },
            success: function(response) {
                var messageDiv = $('#coach-form-message');
                
                if (response.success) {
                    messageDiv.removeClass('notice-error').addClass('notice-success').html('✓ ' + response.data.message).show();
                    
                    // Очищаем форму СРАЗУ
                    $('#coach-name-select').html('<option value="">-- Выберите тренера --</option>').val('').trigger('change');
                    $('#coach-start-date').val('');
                    $('#coach-end-date').val('');
                    $('#delete-coach-btn').hide();
                    
                    // Перезагружаем список тренеров через 1 секунду (как при добавлении)
                    setTimeout(function() {
                        var teamId = $('#coaches-modal-team-id').val();
                        
                        $.ajax({
                            url: arsenal_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'arsenal_get_team_coaches',
                                nonce: arsenal_ajax.nonce,
                                team_id: teamId
                            },
                            success: function(response) {
                                console.log('Got coaches data:', response);
                                
                                var coaches = response.data;
                                if (!Array.isArray(coaches)) {
                                    coaches = [];
                                }
                                
                                var coachesContent = $('#coaches-modal-content');
                                coachesContent.html('');
                                
                                if (!coaches || coaches.length === 0) {
                                    coachesContent.html('<div style="padding: 20px; text-align: center; color: #999;">Нет данных о тренерах</div>');
                                } else {
                                    coaches.forEach(function(coach, index) {
                                        var coachName = coach.coach_name || 'Неизвестный тренер';
                                        var startDate = coach.start_date || '';
                                        var endDate = coach.end_date || '';
                                        
                                        var isActive = endDate === '0000-00-00' || endDate === null || endDate === '';
                                        var borderColor = isActive ? '#28a745' : '#6c757d';
                                        
                                        var startDateFormatted = 'Неизвестно';
                                        if (startDate) {
                                            var dateObj = new Date(startDate);
                                            if (!isNaN(dateObj.getTime())) {
                                                startDateFormatted = dateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                                            }
                                        }
                                        
                                        var endDateText = '';
                                        if (isActive) {
                                            endDateText = ' – <span style="color: #28a745; font-weight: 600;">По настоящее время</span>';
                                        } else if (endDate && endDate !== '0000-00-00' && endDate !== '') {
                                            var endDateObj = new Date(endDate);
                                            if (!isNaN(endDateObj.getTime())) {
                                                var endDateFormatted = endDateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                                                endDateText = ' – По: ' + endDateFormatted;
                                            }
                                        }
                                        
                                        // Кнопка удаления только для последней (первой в отсортированном списке - самой новой) записи
                                        var deleteButton = (index === 0) ? '<button class="delete-coach-btn" data-team-id="' + teamId + '" style="background: #dc3545; color: white; border: none; padding: 4px 8px; border-radius: 3px; cursor: pointer; font-size: 12px; white-space: nowrap;">✕ Удалить</button>' : '';
                                        
                                        var coachHtml = '<div class="coach-item" style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px; border-left: 4px solid ' + borderColor + ';">' +
                                            '<div style="display: flex; justify-content: space-between; align-items: flex-start;">' +
                                            '<div>' +
                                            '<div style="font-weight: 600; color: #0a0a0a; font-size: 14px;">' + coachName + '</div>' +
                                            '<div style="color: #666; font-size: 12px; margin-top: 4px;">' +
                                            'С: ' + startDateFormatted + endDateText +
                                            '</div>' +
                                            '</div>' +
                                            deleteButton +
                                            '</div>' +
                                            '</div>';
                                        
                                        coachesContent.append(coachHtml);
                                    });
                                }
                                
                                // Перезагружаем и список всех тренеров
                                loadCoachesList();
                            },
                            error: function(xhr, status, error) {
                            }
                        });
                    }, 1000); // 1 секунда как при добавлении
                    
                    // Скрыть сообщение через 3 секунды
                    setTimeout(function() {
                        messageDiv.fadeOut(300, function() {
                            $(this).html('').hide();
                        });
                    }, 3000);
                } else {
                    console.log('Coach deletion failed:', response.data.message);
                    messageDiv.removeClass('notice-success').addClass('notice-error').html('✗ ' + response.data.message).show();
                }
            },
            error: function(xhr, status, error) {
                console.log('Delete coach error:', error);
                console.log('XHR:', xhr);
                $('#coach-form-message').removeClass('notice-success').addClass('notice-error').html('✗ Ошибка при удалении тренера').show();
                alert('Ошибка при удалении тренера: ' + error);
            }
        });
    });
    
    // Обработка отправки формы добавления тренера
    $(document).on('submit', '#add-coach-form', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var teamId = $('#coaches-modal-team-id').val();
        var coachId = $('#coach-name-select').val().trim();
        var startDate = $('#coach-start-date').val();
        var endDate = $('#coach-end-date').val();
        var messageDiv = $('#coach-form-message');
        
        if (!coachId || !startDate) {
            messageDiv.css('background-color', '#ffebee').css('color', '#d63638').html('Заполните все обязательные поля').show();
            return;
        }
        
        messageDiv.hide();
        
        $.ajax({
            url: arsenal_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'arsenal_add_coach',
                nonce: arsenal_ajax.nonce,
                team_id: teamId,
                coach_id: coachId,
                start_date: startDate,
                end_date: endDate
            },
            success: function(response) {
                console.log('Add coach response:', response);
                
                if (response.success) {
                    messageDiv.css('background-color', '#eafaf1').css('color', '#27ae60').html('✓ Тренер добавлен успешно').show();
                    
                    // Очищаем форму
                    form[0].reset();
                    
                    // Перезагружаем список тренеров через 1 секунду
                    setTimeout(function() {
                        $.ajax({
                            url: arsenal_ajax.ajax_url,
                            type: 'POST',
                            data: {
                                action: 'arsenal_get_team_coaches',
                                nonce: arsenal_ajax.nonce,
                                team_id: teamId
                            },
                            success: function(response) {
                                // Тот же код что и выше для загрузки списка
                                var coaches = response.data;
                                if (!Array.isArray(coaches)) {
                                    coaches = [];
                                }
                                
                                var coachesContent = $('#coaches-modal-content');
                                coachesContent.html('');
                                
                                if (!coaches || coaches.length === 0) {
                                    coachesContent.html('<div style="padding: 20px; text-align: center; color: #999;">Нет данных о тренерах</div>');
                                } else {
                                    coaches.forEach(function(coach) {
                                        var coachName = coach.coach_name || 'Неизвестный тренер';
                                        var startDate = coach.start_date || '';
                                        var endDate = coach.end_date || '';
                                        
                                        var isActive = endDate === '0000-00-00' || endDate === null || endDate === '';
                                        var borderColor = isActive ? '#28a745' : '#6c757d';
                                        
                                        var startDateFormatted = 'Неизвестно';
                                        if (startDate) {
                                            var dateObj = new Date(startDate);
                                            if (!isNaN(dateObj.getTime())) {
                                                startDateFormatted = dateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                                            }
                                        }
                                        
                                        var endDateText = '';
                                        if (isActive) {
                                            endDateText = ' – <span style="color: #28a745; font-weight: 600;">По настоящее время</span>';
                                        } else if (endDate && endDate !== '0000-00-00' && endDate !== '') {
                                            var endDateObj = new Date(endDate);
                                            if (!isNaN(endDateObj.getTime())) {
                                                var endDateFormatted = endDateObj.toLocaleDateString('ru-RU', { year: 'numeric', month: '2-digit', day: '2-digit' });
                                                endDateText = ' – По: ' + endDateFormatted;
                                            }
                                        }
                                        
                                        var coachHtml = '<div class="coach-item" style="margin-bottom: 10px; padding: 10px; background: #f9f9f9; border-radius: 4px; border-left: 4px solid ' + borderColor + ';">' +
                                            '<div style="font-weight: 600; color: #0a0a0a; font-size: 14px;">' + coachName + '</div>' +
                                            '<div style="color: #666; font-size: 12px; margin-top: 4px;">' +
                                            'С: ' + startDateFormatted + endDateText +
                                            '</div>' +
                                            '</div>';
                                        
                                        coachesContent.append(coachHtml);
                                    });
                                }
                            }
                        });
                    }, 1000);
                } else {
                    messageDiv.css('background-color', '#ffebee').css('color', '#d63638').html('Ошибка: ' + (response.data ? response.data.message : 'Неизвестная ошибка')).show();
                }
            },
            error: function(xhr, status, error) {
                console.log('AJAX error:', xhr, status, error);
                messageDiv.css('background-color', '#ffebee').css('color', '#d63638').html('Ошибка сервера при добавлении тренера').show();
            }
        });
    });
    
    // ========== Модальное окно добавления НОВОГО тренера ==========
    
    // Открыть модаль добавления нового тренера
    $(document).on('click', '#add-new-coach-btn', function(e) {
        e.preventDefault();
        $('#new-coach-modal').addClass('active').show();
    });
    
    // Закрыть модаль добавления нового тренера
    $(document).on('click', '#close-new-coach-modal, #new-coach-modal', function(e) {
        if ($(this).is('#new-coach-modal') && !$(e.target).is('#new-coach-modal')) {
            return;
        }
        e.preventDefault();
        $('#new-coach-modal').removeClass('active').hide();
    });
    
    // Обработка отправки формы добавления нового тренера
    $(document).on('submit', '#new-coach-form', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var coachName = $('#new-coach-name').val().trim();
        var messageDiv = $('#new-coach-form-message');
        
        if (!coachName) {
            messageDiv.css('background-color', '#ffebee').css('color', '#d63638').html('Введите имя тренера').show();
            return;
        }
        
        messageDiv.hide();
        
        $.ajax({
            url: arsenal_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'arsenal_create_new_coach',
                nonce: arsenal_ajax.nonce,
                coach_name: coachName
            },
            success: function(response) {
                console.log('Create coach response:', response);
                
                if (response.success) {
                    messageDiv.css('background-color', '#eafaf1').css('color', '#27ae60').html('✓ Тренер добавлен успешно').show();
                    
                    // Очищаем форму
                    form[0].reset();
                    
                    // Перезагружаем список тренеров
                    loadCoachesList();
                    
                    // Закрываем модаль через 1.5 секунды
                    setTimeout(function() {
                        $('#new-coach-modal').removeClass('active').hide();
                    }, 1500);
                } else {
                    messageDiv.css('background-color', '#ffebee').css('color', '#d63638').html('Ошибка: ' + response.data.message).show();
                }
            },
            error: function(xhr, status, error) {
                console.log('Create coach error:', error);
                messageDiv.css('background-color', '#ffebee').css('color', '#d63638').html('Ошибка сервера при добавлении тренера').show();
            }
        });
    });
    
})(jQuery);
