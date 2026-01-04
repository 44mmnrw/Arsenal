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

<div class="wrap">
    <h1><?php echo $is_edit ? 'Редактирование лиги' : 'Добавить новую лигу'; ?></h1>

    <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'error' ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Произошла ошибка. Попробуйте ещё раз.</strong></p>
        </div>
    <?php endif; ?>

    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
        <input type="hidden" name="action" value="<?php echo $is_edit ? 'arsenal_update_league' : 'arsenal_create_league'; ?>">
        <?php wp_nonce_field( $is_edit ? 'arsenal_update_league' : 'arsenal_create_league' ); ?>
        
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="league_id" value="<?php echo esc_attr( $league->id ); ?>">
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="league_name">Название лиги <span class="description">(обязательно)</span></label>
                </th>
                <td>
                    <input type="text" 
                           id="league_name" 
                           name="league_name" 
                           class="regular-text"
                           value="<?php echo $is_edit ? esc_attr( $league->league_name ) : ''; ?>"
                           required>
                    <p class="description">Например: "BETERA-Высшая лига" или "Maxline-Первая лига"</p>
                </td>
            </tr>
        </table>

        <?php submit_button( $is_edit ? 'Сохранить изменения' : 'Создать лигу' ); ?>
    </form>

    <p><a href="<?php echo admin_url( 'admin.php?page=arsenal-leagues' ); ?>">← Вернуться к списку лиг</a></p>
</div>
