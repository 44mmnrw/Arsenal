#!/bin/bash

# Скрипт для разделения больших INSERT блоков и импорта
# Это решает проблему "MySQL server has gone away"

SQL_FILE="$1"
DB_USER="arsenal_usr"
DB_PASS="jV:<Mn2E_&RPZckF"
DB_NAME="arsenal"

if [ ! -f "$SQL_FILE" ]; then
    echo "Ошибка: файл $SQL_FILE не найден"
    exit 1
fi

echo "Обработка файла: $SQL_FILE"
echo "Размер: $(du -h "$SQL_FILE" | cut -f1)"
echo ""

# Использ perl для разделения больших INSERT на отдельные строки
perl - "$SQL_FILE" "$DB_USER" "$DB_PASS" "$DB_NAME" <<'PERL'
#!/usr/bin/perl
use strict;
use warnings;

my ($file, $user, $pass, $db) = @ARGV;
my $line_count = 0;
my $insert_count = 0;

open(my $fh, '<', $file) or die "Can't open $file: $!";

my $temp_sql = "/tmp/import_chunk_$$.sql";
open(my $out, '>', $temp_sql) or die "Can't write to $temp_sql: $!";

my $in_insert = 0;
my $insert_prefix = '';

while (my $line = <$fh>) {
    $line_count++;
    
    # Начало INSERT блока
    if ($line =~ /^INSERT INTO/) {
        if ($in_insert && tell($out) > 10000000) {  # Если файл > 10MB, импортируем
            close($out);
            system("mysql -u $user -p'$pass' $db < $temp_sql");
            unlink($temp_sql);
            open($out, '>', $temp_sql) or die "Can't write to $temp_sql: $!";
        }
        $in_insert = 1;
        $insert_prefix = $line;
        print $out $line;
    }
    # Конец строки значений в INSERT
    elsif ($line =~ /^\);$/ && $in_insert) {
        print $out $line;
        $in_insert = 0;
        $insert_count++;
        
        # Печать прогресса
        if ($insert_count % 100 == 0) {
            print "✓ Обработано $insert_count INSERT блоков, $line_count строк\n";
        }
    }
    # Разделитель между кортежами
    elsif ($line =~ /^\),\(/ && $in_insert) {
        # Заканчиваем текущий INSERT
        print $out ");\n";
        # Начинаем новый
        print $out $insert_prefix;
        print $out "(" . substr($line, 2);  # Пропускаем "),"
    }
    else {
        print $out $line if !$in_insert || $line =~ /\),\(/;
    }
}

close($out);
close($fh);

# Импортируем последний чанк
if (-s $temp_sql > 0) {
    system("mysql -u $user -p'$pass' $db < $temp_sql");
}
unlink($temp_sql);

print "\n✅ Импорт завершён!\n";
print "   Обработано $line_count строк, $insert_count INSERT блоков\n";
PERL

exit $?
