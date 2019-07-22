<?php

namespace Humi\JsonAPiConnector\Tests\Traits;

trait JsonFixtures
{
    private $fixtureRoot;

    public function setFixtureRoot(string $path): void
    {
        $this->fixtureRoot = $path;
    }

    public function jsonFixture(string $name): string
    {
        $file = $this->fixtureRoot . '/' . $name . '.json';

        $contents = file_get_contents($file);

        return $contents;
    }

    public function jsonFixtureToArray(string $name): array
    {
        return json_decode($this->jsonFixture($name), true);
    }
}
