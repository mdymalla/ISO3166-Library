#!/bin/sh

DOCKER_IMAGE=mjdymalla/iso3166-library

if [ $1 = "rebuild" ]
then
    docker build --no-cache -t $DOCKER_IMAGE .
else
    docker build -t $DOCKER_IMAGE .
fi

docker run --rm -it $DOCKER_IMAGE