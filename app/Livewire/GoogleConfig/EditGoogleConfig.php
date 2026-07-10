<?php

namespace App\Livewire\GoogleConfig;

use App\Repositories\GoogleConfigs\GoogleConfigRepositoryInterface;
use Livewire\Component;

class EditGoogleConfig extends Component
{
    protected $googleConfigRepos;

    public $google_config;
    public $params = [];

    protected $rules = [
        'params.client_id' => 'required',
        'params.client_secret' => 'required',
        'params.redirect_uri' => 'required',
    ];

    public function boot(GoogleConfigRepositoryInterface $googleConfigRepos) {
        $this->googleConfigRepos = $googleConfigRepos;
    }

    public function mount() {
        $this->google_config = $this->googleConfigRepos->first();
        $this->params = $this->google_config ? $this->google_config->toArray() : [];
    }

    public function save() {
        $this->validate($this->rules);
        $this->google_config->update($this->params);
        $this->dispatch('notify', message: 'Đã lưu cấu hình');
    }

    public function render()
    {
        return view('livewire.google-config.edit-google-config');
    }
}
