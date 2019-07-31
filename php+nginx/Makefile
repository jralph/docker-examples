.PHONY: install update add_dep add_dev_dep test build_deps build_test build create_docker_network run run_dev destroy push docker_compose

# Set image names to use when building docker images.
test_image_name ?= docker-php-example-test
deps_image_name ?= docker-php-example-deps
backend_image_name ?= docker-php-example-backend
server_image_name ?= docker-php-example-server
dev_backend_image_name ?= docker-php-example-dev-backend

# Set the remote repo/image names to push images to.
server_remote_repo ?= pmconnect/docker-php-example-server
backend_remote_repo ?= pmconnect/docker-php-example-backend

# Specify local docker network names.
docker_network_name ?= docker-php-example
docker_server_name ?= docker-php-example-server
docker_backend_name ?= docker-php-example-backend

# Local docker host address.
host_address ?= host.docker.internal
xdebug_port ?= 9000

# Set the uid and gid to use when building images.
uid ?= 82
gid ?= 82

default: build

# Install all dependencies, including dev dependencies.
install: build_deps
	docker run -it -v $(CURDIR):/var/app $(deps_image_name) install --no-plugins --ignore-platform-reqs

# Update all dependencies, including dev dependencies.
update: build_deps
	docker run -it -v $(CURDIR):/var/app $(deps_image_name) update --no-plugins --ignore-platform-reqs

# Add a dependency package.
add_dep: build_deps
	docker run -it -v $(CURDIR):/var/app $(deps_image_name) require --no-plugins --ignore-platform-reqs $(package)

# Add a dev-only dependency package.
add_dev_dep: build_deps
	docker run -it -v $(CURDIR):/var/app $(deps_image_name) require --no-plugins --ignore-platform-reqs --dev $(package)

# Test our package.
test: build_test create_docker_network
	docker run -it --network=$(docker_network_name) -u "$(uid):$(gid)" -v $(CURDIR):/var/app $(test_image_name)

# Build an image to use to handle all installation and dependency management.
build_deps:
	docker build -t $(deps_image_name) --target dev_deps .

# Build an image to use to handle all testing.
build_test:
	docker build -t $(test_image_name) --build-arg uid=$(uid) --build-arg gid=$(gid) --target test .

# Build the projects docker images.
build: test
	docker build -t $(server_image_name) --build-arg uid=$(uid) --build-arg gid=$(gid) --target server .
	docker build -t $(backend_image_name) --build-arg uid=$(uid) --build-arg gid=$(gid) --target backend .
	docker build -t $(dev_backend_image_name) --build-arg uid=$(uid) --build-arg gid=$(gid) --build-arg with_xdebug=true --target backend .

# Setup the docker container network.
create_docker_network:
	-docker network create $(docker_network_name)

# Spin up a production ready copy of the app.
run: destroy build create_docker_network
	docker run -d --network=$(docker_network_name) --name $(docker_backend_name) $(backend_image_name)
	docker run -d --network=$(docker_network_name) -p $(server_port):8080 -e PHP_HOST=$(docker_backend_name):9000 --name $(docker_server_name) $(server_image_name)

# Spin up a dev ready copy of the app, mounting local files, and watching backend logs.
run_dev: destroy install build create_docker_network
	docker run -d --network=$(docker_network_name) -u "$(uid):$(gid)" -e XDEBUG_CONFIG="remote_enable=1 remote_host=$(host_address) remote_port=$(xdebug_port)" --name $(docker_backend_name) -v $(CURDIR):/var/app $(dev_backend_image_name)
	docker run -d --network=$(docker_network_name) -u "$(uid):$(gid)" -p $(server_port):8080 -e PHP_HOST=$(docker_backend_name):9000 --name $(docker_server_name) -v $(CURDIR)/public:/var/app/public $(server_image_name)
	docker logs -f $(docker_backend_name)

# Destroy any setup containers for hte app.
destroy:
	-docker container rm --force $(docker_server_name) $(docker_backend_name)
	-docker network rm $(docker_network_name)

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

