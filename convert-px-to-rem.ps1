# PowerShell скрипт для конвертации px в rem во всех CSS файлах
# Использование: .\convert-px-to-rem.ps1 -FilePath "path/to/file.css"

param(
    [Parameter(Mandatory=$true)]
    [string]$FilePath
)

# Таблица конвертации px в rem (1rem = 16px)
$conversions = @(
    @{ px = "480px"; rem = "30rem" },
    @{ px = "430px"; rem = "26.875rem" },
    @{ px = "400px"; rem = "25rem" },
    @{ px = "350px"; rem = "21.875rem" },
    @{ px = "320px"; rem = "20rem" },
    @{ px = "300px"; rem = "18.75rem" },
    @{ px = "250px"; rem = "15.625rem" },
    @{ px = "200px"; rem = "12.5rem" },
    @{ px = "180px"; rem = "11.25rem" },
    @{ px = "150px"; rem = "9.375rem" },
    @{ px = "120px"; rem = "7.5rem" },
    @{ px = "100px"; rem = "6.25rem" },
    @{ px = "96px"; rem = "6rem" },
    @{ px = "90px"; rem = "5.625rem" },
    @{ px = "80px"; rem = "5rem" },
    @{ px = "72px"; rem = "4.5rem" },
    @{ px = "60px"; rem = "3.75rem" },
    @{ px = "56px"; rem = "3.5rem" },
    @{ px = "50px"; rem = "3.125rem" },
    @{ px = "48px"; rem = "3rem" },
    @{ px = "42px"; rem = "2.625rem" },
    @{ px = "40px"; rem = "2.5rem" },
    @{ px = "32px"; rem = "2rem" },
    @{ px = "30px"; rem = "1.875rem" },
    @{ px = "28px"; rem = "1.75rem" },
    @{ px = "25.6px"; rem = "1.6rem" },
    @{ px = "25.2px"; rem = "1.575rem" },
    @{ px = "25px"; rem = "1.5625rem" },
    @{ px = "24px"; rem = "1.5rem" },
    @{ px = "22.4px"; rem = "1.4rem" },
    @{ px = "22px"; rem = "1.375rem" },
    @{ px = "20px"; rem = "1.25rem" },
    @{ px = "18px"; rem = "1.125rem" },
    @{ px = "16px"; rem = "1rem" },
    @{ px = "14px"; rem = "0.875rem" },
    @{ px = "13px"; rem = "0.8125rem" },
    @{ px = "12px"; rem = "0.75rem" },
    @{ px = "11px"; rem = "0.6875rem" },
    @{ px = "10px"; rem = "0.625rem" },
    @{ px = "9px"; rem = "0.5625rem" },
    @{ px = "8px"; rem = "0.5rem" },
    @{ px = "6px"; rem = "0.375rem" },
    @{ px = "5px"; rem = "0.3125rem" },
    @{ px = "4.5px"; rem = "0.28125rem" },
    @{ px = "4px"; rem = "0.25rem" },
    @{ px = "3px"; rem = "0.1875rem" },
    @{ px = "2px"; rem = "0.125rem" }
)

# Читаем файл
if (-Not (Test-Path $FilePath)) {
    Write-Error "Файл не найден: $FilePath"
    exit 1
}

$content = Get-Content $FilePath -Raw

$replacementsCount = 0

# Применяем конвертации по порядку (от больших к малым)
foreach ($conversion in $conversions) {
    $pattern = [regex]::Escape($conversion.px)
    $newContent = $content -replace $pattern, $conversion.rem
    
    if ($newContent -ne $content) {
        $count = (Select-String -InputObject $content -Pattern $pattern -AllMatches).Matches.Count
        $replacementsCount += $count
        $content = $newContent
        Write-Host "✓ Заменено $count × $($conversion.px) → $($conversion.rem)"
    }
}

# Сохраняем файл
$content | Set-Content $FilePath -NoNewline

Write-Host "`n✅ Конвертирован файл: $(Split-Path -Leaf $FilePath)"
Write-Host "Всего заменено: $replacementsCount px значений"
