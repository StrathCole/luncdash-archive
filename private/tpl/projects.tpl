
<h3 class="mt-5">Projects supporting the classic chain <small>(excerpt)</small></h3>

<div class="badge bg-danger p-3 mt-2">We do not list any Token/Coin projects here. This is due to the massive amount of scamming attempts and many new coins that pop up. Please DYOR before investing in any token. Carefully review all information! NONE of the listed projects is financial advice! We won't ever give financial advice at all!</div>

<a href="#" class="btn btn-outline-warning fs-5 p-3 d-inline-block mt-4" data-show="project-listing" data-hide-self="true">I confirm I'll do my own reasearch and that none of the listed projects is financial advice!</a>

<div id="project-listing" class="mt-4 row row-cols-1 row-cols-md-3 row-cols-xl-4 g-4 mt-2 bg-secondary bg-opacity-10 p-4" style="display:none">
	[foreach=projects]
    <div class="col">
        <div class="card bg-secondary bg-opacity-25">
            <img src="{THEME_PATH}/img/{#LOOP.image}" class="card-img-top" alt="{#LOOP.title|ESCAPE}">
            <div class="card-body">
                <h5 class="card-title">{#LOOP.title|ESCAPE}</h5>
                <p class="card-text">{#LOOP.text|ESCAPE}</p>
            </div>
            <div class="card-footer small">
                <ul>
					[if]{#LOOP.item_1}[then]<li>{#LOOP.item_1}</li>[/if]
					[if]{#LOOP.item_2}[then]<li>{#LOOP.item_2}</li>[/if]
					[if]{#LOOP.item_3}[then]<li>{#LOOP.item_3}</li>[/if]
					[if]{#LOOP.item_4}[then]<li>{#LOOP.item_4}</li>[/if]
					[if]{#LOOP.item_5}[then]<li>{#LOOP.item_5}</li>[/if]
					[if]{#LOOP.item_6}[then]<li>{#LOOP.item_6}</li>[/if]
				</ul>
            </div>
            <div class="card-footer text-end">
                <small class="text-muted">[if]{#LOOP.website}[then]<a href="{#LOOP.website}" target="_blank">» {#LOOP.website_name}</a>[/if]
					[if]{#LOOP.twitter}[then]<a href="{#LOOP.twitter}" target="_blank">» Twitter</a>[/if]
					[if]{#LOOP.youtube}[then]<a href="{#LOOP.youtube}" target="_blank">» YouTube</a>[/if]
					[if]{#LOOP.telegram}[then]<a href="{#LOOP.telegram}" target="_blank">» Telegram</a>[/if]</small>
            </div>
        </div>
    </div>
	[/foreach]

</div>
