#!/bin/bash
# Deploy script specific for release 1.1.0
# - It imports the Montepisano DEM
# - It computes the area slopes
# - It makes the estimates
# NOTES: 
#  1) 9 Area are not VALID: 515, 490, 491, 492, 397, 512, 515, 490, 491, 492
#  2) Example to test: 
#       https://sisteco.dev.maphub.it/catalog-areas/547/ (used in xls template)
#       https://sisteco.dev.maphub.it/catalog-areas/412/ (slope class B)
set -e
cat storage/geodata/montepisano25x25_3035.sql | docker exec -i postgres_sisteco psql -U sisteco sisteco
docker exec -i php81_sisteco php artisan sisteco:area-slopes
docker exec -i php81_sisteco php artisan sisteco2:estimate_by_catalog
