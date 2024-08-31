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
- `Skill` is a string representation of the skill or activity. See [RS3 Skills](#rs3-skills) or [OSRS Skills](#osrs-skills) for the known skill values.
- `Type` is a string representation that refers to the type of data to return, see [Types](#types) for valid values.

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
| string | Notes |
| ------ | ----- |
| jsondump | Returns the raw data. (default) |
| overall |
| attack |
| defence |
| strength |
| hitpoints |
| ranged |
| prayer |
| magic |
| cooking |
| woodcutting |
| fletching |
| fishing |
| firemaking |
| crafting |
| smithing |
| mining |
| herblore |
| agility |
| thieving |
| slayer |
| farming |
| runecraft |
| hunter |
| construction |
| summoning |
| dungeoneering |
| divination |
| invention |
| archaeology |
| necromancy |
| bounty hunters |
| bounty hunter rogues |
| dominion tower |
| the crucible |
| castle wars games |
| b.a attackers |
| b.a defenders |
| b.a collectors |
| b.a healers |
| duel tournament |
| mobilising armies |
| conquest |
| fist of guthix |
| gg: resource race |
| gg: athletics |
| we2 armadyl lifetime contribution |
| we2 bandos lifetime contribution |
| we2 armadyl pvp kills |
| we2 bandos pvp kills |
| heist: guard prestige |
| heist: robber prestige |
| cfp: 5 game average |
| cow tips |
| rats slaughtered |
| runescore |
| clue scrolls (easy) |
| clue scrolls (medium) |
| clue scrolls (hard) |
| clue scrolls (elite) |
| clue scrolls (master) |

## OSRS Skills
*Note: These could be changed by jagex at any time. Use the jsondump format to get the most recent values*
| String | Notes |
| ------ | ----- |
| jsondump | Returns the raw data. (default) |
| overall |
| attack |
| defence |
| strength |
| hitpoints |
| ranged |
| prayer |
| magic |
| cooking |
| woodcutting |
| fletching |
| fishing |
| firemaking |
| crafting |
| smithing |
| mining |
| herblore |
| agility |
| thieving |
| slayer |
| farming |
| runecraft |
| hunter |
| construction |
| league points |
| deadman points |
| bounty hunter - hunter |
| bounty hunter - rogue |
| bounty hunter (legacy) - hunter |
| bounty hunter (legacy) - rogue |
| clue scrolls (all) |
| clue scrolls (beginner) |
| clue scrolls (easy) |
| clue scrolls (medium) |
| clue scrolls (hard) |
| clue scrolls (elite) |
| clue scrolls (master) |
| lms - rank |
| pvp arena - rank |
| soul wars zeal |
| rifts closed |
| colosseum glory |
| abyssal sire |
| alchemical hydra |
| araxxor |
| artio |
| barrows chests |
| bryophyta |
| callisto |
| calvar'ion |
| cerberus |
| chambers of xeric |
| chambers of xeric: challenge mode |
| chaos elemental |
| chaos fanatic |
| commander zilyana |
| corporeal beast |
| crazy archaeologist |
| dagannoth prime |
| dagannoth rex |
| dagannoth supreme |
| deranged archaeologist |
| duke sucellus |
| general graardor |
| giant mole |
| grotesque guardians |
| hespori |
| kalphite queen |
| king black dragon |
| kraken |
| kree'arra |
| k'ril tsutsaroth |
| lunar chests |
| mimic |
| nex |
| nightmare |
| phosani's nightmare |
| obor |
| phantom muspah |
| sarachnis |
| scorpia |
| scurrius |
| skotizo |
| sol heredit |
| spindel |
| tempoross |
| the gauntlet |
| the corrupted gauntlet |
| the leviathan |
| the whisperer |
| theatre of blood |
| theatre of blood: hard mode |
| thermonuclear smoke devil |
| tombs of amascut |
| tombs of amascut: expert mode |
| tzkal-zuk |
| tztok-jad |
| vardorvis |
| venenatis |
| vet'ion |
| vorkath |
| wintertodt |
| zalcano |
| zulrah |

## Types
| String | Notes           |
| ------ | --------------- |
| auto   | Level for skills, score for activities |
| xp     | Only for skills |
| level  | Only for skills |
| rank   |                 |
| score  | Only for activities. Corresponds to boss kc for bosses |

## Errors
If there is an error in the usage or request, a message describing the error will be returned instead.

| Message | Details |
| ------- | ------- |
| No player name entered. |
| Too many players requested. No more than $1 are allowed. |
| Skill parameter must not be a number. | Older version of extension required numbers for skill and type. This version requires strings |
| Type parameter must not be a number. | Older version of extension required numbers for skill and type. This version requires strings |
| See previous error. | An error has already occurred in earlier usage. |
| Failed to retrieve player data. Try again later. | An HTTP error occurred, possibly the wiki made too many requests and is temporarily blocked. |
| The API requested does not exist. | See [above](#api) for the valid APIs.
| Player '$1' does not exist. |
| The skill requested does not exist. | See [RS3 Skills](#rs3-skills) or [OSRS Skills](#osrs-skills) for the known valid skills. |
| The type requested does not exist. | See [above](#Types) for the valid types. |
| The highscores endpoint returned unexpected results. | The format of data received might have changed. Maybe this extension must be adjusted accordingly. |
| The value for this skill could not be parsed. | The format of data received might have changed. Maybe this extension must be adjusted accordingly. |
