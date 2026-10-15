# Laravel MFA Package Installation Guide

Reusable multi-factor authentication package for Laravel 8 web/session authentication.

This package adds TOTP authenticator-app MFA, QR setup, one-time recovery codes, challenge enforcement, optional or mandatory policy, and database-backed translation support for apps that use the same translator as the login page.

## 1. Requirements

- PHP 8.0–8.4 (`>=8.0 <8.5`)
- Laravel 8
- Session based `web` authentication
- Existing login flow using Laravel `Auth`
- Composer

The package installs these MFA dependencies:

- `pragmarx/google2fa`
- `bacon/bacon-qr-code`

## 2. Add The Package Repository

For local development inside an existing Laravel app, add a Composer path repository to the main application's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "packages/laravel-mfa",
        "options": {
            "symlink": true
        }
    }
]
```

For a private Git package, use a VCS repository instead:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "git@your-git-server:2fa/laravel-mfa.git"
    }
]
```

## 3. Install The Package

Run this from the Laravel application root:

```bash
composer require 2fa/laravel-mfa:^1.0
```

For local package development after editing the package, refresh the package in the host app:

```bash
composer update 2fa/laravel-mfa --with-all-dependencies
```

## 4. Publish The Config

```bash
php artisan vendor:publish --tag=mfa-config
```

This creates:

```text
config/mfa.php
```

## 5. Configure Environment Values

Add these values to the host application's `.env`:

```dotenv
MFA_ENABLED=true
MFA_POLICY=mandatory
MFA_ISSUER="2FA"
MFA_ROUTE_PREFIX=mfa
MFA_ROUTE_NAME_PREFIX=mfa.
```

Policy options:

- `mandatory`: every authenticated user must set up and pass MFA.
- `optional`: users can enable MFA themselves; only enrolled users are challenged.

After changing `.env`, clear cached config:

```bash
php artisan config:clear
```

## 6. Review `config/mfa.php`

Important settings:

```php
'enabled' => env('MFA_ENABLED', true),
'policy' => env('MFA_POLICY', 'optional'),
'issuer' => env('MFA_ISSUER', config('app.name', 'Laravel')),
'route_prefix' => env('MFA_ROUTE_PREFIX', 'mfa'),
'route_name_prefix' => env('MFA_ROUTE_NAME_PREFIX', 'mfa.'),
```

The package pages can use the host application's layout:

```php
'views' => [
    'layout' => 'admin.layouts.master',
    'content_section' => 'content',
],
```

The challenge page can use the login layout style so users see the same design as the login page.

## 7. Run Migrations

```bash
php artisan migrate
```

The package creates these tables:

- `mfa_settings`
- `mfa_recovery_codes`
- `mfa_challenges`

It also includes a translation seed migration for apps that have an `ltm_translations` table. Translation keys are inserted only when that table exists.

## 8. Clear Laravel Caches

Run these after installation or config/view changes:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

If Artisan fails because Laravel cannot write logs, fix permissions for:

```text
storage/logs/laravel.log
storage/framework
bootstrap/cache
```

## 9. Integrate MFA After Login

In the application's login controller, call the MFA manager immediately after password authentication succeeds and before the normal redirect.

Example:

```php
use TwoFA\LaravelMfa\Services\MfaManager;
use Illuminate\Support\Facades\Auth;

if (Auth::attempt($credentials, $remember)) {
    $redirect = app(MfaManager::class)
        ->handleAuthenticatedUser(Auth::user(), $request);

    if ($redirect) {
        return $redirect;
    }

    return redirect()->intended($this->redirectPath());
}
```

This is required because password login alone is not complete when MFA is required.

## 10. Protect Authenticated Routes

Add the `mfa.verified` middleware to routes that require full authentication:

```php
Route::middleware(['auth', 'mfa.verified'])->group(function () {
    // Protected application routes
});
```

If your app has separate admin and user route groups, add `mfa.verified` to both.

Do not add `mfa.verified` to login, logout, MFA setup, or MFA challenge routes.

## 11. Clear MFA Session On Logout

Call `clearSession()` when logging out:

```php
use TwoFA\LaravelMfa\Services\MfaManager;
use Illuminate\Support\Facades\Auth;

app(MfaManager::class)->clearSession($request);

Auth::logout();
```

This prevents an old MFA verification flag from remaining in the session.

## 12. Add Account Security Menu Links

Add a menu or dropdown link for both admin and user areas:

```php
route('mfa.setup')
```

Suggested label:

```php
TwoFA\LaravelMfa\Support\MfaTranslator::get('account_security')
```

In this app, the MFA link should appear in both admin and user dropdown menus.

## 13. User Setup Flow

When MFA is mandatory and a user has not enrolled yet:

1. User logs in with username and password.
2. Package redirects the user to `/mfa/setup`.
3. User scans the QR image with an authenticator app.
4. User enters the 6-digit code.
5. Package enables MFA and shows recovery codes.
6. User stores recovery codes safely.
7. User can access protected routes.

The setup page shows the QR image only. The raw `otpauth://` setup URI is intentionally not displayed.

## 14. Login Challenge Flow

When a user already has MFA enabled:

1. User logs in with username and password.
2. Package redirects the user to `/mfa/challenge`.
3. User enters an authenticator app code.
4. Package marks the session as MFA verified.
5. User is redirected to the intended protected page.

Users can also verify with a recovery code when needed. Recovery codes are single-use.

## 15. Routes

Default route prefix:

```text
/mfa
```

Default route names:

- `mfa.setup`
- `mfa.setup.verify`
- `mfa.challenge`
- `mfa.challenge.verify`
- `mfa.recovery`
- `mfa.recovery.regenerate`
- `mfa.disable`

The prefix and route name prefix can be changed in `config/mfa.php`.

## 16. Publish Views

The package views can be overridden in the host app:

```bash
php artisan vendor:publish --tag=mfa-views
```

Published views are copied to:

```text
resources/views/vendor/mfa
```

Use this only when the host app needs custom markup. If the package views already match the app layout, publishing is optional.

## 17. Publish Language Files

```bash
php artisan vendor:publish --tag=mfa-lang
```

Published language files are copied to:

```text
resources/lang/vendor/mfa
```

Package translation files are fallback translations. In this app, MFA pages use the same database translator as the login page first.

## 18. Database Translator Support

MFA UI text uses `MfaTranslator`, which checks the application's `lang()` database translator first, then falls back to package language files.

Database keys use the `mfa_` prefix, for example:

- `mfa_account_security`
- `mfa_security_title`
- `mfa_setup_title`
- `mfa_challenge_title`
- `mfa_verify`
- `mfa_recovery_codes`
- `mfa_disable`

To update translations:

1. Run package migrations.
2. Open the application's translation manager.
3. Search for keys beginning with `mfa_`.
4. Update English, Arabic, or other languages in the database.
5. Clear cache/views if the old text still appears.

## 19. Mandatory MFA Rules

For all users:

```dotenv
MFA_POLICY=mandatory
```

For user opt-in:

```dotenv
MFA_POLICY=optional
```

For custom rules, create a class that implements:

```php
\TwoFA\LaravelMfa\Contracts\MfaRequirementResolverInterface
```

Then configure it in `config/mfa.php`:

```php
'requirement_resolver' => App\Security\CustomMfaRequirementResolver::class,
```

Use this when only specific roles, guards, companies, or user groups must use MFA.

## 20. Security Behavior

- TOTP secrets are encrypted.
- Recovery codes are hashed.
- Recovery codes are shown only when generated.
- Recovery codes are single-use.
- MFA challenges expire.
- MFA challenges are session-bound.
- Successful MFA verification regenerates the session.
- MFA verification routes use rate limiting.
- Plaintext secrets and codes are not written to events or logs.
- QR generation is local through `bacon/bacon-qr-code`.

## 21. Current App Checklist

For this application, confirm these items:

- `.env` has `MFA_ENABLED=true`.
- `.env` has `MFA_POLICY=mandatory` if all users must enroll.
- `.env` has `MFA_ISSUER="2FA"`.
- Login controller calls `MfaManager::handleAuthenticatedUser()` after `Auth::attempt()`.
- Admin authenticated routes use `mfa.verified`.
- User authenticated routes use `mfa.verified`.
- Admin dropdown has a link to `route('mfa.setup')`.
- User dropdown has a link to `route('mfa.setup')`.
- `/mfa/challenge` uses the same style as the login page.
- MFA pages have the login page language switcher where needed.
- Translation text is editable through the database translator using `mfa_` keys.
- Setup page shows the QR image and does not show the raw setup URI.

## 22. Troubleshooting

### MFA does not start after login

Check that the login controller calls:

```php
app(\TwoFA\LaravelMfa\Services\MfaManager::class)
    ->handleAuthenticatedUser(Auth::user(), $request);
```

The call must happen before the normal post-login redirect.

### User can bypass MFA

Check that protected routes include:

```php
mfa.verified
```

Also clear route cache:

```bash
php artisan route:clear
```

### Config changes are ignored

Run:

```bash
php artisan config:clear
```

### Translation changes are ignored

Confirm the database contains `mfa_` keys, then run:

```bash
php artisan cache:clear
php artisan view:clear
```

### QR image is missing

Confirm the QR package is installed:

```bash
composer show bacon/bacon-qr-code
```

If missing, update dependencies:

```bash
composer update 2fa/laravel-mfa --with-all-dependencies
```

### Artisan commands fail with log permission errors

Fix write permissions for:

```text
storage/logs
storage/framework
bootstrap/cache
```

Then rerun the command.

## 23. Upgrade

Use semantic versioning in the host app:

```json
"2fa/laravel-mfa": "^1.0"
```

To upgrade:

```bash
composer update 2fa/laravel-mfa --with-all-dependencies
php artisan migrate
php artisan config:clear
php artisan view:clear
```

## 24. Uninstall

1. Remove `mfa.verified` from application routes.
2. Remove the `MfaManager` call from login/logout controllers.
3. Remove menu links to `route('mfa.setup')`.
4. Remove the Composer package:

```bash
composer remove 2fa/laravel-mfa
```

5. Optionally remove MFA database tables after backing up data.

## 25. Events

The package dispatches these events:

- `MfaEnabled`
- `MfaDisabled`
- `MfaChallengeCreated`
- `MfaChallengeSucceeded`
- `MfaChallengeFailed`
- `RecoveryCodeUsed`
- `RecoveryCodesRegenerated`
