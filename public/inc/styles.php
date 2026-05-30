<?php
/**
 * SATUSEHAT API Catalog - Common Styles
 * Reusable CSS styles and Tailwind configuration for all pages
 */
?>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#1e40af',
                    secondary: '#3b82f6',
                    success: '#10b981',
                    warning: '#f59e0b',
                    danger: '#ef4444',
                }
            }
        }
    }
</script>
<style>
    .gradient-bg {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    }
    .card-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1rem;
    }
    .method-post { background-color: #dcfce7; color: #166534; }
    .method-put { background-color: #ffedd5; color: #9c271a; }
    .method-get { background-color: #dbeafe; color: #1e40af; }
    .method-patch { background-color: #fef3c7; color: #92400e; }
    .method-delete { background-color: #fee2e2; color: #991b1b; }
</style>