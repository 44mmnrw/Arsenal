<?php
/**
 * Шаблон: Форма добавления/редактирования турнира
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <div class="tournament-form-wrapper">
        <h1><?php echo $is_edit ? '✏️ Редактировать турнир' : '➕ Добавить турнир'; ?></h1>

        <?php
        // Сообщения об ошибках
        if ( isset( $_GET['message'] ) && $_GET['message'] === 'error' ) {
            echo '<div class="notice notice-error is-dismissible" style="margin: 20px 0;"><p>❌ Произошла ошибка при сохранении.</p></div>';
        }
        ?>

        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>" class="tournament-form">
            <?php
            if ( $is_edit ) {
                wp_nonce_field( 'arsenal_update_tournament' );
                echo '<input type="hidden" name="action" value="arsenal_update_tournament">';
                echo '<input type="hidden" name="tournament_id" value="' . esc_attr( $tournament->tournament_id ) . '">';
            } else {
                wp_nonce_field( 'arsenal_create_tournament' );
                echo '<input type="hidden" name="action" value="arsenal_create_tournament">';
            }
            ?>

            <div class="tournament-form-section">
                <div class="section-header">
                    🏆 Информация о турнире
                </div>

                <div class="form-group">
                    <label for="name">Название турнира<span class="required">*</span></label>
                    <input type="text" name="name" id="name" 
                           value="<?php echo $is_edit ? esc_attr( $tournament->name ) : ''; ?>" 
                           placeholder="Например: Чемпионат Беларуси" required
                           class="regular-text">
                    <p class="form-description">Укажите название турнира (чемпионат, кубок, лига и т.д.)</p>
                </div>

                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea name="description" id="description" rows="6" 
                              placeholder="Дополнительная информация о турнире..." 
                              class="large-text"><?php echo $is_edit && $tournament->description ? wp_kses_post( $tournament->description ) : ''; ?></textarea>
                    <p class="form-description">Опиши правила, формат, статус турнира и другую информацию</p>
                </div>

                <?php if ( $is_edit ) : ?>
                    <div class="form-group">
                        <label>Идентификатор турнира</label>
                        <code style="background: #f0f0f0; padding: 8px 12px; border-radius: 4px; display: block; margin-top: 8px;">
                            <?php echo esc_html( $tournament->tournament_id ); ?>
                        </code>
                        <p class="form-description">Уникальный идентификатор, генерируется автоматически</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="tournament-form-actions">
                <button type="submit" class="button button-primary button-large">
                    <?php echo $is_edit ? '✓ Обновить' : '✓ Создать'; ?>
                </button>
                <a href="<?php echo admin_url( 'admin.php?page=arsenal-tournaments' ); ?>" class="button button-large">
                    ← Назад к списку
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.tournament-form-wrapper {
    max-width: 800px;
    background: white;
    padding: 20px;
    border-radius: 4px;
}

.tournament-form-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f9f9f9;
    border-left: 4px solid #0073aa;
}

.section-header {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.required {
    color: #d32f2f;
    margin-left: 2px;
}

.regular-text,
.large-text {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    font-size: 14px;
}

.large-text {
    resize: vertical;
}

.form-description {
    margin-top: 6px;
    font-size: 12px;
    color: #666;
    font-style: italic;
}

.tournament-form-actions {
    display: flex;
    gap: 10px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}

.button {
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
}

.button-primary {
    background: #0073aa;
    color: white;
    border: none;
    cursor: pointer;
}

.button-primary:hover {
    background: #005a87;
}

.button {
    background: #f0f0f0;
    color: #333;
    border: 1px solid #ccc;
    cursor: pointer;
}

.button:hover {
    background: #e0e0e0;
}
</style>
