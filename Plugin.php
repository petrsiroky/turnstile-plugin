<?php namespace Stheme\Turnstile;

use Event;
use Illuminate\Support\Facades\Validator;
use October\Rain\Exception\ValidationException;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use Stheme\Turnstile\Classes\TurnstileVerifier;
use Stheme\Turnstile\Components\Turnstile;
use Stheme\Turnstile\Models\Settings;

class Plugin extends PluginBase
{
    public function pluginDetails(): array
    {
        return [
            'name'        => 'Cloudflare Turnstile',
            'description' => 'Protect forms from bots using Cloudflare Turnstile.',
            'author'      => 'Petr Široký',
            'icon'        => 'icon-shield',
        ];
    }

    public function registerSettings(): array
    {
        return [
            'settings' => [
                'label'       => 'Cloudflare Turnstile',
                'description' => 'Configure keys and appearance for Cloudflare Turnstile anti-bot protection.',
                'category'    => SettingsManager::CATEGORY_CMS,
                'icon'        => 'icon-shield',
                'class'       => Settings::class,
                'order'       => 500,
                'permissions' => ['stheme.turnstile.manage_settings'],
            ],
        ];
    }

    public function registerPermissions(): array
    {
        return [
            'stheme.turnstile.manage_settings' => [
                'tab'   => 'Cloudflare Turnstile',
                'label' => 'Manage Turnstile settings',
            ],
        ];
    }

    public function registerComponents(): array
    {
        return [
            Turnstile::class => 'turnstile',
        ];
    }

    public function boot(): void
    {
        // Register 'turnstile' validation method for Laravel/October Validator
        Validator::extend('turnstile', function ($attribute, $value, $parameters, $validator) {
            return TurnstileVerifier::verify($value);
        }, Settings::get('custom_error_message') ?: 'Cloudflare Turnstile verification failed. Please try again.');

        // Automatically inject Turnstile widget into Renatio FormBuilder forms
        Event::listen('formBuilder.overrideForm', function (&$form) {
            if (!Settings::get('enabled', true)) {
                return;
            }

            $siteKey = Settings::get('site_key');
            if (empty($siteKey)) {
                return;
            }

            $theme = Settings::get('theme', 'auto');
            $size = Settings::get('size', 'normal');

            $turnstileHtml = sprintf(
                '<div class="cf-turnstile my-3" data-sitekey="%s" data-theme="%s" data-size="%s"></div>' .
                '<script>if(!window.turnstileScriptLoaded){window.turnstileScriptLoaded=true;if(!document.querySelector(\'script[src*="challenges.cloudflare.com/turnstile"]\')){var s=document.createElement("script");s.src="https://challenges.cloudflare.com/turnstile/v0/api.js";s.async=true;s.defer=true;document.head.appendChild(s);}}</script>' .
                '<script>if(typeof jQuery!=="undefined"){jQuery(document).on("ajaxError ajaxInvalid",function(){if(window.turnstile&&window.turnstile.reset){var w=document.querySelectorAll(".cf-turnstile");w.forEach(function(i){window.turnstile.reset(i);});}});}</script>',
                e($siteKey),
                e($theme),
                e($size)
            );

            $currentMarkup = $form->markup ?: $form->getMarkup();

            if (strpos($currentMarkup, 'cf-turnstile') === false) {
                $form->markup = $currentMarkup . "\n" . $turnstileHtml;
            }
        });

        // Intercept email sending inside SendEmailMessage::handle() BEFORE emails/logs are sent
        Event::listen('formBuilder.beforeSendMessage', function ($form, $data) {
            if (!Settings::get('enabled', true)) {
                return true;
            }

            $token = post('cf-turnstile-response') ?: request()->input('cf-turnstile-response');

            if (!TurnstileVerifier::verify($token)) {
                $errorMessage = Settings::get('custom_error_message')
                    ?: 'Cloudflare Turnstile anti-bot verification failed. Please complete the Turnstile widget and try again.';

                throw new ValidationException([
                    'cf-turnstile-response' => $errorMessage,
                ]);
            }

            return true;
        });

        // Additional listener on formSubmitted
        Event::listen('formBuilder.formSubmitted', function (&$form) {
            if (!Settings::get('enabled', true)) {
                return;
            }

            $token = post('cf-turnstile-response') ?: request()->input('cf-turnstile-response');

            if (!TurnstileVerifier::verify($token)) {
                $errorMessage = Settings::get('custom_error_message')
                    ?: 'Cloudflare Turnstile anti-bot verification failed. Please complete the Turnstile widget and try again.';

                throw new ValidationException([
                    'cf-turnstile-response' => $errorMessage,
                ]);
            }
        });
    }
}
