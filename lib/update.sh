#! /bin/bash
set -euo pipefail

if [ -z $1 ]; then
echo "Argument missing: First argument should be lichen-markdown install folder"
fi

INSTALL_ROOT="$1"
NEW_VERSION="v1.5.0"
RELEASE="$INSTALL_ROOT/temp/lichen-markdown.tar.gz"
BACKUP_FOLDER="$INSTALL_ROOT/update/bak/installed_before_$NEW_VERSION"


mkdir -p "$INSTALL_ROOT"/temp

curl -s https://codeberg.org/ukrudt.net/lichen-markdown/archive/"$NEW_VERSION".tar.gz --output "$RELEASE"

FILE_TYPE=$(file --mime-type "$RELEASE")
if [ "$FILE_TYPE" = "$RELEASE: text/plain" ]; then

    echo "Version not found!"
    exit 1

elif [ "$FILE_TYPE" = "$RELEASE: application/gzip" ]; then

    tar -xf "$RELEASE" -C "$INSTALL_ROOT"/temp

    # BACKUP EXISTING VERSION
  
    echo -e "\nCreating backup in $BACKUP_FOLDER"
    if [ -d "$BACKUP_FOLDER/" ]; then
        rm -r "$BACKUP_FOLDER/"
    fi
    mkdir -p "$BACKUP_FOLDER/"
    cp -r "$INSTALL_ROOT"/cms/ "$BACKUP_FOLDER"/cms
    cp -r "$INSTALL_ROOT"/theme/ "$BACKUP_FOLDER"/theme

    # backup or create old manifest file
    if [ -f "$INSTALL_ROOT"/manifest ]; then
        cp "$INSTALL_ROOT"/manifest "$BACKUP_FOLDER"/manifest
    else
        echo "version=unknown_version" > "$BACKUP_FOLDER"/manifest
    fi

    # UPDATE 
    echo -e "\nUpdating to $NEW_VERSION"
    rm -r "$INSTALL_ROOT"/cms/*
    rm -r "$INSTALL_ROOT"/theme/*

    # Set group id mode bit, such that folder content inherits that of the parent folder, instead of the users primary group
    chmod g+s "$INSTALL_ROOT"
    # update source folders
    cp -r "$INSTALL_ROOT"/temp/lichen-markdown/src/cms "$INSTALL_ROOT"/
    cp -r "$INSTALL_ROOT"/temp/lichen-markdown/src/theme "$INSTALL_ROOT"/
    cp -r "$INSTALL_ROOT"/temp/lichen-markdown/src/update "$INSTALL_ROOT"/
    cp "$INSTALL_ROOT"/temp/lichen-markdown/src/.htaccess "$INSTALL_ROOT"

    # Reapply ./cms/.htaccess, this file should not change
    cp "$INSTALL_ROOT"/update/bak/installed_before_"$NEW_VERSION"/cms/.htaccess "$INSTALL_ROOT"/cms

    # Set version in manifest file
    echo "version=$NEW_VERSION" > "$INSTALL_ROOT"/manifest
fi

# Clean up
rm -r "$INSTALL_ROOT"/temp/

echo -e "\nDONE. Lichen-markdown succesfully updated to $NEW_VERSION" 
  

