---
hide:
    - navigation
    - toc
---

# Getting started

This library is here to help abstract all the operations that involve the operating system the PHP code run on.

This kind of abstraction is useful to move away from implementation details and towards user intentions. By abstracting implementations details it becomes easier to port an application into a new environment and accomodate the differences at the implementation level without any change in the user intentions.

The other advantage to use higher level abstractions is to enable end user to build more complex applications by freeing them from thinking of low level details.

For concrete examples have a look at the use cases available in the sidebar.

!!! note ""
    This library is a small overlay on top of a set of individual libraries that contain the concrete abstractions. So you can start using only a subset of abstractions in your code as a starting point.

## Installation

```sh
composer require innmind/operating-system
```

## Basic usage

```php
use Innmind\OperatingSystem\Factory;

$os = Factory::build();
```

There's nothing more to add to start using this abstraction. Head toward the use cases to understand all the things you can do with it.

!!! warning ""
    This library doesn't work on windows environments.
