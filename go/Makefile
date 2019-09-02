.PHONY: default build build_dev build_base build_base_dev mod_download mod_tidy mod_vendor mod_verify test test_dev run run_dev

project_name ?= docker-go-example

# Set image names to use when building docker images.
base_image_name ?= $(project_name)-base
dev_base_image_name ?= $(project_name)-dev-base
app_image_name ?= $(project_name)-app
dev_app_image_name ?= $(project_name)-dev-app

# Set the uid to use when building images.
ifeq ($(shell expr $$(id -u) \>= 999), 1)
	uid ?= $(shell echo $$(id -u))
else
	uid ?= 10001
endif

# Set the gid to use when building images.
ifeq ($(shell expr $$(id -g) \>= 999), 1)
	gid ?= $(shell echo $$(id -g))
else
	gid ?= 10001
endif

default: build

build:
	docker build -t $(app_image_name) --target production .

build_dev:
	docker build -t $(dev_app_image_name) --build-arg uid=$(uid) --build-arg gid=$(gid) --target production .

build_base:
	docker build -t $(base_image_name) --target base .

build_base_dev:
	docker build -t $(dev_base_image_name) --build-arg uid=$(uid) --build-arg gid=$(gid) --target base .

mod_download: build_base_dev
	docker run -v $(CURDIR):/app:cached $(dev_base_image_name) mod download

mod_tidy: build_base_dev
	docker run -v $(CURDIR):/app:cached $(dev_base_image_name) mod tidy

mod_vendor: build_base_dev
	docker run -v $(CURDIR):/app:cached $(dev_base_image_name) mod vendor -v

mod_verify: build_base_dev
	docker run -v $(CURDIR):/app:cached $(dev_base_image_name) mod verify

test: build_base
	docker run $(base_image_name) vet ./...
	docker run --entrypoint golint $(base_image_name) ./...
	docker run --entrypoint staticcheck $(base_image_name) ./...
	docker run $(base_image_name) test ./...

test_dev: build_base_dev
	docker run -v $(CURDIR):/app:cached $(dev_base_image_name) vet ./...
	docker run -v $(CURDIR):/app:cached --entrypoint golint $(dev_base_image_name) ./...
	docker run -v $(CURDIR):/app:cached --entrypoint staticcheck $(dev_base_image_name) ./...
	docker run -v $(CURDIR):/app:cached $(dev_base_image_name) test ./...

run: build
	docker run $(app_image_name)

run_dev: build_dev
	docker run $(dev_app_image_name)

docker-compose.yml: example.dockerapp
	docker app render --set uid=$(uid) --set gid=$(gid) > docker-compose.yml