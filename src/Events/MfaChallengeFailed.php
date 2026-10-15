<?php

namespace TwoFA\LaravelMfa\Events;

class MfaChallengeFailed { public $challenge; public function __construct($challenge) { $this->challenge = $challenge; } }
