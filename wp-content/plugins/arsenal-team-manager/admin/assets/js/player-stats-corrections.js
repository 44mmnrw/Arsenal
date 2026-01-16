/**
 * Arsenal Player Stats Corrections - JavaScript
 * Обработка AJAX запросов для корректировок статистики игроков
 */

// Сохранение новой корректировки
function arsenalSaveCorrection() {
    const form = document.getElementById('arsenal-correction-form');
    const playerId = document.getElementById('player_select').value;
    const tournamentId = document.getElementById('tournament_select').value;
    
    if ( ! playerId ) {
        alert('Пожалуйста, выберите игрока');
        return;
    }
    if ( ! tournamentId ) {
        alert('Пожалуйста, выберите турнир');
        return;
    }
    
    const nonce = document.querySelector('input[name="arsenal_correction_nonce_field"]').value;
    
    const data = new FormData();
    data.append('action', 'arsenal_save_correction');
    data.append('_wpnonce', nonce);
    data.append('player_id', playerId);
    data.append('tournament_id', tournamentId);
    data.append('season_id', document.getElementById('season_select').value || '');
    data.append('minutes_played_delta', document.getElementById('minutes_delta').value || '0');
    data.append('matches_played_delta', document.getElementById('matches_delta').value || '0');
    data.append('goals_delta', document.getElementById('goals_delta').value || '0');
    data.append('assists_delta', document.getElementById('assists_delta').value || '0');
    data.append('yellow_cards_delta', document.getElementById('yellow_delta').value || '0');
    data.append('red_cards_delta', document.getElementById('red_delta').value || '0');
    data.append('correction_reason', document.getElementById('reason').value || '');
    
    fetch(ajaxurl, {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(result => {
        if ( result.success ) {
            // Перенаправить на страницу с успешным сообщением
            const url = new URL(window.location);
            url.searchParams.set('message', 'created');
            window.location = url.toString();
        } else {
            alert('Ошибка: ' + (result.data || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        alert('Ошибка запроса: ' + error);
    });
}

// Применение корректировки к статистике
function arsenalApplyCorrection(correctionId) {
    if ( ! confirm('Вы уверены, что хотите применить эту корректировку к статистике?') ) {
        return;
    }
    
    const nonce = document.querySelector('input[name="arsenal_correction_nonce_field"]').value;
    
    const data = new FormData();
    data.append('action', 'arsenal_apply_correction');
    data.append('_wpnonce', nonce);
    data.append('correction_id', correctionId);
    
    fetch(ajaxurl, {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(result => {
        if ( result.success ) {
            const url = new URL(window.location);
            url.searchParams.set('message', 'applied');
            window.location = url.toString();
        } else {
            alert('Ошибка: ' + (result.data || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        alert('Ошибка запроса: ' + error);
    });
}

// Удаление корректировки
function arsenalDeleteCorrection(correctionId) {
    if ( ! confirm('Вы уверены, что хотите удалить эту корректировку?') ) {
        return;
    }
    
    const nonce = document.querySelector('input[name="arsenal_correction_nonce_field"]').value;
    
    const data = new FormData();
    data.append('action', 'arsenal_delete_correction');
    data.append('_wpnonce', nonce);
    data.append('correction_id', correctionId);
    
    fetch(ajaxurl, {
        method: 'POST',
        body: data
    })
    .then(response => response.json())
    .then(result => {
        if ( result.success ) {
            const url = new URL(window.location);
            url.searchParams.set('message', 'deleted');
            window.location = url.toString();
        } else {
            alert('Ошибка: ' + (result.data || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        alert('Ошибка запроса: ' + error);
    });
}

// Плавная прокрутка к форме
document.addEventListener('DOMContentLoaded', function() {
    const scrollLinks = document.querySelectorAll('.scroll-to-form');
    scrollLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);
            if ( target ) {
                target.scrollIntoView({ behavior: 'smooth' });
                target.focus();
            }
        });
    });
});
