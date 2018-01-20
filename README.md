# RSHiScores

A MediaWiki extension that provides easy access to [RuneScape's HiScores](http://services.runescape.com/m=hiscore/ranking) for use in wikitext and JS calculators on the [RuneScape Wiki](https://runescape.wiki).

# Installation

1. Clone this repository to the extensions directory of your MediaWiki install.
2. Add the following to your `LocalSettings.php`
```php
wfLoadExtension( 'RSHiScores' );

/**
 * Limit the number of calls to RuneScape's HiScores API. Set to 0 to remove limit.
 */
$wgRSHiScoresNameLimit = 2;
```

# Usage

`{{#hs:API|Name|Skill|Type}}`
- `API` is the name of the HiScores API to get data from.
- `Name` is the name of the player to get data for.
- `Skill` is a number that refers to a skill or activity as found in the HiScores API, See [RS3 Skills](#rs3-skills) or [OSRS Skills](#osrs-skills) for the known skill values.
- `Type` is a number that refers to the type of data to return, see [Types](#types) for valid values.

If an error occurs, then an error message will be returned. See [Errors](#errors) for possible errors.

##API
| Name   | API                   |
| ------ | --------------------- |
| rs3    | RuneScape             |
| osrs   | Old School RuneScape  |

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
| 50     | AF15: Cow tipping                  |
| 51     | AF15: Rat kills after miniquest    |

## OSRS Skills
| Number | Skill/Activity                     |
| ------ | ---------------------------------- |
| -1     | Returns the raw data. (default)    |
| 0      | Overall                            |
| 1      | Attack                             |
| 2      | Defence                            |
| 3      | Strength                           |
| 4      | Hitpoints                          |
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
| 24     | Clue Scrolls (easy)                |
| 25     | Clue Scrolls (medium)              |
| 26     | Clue Scrolls (all)                 |
| 27     | Bounty Hunter - Rogue              |
| 28     | Bounty Hunter - Hunter             |
| 29     | Clue Scrolls (hard)                |
| 30     | Last Man Standing - Rank           |
| 31     | Clue Scrolls (elite)               |
| 32     | Clue Scrolls (master)              |

## Types
| Number | Type                     |
| ------ | ------------------------ |
| 0      | Rank                     |
| 1      | Level/Score (default)    |
| 2      | Experience (Skills only) |

## Errors
If there is an error in the usage or request, a message describing the error will be returned instead.

| Message | Details |
| ------- | ------- |
| No player name entered. | <foo> |
| Too many players requested. No more than $1 are allowed. |
| Failed to retrieve player data. Try again later. | An HTTP error occurred, possibly the wiki made too many requests and is temporarily blocked. |
| The API requested does not exist | See [above](#API) for the valid APIs.
| Player '$1' does not exist. | <foo> |
| The skill requested does not exist. | See [RS3 Skills](#rs3-skills) or [OSRS Skills](#osrs-skills) for the known valid skills. |
| The type requested does not exist. | See [above](#Types) for the valid types. |
