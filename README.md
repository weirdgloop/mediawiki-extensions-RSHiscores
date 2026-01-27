# RSHiScores

A MediaWiki extension that provides easy access to [RuneScape's HiScores](http://services.runescape.com/m=hiscore/ranking) for use in wikitext and JS calculators on the [RuneScape Wiki](https://runescape.wiki).

# Installation

1. Clone this repository to the extensions directory of your MediaWiki install.
2. Add the following to your `LocalSettings.php`:

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
- `Skill` is a string representation of the skill or activity. See [RS3 Skills](#rs3-skills) or [OSRS Skills](#osrs-skills) for the known skill values. Numbers are also allowed, for backwards compatibility.
- `Type` is a string representation that refers to the type of data to return, see [Types](#types) for valid values. Numbers are also allowed, for backwards compatibility.

If an error occurs, then an error message will be returned. See [Errors](#errors) for possible errors.

## API
| Name            | API                                        |
| --------------- | ------------------------------------------ |
| rs3             | RuneScape                                  |
| rs3-hardcore    | RuneScape Hardcore Ironman Mode            |
| rs3-ironman     | RuneScape Ironman Mode                     |
| osrs            | Old School RuneScape                       |
| osrs-deadman    | Old School RuneScape Deadman Mode          |
| osrs-hardcore   | Old School RuneScape Hardcore Ironman Mode |
| osrs-ironman    | Old School RuneScape Ironman Mode          |
| osrs-seasonal   | Old School RuneScape Seasonal Mode         |
| osrs-tournament | Old School RuneScape Tournament Mode       |
| osrs-ultimate   | Old School RuneScape Ultimate Ironman Mode |

## RS3 Skills
*Note: These could be changed by jagex at any time. Use the jsondump format to get the most recent values*
| Number | string | Notes |
| ------ | ------ | ----- |
| -1     | jsondump | Returns the raw data. (default) |
| 0      | overall |
| 1      | attack |
| 2      | defence |
| 3      | strength |
| 4      | hitpoints |
| 5      | ranged |
| 6      | prayer |
| 7      | magic |
| 8      | cooking |
| 9      | woodcutting |
| 10     | fletching |
| 11     | fishing |
| 12     | firemaking |
| 13     | crafting |
| 14     | smithing |
| 15     | mining |
| 16     | herblore |
| 17     | agility |
| 18     | thieving |
| 19     | slayer |
| 20     | farming |
| 21     | runecraft |
| 22     | hunter |
| 23     | construction |
| 24     | summoning |
| 25     | dungeoneering |
| 26     | divination |
| 27     | invention |
| 28     | archaeology |
| 29     | necromancy |
| 30     | bounty hunters |
| 31     | bounty hunter rogues |
| 32     | dominion tower |
| 33     | the crucible |
| 34     | castle wars games |
| 35     | b.a attackers |
| 36     | b.a defenders |
| 37     | b.a collectors |
| 38     | b.a healers |
| 39     | duel tournament |
| 40     | mobilising armies |
| 41     | conquest |
| 42     | fist of guthix |
| 43     | gg: resource race |
| 44     | gg: athletics |
| 45     | we2 armadyl lifetime contribution |
| 46     | we2 bandos lifetime contribution |
| 47     | we2 armadyl pvp kills |
| 48     | we2 bandos pvp kills |
| 49     | heist: guard prestige |
| 50     | heist: robber prestige |
| 51     | cfp: 5 game average |
| 52     | cow tips |
| 53     | rats slaughtered |
| 54     | runescore |
| 55     | clue scrolls (easy) |
| 56     | clue scrolls (medium) |
| 57     | clue scrolls (hard) |
| 58     | clue scrolls (elite) |
| 59     | clue scrolls (master) |

## OSRS Skills
*Note: These could be changed by jagex at any time. Use the jsondump format to get the most recent values*
| Number | String | Notes |
| ------ | ------ | ----- |
| -1     | jsondump | Returns the raw data. (default) |
| 0      | overall |
| 1      | attack |
| 2      | defence |
| 3      | strength |
| 4      | hitpoints |
| 5      | ranged |
| 6      | prayer |
| 7      | magic |
| 8      | cooking |
| 9      | woodcutting |
| 10     | fletching |
| 11     | fishing |
| 12     | firemaking |
| 13     | crafting |
| 14     | smithing |
| 15     | mining |
| 16     | herblore |
| 17     | agility |
| 18     | thieving |
| 19     | slayer |
| 20     | farming |
| 21     | runecraft |
| 22     | hunter |
| 23     | construction |
| 24     | league points |
| 25     | deadman points |
| 26     | bounty hunter - hunter |
| 27     | bounty hunter - rogue |
| 28     | bounty hunter (legacy) - hunter |
| 29     | bounty hunter (legacy) - rogue |
| 30     | clue scrolls (all) |
| 31     | clue scrolls (beginner) |
| 32     | clue scrolls (easy) |
| 33     | clue scrolls (medium) |
| 34     | clue scrolls (hard) |
| 35     | clue scrolls (elite) |
| 36     | clue scrolls (master) |
| 37     | lms - rank |
| 38     | pvp arena - rank |
| 39     | soul wars zeal |
| 40     | rifts closed |
| 41     | colosseum glory |
| 42     | abyssal sire |
| 43     | alchemical hydra |
| 44     | araxxor |
| 45     | artio |
| 46     | barrows chests |
| 47     | bryophyta |
| 48     | callisto |
| 49     | calvar'ion |
| 50     | cerberus |
| 51     | chambers of xeric |
| 52     | chambers of xeric: challenge mode |
| 53     | chaos elemental |
| 54     | chaos fanatic |
| 55     | commander zilyana |
| 56     | corporeal beast |
| 57     | crazy archaeologist |
| 58     | dagannoth prime |
| 59     | dagannoth rex |
| 60     | dagannoth supreme |
| 61     | deranged archaeologist |
| 62     | duke sucellus |
| 63     | general graardor |
| 64     | giant mole |
| 65     | grotesque guardians |
| 66     | hespori |
| 67     | kalphite queen |
| 68     | king black dragon |
| 69     | kraken |
| 70     | kree'arra |
| 71     | k'ril tsutsaroth |
| 72     | lunar chests |
| 73     | mimic |
| 74     | nex |
| 75     | nightmare |
| 76     | phosani's nightmare |
| 77     | obor |
| 78     | phantom muspah |
| 79     | sarachnis |
| 80     | scorpia |
| 81     | scurrius |
| 82     | skotizo |
| 83     | sol heredit |
| 84     | spindel |
| 85     | tempoross |
| 86     | the gauntlet |
| 87     | the corrupted gauntlet |
| 88     | the leviathan |
| 89     | the whisperer |
| 90     | theatre of blood |
| 91     | theatre of blood: hard mode |
| 92     | thermonuclear smoke devil |
| 93     | tombs of amascut |
| 94     | tombs of amascut: expert mode |
| 95     | tzkal-zuk |
| 96     | tztok-jad |
| 97     | vardorvis |
| 98     | venenatis |
| 99     | vet'ion |
| 100    | vorkath |
| 101    | wintertodt |
| 102    | zalcano |
| 103    | zulrah |

## Types
| Number | String | Notes           |
| ------ | ------ | --------------- |
| 1      | auto   | Level for skills, score for activities (default) |
| 2      | xp     | Only for skills |
| -      | level  | Only for skills |
| 0      | rank   |                 |
| -      | score  | Only for activities. Corresponds to boss kc for bosses |

## Errors
If there is an error in the usage or request, a message describing the error will be returned instead.

| Message | Details |
| ------- | ------- |
| No player name entered. |
| Too many players requested. No more than $1 are allowed. |
| See previous error. | An error has already occurred in earlier usage. |
| Failed to retrieve player data. Try again later. | An HTTP error occurred, possibly the wiki made too many requests and is temporarily blocked. |
| The API requested does not exist. | See [above](#api) for the valid APIs.
| Player '$1' does not exist. |
| The skill or activity requested does not exist. | See [RS3 Skills](#rs3-skills) or [OSRS Skills](#osrs-skills) for the known valid skills. |
| The type requested does not exist for this skill or activity. | See [above](#Types) for the valid types. |
| The highscores endpoint returned unexpected results. | The format of data received might have changed. Maybe this extension must be adjusted accordingly. |
| The value for this skill could not be parsed. | The format of data received might have changed. Maybe this extension must be adjusted accordingly. |
