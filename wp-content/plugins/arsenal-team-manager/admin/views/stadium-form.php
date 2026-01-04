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

// CSS для двухколонной верстки
$grid_style = 'display: grid; grid-template-columns: 1fr 1fr; gap: 30px;';
?>

<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>
    
    <!-- Сообщения об ошибке -->
    <?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Произошла ошибка при сохранении стадиона!</strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Закрыть уведомление</span></button>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data" style="max-width: 1200px;">
        
        <input type="hidden" name="action" value="<?php echo esc_attr( $form_action ); ?>">
        
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="stadium_id" value="<?php echo intval( $stadium->id ); ?>">
        <?php endif; ?>
        
        <?php wp_nonce_field( 'arsenal_stadium_form', 'arsenal_stadium_nonce' ); ?>
        
        <!-- Основная информация -->
        <h2 style="margin-top: 30px;">Основная информация</h2>
        
        <div style="<?php echo $grid_style; ?>">
            
            <!-- Название -->
            <div>
                <label for="name" style="display: block; font-weight: 600; margin-bottom: 8px;">Название <span style="color: red;">*</span></label>
                <input type="text" name="name" id="name" required 
                       value="<?php echo ! empty( $stadium ) ? esc_attr( $stadium->name ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Название стадиона</p>
            </div>
            
            <!-- Город -->
            <div>
                <label for="city" style="display: block; font-weight: 600; margin-bottom: 8px;">Город</label>
                <input type="text" name="city" id="city" 
                       value="<?php echo ! empty( $stadium ) && ! empty( $stadium->city ) ? esc_attr( $stadium->city ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Город, где расположен стадион</p>
            </div>
            
            <!-- Вместимость -->
            <div>
                <label for="capacity" style="display: block; font-weight: 600; margin-bottom: 8px;">Вместимость</label>
                <input type="number" name="capacity" id="capacity" min="0" 
                       value="<?php echo ! empty( $stadium ) && ! is_null( $stadium->capacity ) ? intval( $stadium->capacity ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Количество мест на стадионе</p>
            </div>
            
            <!-- Фото стадиона -->
            <div>
                <label for="photo" style="display: block; font-weight: 600; margin-bottom: 8px;">Фото стадиона</label>
                <input type="file" name="photo" id="photo" accept="image/*" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Загрузите изображение стадиона (JPG, PNG, GIF)</p>
                
                <?php if ( $is_edit && ! empty( $stadium->photo_url ) ) : ?>
                    <div style="margin-top: 15px;">
                        <p style="font-weight: 600; margin-bottom: 8px;">Текущее изображение:</p>
                        <?php
                        // Преобразуем относительный путь в полный URL если нужно
                        $photo_url = $stadium->photo_url;
                        if ( ! str_starts_with( $photo_url, 'http://' ) && ! str_starts_with( $photo_url, 'https://' ) ) {
                            $photo_url = home_url( $photo_url );
                        }
                        ?>
                        <img src="<?php echo esc_url( $photo_url ); ?>" 
                             alt="<?php echo esc_attr( $stadium->name ); ?>" 
                             style="max-width: 200px; max-height: 200px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; padding: 5px;">
                        <p style="font-size: 12px; color: #666; margin-top: 8px;">Загрузите новое изображение, чтобы заменить текущее</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Кнопки -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px; max-width: 400px;">
            <button type="submit" class="button button-primary button-large" style="padding: 12px 20px; font-size: 16px; text-align: center;">
                <?php echo $is_edit ? 'Обновить стадион' : 'Создать стадион'; ?>
            </button>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-stadiums' ) ); ?>" class="button button-large" style="padding: 12px 20px; font-size: 16px; text-align: center; display: flex; align-items: center; justify-content: center;">
                Назад к списку
            </a>
        </div>
    </form>
</div>
