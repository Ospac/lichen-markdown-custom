# How To Export A Lichen-Markdown Project

Lichen-Markdown provides a way to export your markdown files and assets as a .zip without using the terminal &mdash; for experienced programmers, they may already have ideas of how to use this export, but this guide is a more thorough explanation aimed at folks who are new to programming. 

## Four Ways To Use export.zip

After exporting and downloading an export.zip file of your lichen-markdown project, there are a few different things you could do with it:
1. move your markdown and assets to an entirely different tool (such as [yellow cms](https://datenstrom.se/yellow/))
2. run lichen-markdown locally on your laptop with the contents of the export 
3. spin up your own instance of lichen-markdown on a server somewhere else
4. use php locally on your export to generate a static HTML website, and then upload the static HTML website somewhere else (such as github pages)

Here are more details on each of these possibilities. 

## 1. Move your markdown and assets to an entirely different tool 

There are thousands of other website making tools and static site generators that support markdown. Any of these will work with the export of your lichen-markdown site. You can see a list of some of these possible tools [here](https://lichen.commoninternet.net/alternatives) and each tool will have its own documentation on how to get started with it. 

## 2. Run lichen-markdown locally on your laptop with the contents of the export 

In the terminal, if you "cd" into the directory of your unzipped export, you can then run the command `php -S 127.0.0.1:8000 cms/router.php` and this will spin up a local version of lichen-markdown that you can access in your browser through the url [http://127.0.0.1:8000](http://127.0.0.1:8000). You can then continue using lichen-markdown in the same way as you did on a server, on your local laptop. In order to make this visible on the public internet, you would either need to use option 3 or 4 described below. 

## 3. spin up your own instance of lichen-markdown on a server somewhere else

TODO: a beginner-friendly guide for how to do this is still in the works 

## 4. use php locally on your export to generate a static HTML website, and then upload the static HTML website somewhere else (such as github pages)

In the terminal, if you "cd" into the directory of your unzipped export, you can then run the command `php cms/build.php`. This will create a folder called "dist" within "src" which contains a static version of your website in plain HTML. This static website can then be uploaded anywhere that hosts static websites (such as github pages). The one gotcha to watch out for, is that in order to save space, the dist folder lichen-markdown creates contains "symbolic links" to the static assets in your src directory (to avoid duplicating them). To make a fully self-sufficient static website (without symoblic links) that can be uploaded easily somewhere else, after running `php cms/build.php` you also need to run `cp -rL dist myoutputlocation`. You can then upload `myoutputlocation` somewhere, such as to github pages (as described in [this guide](https://dev.to/mrdprasad/how-to-host-a-static-html-css-and-javascript-website-on-github-pages-a-step-by-step-guide-3o64)).



