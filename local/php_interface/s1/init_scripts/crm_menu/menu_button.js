(function() {
    'use strict';
    
    let crmButtonAdded = false;
    let crmLinks = [];

    function addExpandButtonToCRM() {
        if (crmButtonAdded) return;
        
        const crmMenu = document.querySelector('#bx_left_menu_menu_crm_favorite');
        if (crmMenu && !crmMenu.hasAttribute('data-expand-added')) {
            crmMenu.setAttribute('data-expand-added', 'true');
            crmButtonAdded = true;
            
            const originalOnclick = crmMenu.onclick;
            const originalHref = crmMenu.href;
            
            const menuContainer = document.createElement('div');
            menuContainer.style.cssText = `
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                cursor: pointer;
                position: relative;
            `;
            
            const contentLeft = document.createElement('div');
            contentLeft.style.cssText = `
                display: flex;
                align-items: center;
                flex: 1;
            `;
            contentLeft.innerHTML = crmMenu.innerHTML;
            
            if (originalOnclick) contentLeft.onclick = originalOnclick;
            if (originalHref) {
                contentLeft.style.cursor = 'pointer';
                contentLeft.onclick = function(e) {
                    if (originalOnclick) originalOnclick.call(this, e);
                    else window.location.href = originalHref;
                };
            }
            
            const expandButton = document.createElement('span');
            expandButton.textContent = 'РАЗВЕРНУТЬ';
            expandButton.style.cssText = `
                display: none;
                margin-left: 12px;
                padding: 4px 10px;
                color: white;
                border-radius: 12px;
                font-size: 9px;
                font-weight: bold;
                cursor: pointer;
                opacity: 0.9;
                transition: all 0.2s ease;
                white-space: nowrap;
                flex-shrink: 0;
                text-transform: uppercase;
            `;
            
            expandButton.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                showCrmSlider();
                return false;
            });
            
            menuContainer.addEventListener('mouseenter', function() {
                expandButton.style.display = 'inline-block';
            });
            
            menuContainer.addEventListener('mouseleave', function() {
                expandButton.style.display = 'none';
            });
            
            menuContainer.appendChild(contentLeft);
            menuContainer.appendChild(expandButton);
            crmMenu.innerHTML = '';
            crmMenu.appendChild(menuContainer);
            
            if (window.crmObserver) window.crmObserver.disconnect();
        }
    }
    
    function showCrmSlider() {
        if (typeof BX !== 'undefined' && BX.SidePanel && BX.SidePanel.Instance) {
            const uniqueId = 'crm-expand-slider-' + Date.now();
            
            BX.SidePanel.Instance.open(uniqueId, {
                contentCallback: function(slider) {
                    return new Promise(function(resolve) {
                        loadCrmLinks(function(links) {
                            resolve(createCrmSliderContent(links));
                        });
                    });
                },
                width: 1000,
                cacheable: false,
                autoFocus: true,
                label: 'CRM - Быстрый доступ'
            });
        } 
    }
    
    function loadCrmLinks(callback) {
        try {
            BX.ready(function() {
                var params = {
                    'IBLOCK_TYPE_ID': 'lists',
                    'IBLOCK_ID': '485',
                    'ELEMENT_ORDER': { "ID": "ASC" }
                };
                
                BX.rest.callMethod(
                    'lists.element.get',
                    params,
                    function(result) {
                        if (result.error()) {
                            callback([]);
                        } else {
                            const menu = result.data();
                            const groupedLinks = {};

                            menu.forEach((item, index) => {
                                const groupName = item.NAME || 'Без названия';
                                
                                if (!groupedLinks[groupName]) {
                                    groupedLinks[groupName] = {
                                        name: groupName,
                                        items: [],
                                        detailImage: null,
                                        id: item.ID
                                    };
                                }

                                let urls = [];
                                let linkNames = [];
                                
                                if (item.PROPERTY_3485) {
                                    if (typeof item.PROPERTY_3485 === 'string') {
                                        urls = item.PROPERTY_3485.split(/[,;\n]/)
                                            .map(url => url.trim())
                                            .filter(url => url && url !== '#');
                                    } else if (typeof item.PROPERTY_3485 === 'object') {
                                        Object.values(item.PROPERTY_3485).forEach(value => {
                                            if (typeof value === 'string' && value.trim()) {
                                                const splitUrls = value.split(/[,;\n]/)
                                                    .map(url => url.trim())
                                                    .filter(url => url && url !== '#');
                                                urls.push(...splitUrls);
                                            }
                                        });
                                    }
                                }

                                if (item.PROPERTY_3488) {
                                    if (typeof item.PROPERTY_3488 === 'string') {
                                        linkNames = item.PROPERTY_3488.split(/[,;\n]/)
                                            .map(name => name.trim())
                                            .filter(name => name);
                                    } else if (typeof item.PROPERTY_3488 === 'object') {
                                        Object.values(item.PROPERTY_3488).forEach(value => {
                                            if (typeof value === 'string' && value.trim()) {
                                                const splitNames = value.split(/[,;\n]/)
                                                    .map(name => name.trim())
                                                    .filter(name => name);
                                                linkNames.push(...splitNames);
                                            }
                                        });
                                    }
                                }

                                urls.forEach((url, index) => {
                                    const linkName = linkNames[index] || url || 'Ссылка';
                                    groupedLinks[groupName].items.push({
                                        url: url,
                                        text: linkName
                                    });
                                });

                                if (!groupedLinks[groupName].detailImage && item.PROPERTY_3486) {
                                    if (typeof item.PROPERTY_3486 === 'string') {
                                        groupedLinks[groupName].detailImage = item.PROPERTY_3486;
                                    } else if (typeof item.PROPERTY_3486 === 'object') {
                                        for (let key in item.PROPERTY_3486) {
                                            if (typeof item.PROPERTY_3486[key] === 'string' && item.PROPERTY_3486[key]) {
                                                groupedLinks[groupName].detailImage = item.PROPERTY_3486[key];
                                                break;
                                            }
                                        }
                                    }
                                }
                            });

                            const crmLinks = Object.values(groupedLinks).filter(group => group.items.length > 0);

                            callback(crmLinks);
                        }
                    }
                );
            });
        } catch(error) {
            console.error('Error:', error);
            callback([]);
        }
    }
    
    function getFileSrc(fileId, callback) {
        if (!fileId) {
            callback(null);
            return;
        }
        
        fetch('/local/php_interface/s1/init_scripts/crm_menu/get_file_src.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'file_id=' + encodeURIComponent(fileId)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Response error');
            }
            return response.text();
        })
        .then(text => {
            try {
                const result = JSON.parse(text);
                if (result && result.filePath) {
                    callback(result.filePath);
                } else if (result) {
                    callback(result);
                } else {
                    callback(null);
                }
            } catch (e) {
                callback(text.trim() !== '' ? text : null);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            callback(null);
        });
    }

    function createIconContainer(fileId, elementId) {
        const iconContainer = document.createElement('div');
        iconContainer.style.cssText = `
            width: 24px;
            height: 24px;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        `;
        
        iconContainer.innerHTML = '📄';
        iconContainer.style.fontSize = '16px';
        
        if (fileId && elementId) {
            getFileSrc(fileId, function(filePath) {
                if (filePath) {
                    const finalUrl = filePath.startsWith('/') ? window.location.origin + filePath : filePath;
                    const img = document.createElement('img');
                    img.src = finalUrl;
                    img.alt = '';
                    img.style.cssText = `
                        width: 100%;
                        height: 100%;
                        object-fit: contain;
                        border-radius: 4px;
                    `;
                    
                    img.onload = function() {
                        iconContainer.innerHTML = '';
                        iconContainer.appendChild(img);
                    };
                    
                    img.onerror = function() {
                    };
                }
            });
        }
        
        return iconContainer;
    }

    function createSingleLinkItem(group) {
        const menuItem = document.createElement('div');
        menuItem.style.cssText = `
            width: 100%;
            margin-bottom: 12px;
            border-radius: 12px;
            background: white;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
			border: 2px solid #c6cdd3;
        `;

        const header = document.createElement('div');
        header.style.cssText = `
            display: flex;
            align-items: center;
            padding: 20px 24px;
            min-height: 50px;
            transition: background 0.2s ease;
        `;

        const iconContainer = createIconContainer(group.detailImage, group.id);
        const title = document.createElement('div');
        title.style.cssText = `
            flex: 1;
            font-weight: 600;
            font-size: 24px;
        `;
        title.textContent = group.name;

        header.appendChild(iconContainer);
        header.appendChild(title);

		menuItem.addEventListener('mouseenter', function() {
            this.style.background = '#e9ecef';
        });
        menuItem.addEventListener('mouseleave', function() {
            this.style.background = 'transparent';
        });

        menuItem.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (group.items[0] && group.items[0].url) {
                window.open(group.items[0].url, '_blank');
                setTimeout(() => {
                    if (typeof BX !== 'undefined' && BX.SidePanel && BX.SidePanel.Instance) {
                        BX.SidePanel.Instance.close();
                    }
                }, 100);
            }
        });


        menuItem.appendChild(header);
        return menuItem;
    }

    function createExpandableMenuItem(group) {
        const menuItem = document.createElement('div');
        menuItem.style.cssText = `
            width: 100%;
            margin-bottom: 12px;
            border-radius: 12px;
            background: white;
            overflow: hidden;
            transition: all 0.3s ease;
			border: 2px solid #c6cdd3;
        `;

        const header = document.createElement('div');
        header.style.cssText = `
            display: flex;
            align-items: center;
            padding: 20px 24px;
            cursor: pointer;
            min-height: 50px;
            transition: background 0.2s ease;
        `;

        const iconContainer = createIconContainer(group.detailImage, group.id);
        const title = document.createElement('div');
        title.style.cssText = `
            flex: 1;
            font-weight: 600;
            font-size: 24px;
        `;
        title.textContent = group.name;
        const arrow = document.createElement('div');
        arrow.style.cssText = `
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
            margin-left: 12px;
        `;
        arrow.innerHTML = '▼';
        arrow.style.fontSize = '24px';
        arrow.style.color = 'black';

        header.appendChild(iconContainer);
        header.appendChild(title);
        header.appendChild(arrow);

        const content = document.createElement('div');
        content.style.cssText = `
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        `;

        group.items.forEach((item, index) => {
            const subItem = document.createElement('div');
            subItem.style.cssText = `
                display: flex;
                align-items: center;
                padding: 16px 24px 16px 72px;
                cursor: pointer;
                border: 1px solid #e9ecef;
                transition: background 0.2s ease;
                min-height: 30px;
            `;

            const subIcon = document.createElement('div');
            subIcon.style.cssText = `
                width: 20px;
                height: 20px;
                margin-right: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            `;
            subIcon.innerHTML = '';
            subIcon.style.fontSize = '14px';

            const subText = document.createElement('div');
            subText.style.cssText = `
                flex: 1;
                font-size: 14px;
                color: #495057;
            `;
            subText.textContent = item.text;

            subItem.appendChild(subIcon);
            subItem.appendChild(subText);

            subItem.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (item.url) {
                    window.open(item.url, '_blank');
                    setTimeout(() => {
                        if (typeof BX !== 'undefined' && BX.SidePanel && BX.SidePanel.Instance) {
                            BX.SidePanel.Instance.close();
                        }
                    }, 100);
                }
            });

            subItem.addEventListener('mouseenter', function() {
                this.style.background = '#e9ecef';
            });
            subItem.addEventListener('mouseleave', function() {
                this.style.background = 'transparent';
            });

            content.appendChild(subItem);
        });

        if (content.lastChild) {
            content.lastChild.style.borderBottom = 'none';
        }

        let isExpanded = false;
        header.addEventListener('click', function() {
            isExpanded = !isExpanded;
            
            if (isExpanded) {
                content.style.maxHeight = (content.scrollHeight + 100) + 'px';
                arrow.style.transform = 'rotate(180deg)';
            } else {
                content.style.maxHeight = '0';
                arrow.style.transform = 'rotate(0deg)';
            }
        });

        menuItem.appendChild(header);
        menuItem.appendChild(content);

        return menuItem;
    }

    function createCrmSliderContent(groups) {
        const contentDiv = document.createElement('div');
        contentDiv.style.cssText = `
            height: 100%;
            background: #eef2f4;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        `;

        const title = document.createElement('div');
        title.textContent = 'CRM - Быстрый доступ';
        title.style.cssText = `
            font-size: 22px;
            color: #333;
            padding: 24px 28px 20px 28px;
            border-bottom: 2px solid #f0f0f0;
            font-weight: 700;
            background: white;
        `;
        contentDiv.appendChild(title);

        const groupsContainer = document.createElement('div');
        groupsContainer.style.cssText = `
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 28px;
            margin: 20px;
            overflow-y: auto;
            background: white;
            border-radius: 12px;
        `;

        const innerContainer = document.createElement('div');
        innerContainer.style.cssText = `
            width: 100%;
            max-width: 800px;
            display: flex;
            flex-direction: column;
        `;

        if (!groups || groups.length === 0) {
            const noGroupsMessage = document.createElement('div');
            noGroupsMessage.textContent = 'Нет доступных ссылок';
            noGroupsMessage.style.cssText = `
                text-align: center;
                color: #999;
                font-size: 18px;
                padding: 60px 20px;
                width: 100%;
            `;
            innerContainer.appendChild(noGroupsMessage);
        } else {
            groups.forEach(group => {
                if (group.items.length === 1) {
                    const menuItem = createSingleLinkItem(group);
                    innerContainer.appendChild(menuItem);
                } else {
                    const menuItem = createExpandableMenuItem(group);
                    innerContainer.appendChild(menuItem);
                }
            });
        }

        groupsContainer.appendChild(innerContainer);
        contentDiv.appendChild(groupsContainer);
        return contentDiv;
    }

    function safeDOMObservation() {
        if (!document.body) {
            setTimeout(safeDOMObservation, 100);
            return;
        }
        
        window.crmObserver = new MutationObserver(function(mutations) {
            let shouldCheck = false;
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) shouldCheck = true;
            });
            if (shouldCheck && !crmButtonAdded) setTimeout(addExpandButtonToCRM, 100);
        });
        
        try {
            window.crmObserver.observe(document.body, {
                childList: true,
                subtree: true
            });
        } catch (e) {
            console.error('Ошибка наблюдения за DOM:', e);
        }
    }
    
    function startInterception() {
        if (!crmButtonAdded) addExpandButtonToCRM();
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            startInterception();
            safeDOMObservation();
        });
    } else {
        startInterception();
        safeDOMObservation();
    }

    let checkCount = 0;
    const maxChecks = 30;
    const intervalId = setInterval(function() {
        if (!crmButtonAdded) {
            startInterception();
            checkCount++;
            if (checkCount >= maxChecks) clearInterval(intervalId);
        } else {
            clearInterval(intervalId);
        }
    }, 1000);

})();