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

```php
class Page extends Resource
{

    public function fields(Request $request)
    {
        return [
            
            Select::make(__('Type'), 'type')
                ->options([]),
            //Condition in BACKEND

            KeyValue::make(__('Options'), 'option')
                ->if(['type'], fn($value) => $value['type'] === 'value')
            
            //Condition in FRONT
                ->if(['type'], "_value.name === 'value'")

        ];
    }
}
```

### License

The MIT License (MIT)
