
  </div><!-- /admin-body -->
</div><!-- /admin-main -->

<script src="<?= SITE_URL ?>/assets/js/plugins/jquery.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/plugins/bootstrap.min.js"></script>
<script>
// Generic repeater logic
document.addEventListener('DOMContentLoaded', function () {
    // Add repeater row
    document.querySelectorAll('.add-repeater-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target  = btn.dataset.target;
            var wrapper = document.getElementById(target);
            var tpl     = document.getElementById(target + '-template');
            if (!wrapper || !tpl) return;
            var idx  = wrapper.querySelectorAll('.repeater-item').length;
            var html = tpl.innerHTML.replace(/__IDX__/g, idx);
            var div  = document.createElement('div');
            div.className = 'repeater-item';
            div.innerHTML = html;
            wrapper.appendChild(div);

            // Auto-increment day number for itinerary rows (prevents duplicate 'Day 1')
            if (target === 'itinerary-wrapper') {
                var dayInputs = wrapper.querySelectorAll('input[name="itin_day[]"]');
                var maxDay = 0;
                dayInputs.forEach(function(inp) {
                    var v = parseInt(inp.value, 10);
                    if (!isNaN(v) && v > maxDay) maxDay = v;
                });
                if (dayInputs.length) {
                    dayInputs[dayInputs.length - 1].value = maxDay + 1;
                }
            }
        });
    });

    // Remove repeater row (delegated)
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-btn')) {
            if (confirm('Remove this item?')) {
                e.target.closest('.repeater-item').remove();
            }
        }
    });

    // Image preview on file input change
    document.querySelectorAll('input[type=file][data-preview]').forEach(function(input) {
        input.addEventListener('change', function() {
            var preview = document.getElementById(input.dataset.preview);
            if (!preview || !input.files[0]) return;
            var reader = new FileReader();
            reader.onload = function(e) { preview.src = e.target.result; preview.style.display = 'block'; };
            reader.readAsDataURL(input.files[0]);
        });
    });
});
</script>
</body>
</html>
