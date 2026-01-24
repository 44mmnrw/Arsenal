<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package Arsenal
 */

wp_enqueue_style( 'arsenal-page-404', get_template_directory_uri() . '/assets/css/pages/page-404.css', array( 'arsenal-footer' ), wp_get_theme()->get( 'Version' ) );

get_header(); 
?>

<main id="primary" class="site-main page-404">
	<div class="not-found-container">
		<!-- Main 404 Content -->
		<div class="not-found-wrapper">
			<!-- Soccer Field with 404 -->
			<div class="soccer-field-container">
				<div class="soccer-field">
					<!-- Field striped background -->
					<div class="field-stripes"></div>
					
					<!-- Field elements -->
					<div class="field-elements">
						<!-- Center circle -->
						<div class="center-circle"></div>
						<div class="center-dot"></div>
						
						<!-- Side markings -->
						<div class="side-marking left"></div>
						<div class="side-marking right"></div>
						
						<!-- Center line -->
						<div class="center-line"></div>
						
						<!-- Soccer ball emoji -->
						<div class="ball-icon">⚽</div>
					</div>
					
					<!-- Large 404 text -->
					<div class="field-404">404</div>
					
					<!-- Red card -->
					<div class="red-card">
						<span class="card-text">404</span>
					</div>
				</div>
			</div>

			<!-- Text content -->
			<div class="not-found-content">
				<h1 class="not-found-title">Офсайд! Страница не найдена</h1>
				<p class="not-found-description">
					К сожалению, страница, которую вы ищете, ушла за пределы поля. Возможно, судья показал ей красную карточку, или она забила в свои ворота и удалилась.
				</p>

				<!-- Action buttons -->
				<div class="not-found-buttons">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-primary btn-home">
						<span class="btn-icon">🏠</span>
						<span class="btn-text">Вернуться на главную</span>
					</a>
					<button class="btn btn-secondary btn-back" onclick="window.history.back()">
						<span class="btn-icon">←</span>
						<span class="btn-text">Назад</span>
					</button>
				</div>
			</div>

			<!-- Quick links section -->
			<div class="popular-sections">
				<div class="popular-sections-header">
					<span class="header-icon">🔍</span>
					<h2>Популярные разделы</h2>
				</div>
				
				<div class="quick-links">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="quick-link">
						<div class="link-icon-wrapper">🏠</div>
						<span class="link-text">Главная</span>
					</a>
					<a href="<?php echo esc_url( home_url( '/squad' ) ); ?>" class="quick-link">
						<div class="link-icon-wrapper">👥</div>
						<span class="link-text">Команды</span>
					</a>
					<a href="<?php echo esc_url( home_url( '/calendar' ) ); ?>" class="quick-link">
						<div class="link-icon-wrapper">📅</div>
						<span class="link-text">Матчи</span>
					</a>
				</div>
			</div>

			<!-- Fun fact -->
			<div class="fun-fact" id="randomFact">
				<span class="fact-icon">⚽</span>
				<span class="fact-label">Факт:</span>
				<span class="fact-text" id="factText">Загрузка факта...</span>
			</div>
		</div>
	</div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const facts = [
		// Мировые факты
		"Бразилия — единственная сборная, которая играла на всех чемпионатах мира и при этом владеет рекордом по числу титулов — 5 побед на ЧМ.",
		"Название удара «пенальти» закрепилось после того, как ирландец Джон Пенальти предложил ввести 11‑метровый за грубую игру в штрафной.",
		"Идея жёлтых и красных карточек родилась, когда английский чиновник, вдохновившись светофором, искал универсальный «язык» для арбитров и игроков.",
		"На Мадагаскаре в 2002 году был матч с 149 голами, причём все — автоголы одной команды в знак протеста против судейства в предыдущей игре.",
		"Пеле за карьеру провёл 92 матча с хет‑триком, 31 матч с «покером» и 6 матчей, где забивал по 5 голов; однажды он забил 8 голов за игру.",
		"На ЧМ‑2002 турок Хакан Шюкюр забил гол за 10,89 секунды — это самый быстрый гол в истории чемпионатов мира.",
		"В 1964 году шотландец Томми Росс оформил хет‑трик за примерно 90 секунд — один из самых быстрых хет‑триков в истории.",
		"В 1978/79 «Перуджа» прошла сезон Серии А без единого поражения, но не стала чемпионом — из‑за огромного числа ничьих её обошёл «Милан».",
		"Английские тренеры в эпоху АПЛ (с 1992 года) ни разу не выигрывали чемпионат Англии — все титулы брали иностранные тренеры.",
		"В Шотландии в 1885 году «Арброт» обыграл «Бон Аккорд» 36:0 — это один из самых крупных официальных счётов в истории взрослого футбола.",
        "Красная карточка была впервые применена на чемпионате мира 1970 года",
		
		// Беларусские факты
		"Первый известный матч в Беларуси сыграли в 1911 году в Гомеле: «Первая гимназическая футбольная команда» против «Надежды».",
		"Первое официальное первенство БССР по футболу прошло в 1927 году, а сборная БССР начала выступать на спартакиадах в конце 1920‑х.",
		"Белорусская федерация футбола была создана ещё до распада СССР — в декабре 1989 года, а первый матч независимая сборная Беларуси сыграла в 1992‑м.",
		"Самая крупная победа сборной — 5:0 над Литвой (7 июня 1998), самое крупное поражение — 0:5 от Австрии (11 июня 2003).",
		"В начале 1990‑х чемпионат Беларуси был крайне хаотичным: маленькие клубы из райцентров брали медали, а крупные переживали развал.",
		"БАТЭ Борисов — главный рекордсмен страны: 15 чемпионских титулов, включая серию из 11 титулов подряд (2006–2016).",
		"Антирекорд принадлежит «Гомелю»: команда проиграла 12 матчей подряд в Высшей лиге в одном из сезонов 2000‑х.",
		"С 2008 по 2016 год в белорусской Высшей лиге был уникальный «голод на бомбардиров»: ни один нападающий не смог забить больше 16 голов.",
		"Полузащитник Юрий Кендыш четырежды становился чемпионом в трёх странах за три года: Литва, Беларусь, Молдова.",
		"В 2004 году вратарь БАТЭ Юрий Жевнов забил один из самых курьёзных голов года в Европе — ударом от своих ворот в Кубке УЕФА."
	];

	function showRandomFact() {
		const randomIndex = Math.floor(Math.random() * facts.length);
		const factText = document.getElementById('factText');
		if (factText) {
			factText.textContent = facts[randomIndex];
		}
	}

	// Показать случайный факт при загрузке
	showRandomFact();

	// Переключать факт при клике на контейнер
	const factContainer = document.getElementById('randomFact');
	if (factContainer) {
		factContainer.style.cursor = 'pointer';
		factContainer.addEventListener('click', showRandomFact);
	}
});
</script>

<?php get_footer(); ?>
?>
