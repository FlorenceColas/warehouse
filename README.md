## Getting started

Warehouse is an open source project which has been developed to learn Zend Framework 2. It is composed with four main functionalities:
* an inventory with available articles
* a manual in/out stock management
* a shopping list
* a recipes list with the ingredients based on articles referenced in the database

## Technical part

### Authentication Service

A service has been created to manage the authentication. The service is create using a factory. This factory is defined in `module.config.php`

```php 
    'service_manager' => [
        'factories' => [
            'AuthService' => 'Warehouse\Factory\AuthServiceFactory',
        ],
    ],
```

The service needs three files:
* `AuthServiceFactory.php` (available in [\module\Warehouse\src\Warehouse\Factory](module/Warehouse/src/Warehouse/Factory/)). It implements `FactoryInterface` and redefine the `createService` function

```php
class AuthServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AuthService($serviceLocator);
    }
}
```

* `AuthServiceInterface.php` (available in [\module\Warehouse\src\Warehouse\Service](module/Warehouse/src/Warehouse/Service/)). It contains two functions:

```php
interface AuthServiceInterface
{
    public function authenticateUser($logon, $password, $rememberMe);

    public function sessionIsValid();
}
```

* `AuthService.php` (available in [\module\Warehouse\src\Warehouse\Service](module/Warehouse/src/Warehouse/Service/)). It extends AuthenticationService:

```php
class AuthService extends AuthenticationService implements AuthServiceInterface
{
    ...
}
```


More content coming soon...
