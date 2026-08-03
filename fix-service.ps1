$content = Get-Content "c:\xampp1\htdocs\vms\touriza-htm\about.html" -Raw
$oldText = @'
                <p class="desc">We're here for you anytime, anywhere. Reach out to us day or night.</p>
            </div>
            <div class="service-wrapper">
                <div class="icon"><img src="assets/images/service/icon-02.svg" alt=""></div>
                <h5 class="title">Safety-First Expeditions</h5>
                <p class="desc">We're equipped with emergency kits, trained staff, and real-time monitoring</p>
            </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="service-wrapper">
                            <div class="icon"><i class="fa-solid fa-plane"></i></div>
                            <h5 class="title">Domestic & International Holiday Packages</h5>
                            <p class="desc">Customized holiday packages for destinations worldwide</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
'@
$newText = @'
                            <p class="desc">Customized holiday packages for destinations worldwide</p>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
'@
$content = $content.Replace($oldText, $newText)
$content | Set-Content "c:\xampp1\htdocs\vms\touriza-htm\about.html" -NoNewline
Write-Host "Fixed service section"
