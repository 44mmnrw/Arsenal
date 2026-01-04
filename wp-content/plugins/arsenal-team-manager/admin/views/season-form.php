<?php
/**
 * Шаблон: Форма добавления/редактирования сезона
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo $is_edit ? 'Редактировать сезон' : 'Добавить сезон'; ?></h1>
    
    <?php
    // Сообщения об ошибках
    if ( isset( $_GET['message'] ) && $_GET['message'] === 'error' ) {
        echo '<div class="notice notice-error is-dismissible"><p>Произошла ошибка при сохранении.</p></div>';
    }
    ?>
    
    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
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
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="season_name">Название сезона *</label></th>
                <td>
                    <input type="text" name="season_name" id="season_name" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr( $season->season_name ) : ''; ?>" 
                           placeholder="Сезон 2025-2026" required>
                    <p class="description">Например: "Сезон 2025-2026" или "Сезон 2025"</p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="start_date">Дата начала *</label></th>
                <td>
                    <input type="date" name="start_date" id="start_date" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr( $season->start_date ) : ''; ?>" required>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="end_date">Дата окончания *</label></th>
                <td>
                    <input type="date" name="end_date" id="end_date" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr( $season->end_date ) : ''; ?>" required>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><label for="is_active">Активный сезон</label></th>
                <td>
                    <label>
                        <input type="checkbox" name="is_active" id="is_active" value="1"
                               <?php checked( $is_edit ? $season->is_active : 1 ); ?>>
                        Отметить сезон как активный
                    </label>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" 
                   value="<?php echo $is_edit ? 'Обновить сезон' : 'Добавить сезон'; ?>">
            <a href="<?php echo admin_url( 'admin.php?page=arsenal-seasons' ); ?>" class="button">Отмена</a>
        </p>
    </form>
</div>
