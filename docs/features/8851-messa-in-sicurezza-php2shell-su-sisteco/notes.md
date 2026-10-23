> Ticket: oc:8851

# Notes — Messa in sicurezza PHP2Shell su sisteco

## Divergenze dal piano, task per task

### Task 2 validazione in locale

- **Anteprima PDF sostituita da un caricamento di immagine.** L'app non gestisce PDF: l'unica collezione media è `gallery` di `CatalogArea` (`app/Models/CatalogArea.php:59-66`), che accetta solo immagini e non ha conversioni. Il controllo fatto: un JPEG caricato nella galleria su PHP 8.2, letto con Imagick (64x64) e poi cancellato.
- **`npm install` al posto di `npm ci`.** Il repo non ha `package-lock.json`. Il lock generato è stato cancellato e non entra nel commit; `public/build/` resta non tracciato.
- **`EstimateByCatalogTest` non viene eseguito.** I metodi non hanno né il prefisso `test` né l'annotazione, quindi PHPUnit 10 risponde «No tests found» ed esce con 1 per il warning. Succede anche su 8.1. Al suo posto è servito come prova il confronto descritto sotto, in «Decisioni».
- **Il dump è stato preso con utente e database del container** (`$POSTGRES_USER`/`$POSTGRES_DB`), senza leggere il `.env` di produzione.

### Task 6 geobox rebuild e deploy

- **Il Dockerfile per la build sul server è stato estratto dal branch della PR** (`origin/feature/oc-8851-…`) e non da `origin/develop`, così il server si è preparato senza aspettare il merge. Il contenuto è lo stesso.
- **La copia del compose attivo è in `/root/sisteco-docker-compose.yml.pre-8851`**, non accanto al file, perché `/var/www/html/sisteco` era ancora raggiungibile per IP tramite `000-default`. Il ritorno d'emergenza usa quella copia e `wm-phpfpm:8.1-pre-patch`: sul server il tag `wm-phpfpm:8.1` punta a un'altra immagine (`75d7629e2822`).
- **Il passo 7 ha richiesto anche un restart di Apache**, non solo un reload: dopo `a2dismod php7.4` e il reload, i 7 processi Apache avevano ancora `libphp` in memoria. Dopo il restart nessun processo la carica.

## Bug trovati

- **La ricerca globale di Nova dà 500.** `app/Nova/Owner.php:34-36` cerca sulla colonna `name`, che in `owners` non esiste (ci sono `first_name`, `last_name`, `business_name`). È nel codice dal 2023 e non dipende da PHP: non corretto in questo ticket.

## Decisioni

- **Pool FPM ripulito dopo il deploy, fuori dal piano approvato** (decisione del dev del 23/10). La strategia del tag php2shell lo prevede e il ticket lo cita al punto 6, ma l'overview non lo conteneva. In `docker/configs/phpfpm/www.conf`: `upload_max_filesize` da 1024M a 16M, `post_max_size` da 1024M a 128M, `session.upload_progress.enabled` off. I limiti sono dimensionati sul media più grande in produzione (8,6 MB) e sulla galleria Nova, che manda più foto in una richiesta, come per MPT (oc:8847). Verificato in locale con un nginx davanti all'FPM: 10 MB passano, 140 MB → 413, FPM vivo dopo.

- **La stima è identica su PHP 8.1 e 8.2.** Su una copia locale dei dati di produzione, `sisteco2:estimate_by_catalog` lanciato su PHP 8.1.34 e su 8.2.35 con lo stesso `.env` dà 0 differenze su 194 aree, sia in `estimated_value` sia in `catalog_estimate`.
- **I valori di stima salvati in produzione non coincidono con quelli ricalcolati** (sulle 194 aree cambiano la manutenzione e i totali che ne derivano). La causa sono i parametri `SISTECO_*` del `.env` (`config/sisteco.php:77`) o un calcolo fatto con una versione precedente del codice, non PHP: la differenza è la stessa con 8.1 e con 8.2.
- **Build locale ripartita dopo `docker builder prune -f`** (approvato dal dev): la VM di Docker aveva finito lo spazio.
- **Nessuna deprecazione PHP 8.2** nel log con `LOG_DEPRECATIONS_CHANNEL=single`, dopo il giro su Nova (indice e dettaglio di 6 risorse, dashboard, campi di modifica, azioni), la stima e il caricamento media.

- **Evidenze di compromissione pulite**: `docker diff` con soli file attesi (cache di Composer, cronologia di tinker, file di lavoro in `share/`); nessun `.php` sospetto per contenuto o modificato dal 20/10; `vendor/` del server identico a un'installazione pulita su 14.018 file (i 17 diversi sono file generati da Composer: autoload, `platform_check.php`, proxy in `vendor/bin/`); nei log di sis-te.com dal 20/10 solo scansioni automatiche, tutte 404. Le evidenze sono state conservate fuori dal server.
- **Il deploy dev è rotto da prima di questo ticket.** `dev_deploy.yml` lancia `geobox_dump_restore sisteco` su `secrets.HOSTDEV`, che non è geobox (lì `/var/www/html/.zshrc` non esiste). Sull'host dev `php81_sisteco` non c'è: il run del 23/10 e quello del 09/04 sono falliti. Il database di produzione è rimasto intatto: conteggi uguali al dump delle 14:38, nessun errore nel log di Postgres.

## Follow-up

- Correggere `Owner::$search` (colonna `name` inesistente).
- Rendere eseguibile `EstimateByCatalogTest` (prefisso `test` o attributo `#[Test]`) e riattivare i test in CI.
- Capire perché le stime salvate in produzione differiscono da quelle ricalcolate (parametri `SISTECO_*` del `.env` di produzione o stime da ricalcolare).
- Identificare l'host di `secrets.HOSTDEV` e decidere se il deploy dev va riparato o tolto. `geobox_dump_restore` fa `db:wipe` sul database dell'host su cui gira.
- `www.sis-te.com` non ha un vhost: in HTTPS presenta il certificato di `db.montepisanotree.org`, in HTTP risponde l'app MPT, che dopo la chiusura di `000-default` è il vhost di default. Serve un `ServerAlias www.sis-te.com` con il certificato.
- Rotazione delle credenziali di sisteco: la fa Giuseppe (scrum 23/10), mai `JWT_SECRET`.
