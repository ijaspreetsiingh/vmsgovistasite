$lines = Get-Content "c:\xampp1\htdocs\vms\touriza-htm\about.html" -Encoding UTF8

# Find and remove the duplicate line
$newLines = @()
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($i -gt 0 -and $lines[$i] -eq "                    <div class=`"col-lg-4 col-md-6`">" -and $lines[$i-1] -eq "                    <div class=`"col-lg-4 col-md-6`">") {
        # Skip this duplicate line
        continue
    }
    $newLines += $lines[$i]
}

$newLines | Set-Content "c:\xampp1\htdocs\vms\touriza-htm\about.html" -Encoding UTF8
Write-Host "Removed duplicate line"
