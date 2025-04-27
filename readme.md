# Aghanim

This is not working yet

Enhance your Laravel + Inertia.js apps with **Aghanim**, a package that brings the power of Aghanim’s Scepter to your frontend-backend interactions. Call Laravel Actions from React with a single line—like casting a spell: `aghanim.actions.getAllUsers()`. No custom APIs, just pure magic.

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
     const { call, result } = useAghanim();
     const fetchUsers = () => call(aghanim.actions.getAllUsers);
     return <button onClick={fetchUsers}>Invoke Users</button>;
   }
   ```

## Why Aghanim?

- **Seamless**: Proxy-based API eliminates API boilerplate.
- **Type-Safe**: Generated TypeScript definitions for actions.
- **Reactive**: Built for Inertia.js’s SPA-like experience.

Unleash your app’s ultimate potential with **Aghanim**—the scepter of Inertia.js!
