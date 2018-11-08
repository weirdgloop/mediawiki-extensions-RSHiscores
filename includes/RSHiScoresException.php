<?php
/**
 * RSHiscores, a MediaWiki extension for providing access to RuneScape's HiScores data on the RuneScape Wiki.
 * Copyright (C) 2010-2018 TehKittyCat
 *
 * SPDX-License-Identifier: GPL-3.0+
 *
 * RSHiScores exception for when errors occur.
 */
class RSHiScoresException extends Exception {
	/**
	 * Constructor to change the returned message to be in the page's content language.
	 *
	 * @param Message $message Error message made from wfMessage.
	 * @param int $code Optional error code. Treated as a boolean by RSHiscores, if $code evaluates to true,
	 *                  which it is by default, then add the page to the RSHiscores error tracking category.
	 * @param Exception $previous Exception that caused this exception. Unused by RSHiScores.
	 */
	public function __construct( Message $message, $code = 1, Exception $previous = null ) {
		parent::__construct( $message->inContentLanguage()->parse(), $code, $previous );
	}
}
