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

<div class="adjustment-form-wrapper">
    <h1><?php echo $is_edit ? '✏️ Редактирование корректировки' : '➕ Добавить новую корректировку'; ?></h1>

    <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'error' ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Произошла ошибка. Попробуйте ещё раз.</strong></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" class="adjustment-form">
        <input type="hidden" name="action" value="<?php echo $is_edit ? 'arsenal_update_adjustment' : 'arsenal_create_adjustment'; ?>">
        <?php wp_nonce_field( $is_edit ? 'arsenal_update_adjustment' : 'arsenal_create_adjustment' ); ?>
        
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="adjustment_id" value="<?php echo esc_attr( $adjustment->id ); ?>">
        <?php endif; ?>

        <div class="adjustment-form-section">
            <div class="section-header">📊 Основная информация</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="tournament_id">🏆 Турнир <span class="required">*</span></label>
                    <select id="tournament_id" name="tournament_id" class="form-control" required>
                        <option value="">— Выберите турнир —</option>
                        <?php foreach ( $tournaments as $tournament_id => $tournament_name ) : ?>
                            <option value="<?php echo esc_attr( $tournament_id ); ?>"
                                <?php selected( $is_edit && $adjustment->tournament_id === $tournament_id ); ?>>
                                <?php echo esc_html( $tournament_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="season_id">📅 Сезон <span class="required">*</span></label>
                    <select id="season_id" name="season_id" class="form-control" required>
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
                </div>
            </div>
            <div class="form-group">
                <label for="team_id">👥 Команда <span class="required">*</span></label>
                <select id="team_id" name="team_id" class="form-control" required>
                    <option value="">— Выберите команду —</option>
                    <?php foreach ( $teams as $team_id => $team_name ) : ?>
                        <option value="<?php echo esc_attr( $team_id ); ?>"
                            <?php selected( $is_edit && $adjustment->team_id === $team_id ); ?>>
                            <?php echo esc_html( $team_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="adjustment-form-section">
            <div class="section-header">🌟 Корректировка Очков</div>
            <div class="form-row">
                <div class="form-group">
                    <label for="adjustment_points">Очки <span class="required">*</span></label>
                    <input type="number" 
                           id="adjustment_points" 
                           name="adjustment_points" 
                           class="form-control"
                           value="<?php echo $is_edit ? esc_attr( $adjustment->adjustment_points ) : '0'; ?>"
                           placeholder="+3 или -6"
                           required>
                    <div class="form-description">Положительное = бонус, отрицательное = штраф</div>
                </div>
                <div class="form-group">
                    <label for="applied_date">📅 Дата применения <span class="required">*</span></label>
                    <input type="datetime-local" 
                           id="applied_date" 
                           name="applied_date" 
                           class="form-control"
                           value="<?php echo $is_edit ? esc_attr( date( 'Y-m-d\TH:i', strtotime( $adjustment->applied_date ) ) ) : date( 'Y-m-d\TH:i' ); ?>"
                           required>
                </div>
            </div>
        </div>

        <div class="adjustment-form-section">
            <div class="section-header">🗒️ Обоснование</div>
            <div class="form-group">
                <label for="comment">Комментарий</label>
                <textarea id="comment" 
                          name="comment" 
                          rows="5" 
                          class="form-control"
                          placeholder="Например: Штраф за неявку на матч 15.12.2025"><?php echo $is_edit ? esc_textarea( $adjustment->comment ) : ''; ?></textarea>
                <div class="form-description">Детальное обоснование корректировки</div>
            </div>
        </div>

        <div class="adjustment-form-actions">
            <button type="submit" class="button button-primary">✓ <?php echo $is_edit ? 'Сохранить изменения' : 'Создать корректировку'; ?></button>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-adjustments' ); ?>" class="button">← Отмена</a>
        </div>
    </form>
</div>
