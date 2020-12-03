# TecTools

![logo.png](template/tectools/images/logo.png)

TecTools er en abonnementsbaseret service, som udlejer værktøj til private.

URL: [TecTools](https://www.tectools.virtusb.com)

For at køre tests:

`include/vendor/bin/phpunit --testdox ../tests/`

- Systemkrav
  - PHP 7.4
  - MySQL 5.7

- Projekt oversigt
  - .github
    - workflows
      - CI.yml
        - Konfigurationsfil til GitHub Actions CI/CD
  - da_DK
    - Hunspell ordbog til IDE
  - docs
    - html
      - Dokumentation genereret af Doxygen.
      - Åbn index.html for at læse dokumentationen.
    - documents
      - Brainstorm for TecTools og andre dokumenter
    - images
      - ER diagram for databasen og andet
    - mockups
      - Mockups for TecTools siden
    - scripts
      - SQL scripts til oprettelse af database, tabeller og procedures
  - include
    - plugins
      - PHP klasser som RCMS automatisk loader
      - GlobalHandlers
      - RCMSTables
      - Tectools
    - vendor
      - Mappe hvor composer ligger biblioteker i
    - composer.json
      - Konfigurationsfil til composer, hvor man definere hvilke biblioteker projektet anvender
    - Cron.php
      - Klasse som kører og håndterer cronjobs
    - Helpers.php
      - Klasse som indeholder hjælpe funktioner
    - Login.php
      - Klasse som har metoder til at logge på og af siden
    - Logs.php
      - Klasse som har metoder til at logge hændelser på siden
    - Mailer.php
      - Klasse til at sende e-mails med SwiftMailer
    - RCMS.php
      - Hovedklassen for RCMS-systemet, som loader andre nødvendige klasser
    - StripeWrapper.php
      - Wrapper klasse til at arbejde med Stripe API'et
    - Template.php
      - Denne klasse står for at vise indhold til brugeren
  - Template
    - tectools
      - css
        - Mappen hvor CSS filer ligger
      - fontawesome
        - FontAwesome er et CSS ikon bibliotek
      - icons
        - Ikoner som bruges til TecTools designet
      - images
        - Billeder som bruges til TecTools designet
      - js
        - Mappen hvor klient kode ligger
      - layout
        - Mappen hvor alle de forskellige undersider på TecTools ligger
      - materialize
        - Mappen hvor CSS frameworket Materialize ligger
      - index.php
        - Entry-pointet for tectools templatet
  - tests
    - Mappe til unit tests
  - uploads
    - Mappe til billeder af værktøj
  - .gitignore
    - Konfigurationsfil til git, hvor man kan vælge hvilke filer git skal ignorere
  - .htaccess
    - Konfigurationsfil til apache
  - config.php
    - Konfigurationsfil til RCMS
  - Doxyfile
    - Konfigurationsfil til doxygen, til at generere dokumentation
  - index.php
    - Entry-point for TecTools / RCMS
  - README.me
    - Indeholder information omkring projektet

Style Guide
![Style_Guide_New@3x.png](docs/images/Style_Guide_New%403x.png)