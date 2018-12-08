# Auth module

## Required configuration:

The module configuration is stored in Auth/config.

* Create the **ConfigProvider.local.php** file with personal db credentials

* In the **module.config.php** file of the application, add the "Auth" module name

```
return [
    'AuditTrail',
    ...
];
```
* In the **module.php** file of the application, load the specific Auth configuration. It is stored in 2 config files:
  * ConfigProvider.php
  * ConfigProvider.local.php

* In the **composer.json** file, add the autoload for the namespace:
```
{
   "autoload": {
        "psr-4": {
            "Auth\\": "module/Auth/src/",
            ...
        }
    },
    ...
}
```

Execute the following command to complete the **/vendor/composer/autoload_classmap.php** with the Auth classes:
```
composer dump-autoload -o
```

## Required tables structures:

This module requires a specific table to store the users information. The structure of this table is stored in the "doc" folder:

```
Auth/doc/user.sql
```

## Dependencies:

This module requires the AuditTrail module, in order to track the login/logout/timeout activities.
