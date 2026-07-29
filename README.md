# Cloudflare Turnstile Plugin for October CMS (`Stheme.Turnstile`)

This plugin protects website forms from spam bots using **Cloudflare Turnstile**.
Author: **Petr Široký**

## Features
1. **Backend Administration Interface**:
   - **General Tab**:
     - Enable / disable Turnstile globally.
     - Configure `Site Key` and `Secret Key`.
     - Choose widget theme (Auto, Light, Dark) and size (Normal, Compact, Flexible).
   - **Behavior & Security Tab**:
     - **Custom Error Message**: Define custom validation error text shown to users.
     - **Admin Bypass**: Automatically skip verification for logged-in backend administrators.
     - **IP Whitelist**: Bypass verification for specified IP addresses.

2. **Automatic Integration with Renatio FormBuilder**:
   - Widget is automatically injected into forms generated via FormBuilder.
   - Automatically validated against Cloudflare API before form submission.
   - Automatically resets Turnstile widget on AJAX validation errors.

3. **AJAX Auto-Reset Helper**:
   - Listens to October CMS AJAX framework events (`ajaxError`, `ajaxInvalid`) and automatically resets Turnstile tokens so users can re-submit without page reloads.

4. **Custom Component (`[turnstile]`)**:
   - Add the `[turnstile]` component to a page or layout.
   - Supports custom JavaScript callbacks (`callback`, `expiredCallback`, `errorCallback`).
   - Insert the tag inside your HTML/Twig form:
     ```twig
     {% component 'turnstile' %}
     ```

5. **Validation Rule for PHP / AJAX Handlers**:
   - Use the `turnstile` validation rule in custom forms:
     ```php
     $rules = [
         'cf-turnstile-response' => 'required|turnstile',
     ];
     ```

6. **Detailed Error Logging**:
   - Logs specific Cloudflare error codes (`invalid-input-secret`, `timeout-or-duplicate`, etc.) to October CMS system logs for quick troubleshooting.

## Configuration
In October CMS Backend navigate to:
**Settings -> CMS -> Cloudflare Turnstile**

Fill in your API keys from your Cloudflare Dashboard (Dashboard -> Turnstile -> Add Site).
