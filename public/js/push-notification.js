// Замени на свой public key из .env
const VAPID_PUBLIC_KEY = '{{ config("services.vapid.public_key") }}';

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

async function subscribeToPush() {
    if ('serviceWorker' in navigator && 'PushManager' in window) {
        try {
            // Запрос разрешения
            const permission = await Notification.requestPermission();
            
            if (permission !== 'granted') {
                console.log('Push уведомления запрещены');
                return;
            }

            // Регистрируем Service Worker
            const registration = await navigator.serviceWorker.register('/sw.js');
            
            // Подписываемся на push
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY)
            });

            // Отправляем подписку на сервер
            const response = await fetch('/api/push/subscribe', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(subscription)
            });

            if (response.ok) {
                console.log('Подписка на push успешна');
            }

            return subscription;
        } catch (error) {
            console.error('Ошибка подписки на push:', error);
        }
    } else {
        console.log('Push уведомления не поддерживаются');
    }
}

// Автоматическая подписка при загрузке страницы
document.addEventListener('DOMContentLoaded', () => {
    subscribeToPush();
});