<?php
/**
 * RSHiScores, a MediaWiki extension for providing access to RuneScape's HiScores data on the RuneScape Wiki.
 * Copyright (C) 2010-2018 TehKittyCat
 *
 * SPDX-License-Identifier: GPL-3.0+
 *
 * Main code for the RSHiScores extension.
 */

namespace MediaWiki\Extension\RSHiScores;

use MediaWiki\MediaWikiServices;
use Parser;
use Status;

class RSHiScores {
	public static $cache = [];
	public static $times = 0;
	private const BLOCKED_TIMEOUT = 15 * 60;
	private const ERROR_PREVIOUS = 1;
	private const ERROR_SKIPPABLE = 2;
	private const ERROR_SUPPRESS_CATEGORY = 3;
	private const DEFAULT_TYPE = 'auto';

	/**
	 * Store when we've been blocked in cache to prevent other requests from going out.
	 */
	private static function setBlocked() {
		$objcache = MediaWikiServices::getInstance()->getLocalServerObjectCache();
		$key = $objcache->makeKey( 'rshiscores', 'blocked' );

		$objcache->set( $key, 1, self::BLOCKED_TIMEOUT );
	}

	/**
	 * Check if we're currently blocked.
	 *
	 * @return bool Whether we're blocked or not.
	 */
	private static function isBlocked() {
		$objcache = MediaWikiServices::getInstance()->getLocalServerObjectCache();
		$key = $objcache->makeKey( 'rshiscores', 'blocked' );

		return $objcache->get( $key ) === 1;
	}

	/**
	 * Get the URL for the given API.
	 *
	 * @param string $api Which HiScores API to check.
	 * @param string $playerName Which player to look for
	 * @param string $extension Which type of endpoint to use ('ws' or 'json')
	 *
	 * @return string The HiScores URL to use.
	 *
	 * @throws Exception on error.
	 */
	private static function getUrl( $api, $playerName, $extension ) {
		switch ( $api ) {
			case 'rs3':
				$url = 'https://secure.runescape.com/m=hiscore/index_lite.' . $extension . '?player=';
				break;
			case 'rs3-ironman':
				$url = 'https://secure.runescape.com/m=hiscore_ironman/index_lite.' . $extension . '?player=';
				break;
			case 'rs3-hardcore':
				$url = 'https://secure.runescape.com/m=hiscore_hardcore_ironman/index_lite.' . $extension . '?player=';
				break;
			case 'osrs':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool/index_lite.' . $extension . '?player=';
				break;
			case 'osrs-ironman':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_ironman/index_lite.' . $extension . '?player=';
				break;
			case 'osrs-hardcore':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_hardcore_ironman/index_lite.' . $extension . '?player=';
				break;
			case 'osrs-ultimate':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_ultimate/index_lite.' . $extension . '?player=';
				break;
			case 'osrs-deadman':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_deadman/index_lite.' . $extension . '?player=';
				break;
			case 'osrs-seasonal':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_seasonal/index_lite.' . $extension . '?player=';
				break;
			case 'osrs-tournament':
				$url = 'https://secure.runescape.com/m=hiscore_oldschool_tournament/index_lite.' . $extension . '?player=';
				break;
			default:
				// Error: Unknown API. Should never be reached, because it is already checked in self::lookup().
				throw new Exception( wfMessage( 'rshiscores-error-unknown-api' ) );
		}

		return $url . urlencode( $playerName );
	}

	/**
	 * Fetch the raw HiScores data from RuneScape.
	 *
	 * @param string $api Which HiScores API to fetch from.
	 * @param string $player Player's display name.
	 *
	 * @return string Raw HiScores data.
	 *
	 * @throws Exception on error.
	 */
	private static function fetch( $url ) {
		global $wgCanonicalServer;

		if ( self::isBlocked() ) {
			throw new Exception( wfMessage( 'rshiscores-error-request-failed' ) );
		}

		$http = MediaWikiServices::getInstance()->getHttpRequestFactory();

		// Be a good netizen by including the extension name and wiki server URL in the user agent.
		$options = ['userAgent' => $http->getUserAgent() . " (RSHiScores: $wgCanonicalServer)"];

		// Fetch the HiScores.
		$req = $http->create( $url, $options, __METHOD__ );
		$reqStatus = $req->execute();
		$httpStatus = $req->getStatus();

		// Return the HiScores data or the error that occurred.
		if ( $httpStatus === 200 ) {
			// Player data was returned.
			return trim( $req->getContent() );
		} elseif ( $httpStatus === 404 ) {
			// Error: Player does not exist.
			throw new Exception( wfMessage( 'rshiscores-error-unknown-player', $player ), self::ERROR_SUPPRESS_CATEGORY );
		} else {
			// Log request failures.
			if ( $reqStatus->isOK() ) {
				wfDebugLog( 'rshiscores', "Requested '$url'. Returned HTTP status code '$httpStatus' instead." );
			} else {
				wfDebugLog( 'rshiscores', "Requested '$url'. Returned Error: " . Status::wrap( $reqStatus )->getWikitext() );
			}

			// Assume we've been temporarily blocked so prevent requests for the next 15 minutes.
			self::setBlocked();

			// Error: Request failed.
			throw new Exception( wfMessage( 'rshiscores-error-request-failed' ) );
		}
	}

	/**
	 * Parse the HiScores data from WS endpoint.
	 *
	 * @param string $data Raw HiScores data.
	 * @param int $skill Index representing the requested skill.
	 * @param int $type Index representing the requested type of data for the given skill.
	 *
	 * @return string Requested potion of the RSHiScores data.
	 *
	 * @throws Exception on error.
	 */
	private static function parseWS( $data, $skill, $type ) {
		if ( $type === self::DEFAULT_TYPE ) {
			$type = '1';
		}
		$data = explode( "\n", $data, $skill + 2 );

		if ( !array_key_exists( $skill, $data ) ) {
			// Error: Skill does not exist.
			throw new Exception( wfMessage( 'rshiscores-error-unknown-skill' ), self::ERROR_SKIPPABLE );
		}

		$data = explode( ',', $data[$skill], $type + 2 );

		if ( !array_key_exists( $type, $data ) ) {
			// Error: Type does not exist.
			throw new Exception( wfMessage( 'rshiscores-error-unknown-type' ), self::ERROR_SKIPPABLE );
		}

		return $data[$type];
	}

	/**
	 * Attempt to lookup hiscore data in the cache, or looks it up in the API if not found.
	 *
	 * @param string $api Which HiScores API to use.
	 * @param string $player Player's display name. Can not be empty.
	 * @param string $skill Numeric index or plaintext string representing the requested skill. Leave as -1 for requesting the raw data.
	 * @param string $type Numeric index or plaintext string representing the requested type of data for the given skill.
	 * @param string $extension The endpoint extension to use. Either 'ws' or 'json'
	 * @param &string $cacheKey To be set before any error may occur, to be able to invalidate cache
	 *
	 * @return string parsed hiscore data
	 *
	 * @throws Exception on error.
	 */
	private static function lookup( $api, $player, $skill, $type, $extension, &$cacheKey ) {
		global $wgRSHiScoresNameLimit;

		$skillIsInt = filter_var( $skill, FILTER_VALIDATE_INT ) !== false;
		$giveEverything = $skillIsInt && $skill < 0;

		if ( $extension === 'auto' ) {
			$extension = $skillIsInt ? 'ws' : 'json';
		}

		$cacheKey = "$api.$extension";

		if ( !in_array( $extension, [ 'ws', 'json' ] ) ) {
			// Error: Other extension than 'ws' or 'json' was entered
			throw new Exception( wfMessage( 'rshiscores-error-invalid-extension' ) );

		} elseif ( $extension === 'ws' && !$skillIsInt ) {
			// Error: Requested extension was 'ws' and requested skill was not a number.
			throw new Exception( wfMessage( 'rshiscores-error-invalid-skill' ) );

		} elseif ( $extension === 'ws' && $type !== self::DEFAULT_TYPE && filter_var( $type, FILTER_VALIDATE_INT ) === false ) {
			// Error: Requested extension was 'ws' and requested type was not a number.
			throw new Exception( wfMessage( 'rshiscores-error-invalid-type' ) );
		}

		$player = trim( $player );

		if ( $player == '' ) {
			// Error: No player name was entered.
			throw new Exception( wfMessage( 'rshiscores-error-empty-rsn' ) );

		}


		// Ensure the API is recognised, and determine the URL for the requested HiScores.
		$apiUrl = self::getUrl( $api, $player, $extension );

		if ( array_key_exists( $cacheKey, self::$cache ) && array_key_exists( $player, self::$cache[$cacheKey] ) ) {
			// Get the HiScores data from the cache.
			$data = self::$cache[$cacheKey][$player];

			if ( empty( $data ) ) {
				// Error: See previous error.
				throw new Exception( wfMessage( 'rshiscores-error-previous' ), self::ERROR_PREVIOUS );
			}

		} elseif ( self::$times < $wgRSHiScoresNameLimit || $wgRSHiScoresNameLimit <= 0 ) {
			// Update the name limit counter.
			self::$times++;

			// Get the HiScores data from the site.
			$data = self::fetch( $apiUrl );

			// Do some reformatting & html escaping of the received data
			$data = self::postFetch( $data, $extension );

			// Add the HiScores data to the cache.
			self::$cache[$cacheKey][$player] = $data;
		} else {
			// Error: The name limit set by $wgRSHiScoresNameLimit was exceeded.
			throw new Exception( wfMessage( 'rshiscores-error-exceeded-limit', $wgRSHiScoresNameLimit ) );
		}

		// Finally, return the raw string for use in JS calcs,
		// or if requested, parse the HiScores data.
		if ( $giveEverything ) {
			if ( $extension === 'ws' ) {
				return $data;
			}
			return json_encode( $data );
		} else {
			if ( $extension === 'ws' ) {
				return self::parseWS( $data, $skill, $type );
			}
			return self::getFromJson( $data, $skill, $type );
		}
	}

	/**
	 * Do some post-processing of the data from the endpoint.
	 * Most notably html-escape data from untrusted API
	 *
	 * @param string $data The received data
	 * @param string $extension The endpoint used. If 'json', will decode it and do some further processing.
	 * @return array|string The processed data
	 */
	private static function postFetch( $data, $extension ) {
		if ( $extension === 'ws' ) {
			return self::escapeStrings( $data );
		}

		// $extension is 'json'
		$data = json_decode( $data, true );
		if ( !is_array( $data ) ) {
			// Error: Endpoint returned invalid json
			throw new Exception( wfMessage( 'rshiscores-error-invalid-json' ) );
		}

		// Index all skills/activities in flat array by lowercase key, for easy lookup.
		$parsedData = [];
		foreach ( $data as $skillOrActivity => $stats ) {
			foreach ( $stats as $stat ) {
				if ( isset( $stat['name'] ) ) {
					$parsedData[ self::escapeStrings( strtolower( $stat[ 'name' ] ) ) ] = self::escapeStrings( $stat );
				}
			}
		}

		return $parsedData;
	}

	/**
	 * General-purpose html-escaper.
	 * Recurses into an array and escapes all keys and scalar values encountered.
	 *
	 * @param array|string $arrayOrString The string to escape.
	 * @return array|string The escaped result.
	 */
	private static function escapeStrings( $arrayOrString ) {
		if ( is_scalar( $arrayOrString ) ) {
			return htmlspecialchars( $arrayOrString, ENT_QUOTES );
		}
		if ( is_array( $arrayOrString ) ) {
			$rtr = [];
			foreach ( $arrayOrString as $key => $value ) {
				$rtr[ self::escapeStrings( $key ) ] = self::escapeStrings( $value );
			}
			return $rtr;
		}
		// This should not happen
		throw new Exception( 'rshiscores-error-unexpected-json' );
	}

	/**
	 * Get data for specific skill and type from the data.
	 *
	 * @param array $data The data fetched from the endpoint, and processed by self::postFetch
	 * @param string $skill The skill to search for in the data.
	 * @param string $type The type (xp/rank/score/level/self::DEFAULT_TYPE) of data to get for the skill.
	 *
	 * @return string The requested data
	 *
	 * @throws Exception If $skill or $type could not be found, or if endpoint returned unexpected results
	 */
	private static function getFromJson( $data, $skill, $type ) {
		// Case-insensitive, use same processing as self::postFetch did
		$skill = self::escapeStrings( strtolower( $skill ) );
		if ( !isset( $data[ $skill ] ) ) {
			// Error: Skill/activity is unknown. Maybe they changed the spelling?
			throw new Exception( wfMessage( 'rshiscores-error-unknown-skill' ) );
		}
		if ( $type === self::DEFAULT_TYPE ) {
			if ( isset( $data[ $skill ][ 'level' ] ) ) {
				$type = 'level';
			} else {
				$type = 'score';
			}
		}
		if ( !isset( $data[ $skill ][ $type ] ) ) {
			// Error: Type is unknown. Maybe you asked xp for an activity, or vise-versa?
			throw new Exception( wfMessage( 'rshiscores-error-unknown-type' ) );
		}
		if ( !is_scalar( $data[ $skill ][ $type ] ) ) {
			// Error: Endpoint did not give a scalar as result. Should not happen.
			throw new Exception( wfMessage( 'rshiscores-error-unexpected-value' ) );
		}
		return $data[ $skill ][ $type ];
	}

	/**
	 * Gets requested hiscore data and handles any returned error codes.
	 *
	 * @param Parser &$parser
	 * @param string $api Which HiScores API to use.
	 * @param string $player Player's display name. Can not be empty.
	 * @param string $skill Numeric index or plaintext string representing the requested skill or activity. Leave as -1 for requesting the raw data.
	 * @param string $type Numeric index or plaintext string representing the requested type of data for the given skill or activity.
	 * @param string $extension Type of endpoint to use. Either 'ws' or 'json' or 'auto'
	 *
	 * @return string
	 */
	public static function render( Parser &$parser, $api = 'rs3', $player = '', $skill = '-1', $type = self::DEFAULT_TYPE, $extension = 'auto' ) {
		// cacheKey is passed by reference to be able to set invalid cache on failure with correct cache key
		$cacheKey = $api;
		try {
			$ret = self::lookup( $api, $player, $skill, $type, $extension, $cacheKey );
		} catch ( Exception $e ) {
			$errCode = $e->getCode();

			// Only add the exception to the RSHiScores error tracking category if it's wanted.
			if ( $errCode != self::ERROR_PREVIOUS && $errCode != self::ERROR_SUPPRESS_CATEGORY ) {
				$parser->addTrackingCategory( 'rshiscores-error-category' );
			}

			// If the error would repeat itself, signal to future calls to error out early.
			if ( $errCode != self::ERROR_SKIPPABLE ) {
				self::$cache[$cacheKey][$player] = '';
			}

			// Return an error format compatible with #iferror.
			$ret = '<span class="error">' . $e->getMessage() . '</span>';
		}

		return $ret;
	}
}
