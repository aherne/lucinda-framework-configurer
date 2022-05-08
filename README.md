# lucinda-framework-configurer

This API configures a freshly installed Lucinda Framework 4.0 project via run method of CommandRunner class:

```php
$commandRunner = new Lucinda\Configurer\CommandRunner();
$commandRunner->run($option, $additionalParameters = []);
```

where value of $option can be:

- **project**: creates a project in current lucinda installation according to step-by-step user choices
- **vhost**: creates a virtual host for your project, available IF ran as root/superuser
- **environment**: (TBD) adds a new development environment to existing project according to step-by-step user choices