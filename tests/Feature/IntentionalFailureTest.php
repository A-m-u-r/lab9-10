<?php

namespace Tests\Feature;

use Tests\TestCase;

class IntentionalFailureTest extends TestCase
{
    public function test_demo_failure_for_pipeline_screenshot(): void
    {
        $this->assertTrue(false, 'Intentional failure for Lab 10 pipeline screenshot.');
    }
}
