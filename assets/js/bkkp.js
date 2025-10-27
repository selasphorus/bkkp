jQuery(document).ready(function($) {
    // Add copy icons to table cells with numeric values
    // Target either td.numeric OR td containing span.numeric
    $('td.numeric, td:has(span.numeric)').each(function() {
        var $cell = $(this);
        var $link = $cell.find('a.txn-total');
        var $numericSpan = $cell.find('span.numeric');
        
        // Only add copy icon if there's a numeric value (not zero/dash)
        if ($numericSpan.length && $link.length) {
            // Extract just the number (remove $, commas, and other formatting)
            var numberText = $numericSpan.text().replace(/[^0-9.-]/g, '');
            
            // Create copy icon
            var $copyIcon = $('<span class="copy-number-icon" data-number="' + numberText + '" title="Copy number"></span>');
            
            // Insert icon after the link (but inside the td)
            $link.after($copyIcon);
        }
    });
    
    // Handle copy action
    $(document).on('click', '.copy-number-icon', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var number = $(this).data('number');
        
        // Copy to clipboard
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(number).then(function() {
                // Visual feedback
                var $icon = $(e.target);
                var originalContent = $icon.text();
                $icon.text('✓');
                setTimeout(function() {
                    $icon.text(originalContent);
                }, 1000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
            });
        } else {
            // Fallback for older browsers
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(number).select();
            document.execCommand('copy');
            $temp.remove();
            
            // Visual feedback
            var $icon = $(this);
            var originalContent = $icon.text();
            $icon.text('✓');
            setTimeout(function() {
                $icon.text(originalContent);
            }, 1000);
        }
    });
});