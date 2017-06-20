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
				drawMajorGridlines	: false,
				tickInterval		: "4 months",
				tickOptions			: {formatString : "%#m %Y"}
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
			lineWidth : 3
		}],
		seriesColors: ["#0D72C8"],
    });
});