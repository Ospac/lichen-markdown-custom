#! /bin/bash
set -euo pipefail

INSTALL_ROOT="../"
# Get .env vars
source "$INSTALL_ROOT"/manifest
CURRENT_VERSION="$version"
BACKUP_FOLDER="$INSTALL_ROOT/update/bak/installed_before_$CURRENT_VERSION"

if [ ! -d "$BACKUP_FOLDER" ]; then
    echo "Cannot restore from earlier version. Earlier version not found".
    exit 1
fi

# RESTORE OLD VERSION

# Save current .htaccess
mkdir -p "$INSTALL_ROOT"/temp
cp "$INSTALL_ROOT"/cms/.htaccess "$INSTALL_ROOT"/temp

# Put old program files back in their place
chmod g+s "$INSTALL_ROOT"
echo -e "\nRestoring from $BACKUP_FOLDER"
cp -r "$BACKUP_FOLDER"/cms/ "$INSTALL_ROOT"/cms
cp -r "$BACKUP_FOLDER"/theme/ "$INSTALL_ROOT"/theme 

# Restore old manifest files
cp "$BACKUP_FOLDER"/manifest "$INSTALL_ROOT"/

# Reapply ./cms/.htaccess, this file should not change
cp "$INSTALL_ROOT"/temp/.htaccess "$INSTALL_ROOT"/cms


# Clean up
rm -r "$INSTALL_ROOT"/temp/
rm -r "$BACKUP_FOLDER"

echo -e "\nDONE. Lichen-markdown succesfully restored to older version." 
  

