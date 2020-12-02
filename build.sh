#!/bin/sh

DOCKER_IMAGE=mjdymalla/iso3166-library

docker build -t $DOCKER_IMAGE .
docker run --rm -it $DOCKER_IMAGE $@
