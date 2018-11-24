# iReceptor Service (PHP/MongoDB)

# Requirements
- Linux Ubuntu (tested with Ubuntu Xenial 16.04)
- a user with sudo rights without password

# Installation

## Create a configuration file
Create a file `env` containing your MongoDB database connection info. Example:
```
DB_HOST=192.168.87.9
DB_PORT=
DB_DATABASE=ireceptor
DB_USERNAME=
DB_PASSWORD=
```

## Install Docker
```
curl -s https://get.docker.com | sudo sh
```

## Download and launch the Docker image 
```
sudo docker run --rm -p 80:80 --env-file env ireceptorj/service-php-mongodb
```

## Check it's working
```
curl localhost/v2/samples
```

# Options
# Launch as a daemon
Add the `-d` option to the `docker run` command:
```
sudo docker run -d --rm -p 80:80 --env-file env ireceptorj/service-php-mongodb
```


## Reference
- Website: <http://ireceptor.org>
- Contact: <support@ireceptor.org>
