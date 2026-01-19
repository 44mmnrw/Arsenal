<?php
/**
 * Arsenal History Form
 * 
 * Форма управления историей клуба (post_meta)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e( 'История клуба', 'arsenal-team-manager' ); ?></h1>
    
    <!-- Единый список иконок для всех селектов -->
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
    
    <form method="POST" class="arsenal-history-form">
        <input type="hidden" name="action" value="arsenal_save_history" />
        <?php wp_nonce_field( 'arsenal_save_history', '_wpnonce' ); ?>
        
        <!-- Контейнер двух колонок для всех секций -->
        <div class="form-columns-container">
        
        <!-- Основная информация -->
        <div class="form-section">
            <h3><?php _e( 'Основная информация', 'arsenal-team-manager' ); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="title"><?php _e( 'Название главной секции', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="title" name="title" value="<?php echo esc_attr( $history['title'] ); ?>" class="large-text" />
                        <p class="description"><?php _e( 'Например: "История клуба"', 'arsenal-team-manager' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="description"><?php _e( 'Описание', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <?php
                        wp_editor(
                            wp_unslash( $history['description'] ),
                            'description',
                            array(
                                'textarea_rows' => 10,
                                'media_buttons' => true,
                                'wpautop'       => true,
                            )
                        );
                        ?>
                        <p class="description"><?php _e( 'Основной текст истории клуба', 'arsenal-team-manager' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Временная шкала -->
        <div class="form-section">
            <h3><?php _e( 'Временная шкала', 'arsenal-team-manager' ); ?></h3>
            
            <div class="form-group">
                <button type="button" id="add-timeline-item-btn" class="button button-secondary" style="margin-bottom: 10px;">+ Добавить событие</button>
                <div id="timeline-list"></div>
            </div>
            
            <textarea id="scale" name="scale" style="display: none;"><?php echo esc_textarea( is_array( $history['scale'] ) ? json_encode( $history['scale'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) : $history['scale'] ); ?></textarea>

            <script>
            jQuery(function($) {
                function renderTimeline() {
                    const textarea = $('#scale');
                    const json = textarea.val().trim();
                    const list = $('#timeline-list');
                    list.empty();

                    let items = [];
                    if (json) {
                        try {
                            items = JSON.parse(json);
                        } catch (e) {
                            console.error('Invalid JSON in scale field', e);
                            items = [];
                        }
                    }

                    items.forEach((item, idx) => {
                        const year = item.year || '';
                        const event = item.event || '';
                        
                        const html = `
                            <div class="timeline-item" style="display: grid; grid-template-columns: 100px 1fr auto; gap: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; background: #f9f9f9;">
                                <input type="number" class="timeline-year" value="${year}" placeholder="Год" style="width: 100%;"/>
                                <input type="text" class="timeline-event" value="${event}" placeholder="Описание события" style="width: 100%;"/>
                                <button type="button" class="stadium-form-remove-btn" data-index="${idx}" style="padding: 4px 8px;">✕</button>
                            </div>
                        `;
                        list.append(html);
                    });

                    // Обработчики удаления
                    list.on('click', '.stadium-form-remove-btn', function(e) {
                        e.preventDefault();
                        const index = $(this).data('index');
                        items.splice(index, 1);
                        textarea.val(JSON.stringify(items, null, 2));
                        renderTimeline();
                    });

                    // Обработчики изменения
                    list.on('input', '.timeline-year, .timeline-event', function() {
                        items = [];
                        list.find('.timeline-item').each(function(idx) {
                            items.push({
                                year: parseInt($(this).find('.timeline-year').val()) || 0,
                                event: $(this).find('.timeline-event').val()
                            });
                        });
                        textarea.val(JSON.stringify(items, null, 2));
                    });
                }

                $('#add-timeline-item-btn').on('click', function(e) {
                    e.preventDefault();
                    const textarea = $('#scale');
                    let items = [];
                    if (textarea.val().trim()) {
                        try {
                            items = JSON.parse(textarea.val());
                        } catch (e) {
                            items = [];
                        }
                    }
                    
                    items.push({
                        year: new Date().getFullYear(),
                        event: ''
                    });
                    textarea.val(JSON.stringify(items, null, 2));
                    renderTimeline();
                    return false;
                });

                // Инициализация
                renderTimeline();
            });
            </script>
        </div>
        
        <!-- Рекорды и достижения -->
        <div class="form-section">
            <h3><?php _e( 'Рекорды и достижения', 'arsenal-team-manager' ); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="title_second"><?php _e( 'Название секции', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="title_second" name="title_second" value="<?php echo esc_attr( $history['title_second'] ); ?>" class="large-text" />
                    </td>
                </tr>
            </table>

            <!-- Рекорды редактор -->
            <div style="margin-top: 20px;">
                <h4><?php _e( 'Рекорды', 'arsenal-team-manager' ); ?></h4>
                <button type="button" id="add-record-btn" class="button button-secondary" style="margin-bottom: 10px;">+ Добавить рекорд</button>
                <div id="records-list"></div>
            </div>

            <textarea id="records" name="records" style="display: none;"><?php echo esc_textarea( is_array( $history['records'] ) ? json_encode( $history['records'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) : $history['records'] ); ?></textarea>

            <!-- Достижения редактор -->
            <div style="margin-top: 20px;">
                <h4><?php _e( 'Достижения', 'arsenal-team-manager' ); ?></h4>
                <button type="button" id="add-achievement-btn" class="button button-secondary" style="margin-bottom: 10px;">+ Добавить достижение</button>
                <div id="achievements-list"></div>
            </div>

            <textarea id="achievements" name="achievements" style="display: none;"><?php echo esc_textarea( is_array( $history['achievements'] ) ? json_encode( $history['achievements'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) : $history['achievements'] ); ?></textarea>

            <script>
            jQuery(function($) {
                // ===== РЕКОРДЫ =====
                function renderRecords() {
                    const textarea = $('#records');
                    const json = textarea.val().trim();
                    const list = $('#records-list');
                    list.empty();

                    let items = [];
                    if (json) {
                        try {
                            items = JSON.parse(json);
                        } catch (e) {
                            items = [];
                        }
                    }

                    items.forEach((item, idx) => {
                        const title = item.title || '';
                        const style = item.style || 'primary';
                        const icon = item.icon || 'icon-calendar';
                        const itemsStr = (item.items || []).join('\n');
                        
                        const html = `
                            <div class="record-item" style="padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; background: #f9f9f9;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 10px;">
                                    <input type="text" class="record-title" value="${title}" placeholder="Название рекорда" style="width: 100%;"/>
                                    <select class="record-style" style="width: 100%;">
                                        <option value="primary" ${style === 'primary' ? 'selected' : ''}>primary</option>
                                        <option value="white" ${style === 'white' ? 'selected' : ''}>white</option>
                                    </select>
                                    <button type="button" class="stadium-form-remove-btn" data-index="${idx}" style="padding: 4px 8px;">✕</button>
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <label>Иконка:</label>
                                    <select class="record-icon" style="width: 100%; margin-top: 5px;">
                                        ${STADIUM_FORM_ICONS.map(ic => `<option value="${ic.id}" ${icon === ic.id ? 'selected' : ''}>${ic.name}</option>`).join('')}
                                    </select>
                                </div>
                                <textarea class="record-items" placeholder="Пункты (по одному в строку)" style="width: 100%; height: 80px;">${itemsStr}</textarea>
                            </div>
                        `;
                        list.append(html);
                    });

                    list.on('click', '.stadium-form-remove-btn', function(e) {
                        e.preventDefault();
                        items.splice($(this).data('index'), 1);
                        textarea.val(JSON.stringify(items, null, 2));
                        renderRecords();
                    });

                    list.on('input change', '.record-title, .record-style, .record-icon, .record-items', function() {
                        items = [];
                        list.find('.record-item').each(function() {
                            items.push({
                                title: $(this).find('.record-title').val(),
                                style: $(this).find('.record-style').val(),
                                icon: $(this).find('.record-icon').val(),
                                items: $(this).find('.record-items').val().split('\n').filter(v => v.trim())
                            });
                        });
                        textarea.val(JSON.stringify(items, null, 2));
                    });
                }

                $('#add-record-btn').on('click', function(e) {
                    e.preventDefault();
                    const textarea = $('#records');
                    let items = [];
                    if (textarea.val().trim()) {
                        try {
                            items = JSON.parse(textarea.val());
                        } catch (e) {
                            items = [];
                        }
                    }
                    
                    items.push({
                        title: 'Новый рекорд',
                        style: 'primary',
                        icon: 'icon-cup',
                        items: []
                    });
                    textarea.val(JSON.stringify(items, null, 2));
                    renderRecords();
                    return false;
                });

                // ===== ДОСТИЖЕНИЯ =====
                function renderAchievements() {
                    const textarea = $('#achievements');
                    const json = textarea.val().trim();
                    const list = $('#achievements-list');
                    list.empty();

                    let items = [];
                    if (json) {
                        try {
                            items = JSON.parse(json);
                        } catch (e) {
                            items = [];
                        }
                    }

                    items.forEach((item, idx) => {
                        const label = item.label || '';
                        const value = item.value || '';
                        
                        const html = `
                            <div class="achievement-item" style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; background: #f9f9f9;">
                                <input type="text" class="achievement-label" value="${label}" placeholder="Название" style="width: 100%;"/>
                                <input type="text" class="achievement-value" value="${value}" placeholder="Значение" style="width: 100%;"/>
                                <button type="button" class="stadium-form-remove-btn" data-index="${idx}" style="padding: 4px 8px;">✕</button>
                            </div>
                        `;
                        list.append(html);
                    });

                    list.on('click', '.stadium-form-remove-btn', function(e) {
                        e.preventDefault();
                        items.splice($(this).data('index'), 1);
                        textarea.val(JSON.stringify(items, null, 2));
                        renderAchievements();
                    });

                    list.on('input', '.achievement-label, .achievement-value', function() {
                        items = [];
                        list.find('.achievement-item').each(function() {
                            items.push({
                                label: $(this).find('.achievement-label').val(),
                                value: $(this).find('.achievement-value').val()
                            });
                        });
                        textarea.val(JSON.stringify(items, null, 2));
                    });
                }

                $('#add-achievement-btn').on('click', function(e) {
                    e.preventDefault();
                    const textarea = $('#achievements');
                    let items = [];
                    if (textarea.val().trim()) {
                        try {
                            items = JSON.parse(textarea.val());
                        } catch (e) {
                            items = [];
                        }
                    }
                    
                    items.push({
                        label: 'Новое достижение',
                        value: ''
                    });
                    textarea.val(JSON.stringify(items, null, 2));
                    renderAchievements();
                    return false;
                });

                // Инициализация
                renderRecords();
                renderAchievements();
            });
            </script>
        </div>
        
        <!-- Дополнительная секция -->
        <div class="form-section">
            <h3><?php _e( 'Дополнительная секция', 'arsenal-team-manager' ); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="title_third"><?php _e( 'Название секции', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="title_third" name="title_third" value="<?php echo esc_attr( $history['title_third'] ); ?>" class="large-text" />
                        <p class="description"><?php _e( 'Например: "Домашние стадионы"', 'arsenal-team-manager' ); ?></p>
                    </td>
                </tr>
            </table>

            <!-- Карточки редактор -->
            <div style="margin-top: 20px;">
                <button type="button" id="add-additional-card-btn" class="button button-secondary" style="margin-bottom: 10px;">+ Добавить карточку</button>
                <div id="additional-cards-list"></div>
            </div>

            <textarea id="additional_cards" name="additional_cards" style="display: none;"><?php echo esc_textarea( is_array( $history['additional_cards'] ) ? json_encode( $history['additional_cards'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) : $history['additional_cards'] ); ?></textarea>

            <script>
            jQuery(function($) {
                function renderAdditionalCards() {
                    const textarea = $('#additional_cards');
                    const json = textarea.val().trim();
                    const list = $('#additional-cards-list');
                    list.empty();

                    let items = [];
                    if (json) {
                        try {
                            items = JSON.parse(json);
                        } catch (e) {
                            items = [];
                        }
                    }

                    items.forEach((item, idx) => {
                        const label = item.label || '';
                        const value = item.value || '';
                        const icon = item.icon || 'icon-stadium';
                        
                        const html = `
                            <div class="card-item" style="padding: 15px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; background: #f9f9f9;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr auto; gap: 10px; margin-bottom: 10px;">
                                    <input type="text" class="card-label" value="${label}" placeholder="Название" style="width: 100%;"/>
                                    <input type="text" class="card-value" value="${value}" placeholder="Описание" style="width: 100%;"/>
                                    <button type="button" class="stadium-form-remove-btn" data-index="${idx}" style="padding: 4px 8px;">✕</button>
                                </div>
                                <div>
                                    <label>Иконка:</label>
                                    <select class="card-icon" style="width: 100%; margin-top: 5px;">
                                        ${STADIUM_FORM_ICONS.map(ic => `<option value="${ic.id}" ${icon === ic.id ? 'selected' : ''}>${ic.name}</option>`).join('')}
                                    </select>
                                </div>
                            </div>
                        `;
                        list.append(html);
                    });

                    list.on('click', '.stadium-form-remove-btn', function(e) {
                        e.preventDefault();
                        items.splice($(this).data('index'), 1);
                        textarea.val(JSON.stringify(items, null, 2));
                        renderAdditionalCards();
                    });

                    list.on('input change', '.card-label, .card-value, .card-icon', function() {
                        items = [];
                        list.find('.card-item').each(function() {
                            items.push({
                                label: $(this).find('.card-label').val(),
                                value: $(this).find('.card-value').val(),
                                icon: $(this).find('.card-icon').val()
                            });
                        });
                        textarea.val(JSON.stringify(items, null, 2));
                    });
                }

                $('#add-additional-card-btn').on('click', function(e) {
                    e.preventDefault();
                    const textarea = $('#additional_cards');
                    let items = [];
                    if (textarea.val().trim()) {
                        try {
                            items = JSON.parse(textarea.val());
                        } catch (e) {
                            items = [];
                        }
                    }
                    
                    items.push({
                        label: 'Новая карточка',
                        value: '',
                        icon: 'icon-stadium'
                    });
                    textarea.val(JSON.stringify(items, null, 2));
                    renderAdditionalCards();
                    return false;
                });

                // Инициализация
                renderAdditionalCards();
            });
            </script>
        </div>
        
        </div><!-- закрытие form-columns-container -->
        
        <!-- Кнопки -->
        <div class="form-actions">
            <?php submit_button( __( 'Сохранить историю', 'arsenal-team-manager' ), 'primary', 'submit', true ); ?>
        </div>
    </form>
</div>
