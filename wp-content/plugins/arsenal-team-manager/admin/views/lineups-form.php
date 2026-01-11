<?php
/**
 * Шаблон: Форма редактирования составов матча
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1>📋 Редактировать составы матча</h1>
    
    <!-- Сообщение об успешном удалении игрока -->
    <?php if ( isset( $_GET['lineup_deleted'] ) && $_GET['lineup_deleted'] == 1 ) : ?>
        <div class="notice notice-success is-dismissible" style="margin: 20px 0;">
            <p><strong>✅ Игрок успешно удален из состава матча</strong></p>
        </div>
    <?php endif; ?>
    
    <!-- Информация о матче -->
    <div class="lineups-match-info">
        <h3>⚽ Матч</h3>
        <p class="match-title">
            <strong>
                <?php echo esc_html( $match->home_team_name ?? 'Команда 1' ); ?> 
                vs 
                <?php echo esc_html( $match->away_team_name ?? 'Команда 2' ); ?>
            </strong>
            <span class="match-date">
                <?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $match->match_date ) ) ); ?>
            </span>
        </p>
    </div>
    
    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="lineups-form">
        <input type="hidden" name="action" value="arsenal_update_lineup">
        <input type="hidden" name="match_id" value="<?php echo esc_attr( $match->match_id ); ?>">
        <?php wp_nonce_field( 'arsenal_lineups_form', 'arsenal_lineups_nonce' ); ?>
        
        <div class="lineups-wrapper">
        <?php
        if ( empty( $lineups ) ) {
            echo '<p>Составы не найдены для этого матча. Debug: ' . json_encode( compact( 'match_id' ) ) . '</p>';
        }
        $teams_lineups = array();
        foreach ( $lineups as $lineup ) {
            if ( ! isset( $teams_lineups[ $lineup->team_id ] ) ) {
                $teams_lineups[ $lineup->team_id ] = array(
                    'name' => $lineup->team_name,
                    'starting' => array(),
                    'subs' => array()
                );
            }
            
            if ( $lineup->is_starting ) {
                $teams_lineups[ $lineup->team_id ]['starting'][] = $lineup;
            } else {
                $teams_lineups[ $lineup->team_id ]['subs'][] = $lineup;
            }
        }
        
        foreach ( $teams_lineups as $team_id => $team_data ) :
        ?>
            <div class="lineups-section">
                <h3>👥 <?php echo esc_html( $team_data['name'] ); ?></h3>
                
                <!-- Стартовый состав -->
                <div class="lineups-subsection">
                    <h4>Стартовый состав</h4>
                    <div class="lineups-table">
                        <div class="lineups-row lineups-header">
                            <div class="lineups-col-name">Игрок</div>
                            <div class="lineups-col-number">№</div>
                            <div class="lineups-col-captain">К</div>
                            <div class="lineups-col-action">Удалить</div>
                        </div>
                        
                        <?php foreach ( $team_data['starting'] as $player ) : ?>
                        <div class="lineups-row">
                            <div class="lineups-col-name">
                                <?php echo esc_html( ( $player->first_name ?? '' ) . ' ' . ( $player->last_name ?? '' ) ); ?>
                                <input type="hidden" name="lineups[<?php echo intval( $player->id ); ?>][player_id]" value="<?php echo esc_attr( $player->player_id ); ?>">
                                <input type="hidden" name="lineups[<?php echo intval( $player->id ); ?>][is_starting]" value="1">
                            </div>
                            <div class="lineups-col-number">
                                <input type="number" name="lineups[<?php echo intval( $player->id ); ?>][shirt_number]" 
                                       value="<?php echo intval( $player->shirt_number ); ?>" 
                                       min="0" max="99" class="lineups-input-number">
                            </div>
                            <div class="lineups-col-captain">
                                <input type="checkbox" name="lineups[<?php echo intval( $player->id ); ?>][is_captain]" 
                                       value="1" <?php checked( $player->is_captain ); ?>>
                            </div>
                            <div class="lineups-col-action">
                                <button type="button" class="button button-small button-link-delete" 
                                        onclick="if(confirm('Удалить игрока?')) { document.location='<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_lineup_player&lineup_id=' . $player->id . '&match_id=' . esc_attr( $match->match_id ) ), 'delete_lineup_' . $player->id ) ); ?>'; }">
                                    🗑️
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Запасные -->
                <?php if ( ! empty( $team_data['subs'] ) ) : ?>
                <div class="lineups-subsection">
                    <h4>Запасные</h4>
                    <div class="lineups-table">
                        <div class="lineups-row lineups-header">
                            <div class="lineups-col-name">Игрок</div>
                            <div class="lineups-col-number">№</div>
                            <div class="lineups-col-captain">Стартовый</div>
                            <div class="lineups-col-action">Удалить</div>
                        </div>
                        
                        <?php foreach ( $team_data['subs'] as $player ) : ?>
                        <div class="lineups-row">
                            <div class="lineups-col-name">
                                <?php echo esc_html( $player->first_name . ' ' . $player->last_name ); ?>
                                <input type="hidden" name="lineups[<?php echo intval( $player->id ); ?>][player_id]" value="<?php echo esc_attr( $player->player_id ); ?>">
                            </div>
                            <div class="lineups-col-number">
                                <input type="number" name="lineups[<?php echo intval( $player->id ); ?>][shirt_number]" 
                                       value="<?php echo intval( $player->shirt_number ); ?>" 
                                       min="0" max="99" class="lineups-input-number">
                            </div>
                            <div class="lineups-col-captain">
                                <input type="checkbox" name="lineups[<?php echo intval( $player->id ); ?>][is_starting]" 
                                       value="1" <?php checked( $player->is_starting ); ?>>
                            </div>
                            <div class="lineups-col-action">
                                <button type="button" class="button button-small button-link-delete" 
                                        onclick="if(confirm('Удалить игрока?')) { document.location='<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=arsenal_delete_lineup_player&lineup_id=' . $player->id . '&match_id=' . esc_attr( $match->match_id ) ), 'delete_lineup_' . $player->id ) ); ?>'; }">
                                    🗑️
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
        
        <!-- Добавление новых игроков -->
        <div class="lineups-add-section">
            <h3>➕ Добавить игроков в состав</h3>
            
            <div id="new-players-container" class="new-players-container"></div>
            
            <button type="button" class="button button-secondary" id="add-player-btn">
                ➕ Добавить игрока
            </button>
        </div>
        
        <script>
        (function() {
            const allPlayers = <?php echo json_encode( $all_players ); ?>;
            let playerCounter = 0;
            
            document.getElementById('add-player-btn').addEventListener('click', function() {
                playerCounter++;
                const container = document.getElementById('new-players-container');
                
                let playerOptions = '<option value="">— Выберите игрока —</option>';
                const sortedPlayers = allPlayers.sort((a, b) => {
                    const nameA = (a.full_name || '').trim().toLowerCase();
                    const nameB = (b.full_name || '').trim().toLowerCase();
                    return nameA.localeCompare(nameB, 'ru');
                });
                
                sortedPlayers.forEach(player => {
                    const playerName = player.full_name;
                    const playerNumber = player.shirt_number || '—';
                    playerOptions += '<option value="' + player.player_id + '">' + playerName + ' №' + playerNumber + '</option>';
                });
                
                const rowHTML = `
                    <div class="new-player-row">
                        <select name="new_players[${playerCounter}][player_id]" required>
                            ${playerOptions}
                        </select>
                        
                        <select name="new_players[${playerCounter}][team_id]" required>
                            <option value="${document.querySelector('input[name="match_id"]').value === '<?php echo esc_attr( $match->match_id ); ?>' ? '<?php echo isset( $match->home_team_id ) ? esc_attr( $match->home_team_id ) : '' ?>' : ''}"><?php echo esc_html( $match->home_team_name ?? 'Домашняя' ); ?></option>
                            <option value="<?php echo isset( $match->away_team_id ) ? esc_attr( $match->away_team_id ) : '' ?>"><?php echo esc_html( $match->away_team_name ?? 'Гостевая' ); ?></option>
                        </select>
                        
                        <input type="number" name="new_players[${playerCounter}][shirt_number]" placeholder="№" min="0" max="99" class="new-player-number">
                        
                        <label class="new-player-checkbox">
                            <input type="checkbox" name="new_players[${playerCounter}][is_starting]" value="1">
                            <span>Стартовый</span>
                        </label>
                        
                        <button type="button" class="button button-small button-link-delete" onclick="this.parentElement.remove();">🗑️</button>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', rowHTML);
            });
        })();
        </script>
        
        <!-- Кнопки -->
        <div class="lineups-buttons">
            <button type="submit" class="button button-primary">
                💾 Сохранить составы
            </button>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . intval( $match->id ) ) ); ?>" class="button">
                ❌ Назад к матчу
            </a>
        </div>
    </form>
</div>
