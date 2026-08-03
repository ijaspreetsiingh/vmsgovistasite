# Read file as UTF-8
$content = Get-Content "c:\xampp1\htdocs\vms\touriza-htm\about.html" -Raw -Encoding UTF8

# Find and replace the broken service section
$pattern = '(?s)                <div class="row g-4">.*?                    <div class="col-lg-4 col-md-6">\s*                        <div class="service-wrapper">\s*                            <div class="icon"><i class="fa-solid fa-plane"></i></div>\s*                            <h5 class="title">Domestic &amp; International Holiday Packages</h5>\s*                <p class="desc">We're here for you anytime, anywhere\. Reach out to us day or night\.</p>\s*            </div>\s*            <div class="service-wrapper">\s*                <div class="icon"><img src="assets/images/service/icon-02\.svg" alt=""></div>\s*                <h5 class="title">Safety-First Expeditions</h5>\s*                <p class="desc">We're equipped with emergency kits, trained staff, and real-time monitoring</p>\s*            </div>\s*                    </div>\s*                    <div class="col-lg-4 col-md-6">\s*                        <div class="service-wrapper">\s*                            <div class="icon"><i class="fa-solid fa-plane"></i></div>\s*                            <h5 class="title">Domestic &amp; International Holiday Packages</h5>\s*                            <p class="desc">Customized holiday packages for destinations worldwide</p>\s*                        </div>\s*                    </div>\s*                    <div class="col-lg-4 col-md-6">'

$replacement = @'
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6">
                        <div class="service-wrapper">
                            <div class="icon"><i class="fa-solid fa-plane"></i></div>
                            <h5 class="title">Domestic &amp; International Holiday Packages</h5>
                            <p class="desc">Customized holiday packages for destinations worldwide</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
'@

$content = $content -replace $pattern, $replacement
$content | Set-Content "c:\xampp1\htdocs\vms\touriza-htm\about.html" -Encoding UTF8 -NoNewline
Write-Host "Fixed service section"
