# Ransomware protection app

This app prevents the Nextcloud Sync clients from uploading files with known ransomware file endings.

⚠️ This app does not replace regular backups. Especially since it only prevents infected clients from uploading and overwriting files on your Nextcloud server. It does not help in case your server is infected directly by a ransomware.

⚠️ Neither the developer nor Nextcloud GmbH give any guarantee that your files can not be affected by another way.

## How the app works

When a known sync client is uploading a file with a file name matching the pattern of a ransomware (see [this list of patterns](resources/extensions.txt)), uploading of the file is blocked.

The user receives a notification with 2 options:

> #### File “foobar.txt” could not be uploaded!
>
> The file “foobar.txt” you tried to upload matches the naming pattern of a ransomware/virus “*.txt”.
> If you are sure that your device is not affected, you can temporarily disable the protection.
> Otherwise you can request help from your admin, so they reach out to you.
>
> [ Pause protection ]  [ I need help! ]

If you want to exclude the problematic pattern, you can copy it from this notification and ask your admin to add it to the exclude list. Admins can also see the pattern in the log when it is set to level Warning or lower.

If the user presses "I need help!" admins of the instance receive the following notification:

>  #### User Tester may be infected with ransomware and is asking for your help
>  [ I will help ]

Pressing the button will delete the notification for all administrators.


After 5 "infected" uploads within 30 minutes, the clients of the user get blocked automatically to prevent further damage to the data. After the problem has been solved, the clients can be re-allowed in the personal settings of the user.
