# ISO3166-Library
These codes are the standard for defining countries, dependent territories, special areas of geographical interest, and their principal subdivisions.

### Data 
contains all ICU supported countries including iso3166-1 & iso3166-2 codes in addition to their supported translations.

### Structure
Each country is separated into an individual file containing its Names, Alpha-2, Alpha-3, Numeric, and 3166-2 territory/regions codes.

```json
"alpha-2": "AU",
"alpha-3": "AUD",
"numeric": "036",
"names": {
    "en": "Australia",
    "fr": "Australie"
},
"3166-2": {
    "AU-QLD": {
        "code": "AU-QLD",
        "type": "State",
        "names": {
            "en": "Queensland",
            "ja": "クインズランド"
        }
    }
}
```

### Data Sources
#### Symfony/Intl: 
https://github.com/symfony/symfony
#### Debain/iso-codes
https://salsa.debian.org/iso-codes-team/iso-codes


