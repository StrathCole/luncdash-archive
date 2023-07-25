<div class="card bg-secondary">
	<div class="card-body text-center">
		<h3 class="align-middle">
			Total supply: <span class="badge bg-dark align-middle lh-base text-end">{total_supply} LUNC<br />{total_supply_ust} USTC</span>
		</h3>
	</div>
</div>

<h4 class="mt-4">Top holders</h4>

<ul class="nav nav-tabs" id="holders-tab" role="tablist">
	<li class="nav-item" role="presentation">
		<button class="nav-link active" id="tab-lunc" data-bs-toggle="tab" data-bs-target="#lunc-holders" type="button" role="tab" aria-controls="profile" aria-selected="false">LUNC</button>
	</li>
	<li class="nav-item" role="presentation">
		<button class="nav-link" id="tab-ustc" data-bs-toggle="tab" data-bs-target="#ustc-holders" type="button" role="tab" aria-controls="profile" aria-selected="false">USTC</button>
	</li>
</ul>

<div class="tab-content table-responsive-md" id="holders-tab-content">
	<div class="tab-pane fade show active" id="lunc-holders" role="tabpanel" aria-labelledby="tab-lunc">
		<table class="table table-bordered table-dark table-hover table-striped table-fixed">
		<thead>
			<tr>
				<th>Name <a href="#disc">*</a></th>
				<th width="25%" class="text-end">LUNC</th>
				<th width="5%" clasS="text-end">%</th>
				<th width="30%">Wallet</th>
			</tr>
		</thead>
		<tbody>
			[foreach=holders]
			<tr>
				<td>{#LOOP.descr|ESCAPE}</td>
				<td class="text-end">{#LOOP.uluna}</td>
				<td class="small text-end"><small>{#LOOP.percentage}%</td>
				<td><a href="https://finder.terra.money/columbus-5/address/{#LOOP.wallet|url}" target="_blank">{#LOOP.wallet|ESCAPE}</a></td>
			</tr>
			[/foreach]
		</tbody>
		</table>
	</div>
	<div class="tab-pane fade" id="ustc-holders" role="tabpanel" aria-labelledby="tab-ustc">
		<table class="table table-bordered table-dark table-hover table-striped table-responsive-md table-fixed table-responsive-md">
		<thead>
			<tr>
				<th>Name <a href="#disc">*</a></th>
				<th width="25%" class="text-end">LUNC</th>
				<th width="5%" clasS="text-end">%</th>
				<th width="30%">Wallet</th>
			</tr>
		</thead>
		<tbody>
			[foreach=holders_ust]
			<tr>
				<td>{#LOOP.descr|ESCAPE}</td>
				<td class="text-end">{#LOOP.uusd}</td>
				<td class="small text-end"><small>{#LOOP.percentage}%</td>
				<td><a href="https://finder.terra.money/columbus-5/address/{#LOOP.wallet|url}" target="_blank">{#LOOP.wallet|ESCAPE}</a></td>
			</tr>
			[/foreach]
		</tbody>
		</table>
	</div>
</div>
<div id="disc">* ALL DATA WITHOUT WARRANTY OF BEING CORRECT!</div>