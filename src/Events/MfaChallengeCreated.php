<?php

namespace TwoFA\LaravelMfa\Events;

class MfaChallengeCreated { public $challenge; public function __construct($challenge) { $this->challenge = $challenge; } }
