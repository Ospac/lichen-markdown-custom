# Lichen-Markdown

Lichen-Markdown is a simple and friendly CMS for making static websites. Lichen-markdown is a fork of the [original php version of Lichen](https://codeberg.org/stringbone/lichen/src/branch/master).

There is a simple web page with more info about the project at [https://lichen.commoninternet.net](https://lichen.commoninternet.net).

![screenshot of lichen UI](https://codeberg.org/notplants/lichen-markdown-landing-page/raw/branch/main/screenshots/lichen-markdown-cms-boxshadow4.png)

## Table of Contents

- [Lichen-Markdown](#lichen-markdown)
  - [Table of Contents](#table-of-contents)
  - [Installing and running locally](#installing-and-running-locally)
    - [With docker](#with-docker)
  - [Running On A Server](#running-on-a-server)
    - [With Yunohost](#with-yunohost)
    - [With Apache or Nginx](#with-apache-or-nginx)
      - [Dependencies](#dependencies)
      - [Apache Setup](#apache-setup)
      - [Nginx Setup](#nginx-setup)
      - [Docker Setup](#docker-setup)
  - [Usage](#usage)
    - [Set the title of the page](#set-the-title-of-the-page)
  - [Localization of your Lichen-markdown website](#localization-of-your-lichen-markdown-website)
  - [Project Structure](#project-structure)
  - [Using Lichen-Markdown As An SSG](#using-lichen-markdown-as-an-ssg)
  - [Updating Lichen-markdown](#updating-lichen-markdown)
    - [Updating Lichen-markdown v1.4.0 and above](#updating-lichen-markdown-v140-and-above)
    - [Updating Lichen-markdown v1.3.9 and below](#updating-lichen-markdown-v139-and-below)
  - [Debugging rendering](#debugging-rendering)
  - [Contributors](#contributors)
  - [Contributing](#contributing)
      - [New release](#new-release)
  - [License](#license)


## Installing and running locally

Get the source code:
```
wget https://codeberg.org/ukrudt.net/lichen-markdown/archive/v.1.4.0.zip
unzip v1.5.0.zip
```

You can then run it directly with php:
```
cd lichen-markdown
php -S 127.0.0.1:8000 -t . src/cms/router.php
```

You can then navigate to `127.0.0.1:8000` to see the website.

Navigate to `127.0.0.1:8000/cms/edit.php` to see the admin interface.

Note that the authentication of the cms interface at /cms/edit.php does not work when running with php, as the authentication is depending on apache/nginx

### With docker 

If you want to locally test lichen-markdown in combination with apache, you can use Docker.

A script with the docker command is included: 

```bash
bash run-dev.sh
```

Then go to [localhost:8000](http://localhost:8000) to use the app.


## Running On A Server 

### With Yunohost

One way to run Lichen-Markdown is as a Yunohost application. You can install Yunohost on your server in [the standard way](https://doc.yunohost.org/en/admin/get_started/install_on/), and then install Lichen-Markdown via [the application catalog](https://apps.yunohost.org//app/lichenmarkdown). 

On a server without Yunohost, you can can also serve Lichen-Markdown via Apache or Nginx, following the instructions below. 

First download the latest release of Lichen-Markdown to your server in the same way as above (for running Lichen-Markdown locally). 

This folder can then be served via Apache, Nginx or Docker (using Apache inside).

Instructions for each of these methods are below.

### With Apache or Nginx

#### Dependencies

First install the dependencies:

```
apt install php-cli php-gd curl apache2-utils
```

To install Lichen-Markdown v1.5.0 in the folder `FOLDER` run the following command:

```
curl -s https://codeberg.org/ukrudt.net/lichen-markdown/raw/branch/main/lib/install.sh | bash -s -- FOLDER v1.5.0
```

The script will ask whether you want to create an admin user. If your are installing on a server, this is a MUST. Otherwise anyone can upload things to your server. If you use Nginx (see below) you need to setup http basic auth for this to work.


#### Apache Setup

1. With an apache web server, copy the apache config in this repository in docs/apache.conf to /etc/apache2/sites-enabled/.

2. In the apache.conf you need to replace `/path/to/your_install_dir` with your install-dir, and `your.domain.example.com` with your actual server domain. You also need to change the ssl-settings to point to a working certificate. We recommend using LetsEncrypt for getting certicates - see their docs for more info.

3. Create a soft link from the root folder 


#### Nginx Setup

With an nginx web server, copy the nginx config in this repository in docs/nginx.conf to /etc/nginx/sites-enabled/.

In the nginx.conf you need to replace __INSTALL_DIR__ with the path to your lichen-markdown project, and "your.domain.example.com" with your actual server domain.

There is a also a comment within nginx.conf explaining how to protect the admin panel with http basic auth, if you choose to. 

#### Docker Setup

The Dockerfile in docker/Dockerfile builds a docker image which can be used to serve Lichen-Markdown with apache, via something like this:

```bash
docker build -t lichen-markdown:latest ./docker/
docker run -d -p 8000:80 -v $(pwd):/var/www/html lichen-markdown:latest
```

## Usage

![screenshot of lichen UI](https://codeberg.org/notplants/lichen-markdown-landing-page/raw/branch/main/screenshots/editor-screenshot-boxshadow4.png)

Navigate to `/cms/edit.php` to edit pages or add new ones. Changes you make to the raw Markdown on the left are reflected in the live preview on the right.

Open the cheatsheet.md file in the editor to see how markdown can be used to format your web pages.

Click the green "Save" button at the bottom to save your content and render a fresh HTML file.

![screenshot of lichen UI](https://codeberg.org/notplants/lichen-markdown-landing-page/raw/branch/main/screenshots/file-nav-boxshadow4.png)

The file manager allows you to create new pages and folders, and upload files like images and videos.

Click on a Markdown file (.md) to edit it.

You can also edit the typographic styling of the page: Expand the `assets` folder and click on the `stylesheet.css` file. Changes in this file will be reflected in the live preview.

Hover a file and click the 🔗 button to return to the editor and insert a link to that file. It will be inserted at the current cursor position.

### Set the title of the page

Web pages has a `title` that will be reflected in the browser or tab-header. In Lichen-markdown you can set the title explicitly. If not set the title will fall back to the most prominent headline.

To set a title explicity, use the following format in the markdown-file:

```
<!-- TITLE: A Wonderful Title -->
```

Then `A Wonderful Title` will become the title for that web page.

To set a general title for all pages in your web site, add the title-snippet to the `header.md` file. If you additionally add another title-snippet to a specific page, then the specific page title will override the one in the header-file, for that page.

## Localization of your Lichen-markdown website

You can make simple localised versions of your website by putting other language-versions of your pages in this folder-structure: `/l11n/<LANGUAGE_NAME>/`. "l11n" is a numeronym for "localization". 

If you create at least the files `index.md`, `header.md` and `footer.md` in a folder `/l11n/<LANGUAGE_NAME>/`, Where `<LANGUAGE_NAME>` is whatever you like (e.g. `en` for english or `da` for danish). 

You can then create a link to a localized version of your page. E.g. https://your-lichen-site.net/l11n/<LANGUAGE_NAME>, and put it in your header.

All internal links on the localized version should point to language-specific version of the pages. So if you have a link to an about page, that would normally be `/about`, then in the localised version, the link should be `/l11n/<LANGUAGE_NAME>/about`.

The layout logic will user the localized header and footer for the layout, when you try to view a localized page. If they don't exist it will fall back to the usual header and footer.

Note that this simple localization always leads the user to the front page of the localized version. If you want to have the language-link point to the current page, you could implement the language-links in each individual page, instead of in the header, and then point them to their respective localized versions.

## Project Structure

The "page" folder contains the Markdown files for each web page. The "src" folder contains the application skeleton: the CMS and theme used to render those pages. Static assets live in the top-level "assets" folder.

The "dist" folder is built from the contents of page and assets.

From the project root, dist can be rebuilt via the command: `php src/cms/build.php`.

Dist can also be re-built through the web interface by clicking the "Rebuild" button in the editor.

Rebuilding "dist" manually like this is actually only necessary if you change files on disc, outside of the Lichen admin UI &mdash; otherwise Lichen will keep src and dist in sync, with dist containing the render HTML versions of files in src. 

## Using Lichen-Markdown As An SSG

The first intended usecase of Lichen-Markdown is to be run as a webserver, so that it can be used as a CMS by an individual or between a small group of collaborators.

However it is also possible to use Lichen-Markdown as a sort of static site generator directly, by uploading the contents of "dist" to somewhere else. 

"dist" contains a static artifact of the website and rendered HTML. This is more of a custom use-case, but noting this here in case anyone wants to use it like that. Note that for files that are not renders of .md files, dist actually is made up of symbolic links back to the original files (in order to save space, and not have each file duplicated). So if you are copying 'dist' to another server, for example using rsync, you would want to use a command that copies symbolic links as real files, such as `rsync -avL source/ destination/`.

You can also rebuild dist on the command line via the command: `php src/cms/build.php`.


## Updating Lichen-markdown

For existing Lichen-markdown installs you can update to the newest version. The method depends on your version of Lichen-markdown. You should also see [the release notes for instructions specific to each release](/release_notes.md).

### Updating Lichen-markdown v1.4.0 and above

From your lichen-markdown folder, run:

```
bash ./update/update_lichen.sh
```

### Updating Lichen-markdown v1.3.9 and below

Run the following command in the lichen-markdown folder:

```
curl -s https://codeberg.org/ukrudt.net/lichen-markdown/raw/branch/main/lib/update.sh | bash -s -- "." 
```

## Debugging rendering

You can debug rendered html in php with this trick, where `$variable` is a variable containing an html string.

```php
echo ("<pre><code>" . json_encode($variable) . "</code></pre>");
```

## Contributors

Lichen-Markdown was forked from Lichen, by [@abekonge](https://venner.network/@abekonge), [@soapdog](https://toot.cafe/@soapdog), and [@notplants](https://sunbeam.city/@notplants).

## Contributing

Contributions are welcome.

#### New release

When making a new release you should change the code followingly:

* change the `NEW_VERSION` variable in `lib/update.sh` to the new version number
* search for the old version number in the code base and change it to the new version number where meaningful. Currently this is only in comments and in this readme.
 
## License

The original Lichen and this fork are both licensed using MIT License.

```
The MIT License (MIT)

Copyright © 2022 Sensor Station LLC

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
```
