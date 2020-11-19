# ISO3166-Library
These codes are the standard for defining countries, dependent territories, special areas of geographical interest, and their principal subdivisions.

### Data
contains all ICU supported countries including iso3166-1 & iso3166-2 codes in addition to their supported translations.

### Structure
#### 3166-1
The 3166-1 directory contains both ISO3166-1 official country codes and meta data as well as supported translations separated by locale.

'data/3166-1/meta.json':
```json
"AU": {
    "alpha-2": "AU",
    "alpha-3": "AUD",
    "numeric": "036"
}
```

'data/3166-1/ja.json':
```json
{
    "AU": "オーストラリア"
}
```

#### 3166-2
The 3166-2 directory contains both ISO3166-2 official Sub-division codes and meta data as well as supported translations both separated by alpha-2 country code and locale. Since it is more common to require the sub-divisions for a specific country this structure avoids the loading of irrelevant data.

'data/3166-2/GB/meta.json':
```json
{
    "GB-SCT": {
        "code": "GB-SCT",
        "type": "Country"
    },
    "GB-NLK": {
        "code": "GB-NLK",
        "type": "Council area",
        "parent": "SCT"
    }
}
```

'data/3166-2/GB/ja.json':
```json
{
    "GB-SCT": "スコットランド"
}
```

### Data Sources
#### Symfony/Intl:
https://github.com/symfony/symfony
#### Debain/iso-codes
https://salsa.debian.org/iso-codes-team/iso-codes


