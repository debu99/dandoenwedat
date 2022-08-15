function consolePrint(message) {
    if (en4.core.environment == 'development') {
        console.log(message);
    }
}
function statusOnlineOffline(){
    if (navigator.onLine) {
        consolePrint('online');
        sesJqueryObject('.offline_message_error').show();
    } else {
        consolePrint('offline');
        sesJqueryObject('.offline_message_error').hide();
    }
}
window.addEventListener('online', statusOnlineOffline());
window.addEventListener('offline', statusOnlineOffline());
statusOnlineOffline();
sesJqueryObject(function() {
    sesJqueryObject('#sespwa-loading-image').hide();
    sesJqueryObject(window).on('beforeunload', function() {
        sesJqueryObject('#sespwa-loading-image').show();
    });
});
en4.core.runonce.add(function() {
    if (!('serviceWorker' in navigator)) {
        return;
    }
    navigator.serviceWorker.register(en4.core.baseUrl + 'sespwa-service-worker.js').then(function(registration) {
        consolePrint('register successfully Scope', registration.scope);
    }, function(err) {
        consolePrint('register unsuccessfully Error: ', err);
    });
    navigator.serviceWorker.ready.then(function(serviceWorkerRegistration) {
        window.addEventListener('beforeinstallprompt', function(e) {
            e.userChoice.then(function(choiceResult) {
                if (choiceResult.outcome === 'dismissed') {
                    consolePrint('home screen not installed.');
                } else {
                    consolePrint('home screen added.');
                }
            });
        });
    });
});