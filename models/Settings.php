<?php namespace Stheme\Turnstile\Models;

use October\Rain\Database\Model;
use System\Behaviors\SettingsModel;

class Settings extends Model
{
    public $implement = [SettingsModel::class];

    public $settingsCode = 'stheme_turnstile_settings';

    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->enabled = true;
        $this->theme = 'auto';
        $this->size = 'normal';
        $this->custom_error_message = 'Cloudflare Turnstile verification failed. Please try again.';
        $this->bypass_admins = false;
        $this->whitelisted_ips = '';
    }

    public function getThemeOptions()
    {
        return [
            'auto'  => 'Auto (OS/browser setting)',
            'light' => 'Light',
            'dark'  => 'Dark',
        ];
    }

    public function getSizeOptions()
    {
        return [
            'normal'   => 'Normal',
            'compact'  => 'Compact',
            'flexible' => 'Flexible',
        ];
    }
}
