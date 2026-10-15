<?php

namespace TwoFA\LaravelMfa\Events;

class MfaChallengeSucceeded { public $challenge; public function __construct($challenge) { $this->challenge = $challenge; } }
