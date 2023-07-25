const config = {
	type: 'line',
	data: [],
	options: {
	  pointBackgroundColor: '#444',
	  radius: 2,
	  responsive: true,
	  maintainAspectRatio: false,
	  interaction: {
		  intersect: false,
		  mode: 'index'
	  },
	  plugins: {
		legend: {
			position: 'top',
			labels: {
				color: '#cfcfcf'
			}
		},
		title: {
		  display: false
		},
		zoom: {
			zoom: {
			  drag: {
				enabled: true
			  },
			  pinch: {
				  enabled: false
			  },
			  pan: {
				  enabled: true
			  },
			  mode: 'x',
			  wheel: {
				  enabled: true,
				  modifierKey: 'shift'
			  }
			}
		  }
	  },
	  scales: {
		  x: {
			grid: {
				color: 'rgba(255,255,255,0.2)'
			},
			ticks: {
				color: '#cfcfcf'
			}
		  },
		  y: {
			grid: {
				color: 'rgba(255,255,255,0.2)'
			},
			ticks: {
				color: '#cfcfcf'
			}
		 },
		 y1: {
			type: 'linear',
			display: true,
			position: 'right',
			grid: {
				drawOnChartArea: false, // only want the grid lines for one axis to show up
			},
		  },
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