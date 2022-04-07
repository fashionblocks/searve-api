<?php

use League\FactoryMuffin\Faker\Facade as Faker;

Faker::addProvider(new class(Faker::getGenerator()) extends \Faker\Provider\Base {
    public function dateTimeImmutable($max = 'now', $timezone = null)
    {
        return DateTimeImmutable::createFromMutable($this->generator->dateTime($max, $timezone));
    }

    public function localname($extension = null)
    {
        return implode('.', array_filter([
            $this->generator->sha1(),
            is_array($extension)
            ? $this->generator->randomElement($extension)
            : $extension ?? $this->generator->fileExtension(),
        ]));
    }
});
