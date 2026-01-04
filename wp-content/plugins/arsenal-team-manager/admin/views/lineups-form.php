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
    <h1>Редактировать составы матча</h1>
    
    <!-- Информация о матче -->
    <div style="background: #f1f1f1; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
        <p>
            <strong>Матч:</strong>
            <?php echo esc_html( $match->home_team_name ?? 'Команда 1' ); ?> vs 
            <?php echo esc_html( $match->away_team_name ?? 'Команда 2' ); ?> 
            (<?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $match->match_date ) ) ); ?>)
        </p>
    </div>
    
    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <input type="hidden" name="action" value="arsenal_update_lineup">
        <input type="hidden" name="match_id" value="<?php echo intval( $match->id ); ?>">
        <?php wp_nonce_field( 'arsenal_lineups_form', 'arsenal_lineups_nonce' ); ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
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
                <div style="border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
                    <h3><?php echo esc_html( $team_data['name'] ); ?></h3>
                    
                    <h4 style="margin-top: 20px;">Стартовый состав:</h4>
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f0f0f0;">
                                <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Игрок</th>
                                <th style="width: 80px; text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">№</th>
                                <th style="width: 60px; text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">К</th>
                                <th style="width: 60px; text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">Удалить</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $team_data['starting'] as $player ) : ?>
                                <tr>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                                        <?php echo esc_html( ( $player->first_name ?? '' ) . ' ' . ( $player->last_name ?? '' ) ); ?>
                                        <input type="hidden" name="lineups[<?php echo intval( $player->id ); ?>][player_id]" value="<?php echo esc_attr( $player->player_id ); ?>">
                                        <input type="hidden" name="lineups[<?php echo intval( $player->id ); ?>][is_starting]" value="1">
                                    </td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">
                                        <input type="number" name="lineups[<?php echo intval( $player->id ); ?>][shirt_number]" 
                                               value="<?php echo intval( $player->shirt_number ); ?>" 
                                               min="0" max="99" style="width: 50px; text-align: center;">
                                    </td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">
                                        <input type="checkbox" name="lineups[<?php echo intval( $player->id ); ?>][is_captain]" 
                                               value="1" <?php checked( $player->is_captain ); ?>>
                                    </td>
                                    <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">
                                        <button type="button" class="button button-small button-link-delete" 
                                                onclick="if(confirm('Удалить игрока?')) { document.location='<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?action=arsenal_delete_lineup_player&lineup_id=' . $player->id . '&match_id=' . $match->id ), 'delete_lineup_' . $player->id ) ); ?>'; }">
                                            Удалить
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ( ! empty( $team_data['subs'] ) ) : ?>
                        <h4 style="margin-top: 20px;">Запасные:</h4>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f0f0f0;">
                                    <th style="text-align: left; padding: 8px; border-bottom: 1px solid #ddd;">Игрок</th>
                                    <th style="width: 80px; text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">№</th>
                                    <th style="width: 60px; text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">Стартовый</th>
                                    <th style="width: 60px; text-align: center; padding: 8px; border-bottom: 1px solid #ddd;">Удалить</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $team_data['subs'] as $player ) : ?>
                                    <tr>
                                        <td style="padding: 8px; border-bottom: 1px solid #ddd;">
                                            <?php echo esc_html( $player->first_name . ' ' . $player->last_name ); ?>
                                            <input type="hidden" name="lineups[<?php echo intval( $player->id ); ?>][player_id]" value="<?php echo esc_attr( $player->player_id ); ?>">
                                        </td>
                                        <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">
                                            <input type="number" name="lineups[<?php echo intval( $player->id ); ?>][shirt_number]" 
                                                   value="<?php echo intval( $player->shirt_number ); ?>" 
                                                   min="0" max="99" style="width: 50px; text-align: center;">
                                        </td>
                                        <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">
                                            <input type="checkbox" name="lineups[<?php echo intval( $player->id ); ?>][is_starting]" 
                                                   value="1" <?php checked( $player->is_starting ); ?>>
                                        </td>
                                        <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: center;">
                                            <button type="button" class="button button-small button-link-delete" 
                                                    onclick="if(confirm('Удалить игрока?')) { document.location='<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?action=arsenal_delete_lineup_player&lineup_id=' . $player->id . '&match_id=' . $match->id ), 'delete_lineup_' . $player->id ) ); ?>'; }">
                                                Удалить
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Добавление новых игроков -->
        <h2 style="margin-top: 30px;">Добавить игроков в состав</h2>
        <div style="background: #f1f1f1; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
            <div id="new-players-container"></div>
            
            <button type="button" class="button button-secondary" id="add-player-btn" style="margin-top: 10px;">
                + Добавить игрока
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
                allPlayers.forEach(player => {
                    const playerName = player.full_name;
                    const playerNumber = player.shirt_number || '—';
                    playerOptions += '<option value="' + player.player_id + '">' + playerName + ' №' + playerNumber + '</option>';
                });
                
                const rowHTML = `
                    <div style="display: grid; grid-template-columns: 2fr 100px 100px 100px auto; gap: 10px; margin-bottom: 10px; align-items: center;">
                        <select name="new_players[${playerCounter}][player_id]" required style="padding: 6px; width: 100%;">
                            ${playerOptions}
                        </select>
                        
                        <select name="new_players[${playerCounter}][team_id]" required style="padding: 6px; width: 100%;">
                            <option value="${document.querySelector('input[name="match_id"]').value === '<?php echo intval( $match->id ); ?>' ? '<?php echo isset( $match->home_team_id ) ? esc_attr( $match->home_team_id ) : '' ?>' : ''}"><?php echo esc_html( $match->home_team_name ?? 'Домашняя' ); ?></option>
                            <option value="<?php echo isset( $match->away_team_id ) ? esc_attr( $match->away_team_id ) : '' ?>"><?php echo esc_html( $match->away_team_name ?? 'Гостевая' ); ?></option>
                        </select>
                        
                        <input type="number" name="new_players[${playerCounter}][shirt_number]" placeholder="№" min="0" max="99" style="padding: 6px;">
                        
                        <label style="display: flex; gap: 5px; align-items: center;">
                            <input type="checkbox" name="new_players[${playerCounter}][is_starting]" value="1">
                            Стартовый
                        </label>
                        
                        <button type="button" class="button button-small button-link-delete" onclick="this.parentElement.remove();">Удалить</button>
                    </div>
                `;
                
                container.insertAdjacentHTML('beforeend', rowHTML);
            });
        })();
        </script>
        
        <!-- Кнопки -->
        <div style="margin-top: 30px;">
            <button type="submit" class="button button-primary button-large" style="padding: 10px 30px; font-size: 16px;">
                Сохранить составы
            </button>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-edit&match_id=' . $match->id ) ); ?>" class="button button-large" style="padding: 10px 30px; margin-left: 10px;">
                Назад к матчу
            </a>
        </div>
    </form>
</div>
