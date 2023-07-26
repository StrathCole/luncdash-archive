# luncdash sunsetting
luncdash.com code parts

Not all codes can be published here due to usage of a proprietary PHP framework.
If you recognize any sensitive parts I forgot to strip, please inform me, so I can change those on my side if necessary. GitHub doesn't forget 😄

The code requires a MySQL database (quite extensive usage, so beware of data amount and I/O).
Also it requires a full node running locally due to the amount of queries done. A remote LCD is not appropriate.

There is a file "PriceModule.inc.php" which has to contain a wallet address (placeholder is `terra1xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx`). This wallet is used to calculate APY on delegations. It needs a good amount of delegated coins and frequent withdrawals (but not autocompounds) ~once a week.
After (un/re)delegating in between a manual withdrawal must be done in addition otherwise the numbers are totally off.

The "TwitterModule.inc.php" needs api credentials for the Twitter API.

The full database dump (July 26th 2023) is downloadable from https://luncdash.com/luncdash_dump_20230726.sql.gz
(GZipped, compressed file size approx. 11GB, uncompressed file size approx. 36GB, database size when imported approx. 120GB)
It might contain incomplete data for some things, especially since June 14th (parity upgrade).
The wallet holdings list in the dump should be quite good:
```
SELECT SUM(uluna) FROM wallet;
+----------------------+
| SUM(uluna)           |
+----------------------+
| 6615149068218.061000 |
+----------------------+
1 row in set (0.251 sec)
```
Which includes the staked amount and should nearly match the official supply data.

```
SELECT SUM(uusd) FROM wallet;
+-------------------+
| SUM(uusd)         |
+-------------------+
| 9646066077.072258 |
+-------------------+
1 row in set (0.285 sec)
```
The USTC amount should be a bit undervalued in the holdings list. Not sure why exactly.

Things to note:
In the database the field is named "uluna"/"uusd" while in fact it's already converted to LUNC/USTC (divided by 1,000,000).

In the wallet list there is an account type. If I understood correctly, then type `/terra.vesting.v1beta1.LazyGradedVestingAccount` will give the original 30 wallets of the chain that got vesting tokens. Might be wrong on that.

The type `/cosmos.auth.v1beta1.ModuleAccount` is for the system module wallets, e.g. oracle, burn, …
