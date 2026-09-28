#!/usr/bin/env bash
set -e

# -------- USER CONFIG --------

# Path to your lichen-markdown project
# LM_LOCAL_PATH="/your/localpath/to/lichen-markdown/"
LM_LOCAL_PATH="/Users/ospac/Documents/lichen-markdown/"

# Git remote URL for your pages repo
PAGES_REPO_URL="ssh://git@codeberg.org/geha/pages.git"

# Branch used for Pages
PAGES_BRANCH="main"

# -------- DERIVED PATHS --------

LM_DIST_DIR="$LM_LOCAL_PATH/dist"
LM_PAGES_DIR="$LM_LOCAL_PATH/pages"

# -------- CLONE IF NEEDED --------

if [ ! -d "$LM_PAGES_DIR" ]; then
  echo "Pages directory not found, cloning pages repo..."
  git clone "$PAGES_REPO_URL" "$LM_PAGES_DIR"
fi

# -------- CLEAN PAGES DIR (SAFE) --------

# Remove everything except .git, even if dotglob is enabled
find "$LM_PAGES_DIR" -mindepth 1 -maxdepth 1 \
  ! -name '.git' \
  ! -name '.domains' \
  -exec rm -rf {} +

# -------- COPY BUILD OUTPUT --------

# Copy dist contents explicitly into pages directory
cp -rL "$LM_DIST_DIR"/. "$LM_PAGES_DIR"/

# -------- GIT COMMIT / PUSH --------

cd "$LM_PAGES_DIR"

git checkout "$PAGES_BRANCH"

git add -A

# Only commit if there are changes
if ! git diff --cached --quiet; then
  git commit -m "Website updates"
  git push origin "$PAGES_BRANCH"
else
  echo "No changes to publish."
fi