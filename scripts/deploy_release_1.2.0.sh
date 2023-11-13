#!/bin/bash
# Deploy script specific for release 1.2.0
# - It performs some Data operations: 
#     it empties the DB (db:wipe)
#     it imports the dump storage/backups/20231111.dump.sql.gz
#     It runs migrations
# - It recalculates the catalog estimation
#
# NOTES: 
#  1) 9 Area are still not VALID: 515, 490, 491, 492, 397, 512, 515, 490, 491, 492
#  2) Example to test: 

#     Nessun Intervento (COD_INT: 0) - Sentieri: NO
#     https://sisteco.dev.maphub.it/catalog-areas/457

#     Nessun Intervento (COD_INT: 0) - Sentieri: SI
#     https://sisteco.dev.maphub.it/catalog-areas/458

#     Diradamento (COD_INT: 1) - Sentieri: NO
#     https://sisteco.dev.maphub.it/catalog-areas/454

#     Diradamento (COD_INT: 1) - Sentieri: SI
#     https://sisteco.dev.maphub.it/catalog-areas/465

#     Avviamento alto fusto (COD_INT: 2) - Sentieri: NO
#     https://sisteco.dev.maphub.it/catalog-areas/526

#     Avviamento alto fusto (COD_INT: 2) - Sentieri: SI
#     https://sisteco.dev.maphub.it/catalog-areas/541

#     Taglio Ceduo (COD_INT: 3) - Sentieri: NO
#     https://sisteco.dev.maphub.it/catalog-areas/540

#     Taglio Ceduo (COD_INT: 3) - Sentieri: SI
#     https://sisteco.dev.maphub.it/catalog-areas/423

#     Recupero post-incendio (COD_INT: 4) - Sentieri: NO
#     https://sisteco.dev.maphub.it/catalog-areas/510

#     Recupero post-incendio (COD_INT: 4) - Sentieri: SI
#     https://sisteco.dev.maphub.it/catalog-areas/417

#     Selvicutura ad albero (COD_INT: 5) - Sentieri: NO
#     https://sisteco.dev.maphub.it/catalog-areas/477

#     Selvicutura ad albero (COD_INT: 5) - Sentieri: SI
#     https://sisteco.dev.maphub.it/catalog-areas/472

set -e
# Epty DB
docker exec -i php81_sisteco php artisan db:wipe

# Import data
zless storage/backups/20231111.dump.sql.gz | docker exec -i postgres_sisteco psql -U sisteco sisteco

# RUN migration
docker exec -i php81_sisteco php artisan migrate

# MAKE PES ESTIMATE
docker exec -i php81_sisteco php artisan sisteco2:estimate_by_catalog
