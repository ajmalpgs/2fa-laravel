<?php

namespace TwoFA\LaravelMfa\Events;

class MfaEnabled { public $setting; public function __construct($setting) { $this->setting = $setting; } }
