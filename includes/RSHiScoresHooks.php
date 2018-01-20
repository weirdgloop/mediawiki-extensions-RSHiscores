<?php
/**
 * RSHiscores, a MediaWiki extension for providing access to RuneScape's HiScores data on the RuneScape Wiki.
 * Copyright (C) 2010-2018 TehKittyCat
 *
 * SPDX-License-Identifier: GPL-3.0+
 *
 * Hooks for the RSHiScores extension.
 */
class RSHiScoresHooks {
	/**
	 * Register parser hook.
	 *
	 * Parser &$parser
	 * @return bool
	 */
	public static function onParserFirstCallInit( Parser &$parser ) {
		$parser->setFunctionHook( 'hs', 'RSHiScores::renderHiScores' );
		return true;
	}
}
