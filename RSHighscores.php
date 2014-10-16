<?php
/* This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

$wgExtensionCredits['parserhook'][] = array(
	'path'			=> __FILE__,
	'name'			=> 'RSHiscores',
	'version'		=> '3.0.0-dev',
	'descriptionmsg'	=> 'rshiscores-desc',
	'url'			=> 'https://github.com/TehKittyCat/RSHiscores',
	'author'		=> '[http://runescape.wikia.com/wiki/User_talk:TehKittyCat TehKittyCat]',
);

$wgExtensionMessagesFiles['RSHiscores'] = __DIR__ . '/RSHighscores.i18n.php';
$wgExtensionMessagesFiles['RSHiscoresMagic'] = __DIR__ . '/RSHighscores.i18n.magic.php';

$wgHooks['ParserFirstCallInit'][] = 'wfHiscores';

# Set limit to prevent abuse, defaults to two
# which allows for comparison of hiscore data
if( !isset( $wgRSLimit ) ) {
	$wgRSLimit = 2;
}

# For tracking how many requests have been made
# for comparison to $wgRSLimit
$wgRSTimes = 0;

# Cache of hiscore fetches
$wgRSHiscoreCache = array();

/**
 * Setup parser function
 *
 * @param $parser Parser
 * @return bool
 */
function wfHiscores( &$parser ) {
	$parser->setFunctionHook( 'hs', 'wfHiscores_Render' );
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
function wfHiscores_Render( &$parser, $player = '', $skill = 0, $type = 1) {
	global $wgRSch, $wgRSHiscoreCache, $wgRSLimit, $wgRSTimes, $wgHTTPTimeout;

	$player = trim( $player );

	if( $player == '' ) {
		# No (display)name entered
		return 'A';

	} elseif ( array_key_exists( $player, $wgRSHiscoreCache ) ) {
		# get data from the cache
		$data = $wgRSHiscoreCache[$player];

		# Check to see if an error has already occurred, if so then return the error
		# otherwise will return wrong error and waste a bit of resource.
		# Checks first char as some errors have integer statuses.
		if ( ctype_alpha ( $data{0} ) ) {
			return $data;
		}

		$data = explode( "\n", rtrim($data), $skill + 2 );

		if ( !array_key_exists( $skill, $data ) ) {
			# Non-existant skill
			return 'F';
		}

		$data = explode( ',', $data[$skill], $type + 2 );

		if ( !array_key_exists( $type, $data ) ) {
			# Non-existant type
			return 'G';
		}

		return $data[$type];
	} elseif ( $wgRSTimes < $wgRSLimit || $wgRSLimit == 0 ) {
		$wgRSTimes++;

		if ( !isset( $wgRSch ) ) {
			# Setup cURL
			$wgRSch = curl_init();
			curl_setopt( $wgRSch, CURLOPT_TIMEOUT, $wgHTTPTimeout );
			curl_setopt( $wgRSch, CURLOPT_RETURNTRANSFER, TRUE );
		}

		# Other known working URL: 'http://hiscore.runescape.com/index_lite.ws?player='
		curl_setopt( $wgRSch, CURLOPT_URL, 'http://services.runescape.com/m=hiscore/index_lite.ws?player=' . urlencode( $player ) );

		if ( $data = curl_exec( $wgRSch ) ) {
			$wgRSHiscoreCache[$player] = $data;
			$status = curl_getinfo( $wgRSch, CURLINFO_HTTP_CODE );

			if ( $status == 200 ) {
				$data = $wgRSHiscoreCache[$player];
				$data = explode( "\n", $data, $skill + 2 );

				if ( !array_key_exists( $skill, $data ) ) {
					# Non-existant skill
					return 'F';
				}

				$data = explode( ',', $data[$skill], $type + 2 );

				if ( !array_key_exists( $type, $data ) ) {
					# Non-existant type
					return 'G';
				}

				return $data[$type];

			} elseif ( $status == 404 ) {
				# Non-existant player
				return $wgRSHiscoreCache[$player] = 'B';
			}

			# Unexpected HTTP status code
			return $wgRSHiscoreCache[$player] = 'D'.$status;
		}

		# An unhandled curl error occurred, report it.
		$errno = curl_errno ( $wgRSch );

		if( $errno ) {
			return $wgRSHiscoreCache[$player] = 'C'.$errno;
		}

		# Should be impossible, but odd things happen, so handle it.
		return $wgRSHiscoreCache[$player] = 'C';
	} else {
		# Parser function limit reached.
		return 'E';
	}
}

# @todo move this to documentation
## If A is returned, then no (display)name was entered.(Enter a username!)
## If B is returned, then the player could not be found.(HTTP 404)
## If C is returned, then an unknown error occurred.(Any response or lack there of HTTP 200/404)
## If C<#> is returned, then an unexpected error occurred, see the curl error codes for more information.(http://curl.haxx.se/libcurl/c/libcurl-errors.html)
## If D<#> is returned, then an unexpected HTTP status was returned, see the HTTP status codes for more information.(http://en.wikipedia.org/wiki/List_of_HTTP_status_codes)
## If E is returned, then the hiscores parser function limit was reached.(By default one, configurable with $wgRSLimit, limit is not affected by same username used repeatedly)
## If F is returned, then the skill does not exist.
## If G is returned, then the type does not exist.
## If anything else if returned, then it worked and that is the hiscores data.(Yay!)
