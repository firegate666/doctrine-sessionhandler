# PHP Doctrine session handler

Simple add on to support db based sessions for PHP using doctrine.

## Rules

1. This repository follows [PSR-2 standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
2. This project adheres to [semantic versioning](http://semver.org/).
3. This project follows [Keep a CHANGELOG rules](http://keepachangelog.com/)

## Install

    composer require firegate666/doctrine-sessionhandler

## Usage

    $sessionData = new SessionData(); // own class implementing SessionDataInterface
    $entityManager = EntityManager::create($connConfig, $config);
    session_set_save_handler(new SessionHandler($entityManager));

## Contribute

Fork the repository on [GitHub](https://github.com/firegate666/doctrine-sessionhandler) and submit pull requests

