function urlB64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

if ('serviceWorker' in navigator && 'PushManager' in window) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').then(function(registration) {
            console.log('ServiceWorker registration successful with scope: ', registration.scope);

            registration.pushManager.getSubscription().then(function(subscription) {
                if (subscription === null) {
                    console.log('User is NOT subscribed. Subscribing...');

                    registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlB64ToUint8Array('BN16o4Vep3uUcxOJmhlHO56Mi_m6Kz-Bepj0f05x86DqsrdJgHpeyZRqgDpiod4zMlPWMyxdKRBW2I0hUALZeCw')
                    }).then(function(subscription) {
                        console.log('User is subscribed');
                        console.log(subscription.endpoint);

                        // TODO notify the app server about this new subscription endpoint
                        // let data = {
                        //     appid:      "com.moodle.moodlemobile",
                        //     name:       "",
                        //     model:      "",
                        //     platform:   "",
                        //     version:    "",
                        //     pushid:     subscription.endpoint,
                        //     uuid:       ""
                        // };
                        // 'core_user_add_user_device', data
                    }).catch(function(err) {
                        console.log('Failed to subscribe the user: ', err);
                    });
                } else {
                    console.log('User IS subscribed.');
                }
            });
        }, function(err) {
            console.log('ServiceWorker registration failed: ', err);
        });
    });
}

window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    e.prompt();
});
