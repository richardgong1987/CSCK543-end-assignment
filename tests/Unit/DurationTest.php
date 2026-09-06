<?php

use App\Support\Duration;

it('formats minutes under an hour', function () {
    expect(Duration::format(1))->toBe('1 min');
    expect(Duration::format(45))->toBe('45 mins');
});

it('formats whole hours without a trailing zero', function () {
    expect(Duration::format(60))->toBe('1 hr');
    expect(Duration::format(480))->toBe('8 hrs');
});

it('formats hours and minutes together', function () {
    expect(Duration::format(80))->toBe('1 hr 20 mins');
    expect(Duration::format(150))->toBe('2 hrs 30 mins');
});
