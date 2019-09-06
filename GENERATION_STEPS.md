# FORTE FRAMEWORK API GENRATION


##STEPS REQUIRED TO GENERATE A NEW API FROM THE SKELETON

1. [REQUIRED] From the project root folder, create a `.env` file from the existing `.env.example`;
2. [REQUIRED] Change the **APP_NAME** value in `.env` with the appropriate value from the Forte API configuration file 
(json file specified in the command line).
3. [REQUIRED] Change the **APP_URL** value in the `.env` file with the appropriate from the Forte API configuration file 
(json file specified in the command line).
4. [REQUIRED] Change all the DB configuration keys in `.env` with appropriate values from the Forte API configuration file 
(json file specified in the command line).
    - DB_HOST (default `127.0.0.1`);
    - DB_PORT (default `3306`)
    - DB_DATABASE (required)
    - DB_USERNAME (required)
    - DB_PASSWORD (required)
5. [OPTIONAL] Change all the mail configuration keys with appropriate values from the Forte API configuration file:
    - MAIL_DRIVER (default `smtp`);
    - MAIL_HOST (default `smtp.mailtrap.io`);
    - MAIL_PORT (default `2525`);
    - MAIL_USERNAME (required);
    - MAIL_PASSWORD (required);
    - MAIL_ENCRYPTION (default `tls`);  
    
    If mails are enabled, we should configure the following configuration key in the `.env` file: 
    - MAIL_FROM_NAME: this value is the name used to send out emails. It should be the same as the client application 
    consuming the API;
6. [REQUIRED] From the project root folder, create a `.env.testing` file from the existing `.env.testing.example`.
7. [REQUIRED] Change the **APP_NAME** value in `.env.testing` with the appropriate value from the Forte API configuration 
file (json file specified in the command line).
8. [REQUIRED] Change the **APP_URL** value in the `.env.testing` file with the appropriate from the Forte API configuration 
file (json file specified in the command line).
9. [REQUIRED] Change all the DB configuration keys in `.env.testing` with appropriate values from the Forte API 
configuration file (json file specified in the command line).
    - DB_HOST (default `127.0.0.1`);
    - DB_PORT (default `3306`)
    - DB_DATABASE (required)
    - DB_USERNAME (required)
    - DB_PASSWORD (required)
10. [REQUIRED] Change the application namespace with the following command: 
    
    ```
    php artisan app:name The desired namespace
    ```

    We should be able to configure the namespace. If not defined, we can create it from the application name 
    OR the company name defined in the API config file. You can call this artisan command from the code with the following block:

    ```php
    Artisan::call('app:name');
    ```     
11. [REQUIRED] Disable authentication routes, if not required (remove controllers and tests too).
12. [REQUIRED] Disable registration routes, if not required (remove controllers and tests too).
13. [REQUIRED] Disable user routes, if not required (remove controllers, tests and services too).
14. [REQUIRED] Disable client authentication for non-authenticated routes.
15. [OPTIONAL] Change routes names in `routes/web.php`, `routes/api.php` and `routes/client-request.php`: replace prefix 
`forte` with a configured API prefix route name. 
16. [OPTIONAL] Change the following configuration keys, in case this API is consumed by a client application:
    - CLIENT_APP_NAME: this configuration key contains the name of the client application (i.e. application that 
    consumes this API);
    - CLIENT_APP_URL: this configuration key contains the full URL of the client applciation (e.g. http://www.forteapp.dev);
17. [OPTIONAL] If the verification process is enabled, configure the following configuration key in the `.env` file.
    - CLIENT_EMAIL_VERIFY_URL: e.g. `/email/verify/{id}`; this configuration key holds a reference to the relative path to be used in the verification 
    email, so that the user, once clicked on the link, will be redirected to the client, which will be in charge of calling 
    the appropriate api endpoint to validate a given email. In this way, the client has full control of all the actions of the 
    verification process (call to the api, display of success or error message, eventually redirect the user to another page).
18. [OPTIONAL] If the authentication process is enabled, configure the following configuration key in the `.env`file:
    - CLIENT_PASSWORD_RESET_URL: e.g. `/password/reset`; this configuration key holds a reference to the relative path to be used in the forgot 
    password email. In this way, the client has full control of all the client related actions (e.g. displaying 
    a success or error page).