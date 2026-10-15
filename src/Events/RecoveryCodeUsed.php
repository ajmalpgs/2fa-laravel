<?php

namespace TwoFA\LaravelMfa\Events;

class RecoveryCodeUsed { public $setting; public function __construct($setting) { $this->setting = $setting; } }
