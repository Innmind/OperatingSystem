# SQL connection

You can build a connection to a SQL server like so:

```php
use Innmind\Url\Url;
use Formal\AccessLayer\Connection;

$connection = $os->remote()->sql(Url::of('mysql://127.0.0.1:3306/database_name'));

$connection instanceof Connection; // true
```

By default it uses a lazy connection, meaning it won't open the connection until the first query.

For more information on to query the database with this abstraction, visit the [dedicated documentation](https://formal-php.github.io/access-layer/).
