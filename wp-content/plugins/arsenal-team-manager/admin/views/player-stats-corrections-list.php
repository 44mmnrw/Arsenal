<?php
/**
 * Шаблон: Список корректировок статистики игроков
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Получаем статистику
$stats = array(
    'total'     => isset( $total_count ) ? $total_count : 0,
    'unapplied' => isset( $unapplied_count ) ? $unapplied_count : 0,
    'applied'   => isset( $applied_count ) ? $applied_count : 0,
);
?>

<div class="corrections-wrapper">
    <!-- Заголовок и кнопка -->
    <div class="corrections-header">
        <h1>⚙️ Корректировки статистики игроков</h1>
        <a href="#add-correction-form" class="button button-primary scroll-to-form">
            ➕ Добавить корректировку
        </a>
    </div>

    <!-- Сообщения об успехе/ошибке -->
    <?php if ( isset( $_GET['message'] ) ) : ?>
        <?php if ( $_GET['message'] === 'created' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Корректировка успешно создана.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'updated' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Корректировка успешно обновлена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'deleted' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Корректировка успешно удалена.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'applied' ) : ?>
            <div class="notice notice-success is-dismissible">
                <p><strong>✓ Корректировка успешно применена к статистике.</strong></p>
            </div>
        <?php elseif ( $_GET['message'] === 'error' ) : ?>
            <div class="notice notice-error is-dismissible">
                <p><strong>⚠️ Произошла ошибка. Попробуйте ещё раз.</strong></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Статистика -->
    <div class="corrections-stats">
        <div class="stat-box">
            <span class="stat-number"><?php echo esc_html( $stats['total'] ); ?></span>
            <span class="stat-label">Всего корректировок</span>
        </div>
        <div class="stat-box">
            <span class="stat-number"><?php echo esc_html( $stats['unapplied'] ); ?></span>
            <span class="stat-label">Ожидают применения</span>
        </div>
        <div class="stat-box">
            <span class="stat-number"><?php echo esc_html( $stats['applied'] ); ?></span>
            <span class="stat-label">Применено</span>
        </div>
    </div>

    <!-- Форма добавления корректировки -->
    <div class="corrections-form-box">
        <div class="corrections-form-header">
            <h2 id="add-correction-form">Добавить новую корректировку</h2>
        </div>

        <div class="corrections-form-content">
            <form id="arsenal-correction-form" method="post" action="">
                <!-- Первая строка: Игрок и Турнир -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="player_select">👤 Игрок <span class="required">*</span></label>
                        <select id="player_select" name="player_id" required class="arsenal-searchable-select">
                            <option value="">— Введите имя игрока или выберите из списка —</option>
                            <?php if ( ! empty( $players ) ) : ?>
                                <?php foreach ( $players as $player ) : ?>
                                    <option value="<?php echo esc_attr( $player->player_id ); ?>" data-search="<?php echo esc_attr( strtolower( $player->full_name ) ); ?>">
                                        <?php echo esc_html( $player->full_name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                        <p class="description">🔍 Начните писать имя для поиска</p>
                    </div>

                    <div class="form-group">
                        <label for="tournament_select">🏆 Турнир <span class="required">*</span></label>
                        <select id="tournament_select" name="tournament_id" required>
                            <option value="">— Выберите турнир —</option>
                            <?php if ( ! empty( $tournaments ) ) : ?>
                                <?php foreach ( $tournaments as $tournament ) : ?>
                                    <option value="<?php echo esc_attr( $tournament->tournament_id ); ?>">
                                        <?php echo esc_html( $tournament->name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="season_select">📅 Сезон</label>
                        <select id="season_select" name="season_id">
                            <option value="">— Все сезоны —</option>
                            <?php if ( ! empty( $seasons ) ) : ?>
                                <?php foreach ( $seasons as $season ) : ?>
                                    <option value="<?php echo esc_attr( $season->season_id ); ?>">
                                        <?php echo esc_html( $season->season_name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Параметры коррекции: сетка 2x3 для дельта-значений -->
                <div class="corrections-deltas-header">
                    <h3>⚡ Изменения статистики (Δ)</h3>
                    <p class="description">Положительные числа добавляют к статистике, отрицательные вычитают</p>
                </div>

                <div class="corrections-deltas-grid">
                    <div class="form-group">
                        <label for="minutes_delta">
                            <span class="delta-icon">⏱️</span> Минут за сезон
                        </label>
                        <input type="number" id="minutes_delta" name="minutes_played_delta" value="0" class="delta-input" />
                    </div>

                    <div class="form-group">
                        <label for="matches_delta">
                            <span class="delta-icon">🎮</span> Матчей сыграно
                        </label>
                        <input type="number" id="matches_delta" name="matches_played_delta" value="0" class="delta-input" />
                    </div>

                    <div class="form-group">
                        <label for="goals_delta">
                            <span class="delta-icon">⚽</span> Голов забито
                        </label>
                        <input type="number" id="goals_delta" name="goals_delta" value="0" class="delta-input" />
                    </div>

                    <div class="form-group">
                        <label for="assists_delta">
                            <span class="delta-icon">🎯</span> Ассистов
                        </label>
                        <input type="number" id="assists_delta" name="assists_delta" value="0" class="delta-input" />
                    </div>

                    <div class="form-group">
                        <label for="yellow_delta">
                            <span class="delta-icon">🟨</span> Жёлтых карточек
                        </label>
                        <input type="number" id="yellow_delta" name="yellow_cards_delta" value="0" class="delta-input" />
                    </div>

                    <div class="form-group">
                        <label for="red_delta">
                            <span class="delta-icon">🟥</span> Красных карточек
                        </label>
                        <input type="number" id="red_delta" name="red_cards_delta" value="0" class="delta-input" />
                    </div>
                </div>

                <!-- Причина коррекции -->
                <div class="form-group full-width">
                    <label for="reason">🗒️ Причина корректировки</label>
                    <textarea 
                        id="reason" 
                        name="correction_reason" 
                        rows="4" 
                        placeholder="Причина корректировки (например: исправление ошибки в статисике ABFF, игрок вернулся после травмы и т.д.)..."
                        class="reason-textarea"></textarea>
                    <p class="description">Заполнение этого поля поможет отследить причину каждой коррекции</p>
                </div>

                <!-- Кнопки формы -->
                <div class="corrections-form-buttons">
                    <?php wp_nonce_field( 'arsenal_correction_nonce', 'arsenal_correction_nonce_field' ); ?>
                    <button type="button" class="button button-primary" onclick="arsenalSaveCorrection()">
                        ✓ Добавить корректировку
                    </button>
                    <button type="reset" class="button">
                        ↺ Очистить форму
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Фильтры и таблица корректировок -->
    <div class="corrections-list-box">
        <div class="corrections-list-header">
            <h2>📋 История корректировок</h2>
            <p class="corrections-list-count">Всего записей: <strong><?php echo esc_html( $total_count ); ?></strong></p>
        </div>

        <!-- Форма фильтрации -->
        <div class="corrections-filters">
            <form method="get" action="" class="corrections-filter-form">
                <input type="hidden" name="page" value="<?php echo esc_attr( $_GET['page'] ?? 'arsenal-player-stats-corrections' ); ?>" />

                <div class="filter-group">
                    <label for="filter_player">👤 Игрок:</label>
                    <select name="player_id" id="filter_player">
                        <option value="">— Все игроки —</option>
                        <?php if ( ! empty( $players ) ) : ?>
                            <?php foreach ( $players as $player ) : ?>
                                <option value="<?php echo esc_attr( $player->player_id ); ?>" 
                                    <?php selected( isset( $_GET['player_id'] ) && $_GET['player_id'] === $player->player_id ); ?>>
                                    <?php echo esc_html( $player->full_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter_tournament">🏆 Турнир:</label>
                    <select name="tournament_id" id="filter_tournament">
                        <option value="">— Все турниры —</option>
                        <?php if ( ! empty( $tournaments ) ) : ?>
                            <?php foreach ( $tournaments as $tournament ) : ?>
                                <option value="<?php echo esc_attr( $tournament->tournament_id ); ?>" 
                                    <?php selected( isset( $_GET['tournament_id'] ) && $_GET['tournament_id'] === $tournament->tournament_id ); ?>>
                                    <?php echo esc_html( $tournament->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="filter_status">✓ Статус:</label>
                    <select name="is_applied" id="filter_status">
                        <option value="">— Все —</option>
                        <option value="0" <?php selected( isset( $_GET['is_applied'] ) && $_GET['is_applied'] === '0' ); ?>>⏳ Не применена</option>
                        <option value="1" <?php selected( isset( $_GET['is_applied'] ) && $_GET['is_applied'] === '1' ); ?>>✓ Применена</option>
                    </select>
                </div>

                <button type="submit" class="button">🔍 Фильтровать</button>
            </form>
        </div>

        <!-- Таблица корректировок -->
        <?php if ( ! empty( $corrections ) ) : ?>
            <div class="corrections-table">
                <!-- Заголовок таблицы -->
                <div class="corrections-row corrections-header">
                    <div class="corrections-col-id">ID</div>
                    <div class="corrections-col-player">👤 Игрок</div>
                    <div class="corrections-col-tournament">🏆 Турнир</div>
                    <div class="corrections-col-season">📅 Сезон</div>
                    <div class="corrections-col-reason">🗒️ Причина</div>
                    <div class="corrections-col-status">✓ Статус</div>
                    <div class="corrections-col-date">📅 Дата</div>
                    <div class="corrections-col-actions">Действия</div>
                </div>

                <!-- Строки таблицы -->
                <?php foreach ( $corrections as $correction ) : ?>
                    <?php
                    // Определяем класс статуса
                    $status_class = $correction->is_applied ? 'status-applied' : 'status-pending';
                    $status_text = $correction->is_applied ? '✓ Применена' : '⏳ Ожидает';
                    
                    // Получаем имя игрока
                    $player = $wpdb->get_row( $wpdb->prepare(
                        "SELECT full_name FROM {$wpdb->prefix}arsenal_players WHERE player_id = %s",
                        $correction->player_id
                    ) );
                    $player_name = $player ? $player->full_name : 'Неизвестный';
                    
                    // Получаем имя турнира
                    $tournament = $wpdb->get_row( $wpdb->prepare(
                        "SELECT name FROM {$wpdb->prefix}arsenal_tournaments WHERE tournament_id = %s",
                        $correction->tournament_id
                    ) );
                    $tournament_name = $tournament ? $tournament->name : 'Неизвестный';
                    
                    // Получаем сезон если указан
                    $season_name = '—';
                    if ( ! empty( $correction->season_id ) ) {
                        $season = $wpdb->get_row( $wpdb->prepare(
                            "SELECT season_name, YEAR(start_date) as year FROM {$wpdb->prefix}arsenal_seasons WHERE season_id = %s",
                            $correction->season_id
                        ) );
                        if ( $season ) {
                            $season_name = $season->season_name . ' (' . $season->year . ')';
                        }
                    }
                    
                    // Форматируем дельта-значения
                    $deltas = array();
                    if ( ! empty( $correction->minutes_played_delta ) ) {
                        $delta_class = $correction->minutes_played_delta > 0 ? 'positive' : 'negative';
                        $deltas[] = '<span class="delta ' . $delta_class . '">⏱️ ' . $correction->minutes_played_delta . ' мин</span>';
                    }
                    if ( ! empty( $correction->matches_played_delta ) ) {
                        $delta_class = $correction->matches_played_delta > 0 ? 'positive' : 'negative';
                        $deltas[] = '<span class="delta ' . $delta_class . '">🎮 ' . $correction->matches_played_delta . '</span>';
                    }
                    if ( ! empty( $correction->goals_delta ) ) {
                        $delta_class = $correction->goals_delta > 0 ? 'positive' : 'negative';
                        $deltas[] = '<span class="delta ' . $delta_class . '">⚽ ' . $correction->goals_delta . '</span>';
                    }
                    if ( ! empty( $correction->assists_delta ) ) {
                        $delta_class = $correction->assists_delta > 0 ? 'positive' : 'negative';
                        $deltas[] = '<span class="delta ' . $delta_class . '">🎯 ' . $correction->assists_delta . '</span>';
                    }
                    if ( ! empty( $correction->yellow_cards_delta ) ) {
                        $delta_class = $correction->yellow_cards_delta > 0 ? 'positive' : 'negative';
                        $deltas[] = '<span class="delta ' . $delta_class . '">🟨 ' . $correction->yellow_cards_delta . '</span>';
                    }
                    if ( ! empty( $correction->red_cards_delta ) ) {
                        $delta_class = $correction->red_cards_delta > 0 ? 'positive' : 'negative';
                        $deltas[] = '<span class="delta ' . $delta_class . '">🟥 ' . $correction->red_cards_delta . '</span>';
                    }
                    ?>

                    <div class="corrections-row">
                        <div class="corrections-col-id">
                            <code><?php echo esc_html( $correction->correction_id ); ?></code>
                        </div>
                        <div class="corrections-col-player">
                            <strong><?php echo esc_html( $player_name ); ?></strong>
                        </div>
                        <div class="corrections-col-tournament">
                            <?php echo esc_html( $tournament_name ); ?>
                        </div>
                        <div class="corrections-col-season">
                            <span style="<?php echo empty( $correction->season_id ) ? 'color: #999;' : ''; ?>">
                                <?php echo esc_html( $season_name ); ?>
                            </span>
                        </div>
                        <div class="corrections-col-reason">
                            <span class="reason-text" title="<?php echo esc_attr( $correction->correction_reason ); ?>">
                                <?php echo esc_html( wp_trim_words( $correction->correction_reason, 5 ) ); ?>
                            </span>
                        </div>
                        <div class="corrections-col-status">
                            <span class="badge badge-<?php echo esc_attr( $status_class ); ?>">
                                <?php echo esc_html( $status_text ); ?>
                            </span>
                        </div>
                        <div class="corrections-col-date">
                            <?php echo esc_html( date( 'd.m.Y', strtotime( $correction->created_at ) ) ); ?>
                        </div>
                        <div class="corrections-col-actions">
                            <?php if ( ! $correction->is_applied ) : ?>
                                <button type="button" 
                                        class="button button-small" 
                                        onclick="arsenalApplyCorrection('<?php echo esc_attr( $correction->correction_id ); ?>')"
                                        title="Применить эту корректировку к статистике">
                                    ▶ Применить
                                </button>
                            <?php endif; ?>
                            <button type="button" 
                                    class="button button-small" 
                                    onclick="arsenalDeleteCorrection('<?php echo esc_attr( $correction->correction_id ); ?>')"
                                    title="Удалить эту корректировку">
                                🗑️
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Пагинация -->
            <?php if ( $total_pages > 1 ) : ?>
                <div class="corrections-pagination">
                    <div class="pagination">
                        <?php
                        echo paginate_links( array(
                            'base'      => add_query_arg( 'paged', '%#%' ),
                            'format'    => '',
                            'prev_text' => '← Предыдущая',
                            'next_text' => 'Следующая →',
                            'total'     => $total_pages,
                            'current'   => $current_page,
                        ) );
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="corrections-empty-message">
                <p>📭 Корректировок не найдено. <a href="#add-correction-form" class="scroll-to-form">Создайте первую корректировку</a></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    /**
     * Инициализация поиска в select элементах
     * Позволяет печатать для фильтрации опций
     */
    const searchableSelects = document.querySelectorAll('.arsenal-searchable-select');
    
    searchableSelects.forEach(select => {
        let searchText = '';
        let searchTimeout;
        
        select.addEventListener('keydown', function(e) {
            // Очищаем старый таймер
            clearTimeout(searchTimeout);
            
            // Игнорируем некоторые клавиши
            if (['Enter', 'ArrowUp', 'ArrowDown', 'Tab', 'Shift', 'Control', 'Alt'].includes(e.key)) {
                return;
            }
            
            // Обрабатываем Backspace
            if (e.key === 'Backspace') {
                searchText = searchText.slice(0, -1);
            } else if (e.key.length === 1) {
                searchText += e.key.toLowerCase();
            } else {
                return;
            }
            
            e.preventDefault();
            
            // Ищем совпадающую опцию
            const options = Array.from(select.options).slice(1); // Пропускаем первую пустую опцию
            let found = false;
            
            for (const option of options) {
                const searchData = option.getAttribute('data-search') || option.textContent.toLowerCase();
                if (searchData.includes(searchText)) {
                    select.value = option.value;
                    found = true;
                    break;
                }
            }
            
            // Сбрасываем поиск через 2 секунды
            searchTimeout = setTimeout(() => {
                searchText = '';
            }, 2000);
        });
        
        // Сбрасываем поиск при открытии/закрытии меню
        select.addEventListener('focus', () => {
            searchText = '';
        });
        
        select.addEventListener('blur', () => {
            searchText = '';
        });
    });
})();
</script>

<style>
.arsenal-searchable-select {
    cursor: pointer;
    position: relative;
}

.arsenal-searchable-select:focus {
    outline: 2px solid #2271b1;
    outline-offset: -2px;
}

.arsenal-searchable-select option {
    padding: 4px 6px;
    line-height: 1.4;
    font-size: 13px;
}
</style>
