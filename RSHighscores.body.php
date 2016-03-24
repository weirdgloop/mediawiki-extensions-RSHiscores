<?php
/**
 * <doc>
 */

class RSHiscores {
	public static $ch = null;
	public static $cache = [];
	public static $times = 0;

	/**
	 * Setup parser function
	 *
	 * @param Parser $parser
	 * @return bool
	 */
	public static function register( &$parser ) {
		$parser->setFunctionHook( 'hs', 'RSHiscores::renderHiscores' );
		return true;
	}

	/**
	 * Retrieve the raw hiscores data from RuneScape.
	 *
	 * @param string $hs Which hiscores API to retrieve from.
	 * @param string $player Player's display name.
	 * @return string Raw hiscores data
	 */
	private static function retrieveHiscores( $hs, $player ) {
		global $wgRSTimeout;

		if ( $hs === 'rs3' ) {
			$url = 'http://services.runescape.com/m=hiscore/index_lite.ws?player=';
		} elseif ( $hs === 'osrs' ) {
			$url = 'http://services.runescape.com/m=hiscore_oldschool/index_lite.ws?player=';
		} else {
			// unknown or unsupported hiscores API
			return 'H';
		}

		// setup the cURL handler if not previously initialised
		if ( self::$ch === null ) {
			self::$ch = curl_init();

			curl_setopt( self::$ch, CURLOPT_TIMEOUT, $wgRSTimeout );
			curl_setopt( self::$ch, CURLOPT_RETURNTRANSFER, true );
		}

		$url .= urlencode( $player );
		curl_setopt( self::$ch, CURLOPT_URL, $url );

		$data = curl_exec( self::$ch );

		if ( $data ) {
			$status = curl_getinfo( self::$ch, CURLINFO_HTTP_CODE );

			if ( $status === 200 ) {
				return $data;
			}

			if ( $status === 404 ) {
				// the player could not be found
				return 'B';
			}

			// unexpected HTTP status code
			return 'D' . $status;
		}

		// unexpected curl error occurred
		$ret = 'C';
		$errno = curl_errno( self::$ch );

		// should be impossible for this to fail
		// but just in case
		if ( $errno ) {
			$ret .= $errno;
		}


		return $ret;
	}

	/**
	 * Lookup hiscores data from object cache before retrieving from the site.
	 *
	 * @param string $hs Which hiscores API to retrieve from.
	 * @param string $player Player's display name.
	 * @return string Raw hiscores data
	 */
	private static function lookupHiscores( $hs, $player ) {
		global $wgMemc;

		$key = wfMemcKey( 'rshiscores', $player, $hs );
		$blockedKey = wfMemcKey( 'rshiscores-blocked' );

		$data = $wgMemc->get( $resKey );

		// couldn't find in the cache, so get it from the API
		if ( $data === false ) {
			// check to see if we've had a blocked request recently before trying
			if ( $wgMemc->get( $blockedKey ) === false ) {
				$data = self::retrieveHiscores( $hs, $player );

				// request failed, so no requests for 15 min
				if ( $data === 'C28' ) {
					$wgMemc->set( $blockedKey, true, 60 * 15 );

					// convert to a more descriptive message
					$data = 'I';
				}

				$wgMemc->set( $key, $data, 60 );
			} else {
				// previous request failed
				$data = 'I';
			}
		}

		return $data;
	}

	/**
	 * Parse the hiscores data.
	 *
	 * @param string $data
	 * @param int $skill Index representing the requested skill.
	 * @param int $type Index representing the requested type of data for the given skill.
	 * @return string Requested portion of the hiscores data.
	 */
	private static function parseHiscores( $data, $skill, $type ) {
		// check to see if an error has already occurred and return it if so
		// some errors have int statuses, so only check first char
		if ( ctype_alpha( $data{0} ) ) {
			return $data;
		}

		$data = explode( '\n', $data, $skill + 2 );

		if ( !array_key_exists( $skill, $data ) ) {
			// skill does not exist.
			return 'F';
		}

		$data = explode( ',', $data[$skill], $type + 2 );

		if ( !array_key_exists( $type, $data ) ) {
			// type does not exist.
			return 'G';
		}

		return $data[$type];
	}

	/**
	 * Attempt to lookup hiscore data in the cache, or looks it up in the API if not found.
	 *
	 * @param string $hs Which hiscores API to use.
	 * @param string $player Player's display name. Can not be empty.
	 * @param int $skill Index representing the requested skill. Leave as -1 for requesting the
	 *     raw data.
	 * @param int $type Index representing the requested type of data for the given skill.
	 * @return string
	 */
	private static function getHiscores( $hs, $player, $skill, $type ) {
		global $wgRSLimit;

		if ( $hs !== 'rs3' && $hs !== 'osrs' ) {
			// Unknown or unsupported hiscores API.
			return 'H';
		}

		$player = trim( $player );

		if( $player === '' ) {
			// No name was entered.
			return 'A';

		}

		if ( array_key_exists( $hs, self::$cache ) && array_key_exists( $player, self::$cache[$hs] ) ) {
			// Get the hiscores data from the cache.
			$data = self::$cache[$hs][$player];

		} elseif ( self::$times < $wgRSLimit || $wgRSLimit === 0 ) {
			// Update the name limit counter.
			self::$times++;

			// Lookup the hiscores data from the object cache,
			// if not found, then retrieve the data from the site.
			$data = self::lookupHiscores( $hs, $player );

			// Escape the result as it's from an external API.
			$data = htmlspecialchars( $data, ENT_QUOTES );

			// Add the hiscores data to the cache.
			self::$cache[$hs][$player] = $data;

			// If blocked, then cache for only 15 minutes.
			if ( $data === 'I' ) {
				$output = $parser->getOutput();

				if ( $output->isCacheable() && $output->getCacheExpiry() > 60 * 15 ) {
					$output->updateCacheExpiry( 60 * 15 );
				}
			}

		} else {
			// The name limit set by $wgRSLimit was reached.
			return 'E';
		}

		// Finally, return the raw string for use in JS calcs,
		// or if requested, parse the hiscores data.
		if ( $skill < 0 ) {
			return $data;
		}

		return self::parseHiscores( $data, $skill, $type );
	}

	/**
	 * Gets requested hiscore data and handles any returned error codes.
	 *
	 * @param Parser $parser
	 * @param string $hs Which hiscores API to use.
	 * @param string $player Player's display name. Can not be empty.
	 * @param int $skill Index representing the requested skill. Leave as -1 for requesting the
	 *     raw data.
	 * @param int $type Index representing the requested type of data for the given skill.
	 * @return string
	 */
	public static function renderHiscores( &$parser, $hs = 'rs3', $player = '', $skill = -1, $type = 1 ) {
		$ret = self::getHiscores( $hs, $player, $skill, $type );
		$first = $ret{0};

		if ( ctype_alpha( $first ) ) {
			$parser->addTrackingCategory( 'rshiscores-error-category' );
			$msg = wfMessage( 'rshiscores-error-' . $first );

			// pass any error codes to the returned message as parameters
			if ( strlen( $ret ) > 1 ) {
				$msg = $msg->params( substr( $ret, 1 ) )
			}

			$msg = $msg->parse();

			// return an error format compatible with #iferror
			return '<span class="error">' . $msg . '</span>';
		}

		return $ret;
	}
}
