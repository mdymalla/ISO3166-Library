# ISO3166-Library
These codes are the standard for defining countries, dependent territories, special areas of geographical interest, and their principal subdivisions.

### Data 
contains all ICU supported countries including iso3166-1 & iso3166-2 codes in addition to their supported translations.

### Structure
Both 3166-1 and 3166-2 have a default json file which define each codes structure and data.

ISO3166-1:
```json
"AU": {
    "alpha-2": "AU",
    "alpha-3": "AUD",
    "numeric": "036",
    "name": "Australia"
}
```
ISO3166-2:
```json
"AU-NSW": {
    "name": "New South Wales",
    "code": "AU-NSW",
    "type": "State",
    "administration-level": 1
}
```
The proceeding directory from the default file contains locale separated translations for a countries name in 3166-1, or sub-division name in 3166-2.

ISO3166-1:
```json
(ja)

"Names": {
    "AT": "オーストリア",
    "AU": "オーストラリア",
    "AW": "アルバ"
}
```
ISO3166-2:
```json
(ja)

"Names": {
    "AU-NSW": "ニューサウスウェールズ"
}
```

### Data Sources
#### Symfony/Intl: 
https://github.com/symfony/symfony
#### Debain/iso-codes
https://salsa.debian.org/iso-codes-team/iso-codes


