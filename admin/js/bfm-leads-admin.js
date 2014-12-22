/*global jQuery:false */
/*global ajaxurl:true */
var DEBUG = false;
jQuery(document).ready(function($) {

	'use strict';

	if( $('.bfm-analytics').length ){

		var bfmTable = $('#bfm-stats-general'),
			plotarea = $('#bfm-stats-graph');


		$('.bfm-stats-controls').on('change', 'select', function (event) {
			event.preventDefault();
			var form = $(this).parents('form');
			form.submit();
		});

		var data = {
			'action': 'bfm_get_graph_data',
			'bfm_analytics_form': bfmTable.data('form'),
			'bfm_analytics_time': bfmTable.data('timeframe'),
			'bfm_analytics_period': bfmTable.data('period')
		};
		$.post(ajaxurl, data, function (response) {

			if (DEBUG) {
				console.log(response);
			}

			var json = $.parseJSON(response);

			// Graph Options
			var graphOptions = {
				series: {
					lines: {
						show: true
					},
					points: {
						show: true
					}
				},
				grid: {
					color: '#ccc',
					borderWidth: 1,
					borderColor: '#666',
					hoverable: true
				},
				xaxis: {
					mode: 'time',
					minTickSize: json.scale.minTickSize,
					timeFormat: json.scale.timeformat,
					min: json.min,
					max: json.max
				},
				tooltip: true,
				tooltipOpts: {
					content: "%s: %y"
				}
			};

			// Draw graph
			$.plot(plotarea, [json.impressionsData, json.conversionsData], graphOptions);
			plotarea.removeClass('wpbo-loading');

		});
	}

});