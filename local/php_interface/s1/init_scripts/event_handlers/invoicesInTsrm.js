document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const menuItem = document.querySelector('[data-id="bx_smart_invoice"]');
        if (menuItem) {
            const dataItem = JSON.parse(menuItem.getAttribute('data-item').replace(/&quot;/g, '"'));
            dataItem.URL = '/crm/type/31/details/';
            dataItem.IS_ACTIVE = true;
            
            menuItem.setAttribute('data-item', JSON.stringify(dataItem).replace(/"/g, '&quot;'));

            const linkSpan = menuItem.querySelector('.main-buttons-item-link');
            if (linkSpan && !linkSpan.querySelector('a')) {
                const linkHtml = `
                    <a class="main-buttons-item-link" href="/crm/type/31/details/">
                        <span class="main-buttons-item-icon"></span>
                        <span class="main-buttons-item-text">
                            <span class="main-buttons-item-drag-button" data-slider-ignore-autobinding="true"></span>
                            <span class="main-buttons-item-text-title">
                                <span class="main-buttons-item-text-box">Платежи<span class="main-buttons-item-menu-arrow"></span></span>
                            </span>
                            <span class="main-buttons-item-edit-button" data-slider-ignore-autobinding="true"></span>
                            <span class="main-buttons-item-text-marker"></span>
                        </span>
                        <span data-mib-counter-id="crm_custom_page_31_list" class="main-buttons-item-counter"></span>
                    </a>
                `;
                linkSpan.outerHTML = linkHtml;
            }

        }
    }, 1000);
});