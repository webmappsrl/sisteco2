> Ticket: oc:8851

# Notes — Messa in sicurezza PHP2Shell su sisteco

## Divergenze dal piano, task per task

### Task 2 validazione in locale

- **Anteprima PDF sostituita da un caricamento di immagine.** L'app non gestisce PDF: l'unica collezione media è `gallery` di `CatalogArea` (`app/Models/CatalogArea.php:59-66`), che accetta solo immagini e non ha conversioni. Il controllo fatto: un JPEG caricato nella galleria su PHP 8.2, letto con Imagick (64x64) e poi cancellato.
- **`npm install` al posto di `npm ci`.** Il repo non ha `package-lock.json`. Il lock generato è stato cancellato e non entra nel commit; `public/build/` resta non tracciato.
- **`EstimateByCatalogTest` non viene eseguito.** I metodi non hanno né il prefisso `test` né l'annotazione, quindi PHPUnit 10 risponde «No tests found» ed esce con 1 per il warning. Succede anche su 8.1. Al suo posto è servito come prova il confronto descritto sotto, in «Decisioni».
- **Il dump è stato preso con utente e database del container** (`$POSTGRES_USER`/`$POSTGRES_DB`), senza leggere il `.env` di produzione.

## Bug trovati

- **La ricerca globale di Nova dà 500.** `app/Nova/Owner.php:34-36` cerca sulla colonna `name`, che in `owners` non esiste (ci sono `first_name`, `last_name`, `business_name`). È nel codice dal 2023 e non dipende da PHP: non corretto in questo ticket.

## Decisioni

- **La stima è identica su PHP 8.1 e 8.2.** Su una copia locale dei dati di produzione, `sisteco2:estimate_by_catalog` lanciato su PHP 8.1.34 e su 8.2.35 con lo stesso `.env` dà 0 differenze su 194 aree, sia in `estimated_value` sia in `catalog_estimate`.
- **I valori di stima salvati in produzione non coincidono con quelli ricalcolati** (sulle 194 aree cambiano la manutenzione e i totali che ne derivano). La causa sono i parametri `SISTECO_*` del `.env` (`config/sisteco.php:77`) o un calcolo fatto con una versione precedente del codice, non PHP: la differenza è la stessa con 8.1 e con 8.2.
- **Build locale ripartita dopo `docker builder prune -f`** (approvato dal dev): la VM di Docker aveva finito lo spazio.
- **Nessuna deprecazione PHP 8.2** nel log con `LOG_DEPRECATIONS_CHANNEL=single`, dopo il giro su Nova (indice e dettaglio di 6 risorse, dashboard, campi di modifica, azioni), la stima e il caricamento media.

## Follow-up

- Correggere `Owner::$search` (colonna `name` inesistente).
- Rendere eseguibile `EstimateByCatalogTest` (prefisso `test` o attributo `#[Test]`) e riattivare i test in CI.
- Capire perché le stime salvate in produzione differiscono da quelle ricalcolate (parametri `SISTECO_*` del `.env` di produzione o stime da ricalcolare).
