#!/usr/bin/env sh

# (re)generates the autoloadfiles of all components.
# The script regnerates only autoloadfiles that are already present. This way it knows,
# which prefixes it should use for each component.
# So if you want a new autoload file to be created, you have to touch it first.

# cd into the ymc-components root dir
cd $(dirname $0)/../../
GENERATOR=./EzcAddons/bin/ymc-gen-autoloads

# make sure ./autoload directory is there
mkdir -p ./autoload

for DIR in $(find . -maxdepth 1 -type d -name "[A-Z]*")
do
    if [ -d $DIR/src ]
    then
        echo "generate autoload files for $DIR"
        for AUTOLOADFILE in $(ls -1 $DIR/src/*_autoload.php)
        do
            OUT_NAME=$(basename $AUTOLOADFILE)

            # Generate autoload file for packaging
            $GENERATOR -d $DIR/src -t $DIR/src -b . -p $OUT_NAME

            # Generate autoload file in global autoload dir
            cp $DIR/src/$OUT_NAME ./autoload/$OUT_NAME
        done
    fi
done

