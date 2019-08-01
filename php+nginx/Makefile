.PHONY: install update add_dep add_dev_dep test build_deps build_test build create_docker_network run run_dev destroy push docker_compose

# Set image names to use when building docker images.
test_image_name ?= docker-php-example-test
deps_image_name ?= docker-php-example-deps
backend_image_name ?= docker-php-example-backend
server_image_name ?= docker-php-example-server
dev_backend_image_name ?= docker-php-example-dev-backend
dev_server_image_name ?= docker-php-example-dev-server
dev_test_image_name ?= docker-php-example-dev-test

# Set the remote repo/image names to push images to.
server_remote_repo ?= pmconnect/docker-php-example-server
backend_remote_repo ?= pmconnect/docker-php-example-backend

# Specify local docker network names.
docker_network_name ?= docker-php-example
docker_server_name ?= docker-php-example-server
docker_backend_name ?= docker-php-example-backend
dev_docker_network_name ?= docker-php-example-dev
dev_docker_server_name ?= docker-php-example-dev-server
dev_docker_backend_name ?= docker-php-example-dev-backend

# Local docker host address.
host_address ?= host.docker.internal
xdebug_port ?= 9000

# Set the uid to use when building images.
ifeq ($(shell expr $$(id -u) \>= 999), 1)
	uid ?= $(shell echo $$(id -u))
else
	uid ?= 82
endif

# Set the gid to use when building images.
ifeq ($(shell expr $$(id -g) \>= 999), 1)
	gid ?= $(shell echo $$(id -g))
else
	gid ?= 82
endif

default: build

# Install all dependencies, including dev dependencies.
install: build_deps
	docker run -it -v $(CURDIR):/var/app -u "$(uid):$(gid)" $(deps_image_name) install --no-plugins --ignore-platform-reqs

# Update all dependencies, including dev dependencies.
update: build_deps
	docker run -it -v $(CURDIR):/var/app $(deps_image_name) update --no-plugins --ignore-platform-reqs

# Add a dependency package.
add_dep: build_deps
	docker run -it -v $(CURDIR):/var/app $(deps_image_name) require --no-plugins --ignore-platform-reqs $(package)

# Add a dev-only dependency package.
add_dev_dep: build_deps
	docker run -it -v $(CURDIR):/var/app $(deps_image_name) require --no-plugins --ignore-platform-reqs --dev $(package)

# Test our package for production.
test: destroy build_test create_docker_network
	docker run -it --network=$(docker_network_name) $(test_image_name)

# Test our package locally.
test_dev: destroy build_test_dev create_docker_network
	docker run -it --network=$(dev_docker_network_name) -u "$(uid):$(gid)" -v $(CURDIR):/var/app $(dev_test_image_name)

# Build an image to use to handle all installation and dependency management.
build_deps:
	docker build -t $(deps_image_name) --target dev_deps .

# Build an image to use to handle all testing.
build_test:
	docker build -t $(test_image_name) --target test .

# Build na image to use to handle local testing.
build_test_dev:
	docker build -t $(dev_test_image_name) --build-arg uid=$(uid) --build-arg gid=$(gid) --target test .

# Build the projects docker images for production.
build: test
	docker build -t $(server_image_name) --cache-from=$(server_remote_repo) --target server .
	docker build -t $(backend_image_name) --cache-from=$(backend_remote_repo) --target backend .

# Build the projects docker images for development.
build_dev: test
	docker build -t $(dev_server_image_name) --build-arg uid=$(uid) --target server .
	docker build -t $(dev_backend_image_name) --build-arg uid=$(uid) --build-arg gid=$(gid) --build-arg with_xdebug=true --target backend .

# Setup the docker container network.
create_docker_network:
	-docker network create $(docker_network_name)
	-docker network create $(dev_docker_network_name)

# Spin up a production ready copy of the app.
run: destroy build create_docker_network
	docker run -d --network=$(docker_network_name) --name $(docker_backend_name) $(backend_image_name)
	docker run -d --network=$(docker_network_name) -p $(server_port):8080 -e PHP_HOST=$(docker_backend_name):9000 --name $(docker_server_name) $(server_image_name)

# Spin up a dev ready copy of the app, mounting local files, and watching backend logs.
run_dev: destroy install build_dev create_docker_network
	docker run -d --network=$(dev_docker_network_name) -u "$(uid):$(gid)" -e XDEBUG_CONFIG="remote_enable=1 remote_host=$(host_address) remote_port=$(xdebug_port)" --name $(dev_docker_backend_name) -v $(CURDIR):/var/app $(dev_backend_image_name)
	docker run -d --network=$(dev_docker_network_name) -u "$(uid):$(gid)" -p $(server_port):8080 -e PHP_HOST=$(docker_backend_name):9000 --name $(dev_docker_server_name) -v $(CURDIR)/public:/var/app/public $(dev_server_image_name)
	docker logs -f $(dev_docker_backend_name)

# Destroy any setup containers for the app.
destroy:
	-docker container rm --force $(docker_server_name) $(docker_backend_name) $(dev_server_image_name) $(dev_backend_image_name)
	-docker network rm $(docker_network_name) $(dev_docker_network_name)

# Build docker-compose images ready to use.
docker_compose_build:
	docker app render --set uid=$(uid) --set gid=$(gid) | docker-compose -f - build

# Render the docker compose file with the users uid/gid already set.
docker_compose:
	@docker app render --set uid=$(uid) --set gid=$(gid)

# Push the docker images to the pmconnect repos.
push: build
	docker tag $(server_image_name) $(server_remote_repo)
	docker tag $(backend_image_name) $(backend_remote_repo)
	docker push $(server_remote_repo)
	docker push $(backend_remote_repo)

