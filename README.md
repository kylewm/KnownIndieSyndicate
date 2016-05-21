# KnownIndieSyndicate

This plugin adds support for syndicating via Micropub and Webmention.

Any micropub server can act as a syndication endpoint, including
[silo.pub](https://silo.pub/), other Known sites that have the
IndiePub plugin enabled, and WordPress sites that have
[the micropub plugin](https://github.com/snarfed/wordpress-micropub/).

[Bridgy Publish](https://brid.gy/about#publish) and
[IndieNews](https://news.indiewebcamp.com) are two services (the only two
I'm aware of) that support syndicating via webmention. When they get a
webmention from you, they parse the source for content and syndicate it.

## License

This software is dedicated to the public domain under Creative Commons [CC0][].

[CC0]: http://creativecommons.org/publicdomain/zero/1.0/


## Changelog

- 0.1.3 - 2016-05-21 - added support for syndication via webmention
  (the original version only supported micropub)
