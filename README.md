# Badge.Team Hatchery

[![Build Status](https://travis-ci.org/badgeteam/Hatchery.svg)](https://travis-ci.org/badgeteam/Hatchery)
[![Maintainability](https://api.codeclimate.com/v1/badges/05fc2bac5b3669fa1b0c/maintainability)](https://codeclimate.com/github/badgeteam/Hatchery/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/05fc2bac5b3669fa1b0c/test_coverage)](https://codeclimate.com/github/badgeteam/Hatchery/test_coverage)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/78402ccc553245f2be2d1def6fdc3c68)](https://www.codacy.com/app/Badgeteam/Hatchery?utm_source=github.com&utm_medium=referral&utm_content=badgeteam/Hatchery&utm_campaign=Badge_Grade)
[![CircleCI](https://circleci.com/gh/badgeteam/Hatchery.svg?style=svg)](https://circleci.com/gh/badgeteam/Hatchery)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/badgeteam/Hatchery/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/badgeteam/Hatchery/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/badgeteam/Hatchery/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/badgeteam/Hatchery/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/badgeteam/Hatchery/badges/build.png?b=master)](https://scrutinizer-ci.com/g/badgeteam/Hatchery/build-status/master)
[![codecov](https://codecov.io/gh/badgeteam/Hatchery/branch/master/graph/badge.svg)](https://codecov.io/gh/badgeteam/Hatchery)
[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fbadgeteam%2FHatchery.svg?type=shield)](https://app.fossa.io/projects/git%2Bgithub.com%2Fbadgeteam%2FHatchery?ref=badge_shield)

Simple micropython software repository for Badges.

[Live Site](http://badge.team) | 
[API Documentation](https://badge.team/api) |
[Documentation](https://docs.badge.team/hatchery/) |
[Documentation (wiki)](https://wiki.badge.team/Hatchery) |
[Project Wiki](https://wiki.badge.team) |
[GitHub](https://github.com/badgeteam/)

## License

Hatchery is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).

The Laravel framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).


[![FOSSA Status](https://app.fossa.io/api/projects/git%2Bgithub.com%2Fbadgeteam%2FHatchery.svg?type=large)](https://app.fossa.io/projects/git%2Bgithub.com%2Fbadgeteam%2FHatchery?ref=badge_large)

## Installation

Requires PHP 7.1.3 or later!

For deployment on a server.

```bash
cp .env.example .env
```

Edit your database, mail and other settings..

Or copy the local dev environment config.

```bash
cp .env.dev .env
```

Install and configure required items.

```bash
pip install pyflakes
composer install
php artisan key:generate
php artisan migrate
yarn
yarn production
```

Compiling and installing the patched minigzip.

```bash
wget http://zlib.net/zlib-1.2.11.tar.gz
tar xvf zlib-1.2.11.tar.gz
cd zlib-1.2.11
./configure
echo -e "#define MAX_WBITS  13\n$(cat zconf.h)" > zconf.h
make
sudo make install
```

### Running the development server locally.

After going through the steps

```bash
php artisan serve
```

If you don't want to install things and do the above steps, Docker makes all the above as easy as:

```bash
docker-compose up
docker exec -it hatchery_laravel_1 php artisan migrate --seed
docker exec -it hatchery_laravel_1 yarn watch
```

Enjoy your Hatchery at http://localhost:8000

## API

Apps

```uri
/eggs/get/[app]/json          - get json data for a the egg named [app]
/eggs/list/json               - a list of all eggs with description, revision etc
/eggs/search/[words]/json     - json data for search query [words]
/eggs/categories/json         - json list of categories
/eggs/category/[cat]/json     - json data for category [cat]

/basket/[badge]/list/json           - a list of all eggs for specific [badge]
/basket/[badge]/search/[words]/json - [badge] specific search for [words]
/basket/[badge]/categories/json     - [badge] specific list of categories
/basket/[badge]/category/[cat]/json - json data for category [cat] on [badge]
```

App specific

```uri
/weather                      - weather of sha location proxied from darksky.net
/weather/52.3451,5.4581       - weather of specified geolocation proxied
```

## Running tests

Run all the tests

```bash
phpunit
```

Run a test suite (for a list of availabe suites, see `/phpunit.xml`)

```bash
phpunit --testsuite <suite_name>
```

Run a specific test file

```bash
phpunit tests/<optional_folders>/TestFileName
```

Run a specific test case

```bash
phpunit --filter <test_case_name>
```

Generate code coverage

```bash
phpunit --coverage-html docs/coverage
```

This will create the code coverage docs in `docs/coverage/index.html`

Testing with Codeception

```bash
./vendor/bin/codecept build
./vendor/bin/codecept run
```