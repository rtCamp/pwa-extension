self.addEventListener('push', function (event) {

	if (!(self.Notification && self.Notification.permission === 'granted')) {
		return;
	}

	console.log('[Service Worker] Push Received.');
	console.log(`[Service Worker] Push had this data: "${event.data.text()}"`);

	var pushData = {};

	if (event.data) {
		pushData = event.data.json();
	}

	var title = pushData.title;
	var options = {
		body: pushData.data,
		data: {
			url: pushData.url,
		}
	};

	event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
	console.log('On notification click: ', event.notification.tag);
	event.notification.close();

	var url = event.notification.data.url;

	if( ! url ) {
		url = '/';
	}

	// This looks to see if the current is already open and
	// focuses if it is
	event.waitUntil(clients.matchAll({
		type: "window"
	}).then(function (clientList) {
		console.log( clientList );
		for (var i = 0; i < clientList.length; i++) {
			var client = clientList[i];
			if (client.url === url && 'focus' in client)
				return client.focus();
		}
		if (clients.openWindow) {
			return clients.openWindow( url );
		}
	}));
});
