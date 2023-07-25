/*!
    * Start Bootstrap - SB Admin v7.0.5 (https://startbootstrap.com/template/sb-admin)
    * Copyright 2013-2022 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
    //
// Scripts
//

window.addEventListener('DOMContentLoaded', event => {

    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        // Uncomment Below to persist sidebar toggle between refreshes
        // if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        //     document.body.classList.toggle('sb-sidenav-toggled');
        // }
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

});


function showNotification(title, text, byid) {
	if('undefined' === typeof Notification) {
		return;
	}
	if(Notification.permission === 'denied') {
		return;
	}

	if(Notification.permission === 'granted') {
		if(title) {
			const noti = new Notification(title, {body: text});
			if(byid) {
				noti.onclick = (e) => {
					$('html, body').animate({
						scrollTop: $('#' + byid).offset().top
					}, 1000);
				};
			}
		}
	} else {
		Notification.requestPermission().then(permission => {
			if(permission === 'granted') {
				if(title) {
					showNotification(title, text, byid);
				}
			}
		});
	}
}
showNotification();

$.ajaxSetup({ cache: false });

$('#wallet-search').on('input', function(e) {
	$('#search-result').hide();
}).on('keypress', function(e) {
	if(e.keyCode === 13) {
		$(this).parent().find('button').trigger('click');
	}
});

$('#wallet-submit').on('click', function(e) {
	var val = $('#wallet-search').val();
	if(!val) {
		return;
	}

	$.post('/data/wallet.html', {"wallet": val}, function(data) {
		$('#search-result').html('Wallet owner: ' + data).show();
		$('#wallet-search').select();
	});
});

var ticker = $('#price-ticker');
var prev = {};
var wintitle = document.title;
if(ticker.length > 0) {
	if('undefined' !== typeof WebSocket) {
		var stream = 'wss://stream.binance.com:9443/stream?streams=luncbusd@miniTicker/ustcbusd@miniTicker';
		let socket = new WebSocket(stream);
	
		socket.onopen = function(e) {
		};
	
		socket.onmessage = function(event) {
			let json = JSON.parse(event.data);
			let id = '';
			if(json['data']['s'] === 'LUNCBUSD') {
			  id = 'ticker-lunc';
			} else if(json['data']['s'] === 'USTCBUSD') {
			  id = 'ticker-ustc';
			}
	
			let price = json['data']['c'];
	
			if(!prev[id]) {
				prev[id] = price;
			}
			let cl = 'text-white';
			if(prev[id] > price) {
				cl = 'text-danger';
			} else if(prev[id] < price) {
				cl = 'text-success';
			}
			$('#' + id).text('$ ' + price).removeClass('text-white text-danger text-success').addClass(cl);
			if(json['data']['s'] === 'LUNCBUSD') {
				document.title = 'LUNC: $' + price + ' – ' + wintitle;
			}
			prev[id] = price;
		};
	
		socket.onclose = function(event) {
			if(!event.wasClean) {
				console.log('ws closed');
			}
		};
	
		socket.onerror = function(error) {
			console.log(error.message);
		};
	}

	function blockHeight() {
		$.getJSON('/data/block_height.html', function(data) {
			$('#ticker-height').html(data.height);
			$('#ticker-epoch').html(data.epoch);
			$('#ticker-height-epoch').html(data.epoch_until);
			$('#ticker-height-staking').html(data.staking);
			$('#ticker-height-validators').html(data.validators);
			$('#ticker-height-tax').html(data.tax);
			$('#ticker-height-taxa').html(data.taxa);
			//$('#ticker-staked').html(data.staked);
			$('#ticker-burned-tax').html(data.taxburn);
		});
		window.setTimeout(function() { blockHeight(); }, 2500);
	}

	blockHeight();
}

$('[data-href]').on('click', function(e) {
	window.location = $(this).attr('data-href');
});


$('[data-form-element]').on('focus hover', function(e) {
	let $form = $(this).closest('form');
	if(!$form.attr('data-sub')) {
		$form.attr('data-sub', true);
		$form.attr('action', '/message/send_form.html');
	}
});

$('a[data-show]').on('click', function(e) {
	e.preventDefault();

	let $self = $(this);
	let targ = $self.attr('data-show');
	$('#' + targ).show();
	if($self.attr('data-hide-self')) {
		$self.remove();
	}
});

$(function () {
  $('[data-toggle="tooltip"]').tooltip();
  $('table[data-table="true"]').DataTable({pageLength: 50});
})
