importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/9.22.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: "YOUR_API_KEY",
    authDomain: "YOUR_DOMAIN",
    project_id: "laravel-firebase-8e71e",
    messagingSenderId: "840813138300",
    appId: "YOUR_APP_ID",
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
    console.log('Background message:', payload);

    const title = payload.notification.title;
    const options = {
        body: payload.notification.body,
        data: payload.data
    };

    self.registration.showNotification(title, options);
});
