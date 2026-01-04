<?php
/**
 * Шаблон турнирного дерева Кубка Беларуси
 *
 * @package Arsenal
 * @since 1.0.0
 */

global $wpdb;

// Получаем ID турнира из параметров URL
$tournament_id = isset($_GET['tournament_id']) ? sanitize_text_field($_GET['tournament_id']) : 'E4DE8DC0'; // Кубок Беларуси

// Получаем последний доступный сезон для этого турнира
$season_id = isset($_GET['season_id']) ? sanitize_text_field($_GET['season_id']) : null;

// Если сезон не указан, берём самый свежий из доступных для турнира
if (!$season_id) {
    $latest_season = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT DISTINCT season_id FROM {$wpdb->prefix}arsenal_matches 
             WHERE tournament_id = %s 
             ORDER BY match_date DESC 
             LIMIT 1",
            $tournament_id
        )
    );
    
    $season_id = $latest_season ? $latest_season->season_id : null;
}

// Получаем название турнира
$tournament = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT tournament_id, name FROM {$wpdb->prefix}arsenal_tournaments WHERE tournament_id = %s LIMIT 1",
        $tournament_id
    )
);

// Если турнира нет в таблице tournaments, пытаемся получить название из матчей
if (!$tournament) {
    $tournament_name_row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT DISTINCT tournament_id FROM {$wpdb->prefix}arsenal_matches WHERE tournament_id = %s LIMIT 1",
            $tournament_id
        )
    );
    
    if (!$tournament_name_row) {
        echo '<p class="tournament-not-found">Турнир не найден</p>';
        return;
    }
    
    // Создаём объект турнира с названием по умолчанию
    $tournament = (object) array(
        'tournament_id' => $tournament_id,
        'name' => 'BETERA-Кубок Беларуси'
    );
}

// Если сезон не найден, показываем ошибку
if (!$season_id) {
    echo '<p class="no-matches">Нет доступных сезонов для этого турнира</p>';
    return;
}

// Получаем все матчи турнира, сортируем по tour (раунду)
$matches = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT m.*, 
                ht.name as home_team_name, 
                at.name as away_team_name,
                s.name as stadium_name
         FROM {$wpdb->prefix}arsenal_matches m
         LEFT JOIN {$wpdb->prefix}arsenal_teams ht ON m.home_team_id = ht.team_id
         LEFT JOIN {$wpdb->prefix}arsenal_teams at ON m.away_team_id = at.team_id
         LEFT JOIN {$wpdb->prefix}arsenal_stadiums s ON m.stadium_id = s.stadium_id
         WHERE m.tournament_id = %s AND m.season_id = %s
         ORDER BY m.tour ASC, m.match_date ASC, m.id ASC",
        $tournament_id,
        $season_id
    )
);

if (empty($matches)) {
    echo '<p class="no-matches">Матчи не найдены</p>';
    return;
}

// Распределяем матчи по турам используя поле tour
$rounds = array(
    '1/16'  => array('title' => '1/16 финала', 'total' => 16, 'matches' => array()),
    '1/8'   => array('title' => '1/8 финала', 'total' => 8, 'matches' => array()),
    '1/4'   => array('title' => '1/4 финала', 'total' => 4, 'matches' => array()),
    '1/2'   => array('title' => '1/2 финала', 'total' => 2, 'matches' => array()),
    'final' => array('title' => '🏆 Финал', 'total' => 1, 'matches' => array()),
);

// Распределяем матчи по раундам на основе поля tour
// tour 1 = 1/16, tour 2 = 1/8, tour 3 = 1/4, tour 4 = 1/2, tour 5 = Финал
foreach ($matches as $match) {
    $tour = (int) ($match->tour ?? 0);
    
    switch ($tour) {
        case 1:
            $rounds['1/16']['matches'][] = $match;
            break;
        case 2:
            $rounds['1/8']['matches'][] = $match;
            break;
        case 3:
            $rounds['1/4']['matches'][] = $match;
            break;
        case 4:
            $rounds['1/2']['matches'][] = $match;
            break;
        case 5:
            $rounds['final']['matches'][] = $match;
            break;
    }
}

// Функция для рендеринга карточки матча
function render_match_card($match) {
    if (!$match) {
        // Пустая ячейка для будущего матча
        echo '<div class="bracket-item empty-match"><div class="empty-card">Предстоящий матч</div></div>';
        return;
    }
    
    $has_result = !is_null($match->home_score) && !is_null($match->away_score);
    $formatted_date = format_russian_date($match->match_date);
    $formatted_time = (isset($match->match_time) ? substr($match->match_time, 0, 5) : "00:00");
    ?>
    <div class="bracket-item">
        <article class="bracket-match-card">
            <div class="match-datetime">
                <?php echo esc_html($formatted_date); ?> • <?php echo esc_html($formatted_time); ?>
            </div>
            <div class="match-team match-team-home <?php echo ($has_result && $match->home_score > $match->away_score) ? 'winner' : ''; ?>">
                <div class="team-info">
                    <div class="team-logo">
                        <span class="team-initials"><?php echo esc_html(get_team_initials($match->home_team_name)); ?></span>
                    </div>
                    <span class="team-name"><?php echo esc_html($match->home_team_name ?? 'Предстоящий матч'); ?></span>
                </div>
                <div class="team-score"><?php echo $has_result ? esc_html($match->home_score) : '—'; ?></div>
            </div>
            <div class="match-team match-team-away <?php echo ($has_result && $match->away_score > $match->home_score) ? 'winner' : ''; ?>">
                <div class="team-info">
                    <div class="team-logo">
                        <span class="team-initials"><?php echo esc_html(get_team_initials($match->away_team_name)); ?></span>
                    </div>
                    <span class="team-name"><?php echo esc_html($match->away_team_name ?? 'Предстоящий матч'); ?></span>
                </div>
                <div class="team-score"><?php echo $has_result ? esc_html($match->away_score) : '—'; ?></div>
            </div>
        </article>
    </div>
    <?php
}

// Функция для получения инициалов команды
function get_team_initials($team_name) {
    if (empty($team_name)) return 'ПМ';
    
    // Пытаемся найти паттерн "ФК Название"
    if (preg_match('/ФК\s+([А-Яа-яЁё]+)/u', $team_name, $matches)) {
        return mb_strtoupper(mb_substr($matches[1], 0, 2, 'UTF-8'), 'UTF-8');
    }
    
    // Если не нашли, берём первые 2 буквы названия
    return mb_strtoupper(mb_substr($team_name, 0, 2, 'UTF-8'), 'UTF-8');
}

// Функция для форматирования даты на русском языке
function format_russian_date($date_string) {
    $months = array(
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
        5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
        9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
    );
    
    $timestamp = strtotime($date_string);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    
    return $day . ' ' . $month;
}

?>

<div class="bracket-grid-container">
    
    <!-- Ряд 1: Финал (1 карточка) -->
    <div class="bracket-item bracket-final">
        <?php 
        $match = $rounds['final']['matches'][0] ?? null;
        if ($match):
            $has_result = !is_null($match->home_score) && !is_null($match->away_score);
            $formatted_date = format_russian_date($match->match_date);
            $formatted_time = (isset($match->match_time) ? substr($match->match_time, 0, 5) : "00:00");
        ?>
            <article class="bracket-match-card">
                <div class="match-datetime">
                    <?php echo esc_html($formatted_date); ?> • <?php echo esc_html($formatted_time); ?>
                </div>
                <div class="match-team match-team-home <?php echo ($has_result && $match->home_score > $match->away_score) ? 'winner' : ''; ?>">
                    <div class="team-info">
                        <div class="team-logo">
                            <span class="team-initials"><?php echo esc_html(get_team_initials($match->home_team_name)); ?></span>
                        </div>
                        <span class="team-name"><?php echo esc_html($match->home_team_name ?? 'Предстоящий матч'); ?></span>
                    </div>
                    <div class="team-score"><?php echo $has_result ? esc_html($match->home_score) : '—'; ?></div>
                </div>
                <div class="match-team match-team-away <?php echo ($has_result && $match->away_score > $match->home_score) ? 'winner' : ''; ?>">
                    <div class="team-info">
                        <div class="team-logo">
                            <span class="team-initials"><?php echo esc_html(get_team_initials($match->away_team_name)); ?></span>
                        </div>
                        <span class="team-name"><?php echo esc_html($match->away_team_name ?? 'Предстоящий матч'); ?></span>
                    </div>
                    <div class="team-score"><?php echo $has_result ? esc_html($match->away_score) : '—'; ?></div>
                </div>
            </article>
        <?php else: ?>
            <div class="empty-card">
                <span class="tbd-text">Предстоящий матч</span>
                <span class="tbd-label">Финал</span>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Ряд 2: Соединительная линия + Заголовок 1/2 -->
    <div class="bracket-item connector-line">
        <h3 class="round-title">1/2 финала</h3>
    </div>
    
    <!-- Ряд 3: 1/2 финала (2 карточки) -->
    <?php for ($i = 0; $i < 2; $i++): 
        $match = $rounds['1/2']['matches'][$i] ?? null;
    ?>
        <div class="bracket-item bracket-half-<?php echo $i + 1; ?>">
            <?php if ($match):
                $has_result = !is_null($match->home_score) && !is_null($match->away_score);
                $formatted_date = format_russian_date($match->match_date);
                $formatted_time = (isset($match->match_time) ? substr($match->match_time, 0, 5) : "00:00");
            ?>
                <article class="bracket-match-card">
                    <div class="match-datetime">
                        <?php echo esc_html($formatted_date); ?> • <?php echo esc_html($formatted_time); ?>
                    </div>
                    <div class="match-team match-team-home <?php echo ($has_result && $match->home_score > $match->away_score) ? 'winner' : ''; ?>">
                        <div class="team-info">
                            <div class="team-logo">
                                <span class="team-initials"><?php echo esc_html(get_team_initials($match->home_team_name)); ?></span>
                            </div>
                            <span class="team-name"><?php echo esc_html($match->home_team_name ?? 'Предстоящий матч'); ?></span>
                        </div>
                        <div class="team-score"><?php echo $has_result ? esc_html($match->home_score) : '—'; ?></div>
                </div>
                <div class="match-team match-team-away <?php echo ($has_result && $match->away_score > $match->home_score) ? 'winner' : ''; ?>">
                        <div class="team-info">
                            <div class="team-logo">
                                <span class="team-initials"><?php echo esc_html(get_team_initials($match->away_team_name)); ?></span>
                            </div>
                            <span class="team-name"><?php echo esc_html($match->away_team_name ?? 'Предстоящий матч'); ?></span>
                        </div>
                        <div class="team-score"><?php echo $has_result ? esc_html($match->away_score) : '—'; ?></div>
                </div>
            </article>
            <?php else: ?>
                <div class="empty-card">
                    <span class="tbd-text">Предстоящий матч</span>
                    <span class="tbd-label">1/2 финала</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
    
    <!-- Ряд 4: Соединительная линия + Заголовок 1/4 -->
    <div class="bracket-item connector-line">
        <h3 class="round-title">1/4 финала</h3>
    </div>
    
    <!-- Ряд 5: 1/4 финала (4 карточки) -->
    <?php for ($i = 0; $i < 4; $i++): 
        $match = $rounds['1/4']['matches'][$i] ?? null;
    ?>
        <div class="bracket-item bracket-quarter-<?php echo $i + 1; ?>">
            <?php if ($match):
                $has_result = !is_null($match->home_score) && !is_null($match->away_score);
                $formatted_date = format_russian_date($match->match_date);
                $formatted_time = (isset($match->match_time) ? substr($match->match_time, 0, 5) : "00:00");
            ?>
                <article class="bracket-match-card">
                    <div class="match-datetime">
                        <?php echo esc_html($formatted_date); ?> • <?php echo esc_html($formatted_time); ?>
                    </div>
                    <div class="match-team match-team-home <?php echo ($has_result && $match->home_score > $match->away_score) ? 'winner' : ''; ?>">
                        <div class="team-info">
                            <div class="team-logo">
                                <span class="team-initials"><?php echo esc_html(get_team_initials($match->home_team_name)); ?></span>
                            </div>
                            <span class="team-name"><?php echo esc_html($match->home_team_name ?? 'Предстоящий матч'); ?></span>
                        </div>
                        <div class="team-score"><?php echo $has_result ? esc_html($match->home_score) : '—'; ?></div>
                </div>
                <div class="match-team match-team-away <?php echo ($has_result && $match->away_score > $match->home_score) ? 'winner' : ''; ?>">
                        <div class="team-info">
                            <div class="team-logo">
                                <span class="team-initials"><?php echo esc_html(get_team_initials($match->away_team_name)); ?></span>
                            </div>
                            <span class="team-name"><?php echo esc_html($match->away_team_name ?? 'Предстоящий матч'); ?></span>
                        </div>
                        <div class="team-score"><?php echo $has_result ? esc_html($match->away_score) : '—'; ?></div>
                </div>
            </article>
            <?php else: ?>
                <div class="empty-card">
                    <span class="tbd-text">Предстоящий матч</span>
                    <span class="tbd-label">1/4 финала</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
    
    <!-- Ряд 6: Соединительная линия + Заголовок 1/8 -->
    <div class="bracket-item connector-line">
        <h3 class="round-title">1/8 финала</h3>
    </div>
    
    <!-- Ряды 7-8: 1/8 финала (8 карточек, по 4 в ряду) -->
    <?php for ($i = 0; $i < 8; $i++): 
        $match = $rounds['1/8']['matches'][$i] ?? null;
        $row = floor($i / 4) + 7;
    ?>
        <div class="bracket-item bracket-eighth-<?php echo $i + 1; ?>">
            <?php if ($match):
                $has_result = !is_null($match->home_score) && !is_null($match->away_score);
                $formatted_date = format_russian_date($match->match_date);
                $formatted_time = (isset($match->match_time) ? substr($match->match_time, 0, 5) : "00:00");
            ?>
                <article class="bracket-match-card">
                    <div class="match-datetime">
                        <?php echo esc_html($formatted_date); ?> • <?php echo esc_html($formatted_time); ?>
                    </div>
                    <div class="match-team match-team-home <?php echo ($has_result && $match->home_score > $match->away_score) ? 'winner' : ''; ?>">
                        <div class="team-info">
                            <div class="team-logo">
                                <span class="team-initials"><?php echo esc_html(get_team_initials($match->home_team_name)); ?></span>
                            </div>
                            <span class="team-name"><?php echo esc_html($match->home_team_name ?? 'Предстоящий матч'); ?></span>
                        </div>
                        <div class="team-score"><?php echo $has_result ? esc_html($match->home_score) : '—'; ?></div>
                </div>
                <div class="match-team match-team-away <?php echo ($has_result && $match->away_score > $match->home_score) ? 'winner' : ''; ?>">
                        <div class="team-info">
                            <div class="team-logo">
                                <span class="team-initials"><?php echo esc_html(get_team_initials($match->away_team_name)); ?></span>
                            </div>
                            <span class="team-name"><?php echo esc_html($match->away_team_name ?? 'Предстоящий матч'); ?></span>
                        </div>
                        <div class="team-score"><?php echo $has_result ? esc_html($match->away_score) : '—'; ?></div>
                </div>
            </article>
            <?php else: ?>
                <div class="empty-card">
                    <span class="tbd-text">Предстоящий матч</span>
                    <span class="tbd-label">1/8 финала</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
    
    <!-- Ряд 9: Соединительная линия + Заголовок 1/16 -->
    <div class="bracket-item connector-line">
        <h3 class="round-title">1/16 финала</h3>
    </div>
    
    <!-- Ряды 10-13: 1/16 финала (16 карточек, по 4 в ряду) -->
    <?php for ($i = 0; $i < 16; $i++): 
        $match = $rounds['1/16']['matches'][$i] ?? null;
        $row = floor($i / 4) + 10;
    ?>
        <div class="bracket-item bracket-sixteenth-<?php echo $i + 1; ?>">
            <?php if ($match):
                $has_result = !is_null($match->home_score) && !is_null($match->away_score);
                $formatted_date = format_russian_date($match->match_date);
                $formatted_time = (isset($match->match_time) ? substr($match->match_time, 0, 5) : "00:00");
            ?>
                <article class="bracket-match-card">
                    <div class="match-datetime">
                        <?php echo esc_html($formatted_date); ?> • <?php echo esc_html($formatted_time); ?>
                    </div>
                    <div class="match-team match-team-home <?php echo ($has_result && $match->home_score > $match->away_score) ? 'winner' : ''; ?>">
                        <div class="team-info">
                            <div class="team-logo">
                                <span class="team-initials"><?php echo esc_html(get_team_initials($match->home_team_name)); ?></span>
                            </div>
                            <span class="team-name"><?php echo esc_html($match->home_team_name ?? 'Предстоящий матч'); ?></span>
                        </div>
                        <div class="team-score"><?php echo $has_result ? esc_html($match->home_score) : '—'; ?></div>
                </div>
                <div class="match-team match-team-away <?php echo ($has_result && $match->away_score > $match->home_score) ? 'winner' : ''; ?>">
                        <div class="team-info">
                            <div class="team-logo">
                                <span class="team-initials"><?php echo esc_html(get_team_initials($match->away_team_name)); ?></span>
                            </div>
                            <span class="team-name"><?php echo esc_html($match->away_team_name ?? 'Предстоящий матч'); ?></span>
                        </div>
                        <div class="team-score"><?php echo $has_result ? esc_html($match->away_score) : '—'; ?></div>
                </div>
            </article>
            <?php else: ?>
                <div class="empty-card">
                    <span class="tbd-text">Предстоящий матч</span>
                    <span class="tbd-label">1/16 финала</span>
                </div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>

</div>








