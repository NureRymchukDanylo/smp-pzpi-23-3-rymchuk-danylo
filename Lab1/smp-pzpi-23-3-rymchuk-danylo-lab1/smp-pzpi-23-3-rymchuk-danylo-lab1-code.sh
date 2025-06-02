#!/bin/bash

username=$(whoami)
script="${username}-task2"
version="1.0"

print_help() {
    cat <<EOF
Використання:
  ${script} [--help | --version] | [[-q|--quiet] [академ_група] файл_із_cist.csv]

Опції:
  --help       Показати цю довідку і завершити роботу
  --version    Вивести версію скрипта і завершити роботу
  -q, --quiet  Не виводити інформацію у стандартний потік виведення
EOF
}

print_version() {
    echo "${script} версія ${version}"
}

quiet=false
group=""
csv=""

# Аргументи
while [[ $# -gt 0 ]]; do
    case "$1" in
        --help)
            print_help; exit 0 ;;
        --version)
            print_version; exit 0 ;;
        -q|--quiet)
            quiet=true; shift ;;
        *)
            if [[ -z "$group" ]]; then
                group="$1"
            elif [[ -z "$csv" ]]; then
                csv="$1"
            fi
            shift ;;
    esac
done

if [[ -z "$csv" ]]; then
    echo "Виберіть CSV-файл із розкладом:"
    select csv in $(ls TimeTable_??_??_20??.csv 2>/dev/null | sort); do
        [[ -n "$csv" ]] && break
    done
fi

[[ ! -r "$csv" ]] && {
    echo "Помилка: файл '$csv' не знайдено або він недоступний" >&2
    exit 1
}

temp="Temp_$csv"
if ! iconv -f WINDOWS-1251 -t UTF-8 "$csv" |
     tr '\r' '\n' |
     awk 'NR > 1 {print}' |
     sort -t',' -k1,1 -k2.8,2.11n -k2.5,2.6n -k2.2,2.3n -k3.2,3.3n -k3.5,3.6n > "$temp"
then
    echo "Помилка: не вдалося обробити файл '$csv'" >&2
    exit 1
fi

groups=($(awk -F' ' '$2 ~ /-/ {gsub(/"/, "", $1); print $1}' "$temp" | uniq))

if [[ -z "$group" ]]; then
    if (( ${#groups[@]} == 1 )); then
        group="${groups[0]}"
        echo "Автоматично обрано групу"
    else
        echo "Виберіть академічну групу:"
        select group in "${groups[@]}"; do
            [[ -n "$group" ]] && break
        done
    fi
fi

if ! grep -q "$group" "$temp"; then
    echo "Групу '$group' не знайдено у файлі '$csv'" >&2
    if (( ${#groups[@]} == 1 )); then
        group="${groups[0]}"
        echo "Автоматично обрано групу"
    else
        echo "Виберіть академічну групу:"
        select group in "${groups[@]}"; do
            [[ -n "$group" ]] && break
        done
    fi
fi

output_file="Google_${csv}"
awk_script='
BEGIN {
    OFS = ","; print "Subject,Start Date,Start Time,End Date,End Time,Description"
}
$1 ~ ("^\"" group) {
    for (i = 1; i <= NF; i++) gsub(/"/, "", $i)
    gsub(group " - ", "", $1)
    split($2, sd, "."); split($4, ed, ".")
    split($3, st, ":"); split($5, et, ":")
    start_date = sd[2] "/" sd[1] "/" sd[3]
    end_date   = ed[2] "/" ed[1] "/" ed[3]
    sh = st[1] + 0; eh = et[1] + 0
    sfx = (sh < 12) ? "AM" : "PM"
    efx = (eh < 12) ? "AM" : "PM"
    sh = (sh % 12 == 0) ? 12 : sh % 12
    eh = (eh % 12 == 0) ? 12 : eh % 12
    start_time = sprintf("%02d:%s %s", sh, st[2], sfx)
    end_time   = sprintf("%02d:%s %s", eh, et[2], efx)
    split($1, p, " ")
    key = p[1] " " p[2]
    if ($1 ~ / Лб/) {
        if (!(key in lab)) { lab[key] = 1; cnt[key] = 1 }
        else {
            lab[key]++
            if (lab[key] > 2) { lab[key] = 1; cnt[key]++ }
        }
    } else {
        if (!(key in cnt)) cnt[key] = 1
        else cnt[key]++
    }
    subj = "\"" $1 "; №" cnt[key] "\""
    print subj, start_date, start_time, end_date, end_time, "\"" $12 "\""
}'

if $quiet; then
    awk -F'",' -v group="$group" "$awk_script" "$temp" > "$output_file"
    status=$?
else
    awk -F'",' -v group="$group" "$awk_script" "$temp" | tee "$output_file"
    status=${PIPESTATUS[0]}
fi

rm -f "$temp"

[[ $status -ne 0 ]] && {
    echo "Помилка: невдала обробка файла" >&2
    exit 1
}

echo "Готовий файл: $output_file"
exit 0
