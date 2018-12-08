# AuditTrail module

## Configuration:

The module configuration is stored in AuditTrail/config.

* Create the **ConfigProvider.local.php** file with personal db credentials

* In the **module.config.php** file of the application, add the "AuditTrail" module name

```
return [
    'AuditTrail',
    ...
];
```
* In the **module.php** file of the application, load the specific AuditTrail configuration. It is stored in 2 config files:
  * ConfigProvider.php
  * ConfigProvider.local.php

* In the **composer.json** file, add the autoload for the namespace:
```
{
   "autoload": {
        "psr-4": {
            "AuditTrail\\": "module/AuditTrail/src/",
            ...
        }
    },
    ...
}
```

Execute the following command to complete the **/vendor/composer/autoload_classmap.php** with the AuditTrail classes:
```
composer dump-autoload -o
```

## Database:

This module requires a specific table to store the audit trail data. The structure of this table is stored in the "doc" folder:

```
AuditTrail/doc/audittrail.sql
```

