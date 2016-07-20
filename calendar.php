<?php

/*
Plugin Name: Calendar Widget Month Select
Description: Replaces month caption with the month and year selects. Requires jQuery. Useful on the sites with huge monthly archives 
Version: 1.0
Author: Max Lyuchin
Author URI: http://lyuchin.ru
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

function add_month_select_to_calendar_widget( $calendar )
{
	global $wpdb, $m, $monthnum, $year;
	$start_year = $wpdb->get_var( "SELECT MIN(YEAR(post_date)) FROM {$wpdb->posts} WHERE post_type='post' AND post_status='publish'" );
	$end_year = $wpdb->get_var( "SELECT MAX(YEAR(post_date)) FROM {$wpdb->posts} WHERE post_type='post' AND post_status='publish'" );

	if ( 0 == $year ) {
		$selected_year = date("Y");
	} else {
		$selected_year = $year;
	}
	
	if ( 0 == $monthnum ) {
		$selected_month = date("n");
	} else {
		$selected_month = $monthnum;
	}

	ob_start();
?>
	<select class="calendar-widget-control" name="month" id="calendar_widget_month">
		<?php for ($i=1; $i <= 12; $i++): ?>
			<?php $selected = ( $i == $selected_month ) ? ' selected="selected"' : '' ?>
			<option value="<?php echo $i ?>"<?php echo $selected ?>><?php echo date_i18n( "F", mktime( 0, 0 , 0, $i ,1 ,2000 ) ) ?></option>
		<?php endfor; ?>
	</select>
	<select class="calendar-widget-control" name="year" id="calendar_widget_year">
		<?php for ($i=$start_year; $i <= $end_year; $i++): ?>
			<?php $selected = ( $i == $selected_year ) ? ' selected="selected"' : '' ?>
			<option value="<?php echo $i ?>"<?php echo $selected ?>><?php echo $i ?></option>
		<?php endfor; ?>
	</select>
	<script type="text/javascript">
		jQuery('.calendar-widget-control').change(function() {
			data = {
				action: 'load_calendar',
				m: jQuery("#calendar_widget_month").val(),
				y: jQuery("#calendar_widget_year").val(),
			};
			jQuery("#calendar_wrap").load("<?php echo admin_url('admin-ajax.php') ?>", data);
		});
	</script>
<?php
	$month_select = ob_get_clean();
	
	$calendar = preg_replace( "/<caption>(.*?)<\/caption>/", $month_select, $calendar );
	return $calendar;
}
add_filter( 'get_calendar', 'add_month_select_to_calendar_widget' );

function load_calendar_ajax()
{
	global $monthnum, $year;
	$monthnum = intval($_POST['m']);
	$year = intval($_POST['y']);
	ob_clean();
	get_calendar();
	wp_die();
}
add_action( 'wp_ajax_load_calendar', 'load_calendar_ajax' );
add_action( 'wp_ajax_nopriv_load_calendar', 'load_calendar_ajax' );
