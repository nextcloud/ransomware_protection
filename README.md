# Ransomware protection app

This app prevents the Nextcloud Sync clients from uploading files with known ransomware file endings.

⚠️ This app does not replace regular backups. Especially since it only prevents infected clients from uploading and overwriting files on your Nextcloud server. It does not help in case your server is infected directly by a ransomware.

⚠️ Neither the developer nor Nextcloud GmbH give any guarantee that your files can not be affected by another way.

## How the app works

When a known sync client is uploading a file with a file name matching the pattern of a ransomware (see [this list of patterns](resources/extensions.txt)), uploading of the file is blocked.

The user receives a notification with 2 options:

> #### File “foobar.txt” could not be uploaded!
>
> The file “foobar.txt” you tried to upload matches the naming pattern of a ransomware/virus “\*.txt”.
> If you are sure that your device is not affected, you can temporarily disable the protection.
> Otherwise you can request help from your admin, so they reach out to you.
>
> [ Pause protection ]  [ I need help! ]

If you want to exclude the problematic pattern, you can copy it from this notification and ask your admin to add it to the exclude list. **Hint:** You can find the apps admin settings in the security tab of your Nextcloud instance. Admins can also see the pattern in the log when it is set to level Warning or lower.

If the user presses "I need help!" admins of the instance receive the following notification:

>  #### User Tester may be infected with ransomware and is asking for your help
>  [ I will help ]

Pressing the button will delete the notification for all administrators.


After 5 "infected" uploads within 30 minutes, the clients of the user get blocked automatically to prevent further damage to the data. After the problem has been solved, the clients can be re-allowed in the personal settings of the user.

## Configuration settings

Configuration is managed on the **Settings » Security** page under the heading **Ransomware protection**.

You can choose to ignore extensions from the [resources/extensions.txt file](https://github.com/nextcloud/ransomware_protection/blob/master/resources/extensions.txt "Link to the latest version of this file on Github. This may differ from your locally installed version.") by entering the pattern into the 'Exclude extension patterns' field. You must enter patterns *exactly* as found in the extensions.txt file; entering something that is not a line in that file has no effect.

**Example:** if you entered `.lock` in the Exclude extension patterns configuration field, then files that end in `.lock` will be allowed to sync without suspicion. i.e. you will no longer be protected from attacks that rename/create files called `<anything>.lock`.

You can also choose to use 'Additional extension patterns'.

**Example:** if you entered the pattern `.oh-no` then any files that end with `.oh-no` will be considered suspicious and blocked. [Regular expressions](https://en.wikipedia.org/wiki/Regular_expression "Wikipedia's definition of regular expressions") are supported here, identified by patterns that begin with `^` or end with `$` (or both), so you are not limited to files *ending with* a certain string. The pattern `^.*-evil-ha-ha-` would match any file names with `-evil-ha-ha-` anywhere in their name.

**When compiling the list of patterns, first the extensions.txt is loaded, then exclusions are removed, then additions added.** This is an important feature as it allows specific exceptions to be made for certain files.

**Example:** Say your files include some `composer.lock` and `package.lock` files (these are common files in programming projects) and you wish to allow these to sync, but you don't want to allow any other `.lock` files, as that is a commonly used extension by ransomware. You can achieve this by:

- Add `.lock` to the *Exclude extension patterns* configuration field. This will remove protection for .lock files, but then...
- Add the regular expression `(?<!composer|package)\.lock$` to the *Additional extension patterns* configuration field. This pattern matches all `.lock` *except* `composer.lock` and `package.lock`, so will allow those files to be uploaded but still block other files ending in `.lock`

### Note files

Typically ransomware will leave note files (e.g. "you've been hacked, here's how to pay me to get your files back..."). The ransomware protection app can look out for these note files based on their file names [see resources/notes.txt](https://github.com/nextcloud/ransomware_protection/blob/master/resources/notes.txt). The *Include note files with non-obvious names* option adds in an [additional file of patterns](https://github.com/nextcloud/ransomware_protection/blob/master/resources/notes-biased.txt). These additional files are rarely used in a general office files environment, but are fairly common in other sectors, so review these for your environment before checking this option.

