#!/bin/bash
EXECPATH=`dirname $0`
cd $EXECPATH
cd ..

#rm -rf build
sphinx-build -b html en build/html
