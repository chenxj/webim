#!/bin/sh
make -C ./static_src/im clean
make -C ./static_src/im
make -C ./static_src clean
make -C ./static_src
make debug
