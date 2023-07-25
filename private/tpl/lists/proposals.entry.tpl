			<tr[if]{whitelisted} == 1[then] data-whitelisted="true"[/if]>
				<td class="text-center">{id}</td>
				<td>
					<a class="text-decoration-none" href="https://station.terra.money/proposal/{id}" target="_blank">{title|ESCAPE}</a>[if]{whitelisted} == 1[then] &nbsp; <abbr title="This proposal has been whitelisted." data-toggle="tooltip"><span class="fas fa-flag"></span></abbr>[/if]
				</td>
				<td class="text-center">
					{status}
				</td>
			</tr>
			<tr[if]{whitelisted} == 1[then] data-whitelisted="true"[/if]>
				<td class="bg-secondary bg-opacity-10" colspan="3">
					<table class="table table-hover table-striped table-fixed table-responsive-md">
					[foreach=votes]
					<tr>
						<td class="border-0"><a class="text-decoration-none" href="https://finder.terra.money/classic/validator/{#LOOP.address}" target="_blank">{#LOOP.name}</a></td>
						<td class="border-0 text-center"><strong class="text-white text-uppercase">{#LOOP.vote}</strong></td>
					</tr>
					[/foreach]
					</table>
				</td>
			</tr>
	