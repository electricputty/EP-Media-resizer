EP Media resizer
================

EE add-on that resizes any embedded media item (Video, Google Map, Audio clip) to specified height and width.

### The problem:

You’ve got a client who wants to add video clips to their web pages. They are grabbing clips from all over the place; YouTube, Vimeo, Flickr. You’ve designed a beautiful template with space to accommodate a clip of a certain size. Your client adds an enormous, wide-screen movie and your template explodes.

### The solution:

Get your template to scale the video clip to a size that you, the designer, can control so that it won’t break the layout. You want the scaling to constrain the proportions of the clip, so people don’t end up looking weirdly tall. You also want the the scaling to be a robust process, performed on the sever, so that it will works in all environment and not just the latest browsers.

Media Resizer from Electric Putty is a free Expressionengine Plugin available for EE2.x which does exactly this. Easy to install, wrap the code you want to be processed by it between the following tag pair. For example:

`{exp:ep_media_resizer width="550"} {video_clip} {/exp:ep_media_resizer}`

### Parameters

- `height` The desired height of the media object - if not specified the new height will be calculated automatically from the desired width
- `width` The desired width of the media object - if not specified the new width will be calculated automatically from the desired height
- `debug` If set to ‘true’ the plugin will output the original and processed height and width values for all the DOM elements it has found (object, embed, iframe)

**NOTE:** If neither height or width params are set the object will be returned
at its original size
