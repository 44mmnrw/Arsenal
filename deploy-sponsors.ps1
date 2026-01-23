param(
    [Parameter(Position=0)]
    [ValidateSet('export', 'upload', 'full', 'import', 'check')]
    [string]$Command = 'full'
)

$REMOTE_USER = $env:SSH_USER
$REMOTE_HOST = $env:SSH_HOST
$REMOTE_PATH = "/tmp/"
$REMOTE_DB = "arsenal"

$PROJECT_ROOT = $PSScriptRoot
$EXPORT_DIR = Join-Path $PROJECT_ROOT "service_scripts_ai\exports"
$TIMESTAMP = Get-Date -Format "yyyy-MM-dd_HH-mm-ss"

# Ищем последний экспортированный файл
$latest_file = Get-ChildItem $EXPORT_DIR -Filter "wp_arsenal_sponsors_*.sql" -ErrorAction SilentlyContinue | Sort-Object LastWriteTime -Descending | Select-Object -First 1

if ($latest_file) {
    $EXPORT_FILE = $latest_file.FullName
} else {
    $FILENAME = "wp_arsenal_sponsors_$TIMESTAMP.sql"
    $EXPORT_FILE = Join-Path $EXPORT_DIR $FILENAME
}

function Write-Info {
    param([string]$Message)
    Write-Host "i  $Message" -ForegroundColor Cyan
}

function Write-Success {
    param([string]$Message)
    Write-Host "OK $Message" -ForegroundColor Green
}

function Write-ErrorMsg {
    param([string]$Message)
    Write-Host "ER $Message" -ForegroundColor Red
}

function Export-Table {
    Write-Info "Exporting wp_arsenal_sponsors table..."
    
    $php_script = Join-Path $PROJECT_ROOT "service_scripts_ai\migrate-sponsors-table.php"
    
    & php $php_script --export
    
    if ($LASTEXITCODE -eq 0) {
        Write-Success "Table exported"
        return $true
    } else {
        Write-ErrorMsg "Error during export"
        return $false
    }
}

function Upload-To-Server {
    if (-not (Test-Path $EXPORT_FILE)) {
        Write-ErrorMsg "File not found: $EXPORT_FILE"
        Write-Info "Run: .\deploy-sponsors.ps1 export"
        return $false
    }
    
    Write-Info "Uploading file to remote server..."
    Write-Info "File: $(Split-Path $EXPORT_FILE -Leaf)"
    
    try {
        $scp_path = $EXPORT_FILE -replace '\\', '/'
        $scp_target = "$REMOTE_USER@$REMOTE_HOST`:$REMOTE_PATH"
        
        & scp.exe $scp_path $scp_target 2>&1
        
        if ($LASTEXITCODE -eq 0) {
            $filename = Split-Path $EXPORT_FILE -Leaf
            Write-Success "File uploaded: $REMOTE_PATH$filename"
            return $true
        } else {
            Write-ErrorMsg "Error uploading file"
            Write-Info "Make sure OpenSSH is installed"
            return $false
        }
    }
    catch {
        Write-ErrorMsg "Error: $_"
        return $false
    }
}

function Show-Instructions {
    Write-Host ""
    Write-Host "======================================" -ForegroundColor Yellow
    Write-Host "IMPORT INSTRUCTIONS" -ForegroundColor Yellow
    Write-Host "======================================" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "1. SSH to server:" -ForegroundColor Cyan
    Write-Host "   ssh $REMOTE_USER@$REMOTE_HOST" -ForegroundColor White
    Write-Host ""
    Write-Host "2. Import table:" -ForegroundColor Cyan
    Write-Host "   mysql -u arsenal_user -p arsenal < $REMOTE_PATH$FILENAME" -ForegroundColor White
    Write-Host ""
    Write-Host "3. Verify:" -ForegroundColor Cyan
    Write-Host "   mysql -u arsenal_user -p arsenal -e 'SELECT COUNT(*) FROM wp_arsenal_sponsors;'" -ForegroundColor White
    Write-Host ""
    Write-Host "======================================" -ForegroundColor Yellow
    Write-Host ""
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Magenta
Write-Host "wp_arsenal_sponsors MIGRATION" -ForegroundColor Magenta
Write-Host "======================================" -ForegroundColor Magenta
Write-Host ""

switch ($Command) {
    'export' {
        Export-Table | Out-Null
    }
    
    'upload' {
        if (-not (Test-Path $EXPORT_FILE)) {
            Write-ErrorMsg "Export file not found"
            Write-Info "First run: .\deploy-sponsors.ps1 export"
            exit 1
        }
        Upload-To-Server | Out-Null
    }
    
    'full' {
        if (Export-Table) {
            if (Upload-To-Server) {
                Show-Instructions
            }
        }
    }
    
    'check' {
        Write-Info "Check requires SSH access"
        Show-Instructions
    }
    
    default {
        Write-ErrorMsg "Unknown command: $Command"
        Write-Host ""
        Write-Host "Available commands:" -ForegroundColor Cyan
        Write-Host "  export    Export table to SQL" -ForegroundColor White
        Write-Host "  upload    Upload to server" -ForegroundColor White
        Write-Host "  full      Full migration (export + upload)" -ForegroundColor White
        Write-Host ""
        exit 1
    }
}

Write-Host ""
