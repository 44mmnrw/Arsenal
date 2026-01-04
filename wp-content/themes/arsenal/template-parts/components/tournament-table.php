<?php
/**
 * Template part: Tournament Table Widget
 * 
 * Виджет турнирной таблицы на главной странице
 * Показывает Арсенал в центре с контекстом (2 выше, 2 ниже)
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Используем единую функцию расчета турнирной таблицы с фильтром по tournament_id
$tournament_id = '71CFDAA6'; // Беларусь Высшая лига
$active_season_year = intval( get_option( 'arsenal_active_season_year', intval( date( 'Y' ) ) ) );
$standings = arsenal_calculate_standings( $active_season_year, $tournament_id );

// ===== ЭТАП 1: Находим позицию Арсенала =====
$arsenal_position = -1;

foreach ( $standings as $pos => $team ) {
    if ( stripos( $team['name'], 'Арсенал' ) !== false ) {
        $arsenal_position = $pos;
        break;
    }
}

// ===== ЭТАП 2: Определяем диапазон для отображения =====
$display_standings = [];
$start_position = 1;

if ( $arsenal_position >= 0 ) {
    if ( $arsenal_position < 2 ) {
        // Если Арсенал в топ-3 - показываем топ-5
        $display_standings = array_slice( $standings, 0, min( 5, count( $standings ) ) );
        $start_position = 1;
    } elseif ( $arsenal_position >= count( $standings ) - 2 ) {
        // Если Арсенал в последних 3 - показываем последние 5
        $start = max( 0, count( $standings ) - 5 );
        $display_standings = array_slice( $standings, $start );
        $start_position = $start + 1;
    } else {
        // Арсенал в середине - показываем его с контекстом (2 выше, 2 ниже)
        $start = max( 0, $arsenal_position - 2 );
        $end = min( count( $standings ), $arsenal_position + 3 );
        $display_standings = array_slice( $standings, $start, $end - $start );
        $start_position = $start + 1;
    }
} else {
    // Если Арсенал не найден - показываем топ-5
    $display_standings = array_slice( $standings, 0, min( 5, count( $standings ) ) );
    $start_position = 1;
}
?>

<section class="tournament-table-section">
    <div class="container">
        <div class="tournament-header">
            <div class="tournament-header__left">
                <span class="tournament-header__label">ЧЕМПИОНАТ</span>
                <h2 class="tournament-header__title">Высшая лига Беларуси <?php echo esc_html( $active_season_year ); ?></h2>
            </div>
            <a href="<?php echo esc_url( home_url( '/standings/' ) ); ?>" class="tournament-header__link">
                <span>Полная таблица</span>
                <?php arsenal_icon( 'icon-arrow-right', 20, 12 ); ?>
            </a>
        </div>

        <div class="tournament-table-wrapper">
            <?php if ( ! empty( $display_standings ) ) : ?>
                <table class="tournament-table">
                    <thead>
                        <tr>
                            <th class="col-position">#</th>
                            <th class="col-team">Команда</th>
                            <th class="col-games">И</th>
                            <th class="col-wins">В</th>
                            <th class="col-draws">Н</th>
                            <th class="col-losses">П</th>
                            <th class="col-points">О</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $display_standings as $index => $team ) : ?>
                            <?php $display_position = $start_position + $index; ?>
                            <tr<?php echo stripos( $team['name'], 'Арсенал' ) !== false ? ' class="highlight-row"' : ''; ?>>
                                <td class="col-position"><?php echo esc_html( $display_position ); ?></td>
                                <td class="col-team">
                                    <?php if ( ! empty( $team['logo_url'] ) ) : ?>
                                        <img 
                                            src="<?php echo esc_url( $team['logo_url'] ); ?>" 
                                            alt="" 
                                            class="team-badge"
                                            width="24" 
                                            height="24"
                                            loading="lazy"
                                        >
                                    <?php endif; ?>
                                    <span class="team-name"><?php echo esc_html( $team['name'] ); ?></span>
                                </td>
                                <td class="col-games"><?php echo esc_html( $team['played'] ); ?></td>
                                <td class="col-wins"><?php echo esc_html( $team['wins'] ); ?></td>
                                <td class="col-draws"><?php echo esc_html( $team['draws'] ); ?></td>
                                <td class="col-losses"><?php echo esc_html( $team['losses'] ); ?></td>
                                <td class="col-points"><?php echo esc_html( $team['points'] ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else : ?>
                <p class="tournament-table__error">Данные турнирной таблицы недоступны</p>
            <?php endif; ?>
        </div>
    </div>
</section>
