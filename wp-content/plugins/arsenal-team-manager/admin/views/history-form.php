<?php
/**
 * Arsenal History Form
 * 
 * Форма управления историей клуба (post_meta)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e( 'История клуба', 'arsenal-team-manager' ); ?></h1>
    
    <form method="POST" class="arsenal-history-form">
        <input type="hidden" name="action" value="arsenal_save_history" />
        <?php wp_nonce_field( 'arsenal_save_history', '_wpnonce' ); ?>
        
        <!-- Контейнер двух колонок для всех секций -->
        <div class="form-columns-container">
        
        <!-- Основная информация -->
        <div class="form-section">
            <h3><?php _e( 'Основная информация', 'arsenal-team-manager' ); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="title"><?php _e( 'Название главной секции', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="title" name="title" value="<?php echo esc_attr( $history['title'] ); ?>" class="large-text" />
                        <p class="description"><?php _e( 'Например: "История клуба"', 'arsenal-team-manager' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="description"><?php _e( 'Описание', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <?php
                        wp_editor(
                            wp_unslash( $history['description'] ),
                            'description',
                            array(
                                'textarea_rows' => 10,
                                'media_buttons' => true,
                                'wpautop'       => true,
                            )
                        );
                        ?>
                        <p class="description"><?php _e( 'Основной текст истории клуба', 'arsenal-team-manager' ); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Временная шкала -->
        <div class="form-section">
            <h3><?php _e( 'Временная шкала (JSON)', 'arsenal-team-manager' ); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="scale"><?php _e( 'Данные временной шкалы', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <textarea id="scale" name="scale" rows="10" class="large-text code"><?php echo esc_textarea( is_array( $history['scale'] ) ? json_encode( $history['scale'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) : $history['scale'] ); ?></textarea>
                        <p class="description">
                            <?php _e( 'JSON формат (год и описание события):', 'arsenal-team-manager' ); ?><br/>
                            <code>[<br/>    
  {<br/>
    "year": 2021,<br/>
    "event": "Первый титул - Чемпион Первой лиги"<br/>
  },<br/>
  {<br/>
    "year": 2023,<br/>
    "event": "Повторное чемпионство в Первой лиге"<br/>
  }<br/>
]</code><br/>
                            <strong><?php _e( 'Поля:', 'arsenal-team-manager' ); ?></strong>
                            <ul style="margin: 10px 0; padding-left: 20px;">
                                <li><code>year</code> (число) - год события</li>
                                <li><code>event</code> (строка) - описание события (появится в подсказке при наведении)</li>
                            </ul>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Рекорды и достижения -->
        <div class="form-section">
            <h3><?php _e( 'Рекорды и достижения', 'arsenal-team-manager' ); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="title_second"><?php _e( 'Название секции', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="title_second" name="title_second" value="<?php echo esc_attr( $history['title_second'] ); ?>" class="large-text" />
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="records"><?php _e( 'Рекорды (JSON)', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <textarea id="records" name="records" rows="8" class="large-text code"><?php echo esc_textarea( is_array( $history['records'] ) ? json_encode( $history['records'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) : $history['records'] ); ?></textarea>
                        <p class="description">
                            <?php _e( 'JSON формат (style: primary или white, icon: названием из спрайта):', 'arsenal-team-manager' ); ?><br/>
                            <code>[<br/>
  {<br/>
    "title": "Титулы",<br/>
    "style": "primary",<br/>
    "icon": "cup",<br/>
    "items": [<br/>
      "Двукратный чемпион Первой лиги (2021, 2023)",<br/>
      "Чемпион Второй лиги (2019)"<br/>
    ]<br/>
  },<br/>
  {<br/>
    "title": "Лучшие результаты",<br/>
    "style": "white",<br/>
    "icon": "chart",<br/>
    "items": [<br/>
      "В Высшей лиге: 10 место (2024)",<br/>
      "Самая крупная победа: 3:0"<br/>
    ]<br/>
  }<br/>
]</code>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="achievements"><?php _e( 'Достижения (JSON)', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <textarea id="achievements" name="achievements" rows="8" class="large-text code"><?php echo esc_textarea( is_array( $history['achievements'] ) ? json_encode( $history['achievements'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) : $history['achievements'] ); ?></textarea>
                        <p class="description">
                            <?php _e( 'JSON формат (для дополнительных статистических карточек):', 'arsenal-team-manager' ); ?><br/>
                            <code>[<br/>
  {<br/>
    "label": "Наибольшее количество матчей",<br/>
    "value": "Александр Скшинецкий – 65"<br/>
  },<br/>
  {<br/>
    "label": "Лучший бомбардир всех времен",<br/>
    "value": "Юрий Ловец – 10 голов"<br/>
  }<br/>
]</code>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Дополнительная секция -->
        <div class="form-section">
            <h3><?php _e( 'Дополнительная секция', 'arsenal-team-manager' ); ?></h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="title_third"><?php _e( 'Название секции', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="title_third" name="title_third" value="<?php echo esc_attr( $history['title_third'] ); ?>" class="large-text" />
                        <p class="description"><?php _e( 'Например: "Домашние стадионы"', 'arsenal-team-manager' ); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="additional_cards"><?php _e( 'Карточки (JSON)', 'arsenal-team-manager' ); ?></label>
                    </th>
                    <td>
                        <textarea id="additional_cards" name="additional_cards" rows="6" class="large-text code"><?php echo esc_textarea( is_array( $history['additional_cards'] ) ? json_encode( $history['additional_cards'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) : $history['additional_cards'] ); ?></textarea>
                        <p class="description">
                            <?php _e( 'JSON формат:', 'arsenal-team-manager' ); ?><br/>
                            <code>[<br/>
  {<br/>
    "label": "РЦОП-Стайки",<br/>
    "value": "Минск • Вместимость: 1,500"<br/>
  },<br/>
  {<br/>
    "label": "Городской стадион",<br/>
    "value": "Борисов • Вместимость: 5,402"<br/>
  }<br/>
]</code>
                        </p>
                    </td>
                </tr>
            </table>
        </div>
        
        </div><!-- закрытие form-columns-container -->
        
        <!-- Кнопки -->
        <div class="form-actions">
            <?php submit_button( __( 'Сохранить историю', 'arsenal-team-manager' ), 'primary', 'submit', true ); ?>
        </div>
    </form>
</div>

<style>
    .arsenal-history-form {
        display: block;
        width: 100%;
        box-sizing: border-box;
    }
    
    .form-columns-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin: 20px 0;
        box-sizing: border-box;
        width: 100%;
    }
    
    @media (max-width: 1200px) {
        .form-columns-container {
            grid-template-columns: 1fr;
        }
    }
    
    .arsenal-history-form .form-section {
        background: #fff;
        padding: 20px;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0,0,0,.04);
    }
    
    .arsenal-history-form .form-section h3 {
        margin: 0 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #0073aa;
        color: #0073aa;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .arsenal-history-form .form-table {
        margin: 0;
        width: 100%;
    }
    
    .arsenal-history-form .form-table tr {
        border: none;
    }
    
    .arsenal-history-form .code {
        font-family: 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.6;
        background: #f5f5f5;
        border: 1px solid #ddd;
        padding: 10px;
    }
    
    .form-actions {
        margin: 30px 0;
        padding: 20px;
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
    }
    
    .form-actions .button {
        margin-right: 10px;
    }
</style>
