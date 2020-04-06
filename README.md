# task1
1. Должен быть включен mod_rewrite
2. в основном конфиге AllowOverride All
3. Должна быть создана отдельная база, и под нее отдельный юзер, у которого полный доступ к этой базе, и только к ней, а к остальному доступа вообще нет
4. настройки занести в /private/config.ini (лежащий вне docroot)

пример файла config.ini:
```
[database]
servername = localhost
username = task1_user
password = ELrXkL5cHUyjLP5U
dbname = task1
```
