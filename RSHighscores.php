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

$wgExtensionCredits['parserhook'][] = [
	'path'			=> __FILE__,
	'name'			=> 'RSHiscores',
	'version'		=> '3.1.0',
	'descriptionmsg'	=> 'rshiscores-desc',
	'url'			=> 'https://github.com/rswiki/RSHiscores',
	'author'		=> [
		'[http://runescape.wikia.com/wiki/User_talk:TehKittyCat TehKittyCat]',
		'[http://runescape.wikia.com/wiki/User:Quarenon Quarenon]',
		'[http://runescape.wikia.com/wiki/User:Cqm Cqm]',
	],
];

$wgAutoloadClasses['RSHiscores'] = __DIR__ . '/RSHighscores.body.php';

$wgExtensionMessagesFiles['RSHiscores'] = __DIR__ . '/RSHighscores.i18n.php';
$wgExtensionMessagesFiles['RSHiscoresMagic'] = __DIR__ . '/RSHighscores.i18n.magic.php';

$wgHooks['ParserFirstCallInit'][] = 'RSHiscores::register';

$wgTrackingCategories[] = 'rshiscores-error-category';

/**
 * Defines the maximum number of names allowed per page to retrieve hiscores
 * data for. This is not a call limit. This is to prevent abuse, especially
 * since it is Wikia's bandwidth we are using. Defaults to two for side-by-side
 * comparisons. Set to 0 to disable this limit. On Wikia the limit is set to 2.
 */
$wgRSLimit = 2;

/**
 * Defines the amount of time in seonds allowed for each lookup before timing out.
 */
$wgRSTimeout = 5;
