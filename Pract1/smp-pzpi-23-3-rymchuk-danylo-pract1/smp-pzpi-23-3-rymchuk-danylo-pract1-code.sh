#!/bin/bash

function print_help() {
    echo "Використання: $0 [--help | --version] | [висота_ялинки ширина_снігу]"
}

function print_version() {
    echo "Програма $0, версія 0.1"
}

if [[ "$1" == "--help" ]]; then
    print_help
    exit 0
elif [[ "$1" == "--version" ]]; then
    print_version
    exit 0
fi

if [ "$#" -ne 2 ]; then
    print_help
    exit 1
fi

half_height=$(( $1 / 2 ))
full_height=$(( half_height * 2 ))

snow_width=$2
if (( snow_width % 2 == 0 )); then
    snow_width=$(( snow_width - 1 ))
fi

if [ "$full_height" -le 0 ] || [ "$snow_width" -le 0 ]; then
    echo "Аргументи повинні бути додатними числами!" >&2
    exit 1
fi

if [ "$full_height" -lt 8 ] || [ "$snow_width" -lt 7 ]; then
    echo "Недостатні розміри для побудови ялинки" >&2
    exit 1
fi

if (( full_height - 1 != snow_width )); then
    echo "висота_ялинки занадто велика для заданої ширини снігу ($snow_width)" >&2
    echo "ширина ярусів має бути на 2 менша за ширину снігу" >&2
    exit 1
fi

char='*'

function draw_layer() {
    local height=$1
    local width=$2
    local level=1

    until [ "$level" -ge "$height" ]; do
        padding=$(( (snow_width - width) / 2 ))
        printf "%*s" "$padding" ''
        for (( i = 0; i < width; i++ )); do
            printf "%s" "$char"
        done
        echo ''
        width=$(( width + 2 ))
        (( level++ ))
        char=$([ "$char" = '*' ] && echo '#' || echo '*')
    done
}

draw_layer "$half_height" 1
draw_layer $(( half_height - 1 )) 3

trunk_padding=$(( (snow_width - 3) / 2 ))
for i in 1 2; do
    printf "%*s" "$trunk_padding" ''
    printf "%s\n" '###'
done

counter=1
while [ "$counter" -le "$snow_width" ]; do
    printf "%s" '*'
    (( counter++ ))
done

echo ''
