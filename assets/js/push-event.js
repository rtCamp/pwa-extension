self.addEventListener('push', function (event) {
	console.log('[Service Worker] Push Received.');
	console.log(`[Service Worker] Push had this data: "${event.data.text()}"`);

	const title = 'Test Notifications';
	const options = {
		body: event.data.text(),
	};

	event.waitUntil(self.registration.showNotification(title, options));
});
