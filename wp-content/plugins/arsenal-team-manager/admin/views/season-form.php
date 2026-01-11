<?php
/**
 * Шаблон: Форма добавления/редактирования сезона
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <div class="season-form-wrapper">
        <h1><?php echo $is_edit ? '✏️ Редактировать сезон' : '➕ Добавить сезон'; ?></h1>

        <?php
        // Сообщения об ошибках
        if ( isset( $_GET['message'] ) && $_GET['message'] === 'error' ) {
            echo '<div class="notice notice-error is-dismissible" style="margin: 20px 0;"><p>Произошла ошибка при сохранении.</p></div>';
        }
        ?>

        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" class="season-form">
            <?php
            if ( $is_edit ) {
                wp_nonce_field( 'arsenal_update_season' );
                echo '<input type="hidden" name="action" value="arsenal_update_season">';
                echo '<input type="hidden" name="season_id" value="' . esc_attr( $season->id ) . '">';
            } else {
                wp_nonce_field( 'arsenal_create_season' );
                echo '<input type="hidden" name="action" value="arsenal_create_season">';
            }
            ?>

            <div class="season-form-section season-form-main">
                <div class="section-header">
                    📅 Информация о сезоне
                </div>

                <div class="form-group">
                    <label for="season_name">Название сезона<span class="required">*</span></label>
                    <input type="text" name="season_name" id="season_name" 
                           value="<?php echo $is_edit ? esc_attr( $season->season_name ) : ''; ?>" 
                           placeholder="Сезон 2025-2026" required>
                    <p class="form-description">Например: "Сезон 2025-2026" или "Сезон 2025"</p>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">📅 Дата начала<span class="required">*</span></label>
                        <input type="date" name="start_date" id="start_date" 
                               value="<?php echo $is_edit ? esc_attr( $season->start_date ) : ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="end_date">📅 Дата окончания<span class="required">*</span></label>
                        <input type="date" name="end_date" id="end_date" 
                               value="<?php echo $is_edit ? esc_attr( $season->end_date ) : ''; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="is_active" class="checkbox-label">
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               <?php checked( $is_edit ? $season->is_active : 1 ); ?>>
                        <span>✓ Отметить сезон как активный</span>
                    </label>
                </div>
            </div>

            <div class="season-form-actions">
                <button type="submit" class="button button-primary">
                    <?php echo $is_edit ? '✓ Обновить' : '✓ Создать'; ?>
                </button>
                <a href="<?php echo admin_url( 'admin.php?page=arsenal-seasons' ); ?>" class="button">
                    ← Назад к списку
                </a>
            </div>
        </form>
    </div>
</div>
