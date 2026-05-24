param(
    [int] $Port = 8000,
    [switch] $NoFresh,
    [switch] $NoServe
)

$ErrorActionPreference = 'Stop'
$ProgressPreference = 'SilentlyContinue'

$Root = Resolve-Path (Join-Path $PSScriptRoot '..')
Set-Location $Root

function Write-Step([string] $Message) {
    Write-Host ""
    Write-Host "==> $Message" -ForegroundColor Cyan
}

function Fail([string] $Message) {
    Write-Host ""
    Write-Host "ERROR: $Message" -ForegroundColor Red
    exit 1
}

function Find-Php {
    $candidates = @()

    if ($env:PHP_BINARY) {
        $candidates += $env:PHP_BINARY
    }

    $phpCommand = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCommand) {
        $candidates += $phpCommand.Source
    }

    $candidates += @(
        'C:\php\php.exe',
        'C:\xampp\php\php.exe',
        'C:\laragon\bin\php\php.exe'
    )

    $laragonRoot = 'C:\laragon\bin\php'
    if (Test-Path $laragonRoot) {
        $candidates += Get-ChildItem $laragonRoot -Recurse -Filter php.exe -ErrorAction SilentlyContinue |
            Select-Object -ExpandProperty FullName
    }

    foreach ($candidate in $candidates | Where-Object { $_ } | Select-Object -Unique) {
        if (Test-Path $candidate) {
            return (Resolve-Path $candidate).Path
        }
    }

    Fail 'PHP 8.2+ was not found. Install PHP and add php.exe to PATH, or put it in C:\php\php.exe.'
}

function Get-PhpModules([string] $Php, [string[]] $Args) {
    & $Php @Args -m 2>$null |
        ForEach-Object { $_.Trim().ToLowerInvariant() } |
        Where-Object { $_ -and -not $_.StartsWith('[') }
}

function Assert-CommandSucceeded([int] $ExitCode, [string] $Message) {
    if ($ExitCode -ne 0) {
        Fail $Message
    }
}

$Php = Find-Php
Write-Step "Using PHP: $Php"

& $Php -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);"
Assert-CommandSucceeded $LASTEXITCODE 'PHP 8.2 or newer is required for Laravel 12.'

$PhpArgs = @()
$baseModules = @(Get-PhpModules $Php @())
foreach ($extension in @('fileinfo', 'pdo_sqlite', 'sqlite3')) {
    if ($baseModules -notcontains $extension) {
        $PhpArgs += @('-d', "extension=$extension")
    }
}

$modules = @(Get-PhpModules $Php $PhpArgs)
$requiredExtensions = @('fileinfo', 'pdo_sqlite', 'sqlite3', 'mbstring', 'dom', 'openssl')
$missingExtensions = $requiredExtensions | Where-Object { $modules -notcontains $_ }
if ($missingExtensions) {
    Fail ('Missing PHP extensions: ' + ($missingExtensions -join ', ') + '. Enable them in php.ini and run start-demo.bat again.')
}

if (-not (Test-Path '.env')) {
    Write-Step 'Creating .env from .env.demo'
    Copy-Item '.env.demo' '.env'
}

New-Item -ItemType Directory -Force -Path 'database' | Out-Null
if (-not (Test-Path 'database\database.sqlite')) {
    Write-Step 'Creating SQLite database file'
    New-Item -ItemType File -Path 'database\database.sqlite' | Out-Null
}

function Find-Composer {
    $candidates = @(
        (Join-Path $Root '.demo-tools\composer.phar'),
        'C:\php\composer.phar'
    )

    foreach ($candidate in $candidates) {
        if (Test-Path $candidate) {
            return @{ Type = 'phar'; Path = (Resolve-Path $candidate).Path }
        }
    }

    $composerCommand = Get-Command composer -ErrorAction SilentlyContinue
    if ($composerCommand) {
        & $composerCommand.Source --version *> $null
        if ($LASTEXITCODE -eq 0) {
            return @{ Type = 'command'; Path = $composerCommand.Source }
        }
    }

    return $null
}

function Invoke-Composer([string[]] $ComposerArgs) {
    if ($script:Composer.Type -eq 'command') {
        & $script:Composer.Path @ComposerArgs
    } else {
        $phpComposerArgs = @()
        $phpComposerArgs += $script:PhpArgs
        $phpComposerArgs += $script:Composer.Path
        $phpComposerArgs += $ComposerArgs
        & $script:Php @phpComposerArgs
    }

    Assert-CommandSucceeded $LASTEXITCODE 'Composer command failed.'
}

$Composer = Find-Composer
if (-not $Composer) {
    Write-Step 'Downloading Composer'
    New-Item -ItemType Directory -Force -Path '.demo-tools' | Out-Null
    $composerPath = Join-Path $Root '.demo-tools\composer.phar'
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    Invoke-WebRequest 'https://getcomposer.org/composer-stable.phar' -OutFile $composerPath
    $Composer = @{ Type = 'phar'; Path = $composerPath }
}

Write-Step 'Installing PHP dependencies'
Invoke-Composer @('install', '--no-interaction', '--prefer-dist')

Write-Step 'Preparing Laravel application'
& $Php @PhpArgs artisan key:generate --force
Assert-CommandSucceeded $LASTEXITCODE 'Laravel key generation failed.'

& $Php @PhpArgs artisan config:clear
Assert-CommandSucceeded $LASTEXITCODE 'Laravel config clear failed.'

if ($NoFresh) {
    & $Php @PhpArgs artisan migrate --seed --force
    Assert-CommandSucceeded $LASTEXITCODE 'Laravel migrations failed.'
} else {
    & $Php @PhpArgs artisan migrate:fresh --seed --force
    Assert-CommandSucceeded $LASTEXITCODE 'Laravel fresh migrations failed.'
}

$Url = "http://127.0.0.1:$Port/"
Write-Step 'Demo is ready'
Write-Host "URL: $Url" -ForegroundColor Green
Write-Host 'Demo accounts:' -ForegroundColor Green
Write-Host '  master:  olga@example.com / Master1234'
Write-Host '  visitor: visitor@example.com / Visitor1234'

if ($NoServe) {
    exit 0
}

Write-Host ""
Write-Host 'Starting local server. Press Ctrl+C to stop it.' -ForegroundColor Yellow
& $Php @PhpArgs -S "127.0.0.1:$Port" -t 'public' 'vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php'
