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

# Only execute extension through MediaWiki
if ( !defined( 'MEDIAWIKI' ) ) die();

# Define a setup function
$wgHooks['ParserFirstCallInit'][] = 'wfHiscores';
$wgExtensionCredits['parserhook'][] = array(
    'name' => 'RSHiscores',
    'version' => '2.0',
    'description' => 'A parser function returning raw player data from RuneScape\'s Hiscores Lite',
    'url' => 'http://runescape.wikia.com/wiki/User:Catcrewser/RSHighscores',
    'author' => '[http://runescape.wikia.com/wiki/User_talk:Catcrewser TehKittyCat]'
);

# Set limit to prevent abuse, defaults to two, which allows for comparison of hiscore data
if( !isset( $wgRSLimit ) ) $wgRSLimit = 2;
$wgRSTimes = 0;

# Cache of hiscore fetches
$wgRSHiscoreCache = array();

# Initialise the parser function
$wgHooks['LanguageGetMagic'][] = 'wfHiscores_Magic';

# Setup parser function 
function wfHiscores( &$parser ) {
    $parser->setFunctionHook( 'hiscore', 'wfHiscores_Render' );
	 return true;
}

# Parser function
function wfHiscores_Magic( &$magicWords ) {
    $magicWords['hiscore'] = array( 0, 'hiscore' );
    return true;
}

# Function for the parser function
function wfHiscores_Render( &$parser, $player = '' ) {
    global $wgRSch, $wgRSHiscoreCache, $wgRSLimit, $wgRSTimes;
    $player = trim( $player );
    if( $player == '' ) {
    	return 0;
    } elseif( array_key_exists( $player, $wgRSHiscoreCache ) ) {
        return $wgRSHiscoreCache[$player];
    } elseif( $wgRSTimes < $wgRSLimit || $wgRSLimit == 0 ) {
        $wgRSTimes++;
        if( !isset( $wgRSch ) ) {
	        # Setup cURL
			$wgRSch = curl_init();
			curl_setopt( $wgRSch, CURLOPT_TIMEOUT, $wgHTTPTimeout );
			curl_setopt( $wgRSch, CURLOPT_RETURNTRANSFER, TRUE );
	    }
        curl_setopt( $wgRSch, CURLOPT_URL, 'http://services.runescape.com/m=hiscore/index_lite.ws?player='.urlencode( $player ) );
        if( $data = curl_exec( $wgRSch ) ) {
            $status = curl_getinfo( $wgRSch, CURLINFO_HTTP_CODE );
            if( $status == 200 ) {
                return $wgRSHiscoreCache[$player] = trim( $data );
            } elseif( $status == 404 ) {
                return $wgRSHiscoreCache[$player] = 1;
            }
        }
        return $wgRSHiscoreCache[$player] = 2;
    } else {
        return 3;
    }
}
## If 0 is returned, then no name was entered.(Enter a username!)
## If 1 is returned, then the player could not be found.(HTTP 404)
## If 2 is returned, then an error occurred.(Any response or lack there of HTTP 200/404)
## If 3 is returned, then the hiscores parser function limit was reached.(By default one, configurable with $wgRSLimit, limit is not affected by same username used repeatedly)
## If anything else if returned, then it worked and that is the hiscores data.(Yay!)
