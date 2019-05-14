Changelog
#########

3.0.0
*****

- optimized internal collection and map instantiation
- ``Collection`` and ``Map`` constructors are now private (use the static ``create()`` method instead)
- renamed ``Map::map()`` to ``Map::remap()``
- new methods:

  - ``Map::create()``
  - ``Map::map()``
  - ``Map::build()``
  - ``Map::setPairs()``
  - ``Map::apply()``
  - ``Map::merge()``
  - ``Map::intersectKeys()``
  - ``Map::uintersectKeys()``
  - ``Map::diffKeys()``
  - ``Map::udiffKeys()``
  - ``Collection::create()``
  - ``Collection::collect()``
  - ``Collection::setValues()``
  - ``Collection::pad()``
  - ``Collection::mapColumn()``
  - ``Collection::merge()``


2.0.0
*****

- replaced most of ``IterableHelper`` with a library and marked it as ``@internal``


1.0.0
*****

Initial release
