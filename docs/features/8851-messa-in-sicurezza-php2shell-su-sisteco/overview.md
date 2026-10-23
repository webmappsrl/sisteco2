> Ticket: oc:8851

# Messa in sicurezza PHP2Shell (CVE-2026-109618/109619) su sisteco

## Cosa cambia

sisteco (sis-te.com) passa da PHP 8.1.22, ramo fuori supporto e senza versione corretta per PHP2Shell, a **PHP 8.2.35** in un'immagine con versione bloccata (`php:8.2.35-fpm-trixie`, come osmfeatures in oc:8846). Il container di produzione su geobox viene ricreato col nome `php82_sisteco` e l'immagine `wm-phpfpm:8.2`, e tutti i riferimenti al vecchio nome (workflow di deploy, script, README, compose di esempio) vengono allineati. La CI passa a `php-version: "8.2"`.

Sullo stesso server si chiude l'esposizione per IP: vengono disattivati il vhost `000-default` (che serve tutta `/var/www/html`, compresi i `.env` dei progetti), il modulo Apache `php7.4` e il pool `php7.4-fpm` che nessun vhost usa.

## Perché

Il 21/10/2026 sono state pubblicate due vulnerabilità critiche di PHP che permettono di eseguire comandi sul server senza credenziali, con attacchi già in corso. Per il ramo 8.1 non esiste patch: l'unica correzione è cambiare versione. Laravel 10 e Nova 4, nelle versioni bloccate nel `composer.lock`, supportano la 8.2 (`laravel/framework` v10.6.2 richiede `^8.1`, `laravel/nova` 4.27.14 `^7.3|^8.0`).

Il `.env` di sisteco oggi risponde 200 a chi entra per IP su geobox, perché la cartella del progetto sta dentro `/var/www/html` servita da `000-default`. Sistemare solo PHP lascerebbe esposte le credenziali, comprese quelle nuove che arriveranno con la rotazione.

Decisioni prese allo scrum del 23/10 (Giuseppe Bonfanti): sisteco va messo in sicurezza; la rotazione delle credenziali la fa Giuseppe in un secondo momento, su tutti i server; prima dell'intervento si rifà lo snapshot.

## Requisiti

- [ ] Dockerfile `docker/configs/phpfpm/Dockerfile` su `php:8.2.35-fpm-trixie` (versione bloccata, nessun tag mobile), con tutte le estensioni oggi in uso compilate e caricate: gd (freetype, jpeg, webp), intl, zip, pdo, pdo_mysql, opcache, pgsql, pdo_pgsql, exif, imagick, apcu
- [ ] `docker-compose.yml.example`: immagine `wm-phpfpm:8.2`, container `php82_${APP_NAME}`
- [ ] Riferimenti a `php81_sisteco` / `8.1` aggiornati in `.github/workflows/prod_deploy.yml`, `.github/workflows/dev_deploy.yml` (anche `php-version: "8.2"`), `scripts/`, `docker/configs/phpfpm/init-xdebug.sh`, `README.md`
- [ ] Validazione in locale su PHP 8.2 con un dump del database di produzione e `LOG_DEPRECATIONS_CHANNEL=single` nel `.env` locale (con il valore `null` di `.env-example` Laravel scarta le deprecazioni e il log resta pulito comunque): `php -v` dentro il container riporta 8.2.35; `php -m` elenca tutte le estensioni sopra; `composer install` va a buon fine; login a Nova e apertura delle risorse principali (Owner, CadastralParcel, Catalog, CatalogArea, CatalogType) senza errori né avvisi di deprecazione nel log; una stima per catalogo su particelle reali; un'anteprima PDF di medialibrary; `php artisan test` (EstimateByCatalogTest) verde
- [ ] Il dump di produzione resta in una cartella esclusa da git e viene cancellato a fine ticket
- [ ] Su geobox, prima di toccare qualsiasi cosa: snapshot Hetzner nuovo; `docker diff php81_sisteco` salvato fuori dal server; `docker tag c4f319930e1d wm-phpfpm:8.1-pre-patch`; ricerca di `.php` creati o modificati dal 20/10 in `public/` e `storage/` e di richieste sospette nei log Apache di sis-te.com dal 20/10; confronto di `vendor/` del server con una `composer install` pulita dallo stesso `composer.lock` (`diff -rq`), perché `vendor/` è escluso da git e `git status` non lo vede; ricerca per contenuto (`eval(`, `base64_decode(`, `assert(`, `system(`, `shell_exec(`) nei `.php` di `bootstrap/cache/`, `storage/` e `public/`, senza limite di data; `pg_dump` del database di sisteco salvato fuori dal server; `grep` di `php81_sisteco` fuori dal repo (crontab, `/etc`, `/var/www/html/geobox`) prima del cambio di nome
- [ ] Su geobox, ordine dell'intervento: rebuild dell'immagine con `--pull`, ricreazione del **solo** servizio `phpfpm` col nome nuovo (mai `docker compose up -d` senza nome del servizio), poi push su `main` con workflow e script già aggiornati, così il deploy trova `php82_sisteco`
- [ ] Verifica in produzione: `docker exec php82_sisteco php -v` riporta 8.2.35; sis-te.com risponde e Nova funziona; il deploy da GitHub Actions termina senza errori
- [ ] Su geobox: `000-default` disattivato, `a2dismod php7.4`, `php7.4-fpm` fermato e disabilitato, `apache2ctl configtest` ok; prima, verifica che nessun vhost attivo dipenda da `000-default` o da `mod_php`; dopo, `http://23.88.61.96/sisteco/.env`, `https://23.88.61.96/sisteco/.env` e una richiesta per IP con un Host qualsiasi non restituiscono più il `.env`, e tutti i siti dei container su geobox rispondono
- [ ] Il `docker-compose.yml` attivo su geobox (`/root/html/sisteco/docker-compose.yml`, che è lo stesso file di `/var/www/html/sisteco/docker-compose.yml`, non versionato) aggiornato con nome dell'immagine e del container nuovi
- [ ] Snapshot Hetzner del 22/10 cancellato dopo quello nuovo; snapshot del 23/10 mantenuto

## Rischi

- **Estensioni che non si compilano su Debian trixie** (es. `libmcrypt-dev` non più disponibile, pecl imagick sulle librerie nuove): si scopre nella build in locale, prima di toccare produzione. Se un pacchetto manca, si toglie dal Dockerfile se non è usato (mcrypt è già commentato fra le estensioni) o si ripiega su `php:8.2.35-fpm-bookworm`.
- **Avvisi di deprecazione di PHP 8.2 nel codice** (proprietà dinamiche non verificate in modo esaustivo): coperti dal giro in locale con dati reali e dal log di Laravel, con il canale delle deprecazioni attivo (`LOG_DEPRECATIONS_CHANNEL=single`); se emergono, si correggono nello stesso ticket.
- **Verifica di compromissione con zone cieche**: `git status` sul server copre solo i file tracciati, mentre `vendor/`, `bootstrap/cache/` e `storage/framework/views/` sono esclusi da git e scrivibili da `www-data`; `docker diff` non vede le cartelle montate; la data di un file si falsifica. Mitigazione: confronto di `vendor/` con un'installazione pulita e ricerca per contenuto senza limite di data.
- **Deploy che parte prima del container nuovo**: un push su `main` lancia `docker exec php82_sisteco`; se il container non esiste ancora, il deploy fallisce dopo il `git pull`. Mitigazione: sul server il container nuovo si crea prima del push.
- **Fermo di sis-te.com**: qualche decina di secondi di 503 alla ricreazione del container, più un minuto circa di manutenzione durante il deploy. Accettato, in orario di traffico basso; il cliente viene avvisato a fine ticket.
- **Chiusura di `000-default` che rompe un altro sito**: secondo la ricognizione del tag tutti i siti di produzione su geobox passano ai container via `proxy:fcgi`. Si verifica comunque l'elenco dei vhost attivi prima di disattivare, e si controlla che tutti rispondano dopo.
- **Ritorno indietro**: l'immagine 8.1 resta come `wm-phpfpm:8.1-pre-patch` (e il compose vecchio in copia) **solo come ritorno d'emergenza e per il tempo minimo**, perché è vulnerabile; lo stesso vale per lo snapshot, fatto dopo il 21/10. Se 8.2 rompe qualcosa si corregge in avanti: per questo la validazione in locale con dati reali va completata prima di toccare produzione. Il database ha in più un `pg_dump` fatto subito prima dell'intervento, che non dipende dallo snapshot.

## Out of scope

- Rotazione delle credenziali di sisteco: la fa Giuseppe in un secondo momento, su tutti i server (scrum 23/10), mai `JWT_SECRET`.
- Aggiornamento del sistema operativo di geobox (Ubuntu 20.04): ticket oc:8835.
- MPT, l'altro container PHP 8.1 sullo stesso server: ticket oc:8847.
- Riattivazione dei test in CI (oggi commentati).
- Aggiornamento di Laravel, Nova o delle dipendenze Composer.

## Moduli toccati

Tutto nel repo `sisteco2` (nessun package o submodule coinvolto), più la configurazione del server geobox:

- `docker/configs/phpfpm/Dockerfile`
- `docker/configs/phpfpm/init-xdebug.sh`
- `docker-compose.yml.example`
- `.github/workflows/prod_deploy.yml`, `.github/workflows/dev_deploy.yml`
- `scripts/deploy_local_with_prod.sh`, `scripts/deploy_release_1.1.0.sh`, `scripts/deploy_release_1.2.0.sh`
- `README.md`
- `.gitignore` (cartella del dump locale, se non già esclusa)
- Server geobox (fuori dal repo): `/root/html/sisteco/docker-compose.yml`, configurazione Apache (`000-default`, modulo `php7.4`), servizio `php7.4-fpm`, snapshot Hetzner
