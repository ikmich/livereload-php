/*
 * Livereload requires jQuery but comes bundled with it and loads jQuery
 * dynamically if it is not already available on the page.
 */
(function() {

	var SUCCESS_RESULT = '1';

	/*
	 * Edit ROOT_DIR to the directory path of the site root (where livereload should be located).
	 */
	var ROOT_DIR = '';

	function reload()
	{
		location.reload();
	}

	function livereload()
	{
		var $ = jQuery;
		$(document).ready(function() {
			function reloadCheck()
			{
				$.get(ROOT_DIR + '/livereload/livereload.php', function(response) {
					if (SUCCESS_RESULT === response)
					{
						setTimeout(reload, 10);
					}
					setTimeout(reloadCheck, 400);
				});
			}
			setTimeout(reloadCheck, 1000);
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