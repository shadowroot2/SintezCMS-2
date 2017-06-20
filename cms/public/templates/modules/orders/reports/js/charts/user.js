// DOM
$(function()
{
	$.jqplot('chart', [_CHART_],
	{
		grid: {
            background		: 'rgba(255,255,255,1.0)',
            drawBorder		: false,
            shadow			: false,
            gridLineColor	: '#ccc',
            gridLineWidth	: 1
        },
		animate			: true,
        animateReplot	: true,
		cursor			: {
            show: true,
            zoom: true
        },
		axesDefaults: {
            pad				: 0,
			labelRenderer	: $.jqplot.DateAxisRenderer,
			drawBaseline	: false
        },
		highlighter: {
            show			: true,
			sizeAdjust		: 1,
            tooltipOffset	: 9
        },
		axes:{
			xaxis:{
				renderer			: $.jqplot.DateAxisRenderer,
				drawMajorGridlines	: false
			},
            yaxis: {
                tickOptions: {
                    formatString	: "%'d тг",
                }
            }
		},
		seriesDefaults	: {
			rendererOptions	: {
				smooth: true
			},
			animation: {
				show: true
			}
		},
		series:[{
			lineWidth : 2
		}],
		seriesColors: ["#0D72C8"],
    });
});