<div class="card bg-secondary">
	<div class="card-body text-center">
		<h3>Validators: <span class="badge bg-dark align-middle lh-base text-end">{bonded} bonded<br />
			{unbonded} unbonded<br />
			{jailed} jailed</span></h3>
	</div>
</div>

	<div class="badge bg-info text-white mt-3">More info about validator status in the <a href="https://classic-docs.terra.money/docs/full-node/manage-a-terra-validator/faq.html#what-are-the-different-states-a-validator-can-be-in" target="_blank">Terra Docs</a>.</div>
	
	<div class="mt-3 badge bg-info d-none d-sm-block">Use the mouse (click and drag) to zoom in on the chart.</div>
	<div class="mt-4 text-end"><a href="#" class="btn btn-secondary d-sm-none" onclick="zoomChart(0.7)"><span class="fas fa-search-minus"></span></a><a href="#" class="btn btn-secondary d-sm-none ms-1" onclick="zoomChart(1.3)"><span class="fas fa-search-plus"></span></a><a href="#" class="btn btn-secondary" onclick="refreshChart()"><span class="fas fa-refresh"></span></a></div>
<div id="content" class="mt-1">
	<canvas id="balance-chart" width="800" height="600"></canvas>
	<div id="loader"><div class="lds-roller"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div></div>
</div>