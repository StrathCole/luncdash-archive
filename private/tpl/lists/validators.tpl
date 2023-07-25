<h4>Validators</h4>

<div class="row mt-3 border border-secondary bg-secondary bg-opacity-25 p-4">
	<p>In this table we give a brief overview of the active validator set.</p>
	<h5>What do we consider as a "fair" commission rate?</h5>
	<ul class="ps-4">
	<li>A commission rate should neither be set too low nor too high.</li>
	<li>A commission rate of e. g. zero percent is bad for the chain as a whole. No validator can run a node at such a low commission rate and at the same time make profits. Also bigger validators can afford running at a loss while smaller providers will not be able to. A low commission rate could kick out many validators mid-term.</li>
	<li>A commission rate set too high is not good for delegators. A commission rate of e. g. 60% would mean that only 40% of the node's rewards will be distributed to all delegators of the node.</li>
	<li>
		We consider a fair commission rate somewhat like this idea that was brought up on Twitter: <a href="https://twitter.com/Sephiroth/status/1571281321363902464" target="_blank">dynamic minimum commission</a>. In addition a commission rate above 20% is also not considered a "fair rate", although this <em>might</em> be okay for certain projects that openly communicate the reason for it (e. g. because they burn all rewards or use it to support utility on the chain).
	</li>
	</ul>
	<p>
		Please remember to always check for the details of a validator you want to delegate to. This list is <strong>not</strong> financial advice! Do your own research!
	</p>
</div>

<div class="table-responsive-md mt-4">
<table class="table table-bordered table-dark table-hover table-striped table-fixed table-responsive-md" data-table="true">
	<thead>
		<tr>
			<th width="5%" class="text-center">#</th>
			<th width="30%"><abbr title="Clicking on the validator name will take you to the corresponding validator's page on Terra Finder." data-toggle="tooltip">Name</abbr></th>
			<th width="15%" class="text-center"><abbr title="The voting power defines how much weight a validator's vote has on governance votings! REMEMBER: you can always override the vote of the validators you delegated to by voting yourself!" data-toggle="tooltip">Voting power</abbr></th>
			<th width="10%" class="text-center"><abbr title="The commission defines the share of the node's rewards that the validator keeps for himself. So a commission of 5% means that delegators get 95% of the node's rewards. See box above for 'fair' commission." data-toggle="tooltip">Commission</abbr></th>
			<th width="10%" class="text-center"><abbr title="The uptime defines the missed oracle votes (exchange rate votes) during the previous months. A low uptime might indicate that the node is not reliable. You should do your own research on to why the uptime of a node is low. This is NOT the uptime shown in Terra Station, which shows the missed blocks signed." data-toggle="tooltip">Oracle Uptime</abbr></th>
		</tr>
	</thead>
	<tbody id="burn-list">
		[foreach=validators]
			<tr>
				<td class="text-center">{#LOOP.num}.</td>
				<td>
					<a href="https://finder.terra.money/classic/validator/{#LOOP.address}" target="_blank">{#LOOP.name}</a>
				</td>
				<td class="text-center">
					[number=2]{#LOOP.voting_power}[/number]%<small><br />(= [number=3]{#LOOP.delegation_share}[/number]{#LOOP.delegation_share_unit} LUNC)</small>
				</td>
				<td class="text-center">
					<abbr title="[if]{#LOOP.high_commission}[then]This commission is above 20%. Please check the details carefully whether a commission that high is okay for you.[elseif]{#LOOP.fair_commission}[then]This commission is considered 'fair' regarding the above-mentioned criteria.[else]This commission is too low compared to the voting power. The validator should be urged to raise its commission to at least [number=2]{#LOOP.fair_amount}[/number]%.[/if]" data-toggle="tooltip">[number=2]{#LOOP.commission}[/number]%
					<i class="text-[if]{#LOOP.high_commission}[then]warning[elseif]{#LOOP.fair_commission}[then]primary[elseif]{#LOOP.low_commission}[then]danger[else]warning[/if] fas fa-[if]{#LOOP.high_commission}[then]exclamation-triangle[elseif]{#LOOP.fair_commission}[then]check-square[elseif]{#LOOP.low_commission}[then]ban[else]exclamation-triangle[/if]"></i>
					</abbr>
				</td>
				<td class="text-center">
					[number=2]{#LOOP.uptime}[/number]%<small><br />(missed [number=0]{#LOOP.missed}[/number] oracle votes)</small>
				</td>
			</tr>
		[/foreach]
	</tbody>
</table>
</div>