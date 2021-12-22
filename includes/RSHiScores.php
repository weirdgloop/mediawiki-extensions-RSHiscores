<?php
/**
 * RSHiscores, a MediaWiki extension for providing access to RuneScape's HiScores data on the RuneScape Wiki.
 * Copyright (C) 2010-2018 TehKittyCat
 *
 * SPDX-License-Identifier: GPL-3.0+
 *
 * Main code for the RSHiScores extension.
 */

use MediaWiki\MediaWikiServices;

class RSHiScores {
	public static $cache = [];
	public static $times = 0;
	private const BLACKLIST_TIMEOUT = 15 * 60;
	private const ERROR_PREVIOUS = 1;
	private const ERROR_SKIPPABLE = 2;
	private const ERROR_SUPPRESS_CATEGORY = 3;


	/**
	 * Store when we've been blacklisted in memcached to prevent other requests going out.
	 */
	private static function setBlacklisted() {
		$objcache = MediaWikiServices::getInstance()->getLocalServerObjectCache();
		$key = $objcache->makeKey( 'rshiscores', 'blacklisted' );

		$objcache->set( $key, 1, self::BLACKLIST_TIMEOUT );
	}

	/**
	 * Check if we're currently blacklisted.
	 *
	 * @return bool Whether we're blacklisted or not.
	 */
	private static function isBlacklisted() {
		$objcache = MediaWikiServices::getInstance()->getLocalServerObjectCache();
		$key = $objcache->makeKey( 'rshiscores', 'blacklisted' );

		//return $objcache->get( $key ) !== NULL;
		return false; // TODO: Fix blacklisting.
	}

	/**
	 * Get the URL for the given API.
	 *
	 * @param string $hs Which HiScores API to check.
	 *
	 * @return string The HiScores URL to use.
	 *
	 * @throws RSHiScoresException on error.
	 */
	private static function getApi( $hs ) {
		switch ( $hs ) {
			case 'rs3':
				$url = 'https://secure.runescape.com/m=hiscore/index_lite.ws?player=';
				break;
			case 'rs3-ironman':
				$url = 'https://secure.runescape.com/m=hiscore_ironman/index_lite.ws?player=';
				break;
			case 'rs3-hardcore':
				$url = 'https://secure.runescape.com/m=hiscore_hardcore_ironman/index_lite.ws?player=';
				break;
			case 'osrs':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.ws?player=';
				break;
			case 'osrs-ironman':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_ironman/index_lite.ws?player=';
				break;
			case 'osrs-hardcore':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_hardcore_ironman/index_lite.ws?player=';
				break;
			case 'osrs-ultimate':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_ultimate/index_lite.ws?player=';
				break;
			case 'osrs-deadman':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_deadman/index_lite.ws?player=';
				break;
			case 'osrs-seasonal':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_seasonal/index_lite.ws?player=';
				break;
			case 'osrs-tournament':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_tournament/index_lite.ws?player=';
				break;
			default:
				// Error: Unknown API. Should never be reached, because it is already checked in getHiScores.
				throw new RSHiScoresException( wfMessage( 'rshiscores-error-unknown-api' ) );
		}

		return $url;
	}

	/**
	 * Retrieve the raw HiScores data from RuneScape.
	 *
	 * @param string $hs Which HiScores API to retrieve from.
	 * @param string $player Player's display name.
	 *
	 * @return string Raw HiScores data.
	 *
	 * @throws RSHiScoresException on error.
	 */
	private static function retrieveHiScores( $hs, $player ) {
		global $wgCanonicalServer;

		// Determine the URL for the requested HiScores.
		$url = self::getApi( $hs ) . urlencode( $player );

		if ( self::isBlacklisted() ) {
			throw new RSHiScoresException( wfMessage( 'rshiscores-error-request-failed' ) );
		}

		// Be a good netizen by including the extension name and wiki server URL in the user agent.
		$options = ['userAgent' => Http::userAgent() . " (RSHiScores: $wgCanonicalServer)"];

		// Retrieve the HiScores.
		$req = MediaWikiServices::getInstance()->getHttpRequestFactory()->create( $url, $options, __METHOD__ );
		$reqStatus = $req->execute();
		$httpStatus = $req->getStatus();

		// Return the HiScores data or the error that occurred.
		if ( $httpStatus === 200 ) {
			// Player data was returned.
			return trim( $req->getContent() );
		} elseif ( $httpStatus === 404 ) {
			// Error: Player does not exist.
			throw new RSHiScoresException( wfMessage( 'rshiscores-error-unknown-player', $player ), self::ERROR_SUPPRESS_CATEGORY );
		} else {
			// Log request failures.
			if ( $reqStatus->isOK() ) {
				wfDebugLog( 'rshiscores', "Requested '$url'. Returned HTTP status code '$httpStatus' instead." );
			} else {
				wfDebugLog( 'rshiscores', "Requested '$url'. Returned Error: " . Status::wrap( $reqStatus )->getWikitext() );
			}

			// Assume we've been temporarily blacklisted so prevent requests for the next 15 minutes
			self::setBlackListed();

			// Error: Request failed.
			throw new RSHiScoresException( wfMessage( 'rshiscores-error-request-failed' ) );
		}
	}

	/**
	 * Parse the HiScores data.
	 *
	 * @param string $data Raw HiScores data.
	 * @param int $skill Index representing the requested skill.
	 * @param int $type Index representing the requested type of data for the given skill.
	 *
	 * @return string Requested potion of the RSHiScores data.
	 *
	 * @throws RSHiScoresException on error.
	 */
	private static function parseHiScores( $data, $skill, $type ) {
		$data = explode( "\n", $data, $skill + 2 );

		if ( !array_key_exists( $skill, $data ) ) {
			// Error: Skill does not exist.
			throw new RSHiScoresException( wfMessage( 'rshiscores-error-unknown-skill' ), self::ERROR_SKIPPABLE );
		}

		$data = explode( ',', $data[$skill], $type + 2 );

		if ( !array_key_exists( $type, $data ) ) {
			// Error: Type does not exist.
			throw new RSHiScoresException( wfMessage( 'rshiscores-error-unknown-type' ), self::ERROR_SKIPPABLE );
		}

		return $data[$type];
	}

	/**
	 * Attempt to lookup hiscore data in the cache, or looks it up in the API if not found.
	 *
	 * @param string $hs Which HiScores API to use.
	 * @param string $player Player's display name. Can not be empty.
	 * @param int $skill Index representing the requested skill. Leave as -1 for requesting the raw data.
	 * @param int $type Index representing the requested type of data for the given skill.
	 *
	 * @return string
	 *
	 * @throws RSHiScoresException on error.
	 */
	private static function getHiScores( $hs, $player, $skill, $type ) {
		global $wgRSHiScoresNameLimit;

		// Ensure the API is recognised
		self::getApi( $hs );

		$player = trim( $player );

		if( $player == '' ) {
			// Error: No player name was entered.
			throw new RSHiScoresException( wfMessage( 'rshiscores-error-empty-rsn' ) );

		} elseif ( filter_var( $skill, FILTER_VALIDATE_INT ) === false ) {
			// Error: Skill parameter must be a number.
			throw new RSHiScoresException( wfMessage( 'rshiscores-error-invalid-skill' ) );

		} elseif ( filter_var( $type, FILTER_VALIDATE_INT ) === false ) {
			// Error: Type parameter must be a number.
			throw new RSHiScoresException( wfMessage( 'rshiscores-error-invalid-type' ) );

		} elseif ( array_key_exists( $hs, self::$cache ) && array_key_exists( $player, self::$cache[$hs] ) ) {
			// Get the HiScores data from the cache.
			$data = self::$cache[$hs][$player];

			if ( $data === '' ) {
				// Error: See previous error.
				throw new RSHiScoresException( wfMessage( 'rshiscores-error-previous' ), self::ERROR_PREVIOUS );
			}

		} elseif ( self::$times < $wgRSHiScoresNameLimit || $wgRSHiScoresNameLimit <= 0 ) {
			// Update the name limit counter.
			self::$times++;

			// Get the HiScores data from the site.
			$data = self::retrieveHiScores( $hs, $player );

			// Escape the result as it's from an external API.
			$data = htmlspecialchars( $data, ENT_QUOTES );

			// Add the HiScores data to the cache.
			self::$cache[$hs][$player] = $data;
		} else {
			// Error: The name limit set by $wgRSHiScoresNameLimit was exceeded.
			throw new RSHiScoresException( wfMessage( 'rshiscores-error-exceeded-limit', $wgRSHiScoresNameLimit ) );
		}

		// Finally, return the raw string for use in JS calcs,
		// or if requested, parse the HiScores data.
		if ( $skill < 0 ) {
			return $data;
		} else {
			return self::parseHiScores( $data, $skill, $type );
		}
	}

	/**
	 * Gets requested hiscore data and handles any returned error codes.
	 *
	 * @param Parser &$parser
	 * @param string $hs Which HiScores API to use.
	 * @param string $player Player's display name. Can not be empty.
	 * @param int $skill Index representing the requested skill. Leave as -1 for requesting the raw data.
	 * @param int $type Index representing the requested type of data for the given skill.
	 *
	 * @return string
	 */
	public static function renderHiScores( Parser &$parser, $hs = 'rs3', $player = '', $skill = '-1', $type = '1' ) {
		try {
			$ret = self::getHiScores( $hs, $player, $skill, $type );
		} catch ( RSHiScoresException $e ) {
			$errCode = $e->getCode();

			// Only add the exception to the RSHiScores error tracking category if it's wanted.
			if ( $errCode != self::ERROR_PREVIOUS && $errCode != self::ERROR_SUPPRESS_CATEGORY ) {
				$parser->addTrackingCategory( 'rshiscores-error-category' );
			}

			// If the error would repeat itself, signal to future calls to error out early.
			if ( $errCode != self::ERROR_SKIPPABLE ) {
				self::$cache[$hs][$player] = '';
			}

			// Return an error format compatible with #iferror.
			$ret = '<span class="error">' . $e->getMessage() . '</span>';
		}

		return $ret;
	}
}
