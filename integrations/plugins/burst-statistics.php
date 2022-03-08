<?php
defined( 'ABSPATH' ) or die( "you do not have access to this page!" );

/**
 * Add notice for Burst Statistics
 *
 */

function cmplz_burst_statistics_integration_show_compile_statistics_notice() {
	cmplz_sidebar_notice( __("Burst Statistics will be configured automatically.", 'complianz-gdpr' ) );
}
add_action( 'cmplz_notice_compile_statistics', 'cmplz_burst_statistics_integration_show_compile_statistics_notice' );

function cmplz_burst_statistics_activate_burst() {
	ob_start();
	?>
	<script>
		document.addEventListener("cmplz_cookie_warning_loaded", function(consentData) {
			var region = consentData.detail;
			if ( region !== 'uk' ) {
				let scriptElements = document.querySelectorAll('script[data-service="burst"]');
				scriptElements.forEach(obj => {
					if ( obj.classList.contains('cmplz-activated') || obj.getAttribute('type') === 'text/javascript' ) {
						return;
					}
					obj.classList.add( 'cmplz-activated' );
					let src = obj.getAttribute('src');
					if ( src ) {
						obj.setAttribute('type', 'text/javascript');
						cmplz_run_script(src, 'statistics', 'src');
					}
				});
			}
		});
		document.addEventListener("cmplz_run_after_all_scripts", cmplz_burst_fire_domContentLoadedEvent);
		function cmplz_burst_fire_domContentLoadedEvent() {
			dispatchEvent(new Event('burst_fire_hit'));
		}
	</script>
	<?php
	$script = ob_get_clean();
	$script = str_replace(array('<script>', '</script>'), '', $script);
	wp_add_inline_script( 'cmplz-cookiebanner', $script);
}
add_action( 'wp_enqueue_scripts', 'cmplz_burst_statistics_activate_burst',PHP_INT_MAX );

/**
 * If checked for privacy friendly, and the user select "none of the above", return true, as it's burst.
 * @param $is_privacy_friendly
 *
 * @return bool
 */
function cmplz_burst_statistics_privacy_friendly($is_privacy_friendly){
	$statistics = cmplz_get_value( 'compile_statistics' );
	if ($statistics==='yes') {
		$is_privacy_friendly = true;
	}
	return $is_privacy_friendly;
}
add_filter('cmplz_cookie_warning_required_stats', 'cmplz_burst_statistics_privacy_friendly');
add_filter('cmplz_statistics_privacy_friendly', 'cmplz_burst_statistics_privacy_friendly');
