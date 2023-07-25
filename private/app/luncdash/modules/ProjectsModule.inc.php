<?php

namespace LUNCDash\Modules;

use Framework\Core\Modules\Base as ModuleBase;
use Framework\Core\Template;

class ProjectsModule extends ModuleBase {
	/**
	 * @param string $action
	 */
	public static function onShow() {
		exit;
		$projects = [
			[
				'image' => 'metagloria.png',
				'title' => 'MetaGloria',
				'text' => 'MetaGloria is a Play to Earn game that you haven\'t seen before! Go on a fantastic journey, lead your squad of aliens and fight against evil dark forces!',
				'item_1' => '1.2% burn tax ingame',
				'item_2' => 'Resides on classic chain',
				'item_3' => 'Play to Earn game',
				'item_4' => 'Brings utility back to the chain',
				'item_5' => 'Upcoming NFT marketplace on classic chain',
				'website' => 'https://metagloria.io',
				'website_name' => 'metagloria.io',
				'twitter' => 'https://twitter.com/MetaGloriaNFT',
			],
			[
				'image' => 'lunapunks.jpg',
				'title' => 'Luna Punks',
				'text' => 'Luna Punks NFT DApp for Terra\'s one of a kind onchain Luna Punks NFT.',
				'item_1' => 'Current on-chain project',
				'item_2' => 'LUNAPUNKS are held in your terra station wallet',
				'item_3' => 'Floor price pre crash was $80 - $100',
				'item_4' => 'Supports LUNC payments and brings utility',
				'item_5' => 'Capitulation survivor',
				'website' => 'https://classic.lunapunks.io',
				'website_name' => 'classic.lunapunks.io'
			],
			[
				'image' => 'lunaknights.jpg',
				'title' => 'LunaBurningKnights',
				'text' => 'LunaBurningKnights NFT project launched on Terra Classic blockchain',
				'item_1' => 'Current on-chain project',
				'item_2' => 'Knights are held in your terra station wallet',
				'item_3' => '40% of initial mint price goes to burn',
				'item_4' => 'Only buyable with LUNC and brings utility',
				'item_5' => 'Stakable and used in upcoming RPG game',
				'website' => 'https://lunaburningknights.io/',
				'website_name' => 'lunaburningknights.io/'
			]
		];

		shuffle($projects);

		$rep = [
			'projects' => $projects
		];

		Template::load('projects');
		Template::replacements($rep);

		Template::load('main');
		//Template::assign('META_DESC', HTML::toEntities($meta_data));
		Template::add(Template::get('projects'));
		Template::show();

		exit;
	}

}
