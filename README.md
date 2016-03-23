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

## Skills
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
| 27     | Invention                          |
| 28     | Bounty Hunter                      |
| 29     | Bounty Hunter Rogue                |
| 30     | Dominion Tower                     |
| 31     | The Crucible                       |
| 32     | Castle Wars Games                  |
| 33     | B.A. Attackers                     |
| 34     | B.A. Defenders                     |
| 35     | B.A. Collectors                    |
| 36     | B.A. Healers                       |
| 37     | Duel Tournament                    |
| 38     | Mobilising Armies                  |
| 39     | Conquest                           |
| 40     | Fist of Guthix                     |
| 41     | GG: Resource Race                  |
| 42     | GG: Athletics                      |
| 43     | WE2: Armadyl Lifetime Contribution |
| 44     | WE2: Bandos Lifetime Contribution  |
| 45     | WE2: Armadyl PvP Kills             |
| 46     | WE2: Bandos PvP Kills              |
| 47     | Heist Guard Level                  |
| 48     | Heist Robber Level                 |
| 49     | CFP: 5 Game Average                |

## Types
| Number | Type                  |
| ------ | --------------------- |
| 0      | Rank                  |
| 1      | Level/Score (default) |
| 2      | Experience            |

## Errors
If there is an error in the usage or request, one of the following codes will be returned instead.

| Code | Error                                                                                                                                  |
| ---- | -------------------------------------------------------------------------------------------------------------------------------------- |
| A    | No name was entered.                                                                                                                   |
| B    | The player could not be found.                                                                                                         |
| C<#> | A curl error occurred, if it's form of C<#>, check the number [here](http://curl.haxx.se/libcurl/c/libcurl-errors.html) for the cause. |
| D<#> | An unexpected HTTP status was returned, check the number [here](http://en.wikipedia.org/wiki/List_of_HTTP_status_codes) for the cause. |
| E    | The name call limit was reached. This is by default 2 player names. This is not a limit on the number of function calls.               |
| F    | The skill does not exist.                                                                                                              |
| G    | The type does not exist.                                                                                                               |
| H    | The API is unknown or unsupported.                                                                                                     |
