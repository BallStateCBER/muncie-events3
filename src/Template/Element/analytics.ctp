<?php
    use Cake\Core\Configure;

    $googleAnalyticsId = Configure::read('google_analytics_id');
    $debug = Configure::read('debug');
?>
<?php if ($googleAnalyticsId && !$debug): ?>
	<script type="text/javascript">
		var _gaq = _gaq || [];
		_gaq.push(['_setAccount', '<?= $googleAnalyticsId ?>']);
		_gaq.push(['_setDomainName', 'muncieevents.com']);
		_gaq.push(['_trackPageview']);

		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		})();
	</script>
<?php endif; ?>
