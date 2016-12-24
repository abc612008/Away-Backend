# The Api of Away Backend

## Use

| Name          | File Path      | Method | Parameters              | Return                   | Description         |
| ------------- | -------------- | ------ | ----------------------- | ------------------------ | ------------------- |
| Register      | ./api/user.php | POST   | type, username,password | success                  | type="register"     |
| Login         | ./api/user.php | POST   | type, username,password | success, token, id       | type="login"        |
| Add Friend    | ./api/user.php | POST   | type, id, token         | success                  |                     |
| Get User      | ./api/user.php | POST   | type, id, token         | success, username, score | type="get_user"     |
| Delete Friend | ./api/user.php | POST   | type, id, token         | success                  |                     |
| Change Score  | ./api/user.php | POST   | type, score, token      | success                  |                     |
| Get ID        | ./api/user.php | POST   | type, username, token   | success, id              | type="get_id"       |
| Delete Token  | ./api/user.php | POST   | type, token             | success                  | type="delete_token" |
|               |                |        |                         |                          |                     |

## Configuration

### Database

```sql
CREATE TABLE `away`.`users` ( `id` INT NOT NULL AUTO_INCREMENT , `username` TINYTEXT NOT NULL , `password` TINYTEXT NOT NULL , `salt` TINYTEXT NOT NULL , `registration_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `score` INT NOT NULL DEFAULT '0' , `friends` LONGTEXT NOT NULL , PRIMARY KEY (`id`));
CREATE TABLE `away`.`tokens` ( `id` INT NOT NULL AUTO_INCREMENT , `username` TINYTEXT NOT NULL , `token` TINYTEXT NOT NULL , `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , `valid` BOOLEAN NOT NULL DEFAULT TRUE , PRIMARY KEY (`id`));
```

### Configuration

Please change the configuration in the file `./api/internal/config.php`.