<?php

namespace TwoFA\LaravelMfa\Events;

class MfaDisabled { public $setting; public function __construct($setting) { $this->setting = $setting; } }
