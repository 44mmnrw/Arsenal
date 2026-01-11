<?php
/**
 * Шаблон: Форма добавления/редактирования лиги
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="league-form-wrapper">
    <h1><?php echo $is_edit ? '✏️ Редактирование лиги' : '➕ Добавить новую лигу'; ?></h1>

    <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'error' ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Произошла ошибка. Попробуйте ещё раз.</strong></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" class="league-form">
        <input type="hidden" name="action" value="<?php echo $is_edit ? 'arsenal_update_league' : 'arsenal_create_league'; ?>">
        <?php wp_nonce_field( $is_edit ? 'arsenal_update_league' : 'arsenal_create_league' ); ?>
        
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="league_id" value="<?php echo esc_attr( $league->id ); ?>">
        <?php endif; ?>

        <div class="league-form-section">
            <div class="section-header">⚽ Основная информация</div>
            <div class="form-group">
                <label for="league_name">📝 Название лиги <span class="required">*</span></label>
                <input type="text" 
                       id="league_name" 
                       name="league_name" 
                       class="form-control"
                       value="<?php echo $is_edit ? esc_attr( $league->league_name ) : ''; ?>"
                       placeholder="Например: BETERA-Высшая лига"
                       required>
                <div class="form-description">Полное наименование лиги из чемпионата</div>
            </div>
        </div>

        <div class="league-form-actions">
            <button type="submit" class="button button-primary">✓ <?php echo $is_edit ? 'Сохранить изменения' : 'Создать лигу'; ?></button>
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-leagues' ); ?>" class="button">← Отмена</a>
        </div>
    </form>
</div>
