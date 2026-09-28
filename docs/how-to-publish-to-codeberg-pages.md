## How To Use Lichen-Markdown To Publish To Codeberg Pages 

Lichen-Markdown was firstly intended to be used as a minimal CMS running on a server, that allows multiple people to collaborate on editing as website, similar to Wordpress or other CMS tools. 

But for websites managed by one person, Lichen-Markdown can also be used as a static site generator, which outputs a static website to the dist directory, which can then be published to the internet via tools such as [Codeberg Pages](https://docs.codeberg.org/codeberg-pages/).

To use Lichen-Markdown in this way:
- follow the steps to run Lichen-Markdown locally on your laptop ([https://codeberg.org/ukrudt.net/lichen-markdown#running-locally](https://codeberg.org/ukrudt.net/lichen-markdown#running-locally))
- create a public repository in Codeberg named `pages`
- write a small script which copies the contents of `dist` and publishes them to this repo. something like below (might work as is after setting the constants at the top, or modify it for your needs). then whenever you want to modify your website, run lichen-markdown locally and work on your website, and then when you are ready to publish to the internet run the script. (note: be careful that you set LM_LOCAL_PATH correctly, as the script will `rm -rf $LM_LOCAL_PATH/pages`)

```bash
#!/usr/bin/env bash
set -e

# -------- USER CONFIG --------

# Path to your lichen-markdown project
LM_LOCAL_PATH="/your/localpath/to/lichenmarkdown/project"

# Git remote URL for your pages repo
PAGES_REPO_URL="ssh://git@codeberg.org/yourusername/pages.git"

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
```