<?php
/**
 * RSHiScores, a MediaWiki extension for providing access to RuneScape's HiScores data on the RuneScape Wiki.
 * Copyright (C) 2010-2018 TehKittyCat
 *
 * SPDX-License-Identifier: GPL-3.0+
 *
 * RSHiScores exception for when errors occur.
 */

namespace MediaWiki\Extension\RSHiScores;

class Exception extends \Exception {
	/**
	 * Constructor to change the returned message to be in the page's content language.
	 *
	 * @param Message $message Error message made from wfMessage.
	 * @param int $code Optional error code.
	 * @param Exception $previous Exception that caused this exception. Unused by RSHiScores.
	 */
	public function __construct( \Message $message, $code = 0, \Exception $previous = null ) {
		parent::__construct( $message->inContentLanguage()->parse(), $code, $previous );
	}
}
