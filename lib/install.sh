#! /bin/bash
set -eo pipefail

# arg1 install folder
# arg2 lichen-markdown version
# arg3 optional --dont-create-user

if [ -z $1 ]; then
echo "Argument missing: First argument should be lichen-markdown install folder"
fi

if [ -z $2 ]; then
echo "Argument missing: Second argument should be lichen-markdown version, e.g. v1.5.0"
fi

INSTALL_ROOT="$1"
VERSION="$2"
RELEASE="$INSTALL_ROOT/temp/lichen-markdown.tar.gz"


mkdir -p $INSTALL_ROOT/temp

curl -s https://codeberg.org/ukrudt.net/lichen-markdown/archive/"$VERSION".tar.gz --output "$RELEASE"

FILE_TYPE=$(file --mime-type "$RELEASE")
if [ "$FILE_TYPE" = "$RELEASE: text/plain" ]; then

    echo "Version not found!"
    exit 1

elif [ "$FILE_TYPE" = "$RELEASE: application/gzip" ]; then

    tar -xf "$RELEASE" -C $INSTALL_ROOT/temp

    # Install 
    echo -e "\nInstalling $VERSION"
    
    # Set user and group id mode bit, such that folder content inherits that of the parent folder, instead of the script runners primary group
    chmod ug+s $INSTALL_ROOT
    
    cp -a $INSTALL_ROOT/temp/lichen-markdown/src/. $INSTALL_ROOT/
    
    # Set version in manifest file
    echo "version=$VERSION" > "$INSTALL_ROOT"/manifest

    echo -e "\nProgram files created."
fi

# Clean up
rm -r $INSTALL_ROOT/temp/

if [ "$3" != "--dont-create-user" ]; then

    echo -e "\n\nIf installing on a server you MUST create an admin user, to protect the CMS-interface."

    echo -e "Create admin user? yes/no"

    # force read from current tty to handle when curling script into bash
    read CREATE_ADMIN </dev/tty

    if [ "$CREATE_ADMIN" == "yes" ]; then

        echo "Write a user name:"

        # force read from current tty to handle when curling script into bash
        read USER_NAME </dev/tty

        echo -e "\n\nWhere should the users password-file be placed? It must be outside the website's root folder for security reasons. If in doubt you can input your web-server's folder or your home folder: e.g. /etc/apache2/ or /etc/nginx/ or $HOME.\n"

        read -p "Input directory where the password-file will be placed: " AUTH_PATH </dev/tty

        AUTH=$(cat << HEREDOC
# --- DO NOT DELETE --- #
# This file enables password protection for editing the site.
# Set up an htpasswd file (outside of web root!) and update the path
# setting in the AuthUserFile directive.
# https://httpd.apache.org/docs/current/programs/htpasswd.html

AuthType Basic
AuthName "Protected"
AuthUserFile $AUTH_PATH/lichen.htpasswd
Require valid-user

Require all denied
Require env REDIRECT_authbypass
HEREDOC
)

        echo "$AUTH" > "$INSTALL_ROOT/cms/.htaccess"

        htpasswd -c "$AUTH_PATH/lichen.htpasswd" "$USER_NAME"

        echo -e "\nPassword file $AUTH_PATH/lichen.htpasswd was created. It should work as is for Apache. For Nginx you need to point your nginx-configuraiton to this file, see https://docs.nginx.com/nginx/admin-guide/security-controls/configuring-http-basic-authentication/#configuring-nginx-and-nginx-plus-for-http-basic-authentication"

    fi
fi

echo -e "\nLichen-Markdown $VERSION was succesfully installed in $INSTALL_ROOT"


