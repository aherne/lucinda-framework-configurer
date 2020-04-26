# lucinda-framework-configurer

Works by executing run method of CommandRunner class:

```php
$commandRunner = new Lucinda\Configurer\CommandRunner();
$commandRunner->run($option, $additionalParameters = []);
```

where value of $option can be:

- **project**: creates a project in current lucinda installation according to step-by-step user choices
