# Nova Field Conditional

<br />

### Demo

![Demo](https://raw.githubusercontent.com/izi-dev/nova-conditional-if/master/docs/demo.gif)

<br />

### Installation

The package can be installed through Composer.

```bash
composer require izi-dev/nova-conditional-field
```

<br />

### Usage

Then, you will need to register the tool within the NovaServiceProvider.php:


```php
use IziDev\ConditionalField\ConditionalFieldTool;

/**
 * Get the tools that should be listed in the Nova sidebar.
 *
 * @return array
 */
public function tools()
{
    return [
        // other tools
        new ConditionalFieldTool,
    ];
}
```

```php
class Page extends Resource
{

    public function fields(Request $request)
    {
        return [
            
            Select::make(__('Type'), 'type')
                ->options([]),

            KeyValue::make(__('Options'), 'option')
                ->if(['type'], fn($value) => $value['type'] === 'value'))
        ];
    }
}
```

### License

The MIT License (MIT)
