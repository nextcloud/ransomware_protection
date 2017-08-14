# Changelog
All notable changes to this project will be documented in this file.

## 1.0.1 – 2017-08-14
### Added
 - Console command to block a client allowing for external tools like [cryptostalker](https://github.com/unixist/cryptostalker) to block clients as well [#2](https://github.com/nextcloud/ransomware_protection/issues/2)

### Changed
 - Removed some extensions from the list: .bin, .css, .dll, .exe and .mp3 [#4](https://github.com/nextcloud/ransomware_protection/issues/4)
 
### Fixed
 - Blocking now only affects sync clients, because ransomware doesn't upload via the browser
  [#5](https://github.com/nextcloud/ransomware_protection/issues/5)

## 1.0.0 – 2017-08-08
### Added
 - Initial version


