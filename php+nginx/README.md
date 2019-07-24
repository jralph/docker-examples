# Docker PHP Example

This is an example of setting up a generic php application using nginx and php fpm.

## How It Works

The way docker works best is running a single service/process per container.
So here we split php-fpm and nginx into separate containers to run side by side.

The nginx container contains an env variable that can be set, to configure the location
of the php backend server. By default, this variable is set to use `localhost:9000`.
This will work on kubernetes and standalone docker out of the box. For docker-compose,
we can set this to the name of the backend service, `PHP_HOST=backend:9000`.

## Docker App

```bash
# Build required images.
docker app render | docker-compose -f - build

# Bring the application up and stream logs from the server and backend.
docker app render | docker-compose -f - up server backend

# Remove the application
docker app render | docker-compose -f - down
```

## Kubernetes

```bash
# Kustomize is used to make modifications to the kubernetes files dynamically.
# Creates the following defintions:
#     - service
#     - deployment
#     - ingress
#     - networkpolicy
kubectl apply -k ./docker/k8s/

# Delete the application.
kubectl delete -k ./docker/k8s/
```

## Standalone Docker

```bash
# Build the required images
docker build -t pmconnect/docker-php-example-server --target server .
docker build -t pmconnect/docker-php-example-backend --target backend .

# Run both images.
# Ensure to set the PHP_HOST variable on the server image to point to your exposed port on the backend image.
docker run -it -p 9000:9000 pmconnect/docker-php-example-backend
docker run -it -p 8080:8080 pmconnect/docker-php-example-server
```