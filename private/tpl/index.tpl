<link href="{THEME_PATH}/css/countdown.css?v=1.2" type="text/css" rel="stylesheet" />

<div class="badge col-12 col-sm-auto bg-info bg-opacity-25 mb-3 fs-6">Beware of scammers! The official LUNC burn wallet address is <a href="https://finder.terra.money/classic/address/{burn_wallet}" target="_blank" class="text-decoration-none text-white"><code class="text-white">{burn_wallet}</code></a>.</div>

<div class="row justify-content-between align-items-center">
	<div class="col-12 col-lg-6 col-xl-7">
		<h2>Welcome to #LUNCdash.com</h2>

		<p>NOT FINANCIAL ADVICE! All provided information without any warranty of being correct.</p>
	</div>
</div>

<div class="row px-3">
	<div class="row order-1 order-lg-0 flex-wrap justify-content-around mt-5 bg-secondary bg-opacity-10 p-4 fs-6">
		<div class="card bg-info bg-opacity-25 col-md-4 col-xl-auto p-2 mt-2">
			<div class="card-body text-end">
				<h5 class="card-title font-monospace">{total_supply} LUNC</h5>
				<h5 class="card-title font-monospace">{total_supply_ust} USTC</h5>
				<h5 class="card-title font-monospace">{foreign_supply_ust} <abbr class="fs-6" data-toggle="tooltip" title="This is the additional amount of USTC that would be minted to the total supply when ALL other stablecoins (e. g. EUTC, MYTC, …) would be market-swapped to USTC.">xUSTC</abbr></h5>
				<a href="[intlink]supply;total_supply[/intlink]">Total supply</a>
			</div>
		</div>
		<div class="card bg-info bg-opacity-25 col-md-4 col-xl-auto p-2 mt-2">
			<div class="card-body text-end">
				<h5 class="card-title font-monospace">{circulating_supply} LUNC</h5>
				<h5 class="card-title font-monospace"><abbr class="fs-6" data-toggle="tooltip" title="Unofficial circulating supply. The official value does not take staked coins into account currently.">({circulating_supply_unofficial} LUNC)</abbr></h5>
				<a href="[intlink]supply;circulating_supply[/intlink]">Circulating supply</a>
			</div>
		</div>
		<div class="card bg-info bg-opacity-25 col-md-4 col-xl-auto p-2 mt-2">
			<div class="card-body text-end">
				<h5 class="card-title font-monospace">{community_pool} LUNC</h5>
				<h5 class="card-title font-monospace">{community_pool_ust} USTC</h5>
				<a href="[intlink]supply;community_pool[/intlink]">Community pool</a>
			</div>
		</div>
		<div class="card bg-info bg-opacity-25 col-md-4 col-xl-auto p-2 mt-2">
			<div class="card-body text-end text-nowrap">
				<h5 class="card-title font-monospace">[number=3]{staking_ratio}[/number] %</h5>
				<h5 class="card-title font-monospace small">(bonded: [number=3]{staking_ratio_bonded}[/number] %)</h5>
				<a href="[intlink]staking;pool[/intlink]">Staking ratio</a>
			</div>
		</div>
		<div class="card bg-info bg-opacity-25 col-md-4 col-xl-auto p-2 mt-2">
			<div class="card-body text-end text-nowrap">
				<h5 class="card-title font-monospace"><abbr data-toggle="tooltip" title="This is an estimate and might not be 100% accurate. Includes USTC swapped to LUNC at current exchange rate.">APY</abbr>: [number=1]{apy}[/number] %</h5>
				<h5 class="card-title font-monospace small">(LUNC: [number=1]{apy_uluna}[/number] %)</h5>
				<h5 class="card-title font-monospace small">(<abbr data-toggle="tooltip" title="If swapped to LUNC at current exchange rate.">USTC</abbr>: [number=1]{apy_uusd}[/number] %)</h5>
			</div>
		</div>
	</div>
</div>
			
<div class="mt-4">
	<div id="price-ticker" class="row col-12 flex-wrap justify-content-center bg-secondary bg-opacity-10 p-4 align-items-stretch">
		<h4 class="mb-0">Chain tickers</h4>
		<div class="col-12 col-md-4 col-xl-auto p-3">
			<div class="p-3 bg-light bg-opacity-10 rounded-3 text-end h-100">
				<h5 class="text-center">Block height</h5>
				<span class="badge fs-4" id="ticker-height">loading …</span>
			</div>
		</div>
		<div class="col-12 col-md-4 col-xl-auto p-3">
			<div class="p-3 bg-light bg-opacity-10 rounded-3 text-end h-100">
				<h5 class="text-center">Epoch</h5>
				<span class="badge fs-4" id="ticker-epoch">loading …</span>
			</div>
		</div>
		<div class="col-12 col-md-4 col-xl-auto p-3">
			<div class="p-3 bg-light bg-opacity-10 rounded-3 text-end h-100">
				<h5 class="text-center">Next epoch</h5>
				<span class="badge fs-4" id="ticker-height-epoch">loading …</span>
			</div>
		</div>
		<div class="col-12 col-md-4 col-xl-auto p-3">
			<div class="p-3 bg-light bg-opacity-10 rounded-3 text-end h-100">
				<h5 class="text-center">Burned by tax</h5>
				<div id="ticker-burned-tax">loading …</div>
			</div>
		</div>
		[comment]<div class="col-12 col-md-10 col-xl-auto p-3 row">
			<div class="[if]{tax_active} !== true[then]col-12 col-md-6 [/if]p-3 bg-light bg-opacity-10 rounded-3 text-end h-100 d-flex justify-content-center flex-column">
				<h5 class="text-center">Blocks until tax <abbr data-toggle="tooltip" title="ATTENTION! It is the planned block height.">active <span class="fas fa-question-circle small"></span></abbr></h5>
				<span class="badge fs-4" id="ticker-height-taxa">loading …</span>
			</div>
			[if]{tax_active} !== true[then]
			<div class="col-12 col-md-6 p-3 bg-light bg-opacity-10 rounded-3 text-end h-100">
				<div id="burntax-countdown" class="countdown tiny-countdown">
					<div class="bloc-time days" data-init-value="{burntax_days}">
					  <span class="count-title">Days</span>
				
					  <div class="figure days days-1">
						<span class="top">{burntax_days_1}</span>
						<span class="top-back">
						  <span>{burntax_days_1}</span>
						</span>
						<span class="bottom">{burntax_days_1}</span>
						<span class="bottom-back">
						  <span>{burntax_days_1}</span>
						</span>
					  </div>
				
					  <div class="figure hours hours-2">
						<span class="top">{burntax_days_2}</span>
						<span class="top-back">
						  <span>{burntax_days_2}</span>
						</span>
						<span class="bottom">{burntax_days_2}</span>
						<span class="bottom-back">
						  <span>{burntax_days_2}</span>
						</span>
					  </div>
					</div>
				
					<div class="bloc-time hours" data-init-value="{burntax_hours}">
					  <span class="count-title">Hours</span>
				
					  <div class="figure hours hours-1">
						<span class="top">{burntax_hours_1}</span>
						<span class="top-back">
						  <span>{burntax_hours_1}</span>
						</span>
						<span class="bottom">{burntax_hours_1}</span>
						<span class="bottom-back">
						  <span>{burntax_hours_1}</span>
						</span>
					  </div>
				
					  <div class="figure hours hours-2">
						<span class="top">{burntax_hours_2}</span>
						<span class="top-back">
						  <span>{burntax_hours_2}</span>
						</span>
						<span class="bottom">{burntax_hours_2}</span>
						<span class="bottom-back">
						  <span>{burntax_hours_2}</span>
						</span>
					  </div>
					</div>
					<div class="flex-breaker">
					</div>
					<div class="bloc-time min" data-init-value="{burntax_minutes}">
					  <span class="count-title">Mins</span>
				
					  <div class="figure min min-1">
						<span class="top">{burntax_minutes_1}</span>
						<span class="top-back">
						  <span>{burntax_minutes_1}</span>
						</span>
						<span class="bottom">{burntax_minutes_1}</span>
						<span class="bottom-back">
						  <span>{burntax_minutes_1}</span>
						</span>        
					  </div>
				
					  <div class="figure min min-2">
					   <span class="top">{burntax_minutes_2}</span>
						<span class="top-back">
						  <span>{burntax_minutes_2}</span>
						</span>
						<span class="bottom">{burntax_minutes_2}</span>
						<span class="bottom-back">
						  <span>{burntax_minutes_2}</span>
						</span>
					  </div>
					</div>
				
					<div class="bloc-time sec" data-init-value="{burntax_seconds}">
					  <span class="count-title">Secs</span>
				
						<div class="figure sec sec-1">
						<span class="top">{burntax_seconds_1}</span>
						<span class="top-back">
						  <span>{burntax_seconds_1}</span>
						</span>
						<span class="bottom">{burntax_seconds_1}</span>
						<span class="bottom-back">
						  <span>{burntax_seconds_1}</span>
						</span>          
					  </div>
				
					  <div class="figure sec sec-2">
						<span class="top">{burntax_seconds_2}</span>
						<span class="top-back">
						  <span>{burntax_seconds_2}</span>
						</span>
						<span class="bottom">{burntax_seconds_2}</span>
						<span class="bottom-back">
						  <span>{burntax_seconds_2}</span>
						</span>
					  </div>
					</div>
				  </div>
			</div>[/if]
		</div>[/comment]
		[comment]<div class="col-12 col-md-4 col-xl-auto p-3">
			<div class="p-3 bg-light bg-opacity-10 rounded-3 text-end h-100">
				<h5 class="text-center">Blocks until new validators</h5>
				<span class="badge fs-4" id="ticker-height-validators">loading …</span>
			</div>
		</div>[/comment]
		<div class="col-12 col-md-4 col-xl-auto p-3">
			<div class="p-3 bg-light bg-opacity-10 rounded-3 text-end h-100">
				<h5 class="text-center">LUNC</h5>
				<span class="badge fs-4" id="ticker-lunc">loading …</span>
			</div>
		</div>
		<div class="col-12 col-md-4 col-xl-auto p-3">
			<div class="p-3 bg-light bg-opacity-10 rounded-3 text-end h-100">
				<h5 class="text-center">USTC</h5>
				<span class="badge fs-4" id="ticker-ustc">loading …</span>
			</div>
		</div>
	</div>
</div>
				<div class="mt-2">
					<div class="row bg-secondary bg-opacity-10 p-4">
						<div class="row justify-content-start">
							<a href="#" class="d-block col-auto px-2 py-1 border border-secondary text-decoration-none" onclick="return setCandlePeriod('1d', this);">1d</a>
							<a href="#" class="d-block col-auto px-2 py-1 border border-secondary text-decoration-none" onclick="return setCandlePeriod('4h', this);">4h</a>
							<a href="#" class="d-block col-auto px-2 py-1 border border-secondary text-decoration-none" onclick="return setCandlePeriod('1h', this);">1h</a>
							<a href="#" class="d-block col-auto px-2 py-1 border border-secondary text-decoration-none" onclick="return setCandlePeriod('30m', this);">30m</a>
							<a href="#" class="d-block col-auto px-2 py-1 border border-white text-decoration-none" onclick="return setCandlePeriod('15m', this);">15m</a>
							<a href="#" class="d-block col-auto px-2 py-1 border border-secondary text-decoration-none" onclick="return setCandlePeriod('5m', this);">5m</a>
						</div>
						<div>
							<canvas id="balance-chart" width="800" height="200"></canvas>
						</div>
					</div>
				</div>

<div class="row bg-secondary bg-opacity-10 p-4 mt-4">			
<div class="col-md-5 col-xl-auto p-2">
	<h4>Search wallet owner</h4>
	<div class="badge bg-info text-start text-wrap">You can input a wallet address here and we will show you the owner, if we know it. All information without warranty of being correct.</div>
	<div class="input-group mt-2"><input class="form-control" type="text" id="wallet-search" /><button type="button" class="btn btn-info" id="wallet-submit">Search</button></div>
	<p id="search-result" class="mt-2"></p>
</div>
</div>

