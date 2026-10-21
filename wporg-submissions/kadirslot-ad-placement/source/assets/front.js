/* KadirSlot Ad Placement: request one AdSense fill per unrendered RevGuard unit. */
(function () {
	var units = document.querySelectorAll('.revguard-free-ad ins.adsbygoogle:not([data-adsbygoogle-status])');
	var queue = (window.adsbygoogle = window.adsbygoogle || []);
	for (var i = 0; i < units.length; i++) {
		queue.push({});
	}
})();
