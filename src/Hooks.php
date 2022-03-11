<?php
/**
 * RSHiScores, a MediaWiki extension for providing access to RuneScape's HiScores data on the RuneScape Wiki.
 * Copyright (C) 2010-2018 TehKittyCat
 *
 * SPDX-License-Identifier: GPL-3.0+
 *
 * Hooks for the RSHiScores extension.
 */

namespace MediaWiki\Extension\RSHiScores;

use MediaWiki\Hook\ParserFirstCallInitHook;

class Hooks implements ParserFirstCallInitHook {
	/**
	 * Register parser hook.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 *
	 * @param Parser $parser
	 */
	public function onParserFirstCallInit( $parser ) {
		$parser->setFunctionHook( 'hs', [ RSHiScores::class, 'render' ] );
	}
}
