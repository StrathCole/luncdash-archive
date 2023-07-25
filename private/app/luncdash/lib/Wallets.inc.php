<?php

namespace LUNCDash\Lib;

class Wallets {
	const BINANCE_WALLETS = ['terra10wyptw59xc52l86pg86sy0xcm3nm5wg6a3cf7l','terra12dxclvqrgt7w3s7gtwpdkxgymexv8stgqcr0yu','terra132wegs0kf9q65t9gsm3g2y06l98l2k4treepkq','terra13e9670yuvfs06hctt9pmgjnz0yw28p0wgnhrqn','terra14q8cazgt58y2xkd26mlukemwth0cnvfqmgz2qk','terra14yqy9warjkxyecda5kf5a68qlknf4ve4sh7sa6','terra15jya6ugxp65y80y5h82k4gv90pd7acv58xp6jj','terra15s66unmdcpknuxxldd7fsr44skme966tdckq8c','terra163vzxz9wwy320ccwy73qe6h33yzg2yhyvv5nsf','terra174pe7qe7g867spzdfs5f4rf9fuwmm42zf4hykf','terra17m8tkde0mav43ckeehp537rsz4usqx5jayhf08','terra18vnrzlzm2c4xfsx382pj2xndqtt00rvhu24sqe','terra18wcdhpzpteharlkks5n6k7ent0mjyftvcpm6ee','terra1az3dsad74pwhylrrexnn5qylzj783uyww2s7xz','terra1dlh7k4hcnsrvlfuzhdzx3ctynj7s8dde9zmdyd','terra1dxxnwxlpjjkl959v5xrghx0dtvut60eef6vcch','terra1fax8l6srhew5tu2mavmu83js3v7vsqf9yr4fv7','terra1gft3qujlq04yza3s2r238mql2yn3xxqepzt2up','terra1gu6re549pn0mdpshtv75t3xugn347jghlhul73','terra1hhj92twle9x8rjkr3yffujexsy5ldexak5rglz','terra1j39c9sjr0zpjnrfjtthuua0euecv7txavxvq36','terra1jrq7xa63a4qgpdgtj70k8yz5p32ps9r7mlj3yr','terra1ju68sg6k39t385sa0fazqvjgh6m6gkmsmp4lln','terra1kj43wfnvrgc2ep94dgmwvnzv8vnkkxrxmrnhkp','terra1ky3qcf7v45n6hwfmkm05acwczvlq8ahnq778wf','terra1l89hprfccqxgzzypjzy3fnp7vqpnkqg5vvqgjc','terra1lzdux37s4anmakvg7pahzh03zlf43uveq83wh2','terra1mldd7aauynljpqa2ts5j5ksktys3xcw59e0e7h','terra1ncjg4a59x2pgvqy9qjyqprlj8lrwshm0wleht5','terra1ns7lfvrxzter4d2yl9tschdwntcxa25vtsvd8a','terra1p0vl4s4gp46vy6dm352s2fgtw6hccypph7zc3u','terra1qg59nhvag222kp6fyzxt83l4sw02huymqnklww','terra1s4rd0y5e4gasf0krdm2w8sjhsmh030m74f2x9v','terra1skmktm537pfaycgu9jx4fqryjt6pf77ycpesw0','terra1sujaqwaw7ls9fh6a4x7n06nv7fxx5xexwlnrkf','terra1t0jthtq9zhm4ldtvs9epp02zp23f355wu6zrzq','terra1t957gces65xd6p8g4cuqnyd0sy5tzku59njydd','terra1ttq26dq4egr5exmhd6gezerrxhlutx9u90uncn','terra1u0p7xuwlg0zsqgntagdkyjyumsegd8agzhug99','terra1urj8va62jeygra7y3a03xeex49mjddh3eul0qa','terra1vuvju6la7pj6t8d8zsx4g8ea85k2cg5u62cdhl','terra1xmkwsauuk3kafua9k23hrkfr76gxmwdfq5c09d','terra1y246m036et7vu69nsg4kapelj0tywe8vsmp34d','terra1yxras4z0fs9ugsg2hew9334k65uzejwcslyx0y'];
	const BURN_WALLET = 'terra1sk06e3dyexuq4shw77y3dsv480xv42mq73anxu';
	const ORACLE_WALLET = 'terra1jgp27m8fykex4e4jtt0l7ze8q528ux2lh4zh0f';
	const LUNCBLAZE_CONTRACT = 'terra1y02pee3kf2ayy7queh4w9khanr5dd4ucw73zw2';

	public static function getOwner(string $wallet, ?string $memo = null) : string {
		/*if($wallet === 'terra18vnrzlzm2c4xfsx382pj2xndqtt00rvhu24sqe') {
			$entity = 'Binance hot wallet';
		} else*/if(in_array($wallet, self::BINANCE_WALLETS, true)) {
			$entity = 'Binance Users';
		} elseif($wallet === 'terra13s4gwzxv6dycfctvddfuy6r3zm7d6zklynzzj5' && stripos(preg_replace('/[^A-Za-z]/', '', $memo), 'luncdao') !== false) {
			$entity = 'LUNC DAO';
		} elseif($wallet === 'terra13yxmvax7ndvaptgxmqjgqmejka48nrcfm66xaa') {
			$entity = 'LUNCWhales (CommunityDonatedLUNCBURNS)';
		} elseif($wallet === 'terra1ft5z69294ntj8vlg87z5fcd8yvk79s6nhzxgjl' || $wallet === 'terra1yjeajufvlxrh2hzjfamh2c6dp5a4cay97ql6mr' || $wallet === 'terra1q70lxuppdlu7l6dmajydhjrdf4vnqmch8mnaa0' || $wallet === 'terra17qs0y3qtjxx39qk530zc6f96nr68r9sa7c8pkl') {
			$entity = 'Metagloria NFT';
		} elseif($wallet === 'terra1qf07h97m7s93q7dj450c57h79f3c7a7ddn37r2') {
			$entity = 'CoinInn';
			if(stripos($memo, 'tax') === false && stripos($memo, 'coininn') === false) {
				$entity = 'CoinInn Users';
			}
		} elseif($wallet === 'terra1w8nc8ev0ylg97qnj080np4lnljngdvpd90ev63') {
			$entity = 'MEXC CEX';
		} elseif($wallet === 'terra1gphkyg550x47thys0ghkyctfq009qzu4nauysx') {
			$entity = 'GoldenMan';
		} elseif($wallet === 'terra16hfdw4vz2h8t6y2358fqs7hmau3sln8z3rup03' || $wallet === 'terra15ahd0dg9qwkg5tjmkn7fm6sdrpwa47m50selnm') {
			$entity = 'HappyCattyCrypto';
		} elseif($wallet === 'terra18jxw5kr0wekzzulv3rt2ujv5hswsfdwyzy6t96' || $wallet === 'terra1lquelqf644wl6cd806rn8gry6e4arzey365gp3') {
			$entity = 'TerraRebels';
		} elseif($wallet === 'terra1ucdfpkm6xzujjytg2mqr6yv3ym8526l2tpfjxu') {
			$entity = '@AmazingCryptos';
		} elseif($wallet === 'terra1pscrsgwf7v735saep7e69qsj4cpr4ngsrz8l7p') {
			$entity = 'Sinz#5984';
		} elseif($wallet === 'terra14s5eqfppup8ymjywf3devy8gy75nsrqkq3utjj') {
			$entity = 'BtcTurk';
		} elseif($wallet === 'terra1h7eetq4atvnxsaamx9q5jmhu7jzdkx7f360t0u') {
			$entity = 'Crypto King';
		} elseif($wallet === 'terra1g9htux72h5nj5c0hpzely3rqwnryzmy22xlxpu') {
			$entity = 'DFLunc';
		} elseif($wallet === 'terra16tjyr2qr3evaeucmvdl7w0kld65rthj40lsp0t') {
			if(stripos($memo, 'Visal Ly') !== false) {
				$entity = 'Visal Ly';
			} else {
				$entity = 'Coinspot Users';
			}
		} elseif($wallet === 'terra1zgu5w9ddpdt5ft36qn8cags0h8rz2uswr95p98' || $wallet === 'terra1j6qxpdw8ksetzapngqeuhgkqaxuglk73kgjd4k') {
			$entity = '@LunaVShape';
		} elseif($wallet === 'terra1nm0rrq86ucezaf8uj35pq9fpwr5r82clp5z7r5') {
			$entity = 'kraken Users';
		} elseif($wallet === 'terra1akdd36ty2hvmfqqr7zt8jqpte0r7d37e5rynuw') {
			$entity = 'Yanis';
		} elseif($wallet === 'terra1htl0t6wu3eqgjst5hztf4szc633tmy9fzjf50n') {
			$entity = 'CURRENCY365 (YouTube)';
		} elseif($wallet === 'terra1urzzga0tjwthu5aejujtgmh4kcvvpj87veefn3') {
			$entity = 'Apeiron Nodes';
		} elseif($wallet === 'terra15hn84ddmjj75s6mf2wsttda5ekta4lrl9d2qnd') {
			$entity = 'savethemoon.io';
		} elseif($wallet === 'terra1fg5g8acntt90n9303cm5fjza9s3newleq4rlmk') {
			$entity = 'interstellar lounge';
		} elseif($wallet === 'terra1j27nm2gjm0m4lsye8lspa46rax0rw4fge9awrs') {
			$entity = 'Lunar Station 88';
		} elseif($wallet === 'terra14xjkj5rv72fgqz3h78l883rw0njwhmzce6cjlf') {
			$entity = 'Classy Crypto';
		} elseif($wallet === 'terra1h70yt0f6lcejjt36nxqy3pdvdwh5ujhnhsydaj') {
			$entity = 'Unknown Binance User';
		} elseif($wallet === 'terra1vwchc3pkrxn8kahd0g9wxd8zjr0drnduzkn4z3') {
			$entity = 'Cremation Coin';
		} elseif($wallet === 'terra15hxe9w42srarad9f7decvt6cmnqavhz9see6ax') {
			$entity = 'LUNC Community Italia';
		} elseif($wallet === 'terra153mwt0upple9klvrryrtckx9vneguw6ja33d3c' || $wallet === 'terra1mn3unuuhxt9602v0zwnjum9nmchx4wu846uxsk') {
			$entity = 'TerraCasino.io';
		} elseif($wallet === 'terra19r76k85k22e9rmqh740n2emlrjt2ummuas0ecs') {
			$entity = 'LunaBurningKnights';
		} elseif($wallet === 'terra1wwyzunec5sj3zfvpvgxrk539l0675s0t9yx6cs') {
			$entity = 'Conor Kenny';
		} elseif($wallet === 'terra120ppepaj2lh5vreadx42wnjjznh55vvktwj679') {
			$entity = 'Allnodes';
		} elseif($wallet === 'terra1n6d46e9adt3yzrc4gs7z3rcswa79uklzgzk88e' && stristr($memo, 'VisionaryDeFi') !== false) {
			$entity = 'VisionaryDeFi';
		} elseif($wallet === 'terra1sq6th05hdwwwxvymzytc93lvhjxyx4pkjurhgj' || ($wallet === 'terra1v74a5u6qsjuj4gu6at9yn4p35uctcz82f02fau' && stristr($memo, 'Lunatics Token') !== false)) {
			$entity = 'Lunatics Token';
		} elseif($wallet === 'terra1rvxcszyfecrt2v3a7md8p30hvu39kj6xf48w9e' || $wallet === 'terra1v74a5u6qsjuj4gu6at9yn4p35uctcz82f02fau') {
			$entity = 'KuCoin Users';
		} else {
			$entity = 'unknown';
		}

		return $entity;
	}
}
