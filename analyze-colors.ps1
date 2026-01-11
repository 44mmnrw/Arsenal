# PowerShell скрипт для анализа всех цветов в CSS файлах
# Включает HEX, var(), и другие форматы

$cssPath = "C:\laragon\www\arsenal\wp-content\themes\arsenal\assets\css"
$cssFiles = Get-ChildItem $cssPath -Filter "*.css" -Recurse

Write-Host "`n🔍 Анализирую все CSS файлы на наличие цветов..." -ForegroundColor Yellow
Write-Host "📁 Файлов найдено: $($cssFiles.Count)" -ForegroundColor Cyan

$allContent = ""
foreach ($file in $cssFiles) {
    $content = Get-Content $file.FullName -Raw -Encoding UTF8
    $allContent += "`n/* FILE: $($file.Name) */`n" + $content
}

# Найти все цвета: HEX (#xxxxxxxx и #xxxxxx), var(), rgb, rgba
$colorPatterns = @(
    '#[0-9a-fA-F]{8}(?![0-9a-fA-F])',     # 8-digit HEX (RRGGBBAA)
    '#[0-9a-fA-F]{6}(?![0-9a-fA-F])',     # 6-digit HEX (RRGGBB)
    '#[0-9a-fA-F]{3}(?![0-9a-fA-F])',     # 3-digit HEX (RGB)
    'var\([^)]+\)',                       # CSS переменные
    'transparent',                         # ключевое слово
    'inherit',                            # ключевое слово
    'currentColor'                        # ключевое слово
)

$colors = @{}

foreach ($pattern in $colorPatterns) {
    $matches = [regex]::Matches($allContent, $pattern, [System.Text.RegularExpressions.RegexOptions]::IgnoreCase)
    foreach ($match in $matches) {
        $color = $match.Value.ToLower()
        if ($colors.ContainsKey($color)) {
            $colors[$color]++
        } else {
            $colors[$color] = 1
        }
    }
}

# Сортировка по частоте (убывание)
$sorted = $colors.GetEnumerator() | Sort-Object -Property Value -Descending

Write-Host "`n✅ Анализ завершен!" -ForegroundColor Green
Write-Host "📊 Найдено уникальных цветов: $($colors.Count)" -ForegroundColor Cyan

Write-Host "`n🏆 ТОП-50 САМЫХ ИСПОЛЬЗУЕМЫХ ЦВЕТОВ:" -ForegroundColor Yellow
Write-Host ""

$top50 = $sorted | Select-Object -First 50
$top50 | ForEach-Object {
    Write-Host "  $($_.Value.ToString().PadLeft(3)) | $($_.Name)" -ForegroundColor Green
}

# Сохранить полный список в файл
$reportPath = "C:\laragon\www\arsenal\color-analysis-full.txt"
$report = @"
# 📊 Полный анализ всех цветов в CSS файлах Arsenal
# Дата анализа: $(Get-Date -Format 'dd MMMM yyyy г.')
# Всего уникальных цветов: $($colors.Count)

## 🏆 ТОП-50 САМЫХ ИСПОЛЬЗУЕМЫХ ЦВЕТОВ

| Место | Цвет | Частота использования |
|-------|------|----------------------|
"@

$counter = 1
$top50 | ForEach-Object {
    $report += "`n| $counter | \`$($_.Name)\` | **$($_.Value)** |"
    $counter++
}

$report += "`n`n## 📋 ПОЛНЫЙ СПИСОК ВСЕХ ЦВЕТОВ ($($colors.Count))`n`n"
$report += "| Цвет | Частота |`n"
$report += "|------|---------|`n"

$sorted | ForEach-Object {
    $report += "| \`$($_.Name)\` | $($_.Value) |`n"
}

$report | Out-File $reportPath -Encoding UTF8 -Force
Write-Host "`n💾 Полный отчет сохранен: $reportPath" -ForegroundColor Cyan

# Вывести также в PowerShell
Write-Host "`n📊 ПОЛНЫЙ СПИСОК ВСЕХ ЦВЕТОВ:" -ForegroundColor Yellow
$sorted | ForEach-Object {
    $percent = ([math]::Round(($_.Value / $sorted[0].Value * 100), 1))
    $bar = "█" * ([math]::Round($percent / 2))
    Write-Host "  $($_.Value.ToString().PadLeft(3))x ($($percent.ToString().PadLeft(5))%) | $bar | $($_.Name)"
}

Write-Host "`n✅ Анализ завершен успешно!" -ForegroundColor Green
