<?php
/**
 * Форма редактирования событий матча
 *
 * @var object $match Объект матча
 * @var array $events События матча
 * @var array $lineups Составы матча
 * @var array $event_types Типы событий
 * @var array $home_players Игроки домашней команды
 * @var array $away_players Игроки гостевой команды
 *
 * @package Arsenal
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1>🎯 События матча</h1>
    
    <!-- Информация о матче -->
    <div class="events-match-info">
        <h3>⚽ Матч</h3>
        <p class="match-detail">
            <strong>
                <?php echo esc_html( $match->home_team_name ) . ' – ' . esc_html( $match->away_team_name ); ?>
            </strong>
        </p>
        <p class="match-detail">
            <span class="label">Дата:</span>
            <?php echo esc_html( $match->match_date . ' ' . $match->match_time ); ?>
        </p>
        <p class="match-detail">
            <span class="label">Счёт:</span>
            <strong>
                <?php 
                    echo esc_html( $match->home_team_name ) . ' ' . 
                         intval( $match->home_score ) . ':' . intval( $match->away_score ) . ' ' . 
                         esc_html( $match->away_team_name );
                ?>
            </strong>
        </p>
    </div>
    
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="events-form">
        <input type="hidden" name="action" value="arsenal_update_events">
        <input type="hidden" name="match_id" value="<?php echo intval( $match->id ); ?>">
        <?php wp_nonce_field( 'arsenal_events_form', 'arsenal_events_nonce' ); ?>
        
        <!-- Существующие события -->
        <div class="events-section">
            <h3>📋 Существующие события</h3>
            
            <?php if ( empty( $events ) ) : ?>
                <p style="color: #999; font-style: italic; padding: 20px; text-align: center; background: #f9f9f9; border-radius: 4px;">
                    Событий не найдено
                </p>
            <?php else : ?>
                <div class="events-list">
                    <div class="events-item events-header">
                        <div class="event-minute">Минута</div>
                        <div class="event-player">Игрок</div>
                        <div class="event-type">Тип события</div>
                        <div class="event-action">Действие</div>
                    </div>
                    
                    <?php foreach ( $events as $event ) : ?>
                    <div class="events-item">
                        <div class="event-minute">
                            <input type="number" 
                                   name="events[<?php echo intval( $event->id ); ?>][minute]" 
                                   value="<?php echo intval( $event->minute ); ?>" 
                                   min="0" max="120" 
                                   class="event-input-minute">
                        </div>
                        <div class="event-player">
                            <?php echo esc_html( $event->full_name ); ?>
                        </div>
                        <div class="event-type">
                            <select name="events[<?php echo intval( $event->id ); ?>][event_type]" class="event-select">
                                <?php foreach ( $event_types as $type_id => $type_name ) : ?>
                                    <option value="<?php echo esc_attr( $type_id ); ?>" 
                                            <?php selected( $event->event_type, $type_id ); ?>>
                                        <?php echo esc_html( $type_name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="event-action">
                            <a href="<?php echo esc_url( wp_nonce_url( 
                                admin_url( 'admin-post.php?action=arsenal_delete_event&event_id=' . intval( $event->id ) ),
                                'arsenal_delete_event_' . intval( $event->id )
                            ) ); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('Удалить событие?');">
                                🗑️
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Добавление новых событий -->
        <div class="events-section">
            <h3>➕ Добавить события</h3>
            
            <div id="new-events-container">
                <div class="events-list">
                    <div class="events-item events-header">
                        <div class="event-minute">Минута</div>
                        <div class="event-player">Игрок</div>
                        <div class="event-type">Тип события</div>
                        <div class="event-action">Действие</div>
                    </div>
                    
                    <div id="new-events-list"></div>
                </div>
            </div>
            
            <button type="button" class="button button-secondary" id="add-event-btn" style="margin-top: 15px;">
                ➕ Добавить событие
            </button>
        </div>
        
        <!-- Подсказка -->
        <div class="events-tip">
            <p>
                <strong>💡 Совет:</strong> При добавлении события сначала выберите команду (домашняя или гостевая), 
                чтобы отфильтровать список игроков из состава этой команды.
            </p>
        </div>
        
        <!-- Кнопки -->
        <div class="events-buttons">
            <button type="submit" class="button button-primary">
                💾 Сохранить события
            </button>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match->id ) ); ?>" class="button">
                ❌ Назад к матчу
            </a>
        </div>
    </form>
</div>

<!-- Шаблон для новых событий (скрыт) -->
<template id="new-event-template">
    <div class="events-item">
        <div class="event-minute">
            <input type="number" name="new_events[INDEX][minute]" min="0" max="120" class="event-input-minute">
        </div>
        <div class="event-player">
            <div class="team-selector-group">
                <label class="team-selector-label">
                    <input type="radio" name="new_events[INDEX][team]" value="home" class="team-selector">
                    <?php echo esc_html( $match->home_team_name ); ?>
                </label>
                <label class="team-selector-label">
                    <input type="radio" name="new_events[INDEX][team]" value="away" class="team-selector">
                    <?php echo esc_html( $match->away_team_name ); ?>
                </label>
            </div>
            <select name="new_events[INDEX][player_id]" class="player-select event-select" style="margin-top: 8px;">
                <option value="">— Выберите игрока —</option>
            </select>
        </div>
        <div class="event-type">
            <select name="new_events[INDEX][event_type]" class="event-select">
                <option value="">— Выберите тип —</option>
                <?php foreach ( $event_types as $type_id => $type_name ) : ?>
                    <option value="<?php echo esc_attr( $type_id ); ?>">
                        <?php echo esc_html( $type_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="event-action">
            <button type="button" class="button button-small button-link-delete delete-event-btn">
                🗑️
            </button>
        </div>
    </div>
</template>

<script>
    document.addEventListener( 'DOMContentLoaded', function() {
        const homeTeamId = '<?php echo esc_attr( $match->home_team_id ); ?>';
        const homeTeamName = '<?php echo esc_attr( $match->home_team_name ); ?>';
        const awayTeamName = '<?php echo esc_attr( $match->away_team_name ); ?>';
        
        const allPlayers = {
            home: <?php echo wp_json_encode( $home_players ); ?>,
            away: <?php echo wp_json_encode( $away_players ); ?>
        };
        
        let eventCount = 0;
        
        // Кнопка добавления события
        document.getElementById( 'add-event-btn' ).addEventListener( 'click', function() {
            addNewEventRow();
        } );
        
        function addNewEventRow() {
            const template = document.getElementById( 'new-event-template' );
            const clone = template.content.cloneNode( true );
            
            // Заменяем INDEX на реальный индекс
            const html = new XMLSerializer().serializeToString( clone );
            const newHtml = html.replace( /INDEX/g, eventCount );
            
            // Создаем элемент из HTML
            const container = document.createElement( 'div' );
            container.innerHTML = newHtml;
            const newRow = container.firstElementChild;
            
            // Добавляем обработчики событий
            const teamSelectors = newRow.querySelectorAll( '.team-selector' );
            const playerSelect = newRow.querySelector( '.player-select' );
            const deleteBtn = newRow.querySelector( '.delete-event-btn' );
            
            teamSelectors.forEach( selector => {
                selector.addEventListener( 'change', function() {
                    updatePlayerSelect( playerSelect, this.value );
                } );
            } );
            
            deleteBtn.addEventListener( 'click', function( e ) {
                e.preventDefault();
                newRow.remove();
            } );
            
            document.getElementById( 'new-events-list' ).appendChild( newRow );
            eventCount++;
        }
        
        function updatePlayerSelect( select, team ) {
            const currentValue = select.value;
            select.innerHTML = '<option value="">— Выберите игрока —</option>';
            select.dataset.team = team;
            
            const players = allPlayers[team] || {};
            for ( const [playerId, playerName] of Object.entries( players ) ) {
                const option = document.createElement( 'option' );
                option.value = playerId;
                option.textContent = playerName;
                select.appendChild( option );
            }
            
            select.value = currentValue;
        }
    } );
</script>
