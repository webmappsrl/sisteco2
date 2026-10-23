# sisteco2

Applicazione Laravel 10 + Nova 4 per stimare i servizi ecosistemici sulle particelle catastali (cliente Timesis), in produzione su https://sis-te.com. Gira in Docker: container `php82_sisteco` (PHP-FPM) e `postgres_sisteco` (PostGIS). Repo singolo, nessun submodule: `wm-internal/` è una cartella copiata nel repo.

## Regole del repo

- Un push su `main` è un deploy in produzione: il workflow fa `git pull` sul server ed esegue `scripts/deploy_prod.sh` dentro `php82_sisteco`, senza ricostruire l'immagine.
- Quando cambi la versione di PHP o il nome del container, aggiorna nello stesso lavoro compose di esempio, i due workflow, `scripts/`, `init-xdebug.sh` e README, e ricrea il servizio `phpfpm` sul server **prima** del push su `main`: dettaglio in [docs/knowledge/8851-messa-in-sicurezza-php2shell-su-sisteco.md](docs/knowledge/8851-messa-in-sicurezza-php2shell-su-sisteco.md).

## Conoscenza

| Argomento | Cosa copre | Pagina |
|---|---|---|
| PHP e deploy in produzione | immagine PHP, compose non versionato, flusso di deploy, come si valida un cambio di PHP | [docs/knowledge/8851-messa-in-sicurezza-php2shell-su-sisteco.md](docs/knowledge/8851-messa-in-sicurezza-php2shell-su-sisteco.md) |
