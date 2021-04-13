OC.L10N.register(
    "ransomware_protection",
    {
    "Ransomware protection" : "Защита от вирусов-вымогателей",
    "File “%1$s” could not be uploaded!" : "Файл «%1$s» не может быть отправлен!",
    "Your sync clients are currently blocked from further uploads" : "Прием файлов, отправляемых вашими программами синхронизации, приостановлен",
    "The file “%1$s” you tried to upload matches the naming pattern of a ransomware/virus “%2$s”. If you are sure that your device is not affected, you can temporarily disable the protection. Otherwise you can request help from your admin, so they reach out to you." : "Была выполнена попытка передать на сервер файл с именем «%1$s», которое используется  вирусом-вымогателем «%2$s». В случае уверенности, что устройство не заражено, вы можете временно отключить защиту. В противном случае, вы можете запросить помощь у администратора.",
    "Pause protection" : "Приостановить защиту",
    "I need help!" : "Требуется помощь!",
    "User %s may be infected with ransomware and is asking for your help." : "Устройство пользователя %s, возможно, было заражено вирусом-шифровальщиком. Этот пользователь просит оказать ему помощь.",
    "I will help" : "Я помогу",
    "This app prevents uploading files with known ransomware file endings" : "Это приложение предотвращает загрузку файлов с известными расширениями, присущими программам-вымогателям(шантажистам).",
    "Include note files with non-obvious names, e.g. ReadMe.TxT, info.html" : "Включить информационные файлы с необычными именами, например: «ReadMe.TxT», «info.html»",
    "Additional extension patterns" : "Дополнительные шаблоны расширений файлов",
    "One pattern per line. If the pattern is a regular expression it has to start with ^ or end with $. Leading dot or underscore on non-regular expression patterns mean that the name has to end with the given string." : "Задайте шаблоны, каждый с новой строки. Если шаблон — регулярное выражение, то он должен начинаться со знака ^ или заканчиваться знаком $. Точка в начале шаблона или символ подчёркивания в нерегулярном выражении означает, что имя файла должно оканчиваться заданной строкой.",
    "Additional note file patterns" : "Дополнительные шаблоны имени информационного файла",
    "One pattern per line. If the pattern is a regular expression it has to start with ^ or end with $ otherwise the name must be a complete match." : "Задайте шаблоны, каждый с новой строки. Если шаблон — регулярное выражение, то он должен начинаться со знака ^ или заканчиваться знаком $, в противном случае имя файла должно полностью совпасть с шаблоном.",
    "Exclude extension patterns" : "Шаблоны для исключения расширений файлов",
    "One pattern per line. Copy the exact string from the resource file. This helps keeping your exclusions while updating the app." : "Задайте шаблоны, каждый с новой строки. Скопируйте строку из ресурсного файла, это позволит сохранить заданные исключения при обновлении приложения.",
    "Ignore extension patterns" : "Шаблоны игнорируемых расширений файла",
    "Exclude note file patterns" : "Шаблоны исключаемых имён информационных файлов",
    "Ignore note file patterns" : "Шаблоны игнорируемых имён информационных файлов",
    "Protection is currently active" : "Защита активна",
    "Protection is currently paused until: <strong>%s</strong>" : "Защита временно отключена до <strong>%s</strong>",
    "Re-enable protection now" : "Повторно активировать защиту",
    "This app prevents the Nextcloud Sync clients from uploading files with known ransomware file endings.\n\n⚠️ This app does not replace regular backups. Especially since it only prevents infected clients from uploading and overwriting files on your Nextcloud server. It does not help in case your server is infected directly by a ransomware.\n\n⚠️ Neither the developer nor Nextcloud GmbH give any guarantee that your files can not be affected by another way." : "Это приложение служит для блокирования попыток со стороны клиентов передать на сервер файлы с известными расширениями вирусов-вымогателей.\n\n⚠️ Это приложение не заменяет регулярное создание резервных копий. Приложение препятствует замене файлов на сервере Nextcloud заражёнными компьютерами, но бесполезно, если заражён сам сервер.\n\n⚠️ Разработчики приложения и Nextcloud GmbH не предоставляют никаких гарантий относительно возможности повреждения файлов другими способами."
},
"nplurals=4; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<12 || n%100>14) ? 1 : n%10==0 || (n%10>=5 && n%10<=9) || (n%100>=11 && n%100<=14)? 2 : 3);");
