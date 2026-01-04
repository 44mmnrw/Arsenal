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

<div class="wrap arsenal-events-admin">
    <h1>События матча: <?php echo esc_html( $match->home_team_name ) . ' – ' . esc_html( $match->away_team_name ); ?></h1>
    
    <div class="match-info">
        <p>
            <strong>Дата:</strong> <?php echo esc_html( $match->match_date ) . ' ' . esc_html( $match->match_time ); ?>
        </p>
        <p>
            <strong>Счёт:</strong> 
            <?php 
                echo esc_html( $match->home_team_name ) . ' ' . 
                     intval( $match->home_score ) . ':' . intval( $match->away_score ) . ' ' . 
                     esc_html( $match->away_team_name );
            ?>
        </p>
    </div>
    
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="events-form">
        <input type="hidden" name="action" value="arsenal_update_events">
        <input type="hidden" name="match_id" value="<?php echo intval( $match->id ); ?>">
        <?php wp_nonce_field( 'arsenal_events_form', 'arsenal_events_nonce' ); ?>
        
        <h2>Существующие события</h2>
        
        <?php if ( empty( $events ) ) : ?>
            <p>Событий не найдено.</p>
        <?php else : ?>
            <table class="wp-list-table widefat fixed">
                <thead>
                    <tr>
                        <th>Минута</th>
                        <th>Игрок</th>
                        <th>Тип события</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $events as $event ) : ?>
                        <tr>
                            <td>
                                <input type="number" 
                                       name="events[<?php echo intval( $event->id ); ?>][minute]" 
                                       value="<?php echo intval( $event->minute ); ?>" 
                                       min="0" max="120" style="width: 60px;">
                            </td>
                            <td>
                                <?php echo esc_html( $event->full_name ); ?>
                            </td>
                            <td>
                                <select name="events[<?php echo intval( $event->id ); ?>][event_type]">
                                    <?php foreach ( $event_types as $type_id => $type_name ) : ?>
                                        <option value="<?php echo esc_attr( $type_id ); ?>" 
                                                <?php selected( $event->event_type, $type_id ); ?>>
                                            <?php echo esc_html( $type_name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <a href="<?php echo esc_url( wp_nonce_url( 
                                    admin_url( 'admin-post.php?action=arsenal_delete_event&event_id=' . intval( $event->id ) ),
                                    'arsenal_delete_event_' . intval( $event->id )
                                ) ); ?>" 
                                   class="delete-event" 
                                   onclick="return confirm('Удалить событие?');">
                                    Удалить
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <hr>
        
        <h2>Добавить событие</h2>
        
        <div id="new-events-container">
            <table class="wp-list-table widefat fixed">
                <thead>
                    <tr>
                        <th>Минута</th>
                        <th>Игрок</th>
                        <th>Тип события</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="new-events-list">
                    <tr class="new-event-row" style="display: none;">
                        <td>
                            <input type="number" name="new_events[INDEX][minute]" min="0" max="120" style="width: 60px;">
                        </td>
                        <td>
                            <div style="margin-bottom: 8px;">
                                <label>
                                    <input type="radio" name="new_events[INDEX][team]" value="home" class="team-selector" data-event-index="INDEX">
                                    <?php echo esc_html( $match->home_team_name ); ?> (домашняя)
                                </label>
                                <label style="margin-left: 15px;">
                                    <input type="radio" name="new_events[INDEX][team]" value="away" class="team-selector" data-event-index="INDEX">
                                    <?php echo esc_html( $match->away_team_name ); ?> (гостевая)
                                </label>
                            </div>
                            <select name="new_events[INDEX][player_id]" class="player-select" data-team="" style="width: 100%; max-width: 250px;">
                                <option value="">— Сначала выберите команду —</option>
                            </select>
                        </td>
                        <td>
                            <select name="new_events[INDEX][event_type]">
                                <option value="">— Выберите тип —</option>
                                <?php foreach ( $event_types as $type_id => $type_name ) : ?>
                                    <option value="<?php echo esc_attr( $type_id ); ?>">
                                        <?php echo esc_html( $type_name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <button type="button" class="delete-row-btn" onclick="this.closest('tr').remove();">
                                Удалить
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <p>
            <button type="button" class="button" id="add-event-btn">
                + Добавить событие
            </button>
        </p>
        
        <hr>
        
        <div style="background: #fff3cd; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
            <p style="margin: 0;">
                <strong>Совет:</strong> При добавлении события, сначала выберите команду (домашняя или гостевая), 
                чтобы отфильтровать список игроков из состава этой команды.
            </p>
        </div>
        
        <?php submit_button( 'Сохранить события' ); ?>
    </form>
    
    <p>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match->id ) ); ?>" 
           class="button">
            ← Вернуться к матчу
        </a>
    </p>
</div>

<style>
    .arsenal-events-admin {
        padding: 20px;
    }
    
    .match-info {
        background: #f5f5f5;
        padding: 15px;
        border-left: 4px solid #0073aa;
        margin-bottom: 20px;
    }
    
    .match-info p {
        margin: 5px 0;
    }
    
    .events-form table {
        margin-bottom: 20px;
    }
    
    .events-form input[type="number"],
    .events-form select {
        width: 100%;
        max-width: 200px;
    }
    
    .delete-event {
        color: #dc3545;
        text-decoration: none;
    }
    
    .delete-event:hover {
        color: #c82333;
    }
    
    .delete-row-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
        border-radius: 3px;
    }
    
    .delete-row-btn:hover {
        background-color: #c82333;
    }
    
    #new-events-list .new-event-row:not([style*="display: none"]) {
        display: table-row !important;
    }
    
    .team-selector {
        margin-bottom: 10px;
    }
    
    .team-selector label {
        margin-right: 20px;
        cursor: pointer;
    }
    
    .team-selector input[type="radio"] {
        margin-right: 5px;
    }
</style>

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
            const container = document.getElementById( 'new-events-list' );
            const template = document.querySelector( '.new-event-row' );
            const newRow = template.cloneNode( true );
            
            // Очищаем стиль display
            newRow.style.display = '';
            
            // Заменяем INDEX на реальный индекс
            newRow.innerHTML = newRow.innerHTML.replace( /INDEX/g, eventCount );
            
            // Добавляем слушатели на селекторы команд
            const teamSelectors = newRow.querySelectorAll( '.team-selector' );
            const playerSelect = newRow.querySelector( '.player-select' );
            
            teamSelectors.forEach( selector => {
                selector.addEventListener( 'change', function() {
                    updatePlayerSelect( playerSelect, this.value );
                } );
            } );
            
            container.appendChild( newRow );
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
