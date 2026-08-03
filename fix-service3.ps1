$lines = Get-Content "c:\xampp1\htdocs\vms\touriza-htm\about.html" -Encoding UTF8

# Find the line numbers to replace
$startLine = -1
$endLine = -1

for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match "                <div class=`"row g-4`">") {
        $startLine = $i
    }
    if ($startLine -gt 0 -and $lines[$i] -match "                    <div class=`"col-lg-4 col-md-6`">" -and $i -gt $startLine + 10) {
        $endLine = $i
        break
    }
}

Write-Host "Found lines: $startLine to $endLine"

# Build new content
$newLines = @()
for ($i = 0; $i -lt $startLine; $i++) {
    $newLines += $lines[$i]
}

# Add the corrected section
$newLines += "                <div class=`"row g-4`">"
$newLines += "                    <div class=`"col-lg-4 col-md-6`">"
$newLines += "                        <div class=`"service-wrapper`">"
$newLines += "                            <div class=`"icon`"><i class=`"fa-solid fa-plane`"></i></div>"
$newLines += "                            <h5 class=`"title`">Domestic &amp; International Holiday Packages</h5>"
$newLines += "                            <p class=`"desc`">Customized holiday packages for destinations worldwide</p>"
$newLines += "                        </div>"
$newLines += "                    </div>"
$newLines += "                    <div class=`"col-lg-4 col-md-6`">"

# Add remaining lines
for ($i = $endLine; $i -lt $lines.Count; $i++) {
    $newLines += $lines[$i]
}

# Write back
$newLines | Set-Content "c:\xampp1\htdocs\vms\touriza-htm\about.html" -Encoding UTF8
Write-Host "Fixed service section"
