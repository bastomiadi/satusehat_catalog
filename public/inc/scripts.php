<?php
/**
 * SATUSEHAT API Catalog - Common Scripts
 * Reusable JavaScript for all pages
 */
?>
<script>
    // Modal functions
    function openModal() {
        document.getElementById('apiModal').classList.remove('hidden');
        document.getElementById('apiModal').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('apiModal').classList.add('hidden');
        document.getElementById('apiModal').classList.remove('flex');
        document.body.style.overflow = '';
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Copy to clipboard function
    function copyFromPre(button) {
        const preElement = button.closest('.bg-gray-900').querySelector('pre');
        const content = preElement.textContent || preElement.innerText;
        
        navigator.clipboard.writeText(content).then(function() {
            const originalHTML = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check mr-1"></i> Copied!';
            button.classList.add('bg-green-600');
            button.classList.remove('bg-gray-700');
            setTimeout(function() {
                button.innerHTML = originalHTML;
                button.classList.remove('bg-green-600');
                button.classList.add('bg-gray-700');
            }, 2000);
        }).catch(function(err) {
            console.error('Failed to copy: ', err);
            alert('Failed to copy content. Please try again.');
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>