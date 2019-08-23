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

/**
* Service worker 'install' event.
* If all the files are successfully cached, then the service worker will be installed.
* If any of the files fail to download, then the install step will fail.
*/
this.addEventListener('install', function(event) {
    console.log('Install');
});

/**
* After a service worker is installed and the user navigates to a different page or refreshes,
* the service worker will begin to receive fetch events.
*
* Network-first approach: if online, request is fetched from network and not from cache
*/

self.addEventListener('push', function(event) {  
    console.log('Received a push message', event);
    
    var title = 'Notification';  
    var body = 'There is newly updated content available on the site. Click to see more.';  
    var icon = 'https://raw.githubusercontent.com/deanhume/typography/gh-pages/icons/typography.png';  
    var tag = 'simple-push-demo-notification-tag';
    
    event.waitUntil(  
        self.registration.showNotification(title, {  
            body: body,  
            icon: icon,  
            tag: tag  
        })  
    );  
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


