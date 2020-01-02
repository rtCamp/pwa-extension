const applicationServerPublicKey = 'BGOxOFFkJX1hZd4CInZel4PnjCMkAFRKGD3xORlwItM4Sl1QS5L81rf2axzmSuj7Zt-T7blYt4GnBWxlfNpGZU0';

if (!("Notification" in window)) {
	console.log("This browser does not support desktop notification");
} else if (Notification.permission === "granted") {
	getSubscription();
} else if (Notification.permission !== 'denied' || Notification.permission === "default") {
	getPermission();
}


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

function getPermission() {
	Notification.requestPermission(function (permission) {
		if (permission === "granted") {
			console.log('Permission Granted');
			getSubscription();
		}
	});
}

function getSubscription() {
	console.log('Called');
	
	navigator.serviceWorker.getRegistration().then(function (swRegistration) {
		swRegistration.pushManager.getSubscription()
			.then(function (subscription) {
				isSubscribed = !(subscription === null);

				if (!isSubscribed) {
					subscribeUser(swRegistration);
				} else {
					console.log(JSON.stringify(subscription));
				}

			});
	});
}

function subscribeUser(swRegistration) {
	const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
	swRegistration.pushManager.subscribe({
		userVisibleOnly: true,
		applicationServerKey: applicationServerKey
	})
		.then(function (subscription) {
			console.log('User is subscribed.');
			updateSubscriptionOnServer(subscription);
		})
		.catch(function (error) {
			console.error('Failed to subscribe the user: ', error);
		});
}

function updateSubscriptionOnServer(subscription) {
	jQuery.ajax({
		type: 'POST',
		url: adminURL,
		data: {
			"action": "update_subscription",
			'subscription': JSON.stringify(subscription),
		},
	});
}

function unsubscribeUser() {
	navigator.serviceWorker.getRegistration().then(function (swRegistration) {

		swRegistration.pushManager.getSubscription()
			.then(function (subscription) {
				if (subscription) {
					// TODO: Tell application server to delete subscription
					return subscription.unsubscribe();
				}
			})
			.catch(function (error) {
				console.log('Error unsubscribing', error);
			})
	})
}