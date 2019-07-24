.PHONY: build push install update add_dep

default: build

# Install all dependencies, including dev dependencies.
install: build_deps
	docker run -it -v $(CURDIR):/var/app docker-php-example-deps install --no-plugins --ignore-platform-reqs

# Update all dependencies, including dev dependencies.
update: build_deps
	docker run -it -v $(CURDIR):/var/app docker-php-example-deps update --no-plugins --ignore-platform-reqs

# Add a dependency package.
add_dep: build_deps
	docker run -it -v $(CURDIR):/var/app docker-php-example-deps require --no-plugins --ignore-platform-reqs $(package)

# Add a dev-only dependency package.
add_dev_dep: build_deps
	docker run -it -v $(CURDIR):/var/app docker-php-example-deps require --no-plugins --ignore-platform-reqs --dev $(package)

# Test our package.
test: build_test
	docker run -it -v $(CURDIR):/var/app docker-php-example-test

# Build an image to use to handle all installation and dependency management.
build_deps:
	docker build -t docker-php-example-deps --target dev_deps .

# Build an image to use to handle all testing.
build_test:
	docker build -t docker-php-example-test --target test .

# Build the projects docker images.
build: test
	docker build -t docker-php-example-server --target server .
	docker build -t docker-php-example-backend --target backend .

# Spin up a production ready copy of the app.
run: build
  docker run -d -p $(server_port):8080 -e PHP_HOST=$(php_host):$(php_port) -n docker-php-example-server docker-php-example-server
  docker run -d -p $(php_port):9000 -n docker-php-example-backend docker-php-example-backend

# Spin up a dev ready copy of the app, mounting local files.
run_dev: install
  docker run -d -p $(server_port):8080 -e PHP_HOST=$(php_host):$(php_port) -n docker-php-example-server -v $(CURDIR)/public:/var/app/public docker-php-example-server
  docker run -d -p $(php_port):9000 -n docker-php-example-backend -v $(CURDIR):/var/app docker-php-example-backend

# Destroy any setup containers for hte app.
destroy:
  docker container rm --force docker-php-example-server docker-php-example-backend

# Push the docker images to the pmconnect repos.
push: build
	docker tag docker-php-example-server pmconnect/docker-php-example-server
	docker tag docker-php-example-backend pmconnect/docker-php-example-backend
	docker push pmconnect/docker-php-example-server
	docker push pmconnect/docker-php-example-backend

