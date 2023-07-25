<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no" />
	<title>#LUNCDash information board</title>

	<link rel="apple-touch-icon" sizes="76x76" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	<link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#da532c">
	
	<link href="{THEME_PATH}/css/datatables.min.css" rel="stylesheet" />
	<link href="{THEME_PATH}/css/styles.css?v=1.0" rel="stylesheet" />
	<link href="{THEME_PATH}/css/custom.css?v=1.0" rel="stylesheet" />
	<script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
</head>

<body class="sb-nav-fixed">
	<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
		<!-- Navbar Brand-->
		<a class="navbar-brand ps-3" href="[intlink]index[/intlink]">#LUNCDash</a>
		<!-- Sidebar Toggle-->
		<button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle"><i class="fas fa-bars"></i></button>
	</nav>
	<div id="layoutSidenav">
		<div id="layoutSidenav_nav">
			<nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
				<div class="sb-sidenav-menu">
					<div class="nav">
					<a class="nav-link" href="[intlink]index[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
						Dashboard
					</a>
					<a class="nav-link" href="[intlink]statistics[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-th"></i></div>
						On-chain statistics
					</a>
					[comment]<a class="nav-link" href="[intlink]projects[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
						Projects
					</a>[/comment]

					<div class="sb-sidenav-menu-heading">Top lists</div>
					<a class="nav-link" href="[intlink]toplist;burns[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-fire"></i></div>
						Top burners
					</a>
					<a class="nav-link" href="[intlink]toplist;holders[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-trophy"></i></div>
						Top holders
					</a>

					<div class="sb-sidenav-menu-heading">Governance</div>
					<a class="nav-link" href="[intlink]validators[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-list-ul"></i></div>
						Validator list
					</a>
					<a class="nav-link" href="[intlink]proposals[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-commenting"></i></div>
						Proposals
					</a>
					<a class="nav-link" href="[intlink]staking;pool[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-database"></i></div>
						Staking pool
					</a>
					<a class="nav-link" href="[intlink]staking;validators[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-check"></i></div>
						Validator status
					</a>

					<div class="sb-sidenav-menu-heading">Supply</div>
					<a class="nav-link" href="[intlink]burns;chart[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-fire"></i></div>
						Burn volume
					</a>
					<a class="nav-link" href="[intlink]burn_tax[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-fire"></i></div>
						Burn Tax
					</a>
					<a class="nav-link" href="[intlink]supply;total_supply[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-credit-card"></i></div>
						Total supply
					</a>
					<a class="nav-link" href="[intlink]supply;circulating_supply[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-recycle"></i></div>
						Circulating supply
					</a>
					<a class="nav-link" href="[intlink]supply;community_pool[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
						Community pool
					</a>
					<a class="nav-link" href="[intlink]wallets;holdings;wallet=terra1jgp27m8fykex4e4jtt0l7ze8q528ux2lh4zh0f[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
						Oracle rewards pool
					</a>

					<div class="sb-sidenav-menu-heading">Wallets</div>
					<a class="nav-link" href="[intlink]wallets;holdings;wallet=terra18vnrzlzm2c4xfsx382pj2xndqtt00rvhu24sqe[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
						Binance hot wallet
					</a>
					<a class="nav-link" href="[intlink]wallets;holdings;wallet=terra1ncjg4a59x2pgvqy9qjyqprlj8lrwshm0wleht5[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
						Binance deposit
					</a>
					<a class="nav-link" href="[intlink]wallets;holdings;wallet=terra1jrq7xa63a4qgpdgtj70k8yz5p32ps9r7mlj3yr[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
						Binance staking
					</a>
					<a class="nav-link" href="[intlink]wallets;holdings;wallet=binance[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
						Binance (multi wallets)
					</a>
					<a class="nav-link" href="[intlink]wallets;holdings;wallet=terra1rvxcszyfecrt2v3a7md8p30hvu39kj6xf48w9e[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
						KuCoin hot wallet
					</a>
					<a class="nav-link" href="[intlink]wallets;holdings;wallet=terra1chq5ps8yya004gsw4xz62pd4psr5hafe7kdt6d[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
						KuCoin cold wallet
					</a>
					<a class="nav-link" href="[intlink]wallets;holdings;wallet=terra1v74a5u6qsjuj4gu6at9yn4p35uctcz82f02fau[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-wallet"></i></div>
						KuCoin withdraw
					</a>

					<div class="sb-sidenav-menu-heading">Chain</div>
					<a class="nav-link" href="[intlink]speed;block_chain[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
						Blockchain speed
					</a>
					<a class="nav-link" href="[intlink]volume;on_chain[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-area-chart"></i></div>
						On-chain volume
					</a>
					<a class="nav-link" href="[intlink]volume;binance[/intlink]">
						<div class="sb-nav-link-icon"><i class="fas fa-area-chart"></i></div>
						Binance on-chain volume
					</a>


					[comment]<!-- Beispiel mit Navigation
					<div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
						<nav class="sb-sidenav-menu-nested nav">
							<a class="nav-link" href="layout-static.html">Static Navigation</a>
							<a class="nav-link" href="layout-sidenav-light.html">Light Sidenav</a>
						</nav>
					</div>-->[/comment]
				
					</div>
				</div>
				<div class="sb-sidenav-footer small">
					Copyright &copy; 2022-2023 LuncDash
				</div>
			</nav>
		</div>
		<div id="layoutSidenav_content" class="bg-dark pb-5">

			<main class="pb-5">
				<div class="container-fluid px-4 py-4 bg-dark text-light">
					{CONTENT}
				</div>
			</main>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
	[if]{LOAD_SCRIPT}[then]
	<script src="{THEME_PATH}/js/hammer.min.js"></script>
	[/if]
	[if]{MODULE} === "index"[then]<script src="{THEME_PATH}/js/luxon.js"></script>
	<script src="{THEME_PATH}/js/chartjs.3.0.1.js"></script>
	[else]<script src="{THEME_PATH}/js/chart.min.js"></script>[/if]
	
	<script src="{THEME_PATH}/js/chartjs-plugin-zoom.min.js"></script>
	[if]{MODULE} === "index"[then]<script src="{THEME_PATH}/js/chartjs-chart-financial.js"></script>
	<script src="{THEME_PATH}/js/chartjs-luxon.js"></script>
	[/if]
	<script src="{THEME_PATH}/js/jquery.min.js"></script>
	<script src="{THEME_PATH}/js/datatables.min.js?v=1.0"></script>
	<script src="{THEME_PATH}/js/scripts.js?v=4.17"></script>
	[if]{LOAD_SCRIPT}[then]
	<script src="{THEME_PATH}/js/{LOAD_SCRIPT}?v=2.4"></script>
	[/if]
	[if]{MODULE} === "index"[then]
	<script src="{THEME_PATH}/js/countdown.js?v=1.1"></script>
	<script src="{THEME_PATH}/js/TweenMax.min.js"></script>
	[/if]
	[if]{PRE_SCRIPT}[then]
	<script>
		{PRE_SCRIPT}
	</script>
	[/if]
	<script>
	const container = document.getElementById('balance-chart');
	var bchart;
	if(container) {
		const ctx = container.getContext('2d');
		[if]{MODULE} === "index"[then]if(screen.width < 1200) {
	ctx.canvas.height = 500;
}
[/if]
		bchart = new Chart(ctx, config);
		bchart.canvas.addEventListener('touchmove', (e) => {
			scrollChart(e, bchart);
		});
		bchart.canvas.addEventListener('touchstart', (e) => {
			scrollChartBegin(e);
		});
		setChartValues(bchart);
	}
	</script>
	[if]{ADD_SCRIPT}[then]
	<script>
		{ADD_SCRIPT}
	</script>
	[/if]
</body>

</html>