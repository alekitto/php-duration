# PHP-Duration
## Convert durations between colon formatted time, human-readable time and seconds

The library can accept either in colon separated format, like 2:43 for 2 minutes and 43 seconds
OR written as human-readable or abbreviated time, such as 6m21s for 6 minutes and 21 seconds.

Both can be converted into seconds and minutes with precision for easy storage into a database.

Seconds, colon separated, abbreviated, all three can be parsed and interchanged.
 - supports hours, minutes, and seconds (with microseconds)
 - humanized input supports any form of the words "hour", "minute", "seconds"
   - Example, you could input 1h4m2s or 4 Hr. 32 Min.

This library is a revamped/revised/updated version of the khill/php-duration library.

# Install
```bash
composer require kcs/php-duration
```

# Usage
```php
use Kcs\Duration\Duration;

$duration = new Duration('7:31');

echo $duration->humanize();  // 7m 31s
echo $duration->formatted(); // 7:31
echo $duration->toSeconds(); // 451
echo $duration->toMinutes(); // 7.5166
echo $duration->toMinutes(0); // 8
echo $duration->toMinutes(2); // 7.52
```

```php
$duration = new Duration('1h 2m 5s');

echo $duration->humanize();  // 1h 2m 5s
echo $duration->formatted(); // 1:02:05
echo $duration->toSeconds(); // 3725
echo $duration->toMinutes(); // 62.0833
echo $duration->toMinutes(0); // 62
```

```php
// Configured for 6 hours per day
$duration = new Duration('1.5d 1.5h 2m 5s', 6);

echo $duration->humanize();  // 1d 4h 32m 5s
echo $duration->formatted(); // 10:32:05
echo $duration->toSeconds(); // 37925
echo $duration->toMinutes(); // 632.083333333
echo $duration->toMinutes(0); // 632
```

```php
$duration = new Duration('4293');

echo $duration->humanize();  // 1h 11m 33s
echo $duration->formatted(); // 1:11:33
echo $duration->toSeconds(); // 4293
echo $duration->toMinutes(); // 71.55
echo $duration->toMinutes(0); // 72
```
