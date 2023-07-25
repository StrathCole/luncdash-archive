const config = {
	type: 'candlestick',
	data: [],
	responsive: true,

	scales: {
		x: {
			type: 'timeseries'
		},
		y: {
			type: 'linear'
		},
		adapters: {
			date: {
				locale: 'en'
			}
		}
	},
	options: {
		scales: {
			y: {
				ticks: {
					callback: function(value, index, ticks) {
						return '$' + value.toFixed(8);
					}
				}
			}
		},
		plugins: {
			tooltip: {
				callbacks: {
					label: function(ctx) {
						const point = ctx.parsed;
						const {o, h, l, c} = point;

						return [`O: \$${o}`,`H: \$${h}`,`L: \$${l}`,`C: \$${c}`];
					}
				}
			}
		}
	}
};

function refreshChart() {
	bchart.resetZoom();
	bchart.options.scales.x.max = maxx;
	bchart.update();
}

var startx;
var maxx;
var minx;

function setChartValues(chart) {
	let dat = chart.getInitialScaleBounds();
	if(!dat || !dat.x){
		return;
	}
	maxx = dat.x.max;
	minx = dat.x.min;
}

function scrollChartBegin(ev) {
	if(ev.touches.length > 1) {
		return;
	}

	let touchobj = ev.touches[0];
    startx = parseInt(touchobj.clientX);
}

function scrollChart(ev, chart) {
	let touchobj = ev.changedTouches[0];
    let distx = parseInt(touchobj.clientX) - startx;

	if(ev.changedTouches.length > 1) {
		return;
	}

	if(typeof chart.options.scales.x.max === 'undefined' || typeof chart.options.scales.x.min === 'undefined') {
		return;
	}

	chart.pan({'x': distx / 5});
	chart.update();
}

function zoomChart(step) {
	if(!step) return;

	bchart.zoom(step);
	bchart.update();
}

function setCandlePeriod(p, e) {
	if(p) {
		period = p;
	}

	$(e).siblings('a').removeClass('text-white border-white').addClass('border-secondary');
	$(e).addClass('text-white border-white').removeClass('border-secondary');

	return false;
}

var period = '15m';
window.setInterval(function() {
	$.getJSON('/data/candles.html?p=' + period, function(json) {
		if(!json || !json.data) {
			alert('ERROR');
			return;
		}
		bchart.data = json.data;
		bchart.update();
		bchart.resetZoom();
	});
}, 5000);