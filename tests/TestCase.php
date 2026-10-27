<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Testler derlenmiş Vuexy asset'lerine (public/build) ihtiyaç duymasın
        $this->withoutVite();
    }
}
