# iReceptor Service (PHP/MongoDB)

Important note: this is only the iReceptor **web service**. It requires an existing MongoDB database. For a complete solution including a MongoDB database and scripts to import data, use the [iReceptor Turnkey](https://github.com/sfu-ireceptor/turnkey-service-php/tree/production).

## Installation (5 min)
Requires Linux Ubuntu (tested with Ubuntu Xenial 16.04).

### Create a configuration file

Create a file `env` with your MongoDB database connection info. Example:
```
DB_HOST=localhost
DB_PORT=
DB_DATABASE=ireceptor
DB_USERNAME=
DB_PASSWORD=
```

### Install Docker
```
curl -s https://get.docker.com | sudo sh
```

### Download the Docker image and start a Docker container
```
sudo docker run -d --rm -p 80:80 --env-file env ireceptor/service-php-mongodb
```

### Check it's working
```
curl localhost
curl localhost/airr/v1/info
```
