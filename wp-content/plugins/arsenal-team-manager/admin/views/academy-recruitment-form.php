<?php
/**
 * Админ форма управления страницей набора в академию
 * По аналогии со stadium-form.php, но для Academy Recruitment
 *
 * @package Arsenal
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title = 'Управление страницей набора в академию';
$form_action = 'arsenal_update_academy_recruitment';
?>

<div class="wrap">
	<div class="academy-recruitment-form-wrapper">
		<h1><?php echo esc_html( $page_title ); ?></h1>

		<!-- Сообщения об ошибке/успехе -->
		<?php if ( isset( $_GET['saved'] ) && $_GET['saved'] == 1 ) : ?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Данные успешно сохранены!', 'arsenal-team-manager' ); ?></p>
			</div>
		<?php endif; ?>

		<?php if ( isset( $_GET['error'] ) && $_GET['error'] == 1 ) : ?>
			<div class="notice notice-error is-dismissible">
				<p><?php esc_html_e( 'Произошла ошибка при сохранении!', 'arsenal-team-manager' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="academy-recruitment-form">

			<input type="hidden" name="action" value="<?php echo esc_attr( $form_action ); ?>">
			<?php wp_nonce_field( 'arsenal_academy_recruitment_form', 'arsenal_academy_nonce' ); ?>
		
		<!-- Динамически загружаем иконки из sprite.svg -->
		<script>
		<?php
		// Читаем sprite.svg и извлекаем все иконки
		$sprite_path = get_template_directory() . '/assets/images/sprite.svg';
		if ( file_exists( $sprite_path ) ) {
			$sprite_content = file_get_contents( $sprite_path );
			
			// Извлекаем все symbol id с помощью regex
			preg_match_all( '/<symbol\s+id="([^"]+)"[^>]*data-name="([^"]+)"/', $sprite_content, $matches );
			
			$icons = array();
			if ( ! empty( $matches[1] ) ) {
				for ( $i = 0; $i < count( $matches[1] ); $i++ ) {
					$icons[] = array(
						'id'   => $matches[1][$i],
						'name' => $matches[2][$i],
					);
				}
			}
		}
		?>
		const ACADEMY_FORM_ICONS = <?php echo wp_json_encode( $icons ?? array() ); ?>;
		</script>
			<!-- Hero секция -->
			<div class="academy-recruitment-section academy-recruitment-full">
				<div class="section-header">
					🎯 Hero секция
				</div>

				<div class="form-group">
					<label for="hero_title">Заголовок</label>
					<input type="text" name="hero_title" id="hero_title" 
						   value="<?php echo isset( $data['hero_data']['title'] ) ? esc_attr( $data['hero_data']['title'] ) : ''; ?>"
						   placeholder="Набор в академию">
				</div>

				<div class="form-group">
					<label for="hero_description">Описание</label>
					<textarea name="hero_description" id="hero_description" rows="3"
							  placeholder="Опишите программу набора"><?php echo isset( $data['hero_data']['description'] ) ? esc_textarea( $data['hero_data']['description'] ) : ''; ?></textarea>
				</div>

			<input type="hidden" name="hero_data" id="hero_data" class="hidden-field">
			<!-- Преимущества (Why Us) -->
			<div class="academy-recruitment-section academy-recruitment-full">
				<div class="section-header">
					⭐ Преимущества (Почему мы?)
				</div>

<div id="benefits-editor" class="editor-container">
				<div class="add-btn-container">
					<button type="button" id="add-benefit-btn" class="button">+ Добавить карточку</button>
				</div>
				<div id="benefits-list"></div>
			</div>

			<input type="hidden" name="benefits_data" id="benefits_data" class="hidden-field">

				<script>
				jQuery(function($) {
					function renderBenefits() {
						const benefitsData = <?php echo isset( $data['benefits_data'] ) ? wp_json_encode( $data['benefits_data'] ?? array() ) : '[]'; ?>;
						const list = $('#benefits-list');
						list.empty();

						benefitsData.forEach((item, idx) => {
							const html = $('<div class="benefit-item"></div>')
								.html(`
									<div class="benefit-item-row">
									<select class="benefit-icon">
										<option value="">Выбрать иконку</option>
										${ACADEMY_FORM_ICONS.map(icon => `<option value="${icon.id}" ${item.icon === icon.id ? 'selected' : ''}>${icon.name}</option>`).join('')}
									</select>
									<button type="button" class="academy-form-remove-btn">Удалить</button>
								</div>
							<div class="form-group">
									<input type="text" class="benefit-title" value="${(item.title || '').replace(/"/g, '&quot;')}" placeholder="Заголовок">
								</div>
								<div class="form-group">
									<textarea class="benefit-description" rows="3" placeholder="Описание">${(item.description || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
								</div>
							`)
							.on('input change', 'input, textarea, select', updateBenefitsJSON)
							.on('click', '.academy-form-remove-btn', function(e) {
								e.preventDefault();
								html.remove();
								updateBenefitsJSON();
							});
						list.append(html);
					});
				}

				function updateBenefitsJSON() {
					const benefits = [];
					$('#benefits-list .benefit-item').each(function() {
						benefits.push({
							icon: $(this).find('.benefit-icon').val(),
							title: $(this).find('.benefit-title').val(),
							description: $(this).find('.benefit-description').val(),
						});
					});
					$('#benefits_data').val(JSON.stringify(benefits, null, 2));
				}

				$('#add-benefit-btn').on('click', function(e) {
					e.preventDefault();
					const list = $('#benefits-list');
					const html = $('<div class="benefit-item"></div>')
						.html(`
							<div class="benefit-item-row">
							<select class="benefit-icon">
								<option value="">Выбрать иконку</option>
								${ACADEMY_FORM_ICONS.map(icon => `<option value="${icon.id}">${icon.name}</option>`).join('')}
							</select>
								<button type="button" class="academy-form-remove-btn">Удалить</button>
							</div>
							<div class="form-group">
								<input type="text" class="benefit-title" placeholder="Заголовок">
							</div>
							<div class="form-group">
								<textarea class="benefit-description" rows="3" placeholder="Описание"></textarea>
							</div>
						`)
						.on('input change', 'input, textarea, select', updateBenefitsJSON)
						.on('click', '.academy-form-remove-btn', function(e) {
							e.preventDefault();
							html.remove();
							updateBenefitsJSON();
						});
					list.append(html);
					updateBenefitsJSON();
					return false;
				});

				renderBenefits();
				});
				</script>
			</div>

			<!-- Возрастные группы -->
			<div class="academy-recruitment-section academy-recruitment-full">
				<div class="section-header">
					👥 Возрастные группы
				</div>

<div id="age-groups-editor" class="editor-container">
				<div class="add-btn-container">
					<button type="button" id="add-age-group-btn" class="button">+ Добавить группу</button>
				</div>
				<div id="age-groups-list"></div>
			</div>

			<input type="hidden" name="age_groups_data" id="age_groups_data" class="hidden-field">

				<script>
				jQuery(function($) {
					function renderAgeGroups() {
						const ageGroupsData = <?php echo isset( $data['age_groups_data'] ) ? wp_json_encode( $data['age_groups_data'] ?? array() ) : '[]'; ?>;
						const list = $('#age-groups-list');
						list.empty();

						ageGroupsData.forEach((item, idx) => {
							const html = $('<div class="age-group-item"></div>')
								.html(`
									<div class="age-group-item-header">
										<input type="text" class="age-group-name" value="${(item.name || '').replace(/"/g, '&quot;')}" placeholder="U-9">
										<button type="button" class="academy-form-remove-btn">Удалить</button>
									</div>
									<div class="age-group-item-row">
										<input type="text" class="age-group-range" value="${(item.age_range || '').replace(/"/g, '&quot;')}" placeholder="8-9 лет">
										<input type="text" class="age-group-years" value="${(item.birth_years || '').replace(/"/g, '&quot;')}" placeholder="2016-2017">
									</div>
									<div class="form-group">
										<input type="text" class="age-group-schedule" value="${(item.schedule || '').replace(/"/g, '&quot;')}" placeholder="Пн, Ср, Пт: 16:00-17:30">
									</div>
									<div class="age-group-item-row">
										<input type="number" class="age-group-spots" value="${item.spots_available || 0}" placeholder="Количество мест" min="0">
										<select class="age-group-status">
											<option value="normal" ${item.spots_status === 'normal' ? 'selected' : ''}>Обычный</option>
											<option value="warning" ${item.spots_status === 'warning' ? 'selected' : ''}>Внимание</option>
											<option value="danger" ${item.spots_status === 'danger' ? 'selected' : ''}>Критический</option>
										</select>
									</div>
								`)
								.on('input change', 'input, select', updateAgeGroupsJSON)
								.on('click', '.academy-form-remove-btn', function(e) {
									e.preventDefault();
									html.remove();
									updateAgeGroupsJSON();
								});
							list.append(html);
						});
					}

					function updateAgeGroupsJSON() {
						const ageGroups = [];
						$('#age-groups-list .age-group-item').each(function() {
							ageGroups.push({
								name: $(this).find('.age-group-name').val(),
								age_range: $(this).find('.age-group-range').val(),
								birth_years: $(this).find('.age-group-years').val(),
								schedule: $(this).find('.age-group-schedule').val(),
								spots_available: parseInt($(this).find('.age-group-spots').val()) || 0,
								spots_status: $(this).find('.age-group-status').val(),
							});
						});
						$('#age_groups_data').val(JSON.stringify(ageGroups, null, 2));
					}

					$('#add-age-group-btn').on('click', function(e) {
						e.preventDefault();
						const list = $('#age-groups-list');
						const html = $('<div class="age-group-item"></div>')
							.html(`
								<div class="age-group-item-header">
									<input type="text" class="age-group-name" placeholder="U-9">
									<button type="button" class="academy-form-remove-btn">Удалить</button>
								</div>
								<div class="age-group-item-row">
									<input type="text" class="age-group-range" placeholder="8-9 лет">
									<input type="text" class="age-group-years" placeholder="2016-2017">
								</div>
								<div class="form-group">
									<input type="text" class="age-group-schedule" placeholder="Пн, Ср, Пт: 16:00-17:30">
								</div>
								<div class="age-group-item-row">
									<input type="number" class="age-group-spots" placeholder="Количество мест" min="0">
									<select class="age-group-status">
										<option value="normal">Обычный</option>
										<option value="warning">Внимание</option>
										<option value="danger">Критический</option>
									</select>
								</div>
							`)
							.on('input change', 'input, select', updateAgeGroupsJSON)
							.on('click', '.academy-form-remove-btn', function(e) {
								e.preventDefault();
								html.remove();
								updateAgeGroupsJSON();
							});
						list.append(html);
						updateAgeGroupsJSON();
						return false;
					});

					renderAgeGroups();
				});
				</script>
			</div>

			<!-- Необходимые документы -->
			<div class="academy-recruitment-section">
				<div class="section-header">
					📄 Необходимые документы
				</div>

<div id="documents-editor" class="editor-container">
				<div class="add-btn-container">
					<button type="button" id="add-document-btn" class="button">+ Добавить документ</button>
				</div>
				<div id="documents-list"></div>
			</div>

			<div class="form-group">
				<label for="documents_notice">Примечание</label>
				<textarea name="documents_notice" id="documents_notice" rows="3"><?php echo isset( $data['documents_data']['notice'] ) ? esc_textarea( $data['documents_data']['notice'] ) : ''; ?></textarea>
			</div>

			<input type="hidden" name="documents_data" id="documents_data" class="hidden-field">

				<script>
				jQuery(function($) {
					function renderDocuments() {
						const docsData = <?php echo isset( $data['documents_data']['items'] ) ? wp_json_encode( $data['documents_data']['items'] ?? array() ) : '[]'; ?>;
						const list = $('#documents-list');
						list.empty();

						docsData.forEach((item, idx) => {
							const html = $('<div class="document-item"></div>')
								.html(`
									<div class="document-item-row">
										<input type="text" class="document-text" value="${(item.text || '').replace(/"/g, '&quot;')}" placeholder="Описание документа">
										<button type="button" class="academy-form-remove-btn">Удалить</button>
									</div>
								`)
								.on('input change', 'input', updateDocumentsJSON)
								.on('click', '.academy-form-remove-btn', function(e) {
									e.preventDefault();
									html.remove();
									updateDocumentsJSON();
								});
							list.append(html);
						});
					}

					function updateDocumentsJSON() {
						const documents = [];
						$('#documents-list .document-item').each(function() {
							documents.push({
								text: $(this).find('.document-text').val(),
							});
						});
						$('#documents_data').val(JSON.stringify({ items: documents, notice: $('#documents_notice').val() }, null, 2));
					}

					$('#add-document-btn').on('click', function(e) {
						e.preventDefault();
						const list = $('#documents-list');
						const html = $('<div class="document-item"></div>')
							.html(`
								<div class="document-item-row">
									<input type="text" class="document-text" placeholder="Описание документа">
									<button type="button" class="academy-form-remove-btn">Удалить</button>
								</div>
							`)
							.on('input change', 'input', updateDocumentsJSON)
							.on('click', '.academy-form-remove-btn', function(e) {
								e.preventDefault();
								html.remove();
								updateDocumentsJSON();
							});
						list.append(html);
						updateDocumentsJSON();
						return false;
					});

					$('#documents_notice').on('input', updateDocumentsJSON);
					renderDocuments();
				});
				</script>
			</div>

			<!-- Расписание просмотров -->
			<div class="academy-recruitment-section">
				<div class="section-header">
					📅 Расписание просмотров
				</div>

<div id="schedule-editor" class="editor-container">
				<div class="add-btn-container">
					<button type="button" id="add-schedule-item-btn" class="button">+ Добавить пункт</button>
				</div>
				<div id="schedule-list"></div>
			</div>

			<div class="form-group">
				<label for="schedule_notice">Примечание</label>
				<textarea name="schedule_notice" id="schedule_notice" rows="3"><?php echo isset( $data['schedule_data']['notice'] ) ? esc_textarea( $data['schedule_data']['notice'] ) : ''; ?></textarea>
			</div>

			<input type="hidden" name="schedule_data" id="schedule_data" class="hidden-field">

				<script>
				jQuery(function($) {
					function renderSchedule() {
						const scheduleData = <?php echo isset( $data['schedule_data']['items'] ) ? wp_json_encode( $data['schedule_data']['items'] ?? array() ) : '[]'; ?>;
						const list = $('#schedule-list');
						list.empty();

						scheduleData.forEach((item, idx) => {
							const html = $('<div class="schedule-item-wrapper"></div>')
								.html(`
									<div class="schedule-item-row">
									<select class="schedule-icon">
										<option value="">Выбрать иконку</option>
										${ACADEMY_FORM_ICONS.map(icon => `<option value="${icon.id}" ${item.icon === icon.id ? 'selected' : ''}>${icon.name}</option>`).join('')}
									</select>
									<button type="button" class="academy-form-remove-btn">Удалить</button>
								</div>
								<div class="schedule-item-row">
									<input type="text" class="schedule-heading" value="${(item.heading || '').replace(/"/g, '&quot;')}" placeholder="Каждую субботу">
									<input type="text" class="schedule-text" value="${(item.text || '').replace(/"/g, '&quot;')}" placeholder="10:00 - 12:00">
								</div>
							`)
							.on('input change', 'input, select', updateScheduleJSON)
							.on('click', '.academy-form-remove-btn', function(e) {
								e.preventDefault();
								html.remove();
								updateScheduleJSON();
							});
						list.append(html);
					});
				}

				function updateScheduleJSON() {
					const schedule = [];
					$('#schedule-list .schedule-item-wrapper').each(function() {
						schedule.push({
							icon: $(this).find('.schedule-icon').val(),
							heading: $(this).find('.schedule-heading').val(),
							text: $(this).find('.schedule-text').val(),
						});
					});
					$('#schedule_data').val(JSON.stringify({ items: schedule, notice: $('#schedule_notice').val() }, null, 2));
				}

				$('#add-schedule-item-btn').on('click', function(e) {
					e.preventDefault();
					const list = $('#schedule-list');
					const html = $('<div class="schedule-item-wrapper"></div>')
						.html(`
							<div class="schedule-item-row">
							<select class="schedule-icon">
								<option value="">Выбрать иконку</option>
								${ACADEMY_FORM_ICONS.map(icon => `<option value="${icon.id}">${icon.name}</option>`).join('')}
							</select>
							<button type="button" class="academy-form-remove-btn">Удалить</button>
						</div>
						<div class="schedule-item-row">
							<input type="text" class="schedule-heading" placeholder="Каждую субботу">
							<input type="text" class="schedule-text" placeholder="10:00 - 12:00">
						</div>
					`)
						.on('input change', 'input, select', updateScheduleJSON)
						.on('click', '.academy-form-remove-btn', function(e) {
							e.preventDefault();
							html.remove();
							updateScheduleJSON();
						});
					list.append(html);
					updateScheduleJSON();
					return false;
				});

				$('#schedule_notice').on('input', updateScheduleJSON);
				renderSchedule();
				});
				</script>
			</div>

			<!-- Контакты и Как добраться (объединённо) -->
			<div class="academy-recruitment-section academy-recruitment-full">
				<div class="section-header">
					📞 Контакты и как добраться
				</div>

				<div class="form-row">
					<div class="form-group">
						<label for="contacts_address">Адрес</label>
						<input type="text" name="contacts_address" id="contacts_address"
							   value="<?php echo isset( $data['contacts_data']['address'] ) ? esc_attr( $data['contacts_data']['address'] ) : ''; ?>"
							   placeholder="ул. Спортивная, 2">
					</div>

					<div class="form-group">
						<label for="contacts_phone">Телефон</label>
						<input type="text" name="contacts_phone" id="contacts_phone"
							   value="<?php echo isset( $data['contacts_data']['phone'] ) ? esc_attr( $data['contacts_data']['phone'] ) : ''; ?>"
							   placeholder="+375 (17) 123-45-70">
					</div>

					<div class="form-group">
						<label for="contacts_email">Email</label>
						<input type="email" name="contacts_email" id="contacts_email"
							   value="<?php echo isset( $data['contacts_data']['email'] ) ? esc_attr( $data['contacts_data']['email'] ) : ''; ?>"
							   placeholder="academy@arsenal-dzr.by">
					</div>
				</div>

				<!-- Рабочие дни и время -->
			<div class="subsection">
				<h4 class="subsection-title">Рабочие дни и время</h4>

				<div id="working-schedule-editor">
					<div id="working-schedule-items" class="working-schedule-items">
							<?php 
							$schedule = isset( $data['contacts_data']['working_schedule'] ) && is_array( $data['contacts_data']['working_schedule'] ) ? $data['contacts_data']['working_schedule'] : array();
							foreach ( $schedule as $index => $day ) : 
							?>
						<div class="schedule-day-item" data-index="<?php echo $index; ?>">
							<input type="text" class="schedule-day" value="<?php echo esc_attr( $day['day'] ?? '' ); ?>" placeholder="Понедельник">
							<input type="text" class="schedule-time" value="<?php echo esc_attr( $day['time'] ?? '' ); ?>" placeholder="09:00 - 18:00">
							<button type="button" class="remove-schedule-item">✕</button>
						</div>
						<?php endforeach; ?>
					</div>

					<button type="button" id="add-schedule-item" class="add-schedule-day-btn">+ Добавить день</button>
					<input type="hidden" id="working-schedule-json" name="working_schedule_json" value="<?php echo isset( $data['contacts_data']['working_schedule'] ) ? esc_attr( json_encode( $data['contacts_data']['working_schedule'] ) ) : '[]'; ?>">
				</div>

				<!-- Карты -->
			<div class="subsection">
				<h4 class="subsection-title">Ссылки на карты</h4>
					<div class="form-row">
						<div class="form-group">
							<label for="google_maps_url">Google Maps URL</label>
							<input type="url" name="google_maps_url" id="google_maps_url"
								   value="<?php echo isset( $data['contacts_data']['google_maps_url'] ) ? esc_attr( $data['contacts_data']['google_maps_url'] ) : ''; ?>"
								   placeholder="https://maps.google.com/?q=address">
						</div>

						<div class="form-group">
							<label for="yandex_maps_url">Яндекс Maps URL</label>
							<input type="url" name="yandex_maps_url" id="yandex_maps_url"
								   value="<?php echo isset( $data['contacts_data']['yandex_maps_url'] ) ? esc_attr( $data['contacts_data']['yandex_maps_url'] ) : ''; ?>"
								   placeholder="https://yandex.by/maps/?text=address">
						</div>
					</div>
				</div>

				<!-- Директор -->
			<div class="subsection-alt">
				<h4 class="subsection-title">Директор СДЮШ</h4>
					<div class="form-row">
						<div class="form-group">
							<label for="director_name">Имя директора</label>
							<input type="text" name="director_name" id="director_name"
								   value="<?php echo isset( $data['contacts_data']['director']['name'] ) ? esc_attr( $data['contacts_data']['director']['name'] ) : ''; ?>"
								   placeholder="Петр Иванович Кузнецов">
						</div>

						<div class="form-group">
							<label for="director_role">Должность/Стаж</label>
							<input type="text" name="director_role" id="director_role"
								   value="<?php echo isset( $data['contacts_data']['director']['role'] ) ? esc_attr( $data['contacts_data']['director']['role'] ) : ''; ?>"
								   placeholder="Директор с 2010 года">
						</div>
					</div>

					<div class="form-row">
						<div class="form-group">
							<label for="director_phone">Телефон директора</label>
							<input type="text" name="director_phone" id="director_phone"
								   value="<?php echo isset( $data['contacts_data']['director']['contacts'][0]['value'] ) ? esc_attr( $data['contacts_data']['director']['contacts'][0]['value'] ) : ''; ?>"
								   placeholder="+375 (17) 123-45-71">
						</div>

						<div class="form-group">
							<label for="director_email">Email директора</label>
							<input type="email" name="director_email" id="director_email"
								   value="<?php echo isset( $data['contacts_data']['director']['contacts'][1]['value'] ) ? esc_attr( $data['contacts_data']['director']['contacts'][1]['value'] ) : ''; ?>"
								   placeholder="kuznetsov@arsenal-dzr.by">
						</div>
					</div>
				</div>

				<!-- Маршруты/Направления -->
			<div class="subsection">
				<h4 class="subsection-title">Маршруты проезда</h4>
				<div id="directions-editor" class="editor-container">
					<div class="add-btn-container">
							<button type="button" id="add-direction-btn" class="button">+ Добавить маршрут</button>
						</div>
						<div id="directions-list"></div>
					</div>
				</div>

				<!-- Социальные сети -->
			<div class="subsection">
				<h4 class="subsection-title">Социальные сети</h4>
				<div id="social-editor" class="editor-container">
					<div class="add-btn-container">
							<button type="button" id="add-social-btn" class="button">+ Добавить соцсеть</button>
						</div>
						<div id="social-list"></div>
					</div>
				</div>

			<input type="hidden" name="contacts_data" id="contacts_data" class="hidden-field">
			<input type="hidden" name="directions_data" id="directions_data" class="hidden-field">
			<input type="hidden" name="social_data" id="social_data" class="hidden-field">

				<script>
				jQuery(function($) {
					// === UPDATE HERO JSON ===
					function updateHeroJSON() {
						const heroData = {
							title: $('#hero_title').val(),
							description: $('#hero_description').val(),
							buttons: [
								{ text: 'Подать заявку', action: 'apply', style: 'primary' },
								{ text: 'Контакты', action: '#contacts', style: 'secondary' }
							]
						};
						$('#hero_data').val(JSON.stringify(heroData, null, 2));
					}
					
					$(document).on('input change', '#hero_title, #hero_description', function() {
						updateHeroJSON();
					});

					// === UPDATE CONTACTS JSON ===
					function updateContactsJSON() {
						const contactsData = {
							address: $('#contacts_address').val(),
							phone: $('#contacts_phone').val(),
							email: $('#contacts_email').val(),
							working_schedule: [],
							google_maps_url: $('#google_maps_url').val(),
							yandex_maps_url: $('#yandex_maps_url').val(),
							director: {
								title: 'Директор СДЮШ',
								name: $('#director_name').val(),
								role: $('#director_role').val(),
								contacts: [
									{
										type: 'phone',
										value: $('#director_phone').val()
									},
									{
										type: 'email',
										value: $('#director_email').val()
									}
								]
							}
						};
						
						// Собрать рабочее расписание из скрытого поля
						if ($('#working-schedule-json').val()) {
							try {
								contactsData.working_schedule = JSON.parse($('#working-schedule-json').val());
							} catch (e) {
								contactsData.working_schedule = [];
							}
						}
						
						$('#contacts_data').val(JSON.stringify(contactsData, null, 2));
					}
					
					// Вызвать при любых изменениях контактов
					$(document).on('input change', '#contacts_address, #contacts_phone, #contacts_email, #google_maps_url, #yandex_maps_url, #director_name, #director_role, #director_phone, #director_email', function() {
						updateContactsJSON();
					});

					// === DIRECTIONS ===
					function renderDirections() {
						const directionsData = <?php echo isset( $data['directions_data']['items'] ) ? wp_json_encode( $data['directions_data']['items'] ?? array() ) : '[]'; ?>;
						const list = $('#directions-list');
						list.empty();

						directionsData.forEach((item, idx) => {
							let iconOptions = '';
							ACADEMY_FORM_ICONS.forEach(icon => {
								const selected = item.icon === icon.id ? 'selected' : '';
								iconOptions += '<option value="' + icon.id + '" ' + selected + '>' + icon.name + '</option>';
							});

							const html = $('<div class="direction-item"></div>')
								.html(`
									<div class="academy-direction-item-row">
										<select class="direction-icon">
											<option value="">Выбрать иконку</option>
											` + iconOptions + `
										</select>
										<input type="text" class="direction-transport" placeholder="Транспорт" value="${(item.transport || '').replace(/"/g, '&quot;')}">
										<input type="text" class="direction-route" placeholder="Маршрут" value="${(item.route || '').replace(/"/g, '&quot;')}">
										<input type="text" class="direction-time" placeholder="Время" value="${(item.time || '').replace(/"/g, '&quot;')}">
										<button class="button academy-recruitment-remove-btn" type="button">✕ Удалить</button>
									</div>
								`)
								.on('change', 'select, input', updateDirectionsJSON)
								.on('click', '.academy-recruitment-remove-btn', function() {
									html.remove();
									updateDirectionsJSON();
								});
							list.append(html);
						});
					}

					function updateDirectionsJSON() {
						const items = [];
						$('#directions-list .direction-item').each(function() {
							const icon = $(this).find('.direction-icon').val();
							const transport = $(this).find('.direction-transport').val();
							const route = $(this).find('.direction-route').val();
							const time = $(this).find('.direction-time').val();
							if (transport || route || time) {
								items.push({ icon, transport, route, time });
							}
						});
						$('#directions_data').val(JSON.stringify({ items }, null, 2));
					}

					$('#add-direction-btn').on('click', function(e) {
						e.preventDefault();
						const list = $('#directions-list');
						
						let iconOptions = '';
						ACADEMY_FORM_ICONS.forEach(icon => {
							iconOptions += '<option value="' + icon.id + '">' + icon.name + '</option>';
						});

						const html = $('<div class="direction-item"></div>')
							.html(`
								<div class="academy-direction-item-row">
									<select class="direction-icon">
										<option value="">Выбрать иконку</option>
										` + iconOptions + `
									</select>
									<input type="text" class="direction-transport" placeholder="Транспорт">
									<input type="text" class="direction-route" placeholder="Маршрут">
									<input type="text" class="direction-time" placeholder="Время">
									<button class="button academy-recruitment-remove-btn" type="button">✕ Удалить</button>
								</div>
							`)
							.on('change', 'select, input', updateDirectionsJSON)
							.on('click', '.academy-recruitment-remove-btn', function() {
								html.remove();
								updateDirectionsJSON();
							});
						list.append(html);
						return false;
					});

					renderDirections();

					// === SOCIAL MEDIA ===
					function renderSocial() {
						const socialData = <?php echo isset( $data['social_data'] ) ? wp_json_encode( $data['social_data'] ?? array() ) : '[]'; ?>;
						const list = $('#social-list');
						list.empty();

						socialData.forEach((item, idx) => {
							let iconOptions = '';
							ACADEMY_FORM_ICONS.forEach(icon => {
								const selected = item.icon === icon.id ? 'selected' : '';
								iconOptions += '<option value="' + icon.id + '" ' + selected + '>' + icon.name + '</option>';
							});

							const html = $('<div class="social-item"></div>')
								.html(`
									<div class="social-item-row">
										<select class="social-icon">
											<option value="">Выбрать иконку</option>
											` + iconOptions + `
										</select>
										<input type="url" class="social-url" placeholder="URL" value="${(item.url || '').replace(/"/g, '&quot;')}">
										<button class="button academy-recruitment-remove-btn" type="button">✕ Удалить</button>
									</div>
								`)
								.on('input change', 'input, select', updateSocialJSON)
								.on('click', '.academy-recruitment-remove-btn', function() {
									html.remove();
									updateSocialJSON();
								});
							list.append(html);
						});
						
						// ВАЖНО: вызываем после загрузки чтобы заполнить поле social_data
						updateSocialJSON();
					}

					function updateSocialJSON() {
						const items = [];
						$('#social-list .social-item').each(function() {
							const icon = $(this).find('.social-icon').val();
							const url = $(this).find('.social-url').val();
							if (icon || url) {
								items.push({ icon, url });
							}
						});
						// ВАЖНО: ВСЕГДА сохраняем массив, даже если он пустой
						$('#social_data').val(JSON.stringify(items, null, 2));
					}

					$('#add-social-btn').on('click', function(e) {
						e.preventDefault();
						const list = $('#social-list');
						
						let iconOptions = '';
						ACADEMY_FORM_ICONS.forEach(icon => {
							iconOptions += '<option value="' + icon.id + '">' + icon.name + '</option>';
						});

						const html = $('<div class="social-item"></div>')
							.html(`
								<div class="social-item-row">
									<select class="social-icon">
										<option value="">Выбрать иконку</option>
										` + iconOptions + `
									</select>

									<input type="url" class="social-url" placeholder="URL профиля">
									<button class="button academy-recruitment-remove-btn" type="button">✕ Удалить</button>
								</div>
							`)
							.on('input change', 'input, select', updateSocialJSON)
							.on('click', '.academy-recruitment-remove-btn', function() {
								html.remove();
								updateSocialJSON();
							});
						list.append(html);
						updateSocialJSON();
						return false;
					});

					renderSocial();
				});
				</script>
			</div>

			<!-- FAQ -->
			<div class="academy-recruitment-section academy-recruitment-full">
				<div class="section-header">
					❓ Частые вопросы (FAQ)
				</div>

<div id="faq-editor" class="editor-container">
				<div class="add-btn-container">
					<button type="button" id="add-faq-btn" class="button">+ Добавить вопрос</button>
				</div>
				<div id="faq-list"></div>
			</div>

			<input type="hidden" name="faq_data" id="faq_data" class="hidden-field">

				<script>
				jQuery(function($) {
					function renderFAQ() {
						const faqData = <?php echo isset( $data['faq_data'] ) ? wp_json_encode( $data['faq_data'] ?? array() ) : '[]'; ?>;
						const list = $('#faq-list');
						list.empty();

						faqData.forEach((item, idx) => {
							const html = $('<div class="faq-item"></div>')
								.html(`
									<div class="faq-item-header">
										<input type="text" class="faq-question" value="${(item.question || '').replace(/"/g, '&quot;')}" placeholder="Вопрос">
										<button type="button" class="academy-form-remove-btn">Удалить</button>
									</div>
									<div class="form-group">
										<textarea class="faq-answer" rows="3" placeholder="Ответ">${(item.answer || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
									</div>
								`)
								.on('input change', 'input, textarea', updateFAQJSON)
								.on('click', '.academy-form-remove-btn', function(e) {
									e.preventDefault();
									html.remove();
									updateFAQJSON();
								});
							list.append(html);
						});
					}

					function updateFAQJSON() {
						const faq = [];
						$('#faq-list .faq-item').each(function() {
							faq.push({
								question: $(this).find('.faq-question').val(),
								answer: $(this).find('.faq-answer').val(),
							});
						});
						$('#faq_data').val(JSON.stringify(faq, null, 2));
					}

					$('#add-faq-btn').on('click', function(e) {
						e.preventDefault();
						const list = $('#faq-list');
						const html = $('<div class="faq-item"></div>')
							.html(`
								<div class="faq-item-header">
									<input type="text" class="faq-question" placeholder="Вопрос">
									<button type="button" class="academy-form-remove-btn">Удалить</button>
								</div>
								<div class="form-group">
									<textarea class="faq-answer" rows="3" placeholder="Ответ"></textarea>
								</div>
							`)
							.on('input change', 'input, textarea', updateFAQJSON)
							.on('click', '.academy-form-remove-btn', function(e) {
								e.preventDefault();
								html.remove();
								updateFAQJSON();
							});
						list.append(html);
						updateFAQJSON();
						return false;
					});

					renderFAQ();

					// ===== Управление рабочими днями и временем =====
					function updateWorkingScheduleJSON() {
						const schedule = [];
						$('#working-schedule-items .schedule-day-item').each(function() {
							schedule.push({
								day: $(this).find('.schedule-day').val(),
								time: $(this).find('.schedule-time').val(),
							});
						});
						$('#working-schedule-json').val(JSON.stringify(schedule, null, 2));
					}

					$('#add-schedule-item').on('click', function(e) {
						e.preventDefault();
						const container = $('#working-schedule-items');
						const index = container.children().length;
					const html = $('<div class="schedule-day-item" data-index="' + index + '"></div>')
						.html(`
							<input type="text" class="schedule-day" placeholder="Понедельник">
							<input type="text" class="schedule-time" placeholder="09:00 - 18:00">
							<button type="button" class="remove-schedule-item">✕</button>
							`)
							.on('input change', 'input', updateWorkingScheduleJSON)
							.on('click', '.remove-schedule-item', function(e) {
								e.preventDefault();
								html.remove();
								updateWorkingScheduleJSON();
							});
						container.append(html);
						updateWorkingScheduleJSON();
						return false;
					});
					$(document).on('input change', '#working-schedule-items input', updateWorkingScheduleJSON);
					
					// Инициализировать все JSON поля при загрузке страницы
					updateWorkingScheduleJSON();
					updateHeroJSON();
					updateContactsJSON();
				});

				</script>
			</div>

			<!-- Кнопки -->
			<div class="academy-recruitment-actions">
				<button type="submit" class="button button-primary">
					✓ Сохранить изменения
				</button>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=arsenal-academy-recruitment' ) ); ?>" class="button">
					← Назад
				</a>
			</div>
		</form>
	</div>
</div>
