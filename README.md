# iReceptor Service (PHP/MongoDB)

## Installation (5 min)
Requires:
- Linux Ubuntu (tested with Ubuntu Xenial 16.04)
- a user with sudo rights without password
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
