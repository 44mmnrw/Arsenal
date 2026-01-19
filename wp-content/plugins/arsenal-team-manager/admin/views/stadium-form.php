<?php
/**
 * Шаблон: Форма добавления/редактирования стадиона в админке
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_title = $is_edit ? 'Редактировать стадион' : 'Добавить новый стадион';
$form_action = $is_edit ? 'arsenal_update_stadium' : 'arsenal_create_stadium';
?>

<div class="wrap">
    <div class="stadium-form-wrapper">
        <h1><?php echo esc_html( $page_title ); ?></h1>

        <!-- Сообщения об ошибке -->
        <?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
            <div class="notice notice-error is-dismissible" style="margin: 20px 0;">
                <p><?php esc_html_e( 'Произошла ошибка при сохранении стадиона!', 'arsenal-team-manager' ); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="stadium-form">

            <input type="hidden" name="action" value="<?php echo esc_attr( $form_action ); ?>">

            <?php if ( $is_edit ) : ?>
                <input type="hidden" name="stadium_id" value="<?php echo intval( $stadium->id ); ?>">
            <?php endif; ?>

            <?php wp_nonce_field( 'arsenal_stadium_form', 'arsenal_stadium_nonce' ); ?>

            <!-- Единый список иконок для всех секций -->
            <script>
            const STADIUM_FORM_ICONS = [
                // Основные иконки
                { id: 'icon-calendar', name: '📅 Календарь' },
                { id: 'icon-date', name: '📆 Дата' },
                { id: 'icon-clock', name: '🕐 Часы/Время' },
                { id: 'icon-people', name: '👥 Люди/Фанаты' },
                { id: 'icon-place', name: '📍 Место/Локация' },
                { id: 'icon-phone', name: '☎️ Телефон' },
                { id: 'icon-email', name: '📧 Email' },
                
                // Документы и события
                { id: 'icon-report', name: '📄 Отчет/Документ' },
                { id: 'icon-event', name: '⚡ События/Молния' },
                { id: 'icon-checkbox', name: '✓ Чекбокс/Галочка' },
                { id: 'icon-chart', name: '📊 График/Статистика' },
                
                // Спорт и активность
                { id: 'icon-dumbbell', name: '🏋️ Гантель/Тренажер' },
                { id: 'icon-stadium', name: '🏟️ Стадион' },
                { id: 'icon-cup', name: '🏆 Кубок/Трофей' },
                { id: 'icon-medal', name: '🎖️ Медаль' },
                
                // Транспорт и передвижение
                { id: 'icon-car', name: '🚗 Машина/Транспорт' },
                { id: 'icon-map', name: '🗺️ Карта' },
                
                // Стрелки
                { id: 'icon-arrow-right', name: '➡️ Стрелка вправо' },
                { id: 'icon-arrow-left', name: '⬅️ Стрелка влево' },
                { id: 'icon-arrow-up', name: '⬆️ Стрелка вверх' },
                { id: 'icon-arrow-down', name: '⬇️ Стрелка вниз' },
                { id: 'icon-arrow-banner', name: '🔀 Стрелка баннера' },
                { id: 'icon-chevron-left', name: '◀ Шеврон влево' },
                { id: 'icon-chevron-right', name: '▶ Шеврон вправо' },
                
                // Социальные сети
                { id: 'icon-facebook', name: '📱 Facebook' },
                { id: 'icon-instagram', name: '📱 Instagram' },
                { id: 'icon-youtube', name: '📹 YouTube' },
                { id: 'icon-telegram', name: '✈️ Telegram' },
                { id: 'icon-vk', name: '🔗 VK' },
                
                // Персонал
                { id: 'icon-staff-stat', name: '📋 Статистика персонала' },
                { id: 'icon-bio', name: '👤 Биография' },
                { id: 'icon-career', name: '💼 Карьера' },
                { id: 'icon-partner', name: '🤝 Партнер' },
                
                // Утилиты
                { id: 'icon-ruler', name: '📏 Линейка/Размер' },
                { id: 'icon-medicine', name: '⛑️ Медицина/Крест' },
                { id: 'icon-fact', name: '👁️ Интересный факт' },
                { id: 'icon-team-placeholder', name: '⬜ Заполнитель команды' },
            ];
            </script>

            <!-- Левая колонка: Основная информация -->
            <div class="stadium-form-section stadium-form-left">
                <div class="section-header">
                    📋 Основная информация
                </div>

                <div class="form-group">
                    <label for="name">Название<span class="required">*</span></label>
                    <input type="text" name="name" id="name" required 
                           value="<?php echo ! empty( $stadium ) ? esc_attr( $stadium->name ) : ''; ?>"
                           placeholder="Название стадиона">
                </div>

                <div class="form-group">
                    <label for="city">Город</label>
                    <input type="text" name="city" id="city" 
                           value="<?php echo ! empty( $stadium ) && ! empty( $stadium->city ) ? esc_attr( $stadium->city ) : ''; ?>"
                           placeholder="Город, где расположен стадион">
                </div>

                <div class="form-group">
                    <label for="capacity">Вместимость (мест)</label>
                    <input type="number" name="capacity" id="capacity" min="0" 
                           value="<?php echo ! empty( $stadium ) && ! is_null( $stadium->capacity ) ? intval( $stadium->capacity ) : ''; ?>"
                           placeholder="Количество мест">
                </div>

                <div class="form-group">
                    <label for="open_date">Год открытия</label>
                    <input type="number" name="open_date" id="open_date" min="1900" max="2099" 
                           value="<?php echo ! empty( $stadium ) && ! empty( $stadium->open_date ) ? esc_attr( $stadium->open_date ) : ''; ?>"
                           placeholder="2000">
                </div>
            </div>

            <!-- Правая колонка: Фото -->
            <div class="stadium-form-section stadium-form-right">
                <div class="section-header">
                    🖼️ Фото стадиона
                </div>

                <div class="form-group">
                    <label for="photo">Загрузить фото</label>
                    <input type="file" name="photo" id="photo" accept="image/*" class="file-input">
                    <p class="form-description">Форматы: JPG, PNG, GIF</p>
                </div>

                <?php if ( $is_edit && ! empty( $stadium->photo_url ) ) : ?>
                    <div class="form-group">
                        <label>Текущее фото:</label>
                        <?php
                        $photo_url = $stadium->photo_url;
                        if ( ! str_starts_with( $photo_url, 'http://' ) && ! str_starts_with( $photo_url, 'https://' ) ) {
                            $photo_url = home_url( $photo_url );
                        }
                        ?>
                        <img src="<?php echo esc_url( $photo_url ); ?>" 
                             alt="<?php echo esc_attr( $stadium->name ); ?>"
                             class="stadium-preview-image">
                        <p class="form-description">Загрузите новое изображение, чтобы заменить</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Полная ширина: Описание -->
            <div class="stadium-form-section stadium-form-full">
                <div class="section-header">
                    📝 Описание стадиона
                </div>

                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea name="description" id="description" rows="5" 
                              placeholder="Опишите историю и особенности стадиона"><?php echo ! empty( $stadium ) && ! empty( $stadium->description ) ? esc_textarea( $stadium->description ) : ''; ?></textarea>
                </div>
            </div>

            <!-- Полная ширина: История -->
            <div class="stadium-form-section stadium-form-full">
                <div class="section-header">
                    🏛️ История стадиона
                </div>

                <div class="form-group">
                    <div id="history-editor" style="margin-bottom: 10px;">
                        <div style="margin-bottom: 10px;">
                            <button type="button" id="add-history-btn" class="button">+ Добавить событие</button>
                        </div>
                        <div id="history-list"></div>
                    </div>
                    <textarea name="history" id="history" rows="5" style="display: none;"><?php 
                        echo ! empty( $stadium ) && ! empty( $stadium->history ) ? esc_textarea( $stadium->history ) : ''; 
                    ?></textarea>
                </div>
            </div>

            <!-- В день матча -->
            <div class="stadium-form-section stadium-form-full">
                <div class="section-header">
                    🎫 В день матча
                </div>

                <div id="match-day-editor" style="margin-bottom: 10px;">
                    <div style="margin-bottom: 10px;">
                        <button type="button" id="add-match-day-item-btn" class="button" style="margin-bottom: 10px;">+ Добавить пункт</button>
                    </div>
                    <div id="match-day-list"></div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <label for="on_date">Данные (скрытое поле JSON)</label>
                    <textarea name="on_date" id="on_date" rows="5" class="json-field" style="display: none;"><?php 
                        echo ! empty( $stadium ) && ! empty( $stadium->on_date ) ? esc_textarea( $stadium->on_date ) : ''; 
                    ?></textarea>
                    <p class="form-description">Редактируйте информацию используя форму выше</p>
                </div>

                <script>
                jQuery(function($) {
                    function renderMatchDayItems() {
                        const textarea = $('#on_date');
                        const json = textarea.val().trim();
                        const list = $('#match-day-list');
                        list.empty();

                        let items = [];
                        if (json) {
                            try {
                                items = JSON.parse(json);
                                if (!Array.isArray(items)) items = [];
                            } catch(e) {
                                console.error('Ошибка парсинга JSON:', e);
                            }
                        }

                        items.forEach((item, idx) => {
                            const text = item.text || item;
                            const html = $('<div class="match-day-item"></div>')
                                .html(`
                                    <div class="match-day-item-row">
                                        <input type="text" class="match-day-text" placeholder="Например: Ворота открываются за 1 час" value="${escapeHtml(String(text))}">
                                        <button type="button" class="stadium-form-remove-btn" data-idx="${idx}">✕</button>
                                    </div>
                                `)
                                .on('change', 'input', updateJSON)
                                .on('click', '.stadium-form-remove-btn', function() {
                                    $(this).closest('.match-day-item').remove();
                                    updateJSON();
                                });
                            list.append(html);
                        });
                    }

                    function updateJSON() {
                        const items = [];
                        $('#match-day-list .match-day-item').each(function() {
                            const text = $(this).find('.match-day-text').val();
                            if (text) {
                                items.push({ text: text });
                            }
                        });
                        $('#on_date').val(JSON.stringify(items, null, 2));
                    }

                    function escapeHtml(text) {
                        const div = document.createElement('div');
                        div.textContent = text;
                        return div.innerHTML;
                    }

                    $('#add-match-day-item-btn').on('click', function(e) {
                        e.preventDefault();
                        const textarea = $('#on_date');
                        let items = [];
                        if (textarea.val().trim()) {
                            try {
                                items = JSON.parse(textarea.val());
                                if (!Array.isArray(items)) items = [];
                            } catch(e) {}
                        }
                        
                        items.push({ text: '' });
                        textarea.val(JSON.stringify(items, null, 2));
                        renderMatchDayItems();
                        return false;
                    });

                    // Инициализация
                    renderMatchDayItems();
                });
                </script>
            </div>

            <!-- 3-колончная сетка для всех редакторов -->
            <div class="stadium-form-editors">
                <div class="stadium-form-section">
                    <div class="section-header">
                        📞 Контакты
                    </div>

                    <div id="contacts-editor" style="margin-bottom: 10px;">
                        <div style="margin-bottom: 10px;">
                            <button type="button" id="add-contact-btn" class="button" style="margin-bottom: 10px;">+ Добавить контакт</button>
                        </div>
                        <div id="contacts-list"></div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                       
                        <textarea name="contacts" id="contacts" rows="5" class="json-field" style="display: none;"><?php 
                            echo ! empty( $stadium ) && ! empty( $stadium->contacts ) ? esc_textarea( $stadium->contacts ) : ''; 
                        ?></textarea>
                        
                    </div>

                    <script>
                    jQuery(function($) {
                        function renderContacts() {
                            const textarea = $('#contacts');
                            const json = textarea.val().trim();
                            const list = $('#contacts-list');
                            list.empty();

                            let contacts = {};
                            if (json) {
                                try {
                                    contacts = JSON.parse(json);
                                    if (typeof contacts !== 'object' || Array.isArray(contacts)) contacts = {};
                                } catch(e) {
                                    console.error('Ошибка парсинга JSON:', e);
                                }
                            }

                            Object.entries(contacts).forEach(([key, obj], idx) => {
                                const value = (typeof obj === 'object' && obj.value) ? obj.value : obj;
                                const icon = (typeof obj === 'object' && obj.icon) ? obj.icon : 'icon-phone';
                                
                                const iconOptions = STADIUM_FORM_ICONS.map(ic => 
                                    `<option value="${ic.id}" ${icon === ic.id ? 'selected' : ''}>${ic.name}</option>`
                                ).join('');

                                const html = $('<div class="contact-item"></div>')
                                    .html(`
                                        <div class="contact-item-row">
                                            <input type="text" class="contact-value" placeholder="Значение (например: +375 29 XXX-XX-XX)" value="${escapeHtml(String(value))}">
                                            <select class="contact-icon">
                                                ${iconOptions}
                                            </select>
                                            <button type="button" class="stadium-form-remove-btn" data-idx="${idx}">✕</button>
                                        </div>
                                    `)
                                    .on('change', 'input, select', updateJSON)
                                    .on('click', '.stadium-form-remove-btn', function() {
                                        $(this).closest('.contact-item').remove();
                                        updateJSON();
                                    });
                                list.append(html);
                            });
                        }

                        function updateJSON() {
                            const contacts = {};
                            $('#contacts-list .contact-item').each(function(idx) {
                                const value = $(this).find('.contact-value').val();
                                const icon = $(this).find('.contact-icon').val();
                                if (value) {
                                    const key = 'contact_' + (idx + 1);
                                    contacts[key] = {
                                        value: value,
                                        icon: icon
                                    };
                                }
                            });
                            $('#contacts').val(JSON.stringify(contacts, null, 2));
                        }

                        function escapeHtml(text) {
                            const div = document.createElement('div');
                            div.textContent = text;
                            return div.innerHTML;
                        }

                        $('#add-contact-btn').on('click', function(e) {
                            e.preventDefault();
                            const textarea = $('#contacts');
                            let contacts = {};
                            if (textarea.val().trim()) {
                                try {
                                    contacts = JSON.parse(textarea.val());
                                    if (typeof contacts !== 'object' || Array.isArray(contacts)) contacts = {};
                                } catch(e) {}
                            }
                            
                            // Генерируем уникальный ключ
                            let counter = 1;
                            let newKey = 'contact_' + counter;
                            while (contacts[newKey] !== undefined) {
                                counter++;
                                newKey = 'contact_' + counter;
                            }
                            
                            contacts[newKey] = { value: '', icon: 'icon-phone' };
                            textarea.val(JSON.stringify(contacts, null, 2));
                            renderContacts();
                            return false;
                        });

                        // Инициализация
                        renderContacts();
                    });
                    </script>
                </div>

                <div class="stadium-form-section">
                    <div class="section-header">
                        🏗️ Инфраструктура
                    </div>

                    <div id="infrastructure-editor" style="margin-bottom: 10px;">
                        <div style="margin-bottom: 10px;">
                            <button type="button" id="add-infrastructure-btn" class="button" style="margin-bottom: 10px;">+ Добавить объект</button>
                        </div>
                        <div id="infrastructure-list"></div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                       
                        <textarea name="infrastructure" id="infrastructure" rows="5" class="json-field" style="display: none;"><?php 
                            echo ! empty( $stadium ) && ! empty( $stadium->infrastructure ) ? esc_textarea( $stadium->infrastructure ) : ''; 
                        ?></textarea>
                        
                    </div>
                </div>

            <!-- Скрипты для инфраструктуры и истории -->
            <script>
                jQuery(function($) {

                        function renderInfrastructure() {
                            const textarea = $('#infrastructure');
                            const json = textarea.val().trim();
                            const list = $('#infrastructure-list');
                            list.empty();

                            let infrastructure = {};
                            if (json) {
                                try {
                                    infrastructure = JSON.parse(json);
                                    if (typeof infrastructure !== 'object' || Array.isArray(infrastructure)) infrastructure = {};
                                } catch(e) {
                                    console.error('Ошибка парсинга JSON:', e);
                                }
                            }

                            Object.entries(infrastructure).forEach(([key, obj], idx) => {
                                const name = (typeof obj === 'object') ? obj.name : obj;
                                const icon = (typeof obj === 'object') ? obj.icon : 'icon-place';
                                
                                const iconOptions = STADIUM_FORM_ICONS.map(ic => 
                                    `<option value="${ic.id}" ${icon === ic.id ? 'selected' : ''}>${ic.name}</option>`
                                ).join('');

                                const html = $('<div class="infrastructure-item"></div>')
                                    .html(`
                                        <div class="infrastructure-item-row">
                                            <input type="text" class="infra-name" placeholder="Название (например: Парковка)" value="${escapeHtml(key)}">
                                            <input type="text" class="infra-value" placeholder="Значение (например: 400 мест)" value="${escapeHtml(String(name))}">
                                            <select class="infra-icon">
                                                ${iconOptions}
                                            </select>
                                            <button type="button" class="stadium-form-remove-btn" data-idx="${idx}">✕</button>
                                        </div>
                                    `)
                                    .on('change', 'input, select', updateJSON)
                                    .on('click', '.infrastructure-remove', function() {
                                        $(this).closest('.infrastructure-item').remove();
                                        updateJSON();
                                    });
                                list.append(html);
                            });
                        }

                        function updateJSON() {
                            const infrastructure = {};
                            $('#infrastructure-list .infrastructure-item').each(function() {
                                const name = $(this).find('.infra-name').val();
                                const value = $(this).find('.infra-value').val();
                                const icon = $(this).find('.infra-icon').val();
                                if (name) {
                                    infrastructure[name] = {
                                        name: value,
                                        icon: icon
                                    };
                                }
                            });
                            $('#infrastructure').val(JSON.stringify(infrastructure, null, 2));
                        }

                        function escapeHtml(text) {
                            const div = document.createElement('div');
                            div.textContent = text;
                            return div.innerHTML;
                        }

                        $('#add-infrastructure-btn').on('click', function(e) {
                            e.preventDefault();
                            const textarea = $('#infrastructure');
                            let infrastructure = {};
                            if (textarea.val().trim()) {
                                try {
                                    infrastructure = JSON.parse(textarea.val());
                                    if (typeof infrastructure !== 'object' || Array.isArray(infrastructure)) infrastructure = {};
                                } catch(e) {}
                            }
                            
                            // Генерируем уникальный ключ
                            let newKey = 'Новый';
                            let counter = 1;
                            while (infrastructure[newKey] !== undefined) {
                                newKey = 'Новый ' + counter;
                                counter++;
                            }
                            
                            infrastructure[newKey] = { name: '', icon: 'icon-place' };
                            textarea.val(JSON.stringify(infrastructure, null, 2));
                            renderInfrastructure();
                            return false;
                        });

                        // Инициализация
                        renderInfrastructure();
                    });
                </script>

                <!-- История стадиона редактор -->
                <script>
                jQuery(function($) {
                        function renderHistory() {
                            const textarea = $('#history');
                            const json = textarea.val().trim();
                            const list = $('#history-list');
                            list.empty();

                            let history = [];
                            if (json) {
                                try {
                                    history = JSON.parse(json);
                                    if (!Array.isArray(history)) history = [];
                                } catch(e) {
                                    console.error('Ошибка парсинга JSON:', e);
                                }
                            }

                            history.forEach((item, idx) => {
                                const year = item.year || '';
                                const event = item.event || '';
                                const icon = item.icon || 'icon-calendar';
                                
                                const iconOptions = STADIUM_FORM_ICONS.map(ic => 
                                    `<option value="${ic.id}" ${icon === ic.id ? 'selected' : ''}>${ic.name}</option>`
                                ).join('');

                                const html = $('<div class="history-item"></div>')
                                    .html(`
                                        <div class="history-item-row">
                                            <input type="text" class="history-year" placeholder="Год (например: 2000)" value="${escapeHtml(String(year))}">
                                            <input type="text" class="history-event" placeholder="Событие (например: Открытие стадиона)" value="${escapeHtml(event)}">
                                            <select class="history-icon">
                                                ${iconOptions}
                                            </select>
                                            <button type="button" class="stadium-form-remove-btn">✕</button>
                                        </div>
                                    `);

                                html.find('.history-remove').on('click', function(e) {
                                    e.preventDefault();
                                    html.remove();
                                    updateHistoryJSON();
                                    return false;
                                });

                                html.find('input, select').on('change', updateHistoryJSON);

                                list.append(html);
                            });
                        }

                        function updateHistoryJSON() {
                            const history = [];
                            $('#history-list .history-item').each(function() {
                                const year = $(this).find('.history-year').val();
                                const event = $(this).find('.history-event').val();
                                const icon = $(this).find('.history-icon').val();
                                if (year || event) {
                                    history.push({
                                        year: year ? parseInt(year) : '',
                                        event: event,
                                        icon: icon
                                    });
                                }
                            });
                            $('#history').val(JSON.stringify(history, null, 2));
                        }

                        function escapeHtml(text) {
                            const div = document.createElement('div');
                            div.textContent = text;
                            return div.innerHTML;
                        }

                        $('#add-history-btn').on('click', function(e) {
                            e.preventDefault();
                            const textarea = $('#history');
                            let history = [];
                            if (textarea.val().trim()) {
                                try {
                                    history = JSON.parse(textarea.val());
                                    if (!Array.isArray(history)) history = [];
                                } catch(e) {}
                            }
                            
                            history.push({
                                year: new Date().getFullYear(),
                                event: '',
                                icon: 'icon-calendar'
                            });
                            textarea.val(JSON.stringify(history, null, 2));
                            renderHistory();
                            return false;
                        });

                        // Инициализация
                        renderHistory();
                    });
                </script>

                <div class="stadium-form-section">
                    <div class="section-header">
                        ⚙️ Технические характеристики
                    </div>

                    <div class="form-group">
                        
                        <div id="tech-features-editor" style="margin-bottom: 10px;">
                            <div style="margin-bottom: 10px;">
                                <button type="button" id="add-tech-feature-btn" class="button" style="margin-bottom: 10px;">+ Добавить характеристику</button>
                            </div>
                            <div id="tech-features-list"></div>
                        </div>
                        <textarea name="tech_features" id="tech_features" rows="5" class="json-field" style="display:none;"
                                  placeholder='{"floodlights": true, "heating": false, "drainage_system": "modern", "field_type": "natural_grass"}'><?php 
                            echo ! empty( $stadium ) && ! empty( $stadium->tech_features ) ? esc_textarea( $stadium->tech_features ) : ''; 
                        ?></textarea>
                        <p class="form-description">Технические параметры стадиона (название и значение)</p>
                    </div>

                    <script>
                    jQuery(function($) {
                        function renderTechFeatures() {
                            const textarea = $('#tech_features');
                            const json = textarea.val().trim();
                            const list = $('#tech-features-list');
                            list.empty();

                            let features = {};
                            if (json) {
                                try {
                                    features = JSON.parse(json);
                                    if (typeof features !== 'object' || Array.isArray(features)) features = {};
                                } catch(e) {
                                    console.error('Ошибка парсинга JSON:', e);
                                }
                            }

                            Object.entries(features).forEach(([key, value], idx) => {
                                const html = $('<div class="tech-feature-item"></div>')
                                    .html(`
                                        <div class="tech-feature-item-row">
                                            <input type="text" class="tech-name" placeholder="Название (например: Освещение)" value="${escapeHtml(key)}">
                                            <input type="text" class="tech-value" placeholder="Значение (например: 1200 люкс)" value="${escapeHtml(String(value))}">
                                            <button type="button" class="stadium-form-remove-btn" data-idx="${idx}">✕</button>
                                        </div>
                                    `)
                                    .on('change', 'input', updateJSON)
                                    .on('click', '.tech-feature-remove', function() {
                                        $(this).closest('.tech-feature-item').remove();
                                        updateJSON();
                                    });
                                list.append(html);
                            });
                        }

                        function updateJSON() {
                            const features = {};
                            $('#tech-features-list .tech-feature-item').each(function() {
                                const name = $(this).find('.tech-name').val();
                                const value = $(this).find('.tech-value').val();
                                if (name) {
                                    features[name] = value;
                                }
                            });
                            $('#tech_features').val(JSON.stringify(features, null, 2));
                        }

                        function escapeHtml(text) {
                            const div = document.createElement('div');
                            div.textContent = text;
                            return div.innerHTML;
                        }

                        $('#add-tech-feature-btn').on('click', function(e) {
                            e.preventDefault();
                            const textarea = $('#tech_features');
                            let features = {};
                            if (textarea.val().trim()) {
                                try {
                                    features = JSON.parse(textarea.val());
                                    if (typeof features !== 'object' || Array.isArray(features)) features = {};
                                } catch(e) {}
                            }
                            features['Новая'] = '';
                            textarea.val(JSON.stringify(features, null, 2));
                            renderTechFeatures();
                            return false;
                        });

                        // Инициализация
                        renderTechFeatures();
                    });
                    </script>
                </div>

                <div class="stadium-form-section">
                    <div class="section-header">
                        💺 Сектора стадиона
                    </div>

                    <div id="sectors-editor" style="margin-bottom: 10px;">
                        <div style="margin-bottom: 10px;">
                            <button type="button" id="add-sector-btn" class="button" style="margin-bottom: 10px;">+ Добавить сектор</button>
                        </div>
                        <div id="sectors-list"></div>
                    </div>

                    <div class="form-group" style="margin-top: 20px;">
                        <label for="sectors">Данные секторов (скрытое поле JSON)</label>
                        <textarea name="sectors" id="sectors" rows="5" class="json-field" style="display: none;"><?php 
                            echo ! empty( $stadium ) && ! empty( $stadium->sectors ) ? esc_textarea( $stadium->sectors ) : ''; 
                        ?></textarea>
                        <p class="form-description">Редактируйте секторы используя форму выше</p>
                    </div>

                    <script>
                    jQuery(function($) {
                        function renderSectors() {
                            const textarea = $('#sectors');
                            const json = textarea.val().trim();
                            const list = $('#sectors-list');
                            list.empty();

                            let sectors = {};
                            if (json) {
                                try {
                                    sectors = JSON.parse(json);
                                    if (typeof sectors !== 'object' || Array.isArray(sectors)) sectors = {};
                                } catch(e) {
                                    console.error('Ошибка парсинга JSON:', e);
                                }
                            }

                            Object.entries(sectors).forEach(([key, value], idx) => {
                                const html = $('<div class="sector-item-form"></div>')
                                    .html(`
                                        <div class="sector-item-row">
                                            <input type="text" class="sector-name" placeholder="Название (например: Северная трибуна)" value="${escapeHtml(key)}">
                                            <input type="number" class="sector-capacity" placeholder="Вместимость" value="${escapeHtml(String(value))}">
                                            <button type="button" class="stadium-form-remove-btn" data-idx="${idx}">✕</button>
                                        </div>
                                    `)
                                    .on('change', 'input', updateJSON)
                                    .on('click', '.sector-remove', function() {
                                        $(this).closest('.sector-item-form').remove();
                                        updateJSON();
                                    });
                                list.append(html);
                            });
                        }

                        function updateJSON() {
                            const sectors = {};
                            $('#sectors-list .sector-item-form').each(function() {
                                const name = $(this).find('.sector-name').val();
                                const capacity = $(this).find('.sector-capacity').val();
                                if (name) {
                                    sectors[name] = capacity ? parseInt(capacity) : 0;
                                }
                            });
                            $('#sectors').val(JSON.stringify(sectors, null, 2));
                        }

                        function escapeHtml(text) {
                            const div = document.createElement('div');
                            div.textContent = text;
                            return div.innerHTML;
                        }

                        $('#add-sector-btn').on('click', function(e) {
                            e.preventDefault();
                            const textarea = $('#sectors');
                            let sectors = {};
                            if (textarea.val().trim()) {
                                try {
                                    sectors = JSON.parse(textarea.val());
                                    if (typeof sectors !== 'object' || Array.isArray(sectors)) sectors = {};
                                } catch(e) {}
                            }
                            
                            // Генерируем уникальный ключ
                            let newKey = 'Новый';
                            let counter = 1;
                            while (sectors[newKey] !== undefined) {
                                newKey = 'Новый ' + counter;
                                counter++;
                            }
                            
                            sectors[newKey] = '';
                            textarea.val(JSON.stringify(sectors, null, 2));
                            renderSectors();
                            return false;
                        });

                        // Инициализация
                        renderSectors();
                    });
                    </script>
                </div>

                <div class="stadium-form-section">
                    <div class="section-header">
                        📊 Статистические карточки
                    </div>

                    <div class="form-group">                       
                        <div id="stat-cards-editor" style="margin-bottom: 10px;">
                            <div style="margin-bottom: 10px;">
                                <button type="button" id="add-stat-card-btn" class="button" style="margin-bottom: 10px;">+ Добавить карточку</button>
                            </div>
                            <div id="stat-cards-list"></div>
                        </div>
                        <textarea name="stat_cards" id="stat_cards" rows="5" class="json-field" style="display:none;"
                                  placeholder='[{"title": "Год основания", "value": "2000", "icon": "icon-calendar"}, {"title": "Вместимость", "value": "15000", "icon": "icon-people"}]'><?php 
                            echo ! empty( $stadium ) && ! empty( $stadium->stat_cards ) ? esc_textarea( $stadium->stat_cards ) : ''; 
                        ?></textarea>
                        <p class="form-description">Статистические карточки с иконками из спрайта (title, value, icon)</p>
                    </div>

                    <script>
                    jQuery(function($) {
                        function renderStatCards() {
                            const textarea = $('#stat_cards');
                            const json = textarea.val().trim();
                            const list = $('#stat-cards-list');
                            list.empty();

                            let cards = [];
                            if (json) {
                                try {
                                    cards = JSON.parse(json);
                                    if (!Array.isArray(cards)) cards = [];
                                } catch(e) {
                                    console.error('Ошибка парсинга JSON:', e);
                                }
                            }

                            cards.forEach((card, idx) => {
                                const iconId = card.icon || 'icon-calendar';
                                const html = $('<div class="stat-card-item"></div>')
                                    .html(`
                                        <div class="stat-card-item-row">
                                            <input type="text" class="stat-title" placeholder="Название (например: Год основания)" value="${escapeHtml(card.title || '')}">
                                            <input type="text" class="stat-value" placeholder="Значение (например: 2000)" value="${escapeHtml(card.value || '')}">
                                            <select class="stat-icon">
                                                ${STADIUM_FORM_ICONS.map(opt => `<option value="${opt.id}" ${opt.id === iconId ? 'selected' : ''}>${opt.name}</option>`).join('')}
                                            </select>
                                            <button type="button" class="stadium-form-remove-btn" data-idx="${idx}">✕</button>
                                        </div>
                                    `)
                                    .on('change', 'input, select', updateJSON)
                                    .on('click', '.stat-card-remove', function() {
                                        $(this).closest('.stat-card-item').remove();
                                        updateJSON();
                                    });
                                list.append(html);
                            });
                        }

                        function updateJSON() {
                            const cards = [];
                            $('#stat-cards-list .stat-card-item').each(function() {
                                cards.push({
                                    title: $(this).find('.stat-title').val(),
                                    value: $(this).find('.stat-value').val(),
                                    icon: $(this).find('.stat-icon').val()
                                });
                            });
                            $('#stat_cards').val(JSON.stringify(cards, null, 2));
                        }

                        function escapeHtml(text) {
                            const div = document.createElement('div');
                            div.textContent = text;
                            return div.innerHTML;
                        }

                        $('#add-stat-card-btn').on('click', function() {
                            const textarea = $('#stat_cards');
                            let cards = [];
                            if (textarea.val().trim()) {
                                try {
                                    cards = JSON.parse(textarea.val());
                                    if (!Array.isArray(cards)) cards = [];
                                } catch(e) {}
                            }
                            cards.push({ title: '', value: '', icon: 'icon-calendar' });
                            textarea.val(JSON.stringify(cards, null, 2));
                            renderStatCards();
                        });

                        // Инициализация
                        renderStatCards();
                    });
                    </script>
                </div>

                <div class="stadium-form-section">
                    <div class="section-header">
                        🚗 Как добраться
                    </div>

                    <div id="directions-container" class="form-group">
                        <!-- Направления будут добавлены здесь jQuery -->
                    </div>

                    <button type="button" id="add-direction-btn" class="button button-secondary">
                        + Добавить маршрут
                    </button>

                    <textarea name="to_get" id="to_get" rows="3" class="json-field" style="display: none;"><?php 
                        echo ! empty( $stadium ) && ! empty( $stadium->to_get ) ? esc_textarea( $stadium->to_get ) : ''; 
                    ?></textarea>

                    <p class="form-description">Редактируйте маршруты используя форму выше</p>

                    <script>
                    jQuery(document).ready(function($) {
                        function renderDirections() {
                            const textarea = $('#to_get');
                            const container = $('#directions-container');
                            container.empty();

                            let items = [];
                            try {
                                const val = textarea.val().trim();
                                if (val) {
                                    items = JSON.parse(val);
                                }
                            } catch (e) {
                                console.error('JSON Parse Error:', e);
                                items = [];
                            }

                            items.forEach((item, index) => {
                                const icon = item.icon || 'icon-car';
                                const iconOptions = STADIUM_FORM_ICONS.map(ic => 
                                    `<option value="${ic.id}" ${icon === ic.id ? 'selected' : ''}>${ic.name}</option>`
                                ).join('');

                                const html = `
                                    <div class="direction-item" data-index="${index}">
                                        <div class="direction-item-row">
                                            <input type="text" class="direction-transport" placeholder="Вид транспорта (Автобус, Метро)" value="${item.transport || ''}" />
                                            <select class="direction-icon">
                                                ${iconOptions}
                                            </select>
                                        </div>
                                        <div class="direction-item-row">
                                            <input type="text" class="direction-route" placeholder="Маршрут (№5, №12)" value="${item.route || ''}" />
                                        </div>
                                        <div class="direction-item-row">
                                            <input type="text" class="direction-station-label" placeholder="Заголовок (например: Станция/Остановка)" value="${item.station_label || 'Станция/Остановка'}" />
                                            <input type="text" class="direction-station" placeholder="Название (например: ст. Центральная)" value="${item.station || ''}" />
                                        </div>
                                        <div class="direction-item-row">
                                            <input type="text" class="direction-time" placeholder="Время (15 минут)" value="${item.time || ''}" />
                                            <button type="button" class="stadium-form-remove-btn" data-index="${index}">✕</button>
                                        </div>
                                    </div>
                                `;
                                container.append(html);
                                
                                // Вешаем события на новый элемент
                                const $newItem = container.find('.direction-item').last();
                                $newItem.on('change', 'input, select', updateJSON);
                            });
                        }

                        function updateJSON() {
                            const container = $('#directions-container');
                            const items = [];

                            container.find('.direction-item').each(function() {
                                const transport = $(this).find('.direction-transport').val() || '';
                                const route = $(this).find('.direction-route').val() || '';
                                const station_label = $(this).find('.direction-station-label').val() || 'Станция/Остановка';
                                const station = $(this).find('.direction-station').val() || '';
                                const time = $(this).find('.direction-time').val() || '';
                                const icon = $(this).find('.direction-icon').val() || 'icon-car';

                                if (transport || route || station || time) {
                                    items.push({
                                        transport: transport,
                                        route: route,
                                        station_label: station_label,
                                        station: station,
                                        time: time,
                                        icon: icon
                                    });
                                }
                            });

                            const textarea = $('#to_get');
                            textarea.val(JSON.stringify(items, null, 2));
                        }

                        // Добавить маршрут
                        $('#add-direction-btn').on('click', function(e) {
                            e.preventDefault();
                            const textarea = $('#to_get');
                            let items = [];
                            try {
                                const val = textarea.val().trim();
                                if (val) {
                                    items = JSON.parse(val);
                                }
                            } catch (e) {}
                            items.push({ transport: '', route: '', station_label: '', station: '', time: '', icon: 'icon-car' });
                            textarea.val(JSON.stringify(items, null, 2));
                            renderDirections();
                        });

                        // Удалить маршрут
                        $(document).on('click', '.direction-item .stadium-form-remove-btn', function(e) {
                            e.preventDefault();
                            const textarea = $('#to_get');
                            let items = [];
                            try {
                                const val = textarea.val().trim();
                                if (val) {
                                    items = JSON.parse(val);
                                }
                            } catch (e) {}
                            const index = parseInt($(this).attr('data-index'));
                            items.splice(index, 1);
                            textarea.val(items.length > 0 ? JSON.stringify(items, null, 2) : '');
                            renderDirections();
                        });

                        // Обновить JSON при изменении полей
                        $(document).on('input change', '.direction-transport, .direction-route, .direction-station-label, .direction-station, .direction-time, .direction-icon', function() {
                            updateJSON();
                        });

                        renderDirections();
                    });
                    </script>
                </div>
            </div>

            <!-- Кнопки -->
            <div class="stadium-form-actions">
                <button type="submit" class="button button-primary">
                    <?php echo $is_edit ? '✓ Обновить' : '✓ Создать'; ?>
                </button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-stadiums' ) ); ?>" class="button">
                    ← Назад к списку
                </a>
            </div>
        </form>
    </div>
</div>
