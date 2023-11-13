# Changelog

## [2.0.1](https://github.com/webmappsrl/sisteco2/compare/v2.0.0...v2.0.1) (2023-11-13)


### Bug Fixes

* Update prod_deploy.yml for Laravel deployment ([969b923](https://github.com/webmappsrl/sisteco2/commit/969b923bba4cd1987768f598538dfdc8f786c875))

## [2.0.0](https://github.com/webmappsrl/sisteco2/compare/v1.2.0...v2.0.0) (2023-11-13)


### ⚠ BREAKING CHANGES

* The calculation for the forestal intervention price has been updated, which may affect the total estimated cost.
* The computation of catalog estimates no longer includes VAT.

### Features

* Add calculation of VAT for hectares in CatalogAreaController ([8cb3e02](https://github.com/webmappsrl/sisteco2/commit/8cb3e028811ccdc952c7a1e83ebf599539b058a1))
* Add command to enrich Catalog Area with Hiking Route stats ([005b786](https://github.com/webmappsrl/sisteco2/commit/005b78655df515ddfbf3d5be089e6df6ee522fba))
* Add command to enrich catalog areas with street info ([63bc9f2](https://github.com/webmappsrl/sisteco2/commit/63bc9f298bada67a302e91cd9335b33086f37cff))
* Add computeHikingRoutes method to CatalogArea model ([805a031](https://github.com/webmappsrl/sisteco2/commit/805a03114ff4a8311bcd0e0c4eae4d575395dbf8))
* Add computeSlopeStats method to CatalogArea model ([5c83fb3](https://github.com/webmappsrl/sisteco2/commit/5c83fb34b4120bb696148a455c84ae4eda230dcf))
* Add computeTransportClass method to CatalogArea model ([2012fc3](https://github.com/webmappsrl/sisteco2/commit/2012fc35d4f1e74b683337f6b1e7777d514895b4))
* Add data sync from CAI and compute hiking routes info ([4f1b533](https://github.com/webmappsrl/sisteco2/commit/4f1b5337ccbd2d6135052495e55eeb8d3d856118))
* Add deploy script for release 1.1.0 ([9d8591d](https://github.com/webmappsrl/sisteco2/commit/9d8591df3d213c6d78aad09cb0fdcda9fa972e5b))
* Add filtering of catalog areas by catalog type ([295d45b](https://github.com/webmappsrl/sisteco2/commit/295d45bc67a73421eb4f04058286c10beab2571a))
* Add forestal intervention price to catalog area details ([0242812](https://github.com/webmappsrl/sisteco2/commit/0242812631db2c70f3fcdf104f9f0a2ca9658cd9))
* Add getHikingRouteMinDist method to CatalogArea model ([0aa2a13](https://github.com/webmappsrl/sisteco2/commit/0aa2a131a3ae2a03c987c5fb8ac1ab0fef72ef40))
* Add hiking routes cost per km configuration ([297f694](https://github.com/webmappsrl/sisteco2/commit/297f6942e833d6ee0d4a24680e8b3b15e82a830b))
* Add hiking routes details to catalog area view ([368d18b](https://github.com/webmappsrl/sisteco2/commit/368d18b1d9b14535a035079141688aa0762f9fe4))
* Add hiking_routes_min_dist column to catalog_areas table ([0a7dd4c](https://github.com/webmappsrl/sisteco2/commit/0a7dd4c78413060fa3a07c9c2b98556ac853b4f6))
* Add HikingRouteController, StoreHikingRouteRequest, UpdateHikingRouteRequest, HikingRoute model, HikingRoutePolicy, HikingRouteFactory, create_hiking_routes_table migration, and HikingRouteSeeder ([e664b74](https://github.com/webmappsrl/sisteco2/commit/e664b74f11628fb9f70fd358577d4f386a505dac))
* Add methods to import hiking routes from CAI API ([3381f97](https://github.com/webmappsrl/sisteco2/commit/3381f974c2e5f70dd57c545fa6bdd9caaf485533))
* Add migration to enable postgis_raster extension ([ec02d47](https://github.com/webmappsrl/sisteco2/commit/ec02d47477ba6f95c6d5cef29b9a7d483b8161ec))
* Add new geodata to deployment script ([e997e61](https://github.com/webmappsrl/sisteco2/commit/e997e61917653fdb422421efe825eff2a798147a))
* Add print styles for parcel details ([0807928](https://github.com/webmappsrl/sisteco2/commit/0807928da9cbe4aa070d7bb4fa831019b21f3c5f))
* Add slope class calculation and migration ([b518b1c](https://github.com/webmappsrl/sisteco2/commit/b518b1c2c8556a9455a39586379da87ebcf60cc5))
* Add slope stats fields to catalog areas ([0e715bd](https://github.com/webmappsrl/sisteco2/commit/0e715bd7247b609c8b725c6aa5636077e655e194))
* Add StreetController, StoreStreetRequest, UpdateStreetRequest, Street model, StreetPolicy, StreetFactory, and StreetSeeder ([b450ead](https://github.com/webmappsrl/sisteco2/commit/b450ead002811274eea42a3cac7875dcb45c8325))
* Add surface, catalog type, estimated value, and public URL to CatalogTypeAreaResource ([bd90638](https://github.com/webmappsrl/sisteco2/commit/bd906383a98ef7ed76b1028938727c0354868bce))
* Add SyncCaiHikingRoutesCommand ([303cad1](https://github.com/webmappsrl/sisteco2/commit/303cad1b5e91df3f1be31a4d9b923b6a1cd0fb3b))
* Add transportation information to catalog area view ([95829f8](https://github.com/webmappsrl/sisteco2/commit/95829f8f2c127d0ea256045e3661ca4dc122115d))
* Added "sentieristica" field in catalog area view ([c0822fd](https://github.com/webmappsrl/sisteco2/commit/c0822fd255ff8186f22dd9b91d0821a51d9f41d7))
* added Montepisano DEM SQL file ([fb2799b](https://github.com/webmappsrl/sisteco2/commit/fb2799b805e9444fa299b15ad5cbb0a1c10ab04e))
* addedd sql file with MontePisano streets ([d080e60](https://github.com/webmappsrl/sisteco2/commit/d080e609ccd85a34afaa81b94a6acc18d205ccce))
* Calculate and display additional costs in catalog area ([20a4ced](https://github.com/webmappsrl/sisteco2/commit/20a4ced522fd1cc05b74f8b4117f3ae706c8b9cf))
* Update catalog area calculations ([e8d53ac](https://github.com/webmappsrl/sisteco2/commit/e8d53ac38e00e1df4a423caa7810f61e17c0bf49))
* Update catalog area controller and model ([15f2804](https://github.com/webmappsrl/sisteco2/commit/15f28041378c63c643655e109a90c4bd6918000a))
* Update catalog area controller and view ([2e472c6](https://github.com/webmappsrl/sisteco2/commit/2e472c681fdcce31420f3f3af2ca68f4e9ecb895))
* Update catalog area view ([448f631](https://github.com/webmappsrl/sisteco2/commit/448f631916e75dc2c50b894dcf28278d96cab0cb))
* Update catalog area view ([a211a74](https://github.com/webmappsrl/sisteco2/commit/a211a74b57ac41171a1983f9419a1a46060a2925))
* Update catalog area view and controller ([b6a0e3e](https://github.com/webmappsrl/sisteco2/commit/b6a0e3e72fefac866c6a5e6a855cf02cb88bc31d))
* Update CatalogArea and CatalogType models ([e723712](https://github.com/webmappsrl/sisteco2/commit/e723712928fe19e2e3aeb39d8825a1ada9960b1c))
* Update CatalogArea computation and styling ([f34d5b7](https://github.com/webmappsrl/sisteco2/commit/f34d5b79cdc64792806773c6c612e8b836b3ca19))
* Update CatalogArea model to compute catalog estimate with slope class ([06d1e53](https://github.com/webmappsrl/sisteco2/commit/06d1e53d16ebca9247333a5dfde7e60a31743091))
* Update CatalogArea model to compute parcel code based on transport class ([b03b99e](https://github.com/webmappsrl/sisteco2/commit/b03b99e0bfb0bd8f140a53afebeadbd194ca6a7f))
* Update CatalogTypeAreaResource and sisteco config ([041f289](https://github.com/webmappsrl/sisteco2/commit/041f2897ca1cf417b8c84683453842edd0a18230))
* Update currency unit and value for team management ([d6c91f3](https://github.com/webmappsrl/sisteco2/commit/d6c91f32d6d5997ef20803f8c4e8405a6645b0e7))
* Update estimate calculation and display ([5a0c5cd](https://github.com/webmappsrl/sisteco2/commit/5a0c5cd5c0288aa08428c54881e7979ced12e560))
* Update sisteco.php with new color codes ([63d387c](https://github.com/webmappsrl/sisteco2/commit/63d387c3283c403bbd00fd532d5bea6de22bcb25))


### Bug Fixes

* Improve handling of hiking routes details in CatalogAreaController ([b0bc9e2](https://github.com/webmappsrl/sisteco2/commit/b0bc9e2a160b8fa547c9abd6d45e6d31536e7db2))


### Miscellaneous Chores

* Update label for team management costs ([b674d90](https://github.com/webmappsrl/sisteco2/commit/b674d908573db6475f53f00f09485695aaaca25d))
