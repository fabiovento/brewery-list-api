## Presentazione

Questo progetto si compone primariamente di un sistema API (che fa da proxy verso le API pubbliche esposte da https://www.openbrewerydb.org/), composto dai seguenti endpoint:
- `/api/login`
  - Metodo: `POST`
  - Funzione: tramite Laravel Sanctum, i dati trasmessi dall'utente e, in caso positivo, restituisce un token univoco per le successive richieste.
  - Payload della richiesta:
  ```
      {
         "email": "...",
         "password": "..."
      }
  ```
  - Payload esito negativo (JSON malformato): codice HTTP 422.
  ```
      {
         "status": "error",
         "code": 422,
         "errors": {
             "email": [
                 "The email field is required."
             ],
             "password": [
                 "The password field is required."
             ]
         }
      }
  ```
  - Payload esito negativo (dati di autenticazione errati): codice HTTP 401.
  ```
      {
         "status": "error",
         "code": 401,
         "errors": "These credentials do not match our records."
      }
  ```
  - Payload esito positivo: codice HTTP 200.
  ```
      {
         "status": "ok",
         "access_token": "<token>",
         "token_type": "Bearer"
      }
  ```
- `/api/list`
   - Metodo: `GET`
   - Funzione: fornisce una "pagina" dell'elenco breweries, interpellando a sua volta l'endpoint API pubblico https://api.openbrewerydb.org/v1/breweries e restituendone l'output.
   - Parametri:
     - `page`. Opzionale. Determina a quale "pagina" dell'elenco breweries si desidera accedere. Se non specificato viene assunto pari a 1.
   - Header della richiesta:
   ```
      Authorization: Bearer <token>
   ``` 
   - Payload esito positivo: codice HTTP 200.
   ```
      {
         "status": "ok",
         "data": [
              {
                 "id": "4a09f017-db8f-42e1-a8ec-a0cd81c28761",
                 "name": "1940's Brewing Company",
                 "brewery_type": "micro",
                  ...
   ```
   - Payload esito negativo (token assente o errato): codice HTTP 401.
   ```
      {
         "status": "error",
         "code": 401,
         "errors": "Unauthenticated."
      }
   ```
- `/api/logout`
  - Metodo: `GET`
  - Funzione: revoca il token univoco.
  - Header della richiesta:
   ```
      Authorization: Bearer <token>
   ``` 
  - Payload esito positivo: codice HTTP 200.
   ```
      {
         "status": "ok"
      }
   ```
  - Payload esito negativo (token assente o errato): codice HTTP 401.
   ```
      {
         "status": "error",
         "code": 401,
         "errors": "Unauthenticated."
      }
   ```

È presente anche una pagina SPA, accessibile via browser su `/`, il cui flusso è il seguente:
- Appare inizialmente un form di login (e-mail, password) che, se inviato, effettua un tentativo di autenticazione sull'endpoint `/api/login`.
  - Se le credenziali inserite non sono corrette, visualizza un messaggio di errore.
  - Altrimenti viene richiamato, tramite token ottenuto, l'endpoint `/api/list` per ottenere la prima "pagina" delle breweries. L'output di tale endpoint viene renderizzato in forma tabellare con paginatore.
    - Se l'utente seleziona un elemento del paginatore, viene richiamato lo stesso endpoint `/api/list`, passando però il parametro `page=n` per ottenere l'n-esima "pagina" delle breweries, che viene renderizzata a sua volta in forma tabellare.
    - Se l'utente seleziona il bottone "Logout" viene richiamato l'endpoint `/api/logout` per la revoca del token di autenticazione, e l'applicazione torna al form di login.

Dati utente per login valido:

```
E-mail: root@root.com
Password: password
```

Sono inclusi, nella directory `api_requests`, export delle request alle tre API, nei formati `Insomnia (.yaml)` e `HAR`.

## Infrastruttura

L'infrastruttura poggia su container Docker (Laravel Sail):

- Laravel 12.17.0
- MySQL 8.0.32

L'autenticazione è gestita tramite Laravel Sanctum (https://github.com/laravel/sanctum) che genera un token revocabile
composto da una stringa opaca. Le credenziali dell'utente risedono nella tabella `users` di MySQL con password sottoposta a hashing tramite `bcrypt`.

La pagina web che effettua login e renderizza in forma tabellare i dati ricevuti è una SPA React che richiama
direttamente le API esposte.

## Pattern

Il codice è stato steseo tenendo conto di pattern, che ne garantiscono la manutenibilità e riutilizzabilità:
- Model View Controller
- S.O.L.I.D.
- Service Pattern
- Dependency Injection Pattern
- Exception Handler pattern

## Deploy

Istuzioni per il deploy su ambiente locale:

```
git clone git@github.com:fabiovento/brewery-list.git
cd brewery-list-api
cp .env.example .env
composer install
./vendor/bin/sail up --build
sudo docker exec -it brewery-list-api-laravel.test-1 bash
```

Dentro il container:

``` 
php artisan migrate
``` 

Nella configurazione base, tanto le API esposte quanto la pagina web pubblicata sono accessibili sulla radice http://localhost tramite esposizione diretta sulla porta 80 da parte del container principale.

## Test

Sono presenti tre test incentrati sull'API di login:

- Unitario (`tests\Unit\AuthUnitTest`)

Testa internamente la routine di validazione connessa alla API di login, senza richiamarne l'endpoint. Istanzia
AuthController e fa un mock di AuthService.

- Funzionale (`tests\Unit\AuthFeatureTest`)

Testa l'effettiva funzionalità della API di login, richiamando esternamente il suo endpoint. Fa ancora mock di
AuthService.

- Integrazione (`tests\Unit\AuthIntegrationTest`)

Testa la funzionalità della API di login e la sua integrazione con il vero servizio AuthService e con l'API di elenco
birrerie (al cui endpoint trasmette il token ricevuto).

Per eseguire tali test, è sufficiente dare il seguente comando dentro il container principale:

``` 
php artisan test
``` 
