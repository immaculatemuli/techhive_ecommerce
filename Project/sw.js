self.addEventListener('install',  () => self.skipWaiting());
self.addEventListener('activate', e  => e.waitUntil(self.clients.claim()));

self.addEventListener('push', function (event) {
    var data = {};
    try { data = event.data ? event.data.json() : {}; } catch (_) {}

    var title   = data.title || 'TechHive Payment Request';
    var options = {
        body:             data.body || 'Tap to confirm payment.',
        tag:              'techhive-payment',
        requireInteraction: true,
        data:             { token: data.token },
        actions: [
            { action: 'confirm', title: 'OK, Pay' },
            { action: 'cancel',  title: 'Cancel'  },
        ],
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var token  = event.notification.data && event.notification.data.token;
    var action = event.action === 'cancel' ? 'cancelled' : 'confirmed';

    if (token) {
        event.waitUntil(
            fetch('/techhive/Project/confirm_payment.php', {
                method:      'POST',
                credentials: 'include',
                headers:     { 'Content-Type': 'application/json' },
                body:        JSON.stringify({ token: token, action: action }),
            }).then(function () {
                return self.clients.matchAll({ type: 'window', includeUncontrolled: true });
            }).then(function (clients) {
                if (clients.length > 0) clients[0].focus();
            })
        );
    }
});
