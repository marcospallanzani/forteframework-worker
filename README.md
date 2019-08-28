# FORTE API GENERATOR

This application is in charge to generate a Forte API from the forte-api-skeleton project. 
the base project is the `forteframework-api-skeleton` repository.

## REQUIREMENTS

This application is built with PHP, ZendFramework and Symfony.

It also requires the following PHP modules to be installed:

- php-curl
- ext-zip

##### Curl Module

For unix user, use the following command: 
```
sudo apt-get install curl libcurl3 libcurl3-dev php7-curl
```

##### Zip Module

For unix user, use the following command: 
```
sudo apt-get install php-zip
```

On a dockerized environment:
```
# Install zip
RUN apt-get update && \
     apt-get install -y \
         libzip-dev \
         && docker-php-ext-install zip
```

## TESTS

To run all tests, execute the following command from the base project folder:

```
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests
```
