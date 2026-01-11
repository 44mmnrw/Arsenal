# PowerShell скрипт для конвертирования rgba в hex и обновления CSS файлов
# Использование: .\convert-rgba-to-hex.ps1

$cssPath = "C:\laragon\www\arsenal\wp-content\themes\arsenal\assets\css"
$cssFiles = Get-ChildItem $cssPath -Filter "*.css" -Recurse

# Таблица конвертирования rgba в hex с fallback opacity (используем box-shadow переменную)
$rgbaToHexMap = @{
    'rgba(0, 0, 0, 0.1)' = '#00000019';           # 10% opacity = 19 hex
    'rgba(0,0,0,0.1)' = '#00000019';              # без пробелов
    'rgba(255, 255, 255, 0.1)' = '#ffffff1a';     # белый 10%
    'rgba(255, 255, 255, 0.8)' = '#ffffffcc';     # белый 80%
    'rgba(0, 0, 0, 0.06)' = '#0000000f';          # черный 6%
    'rgba(255, 255, 255, 0.3)' = '#ffffff4d';     # белый 30%
    'rgba(0, 0, 0, 0.2)' = '#00000033';           # черный 20%
    'rgba(0, 0, 0, 0.3)' = '#0000004d';           # черный 30%
    'rgba(255, 255, 255, 0.2)' = '#ffffff33';     # белый 20%
    'rgba(255, 255, 255, 0.15)' = '#ffffff26';    # белый 15%
    'rgba(255, 255, 255, 0.25)' = '#ffffff40';    # белый 25%
    'rgba(0, 0, 0, 0.08)' = '#00000014';          # черный 8%
    'rgba(255, 255, 255, 0.9)' = '#ffffffE6';     # белый 90%
    'rgba(255, 255, 255, 0.5)' = '#ffffff80';     # белый 50%
    'rgba(255, 255, 255, 0.4)' = '#ffffff66';     # белый 40%
    'rgba(255, 255, 255, 0.7)' = '#ffffffB3';     # белый 70%
    'rgba(0, 0, 0, 0.15)' = '#00000026';          # черный 15%
    'rgba(255, 255, 255, 0.6)' = '#ffffff99';     # белый 60%
    'rgba(0, 0, 0, 0.8)' = '#000000CC';           # черный 80%
    'rgba(0, 0, 0, 0.6)' = '#00000099';           # черный 60%
    'rgba(0, 0, 0, 0)' = 'transparent';           # полная прозрачность
    'rgba(0, 0, 0, 0.95)' = '#000000F2';          # черный 95%
    'rgba(30, 30, 30, 0.9)' = '#1e1e1eE6';        # темный 90%
    'rgba(255,255,255,0.1)' = '#ffffff1a';        # белый 10% без пробелов
    'rgba(0, 0, 0, 0.85)' = '#000000D9';          # черный 85%
    'rgba(0, 0, 0, 0.7)' = '#000000B3';           # черный 70%
    'rgba(0, 0, 0, 0.4)' = '#00000066';           # черный 40%
    'rgba(0,0,0,0.05)' = '#0000000D';             # черный 5% без пробелов
    'rgba(0,0,0,0.12)' = '#0000001F';             # черный 12% без пробелов
    'rgba(0,0,0,0.6)' = '#00000099';              # черный 60% без пробелов
    'rgba(0,0,0,0.8)' = '#000000CC';              # черный 80% без пробелов
    'rgba(0, 0, 0, 0.25)' = '#00000040';          # черный 25%
    'rgba(255, 255, 255, 0.05)' = '#ffffff0D';    # белый 5%
    'rgba(253, 199, 0, 0.05)' = '#fdc7000D';      # желтый 5%
    'rgba(255, 26, 26, 0.05)' = '#ff1a1a0D';      # красный 5%
    'rgba(21, 93, 252, 0.05)' = '#155dfc0D';      # синий 5%
    'rgba(255, 26, 26, 0.1)' = '#ff1a1a1a';       # красный 10%
    'rgba(255, 254, 254, 0.4)' = '#fffefe66';     # светлый белый 40%
    'rgba(255, 254, 254, 0.3)' = '#fffefe4d';     # светлый белый 30%
    'rgba(196, 30, 58, 0.12)' = '#c41e3a1F';      # красный 12%
    'rgba(0, 166, 62, 1)' = '#00a63e';            # зеленое поле 100%
    'rgba(0, 130, 54, 1)' = '#008236';            # темное зеленое поле 100%
    'rgba(255, 26, 26, 0.9)' = '#ff1a1aE6';       # красный 90%
    'rgba(153, 0, 0, 0.25)' = '#990000 40';       # темный красный 25%
    'rgba(255, 255, 255, 0.85)' = '#ffffffD9';    # белый 85%
    'rgba(255, 26, 26, 0.7)' = '#ff1a1aB3';       # красный 70%
    'rgba(243, 244, 246, 1)' = '#f3f4f6';         # светло-серый 100%
    'rgba(229, 231, 235, 1)' = '#e5e7eb';         # светло-серый 100%
    'rgb(255, 255, 255)' = '#ffffff';             # чистый белый
    'rgba(255, 26, 26, 0.3)' = '#ff1a1a4d';       # красный 30%
    'rgba(255, 26, 26, 0.4)' = '#ff1a1a66';       # красный 40%
    'rgba(255, 255, 255, 0.3)' = '#ffffff4d';     # белый 30%
    'rgba(0, 0, 0, 0.1)' = 'var(--shadow-light)'; # использовать переменную
}

Write-Host "`n🔄 Начинаю конвертирование rgba в hex..." -ForegroundColor Yellow
Write-Host "📁 Найдено файлов: $($cssFiles.Count)" -ForegroundColor Cyan

$totalChanges = 0

foreach ($file in $cssFiles) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $originalContent = $content
    
    foreach ($rgba in $rgbaToHexMap.Keys) {
        $hex = $rgbaToHexMap[$rgba]
        if ($content -contains $rgba) {
            $content = $content -replace [regex]::Escape($rgba), $hex
            Write-Host "  ✓ $($file.Name): $rgba → $hex" -ForegroundColor Green
            $totalChanges++
        }
    }
    
    if ($content -ne $originalContent) {
        Set-Content $file.FullName $content -Encoding UTF8
        Write-Host "  💾 Сохранен: $($file.Name)" -ForegroundColor Cyan
    }
}

Write-Host "`n✅ Готово! Всего изменений: $totalChanges" -ForegroundColor Green
Write-Host "📊 Обновлено файлов CSS с конвертированными цветами" -ForegroundColor Yellow
