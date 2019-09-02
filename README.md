# FORTE FRAMEWORK: WORKER

This library consists of a set of classes that can be used to 
analyze and modify the content of a configured set of files.

## REQUIREMENTS

This application is built on top of ZendFramework and Symfony.

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

## HOW TO INSTALL

1. Download or clone this repository on your local machine on a desired 
installation folder;
2. Open a terminal on your pc, go to the folder where you just downloaded/cloned the repository and 
 unpack it if necessary;
3. From the same folder of step 2, run the following composer command and wait untill all dependencies 
have been downloaded:
    ```
    composer update
    ```
4. Create a test script in the project base folder. Here follows an example:
```php
<?php

require __DIR__.'/vendor/autoload.php';

use Forte\Worker\Exceptions\WorkerException;
use Forte\Worker\Filters\Arrays\ModifyArray;
use Forte\Worker\Transformers\Transforms\File\ModifyFile;

try {
    $modifyFile = new ModifyFile('.env');
    $modifyFile
        ->replaceLineIfLineStartsWith('APP_NAME', 'APP_NAME=NEW NAME')
        ->replaceValueIfLineStartsWith('APP_ENV', 'local', 'prod')
        ->removeValueIfLineStartsWith('BROADCAST_DRIVER', 'log')
        ->removeLineIfLineStartsWith('SESSION_LIFETIME')
        ->replaceWithTemplateIfLineEqualTo('testtemplate.txt', 'REPLACE_TAG=1')
        ->addTemplateIfLineEqualTo('testtemplate.txt', 'REPLACE_TAG2=2')
        ->run()
    ;
} catch (WorkerException $e) {
    exit;
}

die;
```
## TESTS

To run all tests, execute the following command from the base project folder:

```
./vendor/bin/phpunit --bootstrap vendor/autoload.php tests
```
