# Lichen-markdown release notes

## v.1.4.0

### Update instructions

If you are serving Lichen-markdown with  Nginx be sure to check that your config blocks the new `/update` folder from being accessible from the web. See [/docs](/docs) for configuration examples.

Apart from that you should follow the [update instructions in the README](/README.md#updating-lichen-markdown-v139-and-below).

### Changelog

* Install and update functionality added
* Fix "Build presupposes existing dist dir"
* Fix #105 - header and footer previewed in main
* Added "growing layout". Main will grow such that footer will be at least at the bottom of the viewport.