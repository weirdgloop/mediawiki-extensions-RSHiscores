<?php
class RSHiscores {
	public static $ch = NULL;
	public static $cache = array();
	public static $times = 0;

	/**
	 * Setup parser function
	 *
	 * @param $parser Parser
	 * @return bool
	 */
	public static function register( &$parser ) {
		$parser->setFunctionHook( 'hs', 'RSHiscores::renderHiscores' );
		return true;
	}

	/**
	 * Retrieve the raw hiscores data from RuneScape.
	 *
	 * @param string $player Player's display name.
	 * @return string Raw hiscores data
	 */
	private static function retrieveHiscores( $player ) {
		global $wgHTTPTimeout;

		// Setup the cURL handler if not previously initialised.
		if ( self::$ch == NULL ) {
			wfDebugLog( 'RSHiscores', 'Initialised cURL handler.' );

			self::$ch = curl_init();
			curl_setopt( self::$ch, CURLOPT_TIMEOUT, $wgHTTPTimeout );
			curl_setopt( self::$ch, CURLOPT_RETURNTRANSFER, TRUE );
		}

		curl_setopt( self::$ch, CURLOPT_URL, 'http://services.runescape.com/m=hiscore/index_lite.ws?player=' . urlencode( $player ) );

		if ( $data = curl_exec( self::$ch ) ) {
			$status = curl_getinfo( self::$ch, CURLINFO_HTTP_CODE );

			if ( $status == 200 ) {
				return $data;
			} elseif ( $status == 404 ) {
				// The player could not be found.
				return 'B';
			}

			// An unexpected HTTP status code was returned, so report it.
			return 'D'.$status;
		}

		// An unexpected curl error occurred, so report it.
		$errno = curl_errno ( self::$ch );

		if( $errno ) {
			return 'C'.$errno;
		}

		// Should be impossible, but odd things happen, so handle it.
		return 'C';
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
		/*
		 * Check to see if an error has already occurred.
		 * If so, return the error now, otherwise the wrong error will be
		 * returned. Some errors have int statuses, so only check first char.
		 */
		if ( ctype_alpha ( $data{0} ) ) {
			return $data;
		}

		$data = explode( "\n", $data, $skill + 2 );

		if ( !array_key_exists( $skill, $data ) ) {
			// The skill does not exist.
			return 'F';
		}

		$data = explode( ',', $data[$skill], $type + 2 );

		if ( !array_key_exists( $type, $data ) ) {
			// The type does not exist.
			return 'G';
		}

		return $data[$type];
	}

	/**
	 * <doc>
	 *
	 * @param $parser Parser
	 * @param string $player Player's display name. Can not be empty.
	 * @param int $skill Index representing the requested skill. Leave as -1 for requesting the raw data.
	 * @param int $type Index representing the requested type of data for the given skill.
	 * @return string
	 */
	public static function renderHiscores( &$parser, $player = '', $skill = -1, $type = 1 ) {
		global $wgRSLimit;

		$player = trim( $player );

		if( $player == '' ) {
			// No name was entered.
			return 'A';

		} elseif ( array_key_exists( $player, self::$cache ) ) {
			wfDebugLog( 'RSHiscores', 'Retrieved cached hiscores data.' );

			// Get the hiscores data from the cache.
			$data = self::$cache[$player];

		} elseif ( self::$times < $wgRSLimit || $wgRSLimit == 0 ) {
			wfDebugLog( 'RSHiscores', 'Retrieved fresh hiscores data.' );

			// Update the name limit counter.
			self::$times++;

			// Get the hiscores data from the site.
			$data = self::retrieveHiscores( $player );

			// Add the hiscores data to the cache.
			self::$cache[$player] = $data;

		} else {
			// The name limit set by $wgRSLimit was reached.
			return 'E';
		}

		/*
		 * Finally, return the raw string for use in JS calcs,
		 * or if requested, parse the hiscores data.
		 */
		if ( $skill < 0 ) {
			return $data;
		} else {
			return self::parseHiscores( $data, $skill, $type );
		}
	}
}