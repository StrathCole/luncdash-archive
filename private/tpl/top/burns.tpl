	<div class="row justify-content-between align-items-center">
		<div class="col-12 col-lg-6 col-xl-7">
			<div class="card bg-secondary">
				<div class="card-body text-center">
					<h3>Total burns: <span class="badge bg-dark text-end lh-1" id="total"></span> LUNC</h3>
				</div>
			</div>
		</div>
	
	</div>


<div class="row mt-2">
	<div class="col-12 col-md-6">
		<div class="overflow-scroll position-relative" style="height:400px;">
			<table class="table table-bordered table-dark table-responsive-sm table-hover table-striped">
				<thead class="position-sticky">
					<tr>
						<th>#</th>
						<th>Name</th>
						<th class="text-end">Burned</th>
					</tr>
				</thead>
				<tbody id="top-list">
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-12 col-md-6">
		<div id="chart">
		<canvas id="balance-chart" width="600" height="400"></canvas>
		</div>
	</div>
</div>


<div class="mt-2">
	<div class="d-inline-block bg-secondary p-3 mx-2">
		Burnalot initiative burns: <span class="badge bg-dark text-end lh-1" id="burnalot"></span> LUNC
	</div>
</div>


<h4 class="mt-5">Burn ticker</h4>
<div class="table-responsive-md">
<table class="table table-bordered table-dark table-hover table-striped table-fixed table-responsive-md">
	<thead>
		<tr>
			<th width="15%">Time</th>
			<th width="15%">LUNC</th>
			<th width="30%">Wallet</th>
			<th width="20%">Name</th>
			<th width="20%">Memo</th>
		</tr>
	</thead>
	<tbody id="burn-list">

	</tbody>
</table>
</div>