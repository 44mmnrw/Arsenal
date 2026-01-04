<?php
/**
 * Template Name: Match Selector Page
 * 
 * Страница с селекторами для выбора команды и даты матча
 */

get_header();

global $wpdb;

// Получаем список всех команд из БД
$teams = $wpdb->get_results(
    "SELECT DISTINCT t.id, t.name, t.team_id
     FROM {$wpdb->prefix}arsenal_teams t
     WHERE t.team_id IN (
        SELECT DISTINCT home_team_id FROM {$wpdb->prefix}arsenal_matches 
        UNION 
        SELECT DISTINCT away_team_id FROM {$wpdb->prefix}arsenal_matches
     )
     ORDER BY t.name"
);

// Получаем все доступные даты матчей
$dates = $wpdb->get_results(
    "SELECT DISTINCT match_date 
     FROM {$wpdb->prefix}arsenal_matches 
     WHERE status = 'FT' AND home_score IS NOT NULL AND away_score IS NOT NULL
     ORDER BY match_date DESC"
);
?>

<main id="primary" class="site-main">
    <div class="container">
        <div class="match-selector-wrapper">
            <h1>Выбор матча</h1>
            
            <form method="GET" class="match-selector-form">
                <div class="form-group">
                    <label for="team_select">Команда:</label>
                    <select id="team_select" name="team_name" required>
                        <option value="">-- Выберите команду --</option>
                        <?php foreach ( $teams as $team ) : ?>
                            <option value="<?php echo esc_attr( $team->name ); ?>" <?php selected( isset( $_GET['team_name'] ) ? sanitize_text_field( $_GET['team_name'] ) : '', $team->name ); ?>>
                                <?php echo esc_html( $team->name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date_select">Дата матча:</label>
                    <select id="date_select" name="date" required>
                        <option value="">-- Выберите дату --</option>
                        <?php foreach ( $dates as $date_obj ) : ?>
                            <option value="<?php echo esc_attr( $date_obj->match_date ); ?>" <?php selected( isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : '', $date_obj->match_date ); ?>>
                                <?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $date_obj->match_date ) ) ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn-submit">Показать матч</button>
            </form>

            <?php
            // Если выбраны оба параметра - показываем матч
            if ( ! empty( $_GET['team_name'] ) && ! empty( $_GET['date'] ) ) {
                echo '<div class="match-result">';
                get_template_part( 'template-parts/blocks/match-details' );
                echo '</div>';
            }
            ?>
        </div>
    </div>
</main>

<?php
get_footer();

// Стили
?>

<style>
.match-selector-wrapper {
    padding: 40px 0;
}

.match-selector-wrapper h1 {
    text-align: center;
    margin-bottom: 40px;
    font-size: 32px;
    color: #1a1a1a;
}

.match-selector-form {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    max-width: 500px;
    margin: 0 auto 40px;
}

.form-group {
    margin-bottom: 20px;
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
    font-size: 14px;
}

.form-group select {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background-color: white;
    color: #333;
    cursor: pointer;
}

.form-group select:focus {
    outline: none;
    border-color: #4285f4;
    box-shadow: 0 0 0 3px rgba(66, 133, 244, 0.1);
}

.btn-submit {
    width: 100%;
    padding: 12px;
    background: #4285f4;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-submit:hover {
    background: #3367d6;
}

.btn-submit:active {
    background: #2a56c6;
}

.match-result {
    margin-top: 40px;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 600px) {
    .match-selector-form {
        padding: 20px;
    }
    
    .match-selector-wrapper h1 {
        font-size: 24px;
        margin-bottom: 30px;
    }
}
</style>
