# RSHiscores

A MediaWiki extension that provides easy access to [RuneScape's Hiscores](http://services.runescape.com/m=hiscore/overview) for use in wikitext and JS calculators. Originally designed for the [RuneScape Wiki](http://runescape.wikia.com).

Issues and pull requests should filed [here](https://github.com/TehKittyCat/RSHiscores). However, the version used by [Wikia](http://www.wikia.com) (and the RuneScape Wiki) might be behind this version. For the version used by Wikia see [here](https://github.com/Wikia/app/tree/dev/extensions/3rdparty/RSHighscores). Alternatively, see [Special:Version](http://runescape.wikia.com/wiki/Special:Version) on RuneScape Wiki.

# Installation

1. Clone this repository to the extensions directory of your MediaWiki install.
2. Add the following to your `LocalSettings.php`
```php
// RSHiscores
require_once( 'extensions/RSHiscores/RSHighscores.php' );

/**
 * You may set $wgRSLimit in LocalSettings.php to adjust the maximum number of
 * names allowed to be called per page. Setting to 0 removes the limit.
 * If more than $wgRSLimit calls are made, then 'E' is returned for the name
 * calls over the limit. This example allows for 2 name calls to {{#hs}}.
 */
$wgRSLimit = 2;
```

# Usage

`{{#hs:API|Name|Skill|Type}}`
- `API` is the name of the hiscores API to get data from.
- `Name` is the name of the player to get data for.
- `Skill` is a number that refers to a skill or activity as found in the hiscores API, see [Skills](#skills) for valid values.
- `Type` is a number that referes to the type of data to return, see [Types](#types) for valid values.

If an error occurs, then an error code will be returned. See [Errors](#errors) for possible errors.

##API
| Name   | API                   |
| ------ | --------------------- |
| rs3    | RuneScape (Current)   |
| osrs   | Old School            |

## RS3 Skills
| Number | Skill/Activity                     |
| ------ | ---------------------------------- |
| -1     | Returns the raw data. (default)    |
| 0      | Overall                            |
| 1      | Attack                             |
| 2      | Defence                            |
| 3      | Strength                           |
| 4      | Constitution                       |
| 5      | Ranged                             |
| 6      | Prayer                             |
| 7      | Magic                              |
| 8      | Cooking                            |
| 9      | Woodcutting                        |
| 10     | Fletching                          |
| 11     | Fishing                            |
| 12     | Firemaking                         |
| 13     | Crafting                           |
| 14     | Smithing                           |
| 15     | Mining                             |
| 16     | Herblore                           |
| 17     | Agility                            |
| 18     | Thieving                           |
| 19     | Slayer                             |
| 20     | Farming                            |
| 21     | Runecrafting                       |
| 22     | Hunter                             |
| 23     | Construction                       |
| 24     | Summoning                          |
| 25     | Dungeoneering                      |
| 26     | Divination                         |
| 27     | Bounty Hunter                      |
| 28     | Bounty Hunter Rogue                |
| 29     | Dominion Tower                     |
| 30     | The Crucible                       |
| 31     | Castle Wars Games                  |
| 32     | B.A. Attackers                     |
| 33     | B.A. Defenders                     |
| 34     | B.A. Collectors                    |
| 35     | B.A. Healers                       |
| 36     | Duel Tournament                    |
| 37     | Mobilising Armies                  |
| 38     | Conquest                           |
| 39     | Fist of Guthix                     |
| 40     | GG: Resource Race                  |
| 41     | GG: Athletics                      |
| 42     | WE2: Armadyl Lifetime Contribution |
| 43     | WE2: Bandos Lifetime Contribution  |
| 44     | WE2: Armadyl PvP Kills             |
| 45     | WE2: Bandos PvP Kills              |
| 46     | Heist Guard Level                  |
| 47     | Heist Robber Level                 |
| 48     | CFP: 5 Game Average                |
| 49     | AF15: Cow tipping                  |
| 50     |  AF15: Rat kills after miniquest   |

## OSRS Skills
| Number | Skill/Activity                     |
| ------ | ---------------------------------- |
| -1     | Returns the raw data. (default)    |
| 0      | Overall                            |
| 1      | Attack                             |
| 2      | Defence                            |
| 3      | Strength                           |
| 4      | Constitution                       |
| 5      | Ranged                             |
| 6      | Prayer                             |
| 7      | Magic                              |
| 8      | Cooking                            |
| 9      | Woodcutting                        |
| 10     | Fletching                          |
| 11     | Fishing                            |
| 12     | Firemaking                         |
| 13     | Crafting                           |
| 14     | Smithing                           |
| 15     | Mining                             |
| 16     | Herblore                           |
| 17     | Agility                            |
| 18     | Thieving                           |
| 19     | Slayer                             |
| 20     | Farming                            |
| 21     | Runecrafting                       |
| 22     | Hunter                             |
| 23     | Construction                       |
| 24     | Clue scrolls                       |
| 25     | Bounty Hunter Rogue                |
| 26     | Bounty Hunter                      |

## Types
| Number | Type                  |
| ------ | --------------------- |
| 0      | Rank                  |
| 1      | Level/Score (default) |
| 2      | Experience*            |

\* Experience only applies to skill levels.

## Errors
If there is an error in the usage or request, one of the following codes will be returned instead.

## Errors
If there is an error in the usage or request, a message describing the error will be returned instead.

| Error No. | Message | Details |
| --------- | ------- | ------- |
| 1         | Player name missing. | No name was entered into the parser function usage. |
| 2         | Player was not found in RuneScape's Hiscores. | The requested player could not be found in the hiscores. |
| 3         | Unexpected cURL error returned: $1. | A cURL error occurred. See [here](http://curl.haxx.se/libcurl/c/libcurl-errors.html) for more details.
| 4         | Unexpected HTTP status returned: $1. | A HTTP status that was not 200 or 404 was returned. See [here](http://en.wikipedia.org/wiki/List_of_HTTP_status_codes) for more details.
| 5         | Name call limit exceeded. | The maximum number of players per page was exceeded, as defined by `$wgRSLimit`. |
| 6         | The skill requested does not exist. | <foo> |
| 7         | The type requested does not exist. | <foo> |
| 8         | Unexpected API type entered. | The API type entered was not recognised. See [above](#API) for valid types. |
| 9         | Timeout error returned. All requests are temporarily prevented. | A timeout error occurred, normally caused by too many requests being submitted in too short a time. This causes all requests to be prevented for a cooldown period of 15 minutes, at which point requests can be resumed. |
