> Ticket: oc:8851

# Messa in sicurezza PHP2Shell su sisteco — piano di implementazione

> **Per chi esegue:** sotto-skill richiesta: `superpowers:executing-plans` (o `superpowers:subagent-driven-development` per i task 1-3). I passi usano le checkbox (`- [ ]`). **Nessun `git commit`, `git push` o branch automatico:** i commit qui sotto sono istruzioni per il dev, da eseguire solo dopo la sua approvazione. **Ogni comando su geobox lo lancia il dev** (`! ssh root@23.88.61.96 '...'`): il classificatore dell'auto mode blocca le chiamate SSH verso la produzione.

**Obiettivo:** portare sisteco da PHP 8.1.22 a PHP 8.2.35 su geobox e chiudere l'accesso per IP che espone i `.env`.

**Architettura:** si cambia l'immagine del solo servizio `phpfpm` (Dockerfile versionato), si rinomina il container in `php82_sisteco` in tutti i riferimenti del repo, si valida in locale con dati reali, poi in produzione si segue l'ordine snapshot → evidenze → rebuild → push su `main` → verifiche → chiusura di `000-default` e `mod_php` 7.4.

**Stack:** Laravel 10.6.2, Nova 4.27.14, PostgreSQL/PostGIS 14-3.3, Docker Compose, Apache sull'host (proxy `fcgi`), GitHub Actions.

**Spec:** [overview.md](overview.md)

## Vincoli globali

- Immagine base: `php:8.2.35-fpm-trixie` (ripiego: `php:8.2.35-fpm-bookworm` solo se un pacchetto apt manca su trixie)
- Nome container: `php82_${APP_NAME}` → in produzione `php82_sisteco`; immagine `wm-phpfpm:8.2`
- Estensioni che devono comparire in `php -m`: gd, intl, zip, PDO, pdo_mysql, Zend OPcache, pgsql, pdo_pgsql, exif, imagick, apcu
- Immagine di ritorno: `docker tag c4f319930e1d wm-phpfpm:8.1-pre-patch` — solo emergenza, è vulnerabile
- Compose attivo su geobox: `/root/html/sisteco/docker-compose.yml` (stessa cartella di `/var/www/html/sisteco`, file non versionato)
- Mai `docker compose up -d` senza nome del servizio; rebuild sempre con `--pull`; verifica sempre con `php -v` dentro il container
- Rotazione credenziali fuori scope (Giuseppe), mai `JWT_SECRET`
- Il dump di produzione vive solo in `docker/volumes/share/` (escluso da git da `/docker/volumes/*`, `.gitignore:16`, e montato nel container db in `/root/share`) e si cancella a fine ticket

## Punti da tenere d'occhio in review

1. Una pagina Nova che su 8.2 emette deprecazioni solo con dati reali (proprietà dinamiche): il log va letto con `LOG_DEPRECATIONS_CHANNEL=single` → Task 2, passo 6.
2. Il merge su `develop` fa partire il deploy dev (`secrets.HOSTDEV`, host non censito) con `docker exec php82_sisteco`: se quell'host esiste e ha ancora `php81_sisteco`, il deploy dev fallisce → Task 4, passo 3.
3. Un push su `main` prima che `php82_sisteco` esista sul server rompe il deploy di produzione dopo il `git pull` → Task 6: il merge `develop`→`main` è l'ultimo passo, dopo la ricreazione del container.
4. Dopo `a2dissite 000-default` un altro vhost diventa il default per l'IP, anche su 443 → Task 7, passo 5: test su http, https e Host qualsiasi.
5. Riferimenti a `php81_sisteco` fuori dal repo (crontab, alias geobox) che falliscono in silenzio dopo il rename → Task 5, passo 7.

---

### Task 1: Immagine PHP 8.2 e rename nel compose di esempio

**File:**
- Modifica: `docker/configs/phpfpm/Dockerfile:1`
- Modifica: `docker-compose.yml.example:9-10`
- Modifica: `docker/configs/phpfpm/init-xdebug.sh:4`

**Interfacce:**
- Produce: immagine locale `wm-phpfpm:8.2`, container `php82_sisteco`, usati da Task 2 e Task 6.

- [ ] **Passo 1:** `Dockerfile:1` → `FROM php:8.2.35-fpm-trixie`. Il resto del Dockerfile resta com'è salvo pacchetti apt inesistenti su trixie: se il build fallisce su `libmcrypt-dev`, toglilo (mcrypt è già commentato fra le estensioni). Se fallisce su altro, prova `php:8.2.35-fpm-bookworm` e annota la scelta in `notes.md`.
- [ ] **Passo 2:** `docker-compose.yml.example:9-10` → `container_name: "php82_${APP_NAME}"` e `image: wm-phpfpm:8.2`. `init-xdebug.sh:4` → `containerName="php82_$APP_NAME"`.
- [ ] **Passo 3: ambiente locale.** `cp .env-example .env`, poi nel `.env`: `LOG_DEPRECATIONS_CHANNEL=single`. `cp docker-compose.yml.example docker-compose.yml`. Controlla che le porte 8004, 9104, 5504 siano libere (`docker ps --format '{{.Names}} {{.Ports}}'`); se sono occupate da un altro progetto, chiedi al dev prima di fermarlo.
- [ ] **Passo 4: build.** `docker compose build --pull phpfpm` → esito atteso: build completata senza errori.
- [ ] **Passo 5: verifica.** `docker compose up -d phpfpm db`, poi:
  - `docker exec php82_sisteco php -v` → atteso: `PHP 8.2.35`
  - `docker exec php82_sisteco php -m` → atteso: tutte le estensioni dei vincoli globali presenti
  - `docker exec php82_sisteco php -r 'echo Imagick::getVersion()["versionString"], PHP_EOL;'` → atteso: una riga con la versione di ImageMagick, nessun errore
- [ ] **Passo 6: commit (istruzione per il dev).**
  ```bash
  git add docker/configs/phpfpm/Dockerfile docker-compose.yml.example docker/configs/phpfpm/init-xdebug.sh
  git commit -m "fix(oc:8851): immagine php-fpm su php:8.2.35-fpm-trixie, container php82_"
  ```

### Task 2: Validazione in locale con i dati di produzione

> ⚠️ L'implementazione ha deviato da questo task: [notes.md](notes.md#task-2-validazione-in-locale)

**File:**
- Nessuno previsto. Se emergono deprecazioni o errori: i file dell'app che li generano (correzione minima, ciascuna annotata in `notes.md`).

**Interfacce:**
- Consuma: container `php82_sisteco` e `postgres_sisteco` di Task 1.
- Produce: esito della validazione, prerequisito del Task 5.

- [ ] **Passo 1: dump (lo lancia il dev).** Sul server, dal container db di produzione, in formato custom:
  ```bash
  ! ssh root@23.88.61.96 'docker exec postgres_sisteco pg_dump -U sisteco -Fc sisteco' > docker/volumes/share/sisteco-prod.dump
  ```
  Atteso: file non vuoto (`ls -lh docker/volumes/share/sisteco-prod.dump`). Se l'utente del DB in produzione non è `sisteco`, leggerlo da `/root/html/sisteco/.env` (`DB_USERNAME`) senza stampare la password.
- [ ] **Passo 2: ripristino.** `docker exec postgres_sisteco pg_restore -U sisteco -d sisteco --clean --if-exists --no-owner /root/share/sisteco-prod.dump`. Atteso: nessun errore bloccante (gli avvisi su estensioni PostGIS già presenti sono accettabili).
- [ ] **Passo 3: dipendenze.** `docker exec php82_sisteco composer install`, `docker exec php82_sisteco php artisan key:generate`, `docker exec php82_sisteco php artisan migrate --pretend` → atteso: nessuna migrazione in sospeso. `npm ci && npm run build` sull'host (servono gli asset Vite, `public/build` non è versionato).
- [ ] **Passo 4: test.** `docker exec php82_sisteco php artisan test` → atteso: PASS. Attenzione: `EstimateByCatalogTest` usa `RefreshDatabase` e svuota il DB: lancialo **dopo** il giro manuale, oppure ripeti il passo 2 dopo il test.
- [ ] **Passo 5: giro manuale** con `docker exec php82_sisteco php artisan serve --host=0.0.0.0` (porta 8004), utente Nova creato con `php artisan nova:user` se non ne hai uno:
  - login a Nova;
  - indice e dettaglio di Owner, CadastralParcel, Catalog, CatalogArea, CatalogType su record reali;
  - una stima per catalogo su particelle reali (`php artisan sisteco2:estimate_by_catalog` o l'azione Nova equivalente);
  - caricamento di un PDF in un campo media e generazione dell'anteprima.
- [ ] **Passo 6: log.** `grep -c -E 'ERROR|Deprecated|deprecated' storage/logs/laravel.log` → atteso: `0`. Ogni riga trovata va corretta (Task 2 resta aperto) e annotata in `notes.md`.
- [ ] **Passo 7: commit (solo se ci sono state correzioni, istruzione per il dev).** `fix(oc:8851): compatibilità PHP 8.2 in <file>`.

### Task 3: Rename del container nei workflow, negli script e nel README

**File:**
- Modifica: `.github/workflows/prod_deploy.yml:25,48`, `.github/workflows/dev_deploy.yml:25,48`
- Modifica: `scripts/deploy_local_with_prod.sh:13,36`, `scripts/deploy_release_1.1.0.sh:18-26`, `scripts/deploy_release_1.2.0.sh:51-60`
- Modifica: `README.md:61,78,85,176,228,254`

**Interfacce:**
- Consuma: nome container `php82_sisteco` / `php82_${APP_NAME}` di Task 1.

- [ ] **Passo 1:** nei due workflow `php-version: "8.2"` e `docker exec php82_sisteco`. Negli script e nel README `php81_` → `php82_`; `README.md:61` → «php 8.2». Gli script `deploy_release_*` sono storici (il 1.2.0 contiene `db:wipe`): si rinomina e basta, **non si eseguono**.
- [ ] **Passo 2: verifica.** `grep -rn -E 'php81|php:8\.1|wm-phpfpm:8\.1|php-version: "8\.1"' --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=docs .` → atteso: nessun risultato.
- [ ] **Passo 3: commit (istruzione per il dev).**
  ```bash
  git add .github/workflows scripts README.md
  git commit -m "chore(oc:8851): container php82_sisteco e CI su PHP 8.2"
  ```

### Task 4: PR verso `develop`

- [ ] **Passo 1 (istruzione per il dev):** push del branch `feature/oc-8851-messa-in-sicurezza-php2shell-su-sisteco` e PR verso `develop`.
- [ ] **Passo 2:** la CI della PR non gira (i workflow partono solo su push a `develop`/`main`): la garanzia è il Task 2.
- [ ] **Passo 3:** al merge su `develop` parte `Laravel deploy DEVELOP` verso `secrets.HOSTDEV`. Controlla l'esito in Actions. Se fallisce su `docker exec php82_sisteco` (host dev con il container vecchio), annotalo in `notes.md` e segnalalo al dev: l'host dev non è censito nel tag e non si tocca senza decisione.
- [ ] **Passo 4:** **non** aprire ancora la PR `develop` → `main`: arriva al Task 6, passo 6.

### Task 5: geobox — snapshot ed evidenze (prima di toccare qualsiasi cosa)

Tutti i comandi li lancia il dev con `! ssh root@23.88.61.96 '...'`. Gli output vanno salvati fuori dal server, in `docker/volumes/share/evidenze-8851/` sul portatile (esclusa da git).

- [ ] **Passo 1:** snapshot Hetzner nuovo di geobox dal pannello (Snapshots). Atteso: snapshot in stato «disponibile».
- [ ] **Passo 2:** `docker diff php81_sisteco` → salva l'output.
- [ ] **Passo 3:** `docker tag c4f319930e1d wm-phpfpm:8.1-pre-patch` e `docker image inspect wm-phpfpm:8.1-pre-patch --format '{{.Id}}'` → atteso: `sha256:c4f319930e1d…`.
- [ ] **Passo 4:** `pg_dump` come Task 2, passo 1, in `evidenze-8851/sisteco-pre-patch.dump`.
- [ ] **Passo 5: `vendor/`.** Sul server `cd /var/www/html/sisteco && find vendor -type f -print0 | sort -z | xargs -0 sha256sum` → salva; in locale lo stesso comando sul `vendor/` del Task 2 (stesso `composer.lock`). `diff` dei due elenchi → atteso: nessuna differenza. Ogni differenza va guardata a mano.
- [ ] **Passo 6: contenuto e log.**
  - `grep -rlE "eval\(|base64_decode\(|assert\(|system\(|shell_exec\(" --include=*.php /var/www/html/sisteco/bootstrap/cache /var/www/html/sisteco/storage /var/www/html/sisteco/public` → ogni file trovato va letto;
  - `find /var/www/html/sisteco/public /var/www/html/sisteco/storage -name '*.php' -newermt 2026-10-20`;
  - log Apache di sis-te.com dal 20/10 (file indicati da `apache2ctl -S` per il vhost): richieste `POST` verso `.php` diversi da `index.php`, query con `-d`/`allow_url_include`/`auto_prepend_file`, risposte 200 su percorsi inesistenti nel repo.
  Se emerge qualcosa: **fermarsi** e portarlo al dev prima del rebuild.
- [ ] **Passo 7:** `grep -rn php81_sisteco /etc /var/spool/cron /var/www/html/geobox 2>/dev/null` → ogni risultato va aggiornato a `php82_sisteco` al Task 6, passo 4.

### Task 6: geobox — rebuild, nuovo container, deploy

> ⚠️ L'implementazione ha deviato da questo task: [notes.md](notes.md#task-6-geobox-rebuild-e-deploy)

- [ ] **Passo 1: Dockerfile nuovo senza sporcare il working tree** (il `git pull` del deploy fallirebbe con modifiche locali):
  ```bash
  cd /root/html/sisteco && git fetch origin && rm -rf /tmp/sisteco-8851 && mkdir /tmp/sisteco-8851 \
    && git archive origin/develop docker/configs/phpfpm | tar -x -C /tmp/sisteco-8851
  docker build --pull -t wm-phpfpm:8.2 /tmp/sisteco-8851/docker/configs/phpfpm
  ```
  Atteso: build completata.
- [ ] **Passo 2: compose attivo.** `cp docker-compose.yml docker-compose.yml.pre-8851`, poi in `docker-compose.yml` `container_name` → `php82_${APP_NAME}` (o `php82_sisteco` se scritto per esteso) e `image: wm-phpfpm:8.2`. `diff docker-compose.yml.pre-8851 docker-compose.yml` → atteso: solo quelle due righe.
- [ ] **Passo 3: ricreazione del solo servizio** (in un orario di traffico basso): `docker compose up -d --no-build --no-deps phpfpm`. Atteso: `php81_sisteco` rimosso, `php82_sisteco` avviato, `postgres_sisteco` non toccato (`docker ps` mostra il suo uptime invariato).
- [ ] **Passo 4:** aggiorna i riferimenti trovati al Task 5, passo 7.
- [ ] **Passo 5: verifica.** `docker exec php82_sisteco php -v` → `PHP 8.2.35`; `docker exec php82_sisteco php -m` → estensioni dei vincoli globali; `curl -sI https://sis-te.com` → `200` (o il redirect al login); login a Nova da browser.
- [ ] **Passo 6 (istruzione per il dev):** PR `develop` → `main` e merge. Parte `Laravel deploy PROD` (`git pull` + `docker exec php82_sisteco bash scripts/deploy_prod.sh`). Atteso: job verde. Se fallisce dopo `php artisan down`, il sito resta in manutenzione: `docker exec php82_sisteco php artisan up` subito, poi si indaga.
- [ ] **Passo 7:** `git -C /root/html/sisteco log -1 --oneline` → il merge su `main`; `git status --short` → vuoto; ripeti il passo 5.

**Ritorno d'emergenza** (solo se sis-te.com è giù e non si corregge in pochi minuti): `cp docker-compose.yml.pre-8851 docker-compose.yml`, `docker tag wm-phpfpm:8.1-pre-patch wm-phpfpm:8.1`, `docker compose up -d --no-build --no-deps phpfpm`. È PHP vulnerabile: si torna avanti appena possibile.

### Task 7: geobox — chiusura di `000-default`, `mod_php` 7.4, `php7.4-fpm`

- [ ] **Passo 1: copia della configurazione.** `tar czf /root/apache2-pre-8851.tgz /etc/apache2` e `a2query -M` (annota l'MPM).
- [ ] **Passo 2: chi dipende da cosa.**
  - `apache2ctl -S` → elenco dei vhost attivi: annota quale è il default su `*:80` e su `*:443` dopo `000-default`;
  - `grep -rnE 'DocumentRoot|SetHandler|php7|proxy:fcgi' /etc/apache2/sites-enabled /etc/apache2/conf-enabled` → atteso: nessun vhost o conf che usi `mod_php`/`php7.4-fpm`; nessun `DocumentRoot` uguale a `/var/www/html`.
  Se qualcosa dipende da `mod_php` o da `000-default`: **fermarsi** e portarlo al dev.
- [ ] **Passo 3:** `a2dissite 000-default && a2dismod php7.4 && apache2ctl configtest` → atteso: `Syntax OK`. Se `a2dismod` chiede di cambiare MPM, fermarsi e chiedere al dev. Poi `systemctl reload apache2`.
- [ ] **Passo 4:** `systemctl disable --now php7.4-fpm` → `systemctl is-active php7.4-fpm` → atteso: `inactive`.
- [ ] **Passo 5: verifica dall'esterno** (dal portatile, non dal server):
  ```bash
  curl -s -o /dev/null -w '%{http_code}\n' http://23.88.61.96/sisteco/.env
  curl -sk -o /dev/null -w '%{http_code}\n' https://23.88.61.96/sisteco/.env
  curl -s -o /dev/null -w '%{http_code}\n' -H 'Host: qualsiasi.example' http://23.88.61.96/sisteco/.env
  ```
  Atteso: nessuno dei tre è `200`. Poi `curl -sI` su ogni dominio elencato da `apache2ctl -S` → atteso: stesso codice di prima del passo 3.
- [ ] **Passo 6:** aggiorna la sezione geobox del tag php2shell (Orchestrator): `000-default`, `mod_php` 7.4 e `php7.4-fpm` chiusi su geobox con oc:8851. Vale anche per i `.env` di orchestrator, mpt, osmfeatures e geohub sullo stesso host.

### Task 8: pulizia

- [ ] **Passo 1:** snapshot Hetzner del 22/10 cancellato dal pannello; quello del 23/10 e quello del Task 5 restano.
- [ ] **Passo 2:** sul portatile `rm -rf docker/volumes/share/sisteco-prod.dump docker/volumes/share/evidenze-8851/*.dump` e `docker compose exec db psql -U sisteco -c 'DROP SCHEMA public CASCADE; CREATE SCHEMA public;' sisteco` (o `docker compose down -v` + `rm -rf docker/volumes/postgresql`). Gli output testuali delle evidenze restano finché il ticket è aperto.
- [ ] **Passo 3:** sul server `rm -rf /tmp/sisteco-8851`; `docker-compose.yml.pre-8851` e `/root/apache2-pre-8851.tgz` restano come copia.
- [ ] **Passo 4:** `notes.md` aggiornato con l'esito di Task 2 (deprecazioni), Task 5 (evidenze) e Task 7 (vhost di default dopo la chiusura).
