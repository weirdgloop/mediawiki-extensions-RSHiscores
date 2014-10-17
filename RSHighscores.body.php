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
		$parser->setFunctionHook( 'hs', 'RSHiscores::render' );
		return true;
	}

	/**
	 * <doc>
	 *
	 * @param $parser Parser
	 * @param $player
	 * @param $skill
	 * @param $type
	 * @return string
	 * @todo Add support for returning the raw data
	 */
	public static function render( &$parser, $player = '', $skill = 0, $type = 1) {
		global $wgRSLimit, $wgHTTPTimeout;

		$player = trim( $player );

		if( $player == '' ) {
			// No name was entered.
			return 'A';

		} elseif ( array_key_exists( $player, self::$cache ) ) {
			wfDebugLog( 'RSHiscores', 'Cached hiscores data.' );
			// Get the hiscores data from the cache.
			$data = self::$cache[$player];

			/*
			 * Check to see if an error has already occurred, if so then return
			 * the error otherwise will return wrong error and waste a bit of
			 * resource. Checks first char as some errors have integer statuses.
			 */
			if ( ctype_alpha ( $data{0} ) ) {
				return $data;
			}

			$data = explode( "\n", rtrim($data), $skill + 2 );

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
		} elseif ( self::$times < $wgRSLimit || $wgRSLimit == 0 ) {
			self::$times++;

			// Setup the cURL handler if not previously initialised.
			if ( self::$ch == NULL ) {
				wfDebugLog( 'RSHiscores', 'Initialised cURL handler.' );
				self::$ch = curl_init();
				curl_setopt( self::$ch, CURLOPT_TIMEOUT, $wgHTTPTimeout );
				curl_setopt( self::$ch, CURLOPT_RETURNTRANSFER, TRUE );
			}

			curl_setopt( self::$ch, CURLOPT_URL, 'http://services.runescape.com/m=hiscore/index_lite.ws?player=' . urlencode( $player ) );

			if ( $data = curl_exec( self::$ch ) ) {
				self::$cache[$player] = $data;
				$status = curl_getinfo( self::$ch, CURLINFO_HTTP_CODE );

				if ( $status == 200 ) {
					$data = self::$cache[$player];
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

				} elseif ( $status == 404 ) {
					// The player could not be found.
					return self::$cache[$player] = 'B';
				}

				// An unexpected HTTP status code was returned, so report it.
				return self::$cache[$player] = 'D'.$status;
			}

			// An unexpected curl error occurred, so report it.
			$errno = curl_errno ( self::$ch );

			if( $errno ) {
				return self::$cache[$player] = 'C'.$errno;
			}

			// Should be impossible, but odd things happen, so handle it.
			return self::$cache[$player] = 'C';
		} else {
			// The name limit set by $wgRSLimit was reached.
			return 'E';
		}
	}
}