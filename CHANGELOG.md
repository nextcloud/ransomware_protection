# Changelog
All notable changes to this project will be documented in this file.

## 1.15.0 - 2023-MM-DD
### Changed
- Require Nextcloud 24

## 1.14.0 - 2022-10-25
### Added
- Nextcloud 25 support

### Changed
- Require Nextcloud 23

## 1.13.0 - 2022-04-11
### Added
- Nextcloud 24 support

## 1.12.0 - 2021-12-02
### Added
- Nextcloud 23 support

## 1.11.0 - 2021-07-09
### Added
- Nextcloud 22 support

## 1.10.1 - 2021-05-17
### Added
- Add [AvePoint](https://avepointcdn.azureedge.net/assets/webhelp/compliance_guardian_installation_and_administration/index.htm#!Documents/ransomwareencryptedfileextensionlist.htm) as second source list for ransomware extensions

## 1.7.1 - 2021-05-17
### Added
- Add [AvePoint](https://avepointcdn.azureedge.net/assets/webhelp/compliance_guardian_installation_and_administration/index.htm#!Documents/ransomwareencryptedfileextensionlist.htm) as second source list for ransomware extensions

## 1.10.0 - 2021-03-19
### Changed
- The app version now supports Nextcloud 21 and Nextcloud 20
  
### Fixed
- Fix "Failed opening 'ransomware_protection/personal.php' for inclusion"
  [#88](https://github.com/nextcloud/ransomware_protection/issues/88)

## 1.9.0 - 2021-03-01
### Added
- Nextcloud 21 support

## 1.8.0 - 2020-09-04
### Added
- Nextcloud 20 support

## 1.7.0 - 2020-06-03
### Added
- Nextcloud 19 support

## 1.6.1 - 2020-03-24
### Added
- Fix an issue with exceptions being thrown in nested calls 
  [#64](https://github.com/nextcloud/ransomware_protection/issues/64)

## 1.5.2 - 2020-03-24
### Added
- Fix an issue with exceptions being thrown in nested calls 
  [#65](https://github.com/nextcloud/ransomware_protection/issues/65)

## 1.4.2 - 2020-03-24
### Added
- Fix an issue with exceptions being thrown in nested calls 
  [#66](https://github.com/nextcloud/ransomware_protection/issues/66)

## 1.6.0 - 2020-01-17
### Added
- Nextcloud 18 support

## 1.6.0 - 2020-01-17
### Added
- Nextcloud 18 support

## 1.5.1 - 2019-11-15
### Added
- Add extension `.NEXTCRY against Nextcry

## 1.4.1 - 2019-11-15
### Added
- Add extension `.NEXTCRY against Nextcry

## 1.3.1 - 2019-11-15
### Added
- Add extension `.NEXTCRY` against Nextcry

## 1.5.0 - 2019-08-26
### Added
- Nextcloud 17 support

## 1.4.0 - 2019-04-01
### Added
- Nextcloud 16 support

## 1.3.0 - 2018-12-04
### Added
- Nextcloud 15 support

## 1.2.0 - 2018-09-10
### Added
- Nextcloud 14 support

## 1.1.2 - 2018-09-10
### Added
- Add extension `.CRAB` against GandCrab

## 1.1.1 - 2018-07-06
### Added
- Allow folders to have blocked names, since they can not be dangerous
  [#30](https://github.com/nextcloud/ransomware_protection/issues/30)
- Add extension `.wkgdiba` against Cryptolocker
  (Patch by [dienteperro](https://github.com/dienteperro))

## 1.0.5 - 2017-10-26
### Added
- Add extension `install_flash_player.exe` against Bad Rabbit
  (Patch by [mr-bolle](https://github.com/mr-bolle))

## 1.0.4 – 2017-09-20
### Added
- Add extension `.ykcol` against Locky 
  [#15](https://github.com/nextcloud/ransomware_protection/issues/15)

## 1.0.3 – 2017-09-01
### Fixed
- Correctly remove old strikes and block the client after 5 new strikes
  [#12](https://github.com/nextcloud/ransomware_protection/issues/12) (Patch by [Jakub Augustynowicz](https://github.com/pingwiniasty))

## 1.0.2 – 2017-08-29
### Added
- Add extension `.diabolo6` and `.lukitus` against Diablo6
  [#7](https://github.com/nextcloud/ransomware_protection/issues/7)
- Add extension `.kk` against Syncrypt
  [#8](https://github.com/nextcloud/ransomware_protection/issues/8)

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


