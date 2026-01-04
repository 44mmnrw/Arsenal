<?php
/**
 * Шаблон: Форма добавления/редактирования матча в админке
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$page_title = $is_edit ? 'Редактировать матч' : 'Добавить новый матч';
$form_action = $is_edit ? 'arsenal_update_match' : 'arsenal_create_match';

// CSS для двухколонной верстки
$grid_style = 'display: grid; grid-template-columns: 1fr 1fr; gap: 30px;';
?>

<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>
    
    <!-- Сообщения об ошибке -->
    <?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>Произошла ошибка при сохранении матча!</strong></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Закрыть уведомление</span></button>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="max-width: 1200px;">
        
        <input type="hidden" name="action" value="<?php echo esc_attr( $form_action ); ?>">
        
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="match_id" value="<?php echo intval( $match->id ); ?>">
        <?php endif; ?>
        
        <?php wp_nonce_field( 'arsenal_match_form', 'arsenal_match_nonce' ); ?>
        
        <!-- Основная информация о матче -->
        <h2 style="margin-top: 30px;">Основная информация</h2>
        
        <div style="<?php echo $grid_style; ?>">
            
            <!-- Турнир -->
            <div>
                <label for="tournament_id" style="display: block; font-weight: 600; margin-bottom: 8px;">Турнир <span style="color: red;">*</span></label>
                <select name="tournament_id" id="tournament_id" required style="width: 100%; padding: 8px;">
                    <option value="">— Выберите турнир —</option>
                    <?php foreach ( $tournaments as $tournament_id => $tournament_name ) : ?>
                        <option value="<?php echo esc_attr( $tournament_id ); ?>" 
                            <?php selected( ! empty( $match ) && $match->tournament_id == $tournament_id ); ?>>
                            <?php echo esc_html( $tournament_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description" style="margin-top: 4px;">Выберите турнир, в котором проходит матч</p>
            </div>
            
            <!-- Сезон -->
            <div>
                <label for="season_id" style="display: block; font-weight: 600; margin-bottom: 8px;">Сезон</label>
                <select name="season_id" id="season_id" style="width: 100%; padding: 8px;">
                    <option value="">— Выберите сезон —</option>
                    <?php if ( ! empty( $seasons['seasons'] ) ) : ?>
                        <?php foreach ( $seasons['seasons'] as $season ) : ?>
                            <option value="<?php echo esc_attr( $season->season_id ); ?>" 
                                <?php selected( ! empty( $match ) && $match->season_id === $season->season_id ); ?>>
                                <?php echo esc_html( $season->season_name ); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <p class="description" style="margin-top: 4px;">Выберите сезон, к которому относится матч</p>
            </div>
            
            <!-- Дата матча -->
            <div>
                <label for="match_date" style="display: block; font-weight: 600; margin-bottom: 8px;">Дата матча <span style="color: red;">*</span></label>
                <input type="date" name="match_date" id="match_date" required 
                       value="<?php echo ! empty( $match ) ? esc_attr( $match->match_date ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Дата проведения матча (YYYY-MM-DD)</p>
            </div>
            
            <!-- Время матча -->
            <div>
                <label for="match_time" style="display: block; font-weight: 600; margin-bottom: 8px;">Время матча</label>
                <input type="time" name="match_time" id="match_time" 
                       value="<?php echo ! empty( $match ) && ! empty( $match->match_time ) ? esc_attr( $match->match_time ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Время начала матча (HH:MM)</p>
            </div>
            
            <!-- Статус матча -->
            <div>
                <label for="status" style="display: block; font-weight: 600; margin-bottom: 8px;">Статус матча</label>
                <select name="status" id="status" style="width: 100%; padding: 8px;">
                    <option value="">— Выберите статус —</option>
                    <?php foreach ( $statuses as $status_id => $status_name ) : ?>
                        <option value="<?php echo esc_attr( $status_id ); ?>" 
                            <?php selected( ! empty( $match ) && $match->status === $status_id ); ?>>
                            <?php echo esc_html( $status_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description" style="margin-top: 4px;">Текущий статус матча</p>
            </div>
            
            <!-- Тур -->
            <div>
                <label for="tour" style="display: block; font-weight: 600; margin-bottom: 8px;">Номер тура</label>
                <input type="number" name="tour" id="tour" min="1" max="999"
                       value="<?php echo ! empty( $match ) && ! empty( $match->tour ) ? intval( $match->tour ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Номер тура в чемпионате</p>
            </div>
        </div>
        
        <!-- Команды -->
        <h2 style="margin-top: 30px;">Команды</h2>
        
        <div style="<?php echo $grid_style; ?>">
            
            <!-- Домашняя команда -->
            <div>
                <label for="home_team_id" style="display: block; font-weight: 600; margin-bottom: 8px;">Домашняя команда <span style="color: red;">*</span></label>
                <select name="home_team_id" id="home_team_id" required style="width: 100%; padding: 8px;">
                    <option value="">— Выберите команду —</option>
                    <?php foreach ( $teams as $team_id => $team_name ) : ?>
                        <option value="<?php echo esc_attr( $team_id ); ?>" 
                            <?php selected( ! empty( $match ) && $match->home_team_id === $team_id ); ?>>
                            <?php echo esc_html( $team_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description" style="margin-top: 4px;">Команда, играющая дома</p>
            </div>
            
            <!-- Гостевая команда -->
            <div>
                <label for="away_team_id" style="display: block; font-weight: 600; margin-bottom: 8px;">Гостевая команда <span style="color: red;">*</span></label>
                <select name="away_team_id" id="away_team_id" required style="width: 100%; padding: 8px;">
                    <option value="">— Выберите команду —</option>
                    <?php foreach ( $teams as $team_id => $team_name ) : ?>
                        <option value="<?php echo esc_attr( $team_id ); ?>" 
                            <?php selected( ! empty( $match ) && $match->away_team_id === $team_id ); ?>>
                            <?php echo esc_html( $team_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description" style="margin-top: 4px;">Команда, играющая в гостях</p>
            </div>
        </div>
        
        <!-- Результат матча -->
        <h2 style="margin-top: 30px;">Результат матча</h2>
        
        <div style="<?php echo $grid_style; ?>">
            
            <!-- Счёт дома -->
            <div>
                <label for="home_score" style="display: block; font-weight: 600; margin-bottom: 8px;">Голов дома</label>
                <input type="number" name="home_score" id="home_score" min="0" max="999"
                       value="<?php echo ! empty( $match ) && ! is_null( $match->home_score ) ? intval( $match->home_score ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Количество голов забитых домашней командой</p>
            </div>
            
            <!-- Счёт гостей -->
            <div>
                <label for="away_score" style="display: block; font-weight: 600; margin-bottom: 8px;">Голов в гостях</label>
                <input type="number" name="away_score" id="away_score" min="0" max="999"
                       value="<?php echo ! empty( $match ) && ! is_null( $match->away_score ) ? intval( $match->away_score ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Количество голов забитых гостевой командой</p>
            </div>
        </div>
        
        <!-- Информация о матче -->
        <h2 style="margin-top: 30px;">Дополнительная информация</h2>
        
        <div style="<?php echo $grid_style; ?>">
            
            <!-- Стадион -->
            <div>
                <label for="stadium_id" style="display: block; font-weight: 600; margin-bottom: 8px;">Стадион</label>
                <select name="stadium_id" id="stadium_id" style="width: 100%; padding: 8px;">
                    <option value="">— Выберите стадион —</option>
                    <?php foreach ( $stadiums as $stadium_id => $stadium_name ) : ?>
                        <option value="<?php echo esc_attr( $stadium_id ); ?>" 
                            <?php selected( ! empty( $match ) && $match->stadium_id === $stadium_id ); ?>>
                            <?php echo esc_html( $stadium_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description" style="margin-top: 4px;">Стадион, где проходил матч</p>
            </div>
            
            <!-- Посещаемость -->
            <div>
                <label for="attendance" style="display: block; font-weight: 600; margin-bottom: 8px;">Посещаемость</label>
                <input type="number" name="attendance" id="attendance" min="0" max="999999"
                       value="<?php echo ! empty( $match ) && ! is_null( $match->attendance ) ? intval( $match->attendance ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">Количество зрителей на матче</p>
            </div>
            
            <!-- Главный судья -->
            <div>
                <label for="main_referee" style="display: block; font-weight: 600; margin-bottom: 8px;">Главный судья</label>
                <input type="text" name="main_referee" id="main_referee" 
                       value="<?php echo ! empty( $match ) && ! empty( $match->main_referee ) ? esc_attr( $match->main_referee ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">ФИ главного судьи</p>
            </div>
            
            <!-- Помощник судьи 1 -->
            <div>
                <label for="assistant_referees_1" style="display: block; font-weight: 600; margin-bottom: 8px;">Помощник судьи 1</label>
                <input type="text" name="assistant_referees_1" id="assistant_referees_1" 
                       value="<?php echo ! empty( $match ) && ! empty( $match->assistant_referees_1 ) ? esc_attr( $match->assistant_referees_1 ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">ФИ первого помощника судьи</p>
            </div>
            
            <!-- Помощник судьи 2 -->
            <div>
                <label for="assistant_referees_2" style="display: block; font-weight: 600; margin-bottom: 8px;">Помощник судьи 2</label>
                <input type="text" name="assistant_referees_2" id="assistant_referees_2" 
                       value="<?php echo ! empty( $match ) && ! empty( $match->assistant_referees_2 ) ? esc_attr( $match->assistant_referees_2 ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">ФИ второго помощника судьи</p>
            </div>
            
            <!-- Четвёртый судья -->
            <div>
                <label for="fourth_referee" style="display: block; font-weight: 600; margin-bottom: 8px;">Четвёртый судья</label>
                <input type="text" name="fourth_referee" id="fourth_referee" 
                       value="<?php echo ! empty( $match ) && ! empty( $match->fourth_referee ) ? esc_attr( $match->fourth_referee ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">ФИ четвёртого судьи</p>
            </div>
            
            <!-- Инспектор судей -->
            <div>
                <label for="referee_inspector" style="display: block; font-weight: 600; margin-bottom: 8px;">Инспектор судей</label>
                <input type="text" name="referee_inspector" id="referee_inspector" 
                       value="<?php echo ! empty( $match ) && ! empty( $match->referee_inspector ) ? esc_attr( $match->referee_inspector ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">ФИ инспектора судей</p>
            </div>
            
            <!-- Делегат -->
            <div>
                <label for="delegate" style="display: block; font-weight: 600; margin-bottom: 8px;">Делегат</label>
                <input type="text" name="delegate" id="delegate" 
                       value="<?php echo ! empty( $match ) && ! empty( $match->delegate ) ? esc_attr( $match->delegate ) : ''; ?>" 
                       style="width: 100%; padding: 8px;">
                <p class="description" style="margin-top: 4px;">ФИ делегата матча</p>
            </div>
        </div>
        
        <!-- Отчет о матче -->
        <h2 style="margin-top: 30px;">Отчет о матче</h2>
        <div style="margin-bottom: 20px;">
            <?php 
            $match_report_content = ! empty( $match ) && ! empty( $match->match_report ) ? $match->match_report : '';
            
            wp_editor( 
                $match_report_content, 
                'match_report', 
                array(
                    'textarea_name' => 'match_report',
                    'media_buttons' => false,
                    'textarea_rows' => 15,
                    'teeny' => false,
                    'quicktags' => true,
                    'tinymce' => array(
                        'toolbar1' => 'formatselect,bold,italic,underline,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,undo,redo',
                        'toolbar2' => '',
                    ),
                )
            );
            ?>
            <p class="description" style="margin-top: 8px;">Развернутый отчет о прошедшем матче (статистика, ключевые моменты, комментарии)</p>
        </div>
        
        <!-- Кнопка редактирования событий матча (только при редактировании) -->
        <?php if ( $is_edit ) : ?>
            <h2 style="margin-top: 30px;">Управление матчем</h2>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f1f1f1; padding: 15px 20px; margin: 20px 0; border-radius: 4px;">
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-lineups&match_id=' . esc_attr( $match->match_id ) ) ); ?>" class="button button-secondary" style="padding: 12px 20px; text-align: center; display: block;">
                    Редактировать составы команд
                </a>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-events&match_id=' . intval( $match->id ) ) ); ?>" class="button button-secondary" style="padding: 12px 20px; text-align: center; display: block;">
                    Редактировать события матча
                </a>
            </div>
        <?php endif; ?>
        
        <!-- Кнопки -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 30px; max-width: 400px;">
            <button type="submit" class="button button-primary button-large" style="padding: 12px 20px; font-size: 16px; text-align: center;">
                <?php echo $is_edit ? 'Обновить матч' : 'Создать матч'; ?>
            </button>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-matches' ) ); ?>" class="button button-large" style="padding: 12px 20px; font-size: 16px; text-align: center; display: flex; align-items: center; justify-content: center;">
                Назад к списку
            </a>
        </div>
    </form>
</div>
