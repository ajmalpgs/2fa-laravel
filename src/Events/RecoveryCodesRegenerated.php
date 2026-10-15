<?php

namespace TwoFA\LaravelMfa\Events;

class RecoveryCodesRegenerated { public $setting; public function __construct($setting) { $this->setting = $setting; } }
