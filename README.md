# Docker Examples

This repository contains various examples of using docker and related tech.

## Disclaimer

Please note that these examples are, well, examples. When adapting to a specific project you will
likely need to change various parts of the configuration to match your way of working and to cater for
extra needs such as privately hosted package repositories and authentication.

These examples try to cover as much of the basic setup as possible without getting into project specifics
and business logic.

## php+nginx

This example includes the setup of a basic bare-bones php project and getting it up and running
using nginx and php-fpm, in separate containers.

It includes examples for running using docker-app+docker-compose, kubernetes, and standalone docker.

It also includes a makefile for ease of use, for when running through standalone docker.

Basic unit tests are also included and are runnable through the makefile or through docker app/docker compose.

### Included Tech
- Kubernetes
- Docker Compose
- Docker App
- PHP
- NGINX
- Makefile
- Unit Tests
- Browser Tests (testcafe)
- API Tests (supertest)
- CI/CD Examples (gitlab)

## php+nginx+symfony+mysql

This example includes the setup of a symfony php project and getting it up and running using nginx
and php-fpm, in separate containers.

It includes examples for using docker-app+docker-compose, kubernetes, standalone docker, and a makefile for ease of use.

Basic unit tests are included as an example.

### Included Tech
- Kubernetes
- Docker Compose
- Docker App
- PHP
- NGINX
- Makefile
- Unit Tests
- Symfony Framework
- MySQL

## go

This example includes a setup of a go project that simply runs and repeats output at an interval.

### Included Tech
- Docker Compose
- Docker App
- Go
- Makefile
- Tests