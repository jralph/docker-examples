# Docker PHP Example

This is an example of setting up a generic php application using nginx and php fpm.

## How It Works

The way docker works best is running a single service/process per container.
So here we split php-fpm and nginx into separate containers to run side by side.

The nginx container contains an env variable that can be set, to configure the location
of the php backend server. By default, this variable is set to use `localhost:9000`.
This will work on kubernetes and standalone docker out of the box. For docker-compose,
we can set this to the name of the backend service, `PHP_HOST=backend:9000`.

## Makefile

```bash

```

## Docker App

```bash
# Build required images.
docker app render | docker-compose -f - build

# Bring the application up and stream logs from the server and backend.
docker app render | docker-compose -f - up server backend

# Remove the application
docker app render | docker-compose -f - down

# Install al dependencies.
docker app render | docker-compose -f - run deps install

# Update dependencies.
docker app render | docker-compose -f - run deps update

# Add a package (phpunit in this example).
docker app render | docker-compose -f - run deps require phpunit/phpunit ^8
```

## Kubernetes

```bash
# First, build your images and push them to a repository. (See standalone docker below).
# Copy the example deployment patch.
cp docker/k8s/overlays/development/deployment-patch-example.yml docker/k8s/overlays/development/deployment-patch.yml 

# Edit the deployment patch, replacing the example image names with the names of your built images.
vim docker/k8s/overlays/development/deployment-patch.yml

# For Development
kubectl apply -k ./docker/k8s/overlays/development

# Delete the application.
kubectl delete -k ./docker/k8s/overlays/development

# For Production
kubectl apply -k ./docker/k8s/base

# Delete the application.
kubectl delete -k ./docker/k8s/base
```

## Standalone Docker

*Note: A Makefile is also included to run most things using standalone docker.*

```bash
# Build the required images
docker build -t docker-php-example-deps --target dev_deps .
docker build -t docker-php-example-server --target server .
docker build -t docker-php-example-backend --target backend .

# Run both images.
# Ensure to set the PHP_HOST variable on the server image to point to your exposed port on the backend image.
docker run -it -p 9000:9000 docker-php-example-backend
docker run -it -p 8080:8080 docker-php-example-server

# Run using local code.
# Install deps.
docker run -it -v $PWD:/var/app docker-php-example-deps install

# Run with volumes.
docker run -it -v $PWD:/var/app -p 9000:9000 docker-php-example-backend
docker run -it -v $PWD/public:/var/app/public -p 8080:8080 docker-php-example-server
```