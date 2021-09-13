# Laravel Contacts

[![Latest Version on Packagist](https://img.shields.io/packagist/v/chuckcms/laravel-contacts.svg?style=flat-square)](https://packagist.org/packages/chuckcms/laravel-contacts)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/chuckcms/laravel-contacts/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/chuckcms/laravel-contacts/?branch=main)
[![StyleCI](https://github.styleci.io/repos/403383360/shield?branch=main)](https://github.styleci.io/repos/403383360?branch=main)
[![Total Downloads](https://img.shields.io/packagist/dt/chuckcms/laravel-contacts.svg?style=flat-square)](https://packagist.org/packages/chuckcms/laravel-contacts)

An easy way for attaching contacts to Eloquent models in Laravel.
Inspired by the following packages:
- [Lecturize/Laravel-Addresses](https://github.com/Lecturize/Laravel-Addresses)
- [spatie/laravel-permission](https://github.com/spatie/laravel-permission)

## Installation

Require the package by running

``` composer require chuckcms/laravel-contacts```

## Publish configuration and migration
``` php artisan vendor:publish --provider="Chuckcms\Contacts\ContactsServiceProvider" ```

This command will publish a ```config/contacts.php``` and a migration file.

> You can modify the default fields and their rules by changing both of these files.

After publishing you can run the migrations

``` php artisan migrate ```

## Usage

You can use the ```HasContacts``` trait on any model.

```php
<?php

namespace App\Models;

use Chuckcms\Contacts\Traits\HasContacts;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasContacts;

    // ...
} 
```

After doing this you can use the following methods.

#### Create a new contact and attach it to a model

```php
$company = Company::first();
$company->addContact([
	'first_name' 				=> 'John', // required
	'last_name' 				=> 'Doe', // defaults to: null
	'mobile' 				=> '+32.470123456', // defaults to: null
	'email' 				=> 'john.doe@email.com', // defaults to: null
	'is_public'				=> true, // defaults to: false
	'is_primary'				=> true, // defaults to: false
]);
```

#### Attach an existing contact to a model

```php
use Chuckcms\Contacts\Models\Contact;

$contact = Contact::first();
$company = Company::first();

$company->assignContact($contact);
```

#### Update an existing contact

```php
$company = Company::first();
$contact = $company->getPrimaryContact();

$company->updateContact($contact, ['middle_name' => 'In The Middle']);
```

#### Remove a contact from a model

```php
$company = Company::first();
$contact = $post->contacts()->first();

$company->removeContact($contact);
```

#### Remove all contacts from a model and replace with given contacts (sync)

```php
use Chuckcms\Contacts\Models\Contact;

$contacts = Contact::isPublic();
$company = Company::first();

$company->syncContacts($contacts);
// OR
$contacts = Contact::isPrimary()->pluck('id')->toArray();
$company->syncContacts($contacts);
// OR
$contact = Contact::isPrimary()->first();
$company->syncContacts($contact->id);
```

#### Delete a contact from any model it's linked to

```php
$company = Company::first();
$contact = $company->contacts()->first();

$company->deleteContact($contact);
```

#### Determine if a model has any contacts

```php
$company = Company::first();

if ($company->hasContacts()) {
	//do something
}
```

#### Determine if a model has (one of) the given contact(s).

```php
use Chuckcms\Contacts\Models\Contact;

$company = Company::find();
$contact = Contact::first();

if ($company->hasContact($contact)) {
	//do something
}

//OR
if ($company->hasContact($contact->id)) {
	//do something
}

//OR
$contacts = Contact::where('first_name', 'John')->get();
if ($company->hasContact($contacts)) {
	//do something
}

//OR
$contacts = Contact::where('last_name', 'Doe')->pluck('id')->toArray();
if ($company->hasContact($contacts)) {
	//do something
}
```
> This will return true when *one* of the given contacts belongs to the model.

## Getters

You can use the following methods to retrieve contacts and certain attributes.

#### Get the public contact of the model.

```php
$company = Company::first();

$publicContact = $company->getPublicContact();
```

#### Get the primary contact of the model.

```php
$company = Company::first();

$primaryContact = $company->getPrimaryContact();
```

#### Get the first names of all contacts of the model.

```php
$company = Company::first();

$first_names = $company->getContactFirstNames();
```

## License

Licensed under [MIT license](http://opensource.org/licenses/MIT).

## Author

**Written by [Karel Brijs](https://twitter.com/karelbrijs) in Antwerp.**