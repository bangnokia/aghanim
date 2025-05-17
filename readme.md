# Aghanim

Enhance your Laravel + Inertia.js apps with **Aghanim**, a package that brings the power of Aghanim's Scepter to your frontend-backend interactions. Call Laravel Actions from React with a single line—like casting a spell: `aghanim.actions.getAllUsers()`. No custom APIs, just pure magic.

## Features

- **Seamless**: Proxy-based API eliminates API boilerplate
- **Type-Safe**: Generated TypeScript definitions for actions
- **Reactive**: Built for Inertia.js's SPA-like experience
- **Secure**: Built-in authentication, authorization, and validation
- **Developer-Friendly**: Detailed error handling and validation

## Installation

```bash
composer require bangnokia/aghanim
php artisan vendor:publish --tag=aghanim-config
npm install @bangnokia/aghanim
```

## Setup

1. Generate TypeScript action definitions:
   ```bash
   php artisan aghanim:generate-actions
   ```

2. Use in your React components:
   ```tsx
   import { useAghanim } from '@/hooks/useAghanim';
   import { aghanim } from '@/aghanim-actions';

   function Component() {
     const { call, result, loading, error } = useAghanim();
     
     const fetchUsers = async () => {
       try {
         const data = await call(aghanim.actions.getAllUsers);
         console.log('Users:', data);
       } catch (error) {
         console.error('Error fetching users:', error);
       }
     };
     
     return (
       <div>
         <button onClick={fetchUsers} disabled={loading}>
           {loading ? 'Loading...' : 'Fetch Users'}
         </button>
         
         {error && <div className="error">{error.error}</div>}
         {result && <pre>{JSON.stringify(result, null, 2)}</pre>}
       </div>
     );
   }
   ```

## Creating Actions

Actions are simple PHP classes that extend the `BangNokia\Aghanim\Actions\Action` base class. Each action must implement an `execute` method.

```php
<?php

namespace App\Actions;

use BangNokia\Aghanim\Actions\Action;

class GetUsers extends Action
{
    public function execute(int $limit = 10)
    {
        return \App\Models\User::limit($limit)->get();
    }
    
    // Optional: Define validation rules for parameters
    public function rules(): array
    {
        return [
            'limit' => 'integer|min:1|max:100',
        ];
    }
}
```

## Security

### Authentication

By default, all Aghanim routes are protected by the `auth` middleware. You can customize this in the `config/aghanim.php` file:

```php
'middleware' => ['web', 'auth'],
```

### Action Authorization

You can restrict which actions can be called from the frontend by configuring the `authorized_actions` option in `config/aghanim.php`:

```php
// Allow all actions
'authorized_actions' => 'all',

// Or restrict to specific actions
'authorized_actions' => [
    App\Actions\GetUsers::class,
    App\Actions\CreatePost::class,
],
```

### CSRF Protection

CSRF protection is enabled by default. You can disable it in the config if needed (not recommended):

```php
'csrf_protection' => true,
```

## Advanced Usage

### Validation

Actions can define validation rules for their parameters:

```php
public function rules(): array
{
    return [
        'email' => 'required|email',
        'name' => 'required|string|min:3',
    ];
}

// Custom validation messages
public function messages(): array
{
    return [
        'email.required' => 'Please provide an email address',
        'name.min' => 'Name must be at least 3 characters',
    ];
}
```

In your React components, you can handle validation errors:

```tsx
const { call, error, hasValidationErrors, getValidationErrors } = useAghanim();

// Check if there are validation errors
if (hasValidationErrors()) {
  // Get errors for a specific field
  const emailErrors = getValidationErrors('email');
  if (emailErrors) {
    console.log(emailErrors[0]); // First error message
  }
}
```

### TypeScript Generation

The `aghanim:generate-actions` command generates TypeScript definitions for your actions. By default, it only includes authorized actions. To generate definitions for all actions:

```bash
php artisan aghanim:generate-actions --all
```

## API Reference

### PHP

- `BangNokia\Aghanim\Actions\Action`: Base class for all actions
  - `execute(...$params)`: Abstract method that must be implemented
  - `rules()`: Define validation rules for parameters
  - `messages()`: Define custom validation messages
  - `validate($params)`: Validate parameters against rules

### JavaScript

- `useAghanim()`: React hook for calling actions
  - `call(action, params)`: Call an action
  - `result`: The result of the action
  - `error`: Any error that occurred
  - `loading`: Whether the action is in progress
  - `reset()`: Reset the state
  - `hasValidationErrors()`: Check if there are validation errors
  - `getValidationErrors(field)`: Get validation errors for a field

- `aghanimCall(action, params, options)`: Low-level function for calling actions
  - `options.onSuccess`: Callback for successful calls
  - `options.onError`: Callback for errors
  - `options.onStart`: Callback when the call starts
  - `options.onFinish`: Callback when the call finishes
  - `options.preserveState`: Whether to preserve Inertia state

## License

MIT

---

Unleash your app's ultimate potential with **Aghanim**—the scepter of Inertia.js!
