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


self.addEventListener('install', function(event) {
    console.info('Installed');
});

self.addEventListener('push', function(event) {  
    console.log('Received a push message', event);
    if (event && event.data) {
        var data = event.data.json();
        data = JSON.parse(data.data.notification);
        console.log(data);
        event.waitUntil(self.registration.showNotification(data.title, {
            body: data.body,
            icon: data.icon || null
        }));
    }
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

