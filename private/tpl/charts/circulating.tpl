<div class="card bg-secondary">
	<div class="card-body text-center">
		<h3>Circulating supply: <span class="badge bg-dark align-middle lh-base text-end">{circulating_supply} LUNC<br /><small>(unofficial)</small> {circulating_supply_unofficial} LUNC<br />{circulating_supply_ust} USTC</span></h3>
	</div>
</div>

	<div class="badge bg-info mt-3 p-2">This chart starts at 23rd of July '22!</div>
	
	<div class="mt-3 badge bg-info d-none d-sm-block">Use the mouse (click and drag) to zoom in on the chart.</div>
	<div class="mt-4 text-end"><a href="#" class="btn btn-secondary d-sm-none" onclick="zoomChart(0.7)"><span class="fas fa-search-minus"></span></a><a href="#" class="btn btn-secondary d-sm-none ms-1" onclick="zoomChart(1.3)"><span class="fas fa-search-plus"></span></a><a href="#" class="btn btn-secondary" onclick="refreshChart()"><span class="fas fa-refresh"></span></a></div>
<div id="content" class="mt-1">
	<canvas id="balance-chart" width="800" height="600"></canvas>
	<div id="loader"><div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>
</div>