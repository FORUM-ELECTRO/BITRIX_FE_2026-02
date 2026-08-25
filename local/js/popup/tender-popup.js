// Добавляем обработчик для отображения попапа при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded сработал');
    
    // Проверяем, есть ли данные для отображения попапа
    if (typeof window.tenderPopupData !== 'undefined' && window.tenderPopupData !== null) {
        console.log('Данные для попапа найдены:', window.tenderPopupData);
        // Проверяем, доступен ли BX (ядро Битрикс)
        if (typeof BX !== 'undefined') {
            console.log('BX доступен, пытаемся показать попап');
            showTenderPopup(window.tenderPopupData.deal_id, window.tenderPopupData.title);
        } else {
            console.log('BX не доступен, ждем загрузки BX');
            // Попробуем дождаться загрузки BX
            waitForBX();
        }
    } else {
        console.log('Данные для попапа отсутствуют');
    }
});

function waitForBX() {
    console.log('Ожидаем загрузки BX...');
    if (typeof BX !== 'undefined') {
        console.log('BX загружен, показываем попап');
        showTenderPopup(window.tenderPopupData.deal_id, window.tenderPopupData.title);
    } else {
        // Повторяем через 1 секунду
        setTimeout(waitForBX, 1000);
    }
}

function showTenderPopup(dealId, dealTitle) {
    console.log('Показываем попап для сделки:', dealId, dealTitle);
    try {
        var html = '<div style="padding:15px;"><p><b>' + dealTitle + '</b> (ID: ' + dealId + ')</p><p>Обновить дату процедуры?</p><input type="datetime-local" id="mydt" style="width:100%;padding:8px;border:1px solid #ccc;border-radius:4px;"></div>';
        var popup = new BX.PopupWindow('tender-time-popup', null, {
            content: html,
            buttons: [new BX.PopupWindowButton({
                text: 'OK',
                className: 'popup-window-button-accept',
                events: {
                    click: function() {
                        var val = document.getElementById('mydt').value;
                        if (val) {
                            BX.ajax.runAction('crm.api.deal.update', {
                                data: { id: dealId, fields: { 'UF_CRM_TENDER_DATE_END_ROBOT': val } }
                            }).then(function() {
                                alert('Дата обновлена!');
                                popup.close();
                            }).catch(function() {
                                alert('Ошибка');
                            });
                        } else {
                            popup.close();
                        }
                    }
                }
            })],
            closeIcon: {right: '12px', top: '12px'},
            titleBar: 'Обновление даты процедуры'
        });
        popup.show();
        console.log('Попап показан успешно');
    } catch (error) {
        console.error('Ошибка при показе попапа:', error);
    }
}