#!/bin/bash
# Deploy script specific for release 1.1.0
# - It imports the Montepisano DEM
# - It computes the area slopes
# - Download and sync data from CAI
# - It computes the hiking routes info
# - It makes the estimates
# NOTES: 
#  1) 9 Area are not VALID: 515, 490, 491, 492, 397, 512, 515, 490, 491, 492
#  2) Example to test: 
#       https://sisteco.dev.maphub.it/catalog-areas/547/ (used in xls template)
#       https://sisteco.dev.maphub.it/catalog-areas/412/ (slope class B)
#       https://sisteco.dev.maphub.it/catalog-areas/377/ (70 m from closest path)
set -e
# DOWNLOAD DATA
cat storage/geodata/montepisano25x25_3035.sql | docker exec -i postgres_sisteco psql -U sisteco sisteco
cat storage/geodata/montepisano_street.sql | docker exec -i postgres_sisteco psql -U sisteco sisteco
docker exec -i php81_sisteco php artisan sisteco:sync-cai

# ENRICH CATALOG AREA with SLOPES AND HIKING ROUTES INFO
docker exec -i php81_sisteco php artisan sisteco:area-slopes
docker exec -i php81_sisteco php artisan sisteco:hiking-routes
docker exec -i php81_sisteco php artisan sisteco:enrich-streets

# MAKE PES ESTIMATE
docker exec -i php81_sisteco php artisan sisteco2:estimate_by_catalog
