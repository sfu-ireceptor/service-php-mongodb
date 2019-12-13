# iReceptor Service (PHP/MongoDB)

## Installation using Docker (5 min)
Requirements:
- Linux Ubuntu (tested with Xenial 16.04)
- user with sudo rights and sudo without password

### Create a configuration file
Create a file `env` containing your MongoDB database connection info. Example:
```
DB_HOST=192.168.87.9
DB_PORT=
DB_DATABASE=ireceptor
DB_USERNAME=
DB_PASSWORD=
```

### Install Docker (1 min)
```
curl -s https://get.docker.com | sudo sh
```

### Download and launch the Docker image (2 min)
```
sudo docker run -d --rm -p 80:80 --env-file env ireceptorj/service-php-mongodb
```

### Check it's working
```
curl localhost/v2/samples
```


## Running the tests suite
Requirements:
- a running MongoDB database with its credentials in .env
- php CLI
- having installed the dependencies (composer install)

### Running all tests
```
./vendor/bin/phpunit
PHPUnit 7.5.17 by Sebastian Bergmann and contributors.

................................................................. 65 / 67 ( 97%)
..                                                                67 / 67 (100%)

Time: 19.72 seconds, Memory: 22.00 MB

OK (67 tests, 167 assertions)
```

### Running specific tests
--filter matches function names and/or class names.

```
./vendor/bin/phpunit --filter=repertoire_download
PHPUnit 7.5.17 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 2.95 seconds, Memory: 18.00 MB

OK (1 test, 13 assertions)

```

### Git hook
To automatically run the tests when pushing modifications, install the Git hook:
```
cp util/git-hooks/pre-push .git/hooks
```

To force the push in case some tests fail:
```
git push --no-verify
```
