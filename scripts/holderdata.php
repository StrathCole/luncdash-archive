<?php

require "db.inc.php";
require "functions.inc.php";

$html = '<table class="holders">
<thead>
	<tr>
		<th>Wallet</th>
		<th>LUNC</th>
		<th>Name <a href="#disc">*</a></th>
	</tr>
</thead>
<tbody>
';

$qrystr = 'SELECT `uluna`, `wallet`, `descr` FROM `wallet` WHERE 1 ORDER BY `uluna` DESC LIMIT 50';
$result = $db->query($qrystr);
while(($cur = $result->get())) {
	$html .= '<tr>
		<td><a href="https://finder.terra.money/columbus-5/address/' . $cur['wallet'] . '" target="_blank">' . $cur['wallet'] . '</a></td>
		<td>' . number_format($cur['uluna'], 6, '.', ',') . '</td>
		<td>' . htmlspecialchars($cur['descr'], ENT_COMPAT, 'UTF-8') . '</td>
		</tr>';
}
$result->free();

$html .= '</tbody></table>';
$fp = fopen('/var/www/html/holders.js', 'w');
fwrite($fp, $html);
fclose($fp);
