let timeSpent = 0;
setInterval(() => { timeSpent += 1; }, 1000); // Увеличение времени каждую секунду
document.getElementById('leadForm').addEventListener('submit', () => {
    document.getElementById('timeOnSite').value = timeSpent >= 30 ? 1 : 0; // Запись в поле
});

document.getElementById('leadForm').addEventListener('submit', function (e) {
    e.preventDefault();

    // Форма данных
    const form = e.target;
    const formData = new FormData(form);
    const successMessage = document.getElementById('successMessage');

    // AJAX-запрос к серверу
    fetch(form.action, {
        method: form.method,
        body: formData
    }).then(response => {
        if (response.ok) {
            // Показать сообщение об успешной отправке
            successMessage.classList.remove('hidden');
            form.reset();
        } else {
            alert('Ошибка при отправке. Попробуйте снова.');
        }
    }).catch(() => {
        alert('Ошибка при отправке. Попробуйте снова.');
    });
});
