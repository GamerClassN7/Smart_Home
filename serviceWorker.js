importScripts('https://www.gstatic.com/firebasejs/7.1.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.1.0/firebase-messaging.js');

/**
* Cache version, change name to force reload
*/
var CACHE_VERSION = 'v1';

/**
* Stuff to put in the cache at install
*/
var CACHE_FILES  = [
    'templates/automatio.phtml',
    'templates/dashboard.phtml',
    'templates/home.phtml',
    'templates/login.phtml',
    'templates/scene.phtml',
    'templates/setting.phtml',
    'views/Automation.phtml',
    'views/Dashboard.phtml',
    'views/Home.phtml',
    'views/Login.phtml',
    'views/Scene.phtml',
    'views/Setting.phtml',
    'assets/logo.svg'
];

this.addEventListener('install', function(event) {
});


self.addEventListener('push', function(event) {  
    console.log('Received a push message', event);
    if (!firebase.apps.length) {
        firebase.initializeApp({
        'messagingSenderId': '93473765978'
    });
}
    
    const messaging = firebase.messaging();
    messaging.setBackgroundMessageHandler(function(payload) {
        console.log('[firebase-messaging-sw.js] Received background message ', payload);
        // Customize notification here
        const notificationTitle = 'Background Message Title';
        const notificationOptions = {
            body: 'Background Message body.',
            icon: '/itwonders-web-logo.png'
        };
        
        return self.registration.showNotification(notificationTitle,
            notificationOptions);
        });
    });
    
    self.addEventListener('sync', function(event) {
        console.info('Event: Sync');
        
    });
    
    self.addEventListener('fetch', function (event) {
        
    });
    
    self.addEventListener("online", function (event) {
        
    });
    
    self.addEventListener("offline", function (event) {
    });
    
    self.addEventListener('notificationclick', function(e) {
        
    });
    
    // Initialize the Firebase app in the service worker by passing in the
    // messagingSenderId.
    
    
    
