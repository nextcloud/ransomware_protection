# Changelog
All notable changes to this project will be documented in this file.

## 1.0.6 - 2018-06-22
### Added
  - Add extension `.wkgdiba` against Cryptolocker (Patch by [dienteperro](https://github.com/dienteperro))

## 1.0.5 - 2017-10-26
### Added
  - Add extension `install_flash_player.exe` against Bad Rabbit (Patch by [mr-bolle](https://github.com/mr-bolle))

## 1.0.4 – 2017-09-20
### Added
 - Add extension `.ykcol` against Locky [#15](https://github.com/nextcloud/ransomware_protection/issues/15)
 
## 1.0.3 – 2017-09-01
### Fixed
 - Correctly remove old strikes and block the client after 5 new strikes [#12](https://github.com/nextcloud/ransomware_protection/issues/12) (Patch by [Jakub Augustynowicz](https://github.com/pingwiniasty))

## 1.0.2 – 2017-08-29
### Added
 - Add extension `.diabolo6` and `.lukitus` against Diablo6 [#7](https://github.com/nextcloud/ransomware_protection/issues/7)
 - Add extension `.kk` against Syncrypt [#8](https://github.com/nextcloud/ransomware_protection/issues/8)

## 1.0.1 – 2017-08-14
### Added
 - Console command to block a client allowing for external tools like [cryptostalker](https://github.com/unixist/cryptostalker) to block clients as well [#2](https://github.com/nextcloud/ransomware_protection/issues/2)

### Changed
 - Removed some extensions from the list: `.bin`, `.css`, `.dll`, `.exe` and `.mp3` [#4](https://github.com/nextcloud/ransomware_protection/issues/4)
 
### Fixed
 - Blocking now only affects sync clients, because ransomware doesn't upload via the browser
  [#5](https://github.com/nextcloud/ransomware_protection/issues/5)

## 1.0.0 – 2017-08-08
### Added
 - Initial version


