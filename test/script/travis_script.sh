#!/usr/bin/env bash

set -e
trap '>&2 echo Error: Command \`$BASH_COMMAND\` on line $LINENO failed with exit code $?' ERR

# run static php-cs-fixer code analysis
./vendor/bin/php-cs-fixer fix --dry-run --diff --verbose

## enable xdebug again
mv ~/.phpenv/versions/$(phpenv version-name)/xdebug.ini.bak ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/xdebug.ini

echo "Env Dev: $dev";
echo "Env Level: $level";

## run the unit tests
if [ "$level" == "unit" ]; then
    echo "Perform unit tests only";
    ./vendor/bin/phpunit test/unit --coverage-clover build/coverage/xml
fi

## run the integration tests
if [ "$level" == "integration" ]; then
    echo "Perform integration tests only";
    ./vendor/bin/phpunit test/integration
fi
