# OCHA GTM Barebones

Originally forked from [GTM Barebones][gtm-barebones] with modifications to defer the scripts. This was done after [discussion with the project maintainer][gtm-defer-issue] since the option couldn't really be added without complicating the module beyond the maintainer's desire.

  [gtm-barebones]: https://www.drupal.org/project/gtm_barebones
  [gtm-defer-issue]: https://www.drupal.org/project/gtm_barebones/issues/3415279#comment-15404703

## Usage

This module does not expose its configuration to the admin UI. You can choose to export and modify config, or utilise config overrides in `settings.php`:

```php
$config['gtm_barebones.settings']['container_id'] = 'GTM-ABCDEFGHIJK';
$config['gtm_barebones.settings']['environment_id'] = 'env-123456';
$config['gtm_barebones.settings']['environment_token'] = 'iBN8NANliiuqnAAi81LapqkkdUIjak';
```

## License

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
