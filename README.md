# luncdash sunsetting
luncdash.com code parts

Not all codes can be published here due to usage of a proprietary PHP framework.
If you recognize any sensitive parts I forgot to strip, please inform me, so I can change those on my side if necessary. GitHub doesn't forget 😄

The code requires a MySQL database (quite extensive usage, so beware of data amount and I/O).
Also it requires a full node running locally due to the amount of queries done. A remote LCD is not appropriate.

There is a file "PriceModule.inc.php" which has to contain a wallet address (placeholder is `terra1xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`). This wallet is used to calculate APY on delegations. It needs a good amount of delegated coins and frequent withdrawals (but not autocompounds) ~once a week.
After (un/re)delegating in between a manual withdrawal must be done in addition otherwise the numbers are totally off.
