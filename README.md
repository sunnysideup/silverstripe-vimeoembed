Vimeo
================================================================================

Add vimeo vidoes to your Silverstripe website.


Developer
-----------------------------------------------
Nicolaas Francken [at] sunnysideup.co.nz


Requirements
-----------------------------------------------
see composer.json
CURL


Documentation
-----------------------------------------------
Please contact author for more details.

Any bug reports and/or feature requests will be
looked at

We are also very happy to provide personalised support
for this module in exchange for a small donation.


Installation Instructions
-----------------------------------------------
1. Find out how to add modules to SS and add module as per usual.


2. Review configs and add entries to mysite/_config/config.yml
(or similar) as necessary.
In the _config/ folder of this module
you can usually find some examples of config options (if any).

CONFIG OPTIONS
width (optional) The exact width of the video. Defaults to original size.
maxwidth (optional) Same as width, but video will not exceed original size.
height (optional) The exact height of the video. Defaults to original size.
maxheight (optional) Same as height, but video will not exceed original size.
byline (optional) Show the byline on the video. Defaults to true.
title (optional) Show the title on the video. Defaults to true.
portrait (optional) Show the user's portrait on the video. Defaults to true.
color (optional) Specify the color of the video controls.
callback (optional) When returning JSON, wrap in this function.
autoplay (optional) Automatically start playback of the video. Defaults to false.
xhtml (optional) Make the embed code XHTML compliant. Defaults to true.
api (optional) Enable the Javascript API for Moogaloop. Defaults to false.
wmode (optional) Add the "wmode" parameter. Can be either transparent or opaque.
iframe (optional) Use our new embed code. Defaults to true. NEW!
