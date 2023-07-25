var latest = 0;
var idnum = 0;
function refreshData() {
	$.getJSON('/data/burns.html?block=' + latest, function(json) {
		if(!json || !json.data) {
			alert('ERROR');
			return;
		}
		$('#total').html(json.total);
		$('#burnalot').html(json.burnalot);
		$('#luncblaze').html(json.luncblaze);
		if(latest === 0 || json.latest > latest) {
			bchart.data = json.data;
			bchart.update();
			bchart.resetZoom();
		}
		$('#last-update').text(json.last_update);

		var pos = 0;
		for(var ent of json.top) {
			pos++;
			var row = $('#trow-' + pos);
			if(row.length < 1) {
				row = $('<tr></tr>').attr('id', 'trow-' + pos).appendTo('#top-list');
			}
			if(row.children('td').eq(1).text() === ent.name) {
				continue;
			}

			row.children().remove();

			$('<td></td>').text(pos).appendTo(row);
			$('<td></td>').text(ent.name).appendTo(row);
			$('<td class="text-end font-monospace text-nowrap"></td>').html(ent.amount + ' LUNC').appendTo(row);
		}

		for(var ent of json.burns) {
			idnum++;
			var row = $('<tr></tr>').addClass(ent.class).attr('id', 'row-' + idnum);
			$('<td class="small"></td>').text(ent.time).appendTo(row);
			$('<td class="text-end text-nowrap"></td>').html(ent.amount).appendTo(row);
			var td = $('<td class="text-truncate" title="' + ent.wallet + '"></td>');
			$('<a href="https://finder.terra.money/columbus-5/address/' + ent.wallet + '" target="_blank"></a>').text(ent.wallet).appendTo(td);
			td.appendTo(row);
			$('<td></td>').text(ent.descr).appendTo(row);
			$('<td class="text-truncate small" title="' + ent.memo + '"></td>').text(ent.memo).appendTo(row);
			row.prependTo($('#burn-list'));
			if(latest > 0 && ent.raw >= 1000000) {
				let addown = '';
				if(ent.descr !== 'unknown') {
					addown = ' which belongs to ' + ent.descr;
				}
				showNotification('New BURN of ' + ent.textamount + ' LUNC', ent.textamount + ' LUNC have been just burnt by ' + ent.wallet + addown + '.', idnum);
			}
		}

		latest = json.latest;
	});
};

window.setInterval(function() { refreshData(); }, 30000);

const config = {
	type: 'doughnut',
	data: [],
	plugins: [
		{
			beforeDraw: function(chart) {
				var ctx = chart.ctx;
				ctx.save();
				var image = new Image();
				image.src = '/assets/img/logo-luncburn.png';
				let usewidth = chart.chartArea.width;
				if(chart.chartArea.height < usewidth) {
					usewidth = chart.chartArea.height;
				}
				imageSize = usewidth / 1.9;
				ctx.drawImage(image, (chart.chartArea.width / 2 - imageSize / 2) - 5, (chart.height / 2 - imageSize / 2) - 5, imageSize * 1.2, imageSize);
				ctx.restore();
			}
		}
	],
	options: {
		responsive: true,
		maintainAspectRatio: false,
		plugins: {
			legend: {
				position: 'right',
				labels: {
					color: '#cfcfcf'
				}
			},
			title: {
				display: false
			}
		}
	}
};