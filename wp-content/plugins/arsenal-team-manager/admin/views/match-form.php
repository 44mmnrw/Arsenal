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
?>

<div class="wrap">
    <h1><?php echo esc_html( $page_title ); ?></h1>
    
    <!-- Сообщения об ошибке -->
    <?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
        <div class="notice notice-error is-dismissible">
            <p><strong>⚠️ Ошибка при сохранении матча!</strong></p>
            <p>Заполните все обязательные поля:</p>
            <ul>
                <?php if ( ! empty( $_GET['missing'] ) ) : 
                    $missing_fields = explode( ', ', sanitize_text_field( $_GET['missing'] ) );
                    foreach ( $missing_fields as $field ) : ?>
                        <li><strong><?php echo esc_html( $field ); ?></strong></li>
                    <?php endforeach;
                else: ?>
                    <li><strong>Дата матча</strong></li>
                    <li><strong>Домашняя команда</strong></li>
                    <li><strong>Гостевая команда</strong></li>
                    <li><strong>Турнир</strong></li>
                    <li><strong>Стадион</strong></li>
                <?php endif; ?>
            </ul>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Закрыть уведомление</span></button>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="match-form">
        
        <input type="hidden" name="action" value="<?php echo esc_attr( $form_action ); ?>">
        
        <?php if ( $is_edit ) : ?>
            <input type="hidden" name="match_id" value="<?php echo intval( $match->id ); ?>">
        <?php endif; ?>
        
        <?php wp_nonce_field( 'arsenal_match_form', 'arsenal_match_nonce' ); ?>
        
        <div class="match-form-wrapper">
            <div class="match-form-left">
                
                <!-- Основная информация о матче -->
                <div class="match-form-section">
                    <h3>📅 Дата и время</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="match_date">Дата матча *</label>
                            <input type="date" name="match_date" id="match_date" required 
                                   value="<?php echo ! empty( $match ) ? esc_attr( $match->match_date ) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="match_time">Время матча</label>
                            <input type="time" name="match_time" id="match_time" 
                                   value="<?php echo ! empty( $match ) && ! empty( $match->match_time ) ? esc_attr( $match->match_time ) : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Турнир и сезон -->
                <div class="match-form-section">
                    <h3>🏆 Турнир и сезон</h3>
                    
                    <div class="form-row full">
                        <div class="form-group">
                            <label for="tournament_id">Турнир *</label>
                            <select name="tournament_id" id="tournament_id" required>
                                <option value="">-- Выберите турнир --</option>
                                <?php foreach ( $tournaments as $tournament_id => $tournament_name ) : ?>
                                    <option value="<?php echo esc_attr( $tournament_id ); ?>" 
                                        <?php selected( ! empty( $match ) && $match->tournament_id == $tournament_id ); ?>>
                                        <?php echo esc_html( $tournament_name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="league_id">Лига</label>
                            <select name="league_id" id="league_id">
                                <option value="">-- Выберите лигу --</option>
                                <?php foreach ( $leagues as $league_id => $league_name ) : ?>
                                    <option value="<?php echo esc_attr( $league_id ); ?>" 
                                        <?php selected( ! empty( $match ) && $match->league_id === $league_id ); ?>>
                                        <?php echo esc_html( $league_name ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="season_id">Сезон</label>
                            <select name="season_id" id="season_id">
                                <option value="">-- Выберите сезон --</option>
                                <?php if ( ! empty( $seasons['seasons'] ) ) : ?>
                                    <?php foreach ( $seasons['seasons'] as $season ) : ?>
                                        <option value="<?php echo esc_attr( $season->season_id ); ?>" 
                                            <?php selected( ! empty( $match ) && $match->season_id === $season->season_id ); ?>>
                                            <?php echo esc_html( $season->season_name ); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="tour">Номер тура</label>
                        <input type="number" name="tour" id="tour" min="1" max="999"
                               value="<?php echo ! empty( $match ) && ! empty( $match->tour ) ? intval( $match->tour ) : ''; ?>">
                    </div>
                </div>
                
                <!-- Команды -->
                <div class="match-form-section">
                    <h3>⚽ Команды</h3>
                    
                    <div class="form-group">
                        <label for="home_team_id">Домашняя команда *</label>
                        <select name="home_team_id" id="home_team_id" required>
                            <option value="">-- Выберите команду --</option>
                            <?php foreach ( $teams as $team_id => $team_name ) : ?>
                                <option value="<?php echo esc_attr( $team_id ); ?>" 
                                    <?php selected( ! empty( $match ) && $match->home_team_id === $team_id ); ?>>
                                    <?php echo esc_html( $team_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="away_team_id">Гостевая команда *</label>
                        <select name="away_team_id" id="away_team_id" required>
                            <option value="">-- Выберите команду --</option>
                            <?php foreach ( $teams as $team_id => $team_name ) : ?>
                                <option value="<?php echo esc_attr( $team_id ); ?>" 
                                    <?php selected( ! empty( $match ) && $match->away_team_id === $team_id ); ?>>
                                    <?php echo esc_html( $team_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Результат матча -->
                <div class="match-form-section">
                    <h3>🎯 Результат</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="home_score">Голов дома</label>
                            <input type="number" name="home_score" id="home_score" min="0" max="999"
                                   value="<?php echo ! empty( $match ) && ! is_null( $match->home_score ) ? intval( $match->home_score ) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="away_score">Голов в гостях</label>
                            <input type="number" name="away_score" id="away_score" min="0" max="999"
                                   value="<?php echo ! empty( $match ) && ! is_null( $match->away_score ) ? intval( $match->away_score ) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Статус матча *</label>
                        <select name="status" id="status" required>
                            <option value="">-- Выберите статус --</option>
                            <?php foreach ( $statuses as $status_id => $status_name ) : ?>
                                <option value="<?php echo esc_attr( $status_id ); ?>" 
                                    <?php selected( ! empty( $match ) && $match->status === $status_id ); ?>>
                                    <?php echo esc_html( $status_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <!-- Место и посещаемость -->
                <div class="match-form-section">
                    <h3>🏟️ Место проведения</h3>
                    
                    <div class="form-group">
                        <label for="stadium_id">Стадион *</label>
                        <select name="stadium_id" id="stadium_id" required>
                            <option value="">-- Выберите стадион --</option>
                            <?php foreach ( $stadiums as $stadium_id => $stadium_name ) : ?>
                                <option value="<?php echo esc_attr( $stadium_id ); ?>" 
                                    <?php selected( ! empty( $match ) && $match->stadium_id === $stadium_id ); ?>>
                                    <?php echo esc_html( $stadium_name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="attendance">Посещаемость</label>
                        <input type="number" name="attendance" id="attendance" min="0" max="999999"
                               value="<?php echo ! empty( $match ) && ! is_null( $match->attendance ) ? intval( $match->attendance ) : ''; ?>">
                    </div>
                </div>
                
                <!-- Судьи -->
                <div class="match-form-section">
                    <h3>👨‍⚖️ Судейство</h3>
                    
                    <div class="form-group">
                        <label for="main_referee">Главный судья</label>
                        <input type="text" name="main_referee" id="main_referee" 
                               value="<?php echo ! empty( $match ) && ! empty( $match->main_referee ) ? esc_attr( $match->main_referee ) : ''; ?>">
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="assistant_referees_1">Помощник 1</label>
                            <input type="text" name="assistant_referees_1" id="assistant_referees_1" 
                                   value="<?php echo ! empty( $match ) && ! empty( $match->assistant_referees_1 ) ? esc_attr( $match->assistant_referees_1 ) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="assistant_referees_2">Помощник 2</label>
                            <input type="text" name="assistant_referees_2" id="assistant_referees_2" 
                                   value="<?php echo ! empty( $match ) && ! empty( $match->assistant_referees_2 ) ? esc_attr( $match->assistant_referees_2 ) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fourth_referee">4-й судья</label>
                            <input type="text" name="fourth_referee" id="fourth_referee" 
                                   value="<?php echo ! empty( $match ) && ! empty( $match->fourth_referee ) ? esc_attr( $match->fourth_referee ) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="referee_inspector">Инспектор судей</label>
                            <input type="text" name="referee_inspector" id="referee_inspector" 
                                   value="<?php echo ! empty( $match ) && ! empty( $match->referee_inspector ) ? esc_attr( $match->referee_inspector ) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="delegate">Делегат</label>
                        <input type="text" name="delegate" id="delegate" 
                               value="<?php echo ! empty( $match ) && ! empty( $match->delegate ) ? esc_attr( $match->delegate ) : ''; ?>">
                    </div>
                </div>
                
                <!-- Отчет о матче -->
                <div class="match-form-section">
                    <h3>📝 Отчет о матче</h3>
                    <?php 
                    $match_report_content = ! empty( $match ) && ! empty( $match->match_report ) ? $match->match_report : '';
                    
                    wp_editor( 
                        wp_unslash( $match_report_content ), 
                        'match_report', 
                        array(
                            'textarea_name' => 'match_report',
                            'media_buttons' => true,
                            'textarea_rows' => 10,
                            'wpautop'       => true,
                        )
                    );
                    ?>
                </div>
                
            </div>
            
            <!-- ПРАВАЯ КОЛОНКА -->
            <div class="match-form-right">
                
                <!-- Управление матчем (только при редактировании) -->
                <?php if ( $is_edit ) : ?>
                <div class="match-form-section">
                    <h3>⚙️ Управление</h3>
                    
                    <div class="match-form-actions">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-lineups&match_id=' . esc_attr( $match->match_id ) ) ); ?>" class="button button-secondary button-block">
                            📋 Составы команд
                        </a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-match-events&match_id=' . intval( $match->id ) ) ); ?>" class="button button-secondary button-block">
                            🎯 События матча
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
        <!-- Кнопки -->
        <div class="match-form-buttons">
            <button type="submit" class="button button-primary">
                <?php echo $is_edit ? '💾 Обновить матч' : '✅ Создать матч'; ?>
            </button>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-matches' ) ); ?>" class="button">
                ❌ Назад
            </a>
        </div>
    </form>
</div>
