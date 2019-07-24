.PHONY: install update add_dep add_dev_dep test build_deps build_test build create_docker_network run run_dev destroy push

test_image_name ?= docker-php-example-test
deps_image_name ?= docker-php-example-deps
backend_image_name ?= docker-php-example-backend
server_image_name ?= docker-php-example-server

server_remote_repo ?= pmconnect/docker-php-example-server
backend_remote_repo ?= pmconnect/docker-php-example-backend

docker_network_name ?= docker-php-example
docker_server_name ?= docker-php-example-server
docker_backend_name ?= docker-php-example-backend

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
	docker run -it --network=$(docker_network_name) -v $(CURDIR):/var/app $(test_image_name)

# Build an image to use to handle all installation and dependency management.
build_deps:
	docker build -t $(deps_image_name) --target dev_deps .

# Build an image to use to handle all testing.
build_test:
	docker build -t $(test_image_name) --target test .

# Build the projects docker images.
build: test
	docker build -t $(server_image_name) --target server .
	docker build -t $(backend_image_name) --target backend .

# Setup the docker container network.
create_docker_network:
	-docker network create $(docker_network_name)

# Spin up a production ready copy of the app.
run: build create_docker_network
	docker run -d --network=$(docker_network_name) --name $(docker_backend_name) $(backend_image_name)
	docker run -d --network=$(docker_network_name) -p $(server_port):8080 -e PHP_HOST=$(docker_backend_name):9000 --name $(docker_server_name) $(server_image_name)

# Spin up a dev ready copy of the app, mounting local files.
run_dev: install create_docker_network
	docker run -d --network=$(docker_network_name) --name $(docker_backend_name) -v $(CURDIR):/var/app $(backend_image_name)
	docker run -d --network=$(docker_network_name) -p $(server_port):8080 -e PHP_HOST=$(docker_backend_name):9000 --name $(docker_server_name) -v $(CURDIR)/public:/var/app/public $(server_image_name)

# Destroy any setup containers for hte app.
destroy:
	-docker container rm --force $(docker_server_name) $(docker_backend_name)
	-docker network rm $(docker_network_name)

# Push the docker images to the pmconnect repos.
push: build
	docker tag $(server_image_name) $(server_remote_repo)
	docker tag $(backend_image_name) $(backend_remote_repo)
	docker push $(server_remote_repo)
	docker push $(backend_remote_repo)

