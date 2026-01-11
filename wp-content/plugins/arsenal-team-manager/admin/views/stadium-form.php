<?php
/**
 * Шаблон: Форма добавления/редактирования стадиона в админке
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_title = $is_edit ? 'Редактировать стадион' : 'Добавить новый стадион';
$form_action = $is_edit ? 'arsenal_update_stadium' : 'arsenal_create_stadium';
?>

<div class="wrap">
    <div class="stadium-form-wrapper">
        <h1><?php echo esc_html( $page_title ); ?></h1>

        <!-- Сообщения об ошибке -->
        <?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
            <div class="notice notice-error is-dismissible" style="margin: 20px 0;">
                <p><?php esc_html_e( 'Произошла ошибка при сохранении стадиона!', 'arsenal-team-manager' ); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" class="stadium-form">

            <input type="hidden" name="action" value="<?php echo esc_attr( $form_action ); ?>">

            <?php if ( $is_edit ) : ?>
                <input type="hidden" name="stadium_id" value="<?php echo intval( $stadium->id ); ?>">
            <?php endif; ?>

            <?php wp_nonce_field( 'arsenal_stadium_form', 'arsenal_stadium_nonce' ); ?>

            <!-- Левая колонка: Основная информация -->
            <div class="stadium-form-section stadium-form-left">
                <div class="section-header">
                    📋 Основная информация
                </div>

                <div class="form-group">
                    <label for="name">Название<span class="required">*</span></label>
                    <input type="text" name="name" id="name" required 
                           value="<?php echo ! empty( $stadium ) ? esc_attr( $stadium->name ) : ''; ?>"
                           placeholder="Название стадиона">
                </div>

                <div class="form-group">
                    <label for="city">Город</label>
                    <input type="text" name="city" id="city" 
                           value="<?php echo ! empty( $stadium ) && ! empty( $stadium->city ) ? esc_attr( $stadium->city ) : ''; ?>"
                           placeholder="Город, где расположен стадион">
                </div>

                <div class="form-group">
                    <label for="capacity">Вместимость (мест)</label>
                    <input type="number" name="capacity" id="capacity" min="0" 
                           value="<?php echo ! empty( $stadium ) && ! is_null( $stadium->capacity ) ? intval( $stadium->capacity ) : ''; ?>"
                           placeholder="Количество мест">
                </div>
            </div>

            <!-- Правая колонка: Фото -->
            <div class="stadium-form-section stadium-form-right">
                <div class="section-header">
                    🖼️ Фото стадиона
                </div>

                <div class="form-group">
                    <label for="photo">Загрузить фото</label>
                    <input type="file" name="photo" id="photo" accept="image/*" class="file-input">
                    <p class="form-description">Форматы: JPG, PNG, GIF</p>
                </div>

                <?php if ( $is_edit && ! empty( $stadium->photo_url ) ) : ?>
                    <div class="form-group">
                        <label>Текущее фото:</label>
                        <?php
                        $photo_url = $stadium->photo_url;
                        if ( ! str_starts_with( $photo_url, 'http://' ) && ! str_starts_with( $photo_url, 'https://' ) ) {
                            $photo_url = home_url( $photo_url );
                        }
                        ?>
                        <img src="<?php echo esc_url( $photo_url ); ?>" 
                             alt="<?php echo esc_attr( $stadium->name ); ?>"
                             class="stadium-preview-image">
                        <p class="form-description">Загрузите новое изображение, чтобы заменить</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Кнопки -->
            <div class="stadium-form-actions">
                <button type="submit" class="button button-primary">
                    <?php echo $is_edit ? '✓ Обновить' : '✓ Создать'; ?>
                </button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-stadiums' ) ); ?>" class="button">
                    ← Назад к списку
                </a>
            </div>
        </form>
    </div>
</div>
