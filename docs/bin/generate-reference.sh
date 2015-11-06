#!/bin/bash
EXECPATH=`dirname $0`
cd $EXECPATH
cd ..

rm -rf en/ref/Baleen
mkdir -p en/ref/Baleen
../vendor/bin/sphpdox process -o en/ref Baleen\\Migrations ../src
find en/ref -name "*.rst" -exec bash -c 'mv "$1" "$(sed "s/\.rst$/.txt/" <<< "$1")"' - '{}' \;
