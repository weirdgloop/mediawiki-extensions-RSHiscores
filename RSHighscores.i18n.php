<?php
/**
 * Internationalisation file for Extension RSHiscores
 *
 * @file
 * @ingroup Extensions
 */

$messages = array();

$messages['qqq'] = array(
	'rshiscores-desc'		=> '{{desc}}',
	'rshiscores-error-category'	=> 'Category for tagging errors returned by the parser function.',
	'rshiscores-error-A'		=> 'Error message for missing player name.',
	'rshiscores-error-B'		=> 'Error message for a player that was not found in the RuneScape Hiscores',
	'rshiscores-error-C'		=> 'Error message for when a curl error occurs, where $1 is the number of the curl error.',
	'rshiscores-error-D'		=> 'Error message for when a HTTP error occurs, where $1 is the HTTP status.',
	'rshiscores-error-E'		=> 'Error message for when $wgRSLimit is exceeded.',
	'rshiscores-error-F'		=> 'Error message for when the skill requested does not exist.',
	'rshiscores-error-G'		=> 'Error message for when the type requested does not exist.',
	'rshiscores-error-H'		=> 'Error message for when the API requested does not exist.'
);

/**
 * English (English)
 */
$messages['en'] = array(
	'rshiscores-desc'		=> 'Provides access to RuneScape\'s Hiscores data for use in wikitext and calculators.',
	'rshiscores-error-category'	=> 'Pages with RSHiscores errors',
	'rshiscores-error-A'		=> 'Player name missing.',
	'rshiscores-error-B'		=> 'Player was not found in RuneScape\'s Hiscores.',
	'rshiscores-error-C'		=> 'Unexpected curl error returned: $1.',
	'rshiscores-error-D'		=> 'Unexpected HTTP status returned: $1.',
	'rshiscores-error-E'		=> 'Name call limit exceeded.',
	'rshiscores-error-F'		=> 'The skill requested does not exist.',
	'rshiscores-error-G'		=> 'The type requested does not exist.',
	'rshiscores-error-H'		=> 'Unexpected API type entered.'
);
