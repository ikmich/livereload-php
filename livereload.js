/*
 * @author Ikenna Michael (http://github.com/ikmich/)
 * @email michfx@gmail.com
 */

(function() {

	/*
	 * Edit ROOT_DIR to the directory path of the site root (where livereload should be located).
	 */
	var ROOT_DIR = '';

	var RESULT_MODIFIED = '1';
	var REFRESH_INTERVAL = 400; // ms
	var INITIAL_DELAY = 1000; // ms

	function reload()
	{
		location.reload(true);
	}

	function livereload()
	{
		var $ = jQuery;
		$(document).ready(function() {
			function reloadCheck()
			{
				$.get(ROOT_DIR + '/livereload/livereload.php', function(response) {
					if (RESULT_MODIFIED === response)
					{
						reload();
					}
					setTimeout(reloadCheck, REFRESH_INTERVAL);
				});
			}
			setTimeout(reloadCheck, INITIAL_DELAY);
		});
	}

	if (typeof jQuery == 'undefined')
	{
		// jQuery not available. Load it dynamically.
		var head = document.getElementsByTagName('head')[0];
		var scrJq = document.createElement('script');
		scrJq.src = ROOT_DIR + '/livereload/jquery-1.8.0.min.js';
		head.insertBefore(scrJq, head.getElementsByTagName('script')[0]);

		// Start livereload after the jQuery script has loaded.
		scrJq.addEventListener('load', function() {
			livereload();
		});

		// If the dynamic jQuery loading failed.
		scrJq.addEventListener('error', function() {
			alert("Livereload could not load jQuery");
		});
	}
	else
	{
		livereload();
	}
})();