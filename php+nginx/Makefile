.PHONY: build push

default: build

build:
	docker build -t pmconnect/docker-php-example-server --target server .
	docker build -t pmconnect/docker-php-example-backend --target backend .

push: build
	docker push pmconnect/docker-php-example-server
	docker push pmconnect/docker-php-example-backend

