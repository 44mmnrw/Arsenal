<?php
/**
 * Класс: Регистрация меню в админке WordPress
 *
 * Отвечает исключительно за регистрацию пунктов меню и рендер страниц.
 * Бизнес-логика и инициализация зависимостей — в Arsenal_Team_Manager.
 *
 * @package Arsenal_Team_Manager
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Arsenal_Menu_Manager {

    /**
     * Ссылка на основной объект плагина (для доступа к инстансам admin-классов)
     *
     * @var Arsenal_Team_Manager
     */
    private $plugin;

    /**
     * @param Arsenal_Team_Manager $plugin
     */
    public function __construct( Arsenal_Team_Manager $plugin ) {
        $this->plugin = $plugin;
        add_action( 'admin_head', array( $this, 'hide_arsenal_menu_hint' ) );
    }

    /**
     * Убирает визуальный индикатор «скрытого подменю» у top-level пункта Арсенал.
     *
     * Функциональность подменю сохраняется, скрывается только декоративная стрелка.
     */
    public function hide_arsenal_menu_hint() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <style id="arsenal-menu-hint-fix">
            #adminmenu .toplevel_page_arsenal-team .wp-menu-arrow,
            #adminmenu .toplevel_page_arsenal-team > a.menu-top::after {
                display: none !important;
            }
        </style>
        <?php
    }

    /**
     * Регистрация всех пунктов меню.
     * Хукается на admin_menu.
     */
    public function register() {
        $slug = 'arsenal-team';

        add_menu_page(
            'Арсенал',
            'Арсенал',
            'manage_options',
            $slug,
            array( $this, 'render_dashboard' ),
            'dashicons-dribbble',
            26
        );

        // Резервная ссылка в меню «Плагины», если верхний пункт скрыт кастомизацией админки.
        add_submenu_page(
            'plugins.php',
            'Arsenal Team Manager',
            'Arsenal Team Manager',
            'manage_options',
            $slug,
            array( $this, 'render_dashboard' )
        );

        // Явный пункт главной страницы плагина (dashboard)
        add_submenu_page(
            $slug,
            'Главная',
            'Главная',
            'manage_options',
            $slug,
            array( $this, 'render_dashboard' )
        );

        // ── Видимые подменю (сортировка: алфавит) ──────────────────────────

        add_submenu_page( $slug, 'Библиотека иконок', 'Иконки', 'manage_options',
            'arsenal-icon-library', array( $this, 'render_icon_library' ) );

        add_submenu_page( $slug, 'Игроки', 'Игроки', 'manage_options',
            'arsenal-players', array( $this, 'render_players_list' ) );

        add_submenu_page( $slug, 'Команды лиги', 'Команды лиги', 'manage_options',
            'arsenal-teams', array( $this, 'render_teams_list' ) );

        add_submenu_page( $slug, 'Контракты', 'Контракты', 'manage_options',
            'arsenal-contracts', array( $this, 'render_contracts' ) );

        add_submenu_page( $slug, 'Корректировки статистики игроков', 'Корректировки статистики', 'manage_options',
            'arsenal-player-stats-corrections', array( $this, 'render_player_stats_corrections' ) );

        add_submenu_page( $slug, 'Корректировки таблицы', 'Корректировки таблицы', 'manage_options',
            'arsenal-adjustments', array( $this, 'render_adjustments_list' ) );

        add_submenu_page( $slug, 'Лиги', 'Лиги', 'manage_options',
            'arsenal-leagues', array( $this, 'render_leagues_list' ) );

        add_submenu_page( $slug, 'Матчи', 'Матчи', 'manage_options',
            'arsenal-matches', array( $this->plugin->match_admin, 'render_matches_list' ) );

        add_submenu_page( $slug, 'Персонал', 'Персонал', 'manage_options',
            'arsenal-staff', array( $this, 'render_staff_list' ) );

        add_submenu_page( $slug, 'Сезоны', 'Сезоны', 'manage_options',
            'arsenal-seasons', array( $this, 'render_seasons_list' ) );

        add_submenu_page( $slug, 'Спонсоры и партнеры', 'Спонсоры и партнеры', 'manage_options',
            'arsenal-sponsors', array( $this, 'render_sponsors_list' ) );

        add_submenu_page( $slug, 'Стадионы', 'Стадионы', 'manage_options',
            'arsenal-stadiums', array( $this, 'render_stadiums_list' ) );

        add_submenu_page( $slug, 'Турниры', 'Турниры', 'manage_options',
            'arsenal-tournaments', array( $this->plugin->tournament_admin, 'render_tournaments_list' ) );

        // ── Скрытые страницы (без пункта меню) ────────────────────────────

        // Спонсоры
        add_submenu_page( '', 'Добавить спонсора', 'Добавить спонсора', 'manage_options',
            'arsenal-sponsor-add', array( $this, 'render_sponsor_form' ) );
        add_submenu_page( '', 'Редактировать спонсора', 'Редактировать спонсора', 'manage_options',
            'arsenal-sponsor-edit', array( $this, 'render_sponsor_form' ) );

        // Стадионы
        add_submenu_page( '', 'Добавить стадион', 'Добавить стадион', 'manage_options',
            'arsenal-stadium-add', array( $this, 'render_stadium_form' ) );
        add_submenu_page( '', 'Редактировать стадион', 'Редактировать стадион', 'manage_options',
            'arsenal-stadium-edit', array( $this, 'render_stadium_form' ) );

        // Сезоны
        add_submenu_page( '', 'Добавить сезон', 'Добавить сезон', 'manage_options',
            'arsenal-season-add', array( $this, 'render_season_form' ) );
        add_submenu_page( '', 'Редактировать сезон', 'Редактировать сезон', 'manage_options',
            'arsenal-season-edit', array( $this, 'render_season_form' ) );

        // Турниры
        add_submenu_page( '', 'Добавить турнир', 'Добавить турнир', 'manage_options',
            'arsenal-tournament-add', array( $this->plugin->tournament_admin, 'render_tournament_form' ) );
        add_submenu_page( '', 'Редактировать турнир', 'Редактировать турнир', 'manage_options',
            'arsenal-tournament-edit', array( $this->plugin->tournament_admin, 'render_tournament_form' ) );

        // Лиги
        add_submenu_page( '', 'Добавить лигу', 'Добавить лигу', 'manage_options',
            'arsenal-league-add', array( $this, 'render_league_form' ) );
        add_submenu_page( '', 'Редактировать лигу', 'Редактировать лигу', 'manage_options',
            'arsenal-league-edit', array( $this, 'render_league_form' ) );

        // Корректировки таблицы
        add_submenu_page( '', 'Добавить корректировку', 'Добавить корректировку', 'manage_options',
            'arsenal-adjustment-add', array( $this, 'render_adjustment_form' ) );
        add_submenu_page( '', 'Редактировать корректировку', 'Редактировать корректировку', 'manage_options',
            'arsenal-adjustment-edit', array( $this, 'render_adjustment_form' ) );

        // Персонал
        add_submenu_page( '', 'Добавить сотрудника', 'Добавить сотрудника', 'manage_options',
            'arsenal-staff-add', array( $this, 'render_staff_add' ) );
        add_submenu_page( '', 'Редактировать сотрудника', 'Редактировать сотрудника', 'manage_options',
            'arsenal-staff-edit', array( $this, 'render_staff_edit' ) );
        add_submenu_page( '', 'Добавить должность', 'Добавить должность', 'manage_options',
            'arsenal-job-title-add', array( $this, 'render_job_title_add' ) );
        add_submenu_page( '', 'Редактировать должность', 'Редактировать должность', 'manage_options',
            'arsenal-job-title-edit', array( $this, 'render_job_title_edit' ) );

        // Руководство
        add_submenu_page( '', 'Добавить члена руководства', 'Добавить члена руководства', 'manage_options',
            'arsenal-management-add', array( $this, 'render_management_form' ) );

        // Матчи
        add_submenu_page( '', 'Добавить матч', 'Добавить матч', 'manage_options',
            'arsenal-match-add', array( $this, 'render_match_form' ) );
        add_submenu_page( '', 'Редактировать матч', 'Редактировать матч', 'manage_options',
            'arsenal-match-edit', array( $this, 'render_match_form' ) );
        add_submenu_page( '', 'События матча', 'События матча', 'manage_options',
            'arsenal-match-events', array( $this, 'render_match_events' ) );
        add_submenu_page( '', 'Составы матча', 'Составы матча', 'manage_options',
            'arsenal-match-lineups', array( $this, 'render_match_lineups' ) );

        // Игроки
        add_submenu_page( '', 'Добавить игрока', 'Добавить игрока', 'manage_options',
            'arsenal-player-add', array( $this, 'render_player_form' ) );
        add_submenu_page( '', 'Редактировать игрока', 'Редактировать игрока', 'manage_options',
            'arsenal-player-edit', array( $this, 'render_player_form' ) );
    }

    // ── Render: страницы-включения ─────────────────────────────────────────

    public function render_dashboard() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    public function render_icon_library() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/icon-library.php';
    }

    public function render_players_list() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/players-list.php';
    }

    public function render_player_form() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/player-form.php';
    }

    public function render_teams_list() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/teams-list.php';
    }

    public function render_contracts() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/contracts.php';
    }

    public function render_management_form() {
        include ARSENAL_TM_PLUGIN_DIR . 'admin/views/management-form.php';
    }

    // ── Render: делегирование к admin-классам ──────────────────────────────

    public function render_stadiums_list() {
        $this->plugin->stadium_admin->render_stadiums_list();
    }

    public function render_stadium_form() {
        $this->plugin->stadium_admin->render_stadium_form();
    }

    public function render_seasons_list() {
        $this->plugin->season_admin->render_seasons_list();
    }

    public function render_season_form() {
        $this->plugin->season_admin->render_season_form();
    }

    public function render_leagues_list() {
        $this->plugin->league_admin->render_leagues_list();
    }

    public function render_league_form() {
        $this->plugin->league_admin->render_league_form();
    }

    public function render_adjustments_list() {
        $this->plugin->adjustments_admin->render_adjustments_list();
    }

    public function render_adjustment_form() {
        $this->plugin->adjustments_admin->render_adjustment_form();
    }

    public function render_staff_list() {
        $this->plugin->staff_admin->render_staff_list();
    }

    public function render_staff_add() {
        $this->plugin->staff_admin->render_staff_add();
    }

    public function render_staff_edit() {
        $this->plugin->staff_admin->render_staff_edit();
    }

    public function render_job_title_add() {
        $this->plugin->staff_admin->render_job_title_add();
    }

    public function render_job_title_edit() {
        $this->plugin->staff_admin->render_job_title_edit();
    }

    public function render_sponsors_list() {
        $this->plugin->sponsors_admin->render_sponsors_list();
    }

    public function render_sponsor_form() {
        $this->plugin->sponsors_admin->render_sponsor_form();
    }

    public function render_player_stats_corrections() {
        $this->plugin->corrections_admin->render_page();
    }

    public function render_match_form() {
        $this->plugin->match_admin->render_match_form();
    }

    public function render_match_events() {
        $match_id = intval( $_GET['match_id'] ?? 0 );
        $this->plugin->match_events_admin->render_events_form( $match_id );
    }

    public function render_match_lineups() {
        $this->plugin->lineup_admin->render_lineups_form();
    }
}
