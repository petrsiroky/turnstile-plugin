<?php namespace Stheme\Turnstile\Components;

use Cms\Classes\ComponentBase;
use Stheme\Turnstile\Models\Settings;

class Turnstile extends ComponentBase
{
    public function componentDetails(): array
    {
        return [
            'name'        => 'Cloudflare Turnstile',
            'description' => 'Renders the Cloudflare Turnstile widget in a form.',
        ];
    }

    public function defineProperties(): array
    {
        return [
            'theme' => [
                'title'       => 'Theme',
                'description' => 'Overrides global theme setting for this component.',
                'type'        => 'dropdown',
                'default'     => 'global',
                'options'     => [
                    'global' => 'Use global setting',
                    'auto'   => 'Auto',
                    'light'  => 'Light',
                    'dark'   => 'Dark',
                ],
            ],
            'size' => [
                'title'       => 'Size',
                'description' => 'Overrides global size setting for this component.',
                'type'        => 'dropdown',
                'default'     => 'global',
                'options'     => [
                    'global'   => 'Use global setting',
                    'normal'   => 'Normal',
                    'compact'  => 'Compact',
                    'flexible' => 'Flexible',
                ],
            ],
            'action' => [
                'title'       => 'Action',
                'description' => 'Optional action name for Cloudflare Turnstile analytics.',
                'type'        => 'string',
                'default'     => '',
            ],
            'callback' => [
                'title'       => 'Success Callback',
                'description' => 'Optional JS function name to execute upon successful verification.',
                'type'        => 'string',
                'default'     => '',
            ],
            'expiredCallback' => [
                'title'       => 'Expired Callback',
                'description' => 'Optional JS function name to execute when token expires.',
                'type'        => 'string',
                'default'     => '',
            ],
            'errorCallback' => [
                'title'       => 'Error Callback',
                'description' => 'Optional JS function name to execute on challenge error.',
                'type'        => 'string',
                'default'     => '',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['turnstileEnabled'] = (bool) Settings::get('enabled', true);
        $this->page['turnstileSiteKey'] = (string) Settings::get('site_key');

        $themeProp = $this->property('theme', 'global');
        $this->page['turnstileTheme'] = ($themeProp !== 'global')
            ? $themeProp
            : Settings::get('theme', 'auto');

        $sizeProp = $this->property('size', 'global');
        $this->page['turnstileSize'] = ($sizeProp !== 'global')
            ? $sizeProp
            : Settings::get('size', 'normal');

        $this->page['turnstileAction'] = (string) $this->property('action', '');
        $this->page['turnstileCallback'] = (string) $this->property('callback', '');
        $this->page['turnstileExpiredCallback'] = (string) $this->property('expiredCallback', '');
        $this->page['turnstileErrorCallback'] = (string) $this->property('errorCallback', '');

        if ($this->page['turnstileEnabled'] && !empty($this->page['turnstileSiteKey'])) {
            $this->addJs('assets/js/turnstile-reset.js');
        }
    }
}
