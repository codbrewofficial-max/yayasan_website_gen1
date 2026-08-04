<#
.SYNOPSIS
    Tambahkan tenant lokal: entry hosts subdomain + (opsional) buat record tenant di database.

.DESCRIPTION
    Karena hosts Windows tidak mendukung wildcard, tiap tenant lokal butuh satu baris
    di hosts. Script ini menambahkannya secara idempotent dan (jika diminta) membuat
    record tenant via artisan tinker.

    HARUS dijalankan sebagai Administrator.

.EXAMPLE
    .\add-tenant.ps1 -Subdomain kerkomit

.EXAMPLE
    .\add-tenant.ps1 -Subdomain kerkomit -Name "Yayasan Kerkomit" -CreateTenant
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory = $true)]
    [string]$Subdomain,

    [string]$Domain = 'yayasan-go-digital.test',

    [string]$Name = '',

    [string]$ContactEmail = '',

    [string]$ContactPhone = '',

    [string]$AppPath,

    [string]$PhpPath = 'D:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe',

    [switch]$CreateTenant
)

$ErrorActionPreference = 'Stop'
$hostsFile = Join-Path $env:windir 'System32\drivers\etc\hosts'
$hostname = "$Subdomain.$Domain"

if (-not $AppPath) {
    $AppPath = Join-Path $PSScriptRoot 'app'
}

# ---------------------------------------------------------------------------
# 1. Pastikan elevasi (menulis hosts butuh admin)
# ---------------------------------------------------------------------------
$identity = [Security.Principal.WindowsIdentity]::GetCurrent()
$principal = New-Object Security.Principal.WindowsPrincipal($identity)
if (-not $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)) {
    Write-Warning 'Script ini HARUS dijalankan sebagai Administrator.'
    Write-Host "Jalankan ulang: klik kanan PowerShell -> 'Run as administrator', lalu:" -ForegroundColor Yellow
    Write-Host "    .\add-tenant.ps1 -Subdomain $Subdomain" -ForegroundColor Yellow
    exit 1
}

# ---------------------------------------------------------------------------
# 2. Tambahkan entry hosts (idempotent)
# ---------------------------------------------------------------------------
if (-not (Test-Path $hostsFile)) {
    New-Item -Path $hostsFile -ItemType File -Force | Out-Null
}

$content = Get-Content $hostsFile
$exists = $content | Where-Object { $_ -match [regex]::Escape($hostname) }

if ($exists) {
    Write-Host "Entry hosts untuk '$hostname' sudah ada - dilewati." -ForegroundColor Green
} else {
    $entry = "127.0.0.1`t$hostname`t#laragon magic!"
    Add-Content -Path $hostsFile -Value $entry -Encoding ASCII
    Write-Host "Entry hosts ditambahkan: $entry" -ForegroundColor Green
}

& ipconfig /flushdns | Out-Null
Write-Host 'DNS cache di-flush.' -ForegroundColor Green

# ---------------------------------------------------------------------------
# 3. (Opsional) Buat record tenant di database
# ---------------------------------------------------------------------------
if ($CreateTenant) {
    if (-not $Name) {
        throw '-CreateTenant butuh -Name untuk nama yayasan.'
    }

    if (-not (Test-Path $PhpPath)) {
        Write-Host "PhpPath '$PhpPath' tidak ditemukan, fallback ke 'php' dari PATH." -ForegroundColor Yellow
        $PhpPath = 'php'
    }

    $artisan = Join-Path $AppPath 'artisan'
    if (-not (Test-Path $artisan)) {
        throw "artisan tidak ditemukan di '$AppPath'."
    }

    # Snippet PHP untuk artisan tinker (literal here-string -> aman dari escaping).
    $snippet = @'
$existing = \App\Models\Tenant::withoutGlobalScopes()->firstWhere('subdomain', '__SUBDOMAIN__');
if ($existing) { echo 'Tenant sudah ada.' . PHP_EOL; return; }
$t = \App\Models\Tenant::create([
    'name' => '__NAME__',
    'subdomain' => '__SUBDOMAIN__',
    'category' => 'sosial',
    'status' => 'active',
    'contact_email' => __EMAIL__,
    'contact_phone' => __PHONE__,
]);
echo 'Tenant dibuat: ' . $t->name . ' -> ' . $t->subdomain . '__SUFFIX__' . PHP_EOL;
'@

    $phpEsc = { param($v) return "'" + $v.Replace("'", "\'") + "'" }

    $phpName = & $phpEsc $Name
    $phpSub = & $phpEsc $Subdomain
    $phpSuffix = '.' + $Domain
    if ($ContactEmail) { $phpEmail = & $phpEsc $ContactEmail } else { $phpEmail = 'null' }
    if ($ContactPhone) { $phpPhone = & $phpEsc $ContactPhone } else { $phpPhone = 'null' }

    $snippet = $snippet.Replace('__NAME__', $phpName)
    $snippet = $snippet.Replace('__SUBDOMAIN__', $phpSub)
    $snippet = $snippet.Replace('__SUFFIX__', $phpSuffix)
    $snippet = $snippet.Replace('__EMAIL__', $phpEmail)
    $snippet = $snippet.Replace('__PHONE__', $phpPhone)

    & $PhpPath $artisan tinker --execute $snippet
    if ($LASTEXITCODE -ne 0) {
        throw 'Gagal membuat tenant via tinker.'
    }
}

Write-Host "Selesai. Akses: http://$hostname/" -ForegroundColor Cyan