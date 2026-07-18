$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$nodeRoot = Join-Path $projectRoot '.tools\node-v22.12.0-win-x64'
$npm = Join-Path $nodeRoot 'npm.cmd'

if (-not (Test-Path -LiteralPath $npm)) {
    throw 'Chưa có Node portable. Cài Node 22.12.0 vào .tools\node-v22.12.0-win-x64.'
}

Push-Location $projectRoot
try {
    $env:Path = "$nodeRoot;$env:Path"
    & (Join-Path $nodeRoot 'node.exe') --version
    & $npm ci
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

    & $npm run build
    exit $LASTEXITCODE
} finally {
    Pop-Location
}
