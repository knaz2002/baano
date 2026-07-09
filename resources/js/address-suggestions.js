// DaData с UI подсказками
export function initAddressSuggestions() {
    const input = document.getElementById('address-input');
    
    if (!input) {
        console.log('Поле адреса не найдено');
        return;
    }

    // Создаем контейнер для подсказок
    const suggestionsBox = document.createElement('div');
    suggestionsBox.id = 'address-suggestions';
    suggestionsBox.style.cssText = `
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        width: 100%;
        margin-top: 4px;
    `;
    
    // Оборачиваем input в div для позиционирования
    const wrapper = document.createElement('div');
    wrapper.style.position = 'relative';
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(input);
    wrapper.appendChild(suggestionsBox);

    let timeoutId;
    
    input.addEventListener('input', function(e) {
        const query = e.target.value;
        
        if (query.length < 3) {
            suggestionsBox.style.display = 'none';
            return;
        }
        
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fetch("https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address", {
                method: "POST",
                mode: "cors",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "Authorization": "Token 6c75c6945841c907b697100f2c3cfe0350747e7f"
                },
                body: JSON.stringify({ 
                    query: query,
                    count: 5
                })
            })
            .then(response => response.json())
            .then(result => {
                console.log("Подсказки:", result);
                
                if (result.suggestions && result.suggestions.length > 0) {
                    suggestionsBox.innerHTML = '';
                    
                    result.suggestions.forEach(suggestion => {
                        const item = document.createElement('div');
                        item.style.cssText = `
                            padding: 12px 16px;
                            cursor: pointer;
                            border-bottom: 1px solid #f0f0f0;
                            transition: background 0.2s;
                        `;
                        item.onmouseover = () => item.style.background = '#f8f9fa';
                        item.onmouseout = () => item.style.background = 'white';
                        item.textContent = suggestion.value;
                        
                        item.onclick = () => {
                            input.value = suggestion.value;
                            suggestionsBox.style.display = 'none';
                            console.log("Выбран адрес:", suggestion.value);
                        };
                        
                        suggestionsBox.appendChild(item);
                    });
                    
                    suggestionsBox.style.display = 'block';
                } else {
                    suggestionsBox.style.display = 'none';
                }
            })
            .catch(error => {
                console.error("Ошибка DaData:", error);
                suggestionsBox.style.display = 'none';
            });
        }, 300);
    });
    
    // Скрываем подсказки при клике вне поля
    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) {
            suggestionsBox.style.display = 'none';
        }
    });
    
    console.log("✓ DaData инициализирована с UI");
}
