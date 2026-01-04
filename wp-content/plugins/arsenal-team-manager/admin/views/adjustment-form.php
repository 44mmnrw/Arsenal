<?php
/**
 * Шаблон: Форма добавления/редактирования корректировки турнирной таблицы
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo $is_edit ? 'Редактирование корректировки' : 'Добавить корректировку турнирной таблицы'; ?></h1>

    <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'error' ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Произошла ошибка. Попробуйте ещё раз.</strong></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="hidden" name="action" value="<?php echo $is_edit ? 'arsenal_update_adjustment' : 'arsenal_create_adjustment'; ?>">
        <?php wp_nonce_field( $is_edit ? 'arsenal_update_adjustment' : 'arsenal_create_adjustment' ); ?>
        
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="adjustment_id" value="<?php echo esc_attr( $adjustment->id ); ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="tournament_id">Турнир <span class="description">(обязательно)</span></label>
                </th>
                <td>
                    <select id="tournament_id" name="tournament_id" class="regular-text" required>
                        <option value="">— Выберите турнир —</option>
                        <?php foreach ( $tournaments as $tournament_id => $tournament_name ) : ?>
                            <option value="<?php echo esc_attr( $tournament_id ); ?>"
                                <?php selected( $is_edit && $adjustment->tournament_id === $tournament_id ); ?>>
                                <?php echo esc_html( $tournament_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="season_id">Сезон <span class="description">(обязательно)</span></label>
                </th>
                <td>
                    <select id="season_id" name="season_id" class="regular-text" required>
                        <option value="">— Выберите сезон —</option>
                        <?php if ( ! empty( $seasons['seasons'] ) ) : ?>
                            <?php foreach ( $seasons['seasons'] as $season ) : ?>
                                <option value="<?php echo esc_attr( $season->season_id ); ?>"
                                    <?php selected( $is_edit && $adjustment->season_id === $season->season_id ); ?>>
                                    <?php echo esc_html( $season->season_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="team_id">Команда <span class="description">(обязательно)</span></label>
                </th>
                <td>
                    <select id="team_id" name="team_id" class="regular-text" required>
                        <option value="">— Выберите команду —</option>
                        <?php foreach ( $teams as $team_id => $team_name ) : ?>
                            <option value="<?php echo esc_attr( $team_id ); ?>"
                                <?php selected( $is_edit && $adjustment->team_id === $team_id ); ?>>
                                <?php echo esc_html( $team_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="adjustment_points">Корректировка очков <span class="description">(обязательно)</span></label>
                </th>
                <td>
                    <input type="number" 
                           id="adjustment_points" 
                           name="adjustment_points" 
                           class="regular-text"
                           value="<?php echo $is_edit ? esc_attr( $adjustment->adjustment_points ) : '0'; ?>"
                           required>
                    <p class="description">Положительное число = бонус (например, +3), отрицательное = штраф (например, -6)</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="applied_date">Дата применения <span class="description">(обязательно)</span></label>
                </th>
                <td>
                    <input type="datetime-local" 
                           id="applied_date" 
                           name="applied_date" 
                           class="regular-text"
                           value="<?php echo $is_edit ? esc_attr( date( 'Y-m-d\TH:i', strtotime( $adjustment->applied_date ) ) ) : date( 'Y-m-d\TH:i' ); ?>"
                           required>
                    <p class="description">Дата и время применения корректировки</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="comment">Комментарий (обоснование)</label>
                </th>
                <td>
                    <textarea id="comment" 
                              name="comment" 
                              rows="5" 
                              class="large-text"><?php echo $is_edit ? esc_textarea( $adjustment->comment ) : ''; ?></textarea>
                    <p class="description">Детальное обоснование корректировки (например, "Штраф за неявку на матч 15.12.2025")</p>
                </td>
            </tr>
        </table>

        <?php submit_button( $is_edit ? 'Сохранить изменения' : 'Создать корректировку' ); ?>
    </form>

    <p><a href="<?php echo admin_url( 'admin.php?page=arsenal-adjustments' ); ?>">← Вернуться к списку корректировок</a></p>
</div>
