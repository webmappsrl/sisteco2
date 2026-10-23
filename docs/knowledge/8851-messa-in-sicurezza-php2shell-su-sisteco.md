# PHP e deploy di sisteco in produzione

> Le righe marcate *(server)* descrivono geobox dopo l'intervento previsto da oc:8851 (task 5-7 del piano in `docs/features/8851-messa-in-sicurezza-php2shell-su-sisteco/`). Vanno ricontrollate sul server a intervento concluso.

## Come funziona oggi

- **Immagine PHP.** Il servizio `phpfpm` si costruisce da `docker/configs/phpfpm/Dockerfile`, con la versione di PHP bloccata nel `FROM` (`php:8.2.35-fpm-trixie`). L'immagine si chiama `wm-phpfpm:8.2` e il container `php82_sisteco`. Il nome contiene la versione: a ogni cambio di PHP vanno aggiornati insieme il compose, i due workflow, gli script in `scripts/`, `init-xdebug.sh` e il README.
- **Server** *(server)*. sis-te.com gira su geobox (23.88.61.96). L'Apache dell'host fa da proxy: il vhost `sis-te.com` inoltra a `fcgi://localhost:9104`, cioè al container. `000-default` e `mod_php` 7.4 sono disattivati, quindi chi entra per IP non raggiunge più le cartelle dei progetti. L'immagine precedente resta come `wm-phpfpm:8.1-pre-patch`: è un ritorno d'emergenza, ed è vulnerabile.
- **Compose in produzione.** `docker-compose.yml` non è versionato (`.gitignore:18`): nel repo c'è solo `docker-compose.yml.example`. Quello attivo è `/root/html/sisteco/docker-compose.yml`. `/root/html/sisteco` e `/var/www/html/sisteco` sono la stessa cartella.
- **Deploy.** Un push su `main` lancia `.github/workflows/prod_deploy.yml`, che fa `git pull` in `/root/html/sisteco` ed esegue `scripts/deploy_prod.sh` dentro `php82_sisteco` con `docker exec`. Il deploy **non** ricostruisce l'immagine. Un cambio al Dockerfile va applicato a mano sul server, ricreando il solo servizio `phpfpm`, **prima** del push che porta su `main` un workflow con un nome di container nuovo. Il push su `develop` lancia `dev_deploy.yml` verso `secrets.HOSTDEV`, un host che il censimento del tag php2shell non elenca.
- **Test.** In CI sono commentati, e `EstimateByCatalogTest` non viene eseguito (metodi senza prefisso `test`). La compatibilità di un cambio di PHP si verifica in locale con un dump di produzione e `LOG_DEPRECATIONS_CHANNEL=single`: con il valore `null` di `.env-example` le deprecazioni vengono scartate.

## Perché così

- **PHP 8.2.35** (oc:8851): il ramo 8.1 è fuori supporto e non ha una versione corretta per PHP2Shell (CVE-2026-109618/109619). Laravel 10.6.2 e Nova 4.27.14 supportano la 8.2. Su una copia dei dati reali, la stima per catalogo dà risultati identici su PHP 8.1.34 e 8.2.35.
- **Versione bloccata nel `FROM`** (oc:8851): con un tag mobile il nome dell'immagine smette di dire cosa gira.
- **Nome del container con la versione** (oc:8851): scelto perché il nome dica la verità sulla versione in uso.
- **`000-default` e `mod_php` 7.4 disattivati** (oc:8851): `000-default` serviva tutta `/var/www/html` a chi entrava per IP, e rendeva scaricabile il `.env` di sisteco e degli altri progetti sull'host.

## Come ci siamo arrivati

- **`php:8.1-fpm` con tag mobile, container `php81_sisteco`** (superata con oc:8851): in produzione il container girava su un'immagine senza tag del 2023 (`c4f319930e1d`, PHP 8.1.22), diversa da quella che il nome `wm-phpfpm:8.1` indicava.
