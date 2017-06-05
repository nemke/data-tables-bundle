# data-tables-bundle
DataTables Symfony bundle

Usage:

- composer install nemke/data-tables-bundle
- Add new Nemke\DataTablesBundle\DataTablesBundle() to AppKernel.php
- Add to /app/config/routing.yml:
    data_tables:
        resource: '@DataTablesBundle/Controller/'
        type: annotation

- Setup configuration inside /app/config/config.yml